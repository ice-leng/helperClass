<?php
/**
 * Created by ice.leng(lengbin0@gmail.com)
 * Date: 2016/1/27
 * Time: 15:55
 */

namespace lengbin\helper\upload;


class UploadFileFactory
{

    /**
     * Instance upload file
     *
     * @param string $name            input file name
     * @param array  $config          upload config
     * @param string $uploadClassPath class path
     *
     * @return mixed
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function getUploadFileInstance($name, $config, $uploadClassPath = '')
    {

        if (empty($uploadClassPath)) {
            $uploadClassPath = 'lengbin\helper\upload\UploadFile';
        }
        $class = new $uploadClassPath($name, $config);

        if (!($class instanceof BaseUploadFile)) {
            throw new \Exception("类{$uploadClassPath}不属于BaseUploadFile的子类，请重新指定");
        }

        return $class;
    }
} 