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

use think\facade\Cache;
use app\services\system\ConfigServices;

return Cache::remember('config', function () {
    /** @var ConfigServices $services */
    $services = app()->make(ConfigServices::class);
    $result = $services->getData(null, null, 'name, value')->toArray();
    return count($result) ? array_column($result, 'value', 'name') : '';
}, 3600 * 24 * 7);
