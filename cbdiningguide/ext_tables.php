<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_cbdiningguide_price" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbdiningguide/locallang_db.xml:tt_address.tx_cbdiningguide_price",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cbdiningguide_price",	
			"foreign_table_where" => "AND tx_cbdiningguide_price.pid=###CURRENT_PID### ORDER BY tx_cbdiningguide_price.uid",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 1,	
			"MM" => "tt_address_tx_cbdiningguide_price_mm",
		)
	),
	"tx_cbdiningguide_cuisine" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbdiningguide/locallang_db.xml:tt_address.tx_cbdiningguide_cuisine",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cbdiningguide_cuisine",	
			"foreign_table_where" => "AND tx_cbdiningguide_cuisine.pid=###CURRENT_PID### ORDER BY tx_cbdiningguide_cuisine.uid",	
			"size" => 15,	
			"minitems" => 0,
			"maxitems" => 99,	
			"MM" => "tt_address_tx_cbdiningguide_cuisine_mm",	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_cbdiningguide_cuisine",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
			),
		)
	),
	"tx_cbdiningguide_specialty" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbdiningguide/locallang_db.xml:tt_address.tx_cbdiningguide_specialty",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cbdiningguide_specialty",	
			"foreign_table_where" => "AND tx_cbdiningguide_specialty.pid=###CURRENT_PID### ORDER BY tx_cbdiningguide_specialty.uid",	
			"size" => 15,	
			"minitems" => 0,
			"maxitems" => 99,	
			"MM" => "tt_address_tx_cbdiningguide_specialty_mm",	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_cbdiningguide_specialty",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
			),
		)
	),
	"tx_cbdiningguide_neighborhood" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbdiningguide/locallang_db.xml:tt_address.tx_cbdiningguide_neighborhood",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cbdiningguide_neighborhood",	
			"foreign_table_where" => "AND tx_cbdiningguide_neighborhood.pid=###CURRENT_PID### ORDER BY tx_cbdiningguide_neighborhood.uid",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 1,	
			"MM" => "tt_address_tx_cbdiningguide_neighborhood_mm",	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_cbdiningguide_neighborhood",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
			),
		)
	),
	"tx_cbdiningguide_meals" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbdiningguide/locallang_db.xml:tt_address.tx_cbdiningguide_meals",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_cbdiningguide_meals",	
			"foreign_table_where" => "AND tx_cbdiningguide_meals.pid=###CURRENT_PID### ORDER BY tx_cbdiningguide_meals.uid",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 99,	
			"MM" => "tt_address_tx_cbdiningguide_meals_mm",	
			"wizards" => Array(
				"_PADDING" => 2,
				"_VERTICAL" => 1,
				"add" => Array(
					"type" => "script",
					"title" => "Create new record",
					"icon" => "add.gif",
					"params" => Array(
						"table"=>"tx_cbdiningguide_meals",
						"pid" => "###CURRENT_PID###",
						"setValue" => "prepend"
					),
					"script" => "wizard_add.php",
				),
			),
		)
	),
	"tx_cbdiningguide_hours" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbdiningguide/locallang_db.xml:tt_address.tx_cbdiningguide_hours",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("tt_address");
t3lib_extMgm::addTCAcolumns("tt_address",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_address","tx_cbdiningguide_price;;;;1-1-1,
tx_cbdiningguide_cuisine, tx_cbdiningguide_specialty, tx_cbdiningguide_neighborhood, tx_cbdiningguide_meals, tx_cbdiningguide_hours");

$TCA["tx_cbdiningguide_price"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbdiningguide/locallang_db.xml:tx_cbdiningguide_price',		
		'label'     => 'price',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY price",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbdiningguide_price.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, price",
	)
);

$TCA["tx_cbdiningguide_cuisine"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbdiningguide/locallang_db.xml:tx_cbdiningguide_cuisine',		
		'label'     => 'cuisine',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY cuisine",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbdiningguide_cuisine.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, cuisine",
	)
);

$TCA["tx_cbdiningguide_specialty"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbdiningguide/locallang_db.xml:tx_cbdiningguide_specialty',		
		'label'     => 'specialty',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY specialty",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbdiningguide_specialty.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, specialty",
	)
);

$TCA["tx_cbdiningguide_neighborhood"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbdiningguide/locallang_db.xml:tx_cbdiningguide_neighborhood',		
		'label'     => 'neighborhood',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY neighborhood",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbdiningguide_neighborhood.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, neighborhood",
	)
);
$TCA["tx_cbdiningguide_meals"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbdiningguide/locallang_db.xml:tx_cbdiningguide_meals',		
		'label'     => 'meals',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY meals",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbdiningguide_meals.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, meals",
	)
);
?>