<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$tempColumns = Array (
	"tx_extendedshop_vatcode" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:fe_users.tx_extendedshop_vatcode",		
		"config" => Array (
			"type" => "input",	
			"size" => "20",	
			"eval" => "trim",
		)
	),
	"tx_extendedshop_mobile" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:fe_users.tx_extendedshop_mobile",		
		"config" => Array (
			"type" => "input",	
			"size" => "20",	
			"eval" => "trim",
		)
	),
	"tx_extendedshop_state" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:fe_users.tx_extendedshop_state",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"max" => "250",	
			"eval" => "trim",
		)
	),
	"tx_extendedshop_private" => Array (
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:fe_users.tx_extendedshop_private",		
		"config" => Array (
			"type" => "radio",
			"items" => Array (
				Array("LLL:EXT:extendedshop/locallang_db.php:fe_users.tx_extendedshop_private.I.0", "0"),
				Array("LLL:EXT:extendedshop/locallang_db.php:fe_users.tx_extendedshop_private.I.1", "1"),
			),
		)
	),
	
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_extendedshop_vatcode, tx_extendedshop_state, tx_extendedshop_private, tx_extendedshop_mobile");

$tempColumns = Array (
	"tx_extendedshop_vatcode" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:tt_address.tx_extendedshop_vatcode",		
		"config" => Array (
			"type" => "input",	
			"size" => "20",	
			"eval" => "trim",
		)
	),
	"tx_extendedshop_state" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:tt_address.tx_extendedshop_state",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"max" => "250",	
			"eval" => "trim",
		)
	),
	"tx_extendedshop_private" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:extendedshop/locallang_db.php:tt_address.tx_extendedshop_private",		
		"config" => Array (
			"type" => "radio",
			"items" => Array (
				Array("LLL:EXT:extendedshop/locallang_db.php:tt_address.tx_extendedshop_private.I.0", "0"),
				Array("LLL:EXT:extendedshop/locallang_db.php:tt_address.tx_extendedshop_private.I.1", "1"),
			),
		)
	),
);


t3lib_div::loadTCA("tt_address");
t3lib_extMgm::addTCAcolumns("tt_address",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_address","tx_extendedshop_vatcode, tx_extendedshop_state, tx_extendedshop_private");

//t3lib_extMgm::allowTableOnStandardPages("tx_extendedshop_status");
$TCA["tx_extendedshop_status"] = Array (
    "ctrl" => Array (
        "title" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_status",        
        "label" => "status",    
        "tstamp" => "tstamp",
        "crdate" => "crdate",
        "cruser_id" => "cruser_id",
        "languageField" => "sys_language_uid",    
        "transOrigPointerField" => "l18n_parent",    
        "transOrigDiffSourceField" => "l18n_diffsource",    
        "default_sortby" => "ORDER BY priority",    
        "delete" => "deleted",    
        "enablecolumns" => Array (        
            "disabled" => "hidden",
        ),
        "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
        "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_extendedshop_status.gif",
    ),
    "feInterface" => Array (
        "fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, status, priority",
    )
);

$TCA["tx_extendedshop_category"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_category",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_extendedshop_category.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, code, title, summary, description, image",
	)
);


$TCA["tx_extendedshop_orders"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders",		
		"label" => "code",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_extendedshop_orders.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, code, customer, shippingcustomer, date, shipping, payment, total, weight, volume, trackingcode, state, ip, note, status, deliverydate",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_extendedshop_rows");

$TCA["tx_extendedshop_rows"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows",		
		"label" => "ordercode",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_extendedshop_rows.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, ordercode, productcode, quantity, price, weight, volume, state, accessoriescodes, options",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_extendedshop_products");


t3lib_extMgm::addToInsertRecords("tx_extendedshop_products");

$TCA["tx_extendedshop_products"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products",		
		"label" => "code",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_extendedshop_products.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, code, title, pagetitle, summary, description, image, price, pricenotax, instock, category, www, ordered, weight, volume, correlatedaccessories, offertprice, offertpricenotax, discount, sizes, colors, correlatedproducts",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout";
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cms']['db_layout']['addTables']= Array (
	'tx_extendedshop_products' => Array (
	'0' => Array (
		'fList' => 'code,title,price',
		'icon'=>'0',
		),
	),
);

t3lib_extMgm::addPlugin(Array("LLL:EXT:extendedshop/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


//t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Webformat Shop System");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds_pi1.xml');

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_extendedshop_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_extendedshop_pi1_wizicon.php";

if (TYPO3_MODE=="BE")    {
    t3lib_extMgm::addModule("user","txextendedshopM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

?>
