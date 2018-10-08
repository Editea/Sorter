<?php
class Editea_Sorter_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard{

    public function match(Zend_Controller_Request_Http $request)
    {
        $requestPathInfo = trim($request->getPathInfo(),'/');

        if($requestPathInfo != 'editea_sorter')
            return  false;

        $request->setModuleName('sorter')
            ->setControllerName('index')
            ->setActionName('index');

        $request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            'ronen'
        );

        return true;
    }
}