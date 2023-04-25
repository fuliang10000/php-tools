<?php

use Fuliang\PhpTools\Exceptions\ToolsException;
use Fuliang\PhpTools\Constants\ErrorCode;
use Fuliang\PhpTools\Constants\ErrorMsg;
use Fuliang\PhpTools\Constants\EnumPlatform;

/**
 * 打印数组.
 *
 * @param $arr
 */
if (!function_exists('p')) {
    function p($arr)
    {
        header('content-type:text/html;charset=utf8');
        echo '<pre>' . print_r($arr, true);
    }
}

/**
 * 得到微妙.
 *
 * @return float
 *
 * @author fuliang
 */
if (!function_exists('microtime_float')) {
    function microtime_float()
    {
        list($usec, $sec) = explode(' ', microtime());

        return (float)$usec + (float)$sec;
    }
}

/**
 *  获取客户端ip.
 *
 * @param int $type 0 or 1
 * @return mixed
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip(int $type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }
        if (@$_SERVER['HTTP_X_REAL_IP']) {//nginx 代理模式下，获取客户端真实IP
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR']; //浏览当前页面的用户计算机的ip地址
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf('%u', ip2long($ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];

        return $ip[$type];
    }
}

/**
 * 随机生成编码.
 *
 * @param int $len 长度
 * @param int $type 1:数字 2:字母 3:混淆
 * @return string
 */
if (!function_exists('rand_code')) {
    function rand_code(int $len, int $type = 1): string
    {
        $output = '';
        $str = ['a', 'b', 'c', 'd', 'e', 'f', 'g',
            'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
            's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        ];
        $num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        switch ($type) {
            case 1:
                $chars = $num;
                break;
            case 2:
                $chars = $str;
                break;
            default:
                $chars = array_merge($str, $num);
        }

        $chars_len = count($chars) - 1;
        shuffle($chars);

        for ($i = 0; $i < $len; ++$i) {
            $output .= $chars[mt_rand(0, $chars_len)];
        }

        return $output;
    }
}

/**
 * 加减密.
 *
 * @param $string
 * @param string $operation ENCODE or DECODE
 * @param string $key
 * @param int $expiry
 * @return string
 */
if (!function_exists('auth_code')) {
    function auth_code(string $string, string $operation = 'ENCODE', string $key = '', int $expiry = 0)
    {
        $ckey_length = 0;

        $key = md5($key ? $key : '9e13yK8RN2M0lKP8CLRLhGs468d1WMaSlbDeCcI');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ('DECODE' == $operation ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = 'DECODE' == $operation ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = 100;

        $rndkey = [];
        for ($i = 0; $i <= 255; ++$i) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; ++$i) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = @$box[$i];
            @$box[$i] = $box[$j];
            @$box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; ++$i) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = @$box[$a];
            @$box[$a] = $box[$j];
            @$box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ('DECODE' == $operation) {
            if ((0 == substr($result, 0, 10) || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            }

            return '';
        }

        return $keyc . str_replace('=', '', base64_encode($result));
    }
}


/**
 * 数组转换成xml.
 *
 * @param $array 数组
 * @return string xml结果
 */
if (!function_exists('array_to_xml')) {
    function array_to_xml(array $array): string
    {
        $xml = '<xml>';
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
            }
        }
        $xml .= '</xml>';

        return $xml;
    }
}

/**
 * 将xml转为数组.
 *
 * @param string $xml xml数据
 * @return array
 */
if (!function_exists('xml_to_array')) {
    function xml_to_array(string $xml): array
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}


/**
 * 获取天的问候语.
 *
 * @return string
 */
if (!function_exists('get_day_reeting')) {
    function get_day_reeting(): string
    {
        // 以上海时区为标准
        date_default_timezone_set('Asia/Shanghai');

        $rst = '晚上好';
        $h = date('H');

        if ($h < 11) {
            $rst = '早上好';
        } elseif ($h < 13) {
            $rst = '中午好';
        } elseif ($h < 17) {
            $rst = '下午好';
        }

        return $rst;
    }
}

/**
 * 生成随机字符串，不生成大写字母.
 * @param int $length
 * @return null|string
 */
if (!function_exists('get_rand_char')) {
    function get_rand_char(int $length): ?string
    {
        $str = null;
        $strPol = '0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $str .= $strPol[rand(0, $max)];
        }

        return $str;
    }
}

