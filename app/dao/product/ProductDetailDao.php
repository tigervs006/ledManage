<?php
declare (strict_types = 1);
namespace app\dao\product;

use app\dao\BaseDao;
use app\model\product\ProductDetail;

class ProductDetailDao extends BaseDao
{
    protected function setModel(): string
    {
        return ProductDetail::class;
    }
}
