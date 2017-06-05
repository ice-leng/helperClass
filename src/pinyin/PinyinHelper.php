<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/5
 * Time: 下午2:16
 */

namespace lengbin\helper\pinyin;

class PinyinHelper
{
    /**
     * 生成字母前缀
     *
     * @param string $str 汉字
     *
     * @return int|string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function convertFirst($str)
    {
        $firstChar = ord(strtoupper($str{0}));
        if (($firstChar >= 65 and $firstChar <= 91) or ($firstChar >= 48 and $firstChar <= 57)) {
            return $str{0};
        }
        $s = mb_convert_encoding($str, "gbk", "utf-8");
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 and $asc <= -20284) {
            return "A";
        }
        if ($asc >= -20283 and $asc <= -19776) {
            return "B";
        }
        if ($asc >= -19775 and $asc <= -19219) {
            return "C";
        }
        if ($asc >= -19218 and $asc <= -18711) {
            return "D";
        }
        if ($asc >= -18710 and $asc <= -18527) {
            return "E";
        }
        if ($asc >= -18526 and $asc <= -18240) {
            return "F";
        }
        if ($asc >= -18239 and $asc <= -17923) {
            return "G";
        }
        if ($asc >= -17922 and $asc <= -17418) {
            return "H";
        }
        if ($asc >= -17417 and $asc <= -16475) {
            return "J";
        }
        if ($asc >= -16474 and $asc <= -16213) {
            return "K";
        }
        if ($asc >= -16212 and $asc <= -15641) {
            return "L";
        }
        if ($asc >= -15640 and $asc <= -15166) {
            return "M";
        }
        if ($asc >= -15165 and $asc <= -14923) {
            return "N";
        }
        if ($asc >= -14922 and $asc <= -14915) {
            return "O";
        }
        if ($asc >= -14914 and $asc <= -14631) {
            return "P";
        }
        if ($asc >= -14630 and $asc <= -14150) {
            return "Q";
        }
        if ($asc >= -14149 and $asc <= -14091) {
            return "R";
        }
        if ($asc >= -14090 and $asc <= -13319) {
            return "S";
        }
        if ($asc >= -13318 and $asc <= -12839) {
            return "T";
        }
        if ($asc >= -12838 and $asc <= -12557) {
            return "W";
        }
        if ($asc >= -12556 and $asc <= -11848) {
            return "X";
        }
        if ($asc >= -11847 and $asc <= -11056) {
            return "Y";
        }
        if ($asc >= -11055 and $asc <= -10247) {
            return "Z";
        }
        return 0;
    }

    /**
     * 汉字转拼音
     *
     * @param string $str     汉字
     * @param int    $isHead  是否去每个字母的前缀
     * @param int    $isclose 是否取消global pinyin data
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function pinyin($str, $isHead = 0, $isclose = 1)
    {
        $str = mb_convert_encoding($str, "gbk", "utf-8");
        global $pinyins;
        $restr = '';
        $str = trim($str);
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (count($pinyins) == 0) {
            $fp = fopen(__DIR__ . '/data/pinyin.dat', 'r');
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
            }
            fclose($fp);
        }
        for ($i = 0; $i < $slen; $i++) {
            if (ord($str[$i]) > 0x80) {
                $c = $str[$i] . $str[$i + 1];
                $i++;
                if (isset($pinyins[$c])) {
                    if ($isHead == 0) {
                        $restr .= $pinyins[$c];
                    } else {
                        $restr .= $pinyins[$c][0];
                    }
                }
            } else {
                if (preg_match("/[a-z0-9]/i", $str[$i])) {
                    $restr .= $str[$i];
                }
            }
        }
        if ($isclose == 0) {
            unset($pinyins);
        }
        return $restr;
    }

}