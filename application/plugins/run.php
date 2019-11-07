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
        /** @var \Medoo\Medoo $db */
        $db = Registry::get('db');
        $dbMaster = Registry::get('dbMaster');
        $param = $db->log();
        $paramMaster = $dbMaster->log();
        if (Registry::get('config')->application->debug) {
            trace($param, 'sql');
            trace($paramMaster, 'sql');
        }
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
//        echo 'dispatchLoopShutdown';
    }
}
