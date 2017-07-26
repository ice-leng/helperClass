<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/7/24
 * Time: 下午1:30
 */

namespace lengbin\helper\directory;

use lengbin\helper\mysql\PdoMysqlHelper;

/**
 * 版本自动更新
 * 自备 上传文件/sql更新文件/回滚文件
 *
 * 目录结构
 * upgrade
 *     -20160101121212（目录）
 *          -code
 *               -代码文件
 *          -sql
 *              -update.sql
 *              -back.sql
 *          -compare
 *              -代码文件
 *          -resource
 *              - xxx.tar.gz
 *     - version.log
 *-----------------------------------
 *  将 自动生成
 *      resource  （当前版本所有代码打包）
 *      compare     当前版本于需要升级的文件，将当前代码备份 ）
 *      version.log 执行过得版本日志
 *  目录
 *
 *  使用命令
 *      $update = new UpdateFileHelper($src, $dst);
 *
 *      //上传文件 和 执行sql
 *      $update->auto($upgradeType);
 *      //上传文件
 *      $update->code($upgradeType);
 *      //执行sql
 *      $update->sql($upgradeType);
 *      //文件比较
 *      $update->compare();
 *
 *      yii update/auto
 *      yii update/code
 *      yii update/sql
 *      yii update/compare
 *  参数
 *      $src 升级目录（必须为绝对路径）
 *      $dst 项目更新路径（必须为绝对路径）
 *      $upgradeType 更新类型 update/back/compare  可以自定义名称
 *     （code目录, sql目录，compare目录， resource目录）目录名称可以自定义
 *
 * @package lengbin\helper\directory
 */
class UpdateFileHelper
{
    // 更新
    CONST  UPGRADE_UPDATE = 'update';
    // 回滚
    CONST  UPGRADE_BACK = 'back';
    // 文件对比
    CONST UPGRADE_COMPARE = 'compare';
    // upgrade code目录
    CONST UPGRADE_CODE_DIR_NAME = 'code';
    // upgrade code目录
    CONST UPGRADE_SQL_DIR_NAME = 'sql';
    //斜线
    CONST DIRECTORY_SEPARATOR = '/';

    // 是否验证升级类型过
    private $_isValidateUpgradeType = false;
    // 升级绝对路径
    private $_upgradeDir;
    //项目更新路径
    private $_updateDir;
    // sql 文件夹名称
    private $_upgradeSqlDirName;
    // code 文件夹名称
    private $_upgradeCodeDirName;
    // 升级类型
    private $_upgradeType;
    // 升级类型
    private $_upgradeTypes;
    // 升级类型 - 更新
    private $_upgradeTypeUpdate;
    // 升级类型 - 回滚
    private $_upgradeTypeBack;
    // 升级类型 - 对比
    private $_upgradeTypeCompare;
    // 过路目录
    private $_upgradeFilterDirs;
    // 过滤文件
    private $_upgradeFilterFiles;
    // 是否更新， 默认为 是
    private $_isUpdate = true;
    // 版本
    private $_version;
    // 版本log
    private $_versionLog;
    //是否写入版本log
    private $_isWriteVersionLog = true;
    //mysql
    private $_db;

