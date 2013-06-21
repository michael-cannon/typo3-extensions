<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['powermail_cond']); // Get backandconfig


t3lib_extMgm::allowTableOnStandardPages('tx_powermailcond_conditions');


t3lib_extMgm::addToInsertRecords('tx_powermailcond_conditions');

$TCA["tx_powermailcond_conditions"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions',		
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_powermailcond_conditions.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, title, rules",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_powermailcond_rules');


t3lib_extMgm::addToInsertRecords('tx_powermailcond_rules');


if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("powermail_cond")."be/class.tx_powermailcond_tx_powermailcond_rules_fieldname.php");

$TCA["tx_powermailcond_rules"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules',		
		'label'     => 'title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_powermailcond_rules.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, title, conjunction, fieldname, ops, conditions, condstring, actions",
	)
);





/*
$tempColumns = Array (
	"tx_powermailcond_conditions" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermail_fieldsets.tx_powermailcond_conditions",		
		"config" => Array (
			"type" => "select",	
			"items" => Array (
				Array("",0),
			),
			"foreign_table" => "tx_powermailcond_conditions",	
			"foreign_table_where" => "AND tx_powermailcond_conditions.pid=###CURRENT_PID### ORDER BY tx_powermailcond_conditions.uid",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_powermailcond_conditions",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
				"edit" => Array(
					"type" => "popup",
					"title" => "Edit",
					"script" => "wizard_edit.php",
					"popup_onlyOpenIfSelected" => 1,
					"icon" => "edit2.gif",
					"JSopenParams" => "height=550,width=580,status=0,menubar=0,scrollbars=1",
				),
			),
		)
	),
);


t3lib_div::loadTCA("tx_powermail_fieldsets");
t3lib_extMgm::addTCAcolumns("tx_powermail_fieldsets",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_powermail_fieldsets","tx_powermailcond_conditions;;;;1-1-1");
*/





$tempColumns = Array (
	"tx_powermailcond_conditions" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermail_fields.tx_powermailcond_conditions",		
		"config" => Array (
			"type" => "select",	
			"items" => Array (
				Array("",0),
			),
			"foreign_table" => "tx_powermailcond_conditions",	
			"foreign_table_where" => "AND tx_powermailcond_conditions.pid=###CURRENT_PID### ORDER BY tx_powermailcond_conditions.uid",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_powermailcond_conditions",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
				"edit" => Array(
					"type" => "popup",
					"title" => "Edit",
					"script" => "wizard_edit.php",
					"popup_onlyOpenIfSelected" => 1,
					"icon" => "edit2.gif",
					"JSopenParams" => "height=550,width=580,status=0,menubar=0,scrollbars=1",
				),
			),
		),
	),
	"tx_powermailcond_manualcode" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermail_fields.tx_powermailcond_manualcode",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	)
);


t3lib_div::loadTCA("tx_powermail_fields");
t3lib_extMgm::addTCAcolumns("tx_powermail_fields",$tempColumns,1);
if ($confArr['manualEventHandler'] == 1) t3lib_extMgm::addToAllTCAtypes("tx_powermail_fields","tx_powermailcond_conditions;;;;1-1-1, tx_powermailcond_manualcode;;;;1-1-1");
else t3lib_extMgm::addToAllTCAtypes("tx_powermail_fields","tx_powermailcond_conditions;;;;1-1-1");
?>