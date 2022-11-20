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
use app\dao\develop\ConfigListDao;

/**
 * class ConfigListServices
 * @createAt 2022/11/18 23:59
 * @package app\services\develop
 */
class ConfigListServices extends BaseServices
{
    public function __construct(ConfigListDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存编辑
     * @return void
     * @author Kevin
     * @param array $data
     * @param string $message
     * @createAt 2022/11/19 0:32
     */
    public function saveConfigList(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); /* 释放$data中的id */
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '配置失败');
        });
    }
}