/**
 * 获取用户访问的平台.
 *
 * @return string
 */
if (!function_exists('get_platform')) {
    function get_platform(): string
    {
        // 全部变成小写字母
        $agent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
        $rst = EnumPlatform::PC;

        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $rst = EnumPlatform::IOS;
        }
        if (strpos($agent, 'android')) {
            $rst = EnumPlatform::ANDROID;
        }

        return $rst;
    }
}

/**
 * 是否是移动端用户
 * @return bool
 */
if (!function_exists('is_mobile')) {
    function is_mobile(): bool
    {
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock = preg_match('|/(.*?/)|', $useragent, $matches) > 0 ? $matches[0] : '';
        function CheckSubstrs($substrs, $text)
        {
            foreach ($substrs as $substr) {
                if (false !== strpos($text, $substr)) {
                    return true;
                }
            }

            return false;
        }

        $mobile_os_list = ['Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ'];
        $mobile_token_list = ['Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', '320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod'];
        $found_mobile = CheckSubstrs($mobile_os_list, $useragent_commentsblock) || CheckSubstrs($mobile_token_list, $useragent);
        if ($found_mobile) {
            return true;
        }

        return false;
    }
}

/**
 * 是否是微信用户, 如果是则返回微信版本.
 * @return false|string
 */
if (!function_exists('is_wechat')) {
    function is_wechat()
    {
        $rst = false;
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (false !== strpos($user_agent, 'MicroMessenger')) {
            // 获取版本号
            preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
            $rst = $matches[2] ?? false;
        }

        return $rst;
    }
}

/**
 * 生成订单号.
 *
 * @param float|int $uid
 * @return string
 */
if (!function_exists('make_order_sn')) {
    function make_order_sn($uid): string
    {
        return mt_rand(10, 99)
            . sprintf('%010d', time() - 946656000)
            . sprintf('%03d', (float)microtime() * 1000)
            . sprintf('%03d', (int)$uid % 1000);
    }
}

/**
 * 格式化字节大小.
 *
 * @param int $size 字节数
 * @param string $delimiter 数字和单位分隔符
 * @return string 格式化后的带单位的大小
 */
if (!function_exists('format_bytes')) {
    function format_bytes(int $size, string $delimiter = ''): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $size >= 1024 && $i < 5; ++$i) {
            $size /= 1024;
        }

        return round($size, 2) . $delimiter . $units[$i];
    }
}

/**
 * 格式化数量.
 *
 * @param int $number 数字
 * @param string $delimiter 数字和单位分隔符
 * @return string 格式化后的带单位的大小
 */
if (!function_exists('format_number')) {
    function format_number(int $number, string $delimiter = ''): string
    {
        if ($number < 1000) {
            return (string)$number;
        }

        $number = $number / 1000;
        $units = ['千+', '万+', '十万+', '百万+', '千万+', '亿+', '十亿+', '百亿+', '千亿+', '万亿+'];

        for ($i = 0; $number >= 10 && $i < 10; ++$i) {
            $number /= 10;
        }

        if (!isset($units[$i])) {
            throw new ToolsException(ErrorCode::ERROR_400, ErrorMsg::NUMBER_TOO_LARGE);
        }

        return round($number, 2) . $delimiter . $units[$i];
    }
}

/**
 * 友好的时间显示.
 *
 * @param int $sTime 待显示的时间
 * @param string $type 类型. normal | mohu | full | ymd | other
 * @return string
 */
