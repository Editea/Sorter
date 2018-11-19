<?php

class Editea_Sorter_Model_Validetor extends Mage_Core_Model_Abstract
{
    protected $helper = '';

    protected $mode = 'CBC';

    protected function _construct(){

       $this->_init("sorter/validetor");

       $this->helper = Mage::helper('sorter');
    }

    public function validate($auth, $ts)
    {
        $this->helper->reportLog('auth: ' . $auth . ' ts:' . $ts, 'validetor-data', 'high');

        $userApi = $this->helper->getUserApi();
        $tokenApi = $this->helper->getTokenApi();

        if (empty($userApi) || empty($tokenApi))
            return false;

        $validCombineMd5 =  md5($tokenApi . $ts . $userApi);

        $this->helper->reportLog('validetorCombineMd5: ' . $validCombineMd5 . ' userAPI:' . $userApi, 'validetor-check', 'high');

        if ($validCombineMd5 !== $auth)
            return false;

        return true;
    }
}
	 