<?php
//$Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:50 peimic.comprock Exp $
if (!defined ("TYPO3_MODE"))     die ("Access denied.");
if (TYPO3_MODE=="BE")    {
    t3lib_extMgm::insertModuleFunction(
        "web_func",        
        "tx_memberexpiry_modfunc1",
        t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_memberexpiry_modfunc1.php",
        "LLL:EXT:member_expiry/locallang_db.php:moduleFunction.tx_memberexpiry_modfunc1",
        "wiz"
    );
}
$tempColumns = Array (
    "tx_memberexpiry_expiretime" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:member_expiry/locallang_db.php:fe_users.tx_memberexpiry_expiretime",        
        "config" => Array (
            "type" => "input",
            "size" => "8",
            "max" => "20",
            "eval" => "date",
            "checkbox" => "0",
            "default" => "0"
        )
    ),
    "tx_memberexpiry_expired" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:member_expiry/locallang_db.php:fe_users.tx_memberexpiry_expired",        
        "config" => Array (
            "type" => "check",
        )
    ),
    "tx_memberexpiry_emailsenttime" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:member_expiry/locallang_db.php:fe_users.tx_memberexpiry_emailsenttime",        
        "config" => Array (
            "type" => "input",
            "size" => "8",
            "max" => "20",
            "eval" => "date",
            "checkbox" => "0",
            "default" => "0"
        )
    ),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_memberexpiry_expiretime;;;;1-1-1,tx_memberexpiry_expired,tx_memberexpiry_emailsenttime");


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:member_expiry/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Membership expiry processor");
?>