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
namespace app\services\system;

use app\services\BaseServices;
use app\dao\system\AttachCateDao;
use core\exceptions\ApiException;

class AttachCateServices extends BaseServices
{
    public function __construct(AttachCateDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取上传规则
     * @return null|array
     * @author Kevin
     * @param int $id
     * @createAt 2022/10/27 16:16
     */
    public function verify(int $id): null|array
    {
        $result = $this->dao->value(['id' => $id], 'config');
        $rules = [
            'filesize' => isset($result['size']) ? $result['size'] * 1024 * 1024 : null,
            'fileExt'   => $result['fileExt'] ?? null,
            'fileMime'  => $result['fileMime'] ?? null,
        ];
        $validator = array_filter($rules);
        return $validator ?: null;
    }

    /**
     * @return void
     * @author Kevin
     * @param array $data
     * @param string $message
     * @createAt 2022/10/27 16:57
     */
    public function saveCate(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); // 释放$data中的id
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '目录分类失败');
        });
    }
}
