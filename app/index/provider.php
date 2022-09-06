<?php

// 容器Provider定义文件
use app\index\ExceptionHandle;

return [
    'think\Paginator' => 'app\index\Bootstrap',
    'think\exception\Handle' => ExceptionHandle::class
];
