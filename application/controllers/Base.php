<?php

use Yaf\Controller_Abstract;
use Yaf\Registry;

/**
 * Created by PhpStorm.
 * User: icker
 * Date: 2017/9/4
 * Time: 下午1:53
 * @property \Medoo\MedooManager $db
 * @property Redis $redis
 * @property Twig_Environment $twig
 */
class BaseController extends Controller_Abstract
{
    protected $twig   = null;// twig
    protected $db     = null;// db
    protected $assign = null;// 模板赋值
    protected $redis  = null;// redis
    private   $config = null;// config

    /**
     * init 初始化函数
     */
    public function init()
    {
        $this->config = Registry::get('config');
        $this->twig = Registry::get('twig');
        $this->db = Registry::get('db');
        $this->redis = Registry::get('redis');

        // 初始化assign
        $this->assign = [
            'title'       => '网站标题',
            'keywords'    => '网站关键词',
            'description' => '网站描述',
        ];
    }
}
