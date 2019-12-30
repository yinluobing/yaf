<?php

use Yaf\Registry;

/**
 * Created by PhpStorm.
 * User: icker
 * Date: 2017/10/12
 * Time: 下午2:13
 */
class BaseModel
{
    protected $db = null;

    public function __construct()
    {
        $this->db = Registry::get('db');
    }
}
