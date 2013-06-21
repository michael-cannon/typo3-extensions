<?php
//$Id: tca.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_securityquestion_questions"] = Array (
	"ctrl" => $TCA["tx_securityquestion_questions"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,question"
	),
	"feInterface" => $TCA["tx_securityquestion_questions"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"question" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:security_question/locallang_db.php:tx_securityquestion_questions.question",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "255",	
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, question")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>