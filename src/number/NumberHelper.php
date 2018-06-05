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
     * @param int $length 位数
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function randNum($length = 6)
    {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        $mem = rand($min, $max);
        return $mem;
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
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
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

    /**
     * 时间格式化
     *
     * @param      string /int  $date  时间/时间戳
     * @param bool $isInt 是否为int
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function formattingDay($date, $isInt = true)
    {
        return self::formattingDays($date, $date, $isInt);
    }

    /**
     * 双日期 格式化
     *
     * @param string $date       双日期
     * @param string $separatrix 分割符
     * @param bool   $isInt      是否为int
     *
     * @return array
     */
    public static function formattingDoubleDate($date, $separatrix = ' - ', $isInt = true)
    {
        $dates = explode($separatrix, $date);
        return self::formattingDays($dates[0], $dates[1], $isInt);
    }

    /**
     * 时间格式化
     *
     * @param      string /int  $start  时间/时间戳
     * @param      string /int  $end  时间/时间戳
     * @param bool $isInt 是否为int
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function formattingDays($start, $end, $isInt = true)
    {
        if (is_int($start)) {
            $start = date('Y-m-d', $start);
        }
        if (is_int($end)) {
            $end = date('Y-m-d', $end);
        }
        $start = $start . ' 00:00:00';
        $end = $end . ' 23:59:59';
        if ($isInt) {
            $start = strtotime($start);
            $end = strtotime($end);
        }
        return [$start, $end];
    }

    /**
     * 时间格式化
     *
     * @param   int $month 月份
     * @param bool  $isInt 是否为int
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function formattingMonth($month, $isInt = true)
    {
        if (strlen($month) < 3) {
            $month = date("Y-{$month}-d");
        }
        $timestamp = strtotime($month);
        $startTime = date('Y-m-1 00:00:00', $timestamp);
        $mdays = date('t', $timestamp);
        $endTime = date('Y-m-' . $mdays . ' 23:59:59', $timestamp);
        if ($isInt) {
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
        }
        return [$startTime, $endTime];
    }



}