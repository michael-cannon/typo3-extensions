<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_t3consultancies_selected_only" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_selected_only",
		"config" => Array (
			"type" => "check",
		)
	),

    "tx_t3consultancies_sponsor" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:t3consultancies/locallang_db.php:tt_content.tx_t3consultancies_sponsor",        
        "config" => Array (
            "type" => "select",    
            "foreign_table" => "tx_t3consultancies",    
            "foreign_table_where" => "AND tx_t3consultancies.hidden=0 ORDER BY tx_t3consultancies.title",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        ),
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
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, description, url, contact_email, contact_email2, contact_name, services, selected, weight, weight_bpm, weight_soa, fe_owner_user, logo, cntry",
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
		"fe_admin_fieldList" => "title",
	)
);

$TCA["tx_t3consultancies_contact_requests"] = Array (
       "ctrl" => Array (
               "title" => "LLL:EXT:t3consultancies/locallang_db.php:tx_t3consultancies_contact_requests",
               "label" => "uid",
               "tstamp" => "tstamp",
               "crdate" => "crdate",
               "cruser_id" => "cruser_id",
               "default_sortby" => "ORDER BY crdate",
               "delete" => "deleted",
               "enablecolumns" => Array (
                       "disabled" => "hidden",
               ),
               "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
               "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_t3consultancies_contact_requests.gif",
       ),
       "feInterface" => Array (
               "fe_admin_fieldList" => "hidden, first_name, last_name, company, phone, email, comment, companies",
       )
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_t3consultancies_selected_only;;;;1-1-1";


t3lib_extMgm::addPlugin(Array("LLL:EXT:t3consultancies/locallang_db.php:tt_content.list_type", $_EXTKEY."_pi1"),"list_type");
?>
