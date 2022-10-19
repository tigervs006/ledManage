<?php
declare (strict_types = 1);
namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\AttachCate;

class AttachCateDao extends BaseDao
{

    /**
     * @inheritDoc
     */
    protected function setModel(): string
    {
        return AttachCate::class;
    }
}
