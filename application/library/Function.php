<?php

use think\facade\Cache;
use think\facade\Log;
use Yaf\Dispatcher;
use Yaf\Registry;

if (!function_exists('json')) {
    /**
     * app接口返回json
     * @param int $code
     * @param null $msg
     * @param array $data
     */
    function json($code = 1, $msg = null, $data = [])
    {
        header('Content-type: application/json');
        $json = [
            'code' => intval($code),
            'msg'  => $msg ? $msg : '操作成功',
            'data' => $data
        ];
        die(json_encode($json));
    }
}

if (!function_exists('validateSign')) {
    /**
     * 签名验证 @todo 待重构
     */
    function validateSign()
    {

    }
}

if (!function_exists('trace')) {
    /**
     * 记录日志信息
     * @param mixed $log log信息 支持字符串和数组
     * @param string $level 日志级别
     * @return array|void
     */
    function trace($log, string $level = 'log')
    {
        // 初始化配置
        Log::init(Registry::get('log'));
        Log::record($log, $level);
        Log::save();
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param string $name 缓存名称
     * @param mixed $value 缓存值
     * @param mixed $options 缓存参数
     * @param string $tag 缓存标签
     * @return mixed
     */
    function cache(string $name = null, $value = '', $options = null, $tag = null)
    {
        // 初始化配置
        Cache::config(Registry::get('cache'));
        if (is_null($name)) {
            return Cache::instance();
        }
        if ('' === $value) {
            // 获取缓存
            try {
                return 0 === strpos($name, '?') ? Cache::has(substr($name, 1)) : Cache::get($name);
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            }
        } elseif (is_null($value)) {
            // 删除缓存
            try {
                return Cache::delete($name);
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            }
        }
        // 缓存数据
        if (is_array($options)) {
            $expire = $options['expire'] ?? null; //修复查询缓存无法设置过期时间
        } else {
            $expire = $options;
        }
        if (is_null($tag)) {
            try {
                return Cache::set($name, $value, $expire);
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            }
        } else {
            try {
                return Cache::tag($tag)->set($name, $value, $expire);
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
            }
        }
    }
}

if (!function_exists('lang')) {
    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function lang(string $name, array $vars = [], string $lang = '')
    {
        // @todo 获取语言变量
    }
}

if (!function_exists('checkParams')) {
    /**
     * 检测多个参数是否完整并且不为空值
     * @param $params
     * @param string $type GET,POST
     */
    function checkParams($params, $method = 'GET')
    {
        switch ($method) {
            case 'GET':
                $method = $_GET;
                break;
            case 'POST':
                $method = $_POST;
                break;
        }
        foreach ($params as $param) {
            if (empty(filter($method[$param]))) {
                json(101, $param . " 参数不能为空");
            }
        }
    }
}

if (!function_exists('randStr')) {
    /**
     * 产生随机字串，可用来自动生成密码
     * 默认长度6位 字母和数字混合 支持中文
     * @param integer $len 长度
     * @param string $type 字串类型
     * 0 字母 1 数字 2 大写字母 3 小写字母 4 中文 其它 混合
     * @param string $addChars 额外字符
     * @return string
     */
    function randStr($len = 6, $type = '', $addChars = '')
    {
        return \Utils\Strs::randString($len, $type, $addChars);
    }
}

if (!function_exists('upBase64')) {
    /**
     * 处理base64编码的图片上传
     * @param $base64
     * @param $subdir
     * @param string $pre
     * @return bool|string
     */
    function upBase64($base64, $subdir, $pre = '')
    {
        $base64_image = str_replace(' ', '+', $base64);
        //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
            //匹配成功
            if ($result[2] == 'jpeg') {
                $image_name = $pre . uniqid() . '.jpg';
                //纯粹是看jpeg不爽才替换的
            } else {
                $image_name = $pre . uniqid() . '.' . $result[2];
            }
            $image_file = $subdir . $image_name;
            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))) {
                return $image_name;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

if (!function_exists('utf8_to_unicode_str')) {
    /**
     * utf8转unicode字符串
     * @param $utf8
     * @return string
     */
    function utf8_to_unicode_str($utf8)
    {
        $return = '';

        for ($i = 0; $i < mb_strlen($utf8); $i++) {

            $char = mb_substr($utf8, $i, 1);

            // 3字节是汉字，不转换，4字节才是 emoji
            if (strlen($char) > 3) {
                $char = trim(json_encode($char), '"');
            }
            $return .= $char;
        }
        return $return;
    }
}

if (!function_exists('filter')) {
    /**
     * 使用反斜线引用数据 防注入 前端xss过滤 (使用pdo, 省略addslashes过滤)
     * @param $string
     * @param int $force
     * @param string $allow
     * @return array|string
     */
    function filter($string, $force = 1, $allow = '')
    {
        if ($force) {
            if (is_array($string)) {
                foreach ($string as $key => $val) {
                    $string[$key] = filter($val, $force, $allow);
                }
            } else {
                $string = removeXss($string, $allow);
                $string = addslashes($string);
            }
        }
        return $string;
    }
}


if (function_exists('removeXss')) {
    /**
     * 移除HTML中的危险代码，如iframe和script
     * @param string $content
     * @param string $allow
     * @return string
     */
    function removeXss($content, $allow = '')
    {
        $danger = 'javascript,vbscript,expression,applet,meta,xml,blink,link,style,script,embed,object,iframe,frame,frameset,ilayer,layer,bgsound,title,base,eval';
        $event = 'onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|' .
            'onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|' .
            'oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragend|ondragenter|ondragleave|' .
            'ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterchange|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|' .
            'onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmouseout|onmouseover|onmouseup|' .
            'onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|' .
            'onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload';

        if (!empty($allow)) {
            $allows = explode(',', $allow);
            $danger = str_replace($allow, '', $danger);
        }
        $danger = str_replace(',', '|', $danger);
        //替换所有危险标签
        $content = preg_replace("/<\s*({$danger})[^>]*>[^<]*(<\s*\/\s*\\1\s*>)?/is", '', $content);
        //替换所有危险的JS事件
        $content = preg_replace("/<([^>]*)({$event})\s*\=([^>]*)>/is", "<\\1 \\3>", $content);
        return $content;
    }
}
if (!function_exists('session')) {
    /**
     * 获取或设置 session
     * @param $key
     * @param $value
     */
    function session($key, $value)
    {
        if ($value) {
            \Yaf\Session::getInstance()->set($key, $value);
        } else {
            \Yaf\Session::getInstance()->get($key);
        }
    }
}

if (!function_exists('cookie')) {
    /**
     * 获取或设置 cookie
     * @param $key
     * @param $value
     * @param float|int $exp
     * @param null $domain
     * @return mixed
     */
    function cookie($key, $value = '', $exp = 3600 * 24 * 30, $domain = null)
    {
        $config = Registry::get('config');
        $pre = $config['cookie']['pre'] ? $config['cookie']['pre'] : '';
        $key = $pre . $key;
        if (is_null($value)) {
            setcookie($key, " ", strtotime('0000-00-00 00:00:00'));
        } else if ('' === $value) {
            $value = unserialize(base64_decode(isset($_COOKIE[$key]) ? $_COOKIE[$key] : $value));
        } else {
            setcookie($key, base64_encode(serialize($value)), time() + $exp, '/', $domain);
        }
        return $value;
    }
}

if (!function_exists('hash')) {
    /**
     * 加密方式
     * @param $password
     * @return string
     */
    function hash($password)
    {
        return md5($password);
    }
}

if (!function_exists('upload')) {
    /**
     * 图片上传接口
     * @param string $input 表单名称
     * @param string $dir 路径
     * @param string $size 大小
     *
     */
    function upload($input, $dir, $return = "url", $type = "image", $size = "10M")
    {
        if (empty($_FILES[$input]['tmp_name'])) {
            return false;
        }
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $dir . '/' . $subdir1 . '/' . $subdir2 . '/';
        $url = '';
        $dir = PUBLIC_PATH . $url . $subdir;
        $dir = str_replace('//', '/', $dir);
        $fileUpload = new Utils\FileUpload();
        $fileUpload->setInput($input);
        $fileUpload->setDestinationDirectory($dir, true);
        $fileUpload->setAllowMimeType($type);
        $fileUpload->setMaxFileSize($size);
        $fileUpload->setAutoFilename();
        $fileUpload->saveSingle();
        $fileInfo = $fileUpload->getInfo();
        //echo $subdir . $fileInfo->filename;die;
        if ($fileUpload->getStatus()) {
            if ($return == 'url')
                return $subdir . $fileInfo->filename;
            else
                return $fileInfo;
        }
        return false;
    }
}
if (!function_exists('uploads')) {
    /**
     * 多图上传
     * @param $input
     * @param $dir
     * @param string $type
     * @param string $size
     * @return array|bool
     */
    function uploads($input, $dir, $type = "image", $size = "10M")
    {
        if (empty($_FILES[$input]['tmp_name'])) {
            return false;
        }
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $dir . '/' . $subdir1 . '/' . $subdir2 . '/';

        $config = Registry::get('configarr');
        $url = $config['site']['uploadUri'];
        $dir = PUBLIC_PATH . $url . $subdir;
        $dir = str_replace('//', '/', $dir);


        $fileUpload = new Utils\FileUpload();
        $fileUpload->setInput($input);
        $fileUpload->setDestinationDirectory($dir, true);
        $fileUpload->setAllowMimeType($type);
        $fileUpload->setMaxFileSize($size);
        $fileUpload->setAutoFilename();
        $fileUpload->save();
        return $fileUpload->getUrls();
    }
}

if (!function_exists('getIp')) {
    /**
     *  获取用户的ip
     */
    function getIp()
    {
        $keys = array('X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (isset($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        return null;
    }
}

if (!function_exists('getIpPort')) {
    /**
     * 获取客户端ip端口
     *
     */
    function getIpPort()
    {
        return (int)(getenv('REMOTE_PORT') ? getenv('REMOTE_PORT') : $_SERVER['REMOTE_PORT']);
    }
}

if (!function_exists('iplookup')) {
    /**
     * 通过新浪接口 获取ip地理位置
     */
    function iplookup($ip)
    {
        $add = '未知区域';

        $str = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip);
        $str = json_decode($str, true);
        if ($str) {
            $add = $str['data']['region'] . ' ' . $str['data']['city'];
        }
        return $add;
    }
}

if (!function_exists('getBrowser')) {
    /**
     * 获取用户浏览器型号。新加浏览器，修改代码，增加特征字符串.把IE加到12.0 可以使用5-10年了.
     */
    function getBrowser()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')) {
            $browser = 'Maxthon';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 12.0')) {
            $browser = 'IE12.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 11.0')) {
            $browser = 'IE11.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) {
            $browser = 'IE10.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {
            $browser = 'IE9.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {
            $browser = 'IE8.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {
            $browser = 'IE7.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
            $browser = 'IE6.0';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'NetCaptor')) {
            $browser = 'NetCaptor';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape')) {
            $browser = 'Netscape';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx')) {
            $browser = 'Lynx';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
            $browser = 'Opera';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
            $browser = 'Google';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {
            $browser = 'Firefox';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
            $browser = 'Safari';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iphone')) {
            $browser = 'iphone';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'ipod')) {
            $browser = 'ipod';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'ipad')) {
            $browser = 'ipad';
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'android')) {
            $browser = 'android';
        } else {
            $browser = 'other';
        }
        return $browser;
    }
}

