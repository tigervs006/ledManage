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
namespace app\services\system;

use app\dao\system\AttachDao;
use app\services\BaseServices;

class AttachServices extends BaseServices
{
    public function __construct(AttachDao $dao)
    {
        $this->dao = $dao;
    }
}
