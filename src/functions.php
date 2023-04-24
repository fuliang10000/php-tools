<?php

use Fuliang\PhpTools\Helper\ToolsException;
use Fuliang\PhpTools\Helper\ErrorCode;
use Fuliang\PhpTools\Helper\ErrorMsg;
/**
 * 编码转换.
 *
 * @param $value
 *
 * @author fuliang
 */
function str_iconv(&$value)
{
    if (!(is_numeric($value) || is_float($value))) {
        $value = (string)"\t" . $value;
        mb_convert_encoding($value, 'GBK');
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
 * @param int $type
 * @return mixed
 */
function getclientip($type = 0)
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

/**
 * 发送get请求.
 *
 * @param $url
 * @param $timeout
 * @param array $data
 *
 * @return null|bool|mixed|string
 * @author fuliang.
 *
 */
function http_get($url, $timeout = 10, $data = [])
{
    $rst = null;
    if (!empty($data)) {
        $data = is_array($data) ? toUrlParams($data) : $data;
        $url .= (false === strpos($url, '?') ? '?' : '&') . $data;
    }
//    if (function_exists('file_get_contents') && !is_null($timeout)) {
//        $rst = file_get_contents($url);
//        debug('rst'.$rst);
//    } else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 这个是重点。
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $rst = curl_exec($ch);
    curl_close($ch);
//    }

    return $rst;
}

/**
 * 执行一个 HTTP 请求
 *
 * @param string $Url 执行请求的Url
 * @param mixed $Params 表单参数
 * @param string $Method 请求方法 post / get
 * @return array  结果数组
 */
function sendRequest($Url, $Params, $Method = 'post')
{
    $Curl = curl_init(); //初始化curl

    if ('get' == $Method) {//以GET方式发送请求
        curl_setopt($Curl, CURLOPT_URL, "{$Url}?{$Params}");
    } else {//以POST方式发送请求
        curl_setopt($Curl, CURLOPT_URL, $Url);
        curl_setopt($Curl, CURLOPT_POST, 1); //post提交方式
        curl_setopt($Curl, CURLOPT_POSTFIELDS, $Params); //设置传送的参数
    }

    curl_setopt($Curl, CURLOPT_HEADER, false); //设置header
    curl_setopt($Curl, CURLOPT_RETURNTRANSFER, true); //要求结果为字符串且输出到屏幕上
    //curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间

    $Res = curl_exec($Curl); //运行curl

    curl_close($Curl); //关闭curl

    return $Res;
}

/**
 * 格式化字节大小.
 * @param number $size 字节数
 * @param string $delimiter 数字和单位分隔符
 * @return string 格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '')
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    for ($i = 0; $size >= 1024 && $i < 5; ++$i) {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 发送post请求.
 *
 * @param $url 地址
 * @param $args 参数
 * @param $timeout 过期时间 秒
 *
 * @return mixed
 * @author fuliang
 *
 */
function http_post($url, $args, $timeout = 30)
{
    $_header = [
        //       'Content-Type: application/json; charset=utf-8',
        //        'Content-Length: ' . strlen($args)
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $_header);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    $ret = curl_exec($ch);
    curl_close($ch);

    return $ret;
}

function dbCreateIn($params, $is_str = false)
{
    if (!$params) {
        return 0;
    }
    $rst = is_string($params) ? "{$params}" : $params;
    if (is_array($params)) {
        $params = array_filter(array_unique($params));
        $rst = '';
        foreach ($params as $val) {
            $rst .= (is_numeric($val) && !$is_str ? $val : "'{$val}'") . ',';
        }
        $rst = trim($rst, ',');
    }

    return $rst;
}

/**
 * 随机生成编码.
 *
 * @param $len 长度
 * @param int $type 1:数字 2:字母 3:混淆
 * @return string
 * @author
 *
 */
function rand_code($len, $type = 1)
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

/**
 * 打印数组.
 *
 * @param $arr
 */
function p($arr)
{
    //header('content-type:text/html;charset=utf8');
    echo '<pre>' . print_r($arr, true);
    exit;
}

/**
 * 多位数组排序.
 *
 * @param $arr
 * @param $key
 * @param int $sort_order
 * @param int $sort_type
 *
 * @return array 排好的数组
 */
function arrayMultiSort($arr, $key, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC)
{
    if (is_array($arr)) {
        foreach ($arr as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$key];
            }
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arr);
    }

    return $arr;
}

function arrayColumnHasVal($ary, $k)
{
    $a = [];
    foreach ($ary as $row) {
        if (!empty($row[$k])) {
            $a[] = $row[$k];
        }
    }

    return $a;
}

