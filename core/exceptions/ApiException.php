<?php

namespace core\exceptions;

/**
 * API应用错误信息
 * Class ApiException
 * @package core\exceptions
 */
class ApiException extends \RuntimeException
{
    public function __construct(array|string $message, int $code = 400, \Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $code = $errInfo[0] ?? $code;
            $message = $errInfo[1] ?? 'Unknow Error';
        }

        parent::__construct($message, $code, $previous);
    }
}
