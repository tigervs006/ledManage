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
namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\Attach;

class AttachDao extends BaseDao
{

    /**
     * @inheritDoc
     */
    protected function setModel(): string
    {
        return Attach::class;
    }
}
