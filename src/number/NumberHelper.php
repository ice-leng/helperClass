<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/5
 * Time: 下午1:22
 */

namespace lengbin\helper\number;

class NumberHelper
{

    /**
     * 字节格式化
     *
     * @param int $size 字节
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function formatBytes($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    /**
     * 金额格式化
     *
     * @param $number
     * @param $decimals
     *
     * @return string
     */
    public static function formatNumbers($number, $decimals)
    {
        return sprintf("%.{$decimals}f", $number);
    }

    /**
     * 数字随机数
     *
     * @param int $num 位数
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function randNum($num = 7)
    {
        $rand = "";
        for ($i = 0; $i < $num; $i++) {
            $rand .= mt_rand(0, 9);
        }
        return $rand;
    }

    /**
     * 字母数字混合随机数
     *
     * @param int $num 位数
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function randStr($num = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $string = "";
        for ($i = 0; $i < $num; $i++) {
            $string .= substr($chars, rand(0, strlen($chars)), 1);
        }
        return $string;
    }


    /**
     * 数字金额转换为中文
     *
     * @param double $num 数字
     * @param bool   $sim 大小写
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function numberToChinese($num, $sim = FALSE)
    {
        if (!is_numeric($num)) {
            return '含有非数字非小数点字符！';
        }
        $char = $sim ? [
            '零',
            '一',
            '二',
            '三',
            '四',
            '五',
            '六',
            '七',
            '八',
            '九',
        ] : [
            '零',
            '壹',
            '贰',
            '叁',
            '肆',
            '伍',
            '陆',
            '柒',
            '捌',
            '玖',
        ];
        $unit = $sim ? ['', '十', '百', '千', '', '万', '亿', '兆'] : ['', '拾', '佰', '仟', '', '萬', '億', '兆'];
        $retval = '';
        $num = sprintf("%01.2f", $num);
        list ($num, $dec) = explode('.', $num);
        // 小数部分
        if ($dec['0'] > 0) {
            $retval .= "{$char[$dec['0']]}角";
        }
        if ($dec['1'] > 0) {
            $retval .= "{$char[$dec['1']]}分";
        }
        // 整数部分
        if ($num > 0) {
            $retval = "元" . $retval;
            $f = 1;
            $out = [];
            $str = strrev(intval($num));
            for ($i = 0, $c = strlen($str); $i < $c; $i++) {
                if ($str[$i] > 0) {
                    $f = 0;
                }
                if ($f == 1 && $str[$i] == 0) {
                    $out[$i] = "";
                } else {
                    $out[$i] = $char[$str[$i]];
                }
                $out[$i] .= $str[$i] != '0' ? $unit[$i % 4] : '';
                if ($i > 1 and $str[$i] + $str[$i - 1] == 0) {
                    $out[$i] = '';
                }
                if ($i % 4 == 0) {
                    $out[$i] .= $unit[4 + floor($i / 4)];
                }
            }
            $retval = join('', array_reverse($out)) . $retval;
        }
        return $retval;
    }


}