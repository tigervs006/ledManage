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

namespace app\console\validate;

use think\validate;

class LinkValidator extends validate
{
    protected $regex = [
        'bool'  => '[0|1]',
    ];

    protected $rule = [
        'url'           => 'require|url',
        'name'          => 'require|max:15',
        'sort'          => 'require|between:1,999',
        'status'        => 'require|regex:bool',
        'contact'       => 'require|max:20',
        'description'   => 'max: 50',
    ];

    protected $message = [
        'url.require'   => '友链地址不得为空',
        'url.url'       => '不是有效的url地址',
        'name.require'  => '友链名称不得为空',
        'name.max'      => '友链名称请控制在15个字符以内',
        'sort.require'  => '友链排序不得为空',
        'sort.between'  => '友链排序的有效值就在1~999之间',
        'status.require'    => '友链状态值不得为空',
        'status.bool'       => '友链状态值应为0或1的正整数',
        'contact.require'   => '联系方式不得为空',
        'contact.max'       => '联系方式请控制在20个字符以内',
        'description.max'   => '其它备注信息请控制在50个字符以内',
    ];
}
