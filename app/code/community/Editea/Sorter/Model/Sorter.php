<?php

class Editea_Sorter_Model_Sorter extends Mage_Core_Model_Abstract
{
    protected $helper = '';
    protected $conn = '';
    protected $totalPages = 1;


    protected function _construct(){

       $this->_init("sorter/sorter");

       $this->conn = Mage::getModel('core/resource')->getConnection('core_write');

       $this->helper = Mage::helper('sorter');

       $this->clearOldReports();
    }

    public function setProductsPosition($jsonRequest)
    {
        $this->helper->reportLog($jsonRequest, 'setProductsPosition');

        $sorterParams = json_decode($jsonRequest);

        if ($sorterParams->sorter_type == "brand" && !empty($this->helper->getBrandPositionId()))
            return $this->setProductPositionForBrand($sorterParams);

        $categoryId = $sorterParams->category_id;

        $this->helper->reportLog('category-id: ' . $categoryId, 'setProductsPosition');

        $productsIds = array();

        foreach ($sorterParams->products as $productId => $position)
        {
            $productsIds[] = $productId;

            if (!empty($productId) && !empty($categoryId) && is_numeric($position))
                $this->setProductPositionByCategory($categoryId, $productId, $position);

            $this->helper->reportLog('position: ' . $position, 'setProductsPosition', 'high', $productId);
        }

        $this->reindexCatalogCategoryProduct($productsIds);

        $this->clearCategoryCache($categoryId, $sorterParams->category_url);

        return $this->helper->getSessionId();
    }

    private function setProductPositionByCategory($categoryId, $productId, $position)
    {
        $table = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');

        $productOrderData = array(
            'product_id'  => (int)$productId,
            'category_id' => (int)$categoryId,
            'position'    => (int)$position
        );

        $queryUpdate = array_keys($productOrderData);
        $this->conn->insertOnDuplicate($table, $productOrderData, $queryUpdate);
    }

    private function reindexCatalogCategoryProduct($productIds)
    {
        if ($this->helper->getReindexCategoryProducts()) {
            $event = Mage::getModel('index/event');
            $event->setNewData(array('product_ids' => $productIds));
            Mage::getResourceSingleton('catalog/category_indexer_product')->catalogProductMassAction($event);
        }
    }

    private function clearCategoryCache($categoryId, $categoryUrl)
    {
        if ($this->helper->getCleanCategoryCache()) {

            if (!empty($categoryId))
                Mage::app()->cleanCache(array(Mage_Catalog_Model_Category::CACHE_TAG . '_' . $categoryId));

            if (Mage::helper('core')->isModuleEnabled('Nexcessnet_Turpentine') && !empty($categoryUrl)) {

                $categoryUrl = substr($categoryUrl, strrpos($categoryUrl, '/') + 1);

                $result = Mage::getModel('turpentine/varnish_admin')->flushUrl($categoryUrl);

                $this->helper->reportLog($result, 'clearCategoryCache', 'high');
            }
        }
    }

    public function getProductsDetails($jsonRequest)
    {
        $this->helper->reportLog($jsonRequest, 'getProductsDetails');

        $sorterParams = json_decode($jsonRequest);
        $categoryId = $sorterParams->category_id;
        $page = $sorterParams->page;
        $sorterType = $sorterParams->sorter_type;
        $totalPages = $sorterParams->total_pages;

        if (empty($totalPages))
            $totalPages = 1;

        if (empty($page))
            return 'No page value';

        $this->helper->reportLog('Sorter Type: ' . $sorterType, 'getProductsDetails');

        if (!empty($categoryId) && !empty($page)) {

            $response = array();

            for ($i = $page; $i < ($totalPages + $page); $i++)
            {
                $productsDetails = $this->getProductsDetailsArray($categoryId, $i, $sorterType);

                $response[] = array(
                    'page' => (int)$i,
                    'total_pages' => (int)$this->totalPages,
                    'products' => $productsDetails
                );
            }

            return json_encode($response);
        }

        return 'No categoryId value';
    }

    public function getCategoriesTree()
    {
        $categoriesTree = $this->categoriesTree($this->helper->getRootCategoryId());

        return json_encode($categoriesTree);
    }

    private function categoriesTree($id)
    {
        $category = Mage::getModel('catalog/category');

        $_category = $category->setStoreId($this->helper->getStoreId())->load($id);

        $details = array(
            'id' => $_category->getId(),
            'name' => $_category->getName(),
            'url' => $this->helper->getHomeUrl() . $_category->getUrlPath(),
            'url_key' => $_category->getUrlKey(),
            'level' => $_category->getLevel(),
            'type' => 'category',
            'children' => array()
        );

        foreach (array_filter(explode(',', $_category->getChildren())) as $childId) {
            $details['children'][] = $this->categoriesTree($childId);
        }
        if (count($details['children']) === 0) {
            unset($details['children']);
        }

        if($_category->getId() == $this->helper->getBrandPositionCategoryId())
            $details['children'] = $this->getBrands($_category->getLevel());

        return $details;
    }

