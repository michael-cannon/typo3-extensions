<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_ttproductsredemption_activateredemptioncodes" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tt_products.tx_ttproductsredemption_activateredemptioncodes",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_ttproductsredemption_redemptioncodes" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:tt_products_redemption/locallang_db.php:tt_products.tx_ttproductsredemption_redemptioncodes",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_ttproductsredemption_codes",	
			"size" => 3,	
			"minitems" => 0,
			"maxitems" => 999,	
			"MM" => "tt_products_tx_ttproductsredemption_redemptioncodes_mm",
		)
	),
	"tx_ttproductsredemption_usergroups" => Array (		
		"exclude" => 1,		
		"label" =>
"LLL:EXT:tt_products_redemption/locallang_db.php:tt_products.tx_ttproductsredemption_usergroups",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "fe_groups",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 100,
		)
	),
	"tx_ttproductsredemption_ordering" => Array (		
		"exclude" => 1,		
		"label" =>
"LLL:EXT:tt_products_redemption/locallang_db.php:tt_products.tx_ttproductsredemption_ordering",		
		"config" => Array (
			"type" => "input",	
			"size" => "5",	
			"eval" => "int,nospace",
		)
	),
);


t3lib_div::loadTCA("tt_products");
t3lib_extMgm::addTCAcolumns("tt_products",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_products","tx_ttproductsredemption_activateredemptioncodes;;;;1-1-1, tx_ttproductsredemption_redemptioncodes, tx_ttproductsredemption_usergroups, tx_ttproductsredemption_ordering");

$tempColumns = Array (
	"tx_ttproductsredemption_redemptioncodeusage" => Array (		
		"exclude" => 1,		
		"label" =>
"LLL:EXT:tt_products_redemption/locallang_db.php:fe_users.tx_ttproductsredemption_redemptioncodeusage",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_ttproductsredemption_codes",	
			"size" => 3,	
			"minitems" => 0,
			"maxitems" => 100,	
			"MM" => "fe_users_tx_ttproductsredemption_redemptioncodeusage_mm",
		)
	),
);

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_ttproductsredemption_redemptioncodeusage;;;;1-1-1");

t3lib_extMgm::allowTableOnStandardPages("tx_ttproductsredemption_codes");

$TCA["tx_ttproductsredemption_codes"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:tt_products_redemption/locallang_db.php:tx_ttproductsredemption_codes",		
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
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_ttproductsredemption_codes.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, title, code, percentage, amount, maximumquantity, quantityused",
	)
);
?>
