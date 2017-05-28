<?php
/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/3/14
 * Time: 下午4:37
 */

namespace lengbin\helper\excel;



class DemoImport extends BaseImport
{

    private $_data = [];
    private $_limitUser = 900;


    public function __construct($file, $config)
    {
        parent::__construct($file, $config);
    }

    protected function import($rowData, $num)
    {
        // 已有数据 是否重复
        if (in_array($rowData['mobile'], $this->_mobiles) || in_array($rowData['number'], $this->_numbers)) {
            $this->csv[] = $num . ', 手机/编号(' . $rowData['mobile'] . '/ ' . $rowData['number'] . ')在导入列表中已重复';
            return;
        }
        $this->_data[] = $rowData;
    }

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