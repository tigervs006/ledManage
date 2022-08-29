<?php
declare (strict_types = 1);
namespace app\dao\channel;

use app\dao\BaseDao;
use app\model\channel\Module;

class ModuleDao extends BaseDao
{
    protected function setModel(): string
    {
        return Module::class;
    }
}
