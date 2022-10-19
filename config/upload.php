<?php

return [
    // 默认上传模式
    'default' => 'local',
    // 上传文件大小
    'filesize' => 10485760,
    // 上传文件后缀
    'fileExt' => ['jpg', 'jpeg', 'png', 'gif', 'pem', 'mp3', 'wma', 'wav', 'amr', 'mp4', 'key', 'xlsx', 'xls', 'txt', 'ico'],
    // 上传文件类型
    'fileMime' => [
        'image/jpeg',
        'image/gif',
        'image/png',
        'text/plain',
        'audio/mpeg',
        'video/mp4',
        'application/octet-stream',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-works',
        'application/vnd.ms-excel',
        'application/zip',
        'application/vnd.ms-excel',
        'application/vnd.ms-excel',
        'text/xml',
        'image/x-icon',
        'image/vnd.microsoft.icon',
    ],
    // 驱动模式
    'stores' => [
        // 本地上传配置
        'local' => [],
        // oss上传配置
        'oss' => [],
        // cos上传配置
        'cos' => [],
    ]
];
