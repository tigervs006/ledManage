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

class ChannelValidator extends validate
{
    protected $regex = [
        'url'   => '[\/]{1,2}[^\s]*'
    ];

    protected $rule = [
        'nid'           => 'require|integer',
        'pid'           => 'require|integer',
        'sort'          => 'integer|between:1,1000',
        'path'          => 'require|max:50',
        'name'          => 'require|alphaDash|min:2|max:50',
        'cname'         => 'require|chsDash|min:2|max:32',
        'banner'        => 'regex:url',
        'status'        => 'integer|between:0,1',
        'title'         => 'require|min:8|max:32',
        'keywords'      => 'require|min:8|max:32',
        'description'   => 'require|min:20|max:100'
    ];

    protected $message = [
        'nid.require'           => '模型标识不得为空',
        'nid.integer'           => '模型标识必须是正整数',
        'pid.require'           => '请选择上级栏目',
        'pid.integer'           => '上级栏目ID必须是正整数',
        'sort.integer'          => '栏目排序必须是正整数',
        'sort.between'          => '栏目排序范围应在1~1000',
        'path.require'          => '栏目路径不得为空',
        'path.max'              => '栏目路径深度不得超过50',
        'name.require'          => '栏目别名不得为空',
        'name.alphaDash'        => '栏目别名就为字母、数字或下划线的组合',
        'name.min'              => '栏目别名不得少于2个字符',
        'name.max'              => '栏目别名请控制在50个字符以内',
        'cname.require'         => '栏目名称不得为空',
        'cname.chsDash'         => '栏目名称只能是汉字、字母、数字和下划线_及破折号',
        'cname.min'             => '栏目名称不得于2个中文字符',
        'cname.max'             => '栏目名称不得超过32个中文字符',
        'banner.regex'          => '栏目图片网址错误，只需截取[//]后面的网址则可',
        'status.integer'        => '栏目状态必须是正整数',
        'status.between'        => '栏目状态的值应为0或1',
        'title.require'         => 'SEO标题不得为空',
        'title.min'             => 'SEO标题不得少于8个字符',
        'title.max'             => 'SEO标题请控制在32个字符以内',
        'keywords.require'      => 'SEO关键词不得为空',
        'keywords.min'          => 'SEO关键词不得少于8个字符',
        'keywords.max'          => 'SEO关键词请控制在32个字符以内',
        'description.require'   => 'SEO描述不得不空',
        'description.min'       => 'SEO描述不得少20个字符',
        'description.max'       => 'SEO描述请控制在100个字符以内'
    ];
}
