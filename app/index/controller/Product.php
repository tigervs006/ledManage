<?php
declare (strict_types = 1);
namespace app\index\controller;

use core\basic\BaseController;

class Product extends BaseController
{
    final public function index(): string
    {
         return $this->view::fetch('../product/index');
    }

    final public function list($tid): string
    {
        return $this->view::fetch('../product/detail');
    }
}
