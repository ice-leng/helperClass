<?php

/**
 * Class Curl
 * @author lengbin(lengbin0@gmail.com)
 */

namespace lengbin\helper\curl;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\FileHelper;

class Curl
{
    public $isShowLog = false;
    private $_ch;
    private $_loginUrl;
    private $_loginData;
    private $_proxy = [];
    private $_isAjax;
    private $_method;

    /**
     * 设置/获得 cookie 文件 路径， 适用于免登陆
     * @return string cookie文件 路径
     * @author lengbin(lengbin0@gmail.com)
     */
    public function getCookieFile()
    {
        $data = json_encode($this->_loginData);
        $cookieJar = __DIR__ . '/cookie/cookie.tmp';
        $this->_login($cookieJar, $this->_isAjax);
        return $cookieJar;
    }

    /**
     * 登陆信息
     *
     * @param string  $url   登陆 url
     * @param string  $method
     * @param         string / array $data 登陆参数
     * @param boolean $isAjax
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function login($url, $method = 'get', $data = [], $isAjax = false)
    {
        $this->_loginUrl = $url;
        $this->_loginData = $data;
        $this->_isAjax = $isAjax;
        $this->_method = $method;
    }

    /**
     * 模拟登陆
     *
     * @param string  $cookieJar cookie 文件 路径
     * @param boolean $isAjax    是否ajax 请求
     *
     * @return string cookie 文件 路径
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _login($cookieJar, $isAjax)
    {
        if (!file_exists($cookieJar)) {
            FileHelper::putFile($cookieJar, '');
            DirHelper::chmod($cookieJar, 0777);
        } else {
            return $cookieJar;
        }
        $this->initCurl($cookieJar);
        $this->_exec($this->_loginUrl, $this->_method, $this->_loginData, $isAjax);
        $this->closeCurl();
        return $cookieJar;
    }

    /**
     * ip 代理
     * 格式 http://119.5.1.33:808
     *
     * @param array $proxy
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function proxy(array $proxy)
    {
        $this->_proxy = $proxy;
    }

    /**
     * print log
     *
     * @param string $msg
     * @param string $log_type
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _showLog($msg, $log_type = 'info')
    {
        echo date("Y-m-d H:i:s") . " [{$log_type}] " . $msg . "\n";
    }

    /**
     * init curl
     *
     * 模拟请求代理
     * agent: http://taro.iteye.com/blog/1634253
     *
     * @param string $cookieJar
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function initCurl($cookieJar = '')
    {
        $agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.120 Safari/535.2';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        curl_setopt($ch, CURLOPT_TIMEOUT, 240);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $this->_loginUrl);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, 0);
        if (!empty($this->_proxy)) {
            //$proxy = "http://119.5.1.33:808";
            $key = array_rand($this->_proxy, 1);
            curl_setopt($ch, CURLOPT_PROXY, $this->_proxy[$key]);
        }
        $this->_ch = $ch;
    }

    /**
     * 执行
     *
     * @param string  $url
     * @param string  $method
     * @param         string /array   $data
     * @param boolean $isAjax
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    private function _exec($url, $method = 'get', $data = null, $isAjax = false)
    {
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 1);
        if ('POST' === strtoupper($method)) {
            curl_setopt($this->_ch, CURLOPT_POST, 1);
            if (is_array($data)) {
                $data = http_build_query($data);
            }
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
        }
        if ($isAjax) {
            $header = [
                'X-Requested-With:XMLHttpRequest',
            ];
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $header);
        }
        if ($this->isShowLog) {
            $msg = '--url：' . $url . '--method：' . $method . '--params：' . $data;
            $this->_showLog($msg);
        }

        return curl_exec($this->_ch);
    }

    /**
     * 抓取页面
     *
     * @param string  $url
     * @param string  $method
     * @param         string /array $data
     * @param boolean $isAjax
     *
     * @return mixed
     * @author lengbin(lengbin0@gmail.com)
     */
    public function getHtml($url, $method = 'get', $data = '', $isAjax = false)
    {
        try {
            $cookieJar = $this->getCookieFile();
            $this->initCurl($cookieJar);
        } catch (\Exception $e) {
            $this->_showLog($e->getMessage(), 'error');
            $this->closeCurl();
        }
        return $this->_exec($url, $method, $data, $isAjax);
    }

    /**
     * 关闭curl
     * @author lengbin(lengbin0@gmail.com)
     */
    public function closeCurl()
    {
        if (!empty($this->_ch)) {
            curl_close($this->_ch);
        }
        $this->_ch = '';
    }


}