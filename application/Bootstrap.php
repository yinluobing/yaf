<?php

use Yaf\Application;
use Yaf\Bootstrap_Abstract;
use Yaf\Dispatcher;
use Yaf\Registry;

/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Bootstrap_Abstract
{

    private $config;

    // 加载配置
    public function _initConfig()
    {
        // 获取配置
        $this->config = Application::app()->getConfig();

        // 注册配置
        Registry::set('config', $this->config);

        // 注册日志
        Registry::set('log', [
            // 默认日志记录通道
            'default'      => $this->config->log->type,
            // 日志记录级别
            'level'        => [],
            // 日志类型记录的通道 ['error'=>'email',...]
            'type_channel' => [],

            // 日志通道列表
            'channels'     => [
                'file' => [
                    // 日志记录方式
                    'type'        => 'File',
                    // 日志保存目录
                    'path'        => $this->config->runtime->log,
                    // 单文件日志写入
                    'single'      => false,
                    // 独立日志级别
                    'apart_level' => [],
                    // 最大日志文件数量
                    'max_files'   => 0,
                ],
                // 其它日志通道配置
            ],
        ]);

        // 注册缓存
        Registry::set('cache', [
            'default' => $this->config->cache->type,
            'stores'  => [
                // 文件缓存
                'file'  => [
                    // 驱动方式
                    'type' => 'file',
                    // 设置不同的缓存保存目录
                    'path' => $this->config->runtime->data->cache,
                ],
                // redis缓存
                'redis' => [
                    // 驱动方式
                    'type' => 'redis',
                    // 服务器地址
                    'host' => $this->config->redis->host,
                ],
            ],
        ]);

        // 关闭自动加载模板
        Dispatcher::getInstance()->autoRender(FALSE);
    }

    // 加载公共函数
    public function _initCommon()
    {
        \Yaf\Loader::import($this->config->application->directory . '/library/Function.php');
    }

    // 加载语言
    public function _initLang()
    {

    }

    // 是否显示错误提示
    public function _initError()
    {
        if (Registry::get('config')->application->debug) {
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
            'database_name' => $this->config->db->database,
            'server'        => $this->config->db->hostname,
            'username'      => $this->config->db->username,
            'password'      => $this->config->db->password,
            'prefix'        => $this->config->db->prefix,
            'logging'       => $this->config->db->log,
            'charset'       => 'utf8mb4'
        ]);
        // 注册db
        Registry::set('db', $db);
    }

    // 加载redis
    public function _initRedis()
    {
        $redis = new \Redis();
        $redis->connect($this->config->redis->host, $this->config->redis->port);
        Registry::set('redis', $redis);
    }

    //  加载插件
    public function _initPlugin(Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new runPlugin());
    }

    // 加载路由
    public function _initRoute(Dispatcher $dispatcher)
    {
        $router = $dispatcher->getInstance()->getRouter();
        $router->addConfig($this->config->routes);
    }

    // 加载视图
    public function _initView()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem('views', APP_PATH), [
            'cache' => $this->config->runtime->tpl,
        ]);
        Registry::set('twig', $twig);
    }
}
