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
use Throwable;

class About extends BaseController
{
    /**
     * @var ArticleServices
     */
    private ArticleServices $services;

    /**
     * @var ChannelServices
     */
    private ChannelServices $channelServices;

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ArticleServices::class);
        $this->view::assign('hotart', $this->hortArt()); // 获取热门文章
        $this->channelServices = $this->app->make(ChannelServices::class);
    }

    /**
     * 企业简介
     * @author Kevin
     * @return string
     * @throws Throwable
     * @createAt 2022/11/3 23:55
     */
    final public function index(): string
    {
        $info = $this->channelServices->listInfo();
        return $this->view::fetch('../about/index', compact('info'));
    }

    /**
     * 热门文章
     * @return array|Collection
     */
    final public function hortArt(): array|Collection
    {
        return $this->services->getList(
            1,
            10,
            array(['status', '=', 1], ['cid', 'in', '36']),
            'id, cid, click, title, litpic, create_time',
            ['click' => 'desc'], null, null, ['channel']);
    }

    /**
     * 团队风采
     * @return string
     * @author Kevin
     * @throws Throwable
     * @createAt 2022/11/3 22:41
     */
    final public function team(): string
    {
        $info = $this->channelServices->listInfo();
        return $this->view::fetch('../about/team', compact('info'));
    }

    /**
     * 荣誉资质
     * @return string
     * @author Kevin
     * @throws Throwable
     * @createAt 2022/11/3 22:43
     */
    final public function honor(): string
    {
        $info = $this->channelServices->listInfo();
        return $this->view::fetch('../about/honor', compact('info'));
    }

    /**
     * 合作客户
     * @return string
     * @author Kevin
     * @throws Throwable
     * @createAt 2022/11/3 22:44
     */
    final public function client(): string
    {
        $info = $this->channelServices->listInfo();
        return $this->view::fetch('../about/client', compact('info'));
    }

    /**
     * 企业文化
     * @return string
     * @author Kevin
     * @throws Throwable
     * @createAt 2022/11/3 22:44
     */
    final public function culture(): string
    {
        $info = $this->channelServices->listInfo();
        return $this->view::fetch('../about/culture', compact('info'));
    }

    /**
     * 招贤纳士
     * @return string
     * @author Kevin
     * @throws Throwable
     * @createAt 2022/11/3 22:45
     */
    final public function recruit(): string
    {
        $info = $this->channelServices->listInfo();
        return $this->view::fetch('../about/recruit', compact('info'));
    }
}