function arrayColumnReindex($ary, $k = 0)
{
    $a = [];
    foreach ($ary as $row) {
        $row = (array)$row;
        if ($k) {
            $a[trim($row[$k])] = $row;
        } else {
            $a[] = $row;
        }
    }

    return $a;
}

/**
 * 与客户端调试打印调试信息.
 *
 * @param $data
 * @param bool|false $op_file
 * @param mixed $filename
 */
function debug($data, $op_file = true, $filename = 'debug')
{
    $data = is_array($data) ? var_export($data, true) : $data;
    if ($op_file) {
        file_put_contents(\Yii::$app->basePath . "/../logs/{$filename}.txt", date('Y/m/d H:i:s', time()) . " \t输出结果:" . $data . "\r\n\r\n", FILE_APPEND);
    } else {
        $data = ['error' => 100, 'msg' => $data];
        // 返回JSON数据格式到客户端 包含状态信息
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
}

/**
 * 二维数据计算和.
 *
 * @param $arr
 * @param $key
 * @return int
 * @author fuliang
 *
 */
function arrayMultiSum($arr, $key)
{
    $sum = 0;
    if (is_array($arr)) {
        foreach ($arr as $array) {
            $sum += $array[$key];
        }
    }

    return $sum;
}

/**
 * 下载远程文件.
 *
 * @param $url
 * @param $path
 *
 * @return bool true false
 * @author fuliang
 *
 */
function download($url, $path = null)
{
    $file = http_get($url);

    if (empty($path)) {
        return $file;
    }

    $basedir = dirname($path);
    if (!is_dir($basedir)) {
        mkdir($basedir);
    }

    // 直接写入文件
    file_put_contents($path, $file);

    return file_exists($path);
}

/**
 * 加减密.
 *
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 *
 * @return string
 * @author fuliang
 *
 */
function authCode($string, $operation = ENCODE, $key = AUTH_KEY, $expiry = 0)
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





/**
 * 导入Excel.
 *
 * @param $fileName
 * @param string $encode
 * @return array
 * @throws PHPExcel_Exception
 * @throws Exception
 * @author fuliang
 *
 */
function importExcel($fileName, $encode = 'utf-8')
{
    $excelData = [];

    if (!file_exists($fileName)) {
        return $excelData;
    }

    header("Content-type:text/html;charset={$encode}");
    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
    if (!$objReader->canRead($fileName)) {
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
    }
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($fileName);

    $objWorksheet = $objPHPExcel->getActiveSheet();
    $highestRow = $objWorksheet->getHighestRow();
    $highestColumn = $objWorksheet->getHighestColumn();
    $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

    for ($row = 2; $row <= $highestRow; ++$row) {
        for ($col = 0; $col < $highestColumnIndex; ++$col) {
            $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }

    return $excelData;
}

/**
 * 导出excel.
 *
 * @param [type] $fileName                                                                                            [文件名]
 * @param [type] $arr_field                                                                                           [excel 的title字段]
 * @param [type] $arr_list                                                                                            [ 导出的数组数据]
 * @param [type] $k_time                                                                                              [ 要格式化转换时间的字段]
 * @param [type] $array_keys                                                                                          [要导出数组的 键名  keys]
 * @param [type] $model                                                                                               array[列=>宽度]
 * @param [type] $title                                                                                               sheet名称
 * @param [type] $statistics                                                                                          统计头数组 array(array（"A1数据","B1数据"...）...)
 * @param [type] $list_title_index                                                                                    列表头的行数
 * @param [type] $style=array("A1"=>array("align"=>"center,left,right","weight"=>'bold'),"height"=array("3"=>"25"..))
 * @author fuliang
 *
 */
function exportExcel($fileName, $arr_field, $arr_list, $array_keys, $k_time = 'createtime', $model = [], $title = null, $statistics = [], $list_title_index = 1, $style = [])
{
    // 加载PHPExcel.php
    header('Content-type:text/html;charset=utf-8');
    if (empty($arr_list) || !is_array($arr_list)) {
        echo '<script>
                        alert("数据必须是数组，且不能为空！");
                        history.go("-1");
                    </script>';
        exit;
    }
    if (empty($fileName)) {
        exit('文件名不能为空');
    }
    // 设置文件名
    $date = date('Y_m_d', time());
    $fileName .= "_{$date}.xlsx";
    //新建
    $resultPHPExcel = new \PHPExcel();
    $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp; //保存在php://temp
    $cacheSettings = [' memoryCacheSize ' => '80MB'];
    \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    $countList = count($arr_list);
    $countField = count($arr_field);
    $abc = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    //头部统计
    if (!empty($statistics)) {
        foreach ($statistics as $index => $value) {
            foreach ($value as $k => $v) {
                $resultPHPExcel->getActiveSheet()->setCellValue($abc[$k] . ($index + 1), $v);
            }
        }
    }
    // 设置文件title
    for ($i = 0; $i < $countField; ++$i) {
        $resultPHPExcel->getActiveSheet()->setCellValue($abc[$i] . $list_title_index, $arr_field[$i]);
    }
    // 设置单元格内容
    for ($i = 0; $i < $countList; ++$i) {
        for ($o = 0; $o < $countField; ++$o) {
            if ($array_keys[$o] == $k_time) {
                $resultPHPExcel->getActiveSheet()->setCellValue($abc[$o] . ($i + $list_title_index + 1), date('Y-m-d H:i:s', $arr_list[$i][$array_keys[$o]]));
            } else {
                $resultPHPExcel->getActiveSheet()->setCellValue($abc[$o] . ($i + $list_title_index + 1), @$arr_list[$i][$array_keys[$o]]);
            }
        }
    }
    //设置sheet的title
    if (!empty($title)) {
        $resultPHPExcel->getActiveSheet()->setTitle($title);
    }
    //设置列宽度
    if (count($model) > 0) {
        foreach ($model as $k => $v) {
            $resultPHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth($v);
        }
    } else {
        for ($o = 0; $o < $countField; ++$o) {
            $resultPHPExcel->getActiveSheet()->getColumnDimension($abc[$o])->setAutoSize(true);
        }
    }
    //设置样式
    if (count($style) > 0) {
        foreach ($style as $k => $arr) {
            foreach ($arr as $key => $value) {
                //行的高度
                if ('height' == $k) {
                    $resultPHPExcel->getActiveSheet()->getRowDimension($key)->setRowHeight($value);
                }
                //文字对齐方式
                if ('align' == $key) {
                    if ('center' == $value) {
                        $resultPHPExcel->getActiveSheet()->getStyle($k)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //左右居中
                        $resultPHPExcel->getActiveSheet()->getStyle($k)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
                    }
                }
                //加粗
                if ('weight' == $key) {
                    if ('bold' == $value) {
                        $resultPHPExcel->getActiveSheet()->getStyle($k)->getFont()->setBold(true);
                    }
                }
            }
        }
    }
    //设置导出文件名
    $outputFileName = $fileName;
    $xlsWriter = new \PHPExcel_Writer_Excel2007($resultPHPExcel);
    $xlsWriter->setOffice2003Compatibility(true);
    ob_end_clean(); //清除缓冲区  避免乱码
    header('Content-Type: application/force-download');
    header('Content-Type: application/octet-stream');
    header('Content-Type: application/download');
    header('Content-Disposition:inline;filename="' . $outputFileName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');
    $xlsWriter->save('php://output');
    exit;
}

/**
 * 用户fputcsv导出excel.
 *
 * @param string $fileName 文件名称
 * @param array $heads 头列表
 * @param array $data 数据
 *
 * @author fuliang
 */
function exportCsv($fileName, $heads, $data)
{
    // 不限定时间
    set_time_limit(0);
    // 内存限定
    ini_set('memory_limit', '1024M');
    // 输出Excel文件头
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename = {$fileName}" . '.csv');
    header('Cache-Control: max-age=0');

    // 打开PHP文件句柄，php://output 表示直接输出到浏览器
    $fp = fopen('php://output', 'a');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 添加 BOM
    // 输出Excel列名信息
    array_walk($heads, 'str_iconv');
    // 将数据通过fputcsv写到文件句柄
    fputcsv($fp, $heads);

    // 输出Excel内容
    foreach ($data as $one) {
        array_walk($one, 'str_iconv');
        fputcsv($fp, $one);
    }

    fclose($fp);
    exit;
}

/**
 * 数组转换成xml.
 *
 * @param $arr 数组
 *
 * @return string xml结果
 * @author fuliang
 *
 */
function arrayToXml($arr)
{
    $xml = '<xml>';
    foreach ($arr as $key => $val) {
        if (is_numeric($val)) {
            $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
        } else {
            $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
        }
    }
    $xml .= '</xml>';

    return $xml;
}

/**
 * 将xml转为数组.
 *
 * @param $xml xml数据
 *
 * @return array|mixed|stdClass
 */
function xmlToArray($xml)
{
    //将XML转为array
    return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
}

//获取汉字首字母
function getfirstchar($s0)
{
    $fchar = ord($s0[0]);
    if ($fchar >= ord('A') and $fchar <= ord('z')) {
        return strtoupper($s0[0]);
    }
    $s1 = iconv('UTF-8', 'gb2312', $s0);
    $s2 = iconv('gb2312', 'UTF-8', $s1);
    if ($s2 == $s0) {
        $s = $s1;
    } else {
        $s = $s0;
    }
    $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
    if ($asc >= -20319 and $asc <= -20284) {
        return 'A';
    }
    if ($asc >= -20283 and $asc <= -19776) {
        return 'B';
    }
    if ($asc >= -19775 and $asc <= -19219) {
        return 'C';
    }
    if ($asc >= -19218 and $asc <= -18711) {
        return 'D';
    }
    if ($asc >= -18710 and $asc <= -18527) {
        return 'E';
    }
    if ($asc >= -18526 and $asc <= -18240) {
        return 'F';
    }
    if ($asc >= -18239 and $asc <= -17923) {
        return 'G';
    }
    if ($asc >= -17922 and $asc <= -17418) {
        return 'I';
    }
    if ($asc >= -17417 and $asc <= -16475) {
        return 'J';
    }
    if ($asc >= -16474 and $asc <= -16213) {
        return 'K';
    }
    if ($asc >= -16212 and $asc <= -15641) {
        return 'L';
    }
    if ($asc >= -15640 and $asc <= -15166) {
        return 'M';
    }
    if ($asc >= -15165 and $asc <= -14923) {
        return 'N';
    }
    if ($asc >= -14922 and $asc <= -14915) {
        return 'O';
    }
    if ($asc >= -14914 and $asc <= -14631) {
        return 'P';
    }
    if ($asc >= -14630 and $asc <= -14150) {
        return 'Q';
    }
    if ($asc >= -14149 and $asc <= -14091) {
        return 'R';
    }
    if ($asc >= -14090 and $asc <= -13319) {
        return 'S';
    }
    if ($asc >= -13318 and $asc <= -12839) {
        return 'T';
    }
    if ($asc >= -12838 and $asc <= -12557) {
        return 'W';
    }
    if ($asc >= -12556 and $asc <= -11848) {
        return 'X';
    }
    if ($asc >= -11847 and $asc <= -11056) {
        return 'Y';
    }
    if ($asc >= -11055 and $asc <= -10247) {
        return 'Z';
    }

    return null;
}

/**
 * 获取天的问候语.
 *
 * @return string
 * @author fuliang
 *
 */
function getDayReeting()
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

/**
 * 系统非常规MD5加密方法.
 *
 * @param string $str 要加密的字符串
 * @param mixed $auth_key
 * @return string
 */
function userMd5($str, $auth_key)
{
    if (!$auth_key) {
        $auth_key = '' ?: '>=diMf;Sbduzn@!NBa~Hpl_@&IeG_w]O&ieZtiDffKTh]pK".doZ`wd,T$$:,Ka(';
    }

    return '' === $str ? '' : md5(sha1($str) . $auth_key);
}

/**
 * 生成随机字符串，不生成大写字母.
 * @param $length
 * @return null|string
 */
function getRandChar($length)
{
    $str = null;
    $strPol = '0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($strPol) - 1;

    for ($i = 0; $i < $length; ++$i) {
        $str .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }

    return $str;
}

/**
 * 遍历文件夹找到.app目录.
 * @param [type] $dir [遍历目录]
 * @param  [type]           [文件夹名]
 * @param mixed $type
 * @param mixed $icon
 * @return [type] [icon数组]
 */
function read_all_dir($dir, $type, $icon)
{
    delDirAndFile($dir . '/__MACOSX');
    $handle = opendir($dir);
    $result = '';
    if ($handle) {
        while (($file = readdir($handle)) !== false) {
            if ('.' != $file && '..' != $file) {
                $cur_path = $dir . DIRECTORY_SEPARATOR . $file; //构建子目录路径
                if (is_dir($cur_path)) {  //如果是目录则继续遍历直到找到.app目录
                    if (strpos($cur_path, '.app')) {
                        if (1 == $type) {
                            $all_dir = scandir($cur_path);
                            if (in_array('Info.plist', $all_dir)) {
                                $result['plist_url'] = $cur_path . '/Info.plist';
                            }
                        } elseif (2 == $type) {
                            $result = read_png($cur_path, $icon);
                        } else {
                            $result = getagentinfo($cur_path);
                        }
                    } else {
                        $t = read_all_dir($cur_path, $type, $icon);
                        if (!empty($t)) {
                            $result = $t;
                        }
                    }
                }
            }
        }
    }

    closedir($handle);

    return $result;
}

/**
 * 删除当前文件夹和文件.
 * @param  [type] $path   [description]
 * @param bool $delDir [description]
 * @return [type] [description]
 */
function delDirAndFile($path, $delDir = true)
{
    if ($delDir && !is_dir($path)) {
        return true;
    }

    $handle = opendir($path);
    if ($handle) {
        while (false !== ($item = readdir($handle))) {
            if ('.' != $item && '..' != $item) {
                is_dir("{$path}/{$item}") ? delDirAndFile("{$path}/{$item}", $delDir) : unlink("{$path}/{$item}");
            }
        }
        closedir($handle);
        if ($delDir) {
            return rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }
}

/**
 * 取出png信息.
 * @param  [type] $dir  [description]
 * @param  [type] $icon [description]
 * @return [type] [description]
 */
function read_png($dir, $icon)
{
    $pngs = [];
    $handle = @opendir($dir);
    if ($handle) {
        while (($file = readdir($handle)) !== false) {
            if ('.' != $file && '..' != $file) {
                $cur_path = $dir . DIRECTORY_SEPARATOR . $file; //构建子目录路径
                if (!is_dir($cur_path)) {
                    foreach ($icon as $key => $value) {
                        if (strpos($cur_path, $value)) {
                            $result[] = $cur_path;
                        }
                    }
                } else {
                    delDirAndFile($cur_path);
                    read_png($cur_path, $icon);
                }
            }
        }
        closedir($handle);
    }

    // print_r($result);die;
    return @$result;
}

function getagentinfo($dir)
{
    $result = '';
    $handle = opendir($dir);
    if ($handle) {
        while (($file = readdir($handle)) !== false) {
            if ('.' != $file && '..' != $file) {
                $cur_path = $dir . DIRECTORY_SEPARATOR . $file; //构建子目录路径
                if (!is_dir($cur_path)) {
                    if (strpos($cur_path, 'TUUChannel')) {
                        $result = $cur_path;
                        // print_r($result);die;
                    }
                } else {
                    delDirAndFile($cur_path);
                    getagentinfo($cur_path);
                }
            }
        }
    }
    closedir($handle);

    return $result;
}

/**
 * 获取图片数组最大的一张.
 * @param  [type] $pngs [description]
 * @return [type] [description]
 */
function maxpng($pngs)
{
    $temp = 0;
    foreach ($pngs as $key => $value) {
        $png = intval(filesize($value));
        if ($temp < $png) {
            $temp = $png;
            $k = $key;
        }
    }

    return $pngs[$k];
}

/**
 * 获取plist文件内容.
 * @param  $filename
 * @return [type] [description]
 */
function getIpaVersionMsg($filename)
{
    require __DIR__ . '/../../common/components/thirdparty/CFPropertyList/CFPropertyList.php';
    $content = file_get_contents($filename);
    $plist = new \CFPropertyList\CFPropertyList();
    $plist->parse($content);
    $plist_arr = $plist->toArray();

    $info['version'] = $plist_arr['CFBundleShortVersionString'];
    $info['package_name'] = $plist_arr['CFBundleIdentifier'];
    $info['title'] = $plist_arr['CFBundleDisplayName'];
    $info['icon'] = geticonfiles($plist_arr);

    return $info;
}

function geticonfiles($data)
{
    if (!empty($data['CFBundleIconFiles'])) {
        return $data['CFBundleIconFiles'];
    }
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $ret = geticonfiles($value);
            if (!empty($ret)) {
                return $ret;
            }
        }
    }
}

/**
 * 递归移动文件及文件夹.
 *
 * @param  [string] $source 源目录或源文件
 * @param  [string] $target 目的目录或目的文件
 * @return bool     true
 */
function moveFile($source, $target)
{
    // 如果源目录/文件不存在返回false
    if (!file_exists($source)) {
        return false;
    }

    // 如果要移动文件
    if ('file' == filetype($source)) {
        $basedir = dirname($target);
        if (!is_dir($basedir)) {
            mkdir($basedir, 0755, true);
        } //目标目录不存在时给它创建目录
        copy($source, $target);
        unlink($source);
    } else { // 如果要移动目录
        if (!file_exists($target)) {
            mkdir($target, 0755, true);
        } //目标目录不存在时就创建

        $files = []; //存放文件
        $dirs = []; //存放目录
        $fh = opendir($source);

        if (false != $fh) {
            while ($row = readdir($fh)) {
                $src_file = $source . '/' . $row; //每个源文件
                if ('.' != $row && '..' != $row) {
                    if (!is_dir($src_file)) {
                        $files[] = $row;
                    } else {
                        $dirs[] = $row;
                    }
                }
            }
            closedir($fh);
        }

        foreach ($files as $v) {
            copy($source . '/' . $v, $target . '/' . $v);
            unlink($source . '/' . $v);
        }

        if (count($dirs)) {
            foreach ($dirs as $v) {
                moveFile($source . '/' . $v, $target . '/' . $v);
            }
        }
    }

    return true;
}

/**
 * 转换为url参数.
 *
 * @param $params
 * @return string
 * @author fuliang
 *
 */
function toUrlParams($params)
{
    $buff = '';

    if (empty($params)) {
        return $buff;
    }

    foreach ($params as $k => $v) {
        if (!is_array($v)) {
            $buff .= $k . '=' . urlencode($v) . '&';
        }
    }

    return trim($buff, '&');
}

/**
 * 生成plist文件.
 *
 * @param  [type] $data       要写入文件的信息
 * @param  [type] $filename   生成文件的路径
 * @param mixed $create_dir
 * @return [type] [description]
 * @author fuliang
 *
 */
function createPlist($data, $create_dir, $filename)
{
    require __DIR__ . '/../../common/components/thirdparty/CFPropertyList/CFPropertyList.php';

    $plist = new \CFPropertyList\CFPropertyList();
    $plist->add($dict_1 = new \CFPropertyList\CFDictionary());
    $dict_1->add('items', $array1 = new \CFPropertyList\CFArray());

    $array1->add($dict_2 = new \CFPropertyList\CFDictionary());
    $dict_2->add('assets', $array2 = new \CFPropertyList\CFArray());
    $dict_2->add('metadata', $dict_metadata = new \CFPropertyList\CFDictionary());

    $array2->add($dict_ass_1 = new \CFPropertyList\CFDictionary());
    $array2->add($dict_ass_2 = new \CFPropertyList\CFDictionary());
    $array2->add($dict_ass_3 = new \CFPropertyList\CFDictionary());

    $dict_ass_1->add('kind', new \CFPropertyList\CFString('software-package'));
    $dict_ass_1->add('url', new \CFPropertyList\CFString($data['url']));

    $dict_ass_2->add('kind', new \CFPropertyList\CFString('full-size-image'));
    $dict_ass_2->add('needs-shine', new \CFPropertyList\CFBoolean(true));
    $dict_ass_2->add('url', new \CFPropertyList\CFString($data['icon']));

    $dict_ass_3->add('kind', new \CFPropertyList\CFString('display-image'));
    $dict_ass_3->add('needs-shine', new \CFPropertyList\CFBoolean(true));
    $dict_ass_3->add('url', new \CFPropertyList\CFString($data['icon']));

    $dict_metadata->add('bundle-identifier', new \CFPropertyList\CFString($data['package_name']));
    $dict_metadata->add('bundle-version', new \CFPropertyList\CFString($data['version']));
    $dict_metadata->add('kind', new \CFPropertyList\CFString('software'));
    $dict_metadata->add('subtitle', new \CFPropertyList\CFString($filename));
    $dict_metadata->add('title', new \CFPropertyList\CFString($data['title']));

    if (file_exists($create_dir)) {
        @unlink($create_dir);
    }
    $plist->saveXML($create_dir);
}

/**
 * 读取socket数据.
 *
 * @param $socket
 * @param bool|true $isDividePkg
 * @return null|array|string
 * @author fuliang
 *
 */
function socketRead($socket, $isDividePkg = true)
{
    $rst = null;

    $buf = socket_read($socket, 8192);
    if ($isDividePkg) {
        $_buf = @json_decode($buf, true);
        $rst = !empty($_buf) ? [$_buf['error'], $_buf['msg'], @$_buf['content']] : $buf;
    } else {
        $rst = $buf;
    }

    return $rst;
}

/**
 * 获取访问的平台.
 *
 * @return int
 * @author fuliang
 *
 */
function getPlatform()
{
    // 全部变成小写字母
    $agent = strtolower(@$_SERVER['HTTP_USER_AGENT']);
    $rst = PLATFORM_PC;

    if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
        $rst = PLATFORM_IOS;
    }

    if (strpos($agent, 'android')) {
        $rst = PLATFORM_ANDROID;
    }

    return $rst;
}

function isMobile()
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

/**
 * 是否是微信,如果是则返回微信版本.
 *
 * @return bool
 * @author fuliang
 *
 */
function isWeiXin()
{
    $rst = false;
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (false !== strpos($user_agent, 'MicroMessenger')) {
        // 获取版本号
        preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*/', $user_agent, $matches);
        $rst = @$matches[2];
    }

    return $rst;
}

/**
 * 获取apk版本号.
 *
 * @param $filename apk包路径
 *
 * @return null|int|string
 * @author fuliang
 *
 */
function getApkVersion($filename)
{
    require __DIR__ . '/../../common/components/thirdparty/Apkparser/Apkparser.class.php';

    $apk = new \ApkParser();
    $res = $apk->open($filename);
    $versioncode = $apk->getVersionCode();
    $version = $apk->getVersionName();
    $package = $apk->getPackage();

    $return = [
        'versioncode' => $versioncode,
        'version' => $version,
        'res' => $res,
        'package_name' => $package,
    ];

    return $return;
}

/**
 * 获取省市基础信息.
 *
 * 优先从淘宝获取,获取不到再从新浪获取.
 *
 * @param $ip
 * @return array
 * @author fuliang
 *
 */
function getPCInfoByIp($ip)
{
    $taobao = "http://ip.taobao.com/service/getIpInfo.php?ip={$ip}";
    $sina = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip={$ip}";

    $rest = @json_decode(http_get($taobao, 5), true);
    if ($rest && !empty($rest['data'])) {
        $rst = [
            'p' => $rest['data']['region'],
            'pcode' => $rest['data']['region_id'],
            'c' => $rest['data']['city'],
            'ccode' => $rest['data']['city_id'],
        ];
    } else {
        $rest = @json_decode(http_get($sina, 5), true);
        $rst = [
            'p' => @$rest['province'],
            'pcode' => 0,
            'c' => @$rest['city'],
            'ccode' => 0,
        ];
    }

    return $rst;
}

/**
 * 返回数据统计的百分比显示结果.
 * @param $preNum    昨天的
 * @param $preTwoNum 前天的
 * @param mixed $isReturn
 * @return string
 *                         红色表示上升  stat-percent font-bold text-danger
 *                         绿色表示下降  stat-percent font-bold text-info
 *                         其他情况表示为灰色 stat-percent font-bold
 */
function getPercent($preNum, $preTwoNum, $isReturn = 0)
{
    $htmlClass = 'stat-percent font-bold text-danger';
    $floatNum = 0;
    if (!$preTwoNum || !$preNum || ($preTwoNum == $preNum)) {
        $htmlClass = 'stat-percent font-bold';
    }

    if (!empty($preTwoNum) && '0.00' != $preTwoNum) {
        $floatNum = round((($preNum - $preTwoNum) / $preTwoNum), 2);
    }

    if ($floatNum < 0) {
        $floatNum = abs($floatNum);
        $htmlClass = 'stat-percent font-bold text-info';
    }
    $floatNum = $floatNum * 100;
    if ($isReturn) {
        return $floatNum . '%';
    }

    return "<div class='{$htmlClass}'>" . $floatNum . '%' . '</div>';
}

/**
 * 生成订单号.
 *
 * @param $uid
 * @return string
 * @author fuliang.
 *
 */
function makeOrderNo($uid)
{
    return mt_rand(10, 99)
        . sprintf('%010d', time() - 946656000)
        . sprintf('%03d', (float)microtime() * 1000)
        . sprintf('%03d', (int)$uid % 1000);
}

/**
 * 格式化金额.
 *
 * @param $amount 金额
 * @param $scale 保留小数位数
 * @param $is_floor 是否四舍五入
 * @return string
 * @author fuliang
 *
 */
function formatAmount($amount, $scale = 2, $is_floor = 1)
{
    if (empty($amount)) {
        return 0;
    }
    if ($is_floor) {
        $money = number_format($amount, $scale, '.', '');
    } else {
        $money = (float)substr(sprintf('%.' . ($scale + 1) . 'f', $amount), 0, -1);
    }

    return $money;
}

/**
 * 格式化字节大小.
 *
 * @param number $size 字节数
 * @param string $delimiter 数字和单位分隔符
 * @return string 格式化后的带单位的大小
 * @author fuliang
 *
 */
function formatBytes($size, $delimiter = '')
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    for ($i = 0; $size >= 1024 && $i < 5; ++$i) {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 格式化数量.
 *
 * @param number $count 个数
 * @param string $delimiter 数字和单位分隔符
 *
 * @return string 格式化后的带单位的大小
 * @author fuliang
 *
 */
function formatCount($count, $delimiter = '')
{
    if ($count < 1000) {
        return $count;
    }

    $count = $count / 1000;
    $units = ['千+', '万+', '十万+', '百万+', '千万+'];

    for ($i = 0; $count >= 10 && $i < 5; ++$i) {
        $count /= 10;
    }

    return round($count, 2) . $delimiter . $units[$i];
}

/**
 * 友好的时间显示.
 *
 * @param int $sTime 待显示的时间
 * @param string $type 类型. normal | mohu | full | ymd | other
 * @param string $alt 已失效
 *
 * @return string
 * @author fuliang
 *
 */
function friendly_date($sTime, $type = 'normal', $alt = 'false')
{
    if (!$sTime) {
        return '';
    }
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
                //return intval($dTime/3600)."小时前";
                return '今天' . date('H:i', $sTime);
            } elseif (0 == $dYear) {
                return date('m月d日 H:i', $sTime);
            }

            return date('Y-m-d H:i', $sTime);
            break;
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
            break;
        case 'ymd':
            return date('Y-m-d', $sTime);
            break;
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
            break;
    }
}

