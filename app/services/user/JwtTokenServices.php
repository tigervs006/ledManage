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
namespace app\services\user;

use app\dao\user\JwtTokenDao;
use app\services\BaseServices;

class JwtTokenServices extends BaseServices
{
    public function __construct(JwtTokenDao $dao)
    {
        $this->dao = $dao;
    }
}
