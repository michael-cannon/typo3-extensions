<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_jobbankresumemgr_info"] = Array (
	"ctrl" => $TCA["tx_jobbankresumemgr_info"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,user_id,job_id,resume_file,resume_file_name"
	),
	"feInterface" => $TCA["tx_jobbankresumemgr_info"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"user_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_jobbankresumemgr_info.user_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table"=>"fe_users",
				"foreign_table_where" => " ORDER BY fe_users.uid",
				
			)
		),
		"job_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_jobbankresumemgr_info.job_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table"=>"tx_jobbank_list",
				"foreign_table_where" => " ORDER BY tx_jobbank_list.uid",
				
			)
		

		),
		"resume_file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_jobbankresumemgr_info.resume_file",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_jobbankresumemgr",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"resume_file_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_jobbankresumemgr_info.resume_file_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"job_bank_comments" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:job_bank_resume_mgr/locallang_db.php:tx_jobbankresumemgr_info.job_bank_comments",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, user_id, job_id, resume_file, resume_file_name, job_bank_comments")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>