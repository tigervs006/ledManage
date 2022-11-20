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
 * class FieldsGroup
 * @createAt 2022/10/30 20:23
 * @package app\model\develop
 */
class FieldsGroup extends BaseModel
{
    protected $jsonAssoc = true;
    protected $json = ['fields_type'];
}
