<?php

class Editea_Sorter_Model_System_Config_Source_Dropdown_Stores
{
    public function toOptionArray()
    {
        $response = array();

        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            $response[] = array(
                'value' => $store->getStoreId(),
                'label' => $store->getName()
            );
        }

        return $response;
    }
}