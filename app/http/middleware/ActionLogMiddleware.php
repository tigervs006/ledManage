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

namespace app\http\middleware;

use app\Request;
use core\interfaces\MiddlewareInterface;
use app\services\system\SystemLogServices;

class ActionLogMiddleware implements MiddlewareInterface
{
    private SystemLogServices $logServices;

    public function __construct(SystemLogServices $logServices)
    {
        $this->logServices = $logServices;
    }

    public function handle(Request $request, \Closure $next)
    {
        $this->logServices->actionLogRecord($request->tokenInfo());
        return $next($request);
    }
}
