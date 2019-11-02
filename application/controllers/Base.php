<?php

/**
 * Created by PhpStorm.
 * User: icker
 * Date: 2017/9/4
 * Time: 下午1:53
 */
class BaseController extends Yaf_Controller_Abstract
{
    protected $twig = null;//twig
    protected $db = null;//db
    protected $assign = null;

    /**
     * init 初始化函数
     */
    public function init()
    {
        $loader = new \Twig_Loader_Filesystem('views', APP_PATH);
        $this->twig = new \Twig_Environment($loader, array(/* 'cache' => './compilation_cache', */
        ));

        $this->db = Yaf_Registry::get('db');

        // SeasLog 日志设置
        // SeasLog::setBasePath('/data/log');
        // SeasLog::setLogger('kaoqin');
    }
}
