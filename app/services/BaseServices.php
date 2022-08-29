<?php
declare (strict_types = 1);
namespace app\services;

use think\facade\Db;
use core\traits\ServicesTrait;

/**
 * Class BaseServices
 * @package app\services
 */
abstract class BaseServices
{
    use ServicesTrait;

    /**
     * 模型注入
     * @var object
     */
    protected object $dao;

    /**
     * 默认状态
     * @var array|int[]
     */
    protected array $status = ['status' => 1];

    /**
     * 数据库事务操作
     * @return mixed
     * @param bool $isTran
     * @param callable $closure
     */
    public function transaction(callable $closure, bool $isTran = true): mixed
    {
        return $isTran ? Db::transaction($closure) : $closure();
    }

    /**
     * hash散列加密
     * @return string
     * @param string $password
     */
    public function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @return mixed
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}
