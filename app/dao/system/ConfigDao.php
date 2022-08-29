<?php
declare (strict_types = 1);
namespace app\dao\system;

use app\dao\BaseDao;
use app\model\system\Config;
use core\exceptions\ApiException;

class ConfigDao extends BaseDao
{
    protected function setModel(): string
    {
        return Config::class;
    }

    public function batchUpdateAll(array $data): \think\Collection
    {
        try {
            return $this->getModel()->saveAll($data);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }
}
