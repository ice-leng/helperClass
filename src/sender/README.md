# 消息发送， 后期再加入微信，钉钉等其他通讯
 - email
 - qq
 
  
## email
 ```php
     $email = new \lengbin\helper\sender\EmailSender([
         'server' => 'smtp.163.com',
         'username' => 'useranem@163.com',
         'password' => 'password',
     ]);
     
     $email->isHtml = true;
     
     $content = <<<enf
     <!DOCTYPE html>
     <html lang="en">
     <head>
         <meta charset="UTF-8">
         <title>Title</title>
     </head>
     <body>
         Here is an image <img src="https://www.baidu.com/img/bd_logo1.png" alt="Image" />Rest of message
     </body>
     </html>
     enf;
     
     // 注意  form 参数 必须是 username 的邮箱
     
     $status = $email->sender([
         'title' => '测试邮箱', // 邮件标题
         'form' => ['useranem@163.com' => '11'], // 发件人， 可以另名称
         'email' => 'xxxx@qq.com', // 收件邮箱，可以是数组多个
         'attach' => 'path/app.log', // 附件，绝对路径
         'cc' => ['xxxx@qq.com'], //  添加抄送人
         ’bcc’ => ['xxxx@qq.com']], // 添加密送人
         'receipt' => 'xxxx@qq.com'' // 设置邮件回执
     ], $content);
     var_dump($status);
     
     来自：https://github.com/swiftmailer/swiftmailer
         
 ```
## qq

```php
    注意： 如果qq无法发送消息，请到http://w.qq.com/ 发送一条消息
    
    $qq = new \lengbin\helper\sender\QQSender();
    
    $qq->setPath('/Users/lengbin/Documents/www/localhost/helperClassTest/a');
    
    // qq挂了后是否使用邮箱发送
    $qq->errorChangeSendEmail = true;
    
    // 配置 邮箱服务
    $qq->setEmailServer([
        'server'   => 'smtp.163.com',
        'username' => 'xxxx@163.com',
        'password' => 'xxxx',
    ], [
        'title' => '测试邮箱',
        'form'  => ['xxxx@163.com'],
        'email' => 'xxx@qq.com',
    ]);
    
    $content = '哈哈';
    
    $qq->sender([
        [
            'type' => 'discuss',
            'name' => '知识树bug跟踪',
        ]
    ], $content);

    来自： https://github.com/slince/smartqq
    
    composer.json "slince/smartqq": "^2.0"
    
    composer require slince/smartqq
    

        
```