<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_srfeuserregistersurvey_display_survey" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tt_content.tx_srfeuserregistersurvey_display_survey",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_srfeuserregistersurvey_survey_storage_pid" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tt_content.tx_srfeuserregistersurvey_survey_storage_pid",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_srfeuserregistersurvey_survey_results_pid" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tt_content.tx_srfeuserregistersurvey_survey_results_pid",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_srfeuserregistersurvey_survey_usergroups" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tt_content.tx_srfeuserregistersurvey_survey_usergroups",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "fe_groups",	
			"foreign_table_where" => "ORDER BY fe_groups.uid",	
			"size" => 4,	
			"minitems" => 0,
			"maxitems" => 25,	
			"MM" => "tt_content_tx_srfeuserregistersurvey_survey_usergroups_mm",
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_srfeuserregistersurvey_display_survey;;;;1-1-1, tx_srfeuserregistersurvey_survey_storage_pid, tx_srfeuserregistersurvey_survey_results_pid, tx_srfeuserregistersurvey_survey_usergroups");

$tempColumns = Array (
	"tx_srfeuserregistersurvey_survey_check" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:pages.tx_srfeuserregistersurvey_survey_check",		
		"config" => Array (
			"type" => "check",
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_srfeuserregistersurvey_survey_check;;;;1-1-1");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:sr_feuser_register_survey/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Registration survey");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_srfeuserregistersurvey_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_srfeuserregistersurvey_pi1_wizicon.php';


if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("web","txsrfeuserregistersurveyM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}


t3lib_extMgm::allowTableOnStandardPages("tx_srfeuserregistersurvey_results_archive");


t3lib_extMgm::addToInsertRecords("tx_srfeuserregistersurvey_results_archive");

$TCA["tx_srfeuserregistersurvey_results_archive"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tx_srfeuserregistersurvey_results_archive",		
		"label" => "survey_user_id",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_srfeuserregistersurvey_results_archive.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "survey_result_id, survey_user_id, survey_result, remoteaddress",
	)
);


if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_srfeuserregistersurvey_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_srfeuserregistersurvey_modfunc1.php",
		"LLL:EXT:sr_feuser_register_survey/locallang_db.php:moduleFunction.tx_srfeuserregistersurvey_modfunc1",
		"wiz"	
	);
}
?>