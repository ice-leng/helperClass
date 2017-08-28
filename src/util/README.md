# 工具类

```php

    我都不知道这些方法该放在那，先定义一个工具类来放
    
    方法如下：
        -httpSplitQuery 和 http_build_query 相反，分解出参数
    
```


```php

    crontab
    
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
     
     
      /**
          * 执行任务内容
          *
          * @author lengbin(lengbin0@gmail.com)
          */
         public function task();
     
         /**
          * 执行时间， 每分钟/每秒， 单位时间戳
          * 如果不执行， 请返回false
          * 如果 执行， 请返回时间戳  1 * 60
          * @return mixed
          * @author lengbin(lengbin0@gmail.com)
          */
         public function time();
     
         /**
          * 执行时间， 定时， 单位时间
          * 如果不执行，请返回false
          * 如果 执行，请返回时间
          * 每天 1 点 执行  ->  010000
          * 每天 12 点 执行  -> 120000
          * 每天 晚上 10点 执行 -> 220000
          *
          * @return mixed
          * @author lengbin(lengbin0@gmail.com)
          */
         public function date();
    
    
    
    
        /***
        ** 模版替换  
        ** 
        */
         $content = (new Template($this->phpFile))
                    ->place('before', 1)
                    ->place('case',2 )
                    ->produce();
        
        
        /**
         * Class ZipHelper
         * 文件压缩， 解压帮助类
         *
         *  //压缩
         *  $zip = new ZipHelper();
         *  $zip->setPath('/Users/lengbin/Documents/ruby');
         *  //是否下载
         *  $zip->setIsDownload(true);
         *  // 是否删除文件（zip文件，压缩时候的文件）
         *  $zip->setIsDelete(true);
         *  // 压缩文件， 支持 文件夹， 文件， 网路文件
         *  $zip->zip('test', [
         *      '/Users/lengbin/Documents/ruby/demo',
         *      'http://www.sostudy.cn/images/newhxs/bg.jpg'
         *  ]);
         *
         *  //解压
         *  $zip = new ZipHelper();
         *  $zip->setPath('/Users/lengbin/Documents/ruby');
         *  // 是否删除zip文件
         *  $zip->setIsDelete(true);
         *  // 解压
         *  $zip->unzip('test');
         *
         * @package api\controllers
         * @author  lengbin(lengbin0@gmail.com)
         */
    
```