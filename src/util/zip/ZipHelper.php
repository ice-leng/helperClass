<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/8/28
 * Time: 上午10:34
 */

namespace lengbin\helper\util\zip;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\Filehelper;
use lengbin\helper\directory\ReadDirHelper;

/**
 * Class ZipHelper
 * 文件压缩， 解压帮助类
 *
 *  //压缩
 *  $zip = new ZipHelper();
 *  $zip->setPath('/Users/lengbin/Documents/ruby');
 *  //是否下载
 *  $zip->setIsDownload(true);
 *  // 是否删除文件（zip文件，压缩时候的文件）
 *  $zip->setIsDelete(true);
 *  // 压缩文件， 支持 文件夹， 文件， 网路文件
 *  $zip->zip('test', [
 *      '/Users/lengbin/Documents/ruby/demo',
 *      'http://www.sostudy.cn/images/newhxs/bg.jpg'
 *  ]);
 *
 *  //解压
 *  $zip = new ZipHelper();
 *  $zip->setPath('/Users/lengbin/Documents/ruby');
 *  // 是否删除zip文件
 *  $zip->setIsDelete(true);
 *  // 解压
 *  $zip->unzip('test');
 *
 * @package api\controllers
 * @author  lengbin(lengbin0@gmail.com)
 */
class ZipHelper
{

    private $_isDownload = false;
    private $_isDelete = false;
    private $_filterDirs = [];
    private $_filterFiles = [];
    private $_path;

    /**
     * ZipHelper constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('php extension zip not found, place install');
        }
    }

    /**
     * 设置 是否下载
     *
     * @param boolean $isDownload
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setIsDownload($isDownload)
    {
        $this->_isDownload = $isDownload;
    }

    /**
     * 设置是否删除
     *
     * @param boolean $isDelete
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setIsDelete($isDelete)
    {
        $this->_isDelete = $isDelete;
    }

    /**
     * zip file path
     *
     * @param $path
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setPath($path)
    {
        $this->_path = $path;
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
     * 获得有效数据
     *
     * @param string $filename
     *
     * @return string
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function getFile($filename)
    {
        if (!$this->_path) {
            throw new \Exception('place set zip file path');
        }
        $fileNames = explode(',', $filename);
        if (count($fileNames) > 1) {
            $filename = $fileNames[0];
        }
        return $this->_path . '/' . $filename . '.zip';
    }

    /**
     * 删除文件
     *
     * @param string $zip
     * @param array  $files
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function deleteFile($zip = '', array $files = [])
    {
        if (!empty($zip)) {
            if (!file_exists($zip)) {
                throw new \Exception('zip file [' . $zip . '] not found, place confirm？');
            }
            @unlink($zip);
        }

        if (!empty($files)) {
            foreach ($files as $file){
                if(is_dir($file)){
                    DirHelper::emptyDir($file, true);
                }else{
                    @unlink($file);
                }
            }
        }
    }

    /**
     * 正则
     *
     * @param string $url
     *
     * @return int
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function checkUrl($url)
    {
        $rule = "/((http|https):\/\/)?\w+\.(jpg|jpeg|gif|png)/";
        preg_match($rule,$url,$result);
        return $result;
    }

    /**
     * 压缩文件 / 已有压缩添加文件
     *
     * @param string $filename zip 文件名称
     * @param        array     / string  $data 压缩文件 支持，绝对路径的文件夹，绝对路径的文件， 网路文件
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    public function zip($filename, array $data)
    {
        $file = $this->getFile($filename);
        $zip = new \ZipArchive();
        $zip->open($file, \ZIPARCHIVE::CREATE);
        foreach ($data as $kye => $val) {
            $dirName = '';
            if (is_dir($val)) {
                //文件夹
                $info = pathinfo($val);
                $dirName = isset($info['basename']) ? $info['basename'] : '';
                $readDir = new ReadDirHelper($val);
                $files = $readDir->getFileNames();
            } elseif (is_file($val)) {
                //文件
                $files = [$val];
            } elseif ($this->checkUrl($val)) {
                //url 文件
                $info = pathinfo($val);
                $basename = isset($info['basename']) ? $info['basename'] : '';
                $urlFile= $this->_path . '/download/' . $basename;
                Filehelper::putFile($urlFile, Filehelper::getFile($val));
                $data[$kye] = $this->_path . '/download';
                $files = [$urlFile];
            } else {
                throw new \Exception('this file [' . $val . '] not support zip！');
            }
            foreach ($files as $f) {
                if(!empty($dirName)){
                    $zip->addFile($f, $dirName . '/' . basename($f));
                }else{
                    $zip->addFile($f, basename($f));
                }
            }
        }
        $zip->close();
        if (!file_exists($file)) {
            throw new \Exception('zip file create failed, place confirm permission and file ?');
        }

        // 是否下载
        if ($this->_isDownload) {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . basename($file)); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: ' . filesize($file)); //告诉浏览器，文件大小
            @readfile($file);
        }

        //是否删除文件
        if ($this->_isDelete) {
            $this->deleteFile($file, $data);
        }
    }

    /**
     *
     * 解压文件
     *
     * @param string $filename    zip 文件名称
     * @param string $password    zip 解压密码
     * @param string $extractPath 提取目录，如果没有设置，默认为zip文件目录
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    public function unzip($filename, $extractPath = '', $password = '')
    {
        $file = $this->getFile($filename);
        if (!file_exists($file)) {
            throw new \Exception('zip file [' . $file . '] not found, place confirm？');
        }
        $zip = new \ZipArchive();
        if (!empty($password)) {
            $zip->setPassword($password);
        }
        $re = $zip->open($file);
        if ($re !== true) {
            throw new \Exception('zip file [' . $file . '] not open, place confirm？');
        }
        $path = empty($extractPath) ? $this->_path : $extractPath;
        DirHelper::pathExists($path);
        if (!is_dir($path)) {
            throw new \Exception('this path [' . $path . '] is illegal path！');
        }
        $zip->extractTo($path);
        $zip->close();
        // 是否删除文件
        if ($this->_isDelete) {
            $this->deleteFile($file);
        }

    }
}