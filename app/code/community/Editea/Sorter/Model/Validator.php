<?php

class Editea_Sorter_Model_Validator extends Mage_Core_Model_Abstract
{
    protected $helper = '';

    protected function _construct()
    {
        $this->_init('sorter/validator');
        $this->helper = Mage::helper('sorter');
    }

    /**
     * @param $auth
     * @param $ts
     * @return bool
     */
    public function validate($requestUserApi, $requestToken)
    {
        $isValid = false;

        $this->helper->reportLog('requestUserApi: ' . $requestUserApi . ' requestToken:' . $requestToken, 'request-api', 'high');

        $userApi = $this->helper->getUserApi();
        $tokenApi = $this->helper->getTokenApi();

        if ($userApi && $tokenApi) {
            $isValid = ($userApi === $requestUserApi && $tokenApi === $requestToken);
        }
        $this->helper->reportLog(
            'userApi: ' . $userApi . ' tokenApi:' . $tokenApi, 'app-api', 'high'
        );
        return $isValid;
    }
}
