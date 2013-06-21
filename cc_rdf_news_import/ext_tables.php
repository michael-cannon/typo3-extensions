<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

## WOP:[tables][1][allow_on_pages]
t3lib_extMgm::allowTableOnStandardPages("tx_ccrdfnewsimport");


## WOP:[tables][1][allow_ce_insert_records]
t3lib_extMgm::addToInsertRecords("tx_ccrdfnewsimport");

$TCA["tx_ccrdfnewsimport"] = Array (
	"ctrl" => Array (
		"title" => "News feed import",
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY title",
		"delete" => "deleted",
		"crdate" => "crdate",
		"enablecolumns" => Array (
			"disabled" => "hidden",
			"starttime" => "starttime",
			"endtime" => "endtime",
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ccrdfnewsimport.gif",
	)
);


## WOP:[pi][1][addType]
#t3lib_div::loadTCA("tt_content");
#$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


## WOP:[pi][1][addType]
#t3lib_extMgm::addPlugin(Array("News feed import", $_EXTKEY."_pi1"),"list_type");


?>
