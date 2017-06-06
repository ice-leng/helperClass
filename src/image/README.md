# 图片处理帮助类

```php


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
    
```