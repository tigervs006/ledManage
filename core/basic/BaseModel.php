<?php
declare (strict_types = 1);
namespace core\basic;

use think\Model;
use core\traits\ModelTrait;
use think\model\concern\SoftDelete;

class BaseModel extends Model
{
    use ModelTrait;
    //启用软删除
    use SoftDelete;

    protected string $deleteTime = 'delete_time';
}
