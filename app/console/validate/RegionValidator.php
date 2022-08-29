<?php

namespace app\console\validate;

use think\validate;

class RegionValidator extends validate
{
    protected $regex = [
        'code' => '[^0]\d{11}',
    ];

    protected $rule = [
        'pid'   => 'require|integer',
        'code'  => 'require|regex:code',
        'name'  => 'require|chs',
    ];

    protected $message = [
        'pid.require'       => '请选择上级地区',
        'pid.integer'       => '上级地区ID必须为正整数',
        'code.require'      => '地区编码不得为空',
        'code.regex'        => '地区编码应为非0开头的12位整数',
        'name.require'      => '地区名称不得为空',
        'name.alphaDash'    => '地区名称只能是中文',
    ];
}
