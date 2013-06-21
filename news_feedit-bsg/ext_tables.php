<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_newsfeedit_fe_cruser_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_feedit/locallang_db.php:tt_news.tx_newsfeedit_fe_cruser_id",		
		"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					),
				"foreign_table" => "fe_users",
			"minitems" => 0,
			"maxitems" => 1,
				/*
			"type" => "input",
			"size" => "4",
			"max" => "4",
			"eval" => "int",
			"checkbox" => "0",
			"default" => 0
				*/
		)
	),
	"tx_newsfeedit_fe_crgroup_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_feedit/locallang_db.php:tt_news.tx_newsfeedit_fe_crgroup_id",		
		"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					),
				"foreign_table" => "fe_groups",
			"minitems" => 0,
			"maxitems" => 1,
				/*
			"type" => "input",
			"size" => "4",
			"max" => "4",
			"eval" => "int",
			"checkbox" => "0",
			"default" => 0
				*/
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_newsfeedit_fe_cruser_id;;;;1-1-1, tx_newsfeedit_fe_crgroup_id");


t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:mth_feedit/res/flexform_ds.xml');


t3lib_extMgm::addPlugin(Array('LLL:EXT:news_feedit/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/css/','CSS Styles');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/ts_config/','Default Config');

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_newsfeedit_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_newsfeedit_pi1_wizicon.php';

?>