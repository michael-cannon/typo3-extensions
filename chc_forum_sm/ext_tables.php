<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("web","txchcforumM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

$tempColumns = Array (
    "tx_chcforum_aim" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:chc_forum/locallang_db.php:fe_users.tx_chcforum_aim",        
        "config" => Array (
            "type" => "input",
        )
    ),
    "tx_chcforum_yahoo" => Array (        
        "exclude" => 1,        
        "label" => "LLL:EXT:chc_forum/locallang_db.php:fe_users.tx_chcforum_yahoo",        
        "config" => Array (
            "type" => "input",
        )
    ),
    "tx_chcforum_msn" => Array (        
        "exclude" => 1,
        "label" => "LLL:EXT:chc_forum/locallang_db.php:fe_users.tx_chcforum_msn",        
        "config" => Array (
            "type" => "input",
        )
    ),
    "tx_chcforum_customim" => Array (        
        "exclude" => 1,
        "label" => "LLL:EXT:chc_forum/locallang_db.php:fe_users.tx_chcforum_customim",        
        "config" => Array (
            "type" => "input",
        )
    ),
);

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_chcforum_aim, tx_chcforum_yahoo, tx_chcforum_msn, tx_chcforum_customim");



t3lib_extMgm::allowTableOnStandardPages("tx_chcforum_category");

$TCA["tx_chcforum_category"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_category",		
		"label" => "cat_title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"fe_group" => "fe_group",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icons/icon_tx_chcforum_cat.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, fe_group, cat_title, cat_description",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_chcforum_conference");

$TCA["tx_chcforum_conference"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference",		
		"label" => "conference_name",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icons/icon_tx_chcforum_cnf.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, cat_id, conference_name, conference_desc, conference_public_r, conference_public_w, auth_forumgroup_rw, auth_feuser_mod, auth_forumgroup_attach, hide_new",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_chcforum_forumgroup");

$TCA["tx_chcforum_forumgroup"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_forumgroup",		
		"label" => "forumgroup_title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY forumgroup_title",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icons/icon_tx_chcforum_fgrp.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, forumgroup_title, forumgroup_desc, forumgroup_users, forumgroup_groups",
	)
);



t3lib_extMgm::allowTableOnStandardPages("tx_chcforum_post");

$TCA["tx_chcforum_post"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post",		
		"label" => "post_subject",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icons/icon_tx_chcforum_post.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, category_id, conference_id, thread_id, post_author, post_subject, post_author_ip, post_edit_tstamp, post_edit_count, post_attached, post_text",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_chcforum_thread");

$TCA["tx_chcforum_thread"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread",		
		"label" => "thread_subject",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY thread_datetime",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icons/icon_tx_chcforum_thrd.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, category_id, conference_id, thread_subject, thread_author, thread_datetime, thread_views, thread_replies, thread_firstpostid, thread_lastpostid",
	)
);

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key";
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';


t3lib_extMgm::addPlugin(Array("LLL:EXT:chc_forum/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:chc_forum/flexform_ds.xml');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","CHC Forum");						


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_chcforum_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY)."pi1/class.tx_chcforum_pi1_wizicon.php";
?>