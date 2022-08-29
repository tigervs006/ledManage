<?php
declare (strict_types = 1);
namespace app\console\controller\system;

use think\response\Json;
use core\basic\BaseController;
use app\services\system\ConfigServices;

class ConfigController extends BaseController
{
    private ConfigServices $services;

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(ConfigServices::class);
    }

    /**
     * 更新配置项
     * @return Json
     */
    final public function save(): Json
    {
        $data = $this->request->post();
        $this->services->updateConfig($data);
        return $this->json->successful('更新配置成功');
    }

    /**
     * 获取配置列表
     * @return Json
     */
    final public function list(): Json
    {
        $data = $this->services->getData($this->request->get(['type']));
        $list = array_column($data->toArray(), null, 'name');
        return $data->isEmpty() ? $this->json->fail() : $this->json->successful(['list' => $list]);
    }
}
