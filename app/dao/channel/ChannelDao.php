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
namespace app\dao\channel;

use app\dao\BaseDao;
use app\model\channel\Channel;

class ChannelDao extends BaseDao
{
    protected function setModel(): string
    {
        return Channel::class;
    }
}
