<?php

class Editea_Sorter_Model_Sorter extends Mage_Core_Model_Abstract
{
    /** @var Editea_Sorter_Helper_Data $helper */
    protected $helper;
    protected $conn;
    protected $totalPages = 1;

    protected function _construct()
    {
        $this->_init('sorter/sorter');

        $this->conn = Mage::getModel('core/resource')->getConnection('core_write');

        $this->helper = Mage::helper('sorter');
    }

    /**
     * @param $jsonRequest
     * @return mixed|string
     * @throws Exception
     */
    public function setProductPosition($jsonRequest)
    {
        $this->helper->reportLog($jsonRequest, 'setProductsPosition');

        $sorterParams = json_decode($jsonRequest);

        if ($sorterParams->sorter_type === 'brand' && $this->helper->getBrandPositionId()) {
            return $this->setProductPositionForBrand($sorterParams);
        }

        $categoryId = $sorterParams->category_id;

        $this->helper->reportLog('category-id: ' . $categoryId, 'setProductsPosition');

        $productsIds = [];

        foreach ($sorterParams->products as $productId => $position) {
            $productsIds[] = $productId;

            if (!empty($productId) && !empty($categoryId) && is_numeric($position)) {
                $this->setProductPositionByCategory($categoryId, $productId, $position);
            }

            $this->helper->reportLog('position: ' . $position, 'setProductsPosition', 'high', $productId);
        }

        $this->reindexCatalogCategoryProduct($productsIds);

        $this->clearCategoryCache($categoryId, $sorterParams->category_url);

        return $this->helper->getSessionId();
    }

    private function setProductPositionByCategory($categoryId, $productId, $position)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $productOrderData = [
            'product_id' => (int)$productId,
            'category_id' => (int)$categoryId,
            'position' => (int)$position,
        ];

