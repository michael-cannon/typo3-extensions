<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_shoppingsystem_related_product" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_related_product",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tt_news",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_shoppingsystem_product_store" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_store",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_shoppingsystem_product_brand" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_brand",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_shoppingsystem_product_merchant_url" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_merchant_url",		
		"config" => Array (
			"type" => "input",
			"size" => "15",
			"max" => "255",
			"checkbox" => "",
			"eval" => "trim",
			"wizards" => Array(
				"_PADDING" => 2,
				"link" => Array(
					"type" => "popup",
					"title" => "Link",
					"icon" => "link_popup.gif",
					"script" => "browse_links.php?mode=wizard",
					"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
				)
			)
		)
	),
	"tx_shoppingsystem_product_fetch_url" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_fetch_url",		
		"config" => Array (
			"type" => "input",
			"size" => "15",
			"max" => "255",
			"checkbox" => "",
			"eval" => "trim",
			"wizards" => Array(
				"_PADDING" => 2,
				"link" => Array(
					"type" => "popup",
					"title" => "Link",
					"icon" => "link_popup.gif",
					"script" => "browse_links.php?mode=wizard",
					"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
				)
			)
		)
	),
	"tx_shoppingsystem_product_image" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_image",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
			"max_size" => 500,	
			"uploadfolder" => "uploads/tx_shoppingsystem",
			"show_thumbs" => 1,	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_shoppingsystem_product_image_small" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_image_small",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
			"max_size" => 500,	
			"uploadfolder" => "uploads/tx_shoppingsystem",
			"show_thumbs" => 1,	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_shoppingsystem_product_price" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news.tx_shoppingsystem_product_price",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"eval" => "double",
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","--div--;Products,tx_shoppingsystem_related_product;;;;1-1-1, tx_shoppingsystem_product_store, tx_shoppingsystem_product_brand, tx_shoppingsystem_product_merchant_url, tx_shoppingsystem_product_fetch_url, tx_shoppingsystem_product_image, tx_shoppingsystem_product_image_small, tx_shoppingsystem_product_price");

$tempColumns = Array (
	"tx_shoppingsystem_featured_product" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:shopping_system/locallang_db.xml:tt_news_cat.tx_shoppingsystem_featured_product",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tt_news",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_news_cat");
t3lib_extMgm::addTCAcolumns("tt_news_cat",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news_cat","tx_shoppingsystem_featured_product;;;;1-1-1");

if (TYPO3_MODE=='BE')    {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('PRODUCTS_CATEGORIES', 'PRODUCTS_CATEGORIES');
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('PRODUCTS_LIST', 'PRODUCTS_LIST');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('PRODUCTS_SEARCH', 'PRODUCTS_SEARCH');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:shopping_system/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_pi4'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi4/static/","Related Product");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi5']='layout,select_key,pages';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi5']='pi_flexform';


t3lib_extMgm::addPlugin(Array('LLL:EXT:shopping_system/locallang_db.xml:tt_content.list_type_pi5', $_EXTKEY.'_pi5'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi5/static/","Shopping SelectBox");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi5', 'FILE:EXT:shopping_system/flexform_ds_pi5.xml');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi6']='layout,select_key,pages';
// >>> td@krendls
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi6']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi6', 'FILE:EXT:shopping_system/flexform_ds_pi6.xml');
// <<< td@krendls

t3lib_extMgm::addPlugin(Array('LLL:EXT:shopping_system/locallang_db.xml:tt_content.list_type_pi6', $_EXTKEY.'_pi6'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi6/static/","Shopping menu");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi6', 'FILE:EXT:shopping_system/flexform_ds_pi6.xml');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi7']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:shopping_system/locallang_db.xml:tt_content.list_type_pi7', $_EXTKEY.'_pi7'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi7/static/","Generate Text Image");
?>