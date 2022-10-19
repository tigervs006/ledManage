<?php

namespace core\traits;

/**
 *
 * Class ErrorTrait
 * @package core\traits
 */
trait ErrorTrait
{
    /**
     * 错误信息
     * @var null|string
     */
    protected null|string $error;

    /**
     * 设置错误信息
     * @param string|null $error
     * @return bool
     */
    protected function setError(?string $error = null): bool
    {
        $this->error = $error ?: '未知错误';
        return false;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError(): string
    {
        $error = $this->error;
        $this->error = null;
        return $error;
    }
}
