<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_extMgm::allowTableOnStandardPages("tx_thmailformplus_main");


t3lib_extMgm::addToInsertRecords("tx_thmailformplus_main");

$TCA["tx_thmailformplus_main"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:th_mailformplus/locallang_db.php:tx_thmailformplus_main",		
		"label" => "email_subject",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_thmailformplus_main.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "email_to, email_subject, email_sender, email_redirect, email_requiredfields, email_htmltemplate, email_subject_user,email_sendtouser",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:th_mailformplus/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");

if (TYPO3_MODE=="BE")   {
    t3lib_extMgm::addModule("web","txthmailformplusM2","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}
    

# if (TYPO3_MODE=="BE")	
#    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_thmailformplus_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_thmailformplus_pi1_wizicon.php";
?>