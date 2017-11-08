<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/10/6
 * Time: 下午5:36
 */

namespace lengbin\helper\util\log;

use lengbin\helper\directory\DirHelper;
use lengbin\helper\directory\FileHelper;
use lengbin\helper\util\ObjectHelper;

class LogHelper extends ObjectHelper
{
    /**
     * 命令行输出颜色配置
     *
     * @param string $string          输出文字
     * @param string $foregroundColor 字体颜色
     * @param string $backgroundColor 背景颜色
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    private static function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $foregroundColors = [
            'black'        => '0;30',
            'dark_gray'    => '1;30',
            'blue'         => '0;34',
            'light_blue'   => '1;34',
            'green'        => '0;32',
            'light_green'  => '1;32',
            'cyan'         => '0;36',
            'light_cyan'   => '1;36',
            'red'          => '0;31',
            'light_red'    => '1;31',
            'purple'       => '0;35',
            'light_purple' => '1;35',
            'brown'        => '0;33',
            'yellow'       => '1;33',
            'light_gray'   => '0;37',
            'white'        => '1;37',
        ];

        $backgroundColors = [
            'black'      => '40',
            'red'        => '41',
            'green'      => '42',
            'yellow'     => '43',
            'blue'       => '44',
            'magenta'    => '45',
            'cyan'       => '46',
            'light_gray' => '47',
        ];

        $coloredString = "";

        if (isset($foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $foregroundColors[$foregroundColor] . "m";
        }

        if (isset($backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . $backgroundColors[$backgroundColor] . "m";
        }

        $coloredString .= $string . "\033[0m" . PHP_EOL;
        return $coloredString;
    }

    /**
     * 命令行显示错误文字
     *
     * @param $message
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function showError($message)
    {
        return self::getColoredString($message, "white", "red");
    }

    /**
     * 命令行提示文字
     *
     * @param $message
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function showInfo($message)
    {
        return self::getColoredString($message, "light_cyan");
    }

    /**
     * Exception 格式化 文字
     *
     * @param string $message 文字
     * @param string $level   等级
     *
     * @return string
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function formatMessage($message, $level)
    {
        $category = '-';
        $text = $message;
        if ($message instanceof \Exception) {
            $className = get_class($message);
            $text = $className . ':' . $message->getMessage() . ' in ' . $message->getFile() . ':' . $message->getLine();
            $category = $className . ':' . $message->getCode();
        }
        return date('Y-m-d H:i:s', time()) . " [$level][$category] $text" . PHP_EOL;
    }

    /**
     * 写入文件
     *
     * @param string $message
     * @param string $level
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected static function write($message, $level)
    {
        $path = dirname(dirname(__DIR__)) . '/runtime/log';
        if (!is_dir($path)) {
            DirHelper::pathExists($path);
        }
        $logFile = $path . '/app.log';
        $text = self::formatMessage($message, $level);
        if (($fp = @fopen($logFile, 'a')) === false) {
            FileHelper::putFile($logFile, '');
        }
        @flock($fp, LOCK_EX);
        clearstatcache();
        if (@filesize($logFile) > 10240 * 1024) {
            self::rotateFiles($logFile);
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }

    /**
     * 如果文件内容过多 自动下一个
     *
     * @param $file
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected static function rotateFiles($file)
    {
        for ($i = 5; $i >= 0; --$i) {
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                if ($i === 5) {
                    @unlink($rotateFile);
                } else {
                    @copy($rotateFile, $file . '.' . ($i + 1));
                    if ($fp = @fopen($rotateFile, 'a')) {
                        @ftruncate($fp, 0);
                        @fclose($fp);
                    }
                }
            }
        }
    }

    /**
     * 错误日志
     *
     * @param $message
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function error($message)
    {
        self::write($message, 'error');
    }

    /**
     * 提示 日志
     *
     * @param $message
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public static function info($message)
    {
        self::write($message, 'info');
    }


}