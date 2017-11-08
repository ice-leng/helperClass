<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/10/2
 * Time: 下午11:09
 */

namespace lengbin\helper\util\cache;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\FileHelper;
use lengbin\helper\util\ObjectHelper;

class CacheHelper extends ObjectHelper
{

    public $path;

    public function __construct($path = '')
    {
        $this->init($path);
    }

    /**
     * 初始化 path
     *
     * @param string $path
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function init($path = '')
    {
        $this->path = $path;
        if (empty($this->path)) {
            $this->path = dirname(dirname(__DIR__)) . 'runtime/cache';
        }
    }

    /**
     * 设置文件缓存
     *
     * @param string $name 名称
     * @param mixed  $data 缓存数据
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function set($name, $data)
    {
        if (!is_dir($this->path)) {
            DirHelper::pathExists($this->path);
        }
        $file = $this->path . '/' . base64_encode($name);
        FileHelper::putFile($file, serialize($data));
    }

    /**
     * 获得文件数据
     *
     * @param string $name
     *
     * @return mixed|null
     * @author lengbin(lengbin0@gmail.com)
     */
    public function get($name)
    {
        $file = $this->path . '/' . base64_encode($name);
        if (!is_file($file)) {
            return null;
        }
        $data = @file_get_contents($file);
        return unserialize($data);
    }

    /**
     * 删除文件
     *
     * @param string $name
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    public function del($name)
    {
        $file = $this->path . '/' . base64_encode($name);
        if (is_file($file)) {
            return @unlink($file);
        }
        return false;

    }
}