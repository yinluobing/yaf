<?php

use Yaf\Controller_Abstract;
use Yaf\Registry;

/**
 * Created by PhpStorm.
 * User: icker
 * Date: 2017/9/4
 * Time: 下午1:53
 */
class BaseController extends Controller_Abstract
{
    protected $twig = null;//twig
    protected $db = null;//db
    protected $assign = null;//模板赋值
    protected $redis = null; //redis

    /**
     * init 初始化函数
     */
    public function init()
    {
        $loader = new \Twig_Loader_Filesystem('views', APP_PATH);
        $this->twig = new \Twig_Environment($loader, array(/* 'cache' => './compilation_cache', */
        ));

        // 初始化assign
        $this->assign = [
            'title'       => '网站标题',
            'keywords'    => '网站关键词',
            'description' => '网站描述',
        ];

        $this->db = Registry::get('db');
        $this->redis = Registry::get('redis');
        // SeasLog 日志设置
        // SeasLog::setBasePath('/data/log');
        // SeasLog::setLogger('kaoqin');
    }
}
