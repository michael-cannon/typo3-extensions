<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

// MLC turn off survey results icon
if ( false && TYPO3_MODE=="BE")	{
	t3lib_extMgm::addModule("web","txmssurveyM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

$TCA["tx_mssurvey_items"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_items",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"type" => "type",	
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_mssurvey_items.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, multitem, break, type, question, description, itemvalues, itemrows, exclude, items, width, height",
	)
);

$TCA["tx_mssurvey_results"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_results",		
		"label" => "fe_cruser_id",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"fe_cruser_id" => "fe_cruser_id",
		"default_sortby" => "ORDER BY crdate DESC",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_mssurvey_results.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, surveyid, fe_cruser_id, results, remoteaddress",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,recursive";

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:ms_survey/flexform_ds.xml');

t3lib_extMgm::addPlugin(Array("LLL:EXT:ms_survey/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


//t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Survey");						


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_mssurvey_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_mssurvey_pi1_wizicon.php";
?>
