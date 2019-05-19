<?php

class Editea_Sorter_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $active;

    private $userApi;

    private $tokenApi;

    private $homeUrl;

    private $storeId;

    private $rootCategoryId;

    private $brandAttributeCode;

    private $brandPositionId;

    private $brandPositionCategoryId;

    private $cleanCategoryCache;

    private $cleanOnlyVarnishCategoryCache;

    private $reindexCategoryProducts;

    private $minInStock;

    private $additionalAttributes;

    private $debugMode;

    private $saveLogsMonths;

    private $sessionId;

    const SORTER_API_STORE_CONFIG_PATH = 'sorter_api/sorter_api_group/';
    const SORTER_API_STORE_CONFIG_BRAND_SUPPORT_PATH = 'sorter_api/sorter_api_group_brand_support/';
    const SORTER_API_STORE_CONFIG_LOGS_SETTINGS_PATH = 'sorter_api/sorter_logs_settings/';

    /**
     * Editea_Sorter_Helper_Data constructor.
     * @throws Mage_Core_Model_Store_Exception
     */
    public function __construct()
    {
        $this->active = $this->getStoreConfigByFiled('active', true);
        $this->userApi = $this->getStoreConfigByFiled('user_api');
        $this->tokenApi = $this->getStoreConfigByFiled('token_api');
        $this->storeId = $this->getStoreConfigByFiled('store_id'); //the store is needed for base url:
        $this->rootCategoryId = Mage::app()->getStore($this->storeId)->getRootCategoryId();

        if (empty($this->storeId)) {
            $this->storeId = 0;
        }

        // Brand support settings
        $this->brandAttributeCode = $this->getStoreConfigBrandSupportByFiled('brand_attribute_code');
        $this->brandPositionId = $this->getStoreConfigBrandSupportByFiled('brand_position_id');
        $this->brandPositionCategoryId = $this->getStoreConfigBrandSupportByFiled('brand_position_category_id');

        // Main settings
        $this->cleanCategoryCache = $this->getStoreConfigByFiled('clear_cache_after_sort', true);
        $this->cleanOnlyVarnishCategoryCache = $this->getStoreConfigByFiled('clear_varnish_cache__only', true);
        $this->reindexCategoryProducts = $this->getStoreConfigByFiled('reindex_category_products_after_sort', true);
        $this->minInStock = $this->getStoreConfigByFiled('min_in_stock');
        $this->additionalAttributes = $this->renderAdditionalAttributes();

        // Logs Settings
        $this->debugMode = $this->getStoreConfigLogsSettingsByFiled('debug_mode');
        $this->saveLogsMonths = $this->getStoreConfigLogsSettingsByFiled('save_logs_months');

        $homeUrl = Mage::getUrl('', ['_store' => $this->storeId]);
        $this->homeUrl = strtok($homeUrl, '?');

        $session = Mage::getSingleton('core/session');
        $this->sessionId = $session->getEncryptedSessionId();
    }

    private function renderAdditionalAttributes()
    {
        $attributesResponse = [];

        $attributeIds = explode(',', $this->getStoreConfigByFiled('additional_attributes'));

        $productAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds]);

        foreach ($productAttributes as $attribute) {
            $attributesResponse[$attribute->getAttributeId()] = $attribute->getAttributeCode();
        }

        return $attributesResponse;
    }

    private function getStoreConfigByFiled($field, $isFlag = false)
    {
        if ($isFlag) {
            return Mage::getStoreConfigFlag(self::SORTER_API_STORE_CONFIG_PATH . $field);
        }

        return Mage::getStoreConfig(self::SORTER_API_STORE_CONFIG_PATH . $field);
    }

    private function getStoreConfigBrandSupportByFiled($field, $isFlag = false)
    {
        if ($isFlag) {
            return Mage::getStoreConfigFlag(self::SORTER_API_STORE_CONFIG_BRAND_SUPPORT_PATH . $field);
        }

        return Mage::getStoreConfig(self::SORTER_API_STORE_CONFIG_BRAND_SUPPORT_PATH . $field);
    }

    private function getStoreConfigLogsSettingsByFiled($field, $isFlag = false)
    {
        if ($isFlag) {
            return Mage::getStoreConfigFlag(self::SORTER_API_STORE_CONFIG_LOGS_SETTINGS_PATH . $field);
        }

        return Mage::getStoreConfig(self::SORTER_API_STORE_CONFIG_LOGS_SETTINGS_PATH . $field);
    }

    public function getIsActive()
    {
        return $this->active;
    }

    public function getUserApi()
    {
        return $this->userApi;
    }

    public function getTokenApi()
    {
        return $this->tokenApi;
    }

    public function getHomeUrl()
    {
        return $this->homeUrl;
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function getRootCategoryId()
    {
        return $this->rootCategoryId;
    }

    public function getBrandAttributeCode()
    {
        return $this->brandAttributeCode;
    }

    public function getBrandPositionId()
    {
        return $this->brandPositionId;
    }

    public function getBrandPositionCategoryId()
    {
        return $this->brandPositionCategoryId;
    }

    public function getCleanCategoryCache()
    {
        return $this->cleanCategoryCache;
    }

    public function getReindexCategoryProducts()
    {
        return $this->reindexCategoryProducts;
    }

    public function getMinInStock()
    {
        return $this->minInStock;
    }

    public function getAdditionalAttributes()
    {
        return array_values($this->additionalAttributes);
    }

    public function getDebugMode($level)
    {
        if ($this->debugMode == 1 && ($level === 'low')) {
            return true;
        }

        if ($this->debugMode == 2) {
            return true;
        }

        return false;
    }

    public function getSaveLogsMonths()
    {
        if ($this->saveLogsMonths === '') {
            return 1;
        }

        return $this->saveLogsMonths;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function reportLog($message = '', $type = 'general', $level = 'low', $itemId = '')
    {
        if (!empty($message) && $this->getDebugMode($level)) {
            Mage::getModel('sorter/sorter')
                ->setReportType($type)
                ->setReportLevel($level)
                ->setReportItemId($itemId)
                ->setReportMessage(print_r($message, true))
                ->setSessionId($this->getSessionId())
                ->save();
        }
    }

    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->Editea_Sorter->version;
    }
}
	 