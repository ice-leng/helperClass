<?php
/**
 * Created by ice.leng(lengbin@geridge.com)
 * Date: 2016/1/14
 * Time: 20:35
 */

namespace lengbin\helper\excel;


/***
 * Class BaseImport
 *
 * @param string $file   文件绝对路径
 * @param array  $config 配置
 *                       [
 *                       'title' => ['mobile', 'email', 'name', 'id_card', 'nickname', 'higher_user_mobile'],
 *                       'rules' => [
 *                       [['mobile', 'email', 'name', 'id_card', 'nickname'], 'required'],
 *                       [['name','nickname'], 'string', 'max' => 32],
 *                       ['email', 'email'],
 *                       [['mobile', 'higher_user_mobile'], 'mobile'],
 *                       ['id_card', 'idCard'],
 *                       ],
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
abstract class BaseImport
{
    private $objExcel;
    private $allRow;
    protected $allColumn;
    private $errors;
    protected $csv = [];
    private $path;
    private $fileFields;
    private $fullError = false;
    protected $title;
    protected $rules;
    protected $num = 0;
    protected $fullData = [];

    /**
     * 文件加载
     *
     * @param $file
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function fileLoad($file)
    {
        $objReader = \PHPExcel_IOFactory::createReaderForFile($file);
        $this->objExcel = $objReader->load($file);
        $this->objExcel->setActiveSheetIndex();
        $this->allColumn = $this->objExcel->getActiveSheet()->getHighestRow();
        $this->allRow = $this->objExcel->getActiveSheet()->getHighestColumn();
        $this->path = dirname(($file));
    }

    public function __construct($file, $config)
    {
        if (!is_file($file) || !file_exists($file)) {
            throw new \Exception('未传入文件');
        }
        $this->fileLoad($file);
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        // 导入的模版与服务器模版验证
        $this->titleValidate();
        // 规则验证
        if (empty($this->getErrors())) {
            $this->rulesValidate();
        }
    }

    /**
     * 头部验证
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function titleValidate()
    {
        $titles = $this->getFileFields(true);
        if ($this->title != $titles) {
            $num = count($titles) - count($this->title);
            $this->setError('title', '导入的模版与服务器模版不一致，可能多了' . $num . '个空格');
        }
    }

    /**
     * 规则验证
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function rulesValidate()
    {
        foreach ($this->rules as $rules) {
            if (count($rules) < 2) {
                throw new \Exception('规则定义错误');
            }
            $fds = is_array($rules[0]) ? $rules[0] : [$rules[0]];
            if (!empty(array_diff($fds, $this->getFileFields(true)))) {
                throw new \Exception('导入表头与定义的规则字段错误');
            }
        }
    }


    protected abstract function import($rowData, $num);

    private function isRequired($str)
    {
        return (!empty($str)) ? 1 : 0;
    }

    private function isMobile($str)
    {
        $match = '/1[34578]{1}\d{9}$/';
        return preg_match($match, $str) ? 1 : 0;
    }

    private function isEmail($str)
    {
        $match = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
        return preg_match($match, $str) ? 1 : 0;
    }

    private function isIdCard($str)
    {
        if (count(explode('E', $str)) > 2) {
            throw new \Exception('导入数据中身份证号码数据是科学计数法，请将设置成文本格式。');
        }
        $match = '/^(\d{18,18}|\d{15,15}|\d{17,17}(x|X))$/';
        return preg_match($match, $str) ? 1 : 0;
    }

    private function stringValidate($str, $arr)
    {
        $data = '';
        $length = strlen($str);
        if (isset($arr['max']) && $length > $arr['max']) {
            $data = '字符串不能大于' . $arr['max'];
        }

        if (isset($arr['min']) && $length < $arr['min']) {
            $data = '字符串不能小于' . $arr['min'];
        }
        return $data;
    }

    /**
     * 获得文件字段
     *
     * @param bool|false $isValueArray
     *
     * @return array
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function getFileFields($isValueArray = false)
    {
        if (empty($this->fileFields)) {
            $fileFields = [];
            for ($i = 'A'; $i <= $this->allRow; ++$i) {
                $fileFields[$i] = $this->objExcel->getActiveSheet()->getCell($i . '1')->getFormattedValue();
            }
            $this->fileFields = $fileFields;
        }
        return $isValueArray ? array_values($this->fileFields) : $this->fileFields;
    }

    /**
     * 验证错误信息
     *
     * @param $ruleName
     *
     * @return mixed
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function getErrorMessage($ruleName)
    {
        $rn = strtolower($ruleName);
        $messages = [
            'required' => '字段不能为空',
            'email'    => '邮箱验证错误',
            'mobile'   => '手机验证错误',
            'idcard'   => '身份证验证错误',
        ];
        if (!isset($messages[$rn]) || empty($messages[$rn])) {
            throw new \Exception('没有' . $ruleName . '错误信息， 请添加！');
        }
        return $messages[$rn];
    }

    /**
     * 具体字段验证
     *
     * @param $rule
     * @param $val
     * @param $arr
     *
     * @return int|string
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function validate($rule, $val, $arr)
    {
        $status = 2;
        switch (strtolower($rule)) {
            case 'required'  :
                $status = $this->isRequired($val);
                break;
            case 'string'    :
                if (!empty($val)) {
                    $status = $this->stringValidate($val, $arr);
                }
                break;
            case 'email'     :
                if (!empty($val)) {
                    $status = $this->isEmail($val);
                }
                break;
            case 'mobile'    :
                if (!empty($val)) {
                    $status = $this->isMobile($val);
                }
                break;
            case 'idcard'    :
                if (!empty($val)) {
                    $status = $this->isIdCard($val);
                }
                break;
            default :
                throw new \Exception('没有' . $rule . '此验证功能， 请添加！');
                break;
        }
        return $status;
    }

    /**
     * 字段验证
     *
     * @param $field
     * @param $val
     *
     * @return int|string
     * @throws \Exception
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function fieldValidate($field, $val)
    {
        $error = '';
        foreach ($this->rules as $rules) {
            $fields = is_array($rules[0]) ? $rules[0] : [$rules[0]];
            if (in_array($field, $fields)) {
                $msg = isset($rules['message']) ? $rules['message'] : '';
                $status = $this->validate($rules[1], $val, $rules);
                if (is_int($status)) {
                    if (!$status) {
                        $error = $msg ? $msg : $this->getErrorMessage($rules[1]);
                    }
                } else {
                    if ($status) {
                        $error = $msg ? $msg : $status;
                    }
                }
            }
        }
        return $error;
    }

    /**
     * 获得excel 每行数据
     *
     * @param bool|false $onlyOne
     * @param bool|true  $isImport
     * @param bool|true  $fullData
     *
     * @return array|bool
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    private function getRows($onlyOne = false, $isImport = true, $fullData = false)
    {
        if ($this->hasErrors()) {
            return false;
        }
        $number = $onlyOne ? 2 : $this->allColumn;
        $fileFields = $this->getFileFields();
        $errorData = [];
        for ($i = 2; $i <= $number; ++$i) {
            $data = [];
            $rowsData = [];
            $isError = false;
            for ($d = 'A'; $d <= $this->allRow; ++$d) {
                $val = $this->objExcel->getActiveSheet()->getCell($d . $i)->getFormattedValue();
                $fileField = $fileFields[$d];
                $rowsData[$fileField] = trim($val);
            }
            // 强制过滤 为空行的数据
            if (empty(array_filter($rowsData))) {
                //                $this->num++;
                $this->allColumn--;
                continue;
            }
            for ($j = 'A'; $j <= $this->allRow; ++$j) {
                $val = $this->objExcel->getActiveSheet()->getCell($j . $i)->getFormattedValue();
                $fileField = $fileFields[$j];
                $data[$fileField] = trim($val);
                $error = $this->fieldValidate($fileField, $val);
                if ($error) {
                    $errorData[$i][$fileField] = $fileField . $error;
                    $isError = true;
                }
                if ($onlyOne && $error) {
                    $this->setError($fileField, $error);
                    break;
                }
                if ($fullData) {
                    if (isset($this->fullData[$fileField])) {
                        $this->fullData[$fileField] .= ',' . $val;
                    } else {
                        $this->fullData[$fileField] = $val;
                    }
                }
            }
            if ($isImport && !empty(array_filter($data)) && !$this->hasErrors() && !$isError) {
                $this->import($data, $i);
                $this->num++;
            }
            if (isset($errorData[$i]) && !empty($errorData[$i])) {
                $this->csv[] = $i . ', ' . join('|', $errorData[$i]);
            }
        }
        if ($this->fullError) {
            $this->setError('fullError', $errorData);
        }
        return $this->hasErrors() ? false : $data;
    }

    /**
     * 执行导入
     * @return bool
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function processing()
    {
        return $this->getRows() ? true : false;
    }

    /**
     * 获得excel 第一行数据
     * @return array|bool
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function getFirstData()
    {
        return $this->getRows(true, false);
    }

    /**
     * 设置错误信息
     *
     * @param $name
     * @param $value
     *
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function setError($name, $value)
    {
        $this->errors[$name] = $value;
    }

    /**
     * 是否存在错误信息
     * @return bool
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    protected function hasErrors()
    {
        return !empty($this->getErrors());
    }

    /**
     * 获得错误信息
     * @return mixed
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 是否存在错误文件 csv 数据
     * @return bool
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function hasErrorCsv()
    {
        return !empty($this->csv);
    }

    /**
     *  生成 错误文件
     * @return string
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function createErrorCsv()
    {
        if (!$this->hasErrorCsv()) {
            return false;
        }
        array_unshift($this->csv, 'NUMBER, ERRORS');
        $name = $this->path . '/' . time() . '.csv';
        $fp = fopen($name, 'a+');
        foreach ($this->csv as $csv) {
            $data = iconv("UTF-8", "GB2312//IGNORE", $csv);
            fwrite($fp, $data);
            fwrite($fp, "\r\n");
        }
        fclose($fp);
        return $name;
    }

    /**
     * excel 导入成功行数
     * @return int
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function getSuccessNumber()
    {
        return $this->num;
    }

    /**
     * excel 导入总行数
     * @return mixed
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function getImportTotalNumber()
    {
        return ($this->allColumn - 1);
    }

    /**
     * excel 全部正确数据
     * @return mixed
     * @auth ice.leng(lengbin@geridge.com)
     *
     * @issue
     */
    public function getFullDataToString()
    {
        $this->getRows(false, true, true);
        return $this->fullData;
    }

}