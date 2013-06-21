<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_chcforum_category"] = Array (
	"ctrl" => $TCA["tx_chcforum_category"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,fe_group,cat_title,cat_description,auth_forumgroup_r"
	),
	"feInterface" => $TCA["tx_chcforum_category"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"fe_group" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.fe_group",
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					Array("LLL:EXT:lang/locallang_general.php:LGL.hide_at_login", -1),
					Array("LLL:EXT:lang/locallang_general.php:LGL.any_login", -2),
					Array("LLL:EXT:lang/locallang_general.php:LGL.usergroups", "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"cat_title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_category.cat_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "150",	
				"eval" => "required",
			)
		),
		"cat_description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_category.cat_description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "4",
			)
		),
		"auth_forumgroup_r" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_category.auth_forumgroup_r",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_forumgroup",	
				"size" => 4,	
				"minitems" => 0,
				"maxitems" => 25,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, cat_title, cat_description,auth_forumgroup_r")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "fe_group")
	)
);



$TCA["tx_chcforum_conference"] = Array (
	"ctrl" => $TCA["tx_chcforum_conference"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,cat_id,conference_name,conference_desc,conference_allow_user_edits,conference_public_r,conference_public_w,auth_forumgroup_r,auth_forumgroup_w,auth_feuser_mod,auth_forumgroup_attach"
	),
	"feInterface" => $TCA["tx_chcforum_conference"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"cat_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.cat_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_chcforum_category",	
				"foreign_table_where" => "AND tx_chcforum_category.pid=###CURRENT_PID### ORDER BY tx_chcforum_category.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"conference_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.conference_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "150",	
				"eval" => "required",
			)
		),
		"conference_desc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.conference_desc",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "4",
			)
		),
		"conference_allow_user_edits" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.conference_allow_user_edits",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"conference_public_r" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.conference_public_r",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"conference_public_w" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.conference_public_w",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"auth_forumgroup_r" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.auth_forumgroup_r",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_forumgroup",	
				"size" => 4,	
				"minitems" => 0,
				"maxitems" => 25,
			)
		),
		"auth_forumgroup_w" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.auth_forumgroup_w",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_forumgroup",	
				"size" => 4,	
				"minitems" => 0,
				"maxitems" => 25,
			)
		),
		"auth_feuser_mod" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.auth_feuser_mod",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
		"auth_forumgroup_attach" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.auth_forumgroup_attach",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_forumgroup",	
				"size" => 4,	
				"minitems" => 0,
				"maxitems" => 25,
			)
		),
		"hide_new" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_conference.hide_new",		
			"config" => Array (
				"type" => "check",
				"default" => 0,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, cat_id, conference_name, conference_desc, conference_allow_user_edits, conference_public_r, conference_public_w, auth_forumgroup_r, auth_forumgroup_w, auth_feuser_mod, auth_forumgroup_attach, hide_new")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_chcforum_forumgroup"] = Array (
	"ctrl" => $TCA["tx_chcforum_forumgroup"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,forumgroup_title,forumgroup_desc,forumgroup_users,forumgroup_groups"
	),
	"feInterface" => $TCA["tx_chcforum_forumgroup"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"forumgroup_title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_forumgroup.forumgroup_title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "150",	
				"eval" => "required",
			)
		),
		"forumgroup_desc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_forumgroup.forumgroup_desc",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"forumgroup_users" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_forumgroup.forumgroup_users",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"forumgroup_groups" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_forumgroup.forumgroup_groups",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_groups",	
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 50,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, forumgroup_title, forumgroup_desc, forumgroup_users, forumgroup_groups")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);




$TCA["tx_chcforum_post"] = Array (
	"ctrl" => $TCA["tx_chcforum_post"]["ctrl"],

	"interface" => Array (
		"showRecordFieldList" => "hidden,category_id,conference_id,thread_id,post_author,post_subject,post_author_ip,post_edit_tstamp,post_edit_count,post_attached,post_text, cache_parsed_text, cache_tstamp"
	),
	"feInterface" => $TCA["tx_chcforum_post"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"category_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.category_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_category",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"conference_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.conference_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_conference",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"thread_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.thread_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_thread",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"post_author" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_author",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"post_subject" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_subject",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "150",
			)
		),
		"post_author_ip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_author_ip",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "20",
			)
		),
		"post_edit_tstamp" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_edit_tstamp",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"post_edit_count" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_edit_count",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"max" => "3",	
				"eval" => "int,nospace",
			)
		),
		"post_attached" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_attached",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 250,	
				"uploadfolder" => "uploads/tx_chcforum",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"post_text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.post_text",		
			"config" => Array (
				"type" => "text",
				"cols" => "40",	
				"rows" => "10",
			)
		),
		"cache_parsed_text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.cache_parsed_text",		
			"config" => Array (
				"type" => "text",
				"cols" => "40",	
				"rows" => "10",
			)
		),		
		"cache_tstamp" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_post.cache_tstamp",		
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
		"0" => Array("showitem" => "hidden;;1;;1-1-1, category_id, conference_id, thread_id, post_author, post_subject, post_author_ip, post_edit_tstamp, post_edit_count, post_attached, post_text, cache_parsed_text, cache_tstamp")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_chcforum_thread"] = Array (
	"ctrl" => $TCA["tx_chcforum_thread"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,category_id,conference_id,thread_closed,thread_attribute,thread_subject,thread_author,thread_datetime,thread_views,thread_replies,thread_firstpostid,thread_lastpostid,endtime"
	),
	"feInterface" => $TCA["tx_chcforum_thread"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"category_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.category_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_chcforum_category",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"conference_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.conference_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_chcforum_conference",	
				"foreign_table_where" => "AND tx_chcforum_conference.pid=###CURRENT_PID### ORDER BY tx_chcforum_conference.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"thread_closed" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_closed",		
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"thread_attribute" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_attribute",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"max" => "10",	
				"eval" => "int,nospace",
			)
		),
		"thread_subject" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_subject",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "150",	
				"eval" => "required",
			)
		),
		"thread_author" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_author",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"thread_datetime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_datetime",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"thread_views" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_views",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"max" => "10",	
				"eval" => "int,nospace",
			)
		),
		"thread_replies" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_replies",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"max" => "10",	
				"eval" => "int,nospace",
			)
		),
		"thread_firstpostid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_firstpostid",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_chcforum_post",	
				"foreign_table_where" => "AND tx_chcforum_post.pid=###CURRENT_PID### ORDER BY tx_chcforum_post.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"thread_lastpostid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:chc_forum/locallang_db.php:tx_chcforum_thread.thread_lastpostid",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_chcforum_post",	
				"foreign_table_where" => "AND tx_chcforum_post.pid=###CURRENT_PID### ORDER BY tx_chcforum_post.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, category_id, conference_id, thread_closed, thread_attribute, thread_subject, thread_author, thread_datetime, thread_views, thread_replies, thread_firstpostid, thread_lastpostid, endtime")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);

?>