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

class About extends BaseController
{
    /**
     * @var ArticleServices
     */
    private ArticleServices $articleServices;

    public function initialize()
    {
        parent::initialize();
        $info = $this->app->make(ChannelServices::class);
        $this->articleServices = $this->app->make(ArticleServices::class);
        $this->view::assign(['info' => $info->listInfo(), 'hotart' => $this->hortArt()]);
    }

    /**
     * 企业简介
     * @author Kevin
     * @return string
     * @createAt 2022/11/3 23:55
     */
    final public function index(): string
    {
        return $this->view::fetch('../about/index');
    }

    /**
     * 热门文章
     * @return array|Collection
     */
    final public function hortArt(): array|Collection
    {
        return $this->articleServices->getList(
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
     * @createAt 2022/11/3 22:41
     */
    final public function team(): string
    {
        return $this->view::fetch('../about/team');
    }

    /**
     * 荣誉资质
     * @return string
     * @author Kevin
     * @createAt 2022/11/3 22:43
     */
    final public function honor(): string
    {
        return $this->view::fetch('../about/honor');
    }

    /**
     * 合作客户
     * @return string
     * @author Kevin
     * @createAt 2022/11/3 22:44
     */
    final public function client(): string
    {
        return $this->view::fetch('../about/client');
    }

    /**
     * 企业文化
     * @return string
     * @author Kevin
     * @createAt 2022/11/3 22:44
     */
    final public function culture(): string
    {
        return $this->view::fetch('../about/culture');
    }

    /**
     * 招贤纳士
     * @return string
     * @author Kevin
     * @createAt 2022/11/3 22:45
     */
    final public function recruit(): string
    {
        return $this->view::fetch('../about/recruit');
    }
}
