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
    private $_time;

    public function __construct()
    {
        $this->_time = time();
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
        $read->setNamespace('\lengbin\helper\util\Crontab\task');
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
    protected function checkTime($taskName, $time)
    {
        if (!$time) {
            return $time;
        }
        $t = $this->getCache($taskName);
        return time() >= $t;
    }

    /**
     * 检查是否到达执行时间
     *
     * @param string $taskName
     * @param int    $date
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function checkDate($taskName, $date)
    {
        if (!$date) {
            return $date;
        }
        $t = $this->getCache($taskName);
        if($t){
            $sysDate = (int)date('YmdHis', $this->_time);
            $taskDate = (int)$t;
        }else{
            $sysDate = (int)date('His', $this->_time);
            $taskDate = (int)$date;
        }
        return $sysDate >= $taskDate;
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
            $reflection = new \ReflectionClass($class);
            $name = $reflection->getShortName();
            $time = $class->time();
            $date = $class->date();
            if (!$time && !$date) {
                $this->setCache($name, "class {$name} not set time, check code", true);
                continue;
            }
            if ($this->checkTime($name, $time) || $this->checkDate($name, $date)) {
                $t =  $date ? (int)date('Ymd000000', strtotime(' +1 d ') ) + $date : $this->_time + $time ;
                try{
                    $class->task();
                    $this->setCache($name, $t);
                }catch (\Exception $e){
                    $this->setCache($name, $e->getMessage(), true);
                }
            }
        }
        $this->setCache(self::LOCK, 0);
    }

}