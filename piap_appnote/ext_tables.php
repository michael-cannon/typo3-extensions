<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {
       include_once(t3lib_extMgm::extPath('piap_appnote').'class.tx_piapappnote_be_tree_select.php');
       include_once(t3lib_extMgm::extPath('piap_appnote').'class.tx_piapappnote_be_tree_select2.php');
       include_once(t3lib_extMgm::extPath('piap_appnote').'class.tx_piapappnote_be_prevent_circ_ref.php');
}

$TCA["tx_piapappnote_notes"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes",		
		"label" => "noteid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_piapappnote_notes.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, noteid, title, description, datetime, author, pdffile, zipfiles, categories, devices, versions, specialstart, specialend, specialpriority, related_appnotes",
	)
);

$TCA["tx_piapappnote_categories"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_categories",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_piapappnote_categories.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, description, childof",
	)
);

$TCA["tx_piapappnote_devices"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_devices",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_piapappnote_devices.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, description, childof, link",
	)
);

$TCA["tx_piapappnote_versions"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_versions",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_piapappnote_versions.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, description, childof",
	)
);

$TCA["tx_piapappnote_zips"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_zips",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_piapappnote_zips.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "file",
	)
);

$TCA["tx_piapappnote_pdfs"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_pdfs",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_piapappnote_pdfs.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "file",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='tx_piapappnote_categories;;;;1-1-1, tx_piapappnote_versions, tx_piapappnote_devices, tx_piapappnote_details_page, tx_piapappnote_published_in_the_last_x_days, tx_piapappnote_max_notes_to_show';


t3lib_extMgm::addPlugin(Array('LLL:EXT:piap_appnote/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Application Notes");

$tempColumns = Array (
	"tx_piapappnote_categories" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:piap_appnote/locallang_db.php:tt_content.tx_piapappnote_categories",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_piapappnote_categories",	
			"foreign_table_where" => "AND tx_piapappnote_categories.pid=###STORAGE_PID### ORDER BY tx_piapappnote_categories.title",	
			"itemsProcFunc" => "tx_piapappnote_be_tree_select2->main",
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 100,
		)
	),
	"tx_piapappnote_versions" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:piap_appnote/locallang_db.php:tt_content.tx_piapappnote_versions",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_piapappnote_versions",	
			"foreign_table_where" => "AND tx_piapappnote_versions.pid=###STORAGE_PID### ORDER BY tx_piapappnote_versions.title",	
			"itemsProcFunc" => "tx_piapappnote_be_tree_select2->main",
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 100,
		)
	),
	"tx_piapappnote_devices" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:piap_appnote/locallang_db.php:tt_content.tx_piapappnote_devices",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_piapappnote_devices",	
			"foreign_table_where" => "AND tx_piapappnote_devices.pid=###STORAGE_PID### ORDER BY tx_piapappnote_devices.title",	
			"itemsProcFunc" => "tx_piapappnote_be_tree_select2->main",
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 100,
		)
	),
	"tx_piapappnote_details_page" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:piap_appnote/locallang_db.php:tt_content.tx_piapappnote_details_page",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "pages",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
    "tx_piapappnote_published_in_the_last_x_days" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:piap_appnote/locallang_db.php:tt_content.tx_piapappnote_published_in_the_last_x_days",
        "config" => Array (
            "type" => "input",
            "size" => "4",
            "max" => "4",
            "eval" => "int",
            "checkbox" => "0",
            "range" => Array (
                "upper" => "1000",
                "lower" => "0"
            ),
            "default" => 0
        )
    ),
    "tx_piapappnote_max_notes_to_show" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:piap_appnote/locallang_db.php:tt_content.tx_piapappnote_max_notes_to_show",
        "config" => Array (
            "type" => "input",
            "size" => "4",
            "max" => "4",
            "eval" => "int",
            "checkbox" => "0",
            "range" => Array (
                "upper" => "1000",
                "lower" => "0"
            ),
            "default" => 0
        )
    ),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
?>
