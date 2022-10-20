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

declare (strict_types = 1);
namespace app\console\controller\product;

use think\response\Json;
use core\basic\BaseController;
use core\exceptions\ApiException;
use core\utils\StringHandler as Str;
use think\exception\ValidateException;
use app\services\product\ProductServices;
use app\services\channel\ChannelServices;

class ProductController extends BaseController
{
    private ProductServices $services;

    private string $validator = 'app\console\validate\ProductValidator';

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ProductServices::class);
    }

    /**
     * 获取商品信息
     * @return Json
     */
    final public function index(): Json
    {
        $info = $this->services->getOne(['id' => $this->id], null, ['detail']);
        return is_null($info) ? $this->json->fail() : $this->json->successful(compact('info'));
    }

    /**
     * 新增/编辑商品
     * @return Json
     */
    final public function save(): Json
    {
        $post = $this->request->only([
            'id',
            'pid',
            'album',
            'title',
            'sales',
            'stock',
            'price',
            'special',
            'content',
            'keywords',
            'inquiries',
            'description',
        ], 'post', 'trim');

        if (isset($post['id'])) {
            $message = '编辑';
        } else {
            $message = '新增';
            $post['click'] = mt_rand(436, 695);
            $post['sales'] = mt_rand(682, 1869);
            $post['inquiries'] = mt_rand(862, 2468);
        }

        /* 处理特殊符号 */
        $post['keywords'] = Str::strSymbol($post['keywords']);
        /* 验证数据 */
        try {
            $this->validate($post, $this->validator);
        } catch (ValidateException $e) {
            throw new ApiException($e->getError());
        }

        $this->services->saveProduct($post, $message);

        return $this->json->successful($message . '商品成功');
    }

    /**
     * 获取商品列表
     * @return Json
     */
    final public function list(): Json
    {
        // 模糊查找
        $whereLike = [];
        // 时间段搜索
        $betweenTime = [];
        /** 获取搜索标题 */
        $title = $this->request->get('title/s', null, 'trim');
        /** 获取时间范围 */
        $dateRange = $this->request->only(['dateRange'], 'get', 'trim');
        /** 获取搜索条件 */
        $map = $this->request->only(['id', 'pid', 'status'], 'get', 'trim');
        /** 获取排序条件 */
        $order = $this->request->only(['click', 'sales', 'stock', 'price', 'inquiries'], 'get', 'strOrderFilter');
        /** 组装标题搜索条件 */
        $title && $whereLike = ['title', '%' . $title . '%'];
        /** 组装按时间段搜索条件  */
        $dateRange && $betweenTime = ['create_time', $dateRange['dateRange'][0], $dateRange['dateRange'][1]];
        $list = $this->services->getList($this->current, $this->pageSize, $map ?: null, '*', $order ?: $this->order, $betweenTime, $whereLike, ['channel']);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($map ?: null, null, $betweenTime, $whereLike);
            return $this->json->successful(compact('list', 'total'));
        }
    }

    /**
     * 删除商品
     * @return Json
     */
    final public function delete(): Json
    {
        $this->services->remove($this->id);
        return $this->json->successful('删除商品成功');
    }

    /**
     * 商品状态
     * @return Json
     */
    final public function setStatus(): Json
    {
        $data = $this->request->post(['status']);
        $message = $data['status'] ? '上架' : '下架';
        $this->services->updateOne($this->id, $data);
        return $this->json->successful($message . '商品成功');
    }
}
