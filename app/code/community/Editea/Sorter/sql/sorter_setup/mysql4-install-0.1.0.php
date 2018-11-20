<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `editea_report` (
  `editea_report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(255) DEFAULT NULL,
  `report_level` varchar(255) DEFAULT NULL,
  `report_item_id` varchar(255) DEFAULT NULL,
  `report_message` text DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`editea_report_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 