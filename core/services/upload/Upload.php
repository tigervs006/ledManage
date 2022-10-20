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

declare (strict_types = 1);
namespace core\services\upload;

use think\facade\Config;
use core\basic\BaseManager;

/**
 * Class Upload
 * @package core\services\upload
 * @mixin \core\services\upload\storage\OSS
 * @mixin \core\services\upload\storage\COS
 */
class Upload extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\core\\services\\upload\\storage\\';

    /**
     * 设置默认上传类型
     * @return mixed
     */
    protected function getDefaultDriver(): mixed
    {
        return Config::get('upload.default', 'local');
    }
}
