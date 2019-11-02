<?php

/**
 * Created by PhpStorm.
 * User: icker
 * Date: 2017/10/13
 * Time: 上午9:49
 */
class ErrorController extends BaseController
{
    /**
     * 错误信息输出
     * @param $exception
     */
    public function errorAction($exception)
    {
        switch ($exception->getCode()) {
            case YAF\ERR\NOTFOUND\MODULE:
            case YAF\ERR\NOTFOUND\CONTROLLER:
            case YAF\ERR\NOTFOUND\ACTION:
            case YAF\ERR\NOTFOUND\VIEW:
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
                break;
            default:
                header('HTTP/1.0 500 Internal Server Error');
                break;
        }
        if (is_string($exception))
            echo $exception;
        else
            echo $exception->getMessage();
    }
}
