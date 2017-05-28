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
 * Class BaseUploadFile
 * @package common\helpers
 */
abstract class BaseUploadFile {
    private $fileKey;
    protected  $uploadDir='/uploads/';
//    private $allowExt=['jpg','png','jpeg','bmp','gif'];
    private $maxSize=712345678;
    private $allowExt=['image/jpeg','image/png','image/gif','image/bmp'];
    private $errors;
    private $width;
    private $height;

    private $extValidate=false;

    /*************属性*****************/
    private $name;
    private $newName;
    private $size;
    protected $tmpName;
    private $type;
    private $suffix;
    private $is_suffix;
    //图片属性
//    private $_width;
//    private $_height;

    public function __construct($fileKey,$config=null){
        $this->fileKey=$fileKey;

        if(isset($config)){
            if(!empty($config['allowExt'])){
                $this->extValidate=true;
            }

            $pros=get_class_vars(get_class($this));
            foreach($config as $k=>$v){
                if(empty($v)) continue;

                if(array_key_exists($k,$pros)){
                    $this->$k=$v;
                }
            }
            if(!empty($config['imagePixel']) && is_array($config['imagePixel'])){
                $this->width=$config['imagePixel']['w'];
                $this->height=$config['imagePixel']['h'];
            }
            $this->is_suffix=isset($config['suffix']);
        }
    }

    /**
     * 开始上传文件
     * @return array|bool
     */
    public function uploader(){
        if(empty($_FILES[$this->fileKey])){
            $this->setError(-3);
            return false;
        }

        $files=$this->parseFiles();
        if(empty($files)){
            $this->setError(-3);
            return false;
        }

        $uploadedInfo=[];
        foreach($files as $v){
            $r=$this->_upload($v);

            //遇到一个错误就返回了，下步实现如何把已经上传的文件消除并记录所有文件上传错误？
            if(!$r) return false;

            $uploadedInfo[]=$r;
        }

//        return (count($files) == 1) ? $uploadedInfo[0] : $uploadedInfo;
        return $uploadedInfo;
    }

    //解析文件数组
    protected function parseFiles(){
        $infos=$_FILES[$this->fileKey];
        if(is_string($infos['name'])){
            return [$infos];
        }

        //$fileInfos[]=[];
        foreach($infos as $k=>$strs){
//            for($i=0;$i<count($strs);$i++){
//                $fileInfos[$i][$k]=$strs[$i];
//            }
            $i=0;
            foreach($strs as $kk=>$vv){
                $fileInfos[$i][$k]=$vv;
                $i++;
            }
        }

        foreach($fileInfos as $k=>$v){
            if(empty($v['name'])){
                unset($fileInfos[$k]);
            }
        }

        return $fileInfos;
    }

    //实现单个文件上传
    private function _upload($fileInfo){
        $error=$fileInfo['error'];
        $this->name=$fileInfo['name'];
        if(!empty($this->is_suffix)){
            $this->suffix=$this->getSuffix($this->name);
        }

        if($error != 0){
            $this->setError($error);
            return false;
        }

        $this->size=$fileInfo['size'];
        $this->tmpName=$fileInfo['tmp_name'];
        $this->type=$fileInfo['type'];
        $ext=$this->getExt();
        if(!in_array($this->extValidate ? $ext : $this->type,$this->allowExt)){
            $this->setError(-1);
            return false;
        }

        if($this->size > $this->maxSize){
            $this->setError(-2);
            return false;
        }

        $this->uploadDir = $this->uploadDir.'/'.date('Y-m-d');


        $uniName = $this->uniName($ext);

        //检查图片尺寸
        if(!empty($this->width)){
            $w=$fileInfo['width'];
            $h=$fileInfo['height'];

            if(!$this->checkImagePixel($w,$h)) return false;
        }

        //存放目录检查
        if(!$this->dirExists($this->uploadDir)){

            return false;
        }

        //用于upyun继承提取公用
        $this->invoke($uniName);

        $res=['path'=>$this->uploadDir.'/'.$uniName,'newName'=>$uniName,'orgName'=>$this->name,'size'=>$this->size,'url'=>\Yii::$app->urlManager->createAbsoluteUrl('uploads/'.date('Y-m-d')) . '/' . $uniName];
        if(isset($this->suffix)){
            $res['suffix']=$this->suffix;
        }
        return $res;
    }

    /**
     * 定义具体上传文件的实现
     * @param $uniName
     * @return mixed
     */
    protected abstract function invoke($uniName);


    private function checkImagePixel($w,$h){
        //验证宽度
        if(!empty($this->width)){
            if(is_array($this->width)){
                if($w < $this->width[0] || $w > $this->width[1]){
                    $this->setError(-6);
                    return false;
                }
            }else{
                if($w != $this->width){
                    $this->setError(-6);
                    return false;
                }
            }
        }

        //验证高度
        if(!empty($this->height)){
            if(is_array($this->height)){
                if($h < $this->height[0] || $w > $this->height[1]){
                    $this->setError(-6);
                    return false;
                }
            }else{
                if($h != $this->height){
                    $this->setError(-6);
                    return false;
                }
            }
        }


        return true;
    }

    protected function dirExists($dir){
        if(!file_exists($dir)){
            if(!mkdir($dir, 0777, true)){
                $this->setError(-4);
                return false;
            }
        }

        //判断是否可写 -5
        return true;
    }

    private function uniName($ext){
        return md5(uniqid(microtime(true),true)).'.'.$ext;
    }

    private function getExt(){
        return strtolower(pathinfo($this->name,PATHINFO_EXTENSION));
    }

    protected  function setError($code){
        $str="文件<span style='color: #ff0000;'> {$this->name} </span>上传错误，错误原因：";
        switch($code){
            case 1: $str .='上传文件超过了PHP配置文件中允许的最大上传文件大小'; break;
            case 2: $str .='超过了文件最大限制大小'; break;
            case 3: $str .='文件被部分上传'; break;
            case 4: $str .='没有选择上传文件'; break;
            case 6: $str .=''; break;
            case 7: $str .=''; break;
            case 8: $str .=''; break;
            case -1: $str .='非法的文件类型'; break;
            case -2: $str .='上传文件超过最大大小限制'; break;
            case -3: $str ='没有添加上传文件'; break;
            case -4: $str .='上传错误'; break;
            case -5: $str .='上传文件夹没有可写权限，请更换存放目录'; break;
            case -6: $str .='图片尺寸不符合，请重新指定图片'; break;

            default: $str ='未知错误';
        }

        $this->errors[]=$str;
    }

    public function getError(){
//        return count($this->errors) == 1 ? $this->errors[0] : $this->errors;
        return $this->errors;
    }

    private function getSuffix($fn){

        $extend =explode("." , $fn);
        return end($extend);
    }
}
