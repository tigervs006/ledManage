<?php
declare (strict_types = 1);
namespace app\model\channel;

use core\basic\BaseModel;
use think\model\relation\HasOne;

class Channel extends BaseModel
{
    /**
     * 关联栏目模型
     * @return HasOne
     */
    public function module(): HasOne
    {
        return $this->hasOne(Module::class, 'id', 'nid');
    }
}
