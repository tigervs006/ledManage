<?php
declare (strict_types = 1);
namespace app\console;

use Throwable;
use app\Request;
use think\Response;
use think\facade\Log;
use think\facade\Config;
use think\exception\Handle;
use core\exceptions\ApiException;
use core\exceptions\AuthException;
use think\db\exception\DbException;
use core\exceptions\UploadException;
use think\exception\ValidateException;
use app\services\system\SystemLogServices;

class ExceptionHandle extends Handle
{
    /**
     * 记录异常信息
     * @return void
     * @param Throwable $exception
     */
    public function report(Throwable $exception): void
    {
        /* AuthTokenMiddleware class method */
        $tokenInfo = request()->parseTokenInfo;

        $data = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $this->getCode($exception),
            'message' => $this->getMessage($exception),
        ];

        /* 日志内容 */
        $log = [
            $tokenInfo ? $tokenInfo['aud'] : 'visitor',
            request()->ip(),
            ceil(msectime() - (request()->time(true) * 1000)) . 'ms',
            strtoupper(request()->rule()->getMethod()),
            app('http')->getName() . '/' . request()->rule()->getRule(),
            json_encode(request()->param(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),

        ];

        /* 把日志写入到文件 */
        Log::write(implode("|", $log), "error");

        if ($tokenInfo && Config::get('index.record_action_log')) {
            /* 把日志写入到数据库 */
            $logServices = $this->app->make(SystemLogServices::class);
            $logServices->actionLogRecord($tokenInfo, 1, $this->getMessage($exception));
        }
    }

    /**
     * Render an exception into an HTTP response.
     * @return Response
     * @param Throwable $e
     * @param Request $request
     */
    public function render($request, Throwable $e): Response
    {
        /* 添加自定义异常处理机制 */
        if ($e instanceof DbException) {
            return app('json')->fail($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]);
        } else if ($e instanceof AuthException || $e instanceof ApiException || $e instanceof UploadException || $e instanceof ValidateException) {
            return app('json')->fail($e->getMessage(), $e->getCode() ?: 400);
        } else {
            return app('json')->fail($e->getMessage(), $e->getCode() ?: 400, Config::get('index.app_debug') ? [
                'file' => $e->getFile(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
                'message' => $e->getMessage(),
                'previous' => $e->getPrevious()
            ] : []);
        }
    }
}
