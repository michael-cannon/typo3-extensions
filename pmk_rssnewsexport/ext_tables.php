<?php
if (!defined ("TYPO3_MODE"))     die ("Access denied.");
$tempColumns = Array (
    "tx_pmkrssnewsexport_is_protected" => Array (        
        "exclude" => 0,        
        "label" => "LLL:EXT:pmk_rssnewsexport/locallang_db.php:tt_content.tx_pmkrssnewsexport_is_protected",        
        "config" => Array (
            "type" => "check",
        )
    ),
    "tx_pmkrssnewsexport_rss_version" => Array (        
        "exclude" => 0,        
        "label" => "LLL:EXT:pmk_rssnewsexport/locallang_db.php:tt_content.tx_pmkrssnewsexport_rss_version",        
        "config" => Array (
            "type" => "radio",
            "items" => Array (
				Array("LLL:EXT:pmk_rssnewsexport/locallang_db.php:tt_content.tx_pmkrssnewsexport_rss_version.I.0","0"),
				Array("LLL:EXT:pmk_rssnewsexport/locallang_db.php:tt_content.tx_pmkrssnewsexport_rss_version.I.1","1"),
            ),
        )
    ),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="tx_pmkrssnewsexport_is_protected;;;;1-1-1,tx_pmkrssnewsexport_rss_version";

t3lib_extMgm::addPlugin(Array("LLL:EXT:pmk_rssnewsexport/locallang_db.php:tt_content.list_type",$_EXTKEY."_pi1"),"list_type");

if (TYPO3_MODE=="BE") $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_pmkrssnewsexport_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_pmkrssnewsexport_pi1_wizicon.php";
?>
