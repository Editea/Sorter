<?php
class Editea_Sorter_IndexController extends Mage_Core_Controller_Front_Action{

    private $sorterModel = '';

    private $validetorModel = '';

    private $jsonRequest = '';

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request,  $response, $invokeArgs);

        $this->sorterModel = Mage::getModel("sorter/sorter");

        $this->validetorModel = Mage::getModel("sorter/validetor");

        $jsonRequest = $this->getRequest()->getPost('data');

        $this->jsonRequest = $jsonRequest;
    }

    private function returnResponse($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($response);
    }

    private function returnErrorResponse()
    {
        $message = 'Post request is not valid (data param is not set)';
        $this->getResponse()->setHeader('HTTP/1.0','400',true);
        $this->getResponse()->setBody($message);

        return false;
    }

    private function returnBadAuthResponse()
    {
        $message = 'Post request is not authorize';
        $this->getResponse()->setHeader('HTTP/1.0','400',true);
        $this->getResponse()->setBody($message);

        return false;
    }

    private function returnUnactiveResponse()
    {
        $message = 'API is not active';
        $this->getResponse()->setHeader('HTTP/1.0','400',true);
        $this->getResponse()->setBody($message);

        return false;
    }

    public function IndexAction() {
        $version = Mage::helper('sorter')->getExtensionVersion();

        $this->returnResponse($version);
    }

    private function validateRequest()
    {
        if (!Mage::helper('sorter')->getIsActive())
            return $this->returnUnactiveResponse();

        if (empty($this->jsonRequest))
            return $this->returnErrorResponse();

        $sorterParams = json_decode($this->jsonRequest);

        if (empty($sorterParams->auth) || empty($sorterParams->ts))
            return $this->returnErrorResponse();

        if (!$this->validetorModel->validate($sorterParams->auth, $sorterParams->ts))
            return $this->returnBadAuthResponse();

        return true;
    }

    public function checkAuthAction()
    {
        if (!$this->validateRequest())
            return $this;

        $this->returnResponse(true);
    }

    public function setProductsPositionAction()
    {
        if (!$this->validateRequest())
            return $this;

        $response = $this->sorterModel->setProductsPosition($this->jsonRequest);

        $this->returnResponse($response);
    }

    public function getCategoriesTreeAction()
    {
        if (!$this->validateRequest())
            return $this;

        $response = $this->sorterModel->getCategoriesTree($this->jsonRequest);

        $this->returnResponse($response);
    }

    public function getProductsDetailsAction()
    {
        if (!$this->validateRequest())
            return $this;

        $response = $this->sorterModel->getProductsDetails($this->jsonRequest);

        $this->returnResponse($response);
    }
}