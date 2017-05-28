# 上传

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
     * @package common\helpers
     */
     
     // 根目录
     $rootDir = '/home/lengbin/Document/www/helperClass'
     $readDir = new \lengbin\helper\directory\ReadDirHelper($rootDir);
     // 是否需要返回为空间名称
     $readDir->setIsNamespace(true);
     $readDir->setTargetDir($dirName);
     $readDir->setFilterDirs(['common']);
     return $readDir->getFileNames();
     
```