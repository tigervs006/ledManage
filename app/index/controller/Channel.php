<?php
declare (strict_types = 1);
namespace app\index\controller;

use think\response\Json;
use core\basic\BaseController;
use app\services\channel\ChannelServices;

class Channel extends BaseController
{
    /**
     * @var ChannelServices
     */
    private ChannelServices $services;

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ChannelServices::class);
    }

    /**
     * 查找子栏目
     * @return Json
     * @param int $id
     */
    public function list(int $id): Json
    {
        $field = 'id, pid, name, cname, fullpath';
        $list = $this->services->getData(array_merge(['pid' => $id], $this->status), $this->order, $field);
        return $list->isEmpty() ? $this->json->fail() : $this->json->successful(compact('list'));
    }
}