/**
 * 时间差值
 * @param $begin_time
 * @param $end_time
 * @return string
 */
function getTimeDiff($begin_time, $end_time)
{
    if ($begin_time < $end_time) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    $timediff = $endtime - $starttime;
    $days = intval($timediff / 86400);
    $remain = $timediff % 86400;
    $hours = intval($remain / 3600);
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    $secs = $remain % 60;

    return $days . '天' . $hours . '小时' . $mins . '分';
}

/**
 * 获取游戏icon.
 *
 * @param $gameid 游戏id
 *
 * @return string
 */
function getGameIcon($gameid)
{
    return \common\models\MoxGame::getLogo($gameid);
}

/**
 * 获取游戏icon. 老官网.
 *
 * @param $gameid 游戏id
 *
 * @return string
 */
function getGameIconOld($gameid)
{
    return \common\models\MoxGame::getLogoOld($gameid);
}

/**
 * 数组分页函数.
 *
 * @param $array 查询出来的所有数组
 * @param int $page 当前第几页
 * @param int $count每页多少条数据
 * @param mixed $count
 * @return array [需要的数据,总页数,总记录数]
 * @author fuliang
 *
 */
function arrayPage($array, $page = 1, $count = 10)
{
    global $totalPage;

    // 判断当前页面是否为空 如果为空就表示为第一页面
    $page = (empty($page) || $page <= 1) ? 1 : $page;

    // 计算每次分页的开始位置
    $start = ($page - 1) * $count;

    $total = count($array);

    // 计算总页面数
    $totalPage = ceil($total / $count);

    // 拆分数据
    $list = array_slice($array, $start, $count);

    return [$list, $totalPage, $total];
}

