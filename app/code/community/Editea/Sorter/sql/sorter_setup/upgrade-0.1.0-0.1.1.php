<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `editea_validetor` (
  `editea_validetor_id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` varchar(255) DEFAULT NULL,
  `token` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`editea_validetor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQLTEXT;

$installer->run($sql);

$installer->endSetup();