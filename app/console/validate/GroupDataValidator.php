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
 * class GroupDataValidator
 * @createAt 2022/10/31 12:11
 * @package app\console\validate
 */
class GroupDataValidator extends validate
{
    protected $rule = [
        'id'            => 'integer',
        'cid'           => 'integer',
        'name'          => 'require|max:38|regex:[\x7f-\xff\/\w_]+',
        'cname'         => 'require|max:64|regex:[a-zA-Z_]+',
        'summary'       => 'require|max:128',
        'fields_type'   => 'require|array'
    ];

    protected $message = [
        'id.integer'            => 'id必须为正整数',
        'cid.integer'           => 'cid必须为正整数',
        'name.require'          => '字段名不得为空',
        'name.max'              => '字段名不得超过38个字符',
        'name.regex'            => '字段名不得包含其它特殊符号',
        'cname.require'         => '字段别名不得为空',
        'cname.max'             => '字段别名不得超过64个字符',
        'cname.regex'           => '字段别名只能是英文字母和下划线的组合',
        'summary.require'       => '数组简述不得为空',
        'summary.max'           => '数组简述不得过去128个字符',
        'fields_type.require'   => '数组字段不得为空',
        'fields_type.array'     => '数组字段参数类型应为array'
    ];
}