    public function getBrands($level)
    {
        $details = array();

        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $this->helper->getBrandAttributeCode());

        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        } else{
            return $details;
        }

        foreach ($options as $option)
        {
            $brandUrl = str_replace(" ","_",strtolower($option['label']));

            $details[] = array(
                'id' => $option['value'],
                'name' => $option['label'],
                'url' => $this->helper->getHomeUrl() . $brandUrl . '.html',
                'url_key' => $brandUrl,
                'level' => $level + 1,
                'type' => $this->helper->getBrandAttributeCode(),
                'children' => array()
            );
        }

        return $details;
    }

    private function setProductPositionForBrand($sorterParams)
    {
        $positionCounter = 10000;
        $productIds = array();

        foreach ($sorterParams->products as $productId => $position)
        {
            if (!empty($productId)  && is_numeric($position)) {
                $productIds[] = $productId;
                $action = Mage::getModel('catalog/resource_product_action');
                $action->updateAttributes(array($productId),
                    array(
                        $this->helper->getBrandPositionId() => ($positionCounter - $position)
                    ),
                    $this->helper->getStoreId()
                );
            }
            $this->helper->reportLog('Position: ' . ($positionCounter - $position), 'setProductPositionForBrand', 'high', $productId);
        }

        $this->reindexCatalogProducts($productIds);
        $this->clearCategoryCache('', $sorterParams->category_id);

        return $this->helper->getSessionId();
    }

    private function reindexCatalogProducts($productIds)
    {
        if ($this->helper->getReindexCategoryProducts()) {
            Mage::getModel('catalog/product_flat_indexer')->updateProduct($productIds, null);
        }
    }

    private function getProductsDetailsArray($categoryId, $page, $sorterType)
    {
        $brandId = '';

        if ($sorterType == $this->helper->getBrandAttributeCode()) {
            $brandId = $categoryId;
            $categoryId = $this->helper->getRootCategoryId();
        }

        $productsCollection = $this->getCategoryProductsCollection($categoryId, $page, $this->helper->getStoreId(), $brandId);

        $symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();

        $productsDetails = array();

        foreach ($productsCollection as $product)
        {
            $tempDetails = array();
            $attributesCode = array();
            $qty = 0;
            $totalChildsInStock = 0;
            $qtyView = array();

            if ($product->getTypeId() == 'configurable')
            {
                $childProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getUsedProducts(null, $product);

                $configurableAttributes = Mage::getModel('catalog/product_type_configurable')
                    ->getConfigurableAttributesAsArray($product);

                foreach ($configurableAttributes as $configurableAttribute)
                {
                    $attributesCode[] = $configurableAttribute['attribute_code'];

                    foreach ($configurableAttribute['values'] as $attributeValue)
                    {
                        $attributeValueId = $attributeValue['value_index'];
                        $attributeDefaultLable = $attributeValue['default_label'];

                        $tempDetails[$attributeValueId] = $attributeDefaultLable;
                    }
                }

                foreach ($childProducts as $childProduct)
                {
                    $attributesLabel = '';
                    $childQty = $childProduct->getStockItem()->getQty();
                    $childQty = intval($childQty);
                    $qty += $childQty;
                    $inStock = false;

                    if ($childQty >= $this->helper->getMinInStock() && $this->helper->getMinInStock() !== 0) {
                        $totalChildsInStock += 1;
                        $inStock = true;
                    }

                    foreach ($attributesCode as $attributeCode)
                    {
                        $attributesLabel .= $tempDetails[$childProduct->getData($attributeCode)] . ' => ';
                    }

                    $qtyView[$childProduct->getSku()]['label'] = $attributesLabel;
                    $qtyView[$childProduct->getSku()]['qty'] = $childQty;
                    $qtyView[$childProduct->getSku()]['in_stock'] = $inStock;

                }

            } else{
                $stockItem = Mage::getModel('cataloginventory/stock_item')
                    ->loadByProduct($product->getEntityId());

                $qty = intval($stockItem->getQty());

                $inStock = false;

                if ($qty >= $this->helper->getMinInStock() && $this->helper->getMinInStock() !== 0) {
                    $totalChildsInStock = 1;
                    $inStock = true;
                }

                $qtyView[$product->getSku()]['label'] = $product->getSku();
                $qtyView[$product->getSku()]['qty'] = $qty;
                $qtyView[$product->getSku()]['in_stock'] = $inStock;
            }

            $imageUrl = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize(200,200);

            $productPrice = round($product->getPrice(), 2);

            $productSpecialPrice = $product->getFinalPrice() < $product->getPrice() ? round($product->getFinalPrice(),2) : -1;

            $additionalAttributes = $this->getAdditionalAttributes($product);

            $tempDetails = array(
                'id' => $product->getEntityId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'url' => $product->getProductUrl(true),
                'image' => $imageUrl,
                'price' => $productPrice,
                'special_price' => $productSpecialPrice,
                'qty' => $qty,
                'qty_view' => $qtyView,
                'total_childs_in_stock' => $totalChildsInStock,
                'currency_symbol' => $symbol,
                'additional_attributes' => $additionalAttributes
            );

            $productDetails = new Varien_Object($tempDetails);
            Mage::dispatchEvent('editea_sorter_add_products_details_before', array('product_details' => $productDetails, 'product' => $product));

            $productsDetails[] = $productDetails->getData();
        }

        return $productsDetails;
    }

    private function getAdditionalAttributes($product)
    {
        $response = array();

        foreach($this->helper->getAdditionalAttributes() as $attributeId => $attributeCode)
        {
            if ($optionValue = $product->getAttributeText($attributeCode))
                $response[$attributeCode] = $optionValue;
            else
                $response[$attributeCode] = $product->getData($attributeCode);
        }

        return $response;
    }

    private function getCategoryProductsCollection($categoryId, $page, $storeId, $brandId)
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

        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection' => $collection
        ));

        $lastPageNum = $collection->getLastPageNumber();

        $this->totalPages = $lastPageNum;

        if ($page > $lastPageNum)
            return array();

        return $collection;
    }

    private function clearOldReports()
    {
        // if debug mode enable clear old reports from report table
        if ($this->helper->getDebugMode('low'))
        {
            $deleteDate = date('Y-m-d 00:00:00', strtotime('-1 month'));
            $this->conn->query("DELETE FROM editea_report where created_at < '" . $deleteDate . "';");
        }
    }
}
	 