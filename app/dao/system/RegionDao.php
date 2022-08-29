<?php
declare (strict_types = 1);
namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\Region;

class RegionDao extends BaseDao
{
    protected function setModel(): string
    {
        return Region::class;
    }
    /**
     * 获取cid的最大值
     * @return mixed
     */
    public function getCityIdMax(): mixed
    {
        return $this->getModel()->max('cid');
    }
}
