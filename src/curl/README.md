# 模拟请求 / 爬虫



```php
 use lengbin\helper\curl\Curl;
 use lengbin\helper\curl\Selector;
 
 require_once 'Curl.php';
 require_once 'Selector.php';
 
 $curl = new Curl();
 
 // ip 代理
 $curl->proxy([
     'http://119.5.1.33:808'
 ]);
 
 //登陆
 $curl->login('xxxxx', [
     'username' => '',
     'password' => '',
 ]);
 
 // 是否显示日志
 $curl->isShowLog = true;
 
 //抓取页面
 $indexHtml = $curl->getHtml('http://college.gaokao.com/schlist');
 
 // xpath 匹配
 $locationHtml = Selector::select($indexHtml, './/*[@id=\'wrapper\']/div[1]/p[1]');
 // regex 匹配
 $locations = Selector::select($locationHtml, '/\/schlist\/a(\d+)?\/">(.*)?<\/a>/', 'regex');
 
 $curl->closeCurl();
     
```