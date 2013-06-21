<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_cbsalesforce_salesforceid" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbsalesforce/locallang_db.php:fe_users.tx_cbsalesforce_salesforceid",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_cbsalesforce_salesforceid;;;;1-1-1");

$tempColumns = Array (
	"tx_cbsalesforce_salesforceid" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbsalesforce/locallang_db.php:fe_groups.tx_cbsalesforce_salesforceid",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("fe_groups");
t3lib_extMgm::addTCAcolumns("fe_groups",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_groups","tx_cbsalesforce_salesforceid;;;;1-1-1");
?>