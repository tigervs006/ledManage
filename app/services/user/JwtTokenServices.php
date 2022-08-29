<?php
declare (strict_types = 1);
namespace app\services\user;

use app\dao\user\JwtTokenDao;
use app\services\BaseServices;

class JwtTokenServices extends BaseServices
{
    public function __construct(JwtTokenDao $dao)
    {
        $this->dao = $dao;
    }
}
