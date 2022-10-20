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
namespace app\services\link;

use app\dao\link\LinkDao;
use app\services\BaseServices;
use core\exceptions\ApiException;

class LinkServices extends BaseServices
{
    public function __construct(LinkDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 新增/编辑链接
     * @return void
     * @param array $data 数据
     * @param string $message 新增/编辑
     */
    public function saveLink(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); /* 释放$data中的id */
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '友情链接失败');
        });
    }
}
