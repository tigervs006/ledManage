<?php
namespace app;

use core\utils\JwtAuth;
use core\exceptions\AuthException;

/**
 * Class Request
 * @package app
 */
class Request extends \think\Request
{
    /**
     * 获取token
     * @return string
     * @param string|null $name
     */
    public function token(?string $name = null): string
    {
        $token = $this->header($name ?: 'Authorization');
        !$token && throw new AuthException('Token is missing or incorrect');
        return $token;
    }
    /**
     * 解析token
     * @return array
     */
    public function tokenInfo(): array
    {
        $jwtServices = app()->make(JwtAuth::class);
        return $jwtServices->parseToken($this->token());
    }
}
