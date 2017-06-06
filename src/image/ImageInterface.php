<?php
/**
 * Created by PhpStorm.
 * User: ice.leng(lengbin@geridge.com)
 * Date: 16/9/8
 * Time: 下午2:32
 */
namespace lengbin\helper\image;
interface ImageInterface
{

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
    public function generateImage($outputDir = '');

}