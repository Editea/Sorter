<?php

class Editea_Sorter_Model_Validetor extends Mage_Core_Model_Abstract
{
    protected $helper = '';

    protected function _construct(){

       $this->_init("sorter/validetor");

       $this->helper = Mage::helper('sorter');
    }

}
	 