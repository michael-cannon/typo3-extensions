<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_newseventregister_participants"] = Array (
	"ctrl" => $TCA["tx_newseventregister_participants"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,registrationdate,news_id,fe_user_id,thankyousent,firstremindersent,secondremindersent,thirdremindersent,accessinformationsent,followupsent,unregistered"
	),
	"feInterface" => $TCA["tx_newseventregister_participants"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"registrationdate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.registrationdate",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"news_id" => Array (		
			"exclude" => 1,		
			"label" =>
			"LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.news_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tt_news",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"fe_user_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.fe_user_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"thankyousent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.thankyousent",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"firstremindersent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.firstremindersent",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"secondremindersent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.secondremindersent",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"thirdremindersent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.thirdremindersent",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"accessinformationsent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.accessinformationsent",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"followupsent" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.followupsent",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"unregistered" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants.unregistered",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "registrationdate;;1;;1-1-1, news_id, fe_user_id, thankyousent, firstremindersent, secondremindersent, thirdremindersent, accessinformationsent, followupsent, unregistered, hidden")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
