<?php
/**
 * Created by ice.leng(lengbin0@gmail.com)
 * Date: 2016/1/27
 * Time: 15:55
 */

namespace lengbin\helper\upload;

/**
 * 上传资源辅助类
 * 单文件上传示例：
 * $upload=new UploadFile('myfile');
 * UploadFile构造函数有两个参数，第一个是上传文件表单name,第二个可选配置项，具体有：
 * config=[
 *  'uploadDir'=>'上传文件的存放目录',//默认为web可访问目录下的uploads/下
 *  'suffix'=>'是否返回文件的后缀名，默认false，可设置true'
 *  'maxSize'=>'单个文件上传的最大大小，单位字节',//默认2MB
 *  'allowExt'=>['允许的上传文件类型，后缀名数组'],//默认为图片类型
 * 'imagePixel'=>'当为图片上传时，是否验证图片尺寸，默认不验证，当你想验证时，你可以如下设置：'
 *      [
 *          'w'=>值 | 范围值[最小值 , 最大值],
 *          'h'=>值 | 范围值[最小值 , 最大值]
 *      ]
 *
 * ]
 *
 * //文件上传
 * $r=$upload->uploader();//返回值为false上传失败否则返回上传成功的文件信息
 * $r=false | [['orgName'=>'原文件名','newName'=>'新文件名','path'=>'文件存放路径'],...]
 *
 * //获取错误信息
 * $upload->getError();
 *
 *
 * 多文件上传示例：
 * 前端上传表单构造为：
 * <input type='file' name='myfile[]' />
 * <input type='file' name='myfile[]' />
 * 后端：
 * $upload=new UploadFile('myfile');
 * $upload->uploader();
 * 即可实现多文件上传，上传后，用二维数组方式获取已经上传的文件信息。
 *
 * Class UploadFile
 * @package app\common\web
 */

class UploadFile extends BaseUploadFile {

    public function __construct($fileKey,$config=null){
        parent::__construct($fileKey,$config);
    }


    /**
     * 定义具体上传文件的实现
     * @param $uniName
     * @return mixed
     */
    protected function invoke($uniName)
    {
        if(!@move_uploaded_file($this->tmpName,$this->uploadDir.'/'.$uniName)){
            $this->setError(-4);
            return false;
        }
    }
}
