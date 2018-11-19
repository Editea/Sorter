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
class Editea_Sorter_Block_Adminhtml_System_Config_Source_Text_Url extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $storeCode= '';

        $controllerFrontName = 'editea_sorter';

        $flag = Mage::getStoreConfigFlag('web/url/use_store');

        $storeId = $iDefaultStoreId = Mage::app()
            ->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStoreId();

        $frontendUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        if ($flag)
        {
            $configStoreId = Mage::helper('sorter')->getStoreId();

            if ($configStoreId !== $storeId)
                $frontendUrl = Mage::app()->getStore($configStoreId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        }

        $url = $frontendUrl . $controllerFrontName;

        $html = '<td class="label"><label for="sorter_api_sorter_api_group_request_url"> Request URL</label></td>';

        $html .= '<td class="value">';

        $html .= '<input type="text" class=" input-text" value="' . $url . '" id="requestUrl" readonly="true">';

        $html .= '<button onclick="copyToCliboardRequestUrl(); return false;">Copy to clipboard</button>';

        $html .= '<p class="note"><span>Copy this field to your Editea web app for integration</span></p></td>';

        $html .= '<script>
            function copyToCliboardRequestUrl() {
                      var copyText = document.getElementById("requestUrl");
                    
                      /* Select the text field */
                      copyText.select();
                    
                      /* Copy the text inside the text field */
                      document.execCommand("copy");
                      
                      return false;
                    }
             </script>';

        return $html;
    }

}