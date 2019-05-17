<?php

class Editea_Sorter_Model_System_Config_Source_Dropdown_Stores
{
    public function toOptionArray()
    {
        $response = [];

        $stores = Mage::app()->getStores();

        $response[] = [
            'value' => 0,
            'label' => Mage::helper('customer')->__('All Store Views'),
        ];

        foreach ($stores as $store) {
            $response[] = [
                'value' => $store->getStoreId(),
                'label' => $store->getName(),
            ];
        }

        return $response;
    }
}