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
use app\dao\develop\ConfigCateDao;

/**
 * class ConfigCateServices
 * @createAt 2022/11/16 12:07
 * @package app\services\develop
 */
class ConfigCateServices extends BaseServices
{
    public function __construct(ConfigCateDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存配置分类
     * @return void
     * @author Kevin
     * @param array $data
     * @param string $message
     * @createAt 2022/11/16 15:02
     */
    public function saveConfigCate(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); /* 释放$data中的id */
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '配置分类失败');
        });
    }
}
