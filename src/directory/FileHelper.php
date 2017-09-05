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

    /**
     * 读取文件最后几条内容
     *
     * @param string $file
     * @param int    $num
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function readFileLastContent($file, $num = 1)
    {
        $fp = fopen($file, "r");
        $pos = -2;
        $eof = "";
        $head = false;   //当总行数小于Num时，判断是否到第一行了
        $lines = [];
        while ($num > 0) {
            while ($eof !== "\n") {
                if (fseek($fp, $pos, SEEK_END) === 0) {
                    $eof = fgetc($fp);
                    $pos--;
                } else {
                    fseek($fp, 0, SEEK_SET);
                    $head = true;
                    break;
                }

            }
            array_unshift($lines, fgets($fp));
            if ($head) {
                break;
            }
            $eof = "";
            $num--;
        }
        fclose($fp);
        return $lines;
    }

    /**
     * 下载文件
     *
     * @param string $name 文件名称
     * @param string $url  网络路径
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function download($name, $url)
    {
        $suffix = FileHelper::getExtension($url);
        $info = getimagesize($url);
        if ($info) {
            switch ($info[2]) {
                case 1:
                    $suffix = 'gif';
                    break;
                case 2:
                    $suffix = 'jpg';
                    break;
                case 3:
                    $suffix = 'png';
                    break;
            }
        }
        $file = $name . '.' . $suffix;
        FileHelper::putFile($file, FileHelper::getFile($url));
    }

}