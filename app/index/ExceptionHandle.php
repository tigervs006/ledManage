<?php
namespace app\index;

use Throwable;
use think\Request;
use think\Response;
use think\exception\Handle;
use think\exception\HttpException;
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
     * @var array
     * 不需要记录信息（日志）的异常类列表
     */
    protected $ignoreReport = [
        HttpException::class,
        ValidateException::class,
        HttpResponseException::class,
        DataNotFoundException::class,
        ModelNotFoundException::class,
    ];

    /**
     * 记录异常信息
     *
     * @return void
     * @access public
     * @param  Throwable $exception
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @return Response
     * @param Throwable $e
     * @param Request   $request
     */
    public function render($request, Throwable $e): Response
    {
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
