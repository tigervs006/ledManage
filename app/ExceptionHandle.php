<?php

namespace app;

use Throwable;
use think\Response;
use think\facade\Log;
use think\exception\Handle;
use think\exception\HttpException;
use think\db\exception\DbException;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        ValidateException::class,
        HttpResponseException::class,
        DataNotFoundException::class,
        ModelNotFoundException::class
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        $data = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $this->getCode($exception),
            'message' => $this->getMessage($exception),
        ];

        //日志内容
        $log = [
            'visitor',
            request()->ip(),
            ceil(msectime() - (request()->time(true) * 1000)),
            strtoupper(request()->rule()->getMethod()),
            request()->baseUrl(),
            json_encode(request()->param(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),

        ];
        Log::write(implode("|", $log), "error");
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof DbException) {
            return app('json')->fail($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]);
        } else {
            return app('json')->fail($e->getMessage(), 400, config('index.app_debug') ? [
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
