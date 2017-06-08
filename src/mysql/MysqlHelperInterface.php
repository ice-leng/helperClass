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
    public function connect($host, $database, $user, $password);

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
}