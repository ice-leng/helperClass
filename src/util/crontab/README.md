# crontab  计划脚本 

```php
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
```
