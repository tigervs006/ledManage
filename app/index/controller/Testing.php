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
namespace app\index\controller;

use think\Collection;
use core\basic\BaseController;
use app\services\channel\ChannelServices;
use app\services\article\ArticleServices;

class Testing extends BaseController
{
    /**
     * @var ArticleServices
     */
    private ArticleServices $services;

    /**
     * @var ChannelServices
     */
    private ChannelServices $channelServices;

    /**
     * @var string
     */
    private string $field = 'id, cid, click, title, author, litpic, create_time, description';

    protected function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ArticleServices::class);
        $this->view::assign('related', $this->related()); // 获取相关文档
        $this->channelServices = $this->app->make(ChannelServices::class);
    }

    /**
     * 测试内容
     * @return string
     */
    final public function index(): string
    {
        $map = array(['status', '=', 1], ['cid', 'notin', '35,36']);
        $info = $this->services->get($this->id, null, ['content']);
        // 阅读量自增
        $info && $this->services->setInc($info['id'], $this->incValue);
        // 上 / 下一篇
        $prenext = $this->services->getPrenext($info['id'], $map, 'id, cid, title');
        return $this->view::fetch('../testing/index', compact('info', 'prenext'));
    }

    /**
     * 测试列表
     * @return string
     * @throws \Throwable
     */
    final public function list(): string
    {
        /* 获取当前栏目信息 */
        $info = $this->channelServices->listInfo();
        $map = array_merge($this->status, ['cid' => $info['ids']]);
        $list = $this->services->getPaginate($map, $this->current, 9, $info['fullpath'], $this->field, $this->order, ['channel']);
        return $this->view::fetch('../testing/list', compact('list', 'info'));
    }

    /**
     * 热门文档
     * @return array|Collection
     */
    final public function related(): array|Collection
    {
        return $this->services->getList(
            1,
            4,
            array(['status', '=', 1], ['cid', 'notin', '35,36']),
            'id, cid, click, title, litpic',
            ['click' => 'desc'], null, null, ['channel']);
    }
}
