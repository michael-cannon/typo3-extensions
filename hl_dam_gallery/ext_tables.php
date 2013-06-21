<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
    "tx_hldamgallery_usepage" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:hl_dam_gallery/locallang_db.xml:tt_content.tx_hldamgallery_usepage",
        'config' => Array (
            'type' => 'check'
        )
    ),
    "tx_hldamgallery_hidenav" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:hl_dam_gallery/locallang_db.xml:tt_content.tx_hldamgallery_hidenav",
        'config' => Array (
            'type' => 'check'
        )
    ),
    "tx_hldamgallery_hidemeta" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:hl_dam_gallery/locallang_db.xml:tt_content.tx_hldamgallery_hidemeta",
        'config' => Array (
            'type' => 'check'
        )
    ),
    "tx_hldamgallery_squarethumbs" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:hl_dam_gallery/locallang_db.xml:tt_content.tx_hldamgallery_squarethumbs",
        'config' => Array (
            'type' => 'check'
        )
    ),
    "tx_hldamgallery_displaypage" => Array (
        "exclude" => 1,
        "label" => "LLL:EXT:hl_dam_gallery/locallang_db.xml:tt_content.tx_hldamgallery_displaypage",
        "config" => Array (
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'pages',
            'size' => '1',
            'maxitems' => '1',
            'minitems' => '0',
            'show_thumbs' => '1'
        )
    ),
);

t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);

// t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
// $TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='tx_hldamgallery_displaymode;;;;1-1-1, tx_hldamgallery_displaypage';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='imagewidth;;13,
                        --palette--;LLL:EXT:cms/locallang_ttc.php:ALT.imgOptions;11';

// Add plugin to list of possible plugins
t3lib_extMgm::addPlugin(Array('LLL:EXT:hl_dam_gallery/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

// Add config parameters to backend page for content elements "Text /w image" and "Image"
t3lib_extMgm::addToAllTCAtypes('tt_content','tx_hldamgallery_displaypage','image','after:tx_damttcontent_files');
//t3lib_extMgm::addToAllTCAtypes('tt_content','tx_hldamgallery_squarethumbs','image','after:tx_hldamgallery_displaypage');
// t3lib_extMgm::addToAllTCAtypes('tt_content','tx_hldamgallery_displaynav','image','after:tx_hldamgallery_displaypage');

t3lib_extMgm::addToAllTCAtypes('tt_content','tx_hldamgallery_displaypage','textpic','after:tx_damttcontent_files');
t3lib_extMgm::addToAllTCAtypes('tt_content','tx_hldamgallery_displaymode','textpic','after:tx_hldamgallery_displaypage');

$TCA['tt_content']['palettes']['7']['showitem'] = $TCA['tt_content']['palettes']['7']['showitem'] .
        ', tx_hldamgallery_usepage, tx_hldamgallery_hidenav, tx_hldamgallery_hidemeta';

$TCA['tt_content']['palettes']['13']['showitem'] = $TCA['tt_content']['palettes']['13']['showitem'] .
        ', tx_hldamgallery_squarethumbs';

// t3lib_extMgm::addToAllTCAtypes('tt_content','tx_hldamgallery_displaymode','textpic','after:tx_damttcontent_files');

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_hldamgallery_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_hldamgallery_pi1_wizicon.php';
?>