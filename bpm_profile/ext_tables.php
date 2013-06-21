<?php
//$Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:06 peimic.comprock Exp $
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_bpmprofile_newsletter1" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter1",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter2" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter2",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter3" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter3",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter4" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter4",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter5" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter5",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter6" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter6",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter7" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter7",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter8" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter8",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter9" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter9",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_bpmprofile_newsletter10" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bpm_profile/locallang_db.php:fe_users.tx_bpmprofile_newsletter10",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_bpmprofile_newsletter1;;;;1-1-1,tx_bpmprofile_newsletter2,tx_bpmprofile_newsletter3,tx_bpmprofile_newsletter4,tx_bpmprofile_newsletter5,tx_bpmprofile_newsletter6, tx_bpmprofile_newsletter7, tx_bpmprofile_newsletter8, tx_bpmprofile_newsletter9, tx_bpmprofile_newsletter10");
?>
