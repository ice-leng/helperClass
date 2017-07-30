# 文件夹及文件

```php
    /**
     * 读取目录帮助类，
     * 可以指定目录获取文件
     * 获得文件 namespace
     * 定义排除目录，文件
     *
     * $readDir = new ReadDirHelper( $dir )
     * $readDir->getFileNames();
     *
     *
     * 后续改进：
     *      返回可以根据目录结构返回
     *      返回可以获得文件目录 / 文件结构目录
     *
     * @package lengbin\helper\directory
     */
     
     // 根目录
     $rootDir = '/home/lengbin/Document/www/helperClass'
     $readDir = new \lengbin\helper\directory\ReadDirHelper($rootDir);
     // 是否需要返回为空间名称
     $readDir->setIsNamespace(true);
     $readDir->setNamespace('xxxxx');
     $readDir->setTargetDir($dirName);
     $readDir->setFilterDirs(['common']);
     // 是否当前目录
     $readDir->setIsReadCurrentDir(true);
     return $readDir->getFileNames();
     // 目录名称
     $readDir->getDirNames();
     
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
     
```