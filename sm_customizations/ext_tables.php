<?php
/* $Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:04:03 peimic.comprock Exp $ */
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_smcustomizations_terms_agree" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_terms_agree",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_smcustomizations_income_range" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_income_range",		
		"config" => Array (
			"type" => "input",
			"size" => 30,
		)
	),
	"tx_smcustomizations_education_level" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_education_level",		
		"config" => Array (
			"type" => "input",
			"size" => 30,
		)
	),
	"tx_smcustomizations_shopping_frequency" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_shopping_frequency",		
		"config" => Array (
			"type" => "input",
			"size" => 30,
		)
	),
	"tx_smcustomizations_online_shopping" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_online_shopping",		
		"config" => Array (
			"type" => "input",
			"size" => 30,
		)
	),
	"tx_smcustomizations_how_found" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_how_found",		
		"config" => Array (
			"type" => "input",
			"size" => 30,
		)
	),
	"tx_smcustomizations_how_found_text" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_how_found_text",		
		"config" => Array (
			"type" => "input",
			"size" => 30,
		)
	),
	"tx_smcustomizations_age" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sm_customizations/locallang_db.xml:fe_users.tx_smcustomizations_age",		
		"config" => Array (
			"type" => "input",
			"size" => 3,
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_smcustomizations_terms_agree;;;;1-1-1, tx_smcustomizations_income_range, tx_smcustomizations_education_level, tx_smcustomizations_shopping_frequency, tx_smcustomizations_online_shopping, tx_smcustomizations_how_found,  tx_smcustomizations_how_found_text, tx_smcustomizations_age ");
?>