<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/2/6
 * Time: 下午11:24
 */

namespace common\helpers;

/**
 * 读取目录帮助类，
 * 可以指定目录获取文件
 * 获得文件 namespace
 * 定义排除目录，文件
 *
 * $readDir = new ReadDirHelper( $dir )
 * $readDir->getFileNames();
 *
 *
 * 后续改进：
 *      返回可以根据目录结构返回
 *      返回可以获得文件目录 / 文件结构目录
 *
 * @package common\helpers
 */
class ReadDirHelper
{
    // 根目录
    private $_rootDir;
    //根目录名称
    private $_rootDirName;
    // 指定目录
    private $_targetDir = [];
    //是否输出全路径文件名称
    private $_isNamespace = false;
    // 过路目录
    private $_filterDirs = [];
    // 过滤文件
    private $_filterFiles = [];
    // 文件
    private $_files = [];

    public function __construct($rootDir)
    {
        if (!is_dir($rootDir)) {
            throw new \Exception("目录：{$rootDir}, 不存在");
        }
        $this->_rootDir = $rootDir;
    }

    /**
     * 设置 指定目录
     *
     * @param array $targetDir
     */
    public function setTargetDir(array $targetDir)
    {
        $this->_targetDir = $targetDir;
    }

    /**
     * 设置   返回数据为命名空间
     * 默认为 全路径
     *
     * @param $isNamespace
     */
    public function setIsNamespace($isNamespace)
    {
        $this->_isNamespace = $isNamespace;
    }

    /**
     * 设置 过滤目录
     *
     * @param array $filterDirs
     */
    public function setFilterDirs(array $filterDirs)
    {
        $this->_filterDirs = $filterDirs;
    }

    /**
     * 设置 过滤文件
     *
     * @param array $filterFiles
     */
    public function setFilterFiles(array $filterFiles)
    {
        $this->_filterFiles = $filterFiles;
    }

    /**
     * 读取文件
     *
     * @param $dir
     */
    private function _readDir($dir)
    {
        $handle = opendir($dir);
        while (($fileName = readdir($handle)) !== false) {
            // 过滤 过滤目录， 过滤文件
            if ($fileName != "." && $fileName != ".." && !in_array($fileName, $this->_filterDirs) && !in_array($fileName, $this->_filterFiles)) {
                $path = $dir . '/' . $fileName;
                if (is_dir($path)) {
                    $this->_readDir($path);
                } else {
                    if (is_file($path)) {
                        if ($this->_isNamespace) {
                            $pathInfo = pathinfo($path);
                            $filePath = substr($pathInfo['dirname'], strpos($pathInfo['dirname'], $this->_rootDirName));
                            $filePath = $filePath . '/' . $pathInfo['filename'];
                            $path = str_replace('/', '\\', $filePath);
                        }
                        // 是否存在 目标目录， 当前目录是否在目标目录中， 如果没有 continue
                        if (!empty($this->_targetDir) && !in_array(basename($dir), $this->_targetDir)) {
                            continue;
                        }
                        $this->_files[$path] = $path;
                    }
                }
            }
        }
    }

    /**
     * 获得 文件名称
     * @return array
     */
    public function getFileNames()
    {
        $this->_rootDirName = $this->_isNamespace ? basename($this->_rootDir) : $this->_rootDir;
        $this->_readDir($this->_rootDir);
        return $this->_files;
    }
}