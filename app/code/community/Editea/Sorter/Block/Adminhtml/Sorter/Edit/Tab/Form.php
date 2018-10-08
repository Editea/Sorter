<?php
class Editea_Sorter_Block_Adminhtml_Sorter_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("sorter_form", array("legend"=>Mage::helper("sorter")->__("Item information")));


        $fieldset->addField("editea_report_id", "text", array(
            "label" => Mage::helper("sorter")->__("Id"),
            "name" => "editea_report_id",
            "readonly" => true
        ));

        $fieldset->addField("report_type", "text", array(
            "label" => Mage::helper("sorter")->__("Type"),
            "name" => "report_type",
            "readonly" => true
        ));

        $fieldset->addField("report_level", "text", array(
            "label" => Mage::helper("sorter")->__("Level"),
            "name" => "report_level",
            "readonly" => true
        ));

        $fieldset->addField("report_item_id", "text", array(
            "label" => Mage::helper("sorter")->__("Item Id"),
            "name" => "report_item_id",
            "readonly" => true
        ));

        $fieldset->addField("report_message", "text", array(
            "label" => Mage::helper("sorter")->__("Message"),
            "name" => "report_message",
            "readonly" => true
        ));

        $fieldset->addField("session_id", "text", array(
            "label" => Mage::helper("sorter")->__("Session Id"),
            "name" => "session_id",
            "readonly" => true
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        );

        $fieldset->addField('created_at', 'date', array(
            'label'        => Mage::helper('sorter')->__('Created At'),
            'name'         => 'created_at',
            'time' => true,
            'image'        => $this->getSkinUrl('images/grid-cal.gif'),
            'format'       => $dateFormatIso,
            "readonly" => true
        ));

        if (Mage::getSingleton("adminhtml/session")->getSorterData())
        {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getSorterData());
            Mage::getSingleton("adminhtml/session")->setSorterData(null);
        }
        elseif(Mage::registry("sorter_data")) {
            $form->setValues(Mage::registry("sorter_data")->getData());
        }
        return parent::_prepareForm();
    }
}
