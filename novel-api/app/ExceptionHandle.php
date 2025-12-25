<?php
namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use app\exception\BusinessException;

use think\Response;
use Throwable;

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
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        BusinessException::class,
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
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        //验证器异常
        if ($e instanceof ValidateException) {
            $msg = is_string($e->getError()) ? lang($e->getError()) : lang('参数校验失败');
            return json(['code'=>422,'msg'=>$msg]);
        }
        // 模型不存在异常
        if ($e instanceof ModelNotFoundException) {
            return json(['code'=>404,'msg'=>lang($e->getMessage())]);
        }
        // 数据不存在异常
        if ($e instanceof DataNotFoundException) {
            return json(['code'=>404,'msg'=>lang($e->getMessage())]);
        }
        // 添加自定义异常处理机制
        if ($e instanceof HttpException ) {
            return json(['code'=>$e->getStatusCode(),'msg'=>lang($e->getMessage())]);
        }
        // 业务异常
        if ($e instanceof BusinessException) {
            $status = method_exists($e, 'getHttpStatus') ? $e->getHttpStatus() : 400;
            return json(['code'=>$status,'msg'=>lang($e->getMessage()),'data'=>$e->getData()]);
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
