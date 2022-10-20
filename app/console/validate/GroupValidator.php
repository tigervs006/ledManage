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

class GroupValidator extends validate
{
    protected $regex = [
        'bool'  => '[0|1]',
        'auth'  => '[\d?:\,]+',
        'name'  => '[\x7f-\xff\_]+'
    ];

    protected $rule = [
        'id'        => 'integer',
        'status'    => 'integer|regex:bool',
        'name'      => 'require|regex:name',
        'menu'      => 'require|regex:auth',
    ];

    protected $message = [
        'id.integer'        => '用户组ID必须是正整数',
        'status.integer'    => '用户组状态必须是正整数',
        'status.regex'      => '用户组状态只能是0或1的正整数',
        'name.require'      => '用户组名不得为空',
        'name.regex'        => '用户组名只能是中英文、数字与下划线的组合',
        'menu.require'      => '请为当前用户组至少设置一个权限菜单',
        'menu.regex'        => '用户组权限菜单只能是数字和英文逗号的组合',
    ];
}
