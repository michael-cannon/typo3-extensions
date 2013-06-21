<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_newseventregister_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_newseventregister_modfunc1.php",
		"LLL:EXT:news_event_register/locallang_db.php:moduleFunction.tx_newseventregister_modfunc1",
		"wiz"	
	);
}

$tempColumns = Array (
	"tx_newseventregister_webexlink" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_webexlink",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"wizards" => Array(
				"_PADDING" => 2,
				"link" => Array(
					"type" => "popup",
					"title" => "Link",
					"icon" => "link_popup.gif",
					"script" => "browse_links.php?mode=wizard",
					"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
				),
			),
		)
	),
	"tx_newseventregister_eventlink" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_eventlink",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"wizards" => Array(
				"_PADDING" => 2,
				"link" => Array(
					"type" => "popup",
					"title" => "Link",
					"icon" => "link_popup.gif",
					"script" => "browse_links.php?mode=wizard",
					"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
				),
			),
		)
	),
	"tx_newseventregister_eventinformation" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_eventinformation",		
		"config" => Array (
			"type" => "text",
			"cols" => "40",	
			"rows" => "5",
		)
	),
	"tx_newseventregister_sendregistrationthankyou" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendregistrationthankyou",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_newseventregister_usetxnewssponsormessage" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_usetxnewssponsormessage",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_newseventregister_useeventinformationregistered" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_useeventinformationregistered",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_newseventregister_sendregistrationthankyou" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendregistrationthankyou",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_newseventregister_sendfirstreminder" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendfirstreminder",		
		"config" => Array (
			"type" => "check",
			"default" => 0,
		)
	),
	"tx_newseventregister_sendsecondreminder" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendsecondreminder",		
		"config" => Array (
			"type" => "check",
			"default" => 0,
		)
	),
	"tx_newseventregister_sendthirdreminder" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendthirdreminder",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_newseventregister_eventaccessinformation" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_eventaccessinformation",		
		"config" => Array (
			"type" => "text",
			"cols" => "40",	
			"rows" => "5",
		)
	),
	"tx_newseventregister_sendaccessinformation" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendaccessinformation",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
	"tx_newseventregister_followupmessage" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_followupmessage",		
		"config" => Array (
			"type" => "text",
			"cols" => "40",	
			"rows" => "5",
		)
	),
	"tx_newseventregister_followuplink" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_followuplink",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"wizards" => Array(
				"_PADDING" => 2,
				"link" => Array(
					"type" => "popup",
					"title" => "Link",
					"icon" => "link_popup.gif",
					"script" => "browse_links.php?mode=wizard",
					"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
				),
			),
		)
	),
	"tx_newseventregister_sendfollowup" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_sendfollowup",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_newseventregister_eventon" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_eventon",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_newseventregister_surveyon" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_surveyon",		
		"config" => Array (
			"type" => "check",
		)
	),
	"tx_newseventregister_surveyrequired" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_surveyrequired",		
		"config" => Array (
			"type" => "check",
			"default" => 0,
		)
	),
	"tx_newseventregister_surveyquestions" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_surveyquestions",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_mssurvey_items",	
			"size" => 10,	
			"minitems" => 0,
			"maxitems" => 99,	
			"MM" => "tt_news_tx_mssurvey_items_mm",
			'wizards' => Array(
				'_PADDING' => 2,
				'_VERTICAL' => 1,
				'edit' => Array(
					'type' => 'popup',
					'title' => 'LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_surveyquestionsedit',		
					'script' => 'wizard_edit.php',
					'popup_onlyOpenIfSelected' => 1,
					'icon' => 'edit2.gif',
					'JSopenParams' => 'height=480,width=600,status=0,menubar=0,scrollbars=1',
				),
				'add' => Array(
					'type' => 'script',
					'title' => 'LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_surveyquestionscreate',		
					'icon' => 'add.gif',
					'params' => Array(
						'table'=>'tx_mssurvey_items',
						'pid' => '###CURRENT_PID###',
						'setValue' => 'prepend'
					),
					'script' => 'wizard_add.php',
				),
			),
		)
	),
	"tx_newseventregister_startdateandtime" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_startdateandtime",		
		"config" => Array (
			"type" => "input",
			"size" => "12",
			"max" => "20",
			"eval" => "datetime",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_newseventregister_enddateandtime" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_enddateandtime",		
		"config" => Array (
			"type" => "input",
			"size" => "12",
			"max" => "20",
			"eval" => "datetime",
			"checkbox" => "0",
			"default" => "0"
		)
	),
	"tx_newseventregister_pointofcontact" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_pointofcontact",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "fe_users",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_newseventregister_eventinformationregistered" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_eventinformationregistered",		
		"config" => Array (
			"type" => "text",
			"cols" => "40",	
			"rows" => "5",
		)
	),
	"tx_newseventregister_canned" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:news_event_register/locallang_db.php:tt_news.tx_newseventregister_canned",		
		"config" => Array (
			"type" => "check",
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news" ,"
	,--div--;Round Table
	, tx_newseventregister_eventon;;;;1-1-1
	, tx_newseventregister_canned;;;;1-1-1
	, tx_newseventregister_startdateandtime
	, tx_newseventregister_enddateandtime
	, tx_newseventregister_sendregistrationthankyou;;;;2-2-2
	, tx_newseventregister_eventinformation
	, tx_newseventregister_usetxnewssponsormessage
	, tx_newseventregister_useeventinformationregistered
	, tx_newseventregister_sendfirstreminder;;;;3-3-3
	, tx_newseventregister_sendsecondreminder
	, tx_newseventregister_sendthirdreminder
	, tx_newseventregister_sendaccessinformation;;;;4-4-4
	, tx_newseventregister_eventaccessinformation
	, tx_newseventregister_eventlink
	, tx_newseventregister_webexlink
	, tx_newseventregister_sendfollowup;;;;5-5-5
	, tx_newseventregister_followupmessage
	, tx_newseventregister_followuplink
	, tx_newseventregister_pointofcontact;;;;6-6-6
	,--div--;Survey
	, tx_newseventregister_surveyon;;;;7-7-7
	, tx_newseventregister_surveyrequired
	, tx_newseventregister_surveyquestions
");

// add tx_newseventregister_eventinformationregistered to sponsor tab
if ( preg_match( '#(tx_newssponsor_message)#'
		, $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]
		)
	)
{
	// then reinsert on General tab above Subheader
	$TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]	=
								preg_replace( '#(tx_newssponsor_message)#'
									, '\1,tx_newseventregister_eventinformationregistered'
									, $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]
								);
}

// PCR group short and bodytext
$TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]	=
								preg_replace( '#(short)#'
									, '\1;;;;1-1-1'
									, $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ]
								);

// cbPrint(  $TCA[ 'tt_news' ][ 'types' ][ 0 ][ 'showitem' ] );

$TCA["tx_newseventregister_participants"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:news_event_register/locallang_db.php:tx_newseventregister_participants",		
		"label" => "news_id",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate DESC",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_newseventregister_participants.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, registrationdate, news_id, fe_user_id,
		thankyousent, firstremindersent, secondremindersent, thirdremindersent, accessinformationsent, followupsent, unregistered",
	)
);

if ( TYPO3_MODE == 'BE' )
{
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('EVENT_REGISTER', 'EVENT_REGISTER');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('SEND_REMINDERS', 'SEND_REMINDERS');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('SEND_ACCESS', 'SEND_ACCESS');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('SEND_FOLLOW_UP', 'SEND_FOLLOW_UP');
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('SEND_MONITOR', 'SEND_MONITOR');
}
?>
