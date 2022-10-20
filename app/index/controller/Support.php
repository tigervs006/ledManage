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

use core\basic\BaseController;
use app\services\channel\ChannelServices;

class Support extends BaseController
{
    /**
     * @var ChannelServices
     */
    private ChannelServices $channelServices;

    public function initialize()
    {
        parent::initialize();
        $this->channelServices = $this->app->make(ChannelServices::class);
    }

    final public function index(): string
    {
        $name = ['name' => getPath()];
        $map = array_merge($this->status, $name);
        $row = $this->channelServices->getOne($map, '*', ['module']);
        is_null($row) && abort(404, "page doesn't exist");
        $template = match ($row['module']['nid']) {
            'download' => '../modules/software',
            'video' => '../modules/video',
            default => '../support/index',
        };
        return $this->view::fetch($template);
    }

    final public function detail(): string
    {
        return $this->view::fetch('../support/detail');
    }

    final public function video(): string
    {
        return $this->view::fetch('../modules/detail');
    }
}
