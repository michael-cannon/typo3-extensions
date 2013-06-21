<?php
//$Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
    "tx_securityquestion_question" => Array (        
        "exclude" => 0,        
        "label" => "LLL:EXT:security_question/locallang_db.php:fe_users.tx_securityquestion_question",        
        "config" => Array (
            "type" => "group",    
            "internal_type" => "db",    
            "allowed" => "tx_securityquestion_questions",    
            "size" => 1,    
            "minitems" => 0,
            "maxitems" => 1,
        )
    ),
    "tx_securityquestion_answer" => Array (        
        "exclude" => 0,        
        "label" => "LLL:EXT:security_question/locallang_db.php:fe_users.tx_securityquestion_answer",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",    
            "max" => "255",    
            "eval" => "trim",
        )
    ),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_securityquestion_question;;;;1-1-1,tx_securityquestion_answer");

$TCA["tx_securityquestion_questions"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:security_question/locallang_db.php:tx_securityquestion_questions",		
		"label" => "question",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_securityquestion_questions.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, question",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:security_question/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Security Question Verification");
?>