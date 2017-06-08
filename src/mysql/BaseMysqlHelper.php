<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/6
 * Time: 上午10:32
 */

namespace lengbin\helper\mysql;

class BaseMysqlHelper
{

    protected $host;
    protected $database;
    protected $user;
    protected $pass;
    protected $instanceName;
    protected $query;

    protected static $instance = [];
    protected static $instanceLink = [];
    protected static $oldInstance = "";

    public $charset = 'utf8';
    public $isDebug = false;

    /**
     * 初始化参数
     *
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $password
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function init($host = '', $database = '', $user = '', $password = '')
    {
        $this->host = $host;
        $this->database = $database;
        $this->user = $user;
        $this->pass = $password;
        $this->instanceName = $host . "_" . $database . "_" . $user;
    }

    /**
     * BaseMysqlHelper constructor.
     *
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $password
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __construct($host = '', $database = '', $user = '', $password = '')
    {
        $this->init($host, $database, $user, $password);
    }

    /**
     * BaseMysqlHelper destructor.
     */
    public function __destruct()
    {
        if (isset(self::$instance[$this->instanceName])) {
            self::$instance[$this->instanceName]->close();
        }
    }

    /**
     * 获得子级类名称
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    private static function _getSubClassName()
    {
        return get_called_class();
    }

    /**
     * 单列模式
     *
     * @param string $host
     * @param string $database
     * @param string $user
     * @param string $pass
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function getInstance($host = '', $database = '', $user = '', $pass = '')
    {
        $s = $host . "_" . $database . "_" . $user;
        if ($s == "__") {
            $s = self::$oldInstance;
        } else {
            self::$oldInstance = $s;
        }
        if (!isset(self::$instance[$s]) || empty(self::$instance[$s])) {
            $class = self::_getSubClassName();
            self::$instance[$s] = new $class($host, $database, $user, $pass);
            self::$instance[$s]->instanceName = $s;
            self::$oldInstance = $s;
        }
        return self::$instance[$s];
    }

    /**
     * debug
     *
     * @param string $sql
     * @param array  $params
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function debug($sql, array $params)
    {
        if (!$this->isDebug) {
            return;
        }
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_string($value)) {
                    $params[$key] = "'$value'";
                } else {
                    $params[$key] = $value;
                }
            }
            $sql = strtr($sql, $params);
        }
        die($sql);
    }

    /**
     * 开启事物
     *
     * @return void
     * @author lengbin(lengbin0@gmail.com)
     */
    public function beginTransaction()
    {
        self::$instance[$this->instanceName]->execute('BEGIN');
    }

    /**
     * 提交
     *
     * @return void
     * @author lengbin(lengbin0@gmail.com)
     */
    public function commit()
    {
        self::$instance[$this->instanceName]->execute('COMMIT');
    }

    /**
     * 回滚
     *
     * @return void
     * @author lengbin(lengbin0@gmail.com)
     */
    public function rollback()
    {
        self::$instance[$this->instanceName]->execute('ROLLBACK');
    }

    /**
     * 预处理
     *
     * @param string $sql    sql  select * from table where id = :id
     * @param array  $params 参数 [':id' => '1']
     *
     * @return void
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function query($sql, array $params = [])
    {
        if (!isset(self::$instanceLink[$this->instanceName])) {
            self::$instance[$this->instanceName]->connect($this->host, $this->database, $this->user, $this->pass);
        }
        $this->debug($sql, $params);
    }

    /**
     * 规则后的参数
     *
     * @param array $params [':id' => '1']
     * @param array $rules  [':name' => 'like'] || ['name' => 'like']
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function getRuleParams(array $params, array $rules)
    {
        if (empty($rules)) {
            return $params;
        }
        foreach ($rules as $name => $rule) {
            $is = explode(':', $name);
            if (count($is) < 2) {
                $name = ':' . $name;
            }
            if ($rule == 'like') {
                if (isset($params[$name]) && !empty($params[$name])) {
                    $params[$name] = '%%' . $params[$name] . '%%';
                }
            }
        }
        return $params;
    }

    /**
     *  count
     *
     * @param string $sql      sql  select * from table where id = :id
     * @param array  $params   参数 [':id' => '1']
     * @param array  $rule     规则 [':name' => 'like'] || ['name' => 'like']
     * @param string $countSql count的sql
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function count($sql, array $params = [], array $rule = [], $countSql = "")
    {
        if ($countSql == "") {
            $pattern = "/^SELECT(.*)FROM/i";
            $replace = "SELECT COUNT(*) AS count FROM";
            $sql = preg_replace($pattern, $replace, $sql);
        } else {
            $sql = $countSql;
        }
        $data = self::$instance[$this->instanceName]->one($sql, $params, $rule);
        return (isset($data['count']) && !empty($data['count'])) ? $data['count'] : -1;
    }

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
    public function page($sql, array $params = [], array $rule = [], $pageSize = 10)
    {
        $start = 0;
        if (isset($_GET['page']) && intval($_GET['page']) > 1 ) {
            $page = intval($_GET['page']);
            $start = ($page - 1) * $pageSize;
        }
        $sql .= " LIMIT $start," . $pageSize;
        return self::$instance[$this->instanceName]->all($sql, $params, $rule);
    }

    /**
     * empty table data
     *
     * @param string / array $tableName  table name
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    public function truncate($tableName)
    {
        if (is_string($tableName)) {
            $tableName = [$tableName];
        }
        $num = 0;
        foreach ($tableName as $name) {
            $sql = "TRUNCATE TABLE {$name};";
            self::$instance[$this->instanceName]->execute($sql);
            $num += 1;
        }
        return $num;
    }

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
    public function batchInsert($tableName, array $fields, array $params)
    {
        $filed = join('`, `', $fields);
        $sql = 'INSERT INTO ' . $tableName . ' (`' . $filed . '`) VALUES ';
        foreach ($params as $param) {
            $p = join('", "', $param);
            $sql .= ' ("' . $p . '"), ';
        }
        $sql = substr($sql, 0, -2);
        return self::$instance[$this->instanceName]->execute($sql);
    }

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
    public function batchUpdate($tableName, array $fields, array $params)
    {
        $filed = join('`, `', $fields);
        $sql = 'INSERT INTO ' . $tableName . ' (`' . $filed . '`) VALUES ';
        foreach ($params as $param) {
            $p = join('", "', $param);
            $sql .= ' ("' . $p . '"), ';
        }
        $sql = substr($sql, 0, -2);
        $sql .= ' ON DUPLICATE KEY UPDATE ';
        foreach ($fields as $field) {
            $sql .= " {$field}= VALUES({$field}), ";
        }
        $sql = substr($sql, 0, -2);
        return self::$instance[$this->instanceName]->execute($sql);
    }
}