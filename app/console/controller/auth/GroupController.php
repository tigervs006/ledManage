<?php
declare (strict_types = 1);
namespace app\console\controller\auth;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use app\services\auth\GroupServices;
use think\exception\ValidateException;

class GroupController extends BaseController
{
    private GroupServices $services;

    private string $validator = 'app\console\validate\GroupValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(GroupServices::class);
    }

    /**
     * 新增/编辑用户组
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->post([
            'id',
            'name',
            'menu',
            'status',
        ], null, 'trim');
        $message = '新增'; // 设置message的默认值
        // 释放由EditableProTable随机生成的字符串id
        if (isset($post['id']) && is_numeric($post['id'])) {
            $message =  '编辑';
        } else {
            unset($post['id']);
        }
        // 验证必要数据
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $this->services->saveGroup($post, $message);
        return $this->json->successful($message . '用户组成功');
    }

    /**
     * 获取用户组列表
     * @return Json
     */
    final public function list(): Json
    {
        $map = $this->request->only([
            'status'
        ], 'get', 'trim');
        // 获取排序字段
        $order = $this->request->only(['create_time', 'update_time'], 'get', 'strOrderFilter');
        $list = $this->services->getList($this->current, $this->pageSize, $map ?: null, '*', $order);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            // 计算数据总量
            $total = $this->services->getCount($map ?: null);
            return $this->json->successful(compact('total', 'list'));
        }
    }

    /**
     * 删除用户组
     * @return Json
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除用户组失败');
        });
        return $this->json->successful('删除用户组成功');
    }

    /**
     * 设置用户组状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $status = $this->request->post(['status']);
        $message = $status['status'] ? '启用' : '禁用';
        $this->services->updateOne($this->id, $status);
        return $this->json->successful($message . '用户组成功');
    }
}