if (!function_exists('isMobile')) {
    /**
     * 检测是否为手机访问
     *
     */
    function isMobile()
    {
        $mobileBrowserList = ['iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini', 'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung', 'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser', 'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource', 'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone', 'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop', 'benq', 'haier', '^lct', '320x320', '240x320', '176x220', 'pad', 'gt-p1000'];
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

        foreach ($mobileBrowserList as $v) {
            if (strpos($useragent, $v) !== false) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('getFileExt')) {
    /**
     * 获取文件扩展名
     * @param $filename
     * @return string
     */
    function getFileExt($filename)
    {
        return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
    }
}
if (!function_exists('getMemoryLimit')) {
    /**
     * 获取内存限制
     * @return int
     */
    function getMemoryLimit()
    {
        $memory_limit = @ini_get('memory_limit');
        return sizeInBytes($memory_limit);
    }
}

if (!function_exists('sizeFormat')) {
    /**
     * 人性化文件大小单位
     * @param $size
     * @param int $precision
     * @return string
     */
    function sizeFormat($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('B', 'K', 'M', 'G');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}

if (!function_exists('sizeInBytes')) {
    /**
     * 将人性化的文件大小转换成byte
     * @param $size
     * @return float|int
     */
    function sizeInBytes($size)
    {
        $unit = 'B';
        $units = array('B' => 0, 'K' => 1, 'M' => 2, 'G' => 3);
        $matches = array();
        preg_match('/(?<size>[\d\.]+)\s*(?<unit>b|k|m|g)?/i', $size, $matches);
        if (array_key_exists('unit', $matches)) {
            $unit = strtoupper($matches['unit']);
        }
        return (floatval($matches['size']) * pow(1024, $units[$unit]));
    }
}

if (!function_exists('hdate')) {
    /**
     * 根据格式获取当前时间
     */
    function hdate($format = 'Y-m-d H:i:s', $time = 0)
    {
        return empty($time) ? date($format) : date($format, $time);
    }
}

if (!function_exists('formatTime')) {
    /**
     * 获取友好时间
     * @param $time
     * @return false|string
     */
    function formatTime($time)
    {
        $rtime = date('Y/m/d H:i', $time);
        $htime = date('H:i', $time);
        $time = time() - $time;
        if ($time < 60) {
            $str = '刚刚';
        } elseif ($time < 60 * 60) {
            $min = floor($time / 60);
            $str = $min . '分钟前';
        } elseif ($time < 60 * 60 * 24) {
            $h = floor($time / (60 * 60));
            $str = $h . '小时前 ' . $htime;
        } elseif ($time < 60 * 60 * 24 * 3) {
            $d = floor($time / (60 * 60 * 24));
            if ($d == 1) {
                $str = '昨天 ' . $rtime;
            } else {
                $str = '前天 ' . $rtime;
            }
        } else {
            $str = $rtime;
        }
        return $str;
    }
}


if (!function_exists('clearHTML')) {
    /***
     * 清除html
     * @param $str
     * @return mixed|null|string|string[]
     */
    function clearHTML($str)
    {
        $str = preg_replace(array("/<br[^>]*>\s*\r*\n*/is", "/<br \/>/is"), "\r\n\r\n", $str);
        $str = preg_replace("/\n\n/is", "\r\n\r\n", $str);

        $str = str_replace(array('&nbsp;', '&bull;', '&mdash;', '&quot;', '&rdquo;', '&ldquo;', '&#8226;', '&#160;'), ' ', strip_tags(htmlspecialchars_decode($str, ENT_NOQUOTES)));
        return $str;
    }
}

if (!function_exists('ajaxReturn')) {
    /***
     * ajaxReturn
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     */
    function ajaxReturn($data, $type = 'JSON')
    {
        switch (strtoupper($type)) {
            case 'JSON':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $callback = filter($_GET['callback']);
                $handler = $callback ? $callback : 'callfun';
                exit($handler . '(' . json_encode($data) . ');');
        }
    }
}

if (!function_exists('url')) {
    /**
     * url 生成
     * @param string $route 路由
     * @param array $param 参数数组
     * @return string $url
     */
    function url($route, $params = array())
    {
        //$route = preg_replace('/^m\//is', 'mm/', $route);
        // rewrite start
        $rewrite_route = Registry::get('rewrite_route');
        $route_lower = strtolower($route);
        if (isset($rewrite_route[$route_lower])) {
            $router = Dispatcher::getInstance()->getRouter();
            $currRoute = $router->getRoute($route_lower);
            if ($currRoute instanceof Yaf_Route_Regex) {
                $route_temp = explode('/', $route);
                $info = array(':m' => $route_temp[0], ':c' => $route_temp[1], ':a' => $route_temp[2]);
                $url = $currRoute->assemble($info, array());
            } else {
                $url = $currRoute->assemble($params, array());
            }
            if ($url) {
                $config = Registry::get('config');
                $url = $config['site']['domain'] . $url;
                $params_other = array();
                foreach ($params as $key => $value) {
                    if ($value === 0 || $key == 'page')
                        continue;
                    if (strpos($url, ':' . $key) !== false) {
                        $url = str_replace(':' . $key, $value, $url);
                        unset($params[$key]);
                    } else {
                        $params_other[$key] = $value;
                    }
                }

                $url = rtrim($url, '/');
                if ($params_other) {
                    $query = http_build_query($params_other);
                    if ($query) {
                        $url = $url . '?' . $query;
                    }
                }
                return $url;
            }
        }
        // rewrite end


        // 系统默认
        $moduleName = Dispatcher::getInstance()->getRequest()->getModuleName();
        $controllerName = Dispatcher::getInstance()->getRequest()->getControllerName();
        $actionName = Dispatcher::getInstance()->getRequest()->getActionName();

        // 当前url
        if ($route == 'curr_url') {
            $route = $moduleName . '/' . $controllerName . '/' . $actionName;
            $route = strtolower($route);
            $arr = Dispatcher::getInstance()->getRequest()->getParams();

            // backend sort 处理
            if (isset($arr['sort'])) {
                $sort = explode('.', $arr['sort']);
                if ($params['sort'] == $sort[0])
                    unset($params['sort']);
                else
                    unset($arr['sort']);
            }
            $params = array_merge($arr, $params);
            if (isset($params['sort'])) {
                if (stripos($params['sort'], '.desc') !== false) {
                    $params['sort'] = str_ireplace('.desc', '', $params['sort']);
                } else {
                    $params['sort'] = $params['sort'] . '.desc';
                }
            }

        } elseif ($route[0] == '/') {
            // 合并参数
            $arr = Dispatcher::getInstance()->getRequest()->getParams();
            $params = array_merge($arr, $params);
            if (isset($params['page'])) {
                unset($params['page']);
                $params['page'] = '';
            }
            $route = $moduleName . '/' . $controllerName . '/' . $actionName;
        }
        $config = Registry::get('config');
        $url = $config['site']['domain'];
        $url = $url . $route;
        $url = rtrim($url, '/');
        foreach ($params as $key => $value) {
            if (empty($value) && $key != 'page')
                continue;
            $url .= '/' . $key . '/' . $value;
        }

        $currModule = Registry::get('currModule');
        if ($currModule == 'm') {
            $url = preg_replace(array('/index\/index$/i', '/\/index$/i'), '', $url);
        } else {
            $url = preg_replace('/index\/index$/i', '', $url);
        }
        return $url;
    }
}
if (!function_exists('cutstr')) {
    /**
     * 字符串截取
     * @param $string
     * @param $sublen
     * @param int $start
     * @param string $code
     * @return string
     */
    function cutstr($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        return \Utils\Strs::mSubStr($string, $sublen, $start, $code);
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @access public
     * @param  mixed $var 变量
     * @param  boolean $echo 是否输出(默认为 true，为 false 则返回输出字符串)
     * @param  string|null $label 标签(默认为空)
     * @param  integer $flags htmlspecialchars 的标志
     * @return null|string
     */
    function dump($var, $echo = true, $label = null, $flags = ENT_SUBSTITUTE)
    {
        $label = (null === $label) ? '' : rtrim($label) . ':';
        ob_start();
        var_dump($var);
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', ob_get_clean());

        if (!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, $flags);
        }

        $output = '<pre>' . $label . $output . '</pre>';

        if ($echo) {
            echo($output);
            return null;
        }

        return $output;
    }
}

if (!function_exists('sendMail')) {
    /**
     * 发送邮件方法
     * @param $to
     * @param $title
     * @param $content
     * @throws Exception
     */
    function sendMail($to, $title, $content)
    {
        $config = Registry::get('config');
        $email = \Utils\Email::instance([
            'charset'    => $config->mail->charset, // 编码格式
            'debug'      => $config->mail->debug, // 调式模式
            'type'       => $config->mail->type,
            'host'       => $config->mail->host,
            'port'       => $config->mail->port,
            'user'       => $config->mail->user,
            'pass'       => $config->mail->pass,
            'verifyType' => $config->mail->verifyType,
            'from'       => $config->mail->from,
        ]);
        $email->to($to);
        $email->subject($title);
        $email->message($content);
        $result = $email->send();
        return $result;
    }
}
