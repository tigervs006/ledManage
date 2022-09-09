<?php
declare (strict_types = 1);
namespace app\http\middleware;

use app\Request;
use think\Response;
use core\utils\JwtAuth;
use core\interfaces\MiddlewareInterface;

/**
 * AuthToken中间件
 * Class AuthTokenMiddleware
 * @package app\http\middleware
 */
class AuthTokenMiddleware implements MiddlewareInterface
{
    /**
     * JwtAuth
     * @var JwtAuth
     */
    private JwtAuth $jwtService;

    public function __construct(JwtAuth $jwtService) {
        $this->jwtService = $jwtService;
    }

    /**
     * @return Response
     * @param \Closure $next
     * @param Request $request
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if (config('index.access_token_check')) {
            $isRefreshToken = $request->isRefreshToken();
            /* 此方法仅在ExceptionHandle类使用 */
            $request->parseTokenInfo = $this->jwtService->parseToken($request->token());
            $this->jwtService->verifyToken($isRefreshToken ? $request->refreshToken() : $request->token(), $isRefreshToken);
        }
        return $next($request);
    }
}
