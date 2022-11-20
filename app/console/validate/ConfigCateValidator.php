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

namespace app\console\validate;

use think\validate;

/**
 * class ConfigCateValidator
 * @createAt 2022/11/16 15:04
 * @package app\console\validate
 */
class ConfigCateValidator extends validate
{
    protected $rule = [
        'id'        => 'integer',
        'name'      => 'require|max:255',
        'cname'     => 'require|max:255',
        'status'    => 'require|boolean'
    ];

    protected $message = [
        'id.integer'        => 'id必须是正整数',
        'name.require'      => '分类别名不得为空',
        'name.max'          => '分类别名不得超过255个字符',
        'cname.require'     => '分类名称不得为空',
        'cname.max'         => '分类名称不得超过255个字符',
        'status.require'    => '分类状态不得为空',
        'status.boolean'    => '分类状态需为boolean',
    ];
}
