<?php

class Editea_Sorter_Block_Adminhtml_Sorter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sorter_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('sorter')->__('Item Information'));
    }

    /**
     * @return Mage_Core_Block_Abstract
     * @throws Exception
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', [
            'label' => Mage::helper('sorter')->__('Item Information'),
            'title' => Mage::helper('sorter')->__('Item Information'),
            'content' => $this->getLayout()->createBlock('sorter/adminhtml_sorter_edit_tab_form')->toHtml(),
        ]);
        return parent::_beforeToHtml();
    }
}
