<?php
class Editea_Sorter_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    private $sorterModel = '';

    public function __construct()
    {
        $this->sorterModel = Mage::getModel("sorter/sorter");
    }

    public function setproductsposition($jsonRequest)
    {
        return $this->sorterModel->setProductsPosition($jsonRequest);
    }

    public function getcategoriestree()
    {
        return $this->sorterModel->getCategoriesTree();
    }

    public function getbrandstree()
    {
        return $this->sorterModel->getBrands();
    }

    public function getproductsdetails($jsonRequest)
    {
        return $this->sorterModel->getProductsDetails($jsonRequest);
    }


}