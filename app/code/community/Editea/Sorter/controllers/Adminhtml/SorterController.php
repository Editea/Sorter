<?php

class Editea_Sorter_Adminhtml_SorterController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sorter/sorter');
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('sorter/sorter')->_addBreadcrumb(Mage::helper('adminhtml')->__('Sorter  Manager'),
            Mage::helper('adminhtml')->__('Sorter Manager'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Sorter'));
        $this->_title($this->__('Manager Sorter'));
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function editAction()
    {
        $this->_title($this->__('Sorter'));
        $this->_title($this->__('Sorter'));
        $this->_title($this->__('Edit Item'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sorter/sorter')->load($id);
        if ($model->getId()) {
            Mage::register('sorter_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('sorter/sorter');
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Sorter Manager'),
                Mage::helper('adminhtml')->__('Sorter Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Sorter Description'),
                Mage::helper('adminhtml')->__('Sorter Description'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('sorter/adminhtml_sorter_edit'))->_addLeft($this->getLayout()->createBlock('sorter/adminhtml_sorter_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sorter')->__('Item does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * @throws Mage_Core_Exception
     */
    public function newAction()
    {
        $this->_title($this->__('Sorter'));
        $this->_title($this->__('Sorter'));
        $this->_title($this->__('New Item'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('sorter/sorter')->load($id);

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('sorter_data', $model);

        $this->loadLayout();
        $this->_setActiveMenu('sorter/sorter');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Sorter Manager'),
            Mage::helper('adminhtml')->__('Sorter Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Sorter Description'),
            Mage::helper('adminhtml')->__('Sorter Description'));


        $this->_addContent($this->getLayout()->createBlock('sorter/adminhtml_sorter_edit'))->_addLeft($this->getLayout()->createBlock('sorter/adminhtml_sorter_edit_tabs'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        $post_data = $this->getRequest()->getPost();

        if ($post_data) {

            try {
                $model = Mage::getModel('sorter/sorter')
                    ->addData($post_data)
                    ->setId($this->getRequest()->getParam('id'))
                    ->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Sorter was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setSorterData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setSorterData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('sorter/sorter');
                $model->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }
        $this->_redirect('*/*/');
    }

    public function massRemoveAction()
    {
        try {
            $ids = $this->getRequest()->getPost('editea_report_ids', []);
            foreach ($ids as $id) {
                $model = Mage::getModel('sorter/sorter');
                $model->setId($id)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item(s) was successfully removed'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'sorter.csv';
        $grid = $this->getLayout()->createBlock('sorter/adminhtml_sorter_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'sorter.xml';
        $grid = $this->getLayout()->createBlock('sorter/adminhtml_sorter_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
