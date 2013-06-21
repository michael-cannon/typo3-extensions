<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$TCA["tx_salesforcesubscribe_subscribes"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:salesforce_subscribe/locallang_db.php:tx_salesforcesubscribe_subscribes",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_salesforcesubscribe_subscribes.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, fe_user, sponsor",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:salesforce_subscribe/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Salesforce integration");						


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_salesforcesubscribe_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_salesforcesubscribe_pi1_wizicon.php";
?>