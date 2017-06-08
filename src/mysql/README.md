# mysql

```php
注意，直接执行 insert update 是没有做预处理
当前只是做了查询的 预处理

//$mysql = new MysqliHelper();
//$mysql->connect('127.0.0.1', 'teacher', 'root', '');
//$mysql = MysqliHelper::getInstance('127.0.0.1', 'teacher', 'root', '');

//$mysql = new PdoMysqlHelper();
//$mysql->connect('127.0.0.1', 'teacher', 'root', '');
//$mysql = PdoMysqlHelper::getInstance('127.0.0.1', 'teacher', 'root', '');
//$sql = 'select * from demo where id = :id';
//$sql = 'select * from demo where name like :name and id = :id';

//$sql = 'select * from demo';

//debug 打印sql
//$mysql->isDebug = true;

//all
//var_dump($mysql->all($sql, [':name' => 'h', ':id' => 1], ['name' => 'like']));

//one
//var_dump($mysql->one($sql, [':name' => 'h', ':id' => 1], ['name' => 'like']));

// count
//var_dump($mysql->count($sql));

//page
//var_dump($mysql->page($sql, [], [], 2));

//清空
//var_dump($mysql->truncate(['demo']));

// 添加
//var_dump($mysql->batchInsert('demo', ['id', 'name', 'created_at', 'updated_at'], [[1, 1, time(), time()]]));

// 更新
//var_dump($mysql->batchUpdate('demo', ['id', 'name', 'created_at', 'updated_at'], [[
//    1, 1, 1, 1
//]]));
```