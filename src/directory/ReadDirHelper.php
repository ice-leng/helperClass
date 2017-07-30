<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/2/6
 * Time: 下午11:24
 */

namespace lengbin\helper\directory;

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
 * @package lengbin\helper\directory
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
    //命名空间
    private $_namespace;
    // 过路目录
    private $_filterDirs = [];
    // 过滤文件
    private $_filterFiles = [];
    // 文件
    private $_files = [];
    // 文件夹
    private $_dirs = [];
    // 是否读取当前目录
    private $_isReadCurrentDir = false;

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
     * 设置   命名空间
     *
     * @param $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
    }

    /**
     * 设置   是否读取当前目录
     * 默认为 全目录
     *
     * @param $isReadCurrentDir
     */
    public function setIsReadCurrentDir($isReadCurrentDir)
    {
        $this->_isReadCurrentDir = $isReadCurrentDir;
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
     * 读取文件夹, 获取文件名称
     *
     * @param $dir
     */
    private function _getFileNames($dir)
    {
        $handle = opendir($dir);
        while (($fileName = readdir($handle)) !== false) {
            // 过滤 过滤目录， 过滤文件
            if ($fileName !== '.' && $fileName !== '..' && !in_array($fileName, $this->_filterDirs)) {
                $path = $dir . '/' . $fileName;
                if (is_dir($path) && !$this->_isReadCurrentDir) {
                    $this->_getFileNames($path);
                } else {
                    if (is_file($path) && !in_array($fileName, $this->_filterFiles)) {
                        if ($this->_isNamespace) {
                            $pathInfo = pathinfo($path);
                            if (!$this->_namespace) {
                                $filePath = substr($pathInfo['dirname'], strpos($pathInfo['dirname'], $this->_rootDirName));
                                $filePath = $filePath . '/' . $pathInfo['filename'];
                                $path = str_replace('/', '\\', $filePath);
                            } else {
                                $path = $this->_namespace . '\\' . $pathInfo['filename'];
                            }
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
        $this->_getFileNames($this->_rootDir);
        return $this->_files;
    }

    /**
     * 读取文件夹, 获取文件名称
     *
     * @param $dir
     */
    private function _getDirNames($dir)
    {
        $handle = opendir($dir);
        while (($fileName = readdir($handle)) !== false) {
            // 过滤 过滤目录， 过滤文件
            if ($fileName !== '.' && $fileName !== '..' && !in_array($fileName, $this->_filterDirs)) {
                $path = $dir . '/' . $fileName;
                if (is_dir($path)) {
                    $this->_dirs[$path] = $path;
                    if ($this->_isReadCurrentDir) {
                        $rootCount = count(explode('/', $this->_rootDir));
                        $pathCount = count(explode('/', $path));
                        if ($pathCount - $rootCount != 1) {
                            unset($this->_dirs[$path]);
                        }
                    }
                    $this->_getDirNames($path);
                }
            }
        }
    }

    /**
     * 获得 文件夹名称
     * @return array
     */
    public function getDirNames()
    {
        $this->_getDirNames($this->_rootDir);
        return $this->_dirs;
    }
}