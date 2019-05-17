<?php

class Editea_Sorter_Block_Adminhtml_Sorter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'editea_report_id';
        $this->_blockGroup = 'sorter';
        $this->_controller = 'adminhtml_sorter';
        $this->_updateButton('save', 'label', Mage::helper('sorter')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('sorter')->__('Delete Item'));

        $this->_addButton('saveandcontinue', [
            'label' => Mage::helper('sorter')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ], -100);


        $this->_formScripts[] = "

                    function saveAndContinueEdit(){
                        editForm.submit($('edit_form').action+'back/edit/');
                    }
                ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('sorter_data') && Mage::registry('sorter_data')->getId()) {
            return Mage::helper('sorter')->__("Edit Item '%s'",
                $this->htmlEscape(Mage::registry('sorter_data')->getId()));

        }
        return Mage::helper('sorter')->__('Add Item');
    }
}