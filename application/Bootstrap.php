<?php

/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{

    private $config;

    public function _initConfig()
    {
        // 把配置保存起来
        $this->config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->config);

        // 调用方法 Yaf_Registry::get('config')->application->upyun->bucketname
        // 关闭自动加载模板
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
    }

    // 是否显示错误提示
    public function _initError()
    {
        if (Yaf_Registry::get('config')->application->debug) {
            error_reporting(7);
        } else {
            error_reporting(0);
        }
    }

    // 载入数据库
    public function _initDatabase()
    {
        $db = new \Medoo\Medoo([
            'database_type' => 'mysql',
            'database_name' => $this->config->application->db->database,
            'server'        => $this->config->application->db->hostname,
            'username'      => $this->config->application->db->username,
            'password'      => $this->config->application->db->password,
            'prefix'        => $this->config->application->db->prefix,
            'logging'       => $this->config->application->db->log,
            'charset'       => 'utf8mb4'
        ]);
        // 注册db
        Yaf_Registry::set('db', $db);
    }

    // 载入redis
    public function _initRedis()
    {
        $redis = new \Redis();
        $redis->connect($this->config->application->redis->host, $this->config->application->redis->port);
        Yaf_Registry::set('redis', $redis);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        //注册一个插件
    }

    /**
     * @param Yaf_Dispatcher $dispatcher
     * @throws Yaf_Exception_TypeError
     */
    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        // 注册路由配置项
        $router = $dispatcher->getInstance()->getRouter();
        $test = new Yaf_Route_Rewrite(
            'test/',
            array(
                'modules'    => 'Index',
                'controller' => 'Index',
                'action'     => 'test'
            )
        );
        $router->addRoute('index_route', $test);
    }

    /*public function _initView()
    {
        //在这里注册自己的view控制器，例如smarty,twig
    }*/
}
