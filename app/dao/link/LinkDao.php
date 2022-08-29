<?php
declare (strict_types = 1);
namespace app\dao\link;

use app\dao\BaseDao;
use app\model\link\Link;

class LinkDao extends BaseDao
{
    protected function setModel(): string
    {
        return Link::class;
    }
}
