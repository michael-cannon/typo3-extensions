<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("tools","txgeoclassauctionM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:geoclassauction/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','GeoClassAuction Director');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:geoclassauction/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi2/static/','GeoClassAuction Leads');

$TCA["tx_geoclassauction_auctionsites"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_auctionsites",		
		"label" => "sitename",	
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
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_geoclassauction_auctionsites.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, sitename, siteurl, codes, fe_user, description",
	)
);

$TCA["tx_geoclassauction_leads"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads",		
		"label" => "fe_user",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_geoclassauction_leads.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, auction, fe_user, attendanceday, attendancetime, vehicle, pleasecall, note, howheard, eventcode, isdealer, contacted, internalnotes",
	)
);

$tempColumns = Array (
	"tx_geoclassauction_homephone" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:geoclassauction/locallang_db.php:fe_users.tx_geoclassauction_homephone",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_geoclassauction_homephone;;;;1-1-1");
?>