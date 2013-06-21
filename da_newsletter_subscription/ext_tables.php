<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::addModule("web","txdanewslettersubscriptionM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

t3lib_extMgm::addStaticFile($_EXTKEY,"static/","Newsletter subscription");

t3lib_extMgm::allowTableOnStandardPages("tx_danewslettersubscription_cat");
$TCA["tx_danewslettersubscription_cat"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_cat",
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
			"starttime" => "starttime",
			"endtime" => "endtime",
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_danewslettersubscription_cat.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, descr, editor",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_danewslettersubscription_newsletter");
$TCA["tx_danewslettersubscription_newsletter"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_newsletter",
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
			"starttime" => "starttime",
			"endtime" => "endtime",
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_danewslettersubscription_cat.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, link_file, category, html_body",
	)
);

$tempColumns['newsletter']['label'] = "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_newsletter.newsletter";
//$tempColumns['newsletter']['exclude'] = 1;
$tempColumns['newsletter']['config'] = Array(
		'type' => 'select',
		'items' => Array (
				  Array('',0),
		),
		'foreign_table' => 'tx_danewslettersubscription_newsletter',
		'size' => 10,
    'minitems' => 0,
    'maxitems' => 1,
    'default' => '',
	);

t3lib_div::loadTCA('tt_news');
t3lib_extMgm::addTCAcolumns('tt_news',$tempColumns,1);
//$GLOBALS['TCA']['tt_news']['types']['0']['showitem'] .= ',newsletter';

t3lib_extMgm::addToAllTCAtypes('tt_news','newsletter;;;;1-1-1');

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="bodytext;LLL:EXT:da_newsletter_subscription/locallang_db.php:bodytext.ALT.mailform;;nowrap:wizards[nl_forms];1-1-1";

$TCA["tt_content"]["columns"]["bodytext"]["config"]["wizards"]["nl_forms"]= Array(
	"notNewRecords" => 1,
	"enableByTypeConfig" => 1,
	"type" => "script",
	"title" => "Newsletter Forms wizard",
	"icon" => "wizard_forms.gif",
	"script" => "wizard_forms.php",
);

t3lib_extMgm::addPlugin(Array("LLL:EXT:da_newsletter_subscription/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi2"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:da_newsletter_subscription/locallang_db.php:tt_content.list_type_pi2", $_EXTKEY."_pi2"),"list_type");

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi3"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:da_newsletter_subscription/locallang_db.php:tt_content.list_type_pi3", $_EXTKEY."_pi3"),"list_type");

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi4"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:da_newsletter_subscription/locallang_db.php:tt_content.list_type_pi4", $_EXTKEY."_pi4"),"list_type");

?>