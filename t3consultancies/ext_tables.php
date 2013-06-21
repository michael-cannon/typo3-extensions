<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
    "tx_t3consultancies_command" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command",        
        "config" => Array (
            "type" => "select",
            "items" => Array (
                Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command.I.0", "listView"),
                Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command.I.1", "singleView"),
                Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command.I.2", "featuredAd"),
                Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command.I.3", "alphabetical"),
                Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command.I.4", "navigation"),
                Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_command.I.5", "category"),
            ),
            "size" => 1,    
            "maxitems" => 1,
        )
    ),
	"tx_t3consultancies_selected_only" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_selected_only",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_t3consultancies_categories" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_categories",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_t3consultancies_cat",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 99,
		)
	),
	"tx_t3consultancies_template" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_template",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
			"max_size" => 500,	
			"uploadfolder" => "uploads/tx_t3consultancies",
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_t3consultancies_categorylisting" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_categorylisting",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_t3consultancies_alphabeticallisting" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_alphabeticallisting",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_extMgm::allowTableOnStandardPages("tx_t3consultancies");


t3lib_extMgm::addToInsertRecords("tx_t3consultancies");

$TCA["tx_t3consultancies"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"fe_cruser_id" => "fe_owner_user",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_t3consultancies.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, description, url, real_url,
		map_url, contact_name, contact_phone, contact_email, address, city,
		state, zip, cntry, services, logo, selected, featured_logo, coupon, weight, fe_owner_user",
	)
);

$TCA["tx_t3consultancies_cat"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies_cat",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",	
		"delete" => "deleted",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_t3consultancies_cat.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "title, image, description",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_t3consultancies_command;;;;1-1-1,tx_t3consultancies_selected_only,tx_t3consultancies_categories,tx_t3consultancies_template,tx_t3consultancies_categorylisting,tx_t3consultancies_alphabeticallisting";


t3lib_extMgm::addPlugin(Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");

// Help icons
// tt_content - table name
// t3consultancies - extensions' directory name
// locallang_csh_tt_content.php - context sensitive help for tt_content
t3lib_extMgm::addLLrefForTCAdescr('tt_content','EXT:t3consultancies/locallang_csh_tt_content.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_t3consultancies','EXT:t3consultancies/locallang_csh_tx_t3consultancies.php');

?>