/**
 * 执行shell脚本.
 *
 * @param $cmd
 * @return string
 * @author fuliang
 *
 */
function execShell($cmd)
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
        $res = join("\n", $res);
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

/**
 * 生成token.
 *
 * @param $signKey
 * @param $params
 *
 * @return string
 * @author fuliang
 *
 */
function makeToken($signKey, $params)
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

/**
 * 反引用一个使用 addcslashes()转义的字符串.
 *
 * @param $params
 *
 * @return array
 * @author fuliang
 *
 */
function __stripcslashes($params)
{
    $_arr = [];

    foreach ($params as $key => $val) {
        $_arr[$key] = stripcslashes($val);
    }

    return $_arr;
}

/**
 * 获取游戏详情地址.
 *
 * @param $gid
 * @return string
 */
function getGameDetailUrl($gid)
{
    // 官网地址
    $homeUrl = Yii::$app->params['MOX_HOME_URL'];

    return $homeUrl . "game/detail/{$gid}.htm";
}

/**
 * 获取游戏资讯点击地址.
 *
 * @param $id 资讯id
 * @param $gid 游戏id
 * @param int $type 0:非下载 1:安卓下载 2: IOS下载
 *
 * @return string
 * @author fuliang
 *
 */
function getGameInfoUrl($id, $gid, $type = 0)
{
    // 官网地址
    $homeUrl = Yii::$app->params['MOX_API_URL'];

//    if ($type) {
    return $homeUrl . 'html/info-detail/detail.php?id=' . $id;
//    } else {
//        $url = getGameDetailUrl($gid);
//    }
}

