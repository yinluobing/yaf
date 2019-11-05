<?php

use Yaf\Registry;

/**
 * @name runPlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author root
 */
class runPlugin extends Yaf\Plugin_Abstract
{

    /**
     * @param \Yaf\Request\Http $request
     * @param \Yaf\Response_Abstract $response
     * @return bool|void
     */
    public function routerStartup(\Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
        // 记录日志 @todo 记录请求日志
        $param['request'] = $request->getRequest();
        $param['cookie'] = $request->getCookie();
        $param['header'] = getHeader();
        if (Registry::get('config')->application->debug) {
            trace($param, 'info');
        }
    }

    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    /**
     * @param \Yaf\Request_Abstract $request
     * @param \Yaf\Response_Abstract $response
     * @return bool|void
     */
    public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
//        $config = \Yaf\Registry::get('config');
        // 记录日志 @todo sql日志、以及用户日志
        /** @var \Medoo\Medoo $db */
        $db = Registry::get('db');
        $param = $db->log();
        if (Registry::get('config')->application->debug) {
            trace($param, 'sql');
        }
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
//        echo 'dispatchLoopShutdown';
    }
}
