<?php
declare (strict_types = 1);
namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\Client;

class ClientDao extends BaseDao
{
    public function setModel(): string
    {
        return Client::class;
    }
}
