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

class FormValidator extends validate
{
    protected $regex = [
        'tel' => '1[3456789]\d{9}',
    ];

    protected $rule = [
        'username'  => 'require|max:20',
        'mobile'    => 'require|integer|regex:tel',
        'email'     => 'require|email',
        'company'   => 'require|max:30',
        'province'  => 'require|integer',
        'city'      => 'require|integer',
        'district'  => 'require|integer',
        'message'   => 'require|min:10|max:100'
    ];

    protected $message = [
        'username.require'  => '请填写您的姓名',
        'username.max'      => '没有一句话这么长的姓名',
        'mobile.require'    => '请填写您的手机号码',
        'mobile.regex'      => '手机号码格式错误',
        'email.require'     => '请填写您的邮件地址',
        'email.email'       => '请正确填写您的邮箱',
        'company.require'   => '请填写您的公司名称',
        'company.max'       => '没有一句话这么长的公司名称',
        'province.require'  => '请选择您所在的省份',
        'province.integer'  => '省份的ID必须为整数',
        'city.require'      => '请选择您所在的城市',
        'city.integer'      => '城市的ID必须为整数',
        'district.require'  => '请选择您所在的区域',
        'district.integer'  => '区域的ID必须为整数',
        'message.require'   => '请简要描述您的需求',
        'message.min'       => '请稍微详细点描述您的需求',
        'message.max'       => '留下您的联系后再和工作人员详细沟通吧'
    ];
}
