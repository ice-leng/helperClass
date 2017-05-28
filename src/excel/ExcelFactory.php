<?php
/**
 * Created by ice.leng(lengbin@geridge.com)
 * Date: 2016/1/29
 * Time: 9:19
 */

namespace lengbin\helper\excel;

/**
 * excel  工厂类
 * 初始化对象不属于命名空间， 默认为 工厂类同级文件
 * Class ExcelFactory
 * @package common\helpers
 */
class ExcelFactory
{
    /**
     * 导出类
     *
     * @param string $className 对象名称
     * @param string $config    配置参数
     *
     * @return object
     * @auth ice.leng(lengbin@geridge.com)
     * @throws \Exception
     * @issue
     *
     */
    public static function exportInstance($className, $config)
    {
        if (count(explode('\\', $className)) <= 1) {
            $className = __NAMESPACE__ . "\\" . $className;
        }
        $class = new $className($config);
        if (!$class instanceof BaseExport) {
            throw new \Exception("类{$class}不属于BaseExport的子类，请重新指定");
        }
        return $class;
    }

    /**
     * 导入类
     *
     * @param string $className 对象名称
     * @param string $file      文件路径
     * @param array  $config    配置参数
     *
     * @return object
     * @auth ice.leng(lengbin@geridge.com)
     * @throws \Exception
     * @issue
     */
    public static function importInstance($className, $file, $config)
    {
        if (count(explode('\\', $className)) <= 1) {
            $className = __NAMESPACE__ . "\\" . $className;
        }
        $class = new $className($file, $config);
        if (!$class instanceof BaseImport) {
            throw new \Exception("类{$class}不属于BaseImport的子类，请重新指定");
        }
        return $class;
    }

}