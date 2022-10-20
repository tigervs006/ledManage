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

class ProductValidator extends validate
{
    protected $rule = [
        'pid'           => 'require|integer',
        'title'         => 'require|length:8,32',
        'keywords'      => 'require|length:8,32',
        'description'   => 'require|length:20,256',
        'album'         => 'require|array',
        'special'       => 'require|array',
        'content'       => 'require|min:50',
    ];

    protected $message = [
        'pid.require'           => '请选择商品所属栏目',
        'pid.integer'           => '商品所属栏目的id必须是正整数',
        'title.require'         => '商品名称是必填字段',
        'title.length'          => '商品名称应在8~32个字符数之间',
        'keywords.require'      => '商品关键词是必填字段',
        'keywords.length'       => '商品关键词应在8~32个字符数之间',
        'description.require'   => '商品简述是必填字段',
        'description.length'    => '商品简述应在20~256个字符数之间',
        'album.require'         => '商品相册是必填字段',
        'album.array'           => '商品相册的值必须是数组',
        'special.require'       => '商品卖点是必填字段',
        'special.array'         => '商品卖点的值必须是数组',
        'content.require'       => '请完善商品详情',
        'content.min'           => '商品详情不得少于50个字符',
    ];
}
