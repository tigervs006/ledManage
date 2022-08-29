<?php
declare (strict_types = 1);
namespace app\console\controller\channel;

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
                'status',
                'keywords',
                'id' => null,
                'description'
            ], 'post', 'trim'
        );
        $message = $post['id'] ? '编辑' : '新增';
        // 过滤空值字段
        $data = array_filter($post, function ($val) {
            // 避免过滤0、boolean值
            return !("" === $val || null === $val);
        });
        $data['level'] = 0;
        if (!empty($data['pid']) && 0 <= $data['pid']) {
            $data['level'] = $this->services->value(['id' => $data['pid']], 'level') + 1;
        }
        // 验证必要数据
        try {
            $this->validate($data, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        // 处理特殊符号
        $data['keywords'] = Str::strSymbol($data['keywords']);
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
        return $this->json->successful($message . '栏目成功');
    }
}
