<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/9/29
 * Time: 下午2:13
 */

namespace app\common\base;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\FileHelper;
use Slince\SmartQQ\Client;
use Slince\SmartQQ\Credential;
use Slince\SmartQQ\Message\Request\DiscussMessage;
use Slince\SmartQQ\Message\Request\GroupMessage;
use Slince\SmartQQ\Message\Content;
use yii\helpers\VarDumper;

class SendTarget extends \yii\log\FileTarget
{
    /**
     * 发送类型 qq, 邮箱 , 短信 , 钉钉, 微信， 其他通信
     */
    CONST SEND_TYPE_QQ = 'qq';
    CONST SEND_TYPE_EMAIL = 'email';
    CONST SEND_TYPE_SMS = 'sms';

    CONST QQ_TYPE_DISCUSS = 'discuss';
    CONST QQ_TYPE_GROUP = 'group';

    /**
     * @var array   [
     *                  enable => true; // 默认为true
     *                  levels => ['info']; // 推送消息级别 默认 info
     *                  // 就讨论组和群， 其他不开放
     *                  sender => [
     *                        [
     *                              type => discuss， // 讨论组
     *                              name => xxxx,    // 名称
     *                        ],
     *                        [
     *                              type => group,    //群
     *                              name => xxxx,     // 名称
     *                        ],
     *                  ],
     *                  //如果不配置邮箱， qq推送过去将不发生邮件提示
     *                  server => 'xxxxx@xxx.com'; // 发送邮箱服务email
     *                  title  => 'qq消息推送挂了', // 标题 默认qq消息推送挂了
     *                  email  => ['xxxxx@xxx.com', 'xxxxx@xxx.com'], // [接收email] 或者 接收email
     *              ]
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public $qq = [];

    /**
     * @var array [
     *                  enable => true; // 默认为true
     *                  levels => ['error']; // 推送消息级别 默认 error
     *                  server => 'xxxxx@xxx.com'; // 发送邮箱服务email
     *                  title  => 'bug追踪',// 标题
     *                  sender => ['app_id' => ['xxxxx@xxx.com', 'xxxxx@xxx.com'], ], // 应用id => 接收email 或者 [接收email] 或者 接收email
     *            ]
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public $email = [];

    /**
     * 系统生成文件，需要可web 访问路径
     *
     * @var string
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public $path = '';

    /**
     * 是否写入文件 默认false
     * @var boolean/int
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public $write = -1;


    protected $errorFileName = 'error.log';
    protected $imageFileName = 'qrcode.png';
    protected $cleanFileName = 'cleanup.php';
    protected $cacheQQCookie = 'sys.error.sender.smartQQ';
    protected $cacheQQGroup = 'sys.error.sender.smartQQ.group';
    protected $id;
    protected $isQQEnable;
    protected $qqLevels;
    protected $qqServer;
    protected $qqTitle;
    protected $qqSender;
    protected $qqEmail;
    protected $isEmailEnable;
    protected $emailLevels;
    protected $emailServer;
    protected $emailTitle;
    protected $emailSender;


    private function _init()
    {
        $this->id = \Yii::$app->id;
        $this->write = $this->write === -1 ? false : $this->write;
        $this->path = $this->path === '' ? \Yii::getAlias('@app') . '/web/qrcode' : $this->path;
        // qq
        $this->isQQEnable = isset($this->qq['enable']) ? $this->qq['enable'] : false;
        $this->qqLevels = isset($this->qq['levels']) ? $this->qq['levels'] : ['info'];
        $this->qqSender = isset($this->qq['sender']) ? $this->qq['sender'] : [];
        $this->qqTitle = isset($this->qq['title']) ? $this->qq['title'] : 'qq消息推送挂了';
        $this->qqServer = isset($this->qq['server']) ? $this->qq['server'] : '';
        $this->qqEmail = isset($this->qq['email']) ? $this->qq['email'] : '';
        //email
        $this->isEmailEnable = isset($this->email['enable']) ? $this->email['enable'] : false;
        $this->emailLevels = isset($this->email['levels']) ? $this->email['levels'] : ['error'];
        $this->emailSender = isset($this->email['sender']) ? $this->email['sender'] : [];
        $this->emailTitle = isset($this->email['title']) ? $this->email['title'] : 'bug追踪';
        $this->emailServer = isset($this->email['server']) ? $this->email['server'] : '';
    }

    /**
     * 获得文件路径
     *
     * @param string $file
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function getPath($file = '')
    {
        DirHelper::pathExists($this->path);
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
        $cache = \Yii::$app->cache;
        $credentialParameters = $cache->get($this->cacheQQCookie);
        if (empty($credentialParameters)) {
            $smartQQ = new Client();
            $smartQQ->login($this->getPath($this->imageFileName));
            $credential = $smartQQ->getCredential();
            $credentialParameters = $credential->toArray();
            $cache->set($this->cacheQQCookie, $credentialParameters);
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
        return is_file($this->getPath($this->errorFileName));
    }

    /**
     * 清空缓存
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function cleanup()
    {
        $channels = [
            self::QQ_TYPE_DISCUSS,
            self::QQ_TYPE_GROUP,
        ];
        foreach ($channels as $channel) {
            \Yii::$app->cache->delete($this->cacheQQGroup . '.' . $channel);
        }
        \Yii::$app->cache->delete($this->cacheQQCookie);
        DirHelper::emptyDir($this->getPath());
    }

    /**
     * 邮件发送提示
     *
     * @param string $type
     * @param string $errorInfo
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function sendEmail($type, $errorInfo)
    {
        switch ($type) {
            case self::SEND_TYPE_QQ:
                $title = $this->qqTitle;
                $server = $this->qqServer;
                $email = is_string($this->qqEmail) ? [$this->qqEmail] : $this->qqEmail;
                $errorInfo = 'qq消息推送挂啦，[' . $errorInfo . ']';
                break;
            case self::SEND_TYPE_EMAIL:
                $title = $this->emailTitle;
                $server = $this->emailServer;
                $emails = $this->emailSender;
                // 是否为字符串
                if (is_string($emails)) {
                    $email = [$emails];
                } else {
                    // 判断数组是一维还是二维数组
                    if (count($emails) == count($emails, 1)) {
                        $email = $emails;
                    } else {
                        $email = isset($emails[$this->id]) ? $emails[$this->id] : [];
                    }
                }
                break;
            default:
                $server = $email = $title = '';
                break;
        }
        // 如果服务和接收人 为空， 不发生
        if (empty($server) || empty($email) || empty($title) || empty($errorInfo)) {
            return;
        }
        \Yii::$app->mailer->compose()
            ->setFrom($server)
            ->setTo($email)
            ->setSubject($title)
            ->setTextBody($errorInfo)
            ->send();
    }

    protected function errorProcess($errorInfo)
    {
        //清空缓存
        $this->cleanup();
        //邮件发送提示
        $this->sendEmail(self::SEND_TYPE_QQ, $errorInfo);
        //写文件
        FileHelper::putFile($this->getPath($this->errorFileName), $errorInfo);
        if (!is_file($this->getPath($this->cleanFileName))) {
            $cleanupCode = <<<EDF
<?php 
    @unlink('{$this->errorFileName}');
    echo 'success!';
?>
EDF;
            FileHelper::putFile($this->getPath($this->cleanFileName), $cleanupCode);
        }
    }


    /**
     * 发送消息
     *
     * @param string $msg
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function sendQQ($msg)
    {
        if ($this->checkQQActive()) {
            return;
        }
        try {
            $cache = \Yii::$app->cache;
            $smartQQ = new Client($this->getQQCookie());
            $groups = $smartQQ->getGroups();
            $discusses = $smartQQ->getDiscusses();
            foreach ($this->qqSender as $sender) {
                $type = isset($sender['type']) ? $sender['type'] : '';
                $name = isset($sender['name']) ? $sender['name'] : '';
                if (!empty($type) && !empty($name)) {
                    $key = $this->cacheQQGroup . '.' . $type;
                    $name = is_string($name) ? [$name] : $name;
                    $caches = $cache->get($key);
                    switch (strtolower($type)) {
                        case self::QQ_TYPE_DISCUSS:
                            if (empty($caches)) {
                                foreach ($name as $nick) {
                                    $caches[] = $discusses->firstByAttribute('name', $nick);
                                }
                                $cache->set($key, $caches);
                            }
                            foreach ($caches as $cache) {
                                $message = new DiscussMessage($cache, $msg);
                                $smartQQ->sendMessage($message);
                            }
                            break;
                        case self::QQ_TYPE_GROUP:
                            if (empty($caches)) {
                                foreach ($name as $nick) {
                                    $caches[] = $groups->firstByAttribute('name', $nick);
                                }
                                $cache->set($key, $caches);
                            }
                            foreach ($caches as $cache) {
                                $message = new GroupMessage($cache, new Content($msg));
                                $smartQQ->sendMessage($message);
                            }
                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
            $errorInfo = date("Y-m-d H:i:s") . ' ' . VarDumper::export($e);
            $this->errorProcess($errorInfo);
        }
    }

    /**
     * Writes log messages to a file.
     */
    public function export()
    {
        // 初始化
        $this->_init();
        //发送级别
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        $msg = substr($text, 0, strpos($text, 'Stack'));
        // qq
        if ($this->isQQEnable) {
            $this->sendQQ($msg);
        }
        // email
        if ($this->isEmailEnable) {
            $this->sendEmail(self::SEND_TYPE_EMAIL, $text);
        }
        // write file
        if ($this->write) {
            parent::export();
        }
    }

}