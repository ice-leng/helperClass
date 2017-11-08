<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/10/25
 * Time: 下午3:45
 */

namespace lengbin\helper\sender;


interface SenderInterface
{
    /**
     * 发送消息
     *
     * @param array  $channel 发送渠道
     * @param string $content 发送消息内容
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function sender(array $channel, $content);
}