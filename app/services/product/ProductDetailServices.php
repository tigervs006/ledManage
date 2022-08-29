<?php
declare (strict_types = 1);
namespace app\services\product;

use app\services\BaseServices;
use app\dao\product\ProductDetailDao;

class ProductDetailServices extends BaseServices
{
    public function __construct(ProductDetailDao $dao)
    {
        $this->dao = $dao;
    }
}
