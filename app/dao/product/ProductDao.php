<?php
declare (strict_types = 1);
namespace app\dao\product;

use app\dao\BaseDao;
use app\model\product\Product;

class ProductDao extends BaseDao
{
    protected function setModel(): string
    {
        return Product::class;
    }
}
