<?php
declare (strict_types = 1);
namespace app\services\system;

use app\dao\system\AttachDao;
use app\services\BaseServices;

class AttachServices extends BaseServices
{
    public function __construct(AttachDao $dao)
    {
        $this->dao = $dao;
    }
}
