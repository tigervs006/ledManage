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
     */
    public function token(): string
    {
        $access_token = $this->header('Authorization');
        !$access_token && throw new AuthException('Token is missing or incorrect');
        return $access_token;
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

    /**
     * 获取refresh_token
     * @return string
     */
    public function refreshToken(): string
    {
        $refresh_token = $this->post('refresh_token/s');
        !$refresh_token && throw new AuthException('RefreshToken is missing or incorrect');
        return $refresh_token;
    }

    /**
     * 是否为refresh_token
     * @return bool
     */
    public function isRefreshToken(): bool
    {
        return $this->rule()->getRule() === 'public/refresh_token';
    }

    /**
     * 解析refresh_token
     * @return array
     */
    public function refreshTokenInfo(): array
    {
        $jwtServices = app()->make(JwtAuth::class);
        return $jwtServices->parseToken($this->refreshToken());
    }
}
