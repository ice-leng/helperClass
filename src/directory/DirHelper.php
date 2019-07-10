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
                if (@mkdir($pathinfo['dirname'], 0777, true) === false) {
                    return false;
                }
            }
        }
        return $path;
    }

    public static $rootDir;

    /**
     * 递归清空目录
     *
     * @param string  $dir      文件夹路径
     * @param boolean $isDelete 是否删除根目录目录
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function emptyDir($dir, array $filterDir = [], array $filterFile = [], $isDelete = false)
    {
        if (empty(self::$rootDir)) {
            self::$rootDir = $dir;
        }
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $dir . '/' . $file;
                if (!is_dir($fullPath)) {
                    if (in_array($fullPath, $filterFile)) {
                        continue;
                    }
                    @unlink($fullPath);
                } else {
                    if (in_array($fullPath, $filterDir)) {
                        continue;
                    }
                    self::emptyDir($fullPath, $isDelete);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if ($dir !== self::$rootDir) {
            return self::delDir($dir);
        } else {
            return $isDelete ? self::delDir($dir) : true;
        }

    }

    /**
     * 删除目录
     *
     * @param string $dir
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    private static function delDir($dir)
    {
        return @rmdir($dir) ? true : false;
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
            if ($file !== '.' && $file !== '..') {
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

    /**
     * 复制文件夹/文件
     *
     * @param string  $src        源路径
     * @param string  $dst        复制路径
     * @param array   $filterDir  过滤文件夹
     * @param array   $filterFile 过滤文件
     * @param boolean $isUnlink   是否删除
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function copyDir($src, $dst, array $filterDir = [], array $filterFile = [], $isUnlink = false)
    {
        $dir = opendir($src);
        self::pathExists($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..') && !in_array($file, $filterDir)) {
                if (is_dir($src . "/" . $file)) {
                    self::copyDir($src . "/" . $file, $dst . "/" . $file, $filterDir, $filterFile, $isUnlink);
                } else {
                    if (!in_array($file, $filterFile)) {
                        @copy($src . "/" . $file, $dst . "/" . $file);
                        if ($isUnlink) {
                            @unlink($src . "/" . $file);
                        }
                    }
                }
            }
        }
        closedir($dir);
    }

    /**
     * 目录下是否有文件
     *
     * @param $path
     *
     * @return bool
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function dirExistFile($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        $files = scandir($path);
        // 删除  "." 和 ".."
        unset($files[0]);
        unset($files[1]);
        // 判断是否为空
        if (!empty($files[2])) {
            return true;
        }
        return false;
    }

}