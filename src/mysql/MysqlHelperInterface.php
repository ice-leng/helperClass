<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/6
 * Time: 上午10:32
 */

namespace lengbin\helper\mysql;

interface MysqlHelperInterface
{

    /**
     * connect mysql / create mysql object
     *
     * @param string $host     host
     * @param string $database db name
     * @param string $user     username
     * @param string $password password
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function connect($host = '', $database = '', $user = '', $password = '');

    /**
     * close
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function close();

    /**
     * 执行
     *
     * @param string $sql    sql  select * from table where id = :id and name like :name
     * @param array  $params 参数 [':id' => '1', ':name' => 'n']
     * @param array  $rule   规则 [':name' => 'like'] || ['name' => 'like']
     *
     * @return object
     * @author lengbin(lengbin0@gmail.com)
     */
    public function execute($sql, array $params = [], array $rule = []);

    /**
     * get one data
     *
     * @param string $sql    select * from table where id = :id
     * @param array  $params [':id' => '1']
     * @param array  $rule   [':name' => 'like'] || ['name' => 'like']
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function one($sql, array $params = [], array $rule = []);

    /**
     * get all data
     *
     * @param string $sql    select * from table where id = :id
     * @param array  $params [':id' => '1']
     * @param array  $rule   [':name' => 'like'] || ['name' => 'like']
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function all($sql, array $params = [], array $rule = []);

    /**
     * 添加
     *
     * @param string $tableName
     * @param array  $data ['id' => 1]
     *
     * @return int
     */
    public function insert($tableName, array $data);

    /**
     * 更新
     *
     * @param string $tableName
     * @param array $data   ['name' => 1]
     * @param array $where  ['id' => 1]
     *
     * @return mixed
     */
    public function update($tableName, array $data, array $where);

    /**
     * batch insert
     *
     * @param string $tableName table name
     * @param array  $fields    [id,name,where]
     * @param array  $params    [ [1,2,3], [2,3,4] ]
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function batchInsert($tableName, array $fields, array $params);

    /**
     * batch update
     *
     * @param string $tableName table name
     * @param array  $fields    need update field  [id, is_delete]
     * @param array  $params    update data   [1=>1, 2=>1, 3=>1]
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function batchUpdate($tableName, array $fields, array $params);

    /**
     * 获得上一次添加的id
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function getLastInsertId();

    /**
     * empty table data
     *
     * @param string / array $tableName  table name
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function truncate($tableName);

    /**
     * page
     *
     * @param string $sql      select * from table where id = :id
     * @param array  $params   [':id' => '1']
     * @param array  $rule     [':name' => 'like'] || ['name' => 'like']
     * @param int    $pageSize limit
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function page($sql, array $params = [], array $rule = [], $pageSize = 10);
}