<?php
declare (strict_types = 1);
namespace app\index\controller;

use core\basic\BaseController;
use app\services\channel\ChannelServices;
use app\services\product\ProductServices;

class Product extends BaseController
{

    /**
     * @var ProductServices
     */
    private ProductServices $services;

    /**
     * @var ChannelServices
     */
    private ChannelServices $channelServices;

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ProductServices::class);
        $this->channelServices = $this->app->make(ChannelServices::class);
    }

    /**
     * 商品列表
     * @return string
     */
    public function list(): string
    {
        $name = ['name' => getPath()];
        $pid = $this->channelServices->value($name, 'pid');
        is_null($pid) && abort(404, "page doesn't exist");
        $map = !$pid
            ? $this->status
            : array_merge($this->status, ['pid' => $this->channelServices->value($name)]);
        $list = $this->services->getPaginate($map, $this->pageSize, null, $this->order, ['channel']);
        return $this->view::fetch('../product/index', compact('list'));
    }

    /**
     * 商品详情
     * @return string
     */
    public function detail(): string
    {
        $map = array_merge($this->status, ['id' => $this->id]);
        $info = $this->services->getOne($map, '*', ['detail']);
        is_null($info) && abort(404, "page doesn't exist");
        return $this->view::fetch('../product/detail', compact('info'));
    }
}
