<?php
/**
 * Created by PhpStorm.
 * User: ice.leng(lengbin@geridge.com)
 * Date: 16/9/8
 * Time: 下午2:32
 */
namespace lengbin\helper\image;
/**
 * 九宫格图片生成类
 * 支持自定义长宽
 * 支持定义输出文件名称
 * 支持定义输出文件后缀
 * Class ImageScratchableLatexClass
 * @package libs\Img
 * @auth    ice.leng(lengbin@geridge.com)
 *
 *  $img  = new ImageScratchableLatexClass($file);
 *  $data = $img->generateImage();
 */
class ImageScratchableLatexClass extends BaseImageClass implements ImageInterface
{

    public function __construct($file)
    {
        parent::__construct( $file );
    }

    /***
     * 生成图片
     *
     * 横竖 3 等分切割 生成图片
     *
     * @param string $outputDir 输出目录， 默认为文件当前目录
     *
     * @return array [xxxxx.jpeg, xxxx.jepg]
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    public function generateImage($outputDir = '')
    {
        $imgs = [];
        $h = floor( $this->height / 3 );
        $w = floor( $this->width / 3 );
        $outputDir = !empty( $outputDir ) ? $outputDir : dirname( $this->file );
        for( $col = 0; $col < 3; $col++ ){
            for( $row = 0; $row < 3; $row++ ){
                $fn = sprintf( $this->fileName . "_%02d_%02d." . $this->suffix, $col, $row );
                $thumb = imagecreatetruecolor( $w, $h );
                imagecopyresized( $thumb, $this->source, 0, 0, $col * $w, $row * $h, $this->width, $this->height, $this->width, $this->height );
                $this->createImageByCanvas( $thumb, $outputDir . "/{$fn}" );
                imagedestroy( $thumb );
                $imgs[] = $outputDir . "/{$fn}";
            }
        }
        return $imgs;
    }

    /**
     * 关闭gd2
     */
    public function __destruct()
    {
        if( $this->source != null ){
            imagedestroy( $this->source );
        }
    }
}