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

use app\dao\system\AttachDao;
use app\services\BaseServices;
use core\services\UploadService;

class AttachServices extends BaseServices
{
    public function __construct(AttachDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 批量删除文件
     * @author Kevin
     * @return void
     * @param array $attach
     * @createAt 2022/11/3 9:32
     */
    public function destroy(array $attach): void
    {
        array_walk($attach, function ($val) {
            /* 从存储中删除文件 */
            UploadService::init($val['storage'])->delete($val['path']);
            /* 从数据库删除记录 */
            $this->dao->delete($val['id'], 'id', true);
        });
    }
}
