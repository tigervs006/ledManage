<?php
declare (strict_types = 1);
namespace app\services\system;

use app\services\BaseServices;
use app\dao\system\SystemLogDao;

class SystemLogServices extends BaseServices
{
    public function __construct(SystemLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 记录操作日志
     * @return void
     * @param array $token token信息
     * @param int|null $level 日志级别
     * @param string|null $action 操作描述
     */
    public function actionLogRecord(array $token, ?int $level = 3, ?string $action = null): void
    {
        $app = app('http')->getName();
        $method = request()->rule()->getMethod();
        $options = request()->rule()->getOption();
        /* 只记录post方式的日志 */
        'post' === $method && $this->dao->saveOne([
            'level' => $level,
            'uid' => $token['uid'],
            'gid' => $token['gid'],
            'ipaddress' => ip2long(request()->ip()),
            'action' => $action ?: $options['route_name'],
            'path' => $app . '/' . request()->rule()->getRule(),
        ]);
    }
}
