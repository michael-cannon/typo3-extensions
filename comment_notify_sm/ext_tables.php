<?php
#$Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:19 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
#Additions to fe_users table
$tempColumns = Array (
	"tx_commentnotify_global_notify_enabled" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:comment_notify/locallang_db.xml:fe_users.tx_commentnotify_global_notify_enabled",		
		"config" => Array (
			"type" => "check",
			"default" => 1,
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_commentnotify_global_notify_enabled;;;;1-1-1");

#Plugin 1
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:comment_notify/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Comment Notification");

#Plugin 2
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


t3lib_extMgm::addPlugin(Array('LLL:EXT:comment_notify/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","Comment Notification One-Click Handler");

#Table tx_commentnotify_users_posts
$TCA["tx_commentnotify_users_posts"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_users_posts',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate DESC",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_commentnotify_users_posts.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, fe_userid, postid, what, notifyenabled, lastnotified",
	)
);

#Table tx_commentnotify_notifications
$TCA["tx_commentnotify_notifications"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:comment_notify/locallang_db.xml:tx_commentnotify_notifications',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_commentnotify_notifications.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, users_posts_id, notificationtime",
	)
);
?>