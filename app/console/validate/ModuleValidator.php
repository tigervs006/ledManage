<?php

namespace app\console\validate;

use think\Validate;

class ModuleValidator extends Validate
{
    protected $regex = [
        'upper' => '[A-Z][a-zA-Z0-9]+'
    ];

    protected $rule = [
        'nid'       => 'require|max:30|alphaDash',
        'name'      => 'require|max:30',
        'status'    => ['regex'=>'/^[0|1]$/'],
        'ctl_name'  => 'require|max:30|regex:upper',
    ];

    protected $message = [
        'nid.require'       => '模型标识是必填字段',
        'nid.max'           => '模型标识不得超过30个字符',
        'nid.alphaDash'     => '模型标识只能是英文、数字和下划线的组合',
        'name.require'      => '模型名称是必填字段',
        'name.max'          => '模型名称不得超过30个字符',
        'status.regex'      => '模型状态只能是0或1的正整数',
        'ctl_name.require'  => '控制器名是必填字段',
        'ctl_name.max'      => '控制器名不得超过30个字符',
        'ctl_name.regex'    => '控制器名首字母必须是大写'
    ];
}
