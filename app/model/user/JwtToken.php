<?php
declare (strict_types = 1);
namespace app\model\user;

use core\basic\BaseModel;

class JwtToken extends BaseModel
{
    protected $pk = 'uid';
    protected $name = 'authorization';
}
