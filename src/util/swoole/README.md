# 工具类

```php

    websocket 客户端
    
      $client = new \lengbin\helper\util\swoole\WebSocket('127.0.0.1', '9502');
      $client->connect();
      $client->send(json_encode('a333a'));  
    
```

