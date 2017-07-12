# excel 导入 导出

## 导入
```php
/***
 * Class BaseImport
 *
 * @param string $file   文件绝对路径
 * @param array  $config 配置
 *                       [
 *                          'title' => ['mobile', 'email', 'name', 'id_card', 'nickname', 'higher_user_mobile'],
 *                          'rules' => [
 *                              [['mobile', 'email', 'name', 'id_card', 'nickname'], 'required'], //必填验证
 *                              [['name','nickname'], 'string', 'max' => 32], // 字符串长度验证
 *                              ['email', 'email'], // email 验证
 *                              [['mobile', 'higher_user_mobile'], 'mobile'], //手机验证
 *                              ['id_card', 'idCard'], // 身份证验证
 *                          ],
 *                       ];
 * @param array  $title  验证导入的模版与服务器模版同步
 * @param array  $rules  验证规则 支持 mobile， email， idCard， string
 *
 * 支持 生成错误 csv
 * 支持 获得 当前导入 总条数 和 导入成功数量
 *
 *
 *
 * @package lengbin\helper\excel
 */
 


 // 使用
 
  /**
   * 下载文件路径 当前域名+uploads+filename
   *
   * @param string $file 文件路径
   *
   * @return string
   * @auth ice.leng(lengbin0@gmail.com)
   * @issue
   */
 protected function getUploadUrl($file)
 {
     return \Yii::$app->urlManager->createAbsoluteUrl('uploads') . '/' . $file;
 }
 
 /**
  * 导入类
  *
  * @param string $className 对象名称
  * @param string $file      文件路径
  * @param array  $config    配置参数
  *
  * @return object
  * @auth ice.leng(lengbin0@gmail.com)
  * @throws \Exception
  * @issue
  */
 $import = ExcelFactory::importInstance('DemoImport', $file, [
             'title' => ['grade', 'class_room', 'number', 'username', 'mobile', 'email'],
             'rules' => [
                 [['grade', 'number', 'class_room', 'username', 'mobile'], 'required'],
                 [['grade', 'class_room', 'username',], 'string', 'max' => 32],
                 [['mobile', 'number',], 'string', 'max' => 15],
                 ['email', 'email'],
                 [['mobile'], 'mobile'],
             ],
         ]);
 $rs = $import->processing();
 $csv = $import->createErrorCsv();
 if( !$rs || $csv ){
     $len = strpos( $csv, 'uploads' );
     //上传文件的路径 + error.csv 文件名称， 此错误为上传文件内容错误
     // 一般为当前域名/uploads + error.csv 
     $str = $this->getUploadUrl(substr($csv, ($len+8 ), strlen($csv)));
     //异常抛出
     $this->invalidParamException(CodeHelper::EXCEL_IMPORT_STUDENT_ERRORS_FILE, ['file' => $str]);
 }
 // 此错误为文件类型， title 错误
 if($import->getErrors()){
     //异常抛出
     $this->invalidParamException(CodeHelper::EXCEL_IMPORT_STUDENT_ERRORS, $import->getErrors());
 }

// demo import

class DemoImport extends BaseImport
{

    private $_data = [];
    private $_limitUser = 900;


    public function __construct($file, $config)
    {
        parent::__construct($file, $config);
    }
    
    // 返回导入数据的每一行数据， 
    // $num 为读取文件行数
    protected function import($rowData, $num)
    {
        // 已有数据 是否重复
        if (in_array($rowData['mobile'], $this->_mobiles) || in_array($rowData['number'], $this->_numbers)) {
            $this->csv[] = $num . ', 手机/编号(' . $rowData['mobile'] . '/ ' . $rowData['number'] . ')在导入列表中已重复';
            return;
        }
        $this->_data[] = $rowData;
    }
    
    // 执行
    public function processing()
    {
        parent::processing();
        if ($this->getImportTotalNumber() > $this->_limitUser) {
            $this->csv[] = ' , 导入用户不能超过' . $this->_limitUser . '行';
            return false;
        }
        if ($this->hasErrorCsv()) {
            return false;
        }

        if (!empty($this->_data)) {
            foreach ($this->_data as $data) {
                $this->getUserService()->register($data, $this->role);
            }
        }
        return true;
    }

}


```


## 导出
```php

/**
 *
 * @param array $config $config = [
 *                          'width' => '24',
 *                          'height'=> '15',
 *                          'th' =>[     // td数据字段 => th 名称
 *                              'attr' => '规格',
 *                              'stock' => '库存',
 *                              'product_price' => '供货价',
 *                              'sale_price' => '平台价',
 *                              'retail_price' => '建议零售价',
 *                              'market_price' => '市场参考价',
 *                          ],
 *                          'style' => [
 *                              'thBackGroundColor' => '003396e3',  // th 背景颜色, 默认为蓝色
 *                              'thFontIsBold' => true,       // th 字体是否加粗， 默认为是
 *                              'thFontColor' => '00ffffff',      // th 字体颜色， 默认为白色
 *                              'tdBackGroundColor' =>['00ecf7ff','00222222'] / '00ecf7ff',   //  td 背景颜色, 默认为淡蓝色  可以是数组。 表示td 分割颜色， 字符串着为 全部颜色
 *                          ],
 *                          'rules' => [
 *                             [], 'money'
 *                          ]
 *                      ];
 *                      颜色都是16进制
 *                      目前只有 金额 格式化
 */

$config = [
    'width'  => '24',
    'height' => '15',
    'th'     =>[
        'mobile'                => '招商人',
        'username'              => '招商昵称',
        'roles'                 => '招商角色',
        'num'                   => '招商机构数',
        'totalPrice'            => '交易总金额',
        'investmentPrice'       => '招商奖励',
    ],
    'rules' => [
        [['investmentPrice', 'totalPrice'], 'money'],
    ],
    'fileName'=>date('Y-m-d',time()).'_招商统计',
];
$export = ExcelFactory::exportInstance('StandardExport', $config);
$export->load($res['data']);
$export->export();

```