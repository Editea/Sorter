<?php

class Editea_Sorter_Model_Cron
{
    public function clearOldReports()
    {
        if (Mage::helper('sorter')->getClearOldLogReports()) {
            Mage::getModel('sorter/sorter')->clearOldReports();
        }
    }
}