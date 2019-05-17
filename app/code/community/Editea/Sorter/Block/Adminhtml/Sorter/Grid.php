<?php

class Editea_Sorter_Block_Adminhtml_Sorter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sorterGrid');
        $this->setDefaultSort('editea_report_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sorter/sorter')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('editea_report_id', [
            'header' => Mage::helper('sorter')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'editea_report_id',
        ]);

        $this->addColumn('report_type', [
            'header' => Mage::helper('sorter')->__('Type'),
            'index' => 'report_type',
        ]);
        $this->addColumn('report_level', [
            'header' => Mage::helper('sorter')->__('Level'),
            'index' => 'report_level',
        ]);
        $this->addColumn('report_item_id', [
            'header' => Mage::helper('sorter')->__('Item Id'),
            'index' => 'report_item_id',
        ]);
        $this->addColumn('report_message', [
            'header' => Mage::helper('sorter')->__('Message'),
            'index' => 'report_message',
        ]);
        $this->addColumn('session_id', [
            'header' => Mage::helper('sorter')->__('Session Id'),
            'index' => 'session_id',
        ]);
        $this->addColumn('created_at', [
            'header' => Mage::helper('sorter')->__('Created At'),
            'index' => 'created_at',
            'type' => 'datetime',
        ]);

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('editea_report_id');
        $this->getMassactionBlock()->setFormFieldName('editea_report_ids');
        $this->getMassactionBlock()->setUseSelectAll(true);
        $this->getMassactionBlock()->addItem('remove_sorter', [
            'label' => Mage::helper('sorter')->__('Remove Sorter'),
            'url' => $this->getUrl('*/adminhtml_sorter/massRemove'),
            'confirm' => Mage::helper('sorter')->__('Are you sure?'),
        ]);
        return $this;
    }
}