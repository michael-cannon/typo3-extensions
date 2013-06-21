<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:bsg_regsteps/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","BSG Registration Plugin");

$tempColumns = Array (
    "department" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_department",        
        "config" => Array (
            "type" => "input",
            "size" => "30",    
        )
    ),
    "bmail" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_bmail",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
    "adminmail" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_adminmail",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
    "courses" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_courses",        
        "config" => Array (
            "type" => "text",    
            "cols" => "48",
            "rows" => "5",
        )
    ),
    "courses_list" => Array (        
        "exclude" => 1,        
        "label" =>
		"LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_courses_list",
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),

    "priority_code" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_priority_code",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
    "conf_series" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:bsg_regsteps/locallang_db.xml:tx_bsgregsteps_conf_series",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","department;;;;1-1-1, bmail, adminmail, courses, courses_list, priority_code, conf_series");
?>