<?php
declare (strict_types = 1);
namespace app\console\controller\system;

use think\response\Json;
use core\basic\BaseController;
use app\services\system\SystemLogServices;

class SystemLogsController extends BaseController
{
    /**
     * 操作日志服务
     * @var SystemLogServices
     */
    private SystemLogServices $services;

    public function initialize()
    {
        parent::initialize();
        $this->services = $this->app->make(SystemLogServices::class);
    }

    /**
     * 获取操作日志
     * @return Json
     */
    final public function list(): Json
    {
        $betweenTime = [];
        $map = $this->request->only(['uid', 'gid', 'level']);
        /* 获取时间范围 */
        $dateRange = $this->request->only(['dateRange'], 'get', 'trim');
        /* 组装按时间段搜索条件  */
        $dateRange && $betweenTime = ['create_time', $dateRange['dateRange'][0], $dateRange['dateRange'][1]];
        $list = $this->services->getList($this->current, $this->pageSize, $map ?: null, '*', $this->order, $betweenTime);
        if ($list->isEmpty()) {
            return $this->json->fail();
        } else {
            $total = $this->services->getCount($map ?: null, null, $betweenTime);
            return $this->json->successful(compact('list', 'total'));
        }
    }
}
