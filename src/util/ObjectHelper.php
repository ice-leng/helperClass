<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/6/23
 * Time: 上午10:41
 */

namespace lengbin\helper\util;


class ObjectHelper
{
    protected $data = [];

    /**
     * 设置 data
     *
     * @param string $name
     * @param mixed  $value
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _setData($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 获得 data
     *
     * @param string $name
     *
     * @return mixed|null
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _getData($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Set/Get 方法->属性构造
     *
     * @param string $method
     * @param array  $args
     *
     * @return $this|bool|mixed|null
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __call($method, $args)
    {
        $methodType = substr($method, 0, 3);
        $type = strtolower($methodType);
        $attribute = substr($method, 3);
        $name = strtolower($attribute);
        $value = isset($args[0]) ? $args[0] : null;
        switch ($type) {
            case 'set':
                $this->_setData($name, $value);
                return $this;
                break;
            case 'get':
                return $this->_getData($name);
                break;
            case 'has':
                return $this->_getData($name) ? true : false;
                break;
        }
        $message = "Invalid method " . get_class($this) . "::" . $method . "(" . print_r($args, 1) . ")";
        throw new \Exception($message);
    }

    /**
     * 对象 字符串化
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __toString()
    {
        return json_encode($this->data);
    }

    /**
     * set 属性构造
     *
     * @param string $name
     * @param string $value
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __set($name, $value)
    {
        $this->_setData($name, $value);
    }

    /**
     * get 属性构造
     *
     * @param string $name
     *
     * @return mixed|null
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __get($name)
    {
        return $this->_getData($name);
    }

    /**
     * 批量设置 data
     *
     * @param array $params ['id'=>1, 'name' => 'demo']
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setAttributes(array $params)
    {
        foreach ($params as $name => $value) {
            $this->_setData($name, $value);
        }
    }

    /**
     * 获得所有数据
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function getAttributes()
    {
        return $this->data;
    }

    /**
     * 去除 数据
     *
     * @param $name
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function remove($name)
    {
        $data = $this->_getData($name);
        if (!empty($data)) {
            unset($this->data[$name]);
        }
    }

    /**
     * 验证请求参数字段
     * 支持别名
     *
     * @param array        $requests      请求参数  $_GET / $_POST
     * @param array        $validateField 验证字段，支持别名  ['别名' => 字段， 0 => 字段]
     * @param string/array $default       字段默认值 支持自定义 [字段 => 值]
     *
     * @return array
     * @author lengbin(lengbin0@gmail.com)
     */
    public function validateRequestParams($requests, $validateField, $default = '')
    {
        $data = [];
        foreach ($validateField as $key => $field) {
            $param = (isset($requests[$field]) && !empty($requests[$field])) ? $requests[$field] : null;
            if (!empty($default) && $param === null) {
                if (is_array($default)) {
                    $param = (isset($default[$field]) && !empty($default[$field])) ? $default[$field] : null;
                } else {
                    $param = $default;
                }
            }
            if($default === [] && $param === null){
                $param = [];
            }
            if (is_int($key)) {
                $data[$field] = $param;
            } else {
                $data[$key] = $param;
            }
        }
        return $data;
    }


}