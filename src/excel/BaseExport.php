<?php
/**
 * Created by ice.leng(lengbin0@gmail.com)
 * Date: 2016/1/27
 * Time: 15:55
 */

namespace lengbin\helper\excel;


interface BaseExport
{

    public function load($data, $offset=0);

    public function export();
}