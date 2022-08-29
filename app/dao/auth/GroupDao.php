<?php
declare (strict_types = 1);
namespace app\dao\auth;

use app\dao\BaseDao;
use app\model\auth\GroupModel;

class GroupDao extends BaseDao
{

    protected function setModel(): string
    {
        return GroupModel::class;
    }
}
