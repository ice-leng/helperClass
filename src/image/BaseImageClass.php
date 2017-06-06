<?php
/**
 * Created by PhpStorm.
 * User: ice.leng(lengbin@geridge.com)
 * Date: 2016/12/13
 * Time: 下午5:00
 */
namespace lengbin\helper\image;
class BaseImageClass
{
    /**
     * 源文件
     */
    protected $file;
    /**
     * gd2 文件资源
     */
    protected $source;
    /**
     * 文件名称
     */
    protected $fileName;
    /**
     * 后缀
     */
    protected $suffix;
    /**
     * 宽
     */
    protected $width;
    /**
     * 高
     */
    protected $height;

    /**
     * 检测是否为文件
     *
     * @param $file
     *
     * @throws \Exception
     */
    protected function checkFile($file)
    {
        if( !is_file( $file ) ) throw new \Exception( $file . '文件不存在' );
    }

    /**
     * 通过文件获得文件信息
     *
     * @param resource $file 文件
     */
    protected function getFileInfo($file)
    {
        $path = pathinfo( $file );
        $this->fileName = $path['filename'];
        $this->suffix = $path['extension'];
    }

    /***
     * 通过文件文件资源（类型）
     *
     * @param resource $file 文件
     *
     * @throws \Exception
     */
    protected function getFileSource($file)
    {
        // 图片资源加载
        $pathInfo = getimagesize( $file );
        list( $this->width, $this->height ) = $pathInfo;
        $type = $pathInfo['2'];
        switch( $type ){
            case '1' :
                $this->source = @imagecreatefromgif( $file );
                break;
            case '2' :
                $this->source = @imagecreatefromjpeg( $file );
                break;
            case '3' :
                $this->source = @imagecreatefrompng( $file );
                break;
            default:
                break;
        }
        if( !$this->source ) throw new \Exception( $file . '  文件有问题,请联系管理员' );
    }

    /**
     * 根据后缀名称，通过画布创建指定文件
     *
     * @param resource $canvas 画布
     * @param string   $file   文件名称
     */
    protected function createImageByCanvas($canvas, $file)
    {
        if( $this->suffix == 'jpg' || $this->suffix == 'jpeg' ){
            imagejpeg( $canvas, $file );
        }elseif( $this->suffix == 'png' ){
            imagepng( $canvas, $file );
        }
    }

    public function __construct($file)
    {
        $this->file = $file;
        $this->getFileInfo( $file );
        $this->getFileSource( $file );
    }

    /**
     * 设置长
     *
     * @param int $width 长
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * 设置宽
     *
     * @param int $height 宽
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * 设置输出文件名称
     *
     * @param $fileName
     */
    public function setOutputFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * 设置输出文件后缀
     *
     * @param $suffix
     */
    public function setOutputSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * 生成随机数
     * @return string
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    protected function createFileName()
    {
        return md5( uniqid( microtime( true ), true ) );
    }

}
