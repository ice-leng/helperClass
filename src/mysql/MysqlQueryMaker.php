<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2018/3/4
 * Time: 下午4:00
 */

namespace lengbin\helper\mysql;


/**
 * MySQL 查询 生成器
 * Class MysqlQuery
 * @package lengbin\helper\mysql
 */
class MysqlQueryMaker
{

    protected $params = [];
    protected $sql = [];
    protected $i = 0;
    protected $isGroupBy = false;

    public function __construct($construct)
    {
        foreach ($construct as $method => $data) {
            $this->sql[] = call_user_func([$this, $method . 'Create'], $data);
        }
    }

    protected function indexCreate($index)
    {
        return $this->i = $index === null ? $this->i : $index;
    }

    protected function formatSql($fields)
    {
        $sql = '';
        foreach ($fields as $alias => $field) {
            $name = ',';
            if (!is_int($alias)) {
                $name = ' AS ' . $alias . ',';
            }
            if ($field instanceof MysqlOrm) {
                if (is_int($alias)) {
                    throw new \Exception('su search must alias');
                }
                $query = $field->create();
                $this->params = array_merge($query->params(), $this->params);
                $this->i = $query->i;
                $sql .= '(' . $query->sql() . ')';
            } else {
                $f = explode(' ', $field);
                $f[0] = "`$f[0]`";
                $select = implode(' ', $f);
                $sql .= $select;
            }
            $sql .= $name;
        }
        $sql = substr($sql, 0, -1);
        return $sql;
    }

    protected function selectCreate($selects)
    {
        if ($selects === null) {
            $filed = '*';
        } else {
            $filed = $this->formatSql($selects);
        }
        return 'SELECT ' . $filed;
    }

    protected function fromCreate($from)
    {
        if ($from === null) {
            throw new \Exception('table not empty!');
        }
        return 'FROM ' . $this->formatSql($from);
    }

    protected function joinCreate($joins)
    {
        if ($joins === null) {
            return $joins;
        }
        $sql = '';
        foreach ($joins as $join) {
            $sql .= $join[0] . ' ' . $this->formatSql($join[1]) . ' ON (' . $join[2] . ') ';
        }

        return trim($sql);
    }
    
