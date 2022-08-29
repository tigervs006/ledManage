<?php
declare (strict_types = 1);
namespace app\index\controller;

use core\basic\BaseController;

class Cases extends BaseController
{
    final public function index(): string
    {
        return $this->view::fetch('../case/index');
    }

    final public function detail(): string
    {
        return $this->view::fetch('../case/detail');
    }
}