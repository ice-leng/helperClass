<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/10/3
 * Time: 上午9:25
 */

namespace lengbin\helper\sender;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\FileHelper;
use lengbin\helper\util\cache\CacheHelper;
use lengbin\helper\util\log\LogHelper;
use lengbin\helper\util\ObjectHelper;
use Slince\SmartQQ\Client;
use Slince\SmartQQ\Credential;
use Slince\SmartQQ\Message\Content;
use Slince\SmartQQ\Message\Request\DiscussMessage;
use Slince\SmartQQ\Message\Request\GroupMessage;

/**
 * Class QQSender
 * @package lengbin\helper\sender
 * @author  lengbin(lengbin0@gmail.com)
 */
class QQSender extends ObjectHelper implements SenderInterface
{

    CONST QQ_TYPE_FRIEND = 'friend';
    CONST QQ_TYPE_DISCUSS = 'discuss';
    CONST QQ_TYPE_GROUP = 'group';

    protected $errorName = 'error.log';
    protected $imageName = 'qrcode.png';
    protected $cleanName = 'cleanup.php';
    protected $cacheQQCookie = 'sys.cache.smartQQ.cookie';
    protected $cacheQQChannel = 'sys.cache.sender.smartQQ.channel';

    protected $cacheServer;
    protected $friend;
    protected $discuss;
    protected $group;

    public $errorChangeSendEmail = false;

    protected $path;
    protected $email;
    protected $emailData;

    /**
     * 设置 文件路径
     *
     * @param string $path
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * 获得文件路径
     *
     * @param string $file
     *
     * @return string
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function getPath($file = '')
    {
        if (empty($this->path)) {
            throw new \Exception('not set path');
        }
        if (!is_dir($this->path)) {
            DirHelper::pathExists($this->path);
        }
        return $file ? $this->path . '/' . $file : $this->path;
    }

    /**
     * 获得 qq 的 cookie  信息
     *
     * @return Credential
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function getQQCookie()
    {
        $this->cacheServer = new CacheHelper($this->path);
        $credentialParameters = $this->cacheServer->get($this->cacheQQCookie);
        if (empty($credentialParameters)) {
            $smartQQ = new Client();
            $smartQQ->login($this->getPath($this->imageName));
            $credential = $smartQQ->getCredential();
            $credentialParameters = $credential->toArray();
            $this->cacheServer->set($this->cacheQQCookie, $credentialParameters);
            $this->group = $smartQQ->getGroups();
            $this->discuss = $smartQQ->getDiscusses();
            $this->friend = $smartQQ->getFriends();

        }
        return Credential::fromArray($credentialParameters);
    }

    /**
     * 判断是否有错误文件
     *
     * @return bool
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function checkQQActive()
    {
        return is_file($this->getPath($this->errorName));
    }

    /**
     * 清空缓存
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function cleanup()
    {
        $channels = [
            self::QQ_TYPE_FRIEND,
            self::QQ_TYPE_DISCUSS,
            self::QQ_TYPE_GROUP,
        ];
        foreach ($channels as $channel) {
            $this->cacheServer->del($this->cacheQQChannel . '.' . $channel);
        }
        $this->cacheServer->del($this->cacheQQCookie);
        DirHelper::emptyDir($this->getPath());
    }

    /**
     * 设置 邮箱 服务
     *
     * @param array $sever  [
     *                      'server' => 'smtp.163.com',
     *                      'username' => 'xxxxxx@163.com',
     *                      'password' => 'xxxxxx',
     *                      ]
     *
     * @param array $params [
     *                      'title' => '测试邮箱',
     *                      'form' => ['xxxxxx@163.com' => '11'],
     *                      'email' => 'xxx@qq.com',
     *                      ]
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setEmailServer(array $sever, array $params)
    {
        $this->email = new EmailSender($sever);
        $this->emailData = $params;
    }

    protected function sendEmail($errorInfo)
    {
        if (!empty($this->email) && !empty($this->emailData)) {
            $errorInfo = 'qq消息推送挂啦，[' . $errorInfo . ']';
            $this->email->sender($this->emailData, $errorInfo);
        }
    }

    protected function errorProcess($errorInfo)
    {
        //清空缓存
        $this->cleanup();
        //邮件发送提示
        $this->sendEmail($errorInfo);
        //写文件
        FileHelper::putFile($this->getPath($this->errorName), $errorInfo);
        if (!is_file($this->getPath($this->cleanName))) {
            $cleanupCode = <<<EDF
<?php 
    @unlink('{$this->errorName}');
    echo 'success!';
?>
EDF;
            FileHelper::putFile($this->getPath($this->cleanName), $cleanupCode);
        }
    }

    /**
     * 发送消息
     *
     * @param array  $channel 发送渠道 [ type=>'group', name=> ['xxx'] ]
     * @param string $content 发送消息内容
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function sender(array $channel, $content)
    {
        if ($this->checkQQActive()) {
            if ($this->errorChangeSendEmail) {
                $this->sendEmail($content);
            }
            return;
        }
        try {
            $smartQQ = new Client($this->getQQCookie());
            foreach ($channel as $sender) {
                $type = isset($sender['type']) ? $sender['type'] : '';
                $name = isset($sender['name']) ? $sender['name'] : [];
                if (!empty($type) && !empty($name)) {
                    $key = $this->cacheQQChannel . '.' . $type;
                    $name = is_string($name) ? [$name] : $name;
                    $caches = $this->cacheServer->get($key);
                    switch (strtolower($type)) {
                        case self::QQ_TYPE_DISCUSS:
                            if (empty($caches)) {
                                foreach ($name as $nick) {
                                    $caches[] = $this->discuss->firstByAttribute('name', $nick);
                                }
                                $this->cacheServer->set($key, $caches);
                            }
                            foreach ($caches as $cache) {
                                $message = new DiscussMessage($cache, $content);
                                $smartQQ->sendMessage($message);
                            }
                            break;
                        case self::QQ_TYPE_GROUP:
                            if (empty($caches)) {
                                foreach ($name as $nick) {
                                    $caches[] = $this->group->firstByAttribute('name', $nick);
                                }
                                $this->cacheServer->set($key, $caches);
                            }
                            foreach ($caches as $cache) {
                                $message = new GroupMessage($cache, new Content($content));
                                $smartQQ->sendMessage($message);
                            }
                            break;
                        case self::QQ_TYPE_FRIEND:
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            LogHelper::error($e);
            $this->errorProcess(LogHelper::formatMessage($e, 'error'));
        }
    }
}