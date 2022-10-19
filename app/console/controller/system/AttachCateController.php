<?php
declare (strict_types = 1);
namespace app\console\controller\system;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use think\exception\ValidateException;
use app\services\system\AttachCateServices;

class AttachCateController extends BaseController
{
    /**
     * @var AttachCateServices
     */
    private AttachCateServices $services;

    private string $validator = 'app\console\validate\AttachCateValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(AttachCateServices::class);
    }

    /**
     * 分类信息
     * @return Json
     */
    final public function index(): Json
    {
        $info = $this->services->getOne(['id' => $this->id], '*');
        return is_null($info) ? $this->json->fail() : $this->json->successful(compact('info'));
    }

    /**
     * 分类列表
     * @return Json
     */
    final public function list(): Json
    {
        $data = $this->services->getData(null, null);
        $list = $this->services->getTreeData($data, 0, null);
        return $data->isEmpty() ? $this->json->fail() : $this->json->successful(compact('list'));
    }

    /**
     * 新增/编辑
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only([
            'id',
            'pid',
            'size',
            'path',
            'name',
            'ename',
            'aspects',
            'astricts',
            'crop' => 0,
            'limit' => 0,
            'astrict' => 0,
        ], 'post', 'trim');
        $message = isset($post['id']) ? '编辑' : '新增';
        /* 过滤空值参数 */
        $data = array_filter($post, function ($val) {
            /* 避免过滤boolean值 */
            return !("" === $val || null === $val);
        });
        /* 验证必要数据 */
        try {
            $this->validate($data, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        /* 默认目录路径名 */
        $data['dirname'] = $data['ename'];
        if (0 < $data['pid']) {
            /* 分割目录id */
            $ids = explode('-', $data['path']);
            /* 查询目录别名 */
            $result = $this->services->getData(array(['id', 'in', $ids]), null, 'ename');
            $names = $result->isEmpty() ? [] : array_column($result->toArray(), 'ename');
            /* 拼接目录路径 */
            $data['dirname'] = implode('/', array_merge($names, [$data['ename']]));
        }
        /* 组装json数据 */
        $data['config'] = array_filter([
            'crop'      => (int) $post['crop'],
            'limit'     => (int) $post['limit'],
            'astrict'   => (int) $post['astrict'],
            'size'      => isset($post['size']) ? (int) $post['size'] : null,
            'aspects'   => isset($post['aspects']) ? array_map('intval', $post['aspects']) : null,
            'astricts'  => isset($post['astricts']) ? array_map('intval', $post['astricts']) : null,
        ], function ($val) {
            return $val !== null;
        });
        $this->services->saveCate($data, $message);
        return $this->json->successful($message . '目录成功');
    }

    /**
     * 删除目录
     * @return Json
     */
    final public function delete(): Json
    {
        $data = $this->services->getData(null, null);
        $ids = $this->services->getChildrenIds($data, $this->id);
        array_unshift($ids, $this->id); /* 追加自身 */
        $this->services->transaction(function () use ($ids) {
            $res = $this->services->delete($ids);
            !$res && throw new ApiException('删除目录失败');
        });
        return $this->json->successful('删除目录成功');
    }
}
