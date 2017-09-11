<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/6
 * Time: 上午10:34
 */

namespace lengbin\helper\mysql;

class MysqliHelper extends BaseMysqlHelper implements MysqlHelperInterface
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
            $con = new \mysqli($host, $user, $password);
            if (!$con) {
                die("Connection failed: " . mysqli_connect_error());
            }
            $this->init($host, $database, $user, $password);
            self::$instance[$this->instanceName] = $this;
            self::$instanceLink[$this->instanceName] = $con;
            $con->select_db($database) or die('Could not select database');
            $this->execute(sprintf("SET NAMES '%s'", $this->charset));
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
        if (isset(self::$instanceLink[$this->instanceName]) && !empty(self::$instanceLink[$this->instanceName])) {
            self::$instanceLink[$this->instanceName]->close();
            self::$instanceLink[$this->instanceName] = null;
        }
    }

    /**
     * 转义
     *
     * @param string $string
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _escape($string)
    {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        return self::$instanceLink[$this->instanceName]->real_escape_string($string);
    }

    /**
     * 预处理
     *
     * @param string $sql    sql  select * from table where id = :id
     * @param array  $params 参数 [':id' => '1']
     * @param array  $rule   规则  [':name' => 'like'] || ['name' => 'like']
     *
     * @return object
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _exec($sql, array $params = [], array $rule = [])
    {
        $params = $this->getRuleParams($params, $rule);
        parent::query($sql, $params);
        self::$instanceLink[$this->instanceName]->select_db($this->database) or die('Could not select database');
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_string($value)) {
                    $params[$key] = "'{$this->_escape($value)}'";
                } else {
                    $params[$key] = $this->_escape($value);
                }
            }
            $sql = strtr($sql, $params);
        }
        $this->query = self::$instanceLink[$this->instanceName]->query($sql);
        return $this;
    }

    /**
     * 执行
     *
     * @param string $sql    sql  select * from table where id = :id
     * @param array  $params 参数 [':id' => '1']
     * @param array  $rule   规则 [':name' => 'like'] || ['name' => 'like']
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function execute($sql, array $params = [], array $rule = [])
    {
        try {
            $this->_exec($sql, $params, $rule);
            return self::$instanceLink[$this->instanceName]->affected_rows;
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
        $this->execute($sql, $params, $rule);
        $data = $this->query->fetch_array(MYSQLI_ASSOC);
        $this->query->free();
        return $data;
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
        $this->execute($sql, $params, $rule);
        $data = $this->query->fetch_all(MYSQLI_ASSOC);
        $this->query->free();
        return $data;
    }
}