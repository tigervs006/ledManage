<?php
/*
 * +----------------------------------------------------------------------------------
 * | https://www.tigervs.com
 * +----------------------------------------------------------------------------------
 * | Email: Kevin@tigervs.com
 * +----------------------------------------------------------------------------------
 * | Copyright (c) Shenzhen Tiger Technology Co., Ltd. 2018~2022. All rights reserved.
 * +----------------------------------------------------------------------------------
 */

declare (strict_types=1);

namespace app\console\controller\develop;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use app\services\develop\ConfigListServices;

/**
 * class ConfigListController
 * @createAt 2022/11/19 0:01
 * @package app\console\controller\develop
 */
class ConfigListController extends BaseController
{
    /**
     * @var ConfigListServices
     */
    private ConfigListServices $services;

    private string $validator = 'app\console\validate\ConfigListValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ConfigListServices::class);
    }

    /**
     * 配置列表
     * @return Json
     * @author Kevin
     * @createAt 2022/11/19 0:27
     */
    final public function list(): Json
    {
        $map = $this->request->only([
            'id',
            'cid',
            'name',
            'fname',
            'status'
        ], 'get', 'intvals');
        /* 组装搜索条件 */
        $where = $map ? filterParams($map) : [];
        /* 获取排序字段 */
        $order = $this->request->only(
            ['sort', 'create_time'], 'get', 'strOrderFilter');
        $list = $this->services->getList($this->current, $this->pageSize, $where ?: null, '*', $order ?: $this->order);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($where ?: null);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    /**
     * 保存配置
     * @return Json
     * @author Kevin
     * @createAt 2022/11/19 0:31
     */
    final public function save(): Json
    {
        $post = $this->request->only([
            'id',
            'cid',
            'name',
            'fname',
            'value',
            'formProps',
            'sort' => 50,
            'status' => 1,
        ], 'post', 'intvals');
        $message = isset($post['id']) ? '编辑' : '新增';
        try {
            $this->validate($post, $this->validator);
        } catch (\think\exception\ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $this->services->saveConfigList($post, $message);
        return $this->json->successful($message . '配置成功');
    }

    /**
     * 删除配置
     * @return Json
     * @author Kevin
     * @createAt 2022/11/19 0:35
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除配置失败');
        });
        return $this->json->successful('删除配置成功');
    }

    /**
     * 设置配置状态
     * @return Json
     * @author Kevin
     * @createAt 2022/11/19 0:36
     */
    final public function status(): Json
    {
        $post = $this->request->only(
            ['status'], 'post', 'intval'
        );
        try {
            $this->validate(
                $post,
                ['status' => 'require|boolean'],
                [
                    'status.require' => '状态值不得为空',
                    'status.boolean' => '状态值需为布尔值'
                ]
            );
        } catch (\think\Exception\ValidateException $e) {
            throw new ApiException($e->getMessage());
        }
        $message = $post['status'] ? '启用' : '禁用';
        $this->services->updateOne($this->id, $post);
        return $this->json->successful($message . '配置成功');
    }
}