    /**
     * UpdateFileHelper constructor.
     *
     * @param string $src 升级目录 （必须为绝对路径）
     * @param string $dst 项目更新路径（必须为绝对路径）
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function __construct($src = '', $dst = '')
    {
        $this->_upgradeDir = $src;
        $this->_updateDir = $dst;
        $this->setUpgradeTypeUpdateName(self::UPGRADE_UPDATE);
        $this->setUpgradeTypeBackName(self::UPGRADE_BACK);
        $this->setUpgradeTypeCompareName(self::UPGRADE_COMPARE);
        $this->setUpgradeCodeDirName(self::UPGRADE_CODE_DIR_NAME);
        $this->setUpgradeSqlDirName(self::UPGRADE_SQL_DIR_NAME);
        $this->setUpgradeFilterDirs();
        $this->setUpgradeFilterFiles([
            'main-local.php',
            'params-local.php',
        ]);
    }

    /**
     * 升级类型 - 更新名称
     *
     * @param string $name
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeTypeUpdateName($name)
    {
        $this->_upgradeTypeUpdate = $name;
    }

    /**
     * 升级类型 - 回滚名称
     *
     * @param string $name
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeTypeBackName($name)
    {
        $this->_upgradeTypeBack = $name;
    }

    /**
     * 升级类型 - 对比名称
     *
     * @param string $name
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeTypeCompareName($name)
    {
        $this->_upgradeTypeCompare = $name;
    }

    /**
     * 升级目录 - 代码目录名称
     *
     * @param string $name
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeCodeDirName($name)
    {
        $this->_upgradeCodeDirName = $name;
    }

    /**
     * 升级目录 - sql目录名称
     *
     * @param string $name
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeSqlDirName($name)
    {
        $this->_upgradeSqlDirName = $name;
    }

    /**
     * 升级过滤 - 目录
     *
     * @param array $dirs
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeFilterDirs(array $dirs = [])
    {
        $this->_upgradeFilterDirs = $dirs;
    }

    /**
     *
     * 升级过滤 - 文件
     *
     * @param array $files
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setUpgradeFilterFiles(array $files = [])
    {
        $this->_upgradeFilterFiles = $files;
    }

    /**
     * 设置 数据库
     *
     * @param $host
     * @param $database
     * @param $user
     * @param $password
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function setDb($host, $database, $user, $password)
    {
        $this->_db = PdoMysqlHelper::getInstance($host, $database, $user, $password);
    }

    /**
     * 获得最新版本
     *
     * @param string $upgradeDir
     *
     * @return mixed
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function getUpdateVersion()
    {
        $dirNames = [];
        if (is_dir($this->_upgradeDir)) {
            $readDir = new ReadDirHelper($this->_upgradeDir);
            $dirNames = $readDir->getFileNames();
        }
        $data = [];
        foreach ($dirNames as $dirName) {
            $newVersion = str_pad($dirName, 14, 0);
            $data[$newVersion] = $dirName;
        }
        ksort($data);
        return array_pop($data);
    }

    /**
     * 写版本日志
     *
     * @param string $data
     *
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function setVersionLog($data)
    {
        if (is_file($this->_versionLog) && $this->_isWriteVersionLog) {
            $date = date('Y-m-d H:i:s', time());
            $handle = fopen($this->_versionLog, 'a+');
            fwrite($handle, "{$this->_version}%%版本更新类型[{$this->_upgradeType}]，执行项目为{$data}，执行成功。执行时间{$date} \r\n");
            fclose($handle);
        }
    }

    /**
     * 通过日志获得最新更新版本
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function getVersionForLog()
    {
        $data = [];
        if (is_file($this->_versionLog)) {
            $contents = FileHelper::readFileLastContent($this->_versionLog, 10);
            if (empty($contents)) {
                return '';
            }
            foreach ($contents as $content) {
                $lastVersion = substr($content, 0, (strpos($content, '%%')));
                if (!strtotime($lastVersion)) {
                    throw new \Exception("版本日志错误， 请查看{$this->_versionLog}日志文件");
                }
                $firstFound = strpos($content, '[') + 1;
                $lastFound = strrpos($content, ']');
                $upgradeType = substr($content, $firstFound, $lastFound - $firstFound);
                if ($upgradeType !== $this->_upgradeTypeCompare) {
                    $data[$lastVersion][] = $upgradeType;
                }
            }
        }
        $version = '';
        $types = isset($data[$this->_version]) ? $data[$this->_version] : [];
        if (!empty($types) && in_array($this->_upgradeType, $types)) {
            $version = $this->_version;
        } else {
            foreach ($data as $versionLog => $d) {
                if (count($d) > 1) {
                    $version = $versionLog;
                    break;
                }
            }
        }
        return $version;
    }

    /**
     * 检测版本目录
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function checkVersionDir()
    {
        $this->_versionLog = $this->_upgradeDir . 'version.log';
        if (!is_file($this->_versionLog)) {
            FileHelper::putFile($this->_versionLog, '');
        }
        $this->_version = $this->getUpdateVersion();
        $currentVersion = $this->getVersionForLog();
        if ($currentVersion >= $this->_version && $this->_isUpdate) {
            throw new \Exception("没有找到最新版本，当前版本为【{$currentVersion}】\r\n");
        } else {
            echo "check...发现当前版本为【{$currentVersion}】\r\n";
            echo "check...发现最新版本为【{$this->_version}】\r\n";
        }
    }

    /**
     * 验证升级类型
     *
     * @param $upgradeType
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function validateUpgradeType($upgradeType)
    {
        if (!$this->_isValidateUpgradeType) {
            $this->_isValidateUpgradeType = true;
            $this->_upgradeTypes = [
                $this->_upgradeTypeUpdate,
                $this->_upgradeTypeBack,
                $this->_upgradeTypeCompare,
            ];
            // 验证 来源 是否 为  update ， back
            if (!empty($upgradeType) && !in_array($upgradeType, $this->_upgradeTypes)) {
                throw new \Exception("{$upgradeType} 不是升级类型\r\n");
            }
            // 默认 为  update
            if (empty($upgradeType)) {
                $upgradeType = $this->_upgradeTypeUpdate;
            }
            $this->_upgradeType = $upgradeType;
            $this->checkVersionDir();
        }
    }


    /**
     * 自动 执行所有 更新 / 回滚
     *
     * @param string $upgradeType
     *
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function auto($upgradeType = '')
    {
        // 验证升级类型
        $this->validateUpgradeType($upgradeType);
        $this->_isWriteVersionLog = false;
        $this->sql();
        $this->code();
        $this->_isWriteVersionLog = true;
        $this->setVersionLog('code, sql');
    }

    /**
     * 打包根目录代码
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function unpackResource()
    {
        //整个目录打包
        $this->checkOrAddDir('resource');
        $fileName = "resource" . date('YmdHis', time()) . ".tar.gz";
        shell_exec(" tar -zcvf {$this->_dir}/resource/{$fileName} * --exclude=upgrade ");
        echo $fileName . "打包完成 \r\n";
    }


    /**
     * 检查目录是否存在
     *
     * @param string $dir
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function checkDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \Exception("没有找到此{$dir}目录 \r\n");
        }
    }

    /**
     * 检查文件是否存在
     *
     * @param string $file
     *
     * @throws \Exception
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function checkFile($file)
    {
        if (!is_file($file)) {
            throw new \Exception("没有找到此{$file}文件 \r\n");
        }
    }

    /**
     * 执行sql
     *
     * @param string $file
     *
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function sqlProcess($file)
    {
        $sql = @file_get_contents($file);
        if (empty($this->_db)) {
            throw new \Exception("请设置数据库 obj->setDb() \r\n");
        }
        $this->_db->execute($sql);
    }


    /**
     * 更新 / 回滚 sql
     *
     * @param string $upgradeType
     *
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function sql($upgradeType = '')
    {
        $this->validateUpgradeType($upgradeType);
        $dir = $this->_upgradeDir . DIRECTORY_SEPARATOR . $this->_upgradeSqlDirName;
        $this->checkDir($dir);
        $sqlFile = $dir . DIRECTORY_SEPARATOR . $this->_upgradeType . '.sql';
        $this->checkFile($sqlFile);
        $this->sqlProcess($sqlFile);
        echo $this->_upgradeType . ".sql 执行成功 \r\n";
        $this->setVersionLog('sql');
    }

    /**
     * 执行代码
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    protected function codeProcess()
    {
        $codeDir = $this->_upgradeDir . DIRECTORY_SEPARATOR . $this->_upgradeCodeDirName;
        $this->checkDir($codeDir);
        $compareDir = $this->_upgradeDir . DIRECTORY_SEPARATOR . $this->_upgradeTypeCompare;
        DirHelper::pathExists($compareDir);
        if ($this->_upgradeType === self::UPGRADE_UPDATE || !$this->_isUpdate) {
            DirHelper::emptyDir($compareDir);
            // 备份 原文件
            DirHelper::copyDir($this->_updateDir, $compareDir);
        }
        if (!$this->_isUpdate) {
            return;
        }
        // update file
        DirHelper::copyDir($codeDir, $this->_updateDir);
    }

    /**
     * 更新 / 回滚 代码
     *
     * @param string $upgradeType
     *
     * @auth ice.leng(lengbin@geridge.com)
     *
     */
    public function code($upgradeType = '')
    {
        $this->_isUpdate = true;
        $this->validateUpgradeType($upgradeType);
        $this->codeProcess();
        echo $this->_upgradeType . "文件执行成功\r\n";
        $this->setVersionLog('code');
    }

    /**
     * 提取被更新代码， 用于对比
     * @auth ice.leng(lengbin@geridge.com)
     *
     */
    public function compare()
    {
        $this->_isUpdate = false;
        $this->validateUpgradeType($this->_upgradeTypeCompare);
        $this->codeProcess();
        echo $this->_upgradeType . "文件执行成功\r\n";
        $this->setVersionLog('compare');
    }
}