<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
t3lib_extMgm::allowTableOnStandardPages("tx_jobbankresumemgr_info");

$TCA["tx_jobbankresumemgr_info"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_jobbankresumemgr_info",		
		"label" => "resume_file_name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_jobbankresumemgr_info.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, user_id, job_id, resume_file, resume_file_name, job_bank_comments",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";


t3lib_extMgm::addPlugin(Array("LLL:EXT:job_bank_resume_mgr/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Job Bank Resume");

$tempColumns = Array (
    "tx_jobbankresumemgr_resumecontactname" => Array (        
        "exclude" => 1,        
        "label" =>
"LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_t3consultancies.tx_jobbankresumemgr_resumecontactname",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
    "tx_jobbankresumemgr_resumecontactemail" => Array (        
        "exclude" => 1,        
        "label" =>
"LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_t3consultancies.tx_jobbankresumemgr_resumecontactemail",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
);


t3lib_div::loadTCA("tx_t3consultancies");
t3lib_extMgm::addTCAcolumns("tx_t3consultancies",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_t3consultancies","tx_jobbankresumemgr_resumecontactname;;;;1-1-1, tx_jobbankresumemgr_resumecontactemail");
?>