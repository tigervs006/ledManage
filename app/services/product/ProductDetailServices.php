<?php
/*
 * +----------------------------------------------------------------------------------
 * | https://www.tigervs.com
 * +----------------------------------------------------------------------------------
 * | Email: Kevin@tigervs.com
 * +----------------------------------------------------------------------------------
 * | Copyright (c) Shenzhen Tiger Technology Co., Ltd. 2018~2022. All rights reserved.
 * +----------------------------------------------------------------------------------
 */

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
