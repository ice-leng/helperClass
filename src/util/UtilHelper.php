<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/5
 * Time: 下午4:08
 */

namespace lengbin\helper\util;

class UtilHelper
{
    /**
     * 是否序列化
     *
     * @param string $string
     *
     * @return bool
     */
    public static function isJson($string)
    {
        $data = json_decode($string, false);
        return (json_last_error() == JSON_ERROR_NONE) ? $data : $string;
    }
}