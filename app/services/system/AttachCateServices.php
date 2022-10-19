<?php
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
     * 新增/编辑
     * @return void
     * @param array $data
     * @param string $message
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
