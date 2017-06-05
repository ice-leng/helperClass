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
     * 和 http_build_query 相反，分解出参数
     *
     * @param string $query     url / query 参数
     * @param bool   $isQuery   是否需要解析
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function httpSplitQuery($query, $isQuery = false)
    {
        $params = [];
        if (!$isQuery) {
            $parse_arr = parse_url($query);
            if (empty($parse_arr['query'])) {
                return $params;
            }
            $query = $parse_arr['query'];
        }

        $query_arr = explode("&", $query);
        foreach ($query_arr as $val) {
            $arr = explode("=", $val);
            $params[$arr[0]] = $arr[1];
        }
        return $params;
    }
}