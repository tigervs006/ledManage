<?php
declare (strict_types = 1);
namespace app\services\user;

use app\dao\user\UserDao;
use app\services\BaseServices;
use core\exceptions\ApiException;

class UserServices extends BaseServices
{
    public function __construct(UserDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 编辑/新增用户
     * @return void
     * @param array $data data
     * @param string $message message
     */
    public function saveUser(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); // 释放$data中的id
        $this->transaction(function () use ($id, $data, $message) {
            /* hash散列加密 */
            isset($data['password']) && $data['password'] = $this->passwordHash($data['password']);
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '用户失败');
        });
    }
}
