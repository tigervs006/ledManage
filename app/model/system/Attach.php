<?php
declare (strict_types = 1);
namespace app\model\system;

use core\basic\BaseModel;
use think\model\relation\HasOne;

class Attach extends BaseModel
{
    /**
     * 关联分类模型
     * @return HasOne
     */
    public function attachCate(): HasOne
    {
        return $this->hasOne(AttachCate::class, 'id', 'pid');
    }
}
