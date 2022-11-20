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
use app\services\develop\FieldsGroupDataServices;

/**
 * class groupDataListController
 * @createAt 2022/10/30 20:19
 * @package app\console\controller\develop
 */
class GroupDataListController extends BaseController
{
    /**
     * @var FieldsGroupDataServices
     */
    private FieldsGroupDataServices $services;

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(FieldsGroupDataServices::class);
    }

    /**
     * 获取数据列表
     * @return Json
     * @author Kevin
     * @createAt 2022/11/10 12:00
     * fixme: json字段查询不支持in语句
     */
    final public function list(): Json
    {
        $params = $this->request->only(
            ['id', 'gid', 'status'], 'get', 'intvals'
        );
        $where = filterParams($params);
        $jsonParams = $this->request->except(
            [
                'id',
                'gid',
                'order',
                'status',
                'current',
                'pageSize',
                'create_time',
                'update_time',
            ], 'get');
        $jsonWhere = filterParams($jsonParams, true, 'value');
        /* 获取排序字段 */
        $order = $this->request->only(
            ['create_time', 'update_time'], 'get', 'strOrderFilter'
        );
        $result = $this->services->jsonSearch(
            $this->current,
            $this->pageSize,
            (bool) $jsonWhere, ['value'],
            $where ?: null, $jsonWhere ?: null, $order ?: $this->order);
        if ($result->isEmpty()) {
            return $this->json->fail();
        } else {
            $list = [];
            $data = $result->toArray();
            foreach ($data as $val) {
                $v = (array) $val['value'];
                unset($val['value']);
                $list[] = array_merge($val, $v);
            }
            $total = $this->services->getCount($where ?: null);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    /**
     * 保存数组列表
     * @return Json
     * @author Kevin
     * @createAt 2022/11/15 0:06
     */
    final public function save(): Json
    {
        $post = $this->request->only(
            ['id', 'gid', 'status'], 'post', 'intvals');
        $value = $this->request->except(['id', 'gid'], 'post');
        try {
            $this->validate(
                $post,
                [
                    'id'        => 'integer',
                    'gid'       => 'require|integer',
                    'status'    => 'boolean',
                ],
                [
                    'id.integer'        => 'id必须是正整数',
                    'gid.require'       => '请完善数组GID',
                    'gid.integer'       => 'GID必须是正整数',
                    'status.boolean'    => 'status需为boolean',
                ]
            );
        } catch (\think\exception\ValidateException $e) {
            throw new ApiException($e->getMessage());
        }
        $message = isset($post['id']) ? '编辑' : '新增';
        $data = array_merge($post, compact('value'));
        $this->services->saveGroupDataList($data, $message);
        return $this->json->successful($message . '组合数据列表成功');
    }

    /**
     * 启用/禁用数组
     * @return Json
     * @author Kevin
     * @createAt 2022/11/10 12:03
     */
    final public function status(): Json
    {
        $post = $this->request->only(
            ['status'], 'post', 'intval'
        );
        try {
            $this->validate(
                $post,
                ['status' => 'boolean'],
                ['status.boolean' => 'status需为boolean']
            );
        } catch (\think\Exception\ValidateException $e) {
            throw new ApiException($e->getMessage());
        }
        $message = $post['status'] ? '启用' : '禁用';
        $this->services->updateOne($this->id, $post);
        return $this->json->successful($message . '数组成功');
    }

    /**
     * 删除数据记录
     * @return Json
     * @author Kevin
     * @createAt 2022/10/31 13:33
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除数据记录失败');
        });
        return $this->json->successful('删除数据记录成功');
    }
}
