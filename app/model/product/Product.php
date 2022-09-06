<?php
declare (strict_types = 1);
namespace app\model\product;

use core\basic\BaseModel;
use app\model\channel\Channel;
use think\model\relation\HasOne;

class Product extends BaseModel
{
    protected $pk = 'id';
    protected $jsonAssoc = true;
    protected $json = ['album', 'special'];

    /**
     * 关联商品详情
     * @return HasOne
     */
    public function detail(): HasOne
    {
        return $this->hasOne(ProductDetail::class, 'gid', 'id');
    }

    /**
     * 关联栏目模型
     * @return HasOne
     */
    public function channel(): HasOne
    {
        return $this->hasOne(Channel::class, 'id', 'pid')->field('id, cname, dirname, fullpath');
    }
}
