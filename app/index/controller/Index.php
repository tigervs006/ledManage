<?php
declare (strict_types = 1);
namespace app\index\controller;

use think\response\Json;
use core\basic\BaseController;
use app\services\system\RegionServices;

class Index extends BaseController
{
    /**
     * @var RegionServices
     */
    private RegionServices $regionServices;

    /**
     * @var string 区域字段
     */
    private string $region_field = 'id, cid, pid, code, name';

    protected function initialize()
    {
        parent::initialize();
        $this->regionServices = $this->app->make(RegionServices::class);
    }

    /**
     * 文章列表
     * @return string
     */
    final public function index(): string
    {
        return $this->view::fetch('../template/index.html');
    }

    /**
     * 行政区域
     * @return Json
     */
    final public function region(): Json
    {
        $pid = $this->request->get('pid/d', 0);
        $list = $this->regionServices->getChildCity($pid, $this->region_field);
        return $this->json->successful(compact('list'));
    }
}
