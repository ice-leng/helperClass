# yii2 

```php
    
    SendTarget 第三方消息推送
    支持 qq 消息推送
    支持 邮件 消息推送
    composer require slince/smartqq
    
    
    // 使用方法
    //components 下
    'log' => [
         'traceLevel' => YII_DEBUG ? 3 : 0,
         'targets' => [
             [
                 'class' => 'yii\log\FileTarget',
                 'levels' => ['error', 'warning'],
                 'except' => [
                     'app\common\base\InnerException:-1'  // 自定义异常，不记录日志文件
                 ],
             ],
             [
                 'class' => 'app\common\base\SendTarget',
                 'levels' => ['error', 'warning'],
                 'path'   => '', // path 定义二维码图片生成路径，
                 'qq' => [
                     'enable' =>  true,
                     'sender' => [
                         [
                            'type' => 'discuss',
                            'name' => '知识树bug跟踪',
                         ]
                     ],
                     'title' => 'qq推送挂了',
                     'server' => 'xx@xx.com',
                     'email' => 'xx@xx.com',
                 ],
                 'email' => [
                     'enable' =>  false,
                     'title' => 'bug跟踪',
                     'server' => 'xx@xx.com',
                     'sender' => 'xx@xx.com',
                 ],
             ],
         ],
     ],
        
     // 使用说明
     // 首先让项目 报错，访问二维码，然后登陆qq

     
     /**
      * 发送类型 qq, 邮箱 , 短信 , 钉钉, 微信， 其他通信
      */
     CONST SEND_TYPE_QQ = 'qq';
     CONST SEND_TYPE_EMAIL = 'email';
 
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
    
    
    
    
    
    
```