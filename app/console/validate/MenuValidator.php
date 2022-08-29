<?php

namespace app\console\validate;

use think\validate;

class MenuValidator extends validate
{
    protected $regex = [
        'path'      => '[\d?:\-]+',
        'locale'    => '[\w?:\.]+',
        'route'     => '[a-zA-Z0-9\/]+'
    ];

    protected $rule = [
        'pid'                   => 'require|integer',
        'type'                  => 'require|integer',
        'name'                  => 'require|alphaDash',
        'routes'                => 'requireIf:type,3',
        'icon'                  => 'requireCallBack:check_require_icon|alphaDash',
        'sort'                  => 'integer|between:1,1000',
        'paths'                 => 'require|regex:path',
        'locale'                => 'require|regex:locale',
    ];

    protected $message = [
        'pid.require'           => '请选择上级菜单，默认为顶级菜单',
        'pid.integer'           => '上级菜单的id必须为正整数',
        'type.require'          => '显示类型是必填字段',
        'type.integer'          => '显示类型必须是正整数',
        'paths.require'         => '菜单路径是必填字段',
        'routes.requireIf'      => '请为接口设置路由规则',
        'routes.regex'          => '按钮/接口路径只能是英文字母、数字和斜杠的组合',
        'paths.regex'           => '菜单路径错误，参考格式：0-1-2',
        'name.require'          => '菜单名称为必填项',
        'name.alphaDash'        => '菜单名称只能是英文字母、数字和下划线、破折号的组合',
        'icon.requireCallBack'  => '请为顶级菜单设置图标',
        'icon.alphaDash'        => '菜单图标只能是英文字母、数字和下划线、破折号的组合',
        'sort.integer'          => '菜单排序必须是正整数',
        'sort.between'          => '菜单排序数值应在1~1000之间',
        'locale.require'        => '本地语言是必填字段',
        'locale.regex'          => '多语言只能是英文字母、数字和下划线、英文句号的组合'
    ];

    /** 只有是菜单类型和顶级菜单时图标是必填字段 */
    function check_require_icon($value, $data): bool
    {
        return !$value && 1 == $data['type'] && 0 == $data['pid'];
    }
}
