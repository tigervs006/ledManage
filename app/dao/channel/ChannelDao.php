<?php
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