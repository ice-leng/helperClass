<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2018/3/2
 * Time: 下午2:39
 */

namespace lengbin\helper\mysql;


/**
 * Class MysqlOrm
 *
 * 数组数据， 默认为 别名
 *
 * @package lengbin\helper\mysql
 */
class MysqlQuery
{
    protected $db;

    protected $select;
    protected $from;
    protected $where;
    protected $join;
    protected $orderBy;
    protected $groupBy;
    protected $having;
    protected $limit;
    protected $offset;
    protected $params;

    /**
     * @param $db
     *
     * @throws \Exception
     */
    public function setDb($db)
    {
        if (!empty($db)) {
            if (!$db instanceof MysqlHelperInterface) {
                throw new \Exception('db error!');
            }
            $this->db = $db;
        }
    }

    /**
     * @return MysqlHelperInterface
     * @throws \Exception
     */
    public function getDb()
    {
        if (empty($this->db)) {
            throw new \Exception('not found db');
        }
        return $this->db;
    }

    /**
     * select
     *
     * 别名
     * 用法一：字符串， 'username as name'
     * 用法二：数组， [‘name‘=>’username’]
     * 用法三：子查询， ['test' => (new MysqlOrm)->from(['t' => 'test'])->where('a.id = t.tid')] / (new MysqlOrm)->from(['t' => 'test'])->where('a.id = t.tid')
     *
     * @param string / array / MysqlOrm $select
     *
     * @return $this
     */
    public function select($select)
    {
        if (!is_array($select)) {
            $select = preg_split('/\s*,\s*/', trim($select), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->select = $select;
        return $this;
    }

    /**
     * add select
     *
     * 别名
     * 用法一： 'username as name'
     * 用法二： [‘name‘=>’username’]
     * 用法三： ['test' => (new MysqlOrm)->from(['t' => 'test'])->where('a.id = t.tid')] / (new MysqlOrm)->from(['t' => 'test'])->where('a.id = t.tid')
     *
     * @param string / array / MysqlOrm $select
     *
     * @return $this
     */
    public function addSelect($select)
    {
        if (!is_array($select)) {
            $select = preg_split('/\s*,\s*/', trim($select), -1, PREG_SPLIT_NO_EMPTY);
        }
        if ($this->select === null) {
            $this->select = $select;
        } else {
            $this->select = array_merge($this->select, $select);
        }
        return $this;
    }

    /**
     * 表单
     *
     * 别名
     *
     * @param string / array $tables
     *
     * @return $this
     */
    public function from($tables)
    {
        if (!is_array($tables)) {
            $tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->from = $tables;
        return $this;
    }

    /**
     * 预处理参数
     *
     * @param array $params
     *
     * @return $this
     */
    protected function addParams($params)
    {
        if (!empty($params)) {
            if ($this->params === null) {
                $this->params = $params;
            } else {
                $this->params = array_merge($this->params, $params);
            }
        }
        return $this;
    }

    /**
     * 条件
     *
     * 支持类型
     *      and
     *      or
     *      like
     *      between
     *      >
     *      >=
     *      =
     *      <
     *      <=
     * 默认为 =；
     *
     * 内数组默认为and连接
     * 使用  [ 'like', 'name', 'leng'], / ['name' => 'aaa']
     *
     * @param string / array $where
     * @param array $params
     *
     * @return $this
     */
    public function where($where, $params = [])
    {
        $this->where = $where;
        $this->addParams($params);
        return $this;
    }

    /**
     * and where
     *
     * @param       $where
     * @param array $params
     *
     * @return $this
     */
    public function andWhere($where, $params = [])
    {
        if ($this->where === null) {
            $this->where = $where;
        } elseif (is_array($this->where) && isset($this->where[0]) && strcasecmp($this->where[0], 'and') === 0) {
            $this->where[] = $where;
        } else {
            $this->where = ['and', $this->where, $where];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * or where
     *
     * @param       $where
     * @param array $params
     *
     * @return $this
     */
    public function orWhere($where, $params = [])
    {
        if ($this->where === null) {
            $this->where = $where;
        } else {
            $this->where = ['or', $this->where, $where];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * 连接
     *
     * 支持 内， 左， 右
     *
     * @param string $type
     * @param string / array $table  ['u' => 'user']
     * @param string $on
     * @param array  $params
     *
     * @return MysqlQuery
     */
    public function join($type, $table, $on = '', $params = [])
    {
        $this->join[] = [$type, $table, $on];
        return $this->addParams($params);
    }

    /**
     * 内连接
     *
     * @param        $table
     * @param string $on
     * @param array  $params
     *
     * @return MysqlQuery
     */
    public function innerJoin($table, $on = '', $params = [])
    {
        return $this->join('INNER JOIN', $table, $on, $params);
    }

    /**
     * 左 连接
     *
     * @param        $table
     * @param string $on
     * @param array  $params
     *
     * @return MysqlQuery
     */
    public function leftJoin($table, $on = '', $params = [])
    {
        return $this->join('LEFT JOIN', $table, $on, $params);
    }

    /**
     * 右 连接
     *
     * @param        $table
     * @param string $on
     * @param array  $params
     *
     * @return MysqlQuery
     */
    public function rightJoin($table, $on = '', $params = [])
    {
        return $this->join('RIGHT JOIN', $table, $on, $params);
    }

    /**
     * group
     * 注意， 这个没有 别名概念
     *
     * @param $group
     *
     * @return $this
     */
    public function groupBy($group)
    {
        if (!is_array($group)) {
            $group = preg_split('/\s*,\s*/', trim($group), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->groupBy = $group;
        return $this;
    }

    /**
     * 添加 分组
     *
     * @param $group
     *
     * @return $this
     */
    public function addGroupBy($group)
    {
        if (!is_array($group)) {
            $group = preg_split('/\s*,\s*/', trim($group), -1, PREG_SPLIT_NO_EMPTY);
        }
        if ($this->groupBy === null) {
            $this->groupBy = $group;
        } else {
            $this->groupBy = array_merge($this->groupBy, $group);
        }
        return $this;
    }

    /**
     * 条件过滤
     * 用法和 where 一样
     * 不支持 like  between
     *
     * @param       $having
     * @param array $params
     *
     * @return $this
     */
    public function having($having, $params = [])
    {
        $this->having = $having;
        $this->addParams($params);
        return $this;
    }

    /**
     * 同上
     *
     * @param       $having
     * @param array $params
     *
     * @return $this
     */
    public function andHaving($having, $params = [])
    {
        if ($this->having === null) {
            $this->having = $having;
        } else {
            $this->having = ['and', $this->having, $having];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * 同上
     *
     * @param       $having
     * @param array $params
     *
     * @return $this
     */
    public function orHaving($having, $params = [])
    {
        if ($this->having === null) {
            $this->having = $having;
        } else {
            $this->having = ['or', $this->having, $having];
        }
        $this->addParams($params);
        return $this;
    }

    /**
     * 排序
     *
     * 格式 只支持 desc 和 asc
     * 默认为 asc
     *
     * 'id desc' / ['id' => 'desc']
     *
     * @param string/array $order
     *
     * @return $this
     */
    public function orderBy($order)
    {
        if (is_string($order)) {
            $order = preg_split('/\s*,\s*/', trim($order), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->orderBy = $order;
        return $this;
    }

    /**
     * 添加排序
     * 规则与 添加排序一致
     *
     * @param $order
     *
     * @return $this
     */
    public function addOrderBy($order)
    {
        if (is_string($order)) {
            $order = explode(' ', $order);
        }
        if ($this->orderBy === null) {
            $this->orderBy = $order;
        } else {
            $this->orderBy = array_merge($this->orderBy, $order);
        }
        return $this;
    }

    /**
     * limit
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }

    /**
     * offset
     *
     * @param int $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = (int)$offset;
        return $this;
    }

    /**
     * create sql
     * @return MysqlQueryMaker
     */
    public function create()
    {
        return new MysqlQueryMaker([
            'params'  => $this->params,
            'select'  => $this->select,
            'from'    => $this->from,
            'join'    => $this->join,
            'where'   => $this->where,
            'groupBy' => $this->groupBy,
            'having'  => $this->having,
            'orderBy' => $this->orderBy,
            'limit'   => $this->limit,
            'offset'  => $this->offset,
        ]);
    }

    /**
     * one
     *
     * @param string $db
     *
     * @return array
     * @throws \Exception
     */
    public function one($db = '')
    {
        $this->setDb($db);
        $query = $this->create();
        return $this->getDb()->one($query->sql(), $query->params());
    }

    /**
     *
     * all
     *
     * @param string $db
     *
     * @return array
     * @throws \Exception
     */
    public function all($db = '')
    {
        $this->setDb($db);
        $query = $this->create();
        return $this->getDb()->all($query->sql(), $query->params());
    }

}