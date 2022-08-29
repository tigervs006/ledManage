<?php
declare (strict_types = 1);
namespace app\console\controller\channel;

use core\exceptions\ApiException;
use think\exception\ValidateException;
use think\response\Json;
use core\basic\BaseController;
use app\services\channel\ModuleServices;

class ModuleController extends BaseController
{
    /**
     * @var ModuleServices
     */
    private ModuleServices $services;

    private string $validator = 'app\console\validate\ModuleValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ModuleServices::class);
    }

    /**
     * 获取模型列表
     * @return Json
     */
    final public function list(): Json
    {
        $whereLike = [];
        $betweenTime = [];
        /** 获取搜索条件 */
        $map = $this->request->only(['status'], 'get', 'trim');
        /** 获取模型标识 */
        $nid = $this->request->get('nid/s', null, 'trim');
        /** 获取模型名条件 */
        $name = $this->request->get('name/s', null, 'trim');
        /** 获取时间范围 */
        $dateRange = $this->request->only(['dateRange'], 'get', 'trim');
        /** 获取排序条件 */
        $order = $this->request->only(['id', 'create_time'], 'get', 'strOrderFilter');
        /** 组装控制器搜索条件 */
        $nid && array_push($whereLike, ['nid', '%' . $nid . '%']);
        /** 组装模型名搜索条件 */
        $name && array_push($whereLike, ['name', '%' . $name . '%']);
        /** 组装按时间段搜索条件 */
        $dateRange && $betweenTime = ['create_time', $dateRange['dateRange'][0], $dateRange['dateRange'][1]];
        /** 获取模型列表 */
        $list = $this->services->getList($this->current, $this->pageSize, $map ?: null, '*', $order ?: ['id' => 'asc'], $betweenTime, $whereLike);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($map ?: null, null, $betweenTime, $whereLike);
            return $this->json->successful(compact('total', 'list'));
        }
    }

    final public function save(): Json
    {
        $post = $this->request->only(
            [
                'id',
                'nid',
                'name',
                'status',
                'ctl_name',
            ], 'post', 'trim'
        );
        $message = '新增';
        /* 释放由EditableProTable随机生成的字符串id */
        if (isset($post['id']) && is_numeric($post['id'])) {
            $message =  '编辑';
        } else {
            unset($post['id']);
        }
        /* 验证数据 */
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }
        /* 保存数据 */
        $this->services->saveModule($post, $message);
        return $this->json->successful($message . '模型成功');
    }

    /**
     * 单个/批量删除
     * @return Json
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除模型失败');
        });
        return $this->json->successful('删除模型成功');
    }

    /**
     * 设置模型状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $status = $this->request->post(['status']);
        $message = $status['status'] ? '启用' : '禁用';
        $this->services->updateOne($this->id, $status);
        return $this->json->successful($message . '模型成功');
    }
}
