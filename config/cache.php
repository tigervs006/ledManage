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

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // redis缓存
        'redis'   =>  [
            // 驱动方式
            'type'          => 'redis',
            // 服务器地址
            'host'          => env('redis.redis_hostname', '127.0.0.1'),
            // 端口
            'port'          => env('redis.port', '6379'),
            // 密码
            'password'      => env('redis.redis_password', ''),
            // 缓存有效期 0表示永久缓存
            'expire'        => 0 ,
            // 缓存前缀
            'prefix'        => 'tag:',
            // 缓存标签前缀
            'tag_prefix'    => 'BRAND:',
            // 数据库 0号数据库
            'select'        => env('redis.select', 0),
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'     => [],
            // 服务端主动关闭
            'timeout'       => 0
        ],
    ],
];
