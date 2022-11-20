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

declare (strict_types=1);
namespace app\services\develop;

use app\services\BaseServices;
use core\exceptions\ApiException;
use app\dao\develop\FieldsGroupDataDao;

/**
 * class FieldsGroupDataServices
 * @createAt 2022/10/30 20:31
 * @package app\services\develop
 */
class FieldsGroupDataServices extends BaseServices
{
    public function __construct(FieldsGroupDataDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return void
     * @author Kevin
     * @param array $data
     * @param string $message
     * @createAt 2022/11/15 0:18
     */
    public function saveGroupDataList(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); /* 释放$data中的id */
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '组合数据列表失败');
        });
    }
}
