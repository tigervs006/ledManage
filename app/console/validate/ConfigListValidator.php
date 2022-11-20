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
 * class ConfigListValidator
 * @createAt 2022/11/19 0:03
 * @package app\console\validate
 */
class ConfigListValidator extends validate
{
    protected $rule = [
        'id'        => 'integer',
        'sort'      => 'integer',
        'status'    => 'boolean',
        'formProps' => 'require|array',
        'cid'       => 'require|integer',
        'fname'     => 'require|alphaDash|max:255',
        'name'      => 'require|max:255|regex:[\x7f-\xff\/\w_]+',
    ];

    protected $message = [
        'id.integer'        => 'id必须是正整数',
        'cid.require'       => '请指定配置所属分类',
        'cid.integer'       => '分类ID必须是正整数',
        'sort.integer'      => '配置排序必须是正整数',
        'status.boolean'    => '配置状态必须是布尔值',
        'name.require'      => '配置名称不得为空',
        'name.max'          => '配置名称不得超过255个字符串',
        'name.regex'        => '配置名称不得包含其它特殊符号',
        'fname.require'     => '字段名称不得为空',
        'fname.max'         => '字段名称不得超过255个字符串',
        'fname.alphaDash'   => '字段名称只能是英文字母和下划线的组合',
        'formProps.require' => '表单属性不得为空',
        'formProps.array'   => '表单属性值须为数组',
    ];
}
