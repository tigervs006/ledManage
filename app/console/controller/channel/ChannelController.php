<?php
declare (strict_types = 1);
namespace app\console\controller\channel;

use think\facade\Cache;
use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use core\utils\StringHandler as Str;
use think\exception\ValidateException;
use app\services\channel\ChannelServices;

class ChannelController extends BaseController
{
    private ChannelServices $services;

    private string $validator = 'app\console\validate\ChannelValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ChannelServices::class);
    }

    /**
     * 删除栏目
     * @return Json
     */
    final public function delete(): Json
    {
        $this->services->remove($this->id);
        Cache::delete('channel'); /* 清除缓存 */
        return $this->json->successful('删除栏目成功');
    }

    /**
     * 树状结构数据
     * @return Json
     */
    final public function list(): Json
    {
        $whereLike = [];
        $map = $this->request->get(['status'], null, 'trim');
        $cname = $this->request->get('cname/s', null, 'trim');
        $cname && $whereLike = ['cname', '%' . $cname . '%'];
        $data = $this->services->getData($map ?? null, ['id' => 'asc'], '*', null, $whereLike);
        $list = empty($map) ? $this->services->getTreeData($data) : $data;
        return empty($list) ? $this->json->fail() : $this->json->successful(compact('list'));
    }

    /**
     * 新增/编辑栏目
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only(
            [
                'nid',
                'pid',
                'sort',
                'path',
                'name',
                'cname',
                'level',
                'title',
                'banner',
                'status',
                'keywords',
                'id' => null,
                'description'
            ], 'post', 'trim');
        $message = $post['id'] ? '编辑' : '新增';
        /* 过滤空值参数 */
        $data = array_filter($post, function ($val) {
            /* 避免过滤0、boolean值 */
            return !("" === $val || null === $val);
        });
        /* 验证必要数据 */
        try {
            $this->validate($data, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        /* 默认栏目级别 */
        $data['level'] = 0;
        /* 栏目相对路径 */
        $data['dirname'] = null;
        /* 栏目绝对路径 */
        $data['fullpath'] = "{$data['name']}/";
        /* 处理特殊符号 */
        $data['keywords'] = Str::strSymbol($data['keywords']);
        /* 处理二级以下的栏目 */
        if (0 < $data['pid']) {
            $names = [];
            /* 分割栏目id */
            $ids = explode('-', $data['path']);
            /* 查询栏目别名 */
            foreach ($ids as $id) {
                $names[] = $this->services->value(['id' => $id], 'name');
            }
            /* 设置网站栏目级别 */
            $data['level'] = $this->services->value(['id' => $data['pid']], 'level') + 1;
            /* 拼接栏目绝对路径 */
            $data['fullpath'] = implode('/', array_merge($names, [$data['name']])) . '/';
            /* 拼接栏目相对路径 */
            array_shift($names);
            $data['dirname'] = implode('/', array_merge($names, [$data['name']])) . '/';
        }
        /* 保存栏目数据 */
        $this->services->saveChannel($data, $message);
        return $this->json->successful($message . '栏目成功');
    }

    /**
     * 栏目状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $data = $this->request->post(['status']);
        $message = $data['status'] ? '显示' : '隐藏';
        $this->services->updateOne($this->id, $data);
        Cache::delete('channel'); /* 清除缓存 */
        return $this->json->successful($message . '栏目成功');
    }

    /**
     * 获取指定分类的栏目
     * @return Json
     */
    final public function getCate(): Json
    {
        $field = 'id, name, cname, fullpath';
        $map = $this->request->only(['nid'], 'get', 'trim');
        $list = $this->services->getData($map, ['id' => 'asc'], $field);
        return $list ? $this->json->successful(compact('list')) : $this->json->fail();
    }
}
