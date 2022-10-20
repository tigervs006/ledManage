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

declare (strict_types = 1);
namespace app\services\user;

use app\dao\user\ClientDao;
use app\services\BaseServices;
use core\exceptions\ApiException;

class ClientServices extends BaseServices
{
    public function __construct(ClientDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 编辑/新增客户
     * @return mixed
     * @param array $data data
     * @param string $message message
     */
    public function saveClient(array $data, string $message): mixed
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); // 释放$data中的id
        return $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message);
        });
    }
}
