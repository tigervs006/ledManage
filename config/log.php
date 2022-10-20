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
    // 默认日志记录通道
    'default'      => env('log.channel', 'file'),
    // 日志记录级别
    'level'        => ['error','warning'],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close'        => false,
    // 全局日志处理 支持闭包
    'processor'    => null,

    // 日志通道列表
    'channels'     => [
        'file' => [
            // 独立日志级别
            'apart_level'    => ['sql', 'error'],
            // 最大日志文件数量
            'max_files'      => 30,
            // 日志处理
            'processor'      => null,
            // 单文件日志写入
            'single'         => false,
            // 使用JSON格式记录
            'json'           => false,
            // 关闭通道日志写入
            'close'          => false,
            // 是否实时写入
            'realtime_write' => false,
            // 日志记录方式
            'type'           => 'File',
            // 日志输出格式化
            'format'         => '[%s][%s] %s',
            // 日志保存目录
            'path'           => app()->getRuntimePath() . 'log' . DIRECTORY_SEPARATOR
        ],
        // 其它日志通道配置
    ],

];
