<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_commentnotify_users_posts"] = Array (
	"ctrl" => $TCA["tx_commentnotify_users_posts"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fe_userid,postid,what,notifyenabled,lastnotified"
	),
	"feInterface" => $TCA["tx_commentnotify_users_posts"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fe_userid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.fe_userid",		
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
		"postid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.postid",		
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
		"what" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.what",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.what.I.0", "1"),
					Array("LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.what.I.1", "2"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"notifyenabled" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.notifyenabled",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"lastnotified" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts.lastnotified",		
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
		"0" => Array("showitem" => "hidden;;1;;1-1-1, fe_userid, postid, what, notifyenabled, lastnotified")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_commentnotify_notifications"] = Array (
	"ctrl" => $TCA["tx_commentnotify_notifications"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,users_posts_id,notificationtime"
	),
	"feInterface" => $TCA["tx_commentnotify_notifications"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"users_posts_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_notifications.users_posts_id",		
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
		"notificationtime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_notifications.notificationtime",		
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
		"0" => Array("showitem" => "hidden;;1;;1-1-1, users_posts_id, notificationtime")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>