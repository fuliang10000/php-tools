<?php


namespace Fuliang\PhpTools\Helper;


class StrHelper
{

    /**
     * 获取汉字首字母.
     * @param string $s0
     * @return string|null
     */
    function getFirstChar(string $s0): ?string
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
     * 编码转换.
     * @param string $value
     */
    function strIconv(string &$value): void
    {
        if (!(is_numeric($value) || is_float($value))) {
            $value = (string)"\t" . $value;
            $value = mb_convert_encoding($value, 'GBK');
        }
    }
}