    public function formatWhere($wheres, $type = '=', $connect = 'AND')
    {
        $sql = '';
        if (is_array($wheres)) {
            foreach ($wheres as $name => $value) {
                $inVal = [];
                if (!is_array($value) && !empty($this->params[$value])) {
                    $val = $value;
                } elseif ($value instanceof MysqlOrm) {
                    $query = $value->create();
                    $query->i = $this->i;
                    $this->params = array_merge($query->params(), $this->params);
                    $val = $query->sql();
                } else {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $v){
                        $val = ':q' . $this->i;
                        $this->i++;
                        if (strtoupper($type) === 'LIKE') {
                            $v = '%' . $v . '%';
                        }
                        if (in_array($type, ['IN', 'NOT IN'])) {
                            $inVal[] = $val;
                        }
                        $this->params[$val] = $v;
                    }

                }
                if (in_array($type, ['BETWEEN', 'NOT BETWEEN'])) {
                    if (in_array($connect, ['BETWEEN', 'NOT BETWEEN'])) {
                        $sql .= $val . $connect;
                    } else {
                        $sql .= "`{$name}` {$type} {$val} " . $connect;
                    }
                } elseif (in_array($type, ['IN', 'NOT IN'])) {
                    $val = implode(',', $inVal);
                    $sql .= " (`{$name}` {$type} ({$val})) " . $connect;
                } else {
                    $sql .= " (`{$name}` {$type} {$val}) " . $connect;
                }
            }
            $sql = substr($sql, 0, strripos($sql, $connect));
        }else{
            $sql .= $wheres;
        }
        return $sql;
    }

    protected function constructWhere($wheres)
    {
        $elementCount = count($wheres);
        $sql = '';
        if ($elementCount > 2) {
            $type = array_shift($wheres);
            $type = strtoupper($type);
            switch ($type) {
                case in_array($type, ['AND', 'OR']):
                    foreach ($wheres as $where) {
                        $sql .= '(' . $this->constructWhere($where) . ') ' . strtoupper($type) . ' ';
                    }
                    $sql = substr($sql, 0, strripos($sql, $type));
                    break;
                case 'LIKE':
                    $sql .= $this->formatWhere([$wheres[0] => $wheres[1]], $type);
                    break;
                case in_array($type, ['BETWEEN', 'NOT BETWEEN']):
                    if (count($wheres) !== 3) {
                        throw new \Exception("this {$type} usage ['BETWEEN', filed, val1, val2']");
                    }
                    $sql .= '(' . $this->formatWhere([$wheres[0] => $wheres[1]], $type) . 'AND ' . $this->formatWhere([$wheres[0] => $wheres[2]], $type, $type) . ')';
                    break;
                case in_array($type, ['IN', 'NOT IN']):
                    if (is_string($wheres[1])) {
                        $wheres[1] = [$wheres[1]];
                    }
                    $sql .= $this->formatWhere([$wheres[0] => $wheres[1]], $type);
                    break;
                case in_array($type, ['>', '>=', '=', '<', '<=']):
                    $sql .= $this->formatWhere([$wheres[0] => $wheres[1]], $type);
                    break;
                default:
                    throw new \Exception("this type {$type} not support!");
                    break;
            }
        } else {
            $status = false;
            $and = 'and';
            if (is_array($wheres)) {
                foreach ($wheres as $name => $values) {
                    if (is_array($values)) {
                        $status = true;
                        $sql .= $this->constructWhere(['in', $name, $values]) . strtoupper($and) . ' ';
                        unset($wheres[$name]);
                    }
                }
            }
            if ($status) {
                $sql = substr($sql, 0, strripos($sql, $and));
            }else {
                $sql .= $this->formatWhere($wheres);
            }
        }
        return $sql;
    }

    protected function whereCreate($wheres)
    {
        if ($wheres === null) {
            return $wheres;
        }
        return 'WHERE ' . $this->constructWhere($wheres);
    }

    protected function groupByCreate($groupBy)
    {
        if ($groupBy === null) {
            return $groupBy;
        }
        $this->isGroupBy = true;
        return 'GROUP BY ' . implode(', ', $groupBy);
    }


    protected function havingCreate($having)
    {
        if ($having === null) {
            return $having;
        }
        if (!$this->isGroupBy) {
            throw new \Exception("use having have must group by");
        }

        return 'HAVING ' . $this->constructWhere($having);
    }

    protected function orderByCreate($orderBys)
    {
        if ($orderBys === null) {
            return $orderBys;
        }
        $data = [];
        $types = ['ASC', 'DESC'];
        foreach ($orderBys as $name => $type) {
            if (is_int($name)) {
                $n = explode(' ', $type);
                if (count($n) > 1) {
                    if (!in_array(strtoupper($n[1]), $types)) {
                        throw new \Exception("order by only support desc or asc");
                    }
                    $d = [$n[0] => strtoupper($n[1])];
                } else {
                    $d = [$n[0] => 'ASC'];
                }
            } else {
                if (!in_array(strtoupper($type), $types)) {
                    throw new \Exception("order by only support desc or asc");
                }
                $d = [$name => strtoupper($type)];
            }
            $data = array_merge($data, $d);
        }
        $sql = '';
        foreach ($data as $filed => $o) {
            $sql .= "`{$filed}` {$o}" . ', ';
        }
        $sql = substr($sql, 0, strripos($sql, ', '));
        return 'ORDER BY ' . $sql;
    }

    protected function limitCreate($limit)
    {
        if ($limit === null) {
            return $limit;
        }
        return 'LIMIT ' . $limit;
    }

    protected function offsetCreate($offset)
    {
        if ($offset === null) {
            return $offset;
        }
        return ', ' . $offset;
    }

    protected function paramsCreate($params)
    {
        if ($params !== null) {
            $this->params = array_merge($params, $this->params);
        }
    }

    public function sql()
    {
        $this->sql = array_filter($this->sql);
        return implode(' ', $this->sql);
    }

    public function params()
    {
        return $this->params;
    }

}