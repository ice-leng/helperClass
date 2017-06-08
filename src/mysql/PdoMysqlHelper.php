<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/6
 * Time: 上午10:33
 */

namespace lengbin\helper\mysql;

class PdoMysqlHelper extends BaseMysqlHelper implements MysqlHelperInterface
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
    public function connect($host, $database, $user, $password)
    {
        if (!isset(self::$instanceLink[$this->instanceName])) {
            try {
                $con = new \PDO("mysql:host={$host};dbname={$database}", $user, $password);
                self::$instance[$this->instanceName] = $this;
                self::$instanceLink[$this->instanceName] = $con;
                $this->execute(sprintf("SET NAMES '%s'", $this->charset));
            } catch (\Exception $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return $this;
    }

    /**
     * close
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function close()
    {
        if (self::$instanceLink[$this->instanceName]) {
            self::$instanceLink[$this->instanceName] = null;
        }
    }

    /**
     * 预处理
     *
     * @param string $sql      sql  select * from table where id = :id
     * @param array  $params   参数 [':id' => '1']
     * @param array  $rule     规则  [':name' => 'like'] || ['name' => 'like']
     * @param bool   $isSelect 是否为查询
     *
     * @return object
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _exec($sql, array $params = [], array $rule = [], $isSelect = false)
    {
        $params = $this->getRuleParams($params, $rule);
        parent::query($sql, $params);
        $this->query = self::$instanceLink[$this->instanceName]->prepare($sql);
        if (!$isSelect) {
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $this->query->bindParam($key, $value);
                }
            }
        } else {
            $this->query->execute($params);
        }
        return $this;
    }


    /**
     * 执行
     *
     * @param string $sql      sql  select * from table where id = :id
     * @param array  $params   参数 [':id' => '1']
     * @param array  $rule     规则 [':name' => 'like'] || ['name' => 'like']
     * @param bool   $isSelect 是否为查询
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function execute($sql, array $params = [], array $rule = [], $isSelect = false)
    {
        try {
            $this->_exec($sql, $params, $rule, $isSelect);
            if (!$isSelect) {
                $this->query->execute();
            }
            return $this->query->rowCount();
        } catch (\Exception $e) {
            die("execute failed: " . $e->getMessage());
        }
    }

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
    public function one($sql, array $params = [], array $rule = [])
    {
        $this->execute($sql, $params, $rule, true);
        return $this->query->fetch(\PDO::FETCH_ASSOC);
    }

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
    public function all($sql, array $params = [], array $rule = [])
    {
        $this->execute($sql, $params, $rule, true);
        return $this->query->fetchAll(\PDO::FETCH_ASSOC);
    }

}