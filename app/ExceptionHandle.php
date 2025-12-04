<?php
namespace app;

use app\exception\BusinessException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class ExceptionHandle extends Handle
{
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ValidateException::class,
        BusinessException::class,
    ];

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        if ($e instanceof BusinessException) {
            return $this->buildJsonResponse($e->getMessage(), $e->getCode() ?: 1, $e->getData(), $e->getHttpStatus());
        }

        if ($e instanceof ValidateException) {
            $errors  = $e->getError();
            $message = is_array($errors) ? reset($errors) : $errors;

            return $this->buildJsonResponse($message ?: '参数校验失败', 422, is_array($errors) ? $errors : [], 200);
        }

        if ($e instanceof HttpException) {
            $status  = $e->getStatusCode();
            $message = $e->getMessage() ?: '请求错误';

            return $this->buildJsonResponse($message, $status, [], $status);
        }

        if ($this->app->isDebug()) {
            $debugData = $this->convertExceptionToArray($e);

            return $this->buildJsonResponse($e->getMessage(), $e->getCode() ?: 500, $debugData, 500);
        }

        return $this->buildJsonResponse('服务器开小差，请稍后再试', 500, [], 500);
    }

    protected function buildJsonResponse(string $message, int $code, $data = [], int $httpStatus = 200): Response
    {
        $response = json_error($message, $code, $data);

        return $response->code($httpStatus);
    }
}
