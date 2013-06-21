<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_sponsorcontentscheduler_featured_weeks"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_featured_weeks",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_sponsorcontentscheduler_featured_weeks.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "starttime, endtime, description",
	)
);

$TCA["tx_sponsorcontentscheduler_featured_weeks_mm"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_featured_weeks_mm",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_sponsorcontentscheduler_featured_weeks_mm.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "uid_local, uid_foreign",
	)
);

$TCA["tx_sponsorcontentscheduler_package"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_sponsorcontentscheduler_package",		
		"label" => "title",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",	
			"starttime" => "starttime",	
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_sponsorcontentscheduler_package.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, title, company_profile, bulletin, roundtable, whitepaper, rights, fe_uid, sponsor_id",
	)
);

$tempColumns = Array (
	"tx_sponsorcontentscheduler_sponsor_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_news.tx_sponsorcontentscheduler_sponsor_id",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_t3consultancies",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_sponsorcontentscheduler_max_featured_weeks" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_news.tx_sponsorcontentscheduler_max_featured_weeks",		
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
	"tx_sponsorcontentscheduler_author_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_news.tx_sponsorcontentscheduler_author_id",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "fe_groups",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_sponsorcontentscheduler_news_due_date" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_news.tx_sponsorcontentscheduler_news_due_date",		
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
	"tx_sponsorcontentscheduler_package_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_news.tx_sponsorcontentscheduler_package_id",		
		"config" => Array (
			"type" => "group",	
            "internal_type" => "db",
			"allowed" => "tx_sponsorcontentscheduler_package",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_sponsorcontentscheduler_unsold_leads" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_news.tx_sponsorcontentscheduler_unused_leads",		
		"config" => Array (
			"type" => "input",
			"size" => "4",
			"max" => "4",
			"eval" => "int",
			"checkbox" => "0",
			"range" => Array (
				"upper" => "1000",
				"lower" => "0"
			),
			"default" => 0
		)
	),
	
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_sponsorcontentscheduler_sponsor_id;;;;1-1-1, tx_sponsorcontentscheduler_max_featured_weeks, tx_sponsorcontentscheduler_author_id,tx_sponsorcontentscheduler_news_due_date,tx_sponsorcontentscheduler_package_id,tx_sponsorcontentscheduler_unused_leads");

$tempColumns = Array (
	"tx_sponsorcontentscheduler_package_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:fe_users.tx_sponsorcontentscheduler_package_id",		
		"config" => Array (
			"type" => "select",	
			"foreign_table" => "tx_sponsorcontentscheduler_package",	
			"foreign_table_where" => "ORDER BY tx_sponsorcontentscheduler_package.uid",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_sponsorcontentscheduler_sponsor_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:fe_users.tx_sponsorcontentscheduler_sponsor_id",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_t3consultancies",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_sponsorcontentscheduler_package_id;;;;1-1-1,tx_sponsorcontentscheduler_sponsor_id");

$tempColumns = Array (
	"tx_sponsorcontentscheduler_job_bank" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_t3consultancies.tx_sponsorcontentscheduler_job_bank",		
			"config" => Array (
				"type" => "check",
			)
		),
		"tx_sponsorcontentscheduler_sponsor_page" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_t3consultancies.tx_sponsorcontentscheduler_sponsor_page",		
			"config" => Array (
				"type" => "check",
			)
		),
		"tx_sponsorcontentscheduler_owner_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:sponsor_content_scheduler/locallang_db.php:tx_t3consultancies.tx_sponsorcontentscheduler_owner_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
);



t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPlugin(Array('LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:sponsor_content_scheduler/flexform_ds_pi1.xml');

t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Sponsor Content Scheduler");

t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi2"]="layout,select_key,recursive,pages"; 
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi2"]="tx_t3consultancies_sponsor"; 

t3lib_extMgm::addPlugin(Array("LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_content.list_type_pi2", $_EXTKEY."_pi2"),"list_type"); 
	 
t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_picron"]="layout,select_key,recursive,pages"; 
	 
t3lib_extMgm::addPlugin(Array("LLL:EXT:sponsor_content_scheduler/locallang_db.php:tt_content.list_type_picron", $_EXTKEY."_picron"),"list_type"); 

?>
