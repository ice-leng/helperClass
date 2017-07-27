<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/7/26
 * Time: 上午11:36
 */

namespace lengbin\helper\util\crontab;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\ReadDirHelper;
use lengbin\helper\util\ObjectHelper;

/**
 * Class Crontab
 *
 * 执行脚本， 请配置Crontab  为 每分钟执行一次
 *
 * $crontab = new Crontab();
 * $crontab->setTaskDir('/xxx/xxx/task');
 * $crontab->setCacheDir('/xxx/xxx/cache');
 * $crontab->run();
 *
 *
 * @package lengbin\helper\util\crontab
 * @author  lengbin(lengbin0@gmail.com)
 */
class Crontab extends ObjectHelper
{

    CONST LOCK = 'lock';

    private $_taskDir;

    public function __construct()
    {
        $taskDir = __DIR__ . '/task';
        $this->setTaskDir($taskDir);
    }

    /**
     * 设置 任务目录
     *
     * @param string $dir 目录名称
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setTaskDir($dir)
    {
        $this->_taskDir = $dir;
    }

    /**
     * 获得任务
     *
     * @return array
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function getTasks()
    {
        if (!is_dir($this->_taskDir)) {
            throw new \Exception("not found dir, this dir {$this->_taskDir} is errr");
        }
        $read = new ReadDirHelper($this->_taskDir);
        $read->setIsNamespace(true);
        return $read->getFileNames();
    }

    /**
     * 目录是否存在， 不存在 自动创建
     *
     * @param string $path
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function pathExists($path)
    {
        if (!is_dir($path)) {
            DirHelper::pathExists($path);
        }
    }

    /**
     * 设置缓存，如果需要修改缓存机制， 继承后重构
     *
     * @param string  $taskName
     * @param         $data
     * @param boolean $isError 是否为错误日志
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setCache($taskName, $data, $isError = false)
    {
        if ($isError) {
            $dir = __DIR__ . '/runtime/error';
            $this->pathExists($dir);
            $file = $dir . "/{$taskName}.log";
        } else {
            $dir = __DIR__ . '/runtime/cache';
            $this->pathExists($dir);
            $file = $dir . "/{$taskName}.dat";
        }
        @file_put_contents($file, $data);
    }

    /**
     * 获得缓存，如果需要修改缓存机制， 继承后重构
     *
     * @param string $taskName
     *
     * @return bool|int|string
     * @author lengbin(lengbin0@gmail.com)
     */
    public function getCache($taskName)
    {
        $dir = __DIR__ . '/runtime/cache';
        $this->pathExists($dir);
        $file = $dir . "/{$taskName}.dat";
        if (!is_file($file)) {
            return 0;
        }
        return @file_get_contents($file);
    }

    /**
     * 检查是否到达执行时间
     *
     * @param string $taskName
     * @param int    $time
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function check($taskName, $time)
    {
        if (!$time) {
            return $time;
        }
        $t = $this->getCache($taskName);
        return time() >= $t ? true : false;
    }

    /**
     * 执行任务
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    public function run()
    {
        if ($this->getCache(self::LOCK)) {
            return;
        }
        $this->setCache(self::LOCK, 1);
        $tasks = $this->getTasks();
        foreach ($tasks as $task) {
            $class = new $task;
            if (!$class instanceof CrontabTaskInterface) {
                continue;
            }
            $time = $class->time();
            $date = $class->date();
            if (!$time && !$date) {
                $this->setCache($task, "class {$task} not set time, check code", true);
                continue;
            }
            if ($this->check($task, $time) || $this->check($task, $date)) {
                $t = time() + ( $date ? 86400 : $time );
                try{
                    $class->task();
                    $this->setCache($task, $t);
                }catch (\Exception $e){
                    $this->setCache($task, $e->getMessage(), true);
                }
            }
        }
        $this->setCache(self::LOCK, 0);
    }

}