<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/6
 * Time: 上午10:33
 */

namespace lengbin\helper\mysql;

/**
 * Class MysqlHelper
 *
 * php5.5 已经弃用， 请谨慎使用
 * 没有测试，请自行测试， 建议不使用
 *
 * @author lengbin(lengbin0@gmail.com)
 */
class MysqlHelper extends BaseMysqlHelper implements MysqlHelperInterface
{

    public function __construct($host, $database, $user, $password)
    {
        parent::__construct($host, $database, $user, $password);
    }

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
            self::$instanceLink[$this->instanceName] = mysql_connect($host, $user, $password, null, 65536 | 131072) or die('Could not connect: ' . mysql_error());
            mysql_select_db($database, self::$instanceLink[$this->instanceName]) or die('Could not select database');
            $this->execute(sprintf("SET NAMES '%s'", $this->charset));
        }
        return $this;
    }

    /**
     * 转义
     *
     * @param string $string
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    private static function _escape($string)
    {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        return mysql_real_escape_string($string);
    }

    /**
     * 预处理
     *
     * @param       $sql
     * @param array $params
     * @param array $rule
     *
     * @return $this
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _exec($sql, array $params = [], array $rule = [])
    {
        if (!isset(self::$instanceLink[$this->instanceName])) {
            $this->connect($this->host, $this->database, $this->user, $this->pass);
        } else {
            if (!mysql_ping(self::$instanceLink[$this->instanceName])) {
                $this->connect($this->host, $this->database, $this->user, $this->pass);
            }
        }
        $this->debug($sql, $params);
        if (!empty($params)) {
            $search = [];
            $replace = [];
            foreach ($params as $key => $value) {
                $search[] = ":$key";
                $replace[] = self::_escape($value);
            }
            $sql = str_replace($search, $replace, $sql);
        }
        mysql_select_db($this->database, self::$instanceLink[$this->instanceName]) or die('Could not select database');
        $this->query = mysql_query($sql, self::$instanceLink[$this->instanceName]) or die('Invalid query: ' . mysql_error());
        return $this;
    }

    /**
     * 执行
     *
     * @param string $sql      sql  select * from table where id = :id
     * @param array  $params   参数 [':id' => '1']
     * @param array  $rule     规则
     * @param bool   $isUpdate 是否更新操作
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function execute($sql, array $params = [], array $rule = [], $isUpdate = true)
    {
        $this->_exec($sql, $params, $rule);
        if ($isUpdate) {
            $num = mysql_affected_rows($this->query);
        } else {
            $num = mysql_num_rows($this->query);
        }
        return $num;
    }

    /**
     * close
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function close()
    {
        if (isset(self::$instanceLink[$this->instanceName]) && !empty(self::$instanceLink[$this->instanceName])) {
            mysql_close(self::$instanceLink[$this->instanceName]);
            self::$instanceLink[$this->instanceName] = null;
        }
    }

    private function getRow()
    {
        return mysql_fetch_array($this->query, MYSQL_ASSOC);
    }

    /**
     *  count
     *
     * @param string $sql      sql  select * from table where id = :id
     * @param array  $params   参数 [':id' => '1']
     * @param array  $rule     规则
     * @param string $countSql count的sql
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function count($sql, array $params = [], array $rule = [], $countSql = "")
    {
        if ($countSql == "") {
            $pattern = "/^SELECT(.*)FROM/";
            $replace = "SELECT COUNT(*) AS count FROM";
            $sql = preg_replace($pattern, $replace, $sql);
        } else {
            $sql = $countSql;
        }
        $data = $this->_exec($sql, $params, $rule)->getRow();
        return (isset($data['count']) && !empty($data['count'])) ? $data['count'] : -1;
    }

    /**
     * get one data
     *
     * @param string $sql    select * from table where id = :id
     * @param array  $params [':id' => '1']
     * @param array  $rule
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function one($sql, array $params = [], array $rule = [])
    {
        return $this->_exec($sql, $params, $rule)->getRow();
    }

    /**
     * get all data
     *
     * @param string $sql    select * from table where id = :id
     * @param array  $params [':id' => '1']
     * @param array  $rule
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function all($sql, array $params = [], array $rule = [])
    {
        $this->_exec($sql, $params, $rule);
        return mysql_fetch_all($this->query, MYSQL_ASSOC);
    }
}