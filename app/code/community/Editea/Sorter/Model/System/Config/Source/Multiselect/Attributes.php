<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Editea_Sorter_Model_System_Config_Source_Multiselect_Attributes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributesResponse = array();
        $productAttributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $productAttributes->addFieldToFilter('used_in_product_listing',1);

        foreach($productAttributes as $attribute) {
            $temp = array();
            $temp['value'] = $attribute->getAttributeId();
            $temp['label'] = $attribute->getAttributeCode();
            $attributesResponse[] = $temp;
        }

        return $attributesResponse;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $attributesResponse = array();
        $productAttributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $productAttributes->addFieldToFilter('used_in_product_listing',1);

        foreach($productAttributes as $attribute) {
            $attributesResponse[$attribute->getAttributeId()] = $attribute->getAttributeCode();
        }

        return $attributesResponse;
    }

}