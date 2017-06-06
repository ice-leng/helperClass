<?php
/**
 * Created by PhpStorm.
 * User: ice.leng(lengbin@geridge.com)
 * Date: 2016/12/15
 * Time: 上午11:16
 */
namespace lengbin\helper\image;
/**
 * 等比缩放类
 *
 * Class ImageScalingClass
 * @package libs\Img
 * @auth    ice.leng(lengbin@geridge.com)
 *
 *  $img = new \lengbin\helper\image\ImageGeometricScalingClass(__DIR__ . '/img/1.jpg');
 *  $img->setWidth(960);
 *  $img->setHeight(1280);
 *  $img->generateImage();
 *
 */
class ImageGeometricScalingClass extends BaseImageClass implements ImageInterface
{

    private $_width  = 0;
    private $_height = 0;

    public function __construct($file)
    {
        parent::__construct( $file );
    }

    /**
     * 设置长
     *
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->_width = $width;
    }

    /**
     * 设置宽
     *
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->_height = $height;
    }

    /**
     * 设置等比例缩放图片比例
     *
     * @param $scale
     *
     * @throws \Exception
     */
    public function setScale($scale)
    {
        if( $scale < 0 || $scale > 1 ) throw new \Exception( '请设置正确的等比缩放比例。比例在0-1之间 ' );
        $this->_width = floor( $this->width * $scale );
        $this->_height = floor( $this->height * $scale );
    }

    /**
     * 固定高度宽度
     * 根据长， 宽设置等比例缩放图片比例。
     * 所以有3种情况
     * 当设定长时，根据长来等比缩放
     * 当设定宽时，根据宽来等比缩放
     * 当设定长和宽时，固定高度宽度
     *
     * @throws \Exception
     */
    private function _geometricScaling()
    {
        if( $this->_width == 0 && $this->_height == 0 ) throw new \Exception( '请设置等比缩放长宽' );
        if( $this->_width > 0 && $this->_height > 0 ){
            return;
        }elseif( $this->_width > 0 ){
            $this->_height = floor( $this->height / ( $this->width / $this->_width ) );
            return;
        }elseif( $this->_height > 0 ){
            $this->_width = floor( $this->width / ( $this->height / $this->_height ) );
            return;
        }
    }

    /**
     * 生成图片
     *
     * @param string $outputDir 输出目录， 默认为文件当前目录
     *
     * @return string xxx.jpeg
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    public function generateImage($outputDir = '')
    {
        $this->_geometricScaling();
        $outputDir = !empty( $outputDir ) ? $outputDir : dirname( $this->file );
        $fileName = $this->fileName . "_scaling." . $this->suffix;
        $thumb = imagecreatetruecolor( $this->_width, $this->_height );
        imagecopyresampled( $thumb, $this->source, 0, 0, 0, 0, $this->_width, $this->_height, $this->width, $this->height );
        $this->createImageByCanvas( $thumb, $outputDir . "/{$fileName}" );
        imagedestroy( $thumb );
        imagedestroy( $this->source );
        return $outputDir . "/{$fileName}";
    }

}