        $queryUpdate = array_keys($productOrderData);
        $this->conn->insertOnDuplicate($table, $productOrderData, $queryUpdate);
    }

    private function reindexCatalogCategoryProduct($productIds)
    {
        if ($this->helper->getReindexCategoryProducts()) {
            $event = Mage::getModel('index/event');
            $event->setNewData(['product_ids' => $productIds]);
            Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction($event);
        }
    }

    private function clearCategoryCache($categoryId, $categoryUrl)
    {
        if ($this->helper->getCleanCategoryCache()) {

            if ($categoryId && !$this->cleanOnlyVarnishCategoryCache) {

                Mage::app()->cleanCache([Mage_Catalog_Model_Category::CACHE_TAG . '_' . $categoryId]);

                $this->helper->reportLog('categoryId: ' . $categoryId, 'clearCategoryCache', 'high');
            }

            if ($categoryUrl && Mage::helper('core')->isModuleEnabled('Nexcessnet_Turpentine')) {

                $categoryUrl = substr($categoryUrl, strrpos($categoryUrl, '/') + 1);

                $result = Mage::getModel('turpentine/varnish_admin')->flushUrl($categoryUrl);

                $this->helper->reportLog('Result: ' . print_r($result, true) . ' Url: ' . $categoryUrl,
                    'clearVarnishCategoryCache', 'high');
            }
        }
    }

    public function getProductDetail($jsonRequest)
    {
        $this->helper->reportLog($jsonRequest, 'getProductDetail');

        $sorterParams = json_decode($jsonRequest);
        $categoryId = $sorterParams->category_id;
        $page = $sorterParams->page;
        $sorterType = $sorterParams->sorter_type;
        $totalPages = $sorterParams->total_pages;

        if (empty($totalPages)) {
            $totalPages = 1;
        }

        if (empty($page)) {
            return 'No page value';
        }

        $this->helper->reportLog('Sorter Type: ' . $sorterType, 'getProductDetail');

        if ($categoryId && $page) {

            $response = [];

            for ($i = $page; $i < ($totalPages + $page); $i++) {
                $productsDetails = $this->getProductDetailArray($categoryId, $i, $sorterType);

                $response[] = [
                    'page' => (int)$i,
                    'total_pages' => (int)$this->totalPages,
                    'products' => $productsDetails,
                ];
            }

            return $response;
        }

        return 'No categoryId value';
    }

    public function getCategoryTree()
    {
        $categoriesTree = $this->categoryTree($this->helper->getRootCategoryId());

        return $categoriesTree;
    }

    private function categoryTree($id)
    {
        $category = Mage::getModel('catalog/category');

        $_category = $category->setStoreId($this->helper->getStoreId())->load($id);

        $details = [
            'id' => $_category->getId(),
            'name' => $_category->getName(),
            'url' => $this->helper->getHomeUrl() . $_category->getUrlPath(),
            'url_key' => $_category->getUrlKey(),
            'level' => $_category->getLevel(),
            'type' => 'category',
            'children' => [],
        ];

        foreach (array_filter(explode(',', $_category->getChildren())) as $childId) {
            $details['children'][] = $this->categoryTree($childId);
        }
        if (count($details['children']) === 0) {
            unset($details['children']);
        }

        if ($_category->getId() == $this->helper->getBrandPositionCategoryId()) {
            $details['children'] = $this->getBrands($_category->getLevel());
        }

        return $details;
    }

    /**
     * @param $level
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getBrands($level)
    {
        $details = [];

        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $this->helper->getBrandAttributeCode());

        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions();
        } else {
            return $details;
        }

        foreach ($options as $option) {
            $brandUrl = str_replace(' ', '_', strtolower($option['label']));

            $details[] = [
                'id' => $option['value'],
                'name' => $option['label'],
                'url' => $this->helper->getHomeUrl() . $brandUrl . '.html',
                'url_key' => $brandUrl,
                'level' => $level + 1,
                'type' => $this->helper->getBrandAttributeCode(),
                'children' => [],
            ];
        }

        return $details;
    }

    /**
     * @param $sorterParams
     * @return mixed
     * @throws Exception
     */
    private function setProductPositionForBrand($sorterParams)
    {
        $positionCounter = 10000;
        $productIds = [];

        foreach ($sorterParams->products as $productId => $position) {
            if (!empty($productId) && is_numeric($position)) {
                $productIds[] = $productId;
                $action = Mage::getModel('catalog/resource_product_action');
                $action->updateAttributes([$productId],
                    [
                        $this->helper->getBrandPositionId() => $positionCounter - $position,
                    ],
                    $this->helper->getStoreId()
                );
            }
            $this->helper->reportLog('Position: ' . ($positionCounter - $position), 'setProductPositionForBrand',
                'high', $productId);
        }

        $this->reindexCatalogProducts($productIds);
        $this->clearCategoryCache('', $sorterParams->category_id);

        return $this->helper->getSessionId();
    }

    /**
     * @param $productIds
     * @throws Exception
     */
    private function reindexCatalogProducts($productIds)
    {
        if ($this->helper->getReindexCategoryProducts()) {
            Mage::getModel('catalog/product_flat_indexer')->updateProduct($productIds);
        }
    }

    /**
     * @param $categoryId
     * @param $page
     * @param $sorterType
     * @return array
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    private function getProductDetailArray($categoryId, $page, $sorterType)
    {
        $brandId = '';

        if ($sorterType === $this->helper->getBrandAttributeCode()) {
            $brandId = $categoryId;
            $categoryId = $this->helper->getRootCategoryId();
        }

        $productsCollection = $this->getCategoryProductCollection($categoryId, $page, $this->helper->getStoreId(),
            $brandId);

        $symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        $productsDetails = [];

        foreach ($productsCollection as $product) {
            try {
                $tempDetails = [];

                $attributesCode = [];

                $qty = 0;

                $totalChildrenInStock = 0;

                $qtyView = [];

                if ($product->getTypeId() === 'configurable') {
                    $childProducts = Mage::getModel('catalog/product_type_configurable')
                        ->getUsedProducts(null, $product);

                    $configurableAttributes = Mage::getModel('catalog/product_type_configurable')
                        ->getConfigurableAttributesAsArray($product);

                    foreach ($configurableAttributes as $configurableAttribute) {
                        $attributesCode[] = $configurableAttribute['attribute_code'];

                        foreach ($configurableAttribute['values'] as $attributeValue) {
                            $attributeValueId = $attributeValue['value_index'];
                            $attributeDefaultLabel = $attributeValue['default_label'];

                            $tempDetails[$attributeValueId] = $attributeDefaultLabel;
                        }
                    }

                    foreach ($childProducts as $childProduct) {
                        $attributesLabel = '';
                        $childQty = 0;

                        if ($childProduct->getStockItem()) {
                            $childQty = $childProduct->getStockItem()->getQty();
                            $childQty = (int)$childQty;
                        } elseif ($stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct)) {
                            $childQty = (int)$stockItem->getQty();
                        }

                        $qty += $childQty;
                        $inStock = false;

                        if ($childQty >= $this->helper->getMinInStock() && $this->helper->getMinInStock() !== 0) {
                            $totalChildrenInStock += 1;
                            $inStock = true;
                        }

                        foreach ($attributesCode as $attributeCode) {
                            $attributesLabel .= $tempDetails[$childProduct->getData($attributeCode)] . ' => ';
                        }

                        $qtyView[$childProduct->getSku()]['label'] = $attributesLabel;
                        $qtyView[$childProduct->getSku()]['qty'] = $childQty;
                        $qtyView[$childProduct->getSku()]['in_stock'] = $inStock;
                    }

                } else {
                    $stockItem = Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($product->getEntityId());

                    $qty = 0;

                    if ($stockItem) {
                        $qty = (int)$stockItem->getQty();
                    }

                    $inStock = false;

                    if ($qty >= $this->helper->getMinInStock() && $this->helper->getMinInStock() !== 0) {
                        $totalChildrenInStock = 1;
                        $inStock = true;
                    }

                    $qtyView[$product->getSku()]['label'] = $product->getSku();
                    $qtyView[$product->getSku()]['qty'] = $qty;
                    $qtyView[$product->getSku()]['in_stock'] = $inStock;
                }

                $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(200, 200);

                $productPrice = round($product->getPrice(), 2);

                $productSpecialPrice = $product->getFinalPrice() < $product->getPrice() ? round($product->getFinalPrice(),
                    2) : -1;

                $additionalAttributes = $this->getProductAdditionalAttributes($product);

                $tempDetails = [
                    'id' => $product->getEntityId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'url' => $product->getProductUrl(true),
                    'image' => $imageUrl,
                    'price' => $productPrice,
                    'special_price' => $productSpecialPrice,
                    'qty' => $qty,
                    'qty_view' => $qtyView,
                    'total_childs_in_stock' => $totalChildrenInStock,
                    'currency_symbol' => $symbol,
                    'additional_attributes' => $additionalAttributes,
                ];

                $productDetails = new Varien_Object($tempDetails);
                Mage::dispatchEvent('editea_sorter_add_products_details_before',
                    ['product_details' => $productDetails, 'product' => $product]);

                $productsDetails[] = $productDetails->getData();
            } catch (Exception $e) {
                $this->helper->reportLog($e->getMessage(), 'getProductDetailArray', 'high', $product->getEntityId());
            }
        }

        return $productsDetails;
    }

    private function getProductAdditionalAttributes($product)
    {
        $response = [];

        foreach ($this->helper->getAdditionalAttributes() as $attributeId => $attributeCode) {
            if ($optionValue = $product->getAttributeText($attributeCode)) {
                $response[$attributeCode] = $optionValue;
            } else {
                $response[$attributeCode] = $product->getData($attributeCode);
            }
        }

        return $response;
    }

    public function getAdditionalAttributes()
    {
        return $this->helper->getAdditionalAttributes();
    }

    /**
     * @param $categoryId
     * @param $page
     * @param $storeId
     * @param $brandId
     * @return array
     * @throws Mage_Core_Exception
     */
    private function getCategoryProductCollection($categoryId, $page, $storeId, $brandId)
    {
        $category = Mage::getModel('catalog/category')
            ->setStoreId($storeId)
            ->load($categoryId);

        Mage::unregister('current_category');

        Mage::register('current_category', $category);

        $block = Mage::app()
            ->setCurrentStore($storeId)
            ->getLayout()->createBlock('catalog/product_list')
            ->setCategoryId($categoryId);

        Mage::unregister('_singleton/catalog/layer');

        $toolbar = $block->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $block->getLoadedProductCollection();

        // use sortable parameters
        if ($orders = $block->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $block->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $block->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $block->getModes()) {
            $toolbar->setModes($modes);
        }

        if (!empty($brandId)) {
            $collection = $collection->addAttributeToFilter($this->helper->getBrandAttributeCode(), $brandId);
        }

        // set collection to toolbar and apply sort
        $toolbar->getRequest()->setParam('p', $page);
        $toolbar->setCollection($collection);

        Mage::dispatchEvent('catalog_block_product_list_collection', [
            'collection' => $collection,
        ]);

        $lastPageNum = $collection->getLastPageNumber();

        $this->totalPages = $lastPageNum;

        if ($page > $lastPageNum) {
            return [];
        }

        return $collection;
    }

    public function clearOldReports()
    {
        $months = $this->helper->getSaveLogsMonths();

        // if debug mode enable clear old reports from report table
        if ($months > 0 && $this->helper->getDebugMode('low')) {
            $this->clearOldReportsByMonths($months);
        }
    }

    private function clearOldReportsByMonths($months)
    {
        $table = $this->getResource()->getMainTable();
        $deleteDate = date('Y-m-d 00:00:00', strtotime('-' . $months . ' month'));
        $this->conn->query('DELETE FROM' . $table . ' WHERE created_at < ' . $deleteDate . ';');
    }
}
	 