<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_emailscheduler_main"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:emailscheduler/locallang_db.php:tx_emailscheduler_main",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_emailscheduler_main.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, duration, emailcontent",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:emailscheduler/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Email Scheduler");


if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("tools","txemailschedulerM1","top",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}
?>