<?php
/**
 * Created by PhpStorm.
 * User: ice.leng(lengbin@geridge.com)
 * Date: 2016/12/13
 * Time: 下午3:15
 */
namespace lengbin\helper\image;
/**
 * 图片拼接， 支持横竖拼接
 * 以第一张图片为基础图片
 * 如果是横拼接 以第一张图片的宽基础拼接
 * 如果是竖拼接 以第一张图片的长基础拼接
 * 支持自定义长宽
 * 支持定义输出文件名称
 * 支持定义输出文件后缀
 *
 * Class ImageMergeClass
 * @package libs\Img
 * @auth    ice.leng(lengbin@geridge.com)
 *
 *  $img = new \lengbin\helper\image\ImageMergeClass(['img/ice.jpg', 'img/1.jpg']);
 *  $img->generateImage();
 *
 */
class ImageMergeClass extends BaseImageClass implements ImageInterface
{
    /**
     * 水平拼接模式常量
     */
    const MODE_HORIZONTAL = "horizontal";

    /**
     * 垂直拼接模式常量
     */
    const MODE_VERTICAL = "vertical";

    /**
     * 图片组
     */
    private $_files;

    /**
     * 拼接模式
     */
    private $_mode;

    /**
     * 画布
     */
    private $_canvas;

    /*
     * 创建制定的长
     */
    private $_width;

    /*
     * 创建制定的宽
     */
    private $_height;

    /*
     * array 画布长
     */
    private $_canvasWidth;

    /*
     * 间隙
     */
    private $_gap;

    /*
     * array 画布宽
     */
    private $_canvasHeight;

    public function __construct($files)
    {
        $this->_files = $files;
        if( count( $files ) < 1 ) throw new \Exception( '图片拼接至少需要2张图' );
        $file = isset( $files[0] ) ? $files[0] : '';
        parent::__construct( $file );
        $this->setMode();
    }

    /**
     * 设置拼接模式
     *
     * @param string $mode 拼接模式，默认为垂直;
     */
    public function setMode($mode = self::MODE_VERTICAL)
    {
        $this->_mode = $mode;
    }

    /**
     * 拼接图片间隔
     *
     * @param int $gap 单位为px
     */
    public function setGap($gap = 0)
    {
        $this->_gap = $gap;
    }

    /**
     *  根据类型 缩放等比，从第二张开始
     */
    private function _geometricScaling()
    {
        // 从第二张开始，等比缩放
        for( $i = 1; $i < count( $this->_files ); $i++ ){
            $file = $this->_files[ $i ];
            $img = new ImageGeometricScalingClass( $file );
            // 根据类型 缩放等比
            if( $this->_mode == self::MODE_HORIZONTAL ){
                $img->setHeight( $this->_height );
            }else if( $this->_mode == self::MODE_VERTICAL ){
                $img->setWidth( $this->_width );
            }
            $img->setOutputFileName( $this->createFileName() );
            $newFile = $img->generateImage();
            $this->checkFile( $newFile );
            $this->getFileSource( $newFile );
            $this->_canvasWidth[ $i ] = $this->width;
            $this->_canvasHeight[ $i ] = $this->height;
            $this->_files[ $i ] = $newFile;
        }
    }

    /**
     * 创建透明画布,合并图的最下层的画布
     */
    private function _createCanvas()
    {
        $width = 0;
        $height = 0;
        $this->_width = $this->width;
        $this->_height = $this->height;
        $this->_geometricScaling();
        if( $this->_mode == self::MODE_HORIZONTAL ){
            $width = $this->_width + array_sum( $this->_canvasWidth );
            $height = $this->_height;
        }else if( $this->_mode == self::MODE_VERTICAL ){
            $width = $this->_width;
            $height = $this->_height + array_sum( $this->_canvasHeight );
        }
        $this->_canvas = imagecreatetruecolor( $width, $height );
        // 使画布透明
        $white = imagecolorallocate( $this->_canvas, 255, 255, 255 );
        imagefill( $this->_canvas, 0, 0, $white );
        imagecolortransparent( $this->_canvas, $white );
        $this->_canvasWidth[0] = $this->_width;
        $this->_canvasHeight[0] = $this->_height;
    }

    private function _getCanvasDataByIndex($index, $isWidth = true)
    {
        $data = $isWidth ? $this->_canvasWidth : $this->_canvasHeight;
        $num = 0;
        for( $i = 0; $i <= ( $index - 1 ); $i++ ){
            echo $i;
            $num += isset( $data[ $i ] ) ? $data[ $i ] : 0;
        }
        return $num;
    }

    /**
     * 生成图片
     *
     * @param string $outputDir 输出目录， 默认为文件当前目录
     *
     * @return string / array xxx.jpeg / [xxxxx.jpeg, xxxx.jepg]
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    public function generateImage($outputDir = '')
    {
        // 画布
        $this->_createCanvas();
        for( $i = 0; $i < count( $this->_files ); $i++ ){
            $file = $this->_files[ $i ];
            $this->checkFile( $file );
            $this->getFileSource( $file );
            $destX = 0;
            $desyY = 0;
            // 计算当前原图片应该位于画布的哪个位置
            if( $this->_mode == self::MODE_HORIZONTAL ){
                $destX = $this->_getCanvasDataByIndex( $i );
            }elseif( $this->_mode == self::MODE_VERTICAL ){
                $desyY = $this->_getCanvasDataByIndex( $i, false );
            }
            imagecopyresampled( $this->_canvas, $this->source, $destX, $desyY, 0, 0, $this->width, $this->height, $this->width, $this->height );
            if( $i > 0 ) @unlink( $file );
        }
        $outputDir = !empty( $outputDir ) ? $outputDir : dirname( $this->file );
        $fileName = $this->fileName . "_merge." . $this->suffix;
        $this->createImageByCanvas( $this->_canvas, $outputDir . "/{$fileName}" );
        return $outputDir . "/{$fileName}";
    }

    /**
     * 关闭gd2
     */
    public function __destruct()
    {
        if( $this->_canvas != null ){
            imagedestroy( $this->_canvas );
            imagedestroy( $this->source );
        }
    }

}