if (!function_exists('friendly_date')) {
    function friendly_date(int $sTime, string $type = 'normal'): string
    {
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime = time();
        $dTime = $cTime - $sTime;
        $dDay = intval(date('z', $cTime)) - intval(date('z', $sTime));
        $dYear = intval(date('Y', $cTime)) - intval(date('Y', $sTime));

        //normal：n秒前，n分钟前，n小时前，日期
        switch ($type) {
            case 'normal':
                if ($dTime < 60) {
                    if ($dTime < 10) {
                        return '刚刚';
                    }
                    return intval(floor($dTime / 10) * 10) . '秒前';
                } elseif ($dTime < 3600) {
                    return intval($dTime / 60) . '分钟前';
                    //今天的数据.年份相同.日期相同.
                } elseif (0 == $dYear && 0 == $dDay) {
                    return '今天' . date('H:i', $sTime);
                } elseif (0 == $dYear) {
                    return date('m月d日 H:i', $sTime);
                }
                return date('Y-m-d H:i', $sTime);
            case 'mohu':
                if ($dTime < 60) {
                    return $dTime . '秒前';
                }
                if ($dTime < 3600) {
                    return intval($dTime / 60) . '分钟前';
                }
                if ($dTime >= 3600 && 0 == $dDay) {
                    return intval($dTime / 3600) . '小时前';
                }
                if ($dDay > 0 && $dDay <= 7) {
                    return intval($dDay) . '天前';
                }
                if ($dDay > 7 && $dDay <= 30) {
                    return intval($dDay / 7) . '周前';
                }
                if ($dDay > 30) {
                    return intval($dDay / 30) . '个月前';
                }
                break;
            case 'full':
                return date('Y-m-d , H:i:s', $sTime);
            case 'ymd':
                return date('Y-m-d', $sTime);
            default:
                if ($dTime < 60) {
                    return $dTime . '秒前';
                }
                if ($dTime < 3600) {
                    return intval($dTime / 60) . '分钟前';
                }
                if ($dTime >= 3600 && 0 == $dDay) {
                    return intval($dTime / 3600) . '小时前';
                }
                if (0 == $dYear) {
                    return date('Y-m-d H:i:s', $sTime);
                }
                return date('Y-m-d H:i:s', $sTime);
        }
    }
}

/**
 * 时间差值
 * @param int $begin_time
 * @param int $end_time
 * @return string
 */
if (!function_exists('get_time_diff')) {
    function get_time_diff(int $begin_time, int $end_time): string
    {
        if ($begin_time < $end_time) {
            $startTime = $begin_time;
            $endTime = $end_time;
        } else {
            $startTime = $end_time;
            $endTime = $begin_time;
        }
        $timeDiff = $endTime - $startTime;
        $days = intval($timeDiff / 86400);
        $remain = $timeDiff % 86400;
        $hours = intval($remain / 3600);
        $remain = $remain % 3600;
        $mins = intval($remain / 60);

        return $days . '天' . $hours . '小时' . $mins . '分';
    }
}

/**
 * 执行shell脚本.
 *
 * @param string $cmd
 * @return string
 */
if (!function_exists('exec_shell')) {
    function exec_shell(string $cmd): string
    {
        $res = '';
        if (function_exists('system')) {
            ob_start();
            system($cmd);
            $res = ob_get_contents();
            ob_end_clean();
        } elseif (function_exists('shell_exec')) {
            $res = shell_exec($cmd);
        } elseif (function_exists('exec')) {
            exec($cmd, $res);
            $res = join(PHP_EOL, $res);
        } elseif (function_exists('passthru')) {
            ob_start();
            passthru($cmd);
            $res = ob_get_contents();
            ob_end_clean();
        } elseif (is_resource($f = @popen($cmd, 'r'))) {
            $res = '';
            while (!feof($f)) {
                $res .= fread($f, 1024);
            }
            pclose($f);
        }

        return $res;
    }
}

/**
 * 生成token.
 *
 * @param string $signKey
 * @param array $params
 * @return string
 *
 */
if (!function_exists('make_token')) {
    function make_token(string $signKey, array $params): string
    {
        $params = __stripcslashes($params);

        ksort($params);

        $str = '';
        foreach ($params as $key => $item) {
            $str .= "{$key}={$item}&";
        }

        $str = trim($str, '&');

        return strtolower(md5($str . $signKey));
    }
}

/**
 * 反引用一个使用 addcslashes()转义的字符串.
 *
 * @param array $params
 * @return array
 */
if (!function_exists('__stripcslashes')) {
    function __stripcslashes(array $params): array
    {
        $_arr = [];

        foreach ($params as $key => $val) {
            $_arr[$key] = stripcslashes($val);
        }

        return $_arr;
    }
}

/**
 * 信息处理函数,结束进程.
 */
if (!function_exists('sig_func')) {
    function sig_func()
    {
        echo "SIGCHLD \r\n";
        pcntl_waitpid(-1, $status, WNOHANG);
    }
}