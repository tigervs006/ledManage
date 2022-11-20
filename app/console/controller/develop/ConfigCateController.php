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

use core\exceptions\ApiException;
use think\response\Json;
use core\basic\BaseController;
use app\services\develop\ConfigCateServices;

/**
 * class ConfigCateController
 * @createAt 2022/11/16 12:09
 * @package app\console\controller\develop
 */
class ConfigCateController extends BaseController
{
    /**
     * @var ConfigCateServices
     */
    private ConfigCateServices $services;

    private string $validator = 'app\console\validate\ConfigCateValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ConfigCateServices::class);
    }

    /**
     * 获取配置列表
     * @return Json
     * @author Kevin
     * @createAt 2022/11/16 12:16
     */
    final public function list(): Json
    {
        $map = $this->request->only([
            'id',
            'name',
            'cname',
            'status'
        ], 'get', 'intvals');
        /* 组装搜索条件 */
        $where = $map ? filterParams($map) : [];
        /* 获取排序字段 */
        $order = $this->request->only(
            ['create_time'], 'get', 'strOrderFilter');
        $list = $this->services->getList($this->current, $this->pageSize, $where ?: null, '*', $order ?: $this->order);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($where ?: null);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    /**
     * 保存配置分类
     * @return Json
     * @author Kevin
     * @createAt 2022/11/16 15:00
     */
    final public function save(): Json
    {
        $message = '新增';
        $post = $this->request->only([
            'id',
            'name',
            'cname',
            'status' => 1,
        ], 'post', 'intvals');
        /* 释放由EditableProTable随机生成的字符串id */
        if (isset($post['id']) && is_numeric($post['id'])) {
            $message =  '编辑';
        } else {
            unset($post['id']);
        }
        try {
            $this->validate($post, $this->validator);
        } catch (\think\exception\ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $this->services->saveConfigCate($post, $message);
        return $this->json->successful($message . '配置分类成功');
    }

    /**
     * 删除配置分类
     * @return Json
     * @author Kevin
     * @createAt 2022/11/16 13:12
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除配置分类失败');
        });
        return $this->json->successful('删除配置分类成功');
    }

    /**
     * 设置分类状态
     * @return Json
     * @author Kevin
     * @createAt 2022/11/16 13:12
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
        return $this->json->successful($message . '配置分类成功');
    }
}
