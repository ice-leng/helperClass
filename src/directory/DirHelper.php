<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/5
 * Time: 下午1:17
 */

namespace lengbin\helper\directory;


class DirHelper
{

    /**
     * 检查路径是否存在,不存在则递归生成路径
     *
     * @param string $path 路径
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function pathExists($path)
    {
        $pathinfo = pathinfo($path . '/tmp.txt');
        if (!empty($pathinfo['dirname'])) {
            if (file_exists($pathinfo['dirname']) === false) {
                if (mkdir($pathinfo['dirname'], 0777, true) === false) {
                    return false;
                }
            }
        }
        return $path;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir 文件夹路径
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function delDir($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullPath = $dir . "/" . $file;
                if (!is_dir($fullPath)) {
                    unlink($fullPath);
                } else {
                    self::delDir($fullPath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 递归修改目录/文件权限
     *
     * @param string $path  路径
     * @param int    $chmod 权限
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function chmod($path, $chmod)
    {
        if (!is_dir($path)) {
            return @chmod($path, $chmod);
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $fullPath = $path . '/' . $file;
                if (is_link($fullPath)) {
                    return FALSE;
                } elseif (!is_dir($fullPath) && !@chmod($fullPath, $chmod)) {
                    return FALSE;
                } elseif (!self::chmod($fullPath, $chmod)) {
                    return FALSE;
                }
            }
        }
        closedir($dh);
        if (@chmod($path, $chmod)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}