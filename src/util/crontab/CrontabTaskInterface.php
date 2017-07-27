<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/7/26
 * Time: 下午1:32
 */

namespace lengbin\helper\util\crontab;

interface CrontabTaskInterface
{
    /**
     * 执行任务内容
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function task();

    /**
     * 执行时间， 每分钟/每秒， 单位时间戳
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function time();

    /**
     * 执行时间， 定时， 单位时间戳
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function date();

}