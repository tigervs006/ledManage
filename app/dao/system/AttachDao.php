<?php
declare (strict_types = 1);
namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\Attach;

class AttachDao extends BaseDao
{

    /**
     * @inheritDoc
     */
    protected function setModel(): string
    {
        return Attach::class;
    }
}
