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
use app\services\develop\FieldsGroupServices;

/**
 * class groupDataController
 * @createAt 2022/10/30 20:18
 * @package app\console\controller\develop
 */
class GroupDataController extends BaseController
{
    /**
     * @var FieldsGroupServices
     */
    private FieldsGroupServices $services;

    private string $validator = 'app\console\validate\GroupDataValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(FieldsGroupServices::class);
    }

    /**
     * 获取数组信息
     * @return Json
     * @author Kevin
     * @createAt 2022/11/8 10:22
     */
    final public function info(): Json
    {
        $map = [
            'id' => $this->id,
            'status' => $this->status,
        ];
        $info = [];
        $data = $this->services->getOne($map, '*');
        if (!is_null($data))
        foreach ($data['fields_type'] as $val) {
            $info['formProps'][] = array_shift($val['formProps']);
            $info['tableProps'][] = array_shift($val['tableProps']);
        }
        return is_null($data) ? $this->json->fail() : $this->json->successful(compact('info'));
    }

    /**
     * 组合数据列表
     * @return Json
     * @author Kevin
     * @createAt 2022/10/30 20:54
     */
    final public function list(): Json
    {
        $map = $this->request->only([
            'name',
            'cname',
        ], 'get', 'trim');
        /* 组装搜索条件 */
        $where = $map ? filterSearch($map) : [];
        /* 获取排序字段 */
        $order = $this->request->only(['id', 'create_time'], 'get', 'strOrderFilter');
        $list = $this->services->getList($this->current, $this->pageSize, $where ?: null, '*', $order ?: $this->order);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($where ?: null);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    /**
     * 保存组合数据
     * @return Json
     * @author Kevin
     * @createAt 2022/10/31 13:25
     */
    final public function save(): Json
    {
        $post = $this->request->only([
            'id',
            'name',
            'cname',
            'summary',
            'cid' => 0,
            'fields_type'
        ], 'post', 'intvals');
        /* 验证必要数据 */
        try {
            $this->validate($post, $this->validator);
        } catch (\think\exception\ValidateException $e) {
            throw new ApiException($e->getError());
        }
        $message = isset($post['id']) ? '编辑' : '新增';
        $this->services->saveGroupData($post, $message);
        return $this->json->successful($message . '组合数据成功');
    }

    /**
     * 删除组合数据
     * @return Json
     * @author Kevin
     * @createAt 2022/10/31 13:33
     */
    final public function delete(): Json
    {
        $id = $this->id;
        $this->services->transaction(function () use ($id) {
            $res = $this->services->delete($id);
            !$res && throw new ApiException('删除组合数据失败');
        });
        return $this->json->successful('删除组合数据成功');
    }
}
