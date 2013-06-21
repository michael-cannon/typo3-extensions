<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:gb_certificate/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Certificate Listing");


t3lib_extMgm::allowTableOnStandardPages("tx_gbcertificate_courses");


t3lib_extMgm::addToInsertRecords("tx_gbcertificate_courses");

$TCA["tx_gbcertificate_courses"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_courses",		
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
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_gbcertificate_courses.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, code, title, detail_pid, show_certificate, course_prerequisites, hours",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_gbcertificate_course_users");


t3lib_extMgm::addToInsertRecords("tx_gbcertificate_course_users");

$TCA["tx_gbcertificate_course_users"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:gb_certificate/locallang_db.php:tx_gbcertificate_course_users",		
		"label" => "name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_gbcertificate_course_users.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, username, name, number, dates, code",
	)
);
?>