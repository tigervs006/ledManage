<?php

namespace app\http\middleware;

use app\Request;
use app\services\auth\AuthServices;
use core\interfaces\MiddlewareInterface;

class AuthCheckMiddleware implements MiddlewareInterface
{
    private AuthServices $authServices;

    public function __construct(AuthServices $authServices)
    {
        $this->authServices = $authServices;
    }

    public function handle(Request $request, \Closure $next)
    {
        config('index.user_auth_check') && $this->authServices->verifyAuthority($request->tokenInfo());
        return $next($request);
    }
}
