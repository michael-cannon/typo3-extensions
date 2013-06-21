<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_srfeuserregistersurvey_results_archive"] = Array (
	"ctrl" => $TCA["tx_srfeuserregistersurvey_results_archive"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "survey_result_id,survey_user_id,survey_result"
	),
	"feInterface" => $TCA["tx_srfeuserregistersurvey_results_archive"]["feInterface"],
	"columns" => Array (
		"survey_result_id" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tx_srfeuserregistersurvey_results_archive.survey_result_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => " tx_mssurvey_results",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"survey_user_id" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tx_srfeuserregistersurvey_results_archive.survey_user_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"survey_result" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:sr_feuser_register_survey/locallang_db.php:tx_srfeuserregistersurvey_results_archive.survey_result",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
        "remoteaddress" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:ms_survey/locallang_db.php:tx_mssurvey_results.remoteaddress",        
            "config" => Array (
                "type" => "none",    
                "size" => "15",    
                "max" => "15",
            )
        ),
	),
	"types" => Array (
		"0" => Array("showitem" => "survey_result_id;;;;1-1-1, survey_user_id, survey_result, remoteaddress")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>