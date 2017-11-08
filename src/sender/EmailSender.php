<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/10/25
 * Time: 下午3:14
 */

namespace lengbin\helper\sender;


use lengbin\helper\util\log\LogHelper;
use lengbin\helper\util\ObjectHelper;

/**
 * Class EmailSender
 * @package lengbin\helper\sender
 * @author  lengbin(lengbin0@gmail.com)
 */
class EmailSender extends ObjectHelper implements SenderInterface
{


    /**
     * 配置 transport
     *
     * @param array $config     [
     *                          server => '',
     *                          username => '',
     *                          password => '',
     *                          port => '',
     *                          ]
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __construct(array $config = [])
    {
        $this->init($config);
    }

    /**
     * 配置 transport
     *
     * @param array $config     [
     *                          server => '',
     *                          username => '',
     *                          password => '',
     *                          port => '',
     *                          ]
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function init($config)
    {
        $this->setAttributes($config);
    }

    /**
     * 建立通信
     *
     * @return \Swift_SmtpTransport
     * @author lengbin(lengbin0@gmail.com)
     * @throws \Exception
     */
    protected function transport()
    {
        try {
            $port = !empty($this->port) ? $this->port : 25;
            $transport = (new \Swift_SmtpTransport($this->server, $port))
                ->setUsername($this->username)
                ->setPassword($this->password);
            if (!empty($this->encryption)) {
                $transport->setEncryption($this->encryption);
            }
            return $transport;
        } catch (\Exception $e) {
            LogHelper::error($e);
            throw $e;
        }
    }


    /**
     * 发送消息
     *
     * @param array  $channel  发送渠道  [
     *                                  title => 'xxxx',
     *                                  email => ['xxx'@xxx.com]
     *                                  ]
     * @param string $content  发送消息
     *
     * @return int
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    public function sender(array $channel, $content)
    {
        if (empty($channel) || empty($content)) {
            return false;
        }
        $mailer = new \Swift_Mailer($this->transport());
        $message = (new \Swift_Message());

        $form = isset($channel['form']) && !empty($channel['form']) ? $channel['form'] : $this->username;
        $charset = !empty($this->charset) ? $this->charset : 'utf8';
        $subject = !empty($channel['title']) ? $channel['title'] : $this->title;
        $contentType = $this->isHtml ? 'text/html' : 'text/plain';

        $message->setSubject($subject)
            ->setTo($channel['email'])
            ->setFrom($form)
            ->setBody($content, $contentType, $charset);

        // 添加附件 只支持一个
        if (isset($channel['attach'])) {
            if (is_string($channel['attach'])) {
                $channel['attach'] = [$channel['attach']];
            }
            foreach ($channel['attach'] as $name => $path) {
                $attachment = \Swift_Attachment::fromPath($path);
                if ($name !== 0) {
                    $attachment->setFilename($name)
                        ->setDisposition('inline');
                }
                $message->attach($attachment);
                break;
            }
        }

        // 添加抄送人
        if (isset($channel['cc'])) {
            $message->setCc($channel['cc']);
        }

        // 添加密送人
        if (isset($channel['bcc'])) {
            $message->setBcc($channel['bcc']);
        }

        // 设置邮件回执
        if (isset($channel['receipt'])) {
            $message->setReadReceiptTo($channel['receipt']);
        }

        try {
            $result = $mailer->send($message);
            return $result;
        } catch (\Exception $e) {
            LogHelper::error($e);
            throw $e;
        }
    }
}