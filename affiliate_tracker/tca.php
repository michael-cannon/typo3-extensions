<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_affiliatetracker_codes"] = Array (
	"ctrl" => $TCA["tx_affiliatetracker_codes"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "affiliate_code"
	),
	"feInterface" => $TCA["tx_affiliatetracker_codes"]["feInterface"],
	"columns" => Array (
		"affiliate_code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_codes.affiliate_code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
				"eval" => "required, trim, int, unique",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "affiliate_code;;;;1-1-1")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_affiliatetracker_visitor_tracking"] = Array (
	"ctrl" => $TCA["tx_affiliatetracker_visitor_tracking"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "affiliate_id,landing_url,referer_url,feuser_id,full_affiliate_code,affiliate_source_code,affiliate_index_code"
	),
	"feInterface" => $TCA["tx_affiliatetracker_visitor_tracking"]["feInterface"],
	"columns" => Array (
		"affiliate_id" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.affiliate_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_t3consultancies",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"landing_url" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.landing_url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"referer_url" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.referer_url",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"feuser_id" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.feuser_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"full_affiliate_code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.full_affiliate_code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"affiliate_source_code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.affiliate_source_code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"affiliate_index_code" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:affiliate_tracker/locallang_db.php:tx_affiliatetracker_visitor_tracking.affiliate_index_code",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "affiliate_id;;;;1-1-1, landing_url, referer_url, feuser_id, full_affiliate_code, affiliate_source_code, affiliate_index_code")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