/**
 * 格式化搜索时间.
 *
 * @param bool|true $is_now
 * @return array     [开始时间,结束时间,相差的天数]
 * @author fuliang
 *
 */
function getSearchDate($is_now = true)
{
    $sdate = \Yii::$app->request->get('start_time');
    $edate = \Yii::$app->request->get('end_time');

    // 昨天时间戳
    $yestoday = date('Y-m-d', strtotime('-1 day'));
    $stime = strtotime($yestoday . ' 00:00:00');
    $etime = $is_now ? time() : strtotime($yestoday . ' 23:59:59');

    if ($sdate && $edate) {
        $stime = strtotime($sdate . ' 00:00:00');
        $etime = strtotime($edate . ' 23:59:59');
    } elseif ($sdate) {
        $stime = strtotime($sdate . ' 00:00:00');
        $etime = strtotime('+1 month', $stime);
    } elseif ($edate) {
        $etime = strtotime($edate . ' 23:59:59');
        $stime = strtotime('-1 month', $stime);
    }

    // 相差的天数
    $differ_day = ceil(($etime - $stime) / 86400);

    return [$stime, $etime, $differ_day];
}

/**
 * 信息处理函数,结束进程.
 *
 * @author fuliang
 */
function sig_func()
{
    echo "SIGCHLD \r\n";

    pcntl_waitpid(-1, $status, WNOHANG);
}


