<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/7/26
 * Time: 下午2:21
 */

namespace lengbin\helper\util\crontab\task;


use lengbin\helper\util\crontab\CrontabTaskInterface;

class DemoTask implements CrontabTaskInterface
{

    /**
     * 执行任务内容
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function task()
    {
        // TODO: Implement task() method.
        // 需要执行的 code
        echo 1;
    }

    /**
     * 执行时间， 每分钟/每秒， 单位时间戳
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function time()
    {
        // 如果不执行， 请返回false
        // 如果 执行， 请返回时间戳
//        return 1 * 60;
        return false;
    }

    /**
     * 执行时间， 定时， 小时的倍数
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function date()
    {
        // 如果不执行， 请返回false
        // 如果 执行， 请返回时间戳
        // 每天 1 点 执行  -> 01000
        return 100000;
    }
}