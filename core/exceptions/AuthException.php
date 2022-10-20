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

namespace core\exceptions;

use Throwable;

/**
 * Class AuthException
 * @package core\exceptions
 */
class AuthException extends \RuntimeException
{
    public function __construct(array|string $message = "", int $code = 401, Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $code = $errInfo[0] ?? $code;
            $message = $errInfo[1] ?? 'Unauthorized';
        }
        parent::__construct($message, $code, $previous);
    }
}
