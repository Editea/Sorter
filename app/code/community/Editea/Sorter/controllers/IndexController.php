<?php

class Editea_Sorter_IndexController extends Mage_Core_Controller_Front_Action
{
    /** @var Editea_Sorter_Model_Sorter $sorterModel */
    private $sorterModel;

    /** @var Editea_Sorter_Model_Validator $validatorModel */
    private $validatorModel;

    private $jsonRequest;

    private $helper;

    /**
     * Editea_Sorter_IndexController constructor.
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = []
    ) {
        parent::__construct($request, $response, $invokeArgs);

        $this->sorterModel = Mage::getModel('sorter/sorter');

        $this->validatorModel = Mage::getModel('sorter/validator');

        $jsonRequest = $this->getRequest()->getPost('data');

        $this->jsonRequest = $jsonRequest;
    }

    public function IndexAction()
    {
        $version = $this->getHelper()->getExtensionVersion();
        $this->prepareResponse($version);
    }

    /**
     * Test Auth
     */
    public function checkAuthAction()
    {
        $this->handleRequest(true);
    }

    /**
     * Update Product Positions
     */
    public function setProductPositionAction()
    {
        $this->handleRequest(
            $this->sorterModel->setProductPosition($this->jsonRequest)
        );
    }

    /**
     * Retrieve Category Tree
     */
    public function getCategoryTreeAction()
    {
        $this->handleRequest(
            $this->sorterModel->getCategoryTree()
        );
    }

    /**
     * Retrieve product details
     */
    public function getProductDetailAction()
    {
        $this->handleRequest(
            $this->sorterModel->getProductsDetails($this->jsonRequest)
        );
    }

    /**
     * Validate & Respond
     * @param $response
     */
    protected function handleRequest($response)
    {
        if ($this->validateRequest()) {
            $this->prepareResponse($response);
        }
    }

    /**
     * @return bool
     */
    private function validateRequest()
    {
        $isValid = false;
        if (!$this->getHelper()->getIsActive()) {
            $this->prepareInactiveResponse();
        } elseif (!$this->jsonRequest) {
            $this->prepareInvalidResponse();
        } elseif (!$this->validateAuth()) {
            $this->prepareBadAuthResponse();
        } else {
            $isValid = true;
        }
        return $isValid;
    }

    /**
     * @return bool
     */
    private function validateAuth()
    {
        $sorterParams = json_decode($this->jsonRequest);
        return ($sorterParams->user && $sorterParams->token) &&
            $this->validatorModel->validate($sorterParams->user, $sorterParams->token);
    }

    private function prepareResponse($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($response);
    }

    private function prepareInvalidResponse()
    {
        $this->prepareErrorResponse('Invalid Request');
    }

    private function prepareBadAuthResponse()
    {
        $this->prepareErrorResponse('Unauthorized Request');
    }

    private function prepareInactiveResponse()
    {
        $this->prepareErrorResponse('API Inactive');
    }

    private function prepareErrorResponse($message)
    {
        $this->getResponse()->setHeader('HTTP/1.0', '400', true);
        $this->getResponse()->setBody($message);
    }

    /**
     * @return Editea_Sorter_Helper_Data
     */
    private function getHelper()
    {
        if (null === $this->helper) {
            $this->helper = Mage::helper('sorter');
        }
        return $this->helper;
    }
}