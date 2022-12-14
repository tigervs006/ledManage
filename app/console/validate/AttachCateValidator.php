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

class AttachCateValidator extends validate
{
    protected $rule = [
        'id'        => 'integer',
        'path'      => 'max:32',
        'pid'       => 'require|integer',
        'name'      => 'require|max:64|chsDash',
        'ename'     => 'require|max:64|alphaDash',
        'option'    => 'boolean',
        'crop'      => 'boolean',
        'limit'     => 'boolean',
        'astrict'   => 'boolean',
        'suff'      => 'boolean',
        'mime'      => 'boolean',
        'size'      => 'requireWith:limit|integer',
        'aspects'   => 'requireWith:crop|array',
        'astricts'  => 'requireWith:astrict|array',
        'fileExt'   => 'requireWith:suff|array',
        'fileMime'  => 'requireWith:mime|array',
    ];

    protected $message = [
        'id.integer'            => 'id必须为正整数',
        'pid.require'           => '上级分类是必填项',
        'pid.integer'           => '上级分类id必须为正整数',
        'name.require'          => '分类目录是必填项',
        'path.max'              => '目录路径不得走去32个字符',
        'name.max'              => '分类目录不得超过64个字符',
        'name.chsDash'          => '分类目录不得包含其它特殊符号',
        'ename.require'         => '目录别名是必填项',
        'ename.max'             => '目录别名不得超过64个字符',
        'ename.alphaDash'       => '目录别名只能是字母、数字和下划线及破折号的组合',
        'option.boolean'        => '配置方式必须为布尔值',
        'crop.boolean'          => '裁剪选项值必须为布尔值',
        'limit.boolean'         => '文件限制选项值必须为布尔值',
        'astrict.boolean'       => '宽高限制值必须为布尔值',
        'size.integer'          => '文件大小值必须为正整数',
        'size.requireWith'      => '请完善文件大小的必要值',
        'aspects.requireWith'   => '请完善裁剪比例的必要值',
        'aspects.array'         => '裁剪比例值必须为数组类型',
        'astricts.requireWith'  => '请完善宽高限制的必要值',
        'astricts.array'        => '宽高限制值必须为数组类型',
        'suff.boolean'          => '启用文件后缀的值必须为布尔值',
        'mime.boolean'          => '启用文件类型的值必须为布尔值',
        'fileExt.requireWith'   => '请完善文件后缀的必要值',
        'fileExt.array'         => '文件后缀的值必须为数组',
        'fileMime.requireWith'  => '请完善文件类型的必要值',
        'fileMime.array'        => '文件类型的值必须为数组',
    ];
}
