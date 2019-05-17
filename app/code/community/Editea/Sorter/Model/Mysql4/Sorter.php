<?php

class Editea_Sorter_Model_Mysql4_Sorter extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('sorter/sorter', 'editea_report_id');
    }
}