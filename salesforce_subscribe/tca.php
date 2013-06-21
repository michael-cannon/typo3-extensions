<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_salesforcesubscribe_subscribes"] = Array (
	"ctrl" => $TCA["tx_salesforcesubscribe_subscribes"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fe_user,sponsor"
	),
	"feInterface" => $TCA["tx_salesforcesubscribe_subscribes"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fe_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:salesforce_subscribe/locallang_db.php:tx_salesforcesubscribe_subscribes.fe_user",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "ORDER BY fe_users.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"sponsor" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:salesforce_subscribe/locallang_db.php:tx_salesforcesubscribe_subscribes.sponsor",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_t3consultancies",	
				"foreign_table_where" => "ORDER BY tx_t3consultancies.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, fe_user, sponsor")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>