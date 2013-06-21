<?php
//$Id: tca.php,v 1.1.1.1 2010/04/15 10:03:07 peimic.comprock Exp $
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_canspamlog_main"] = Array (
	"ctrl" => $TCA["tx_canspamlog_main"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fe_userid,pageid,action_time,url,action,subaction,client_ip,tx_bpmprofile_newsletter1,tx_bpmprofile_newsletter2,tx_bpmprofile_newsletter3,tx_bpmprofile_newsletter4,tx_bpmprofile_newsletter5,tx_bpmprofile_newsletter6,tx_bpmprofile_newsletter7,tx_bpmprofile_newsletter8,tx_bpmprofile_newsletter9,tx_bpmprofile_newsletter9,currentArr"
	),
	"feInterface" => $TCA["tx_canspamlog_main"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fe_userid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.fe_userid",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"pageid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.pageid",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"action_time" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.action_time",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"site" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.site",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
		"url" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
		"action" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.action",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
		"subaction" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.subaction",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",
				"eval" => "trim",
			)
		),
		"client_ip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.client_ip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "15",	
				"eval" => "trim",
			)
		),
        "tx_bpmprofile_newsletter1" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter1",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter2" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter2",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter3" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter3",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter4" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter4",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter5" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter5",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter6" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter6",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter7" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter7",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter8" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter8",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter9" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter9",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "tx_bpmprofile_newsletter10" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.tx_bpmprofile_newsletter10",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "currentArr" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:canspam_log/locallang_db.php:tx_canspamlog_main.currentArr",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),

	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, fe_userid, pageid, action_time, url, action, subaction, client_ip,tx_bpmprofile_newsletter1,tx_bpmprofile_newsletter2,tx_bpmprofile_newsletter3,tx_bpmprofile_newsletter4,tx_bpmprofile_newsletter5,tx_bpmprofile_newsletter6,tx_bpmprofile_newsletter7,tx_bpmprofile_newsletter8,tx_bpmprofile_newsletter9,tx_bpmprofile_newsletter10,currentArr")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>