# mysql

```php
注意，执行批量添加，批量更新 是没有做预处理，大量数据没必要预处理
注意，没怎么测试哦， 亲，有问题留言

// mysqli 
$mysql = new MysqliHelper();
$mysql->connect('127.0.0.1', 'teacher', 'root', '');
$mysql = MysqliHelper::getInstance('127.0.0.1', 'teacher', 'root', '');

// pdo
$mysql = new PdoMysqlHelper();
$mysql->connect('127.0.0.1', 'teacher', 'root', '');
或者
$mysql = PdoMysqlHelper::getInstance('127.0.0.1', 'teacher', 'root', '');

//$sql = 'select * from demo where id = :id';
//$sql = 'select * from demo where name like :name and id = :id';
$sql = 'select * from demo';

//debug 打印sql
$mysql->isDebug = true;

// find 查询器

$mysql->find()->select('id as i, name as n')->from('demo')->where(['id' => ':id'], [':id' => 1])->all();

或者

$query = new \lengbin\helper\mysql\MysqlQuery();
$query
    ->select('id as i, name as n')
    //->addSelect([
            't' => (new \lengbin\helper\mysql\MysqlQuery())
                            ->select('t')
                            ->from(['x' => 'xxx'])
                            ->where('id=x.a')
            ])
    ->from('demo')
    ->where(['id' => ':id'], [':id' => 1])
    //->andWhere(['>', 'id', '1'])
    //->leftJoin(['a' => 'demo2', 'a.id => id']);
    //->groupBy(['id', 'name']);
    //->having(['>', "count(a.id)", '2'])
    //->oderBy(['id' => 'desc'])
    //->limit(1)
    //->offset(1)
    ->all($mysql);
    //->one($mysql);

//all
var_dump($mysql->all($sql, [':name' => 'h', ':id' => 1], ['name' => 'like']));

//one
var_dump($mysql->one($sql, [':name' => 'h', ':id' => 1], ['name' => 'like']));

// count
var_dump($mysql->count($sql));

//page
var_dump($mysql->page($sql, [], [], 2));

//清空
var_dump($mysql->truncate(['demo']));

//  添加
var_dump($mysql->insert('demo', ['name' => '2', 'created_at' => 2, 'updated_at'=> 2]));

//  更新
var_dump($mysql->update('demo', ['name' => 2, 'created_at' => 2], ['id' => 1]));

// 批量添加
var_dump($mysql->batchInsert('demo', ['id', 'name', 'created_at', 'updated_at'], [[1, 1, time(), time()]]));

// 批量更新
var_dump($mysql->batchUpdate('demo', ['id', 'name', 'created_at', 'updated_at'], [[
    1, 1, 1, 1
]]));
```