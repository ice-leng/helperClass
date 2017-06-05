<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/5
 * Time: 下午3:15
 */

namespace lengbin\helper\directory;


class FileHelper
{
    /**
     * 网络路径读取文件
     *
     * @param string $url
     * @param int    $timeout 超时时间
     *
     * @return bool|mixed|string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function getFile($url, $timeout = 10)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $content = curl_exec($ch);
            curl_close($ch);
            if ($content) {
                return $content;
            }
        }
        $ctx = stream_context_create(['http' => ['timeout' => $timeout]]);
        $content = @file_get_contents($url, 0, $ctx);
        if ($content) {
            return $content;
        }
        return false;
    }

    /**
     * 写文件，如果文件目录不存在，则递归生成
     *
     * @param  string $file    文件名 路径+文件
     * @param  string $content 内容
     *
     * @return bool|int
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function putFile($file, $content)
    {
        $pathInfo = pathinfo($file);
        if (!empty($pathInfo['dirname'])) {
            if (file_exists($pathInfo['dirname']) === false) {
                if (@mkdir($pathInfo['dirname'], 0777, true) === false) {
                    return false;
                }
            }
        }
        return @file_put_contents($file, $content);
    }

    /**
     * 获取文件后缀名
     *
     * @param $fileName
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function getExtension($fileName)
    {
        $ext = explode('.', $fileName);
        $ext = array_pop($ext);
        return strtolower($ext);
    }
}