<?php
class Editea_Sorter_Model_Mysql4_Validetor extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("sorter/validetor", "editea_validetor_id");
    }
}