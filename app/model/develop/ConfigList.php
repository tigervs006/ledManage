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

declare (strict_types=1);

namespace app\model\develop;

use core\basic\BaseModel;

/**
 * class ConfigList
 * @createAt 2022/11/18 23:56
 * @package app\model\develop
 */
class ConfigList extends BaseModel
{
    protected $jsonAssoc = true;
    protected $json = ['formProps'];
}
