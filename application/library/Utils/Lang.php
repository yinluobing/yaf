<?php
/**
 * Created by PhpStorm.
 * User: yinluobing
 * Date: 2019/11/5
 * Time: 15:50
 */
declare (strict_types=1);

namespace Utils;

use Yaf\Request_Abstract;

/**
 * 多语言管理类
 * @package Utils
 */
class Lang
{
    private static $instance;
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        // 默认语言
        'default_lang'    => 'zh-cn',
        // 允许的语言列表
        'allow_lang_list' => [],
        // 是否使用Cookie记录
        'use_cookie'      => true,
        // 扩展语言包
        'extend_list'     => [],
        // 多语言cookie变量
        'cookie_var'      => 'think_lang',
        // 多语言自动侦测变量名
        'detect_var'      => 'lang',
        // Accept-Language转义为对应语言包名称
        'accept_language' => [
            'zh-hans-cn' => 'zh-cn',
        ],
        // 是否支持语言分组
        'allow_group'     => false,
    ];
    /**
     * 多语言信息
     * @var array
     */
    private $lang = [];
    /**
     * 当前语言
     * @var string
     */
    private $range = 'zh-cn';

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


    /**
     * 构造方法
     * @access public
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, array_change_key_case($config));
        $this->range = $this->config['default_lang'];
    }

    /**
     * 设置当前语言
     * @access public
     * @param string $lang 语言
     * @return void
     */
    public function setLangSet(string $lang): void
    {
        $this->range = $lang;
    }

    /**
     * 获取当前语言
     * @access public
     * @return string
     */
    public function getLangSet(): string
    {
        return $this->range;
    }

    /**
     * 获取默认语言
     * @access public
     * @return string
     */
    public function defaultLangSet()
    {
        return $this->config['default_lang'];
    }

    /**
     * 加载语言定义(不区分大小写)
     * @access public
     * @param string|array $file 语言文件
     * @param string $range 语言作用域
     * @return array
     */
    public function load($file, $range = ''): array
    {
        $range = $range ?: $this->range;
        if (!isset($this->lang[$range])) {
            $this->lang[$range] = [];
        }
        $lang = [];
        foreach ((array)$file as $_file) {
            if (is_file($_file)) {
                $result = $this->parse($_file);
                $lang = array_change_key_case($result) + $lang;
            }
        }
        if (!empty($lang)) {
            $this->lang[$range] = $lang + $this->lang[$range];
        }
        return $this->lang[$range];
    }

    /**
     * 解析语言文件
     * @access protected
     * @param string $file 语言文件名
     * @return array
     */
    protected function parse(string $file): array
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);
        switch ($type) {
            case 'php':
                $result = include $file;
                break;
            case 'yml':
            case 'yaml':
                if (function_exists('yaml_parse_file')) {
                    $result = yaml_parse_file($file);
                }
                break;
        }
        return isset($result) && is_array($result) ? $result : [];
    }

    /**
     * 判断是否存在语言定义(不区分大小写)
     * @access public
     * @param string|null $name 语言变量
     * @param string $range 语言作用域
     * @return bool
     */
    public function has(string $name, string $range = ''): bool
    {
        $range = $range ?: $this->range;
        if ($this->config['allow_group'] && strpos($name, '.')) {
            [$name1, $name2] = explode('.', $name, 2);
            return isset($this->lang[$range][strtolower($name1)][$name2]);
        }
        return isset($this->lang[$range][strtolower($name)]);
    }

    /**
     * 获取语言定义(不区分大小写)
     * @access public
     * @param string|null $name 语言变量
     * @param array $vars 变量替换
     * @param string $range 语言作用域
     * @return mixed
     */
    public function get(string $name = null, array $vars = [], string $range = '')
    {
        $range = $range ?: $this->range;
        // 空参数返回所有定义
        if (is_null($name)) {
            return $this->lang[$range] ?? [];
        }
        if ($this->config['allow_group'] && strpos($name, '.')) {
            [$name1, $name2] = explode('.', $name, 2);
            $value = $this->lang[$range][strtolower($name1)][$name2] ?? $name;
        } else {
            $value = $this->lang[$range][strtolower($name)] ?? $name;
        }
        // 变量解析
        if (!empty($vars) && is_array($vars)) {
            /**
             * Notes:
             * 为了检测的方便，数字索引的判断仅仅是参数数组的第一个元素的key为数字0
             * 数字索引采用的是系统的 sprintf 函数替换，用法请参考 sprintf 函数
             */
            if (key($vars) === 0) {
                // 数字索引解析
                array_unshift($vars, $value);
                $value = call_user_func_array('sprintf', $vars);
            } else {
                // 关联索引解析
                $replace = array_keys($vars);
                foreach ($replace as &$v) {
                    $v = "{:{$v}}";
                }
                $value = str_replace($replace, $vars, $value);
            }
        }
        return $value;
    }

    /**
     * 自动侦测设置获取语言选择
     * @access public
     * @param Request $request
     * @return string
     */
    public function detect(Request_Abstract $request): string
    {
        // 自动侦测设置获取语言选择
        $langSet = '';
        if ($request->get($this->config['detect_var'])) {
            // url中设置了语言变量
            $langSet = strtolower($request->get($this->config['detect_var']));
        } elseif ($request->getCookie($this->config['cookie_var'])) {
            // Cookie中设置了语言变量
            $langSet = strtolower($request->getCookie($this->config['cookie_var']));
        } elseif ($request->getServer('HTTP_ACCEPT_LANGUAGE')) {
            // 自动侦测浏览器语言
            $match = preg_match('/^([a-z\d\-]+)/i', $request->getServer('HTTP_ACCEPT_LANGUAGE'), $matches);
            if ($match) {
                $langSet = strtolower($matches[1]);
                if (isset($this->config['accept_language'][$langSet])) {
                    $langSet = $this->config['accept_language'][$langSet];
                }
            }
        }
        if (empty($this->config['allow_lang_list']) || in_array($langSet, $this->config['allow_lang_list'])) {
            // 合法的语言
            $this->range = $langSet;
        }
        return $this->range;
    }

    /**
     * 保存当前语言到Cookie
     * @access public
     * @return void
     */
    public function saveToCookie()
    {
        if ($this->config['use_cookie']) {
            setcookie($this->config['cookie_var'], $this->range);
        }
    }
}