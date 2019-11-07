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
    protected $dbMaster = null;

    public function __construct()
    {
        $this->db = Registry::get('db');
        $this->dbMaster = Registry::get('dbMaster');
    }
}
