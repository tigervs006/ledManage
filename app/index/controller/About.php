<?php
declare (strict_types = 1);
namespace app\index\controller;

use core\basic\BaseController;

class About extends BaseController
{
    final public function index(): string
    {
        return $this->view::fetch('../about/index');
    }
}
