<?php
class Editea_Sorter_Block_Adminhtml_Sorter extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
        $this->_controller = "adminhtml_sorter";
        $this->_blockGroup = "sorter";
        $this->_headerText = Mage::helper("sorter")->__("Sorter Manager");
        $this->_addButtonLabel = Mage::helper("sorter")->__("Add New Item");
        parent::__construct();
	
	}
}