<?php

namespace Medoo;

/**
 * Created by PhpStorm.
 * User: yinluobing
 * Date: 2019/11/11
 * Time: 11:11
 */

/**
 * Class MedooManager
 * @package Medoo
 * @property \Medoo\Medoo $master
 * @property \Medoo\Medoo $slave
 */
class MedooManager
{
    private static $instance;
    private static $master;
    private static $slave;

    /**
     * MedooManager constructor.
     * @param $options
     */
    public function __construct($options)
    {
        $this->master = Medoo::instance($options['master']);
        $this->slave = Medoo::instance($options['slave']);
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    public function select($table, $join, $columns = null, $where = null)
    {
        $this->slave->select($table, $join, $columns, $where);
    }

    public function insert($table, $datas)
    {
        $this->master->insert($table, $datas);
    }

    public function update($table, $data, $where = null)
    {
        $this->master->update($table, $data, $where);
    }

    public function delete($table, $where)
    {
        $this->master->delete($table, $where);
    }

    public function replace($table, $columns, $where = null)
    {
        $this->master->replace($table, $columns, $where);
    }

    public function __get($name)
    {
        return $this->$name;
    }
}