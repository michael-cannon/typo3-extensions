<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_geoclassauction_auctionsites"] = Array (
	"ctrl" => $TCA["tx_geoclassauction_auctionsites"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,sitename,siteurl,codes,fe_user,description"
	),
	"feInterface" => $TCA["tx_geoclassauction_auctionsites"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
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
		"sitename" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_auctionsites.sitename",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"siteurl" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_auctionsites.siteurl",		
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
				"eval" => "required",
			)
		),
		"codes" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_auctionsites.codes",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "int,nospace,unique",
			)
		),
		"fe_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_auctionsites.fe_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_auctionsites.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "48",	
				"rows" => "5",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, sitename, siteurl, codes, fe_user, description")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_geoclassauction_leads"] = Array (
	"ctrl" => $TCA["tx_geoclassauction_leads"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,auction,fe_user,attendanceday,attendancetime,vehicle,pleasecall,note,howheard,eventcode,isdealer,contacted,internalnotes"
	),
	"feInterface" => $TCA["tx_geoclassauction_leads"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"auction" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.auction",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_geoclassauction_auctionsites",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"fe_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.fe_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"attendanceday" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday",		
			"config" => Array (
				"type" => "check",
				"cols" => 5,
				"items" => Array (
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.0", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.1", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.2", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.3", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.4", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.5", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendanceday.I.6", ""),
				),
			)
		),
		"attendancetime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime",		
			"config" => Array (
				"type" => "check",
				"cols" => 7,
				"items" => Array (
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.0", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.1", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.2", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.3", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.4", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.5", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.6", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.7", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.8", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.9", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.10", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.11", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.12", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.attendancetime.I.13", ""),
				),
			)
		),
		"vehicle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.vehicle",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"pleasecall" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.pleasecall",		
			"config" => Array (
				"type" => "check",
				"default" => 1,
			)
		),
		"note" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.note",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"howheard" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.howheard",		
			"config" => Array (
				"type" => "check",
				"cols" => 5,
				"items" => Array (
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.howheard.I.0", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.howheard.I.1", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.howheard.I.2", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.howheard.I.3", ""),
					Array("LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.howheard.I.4", ""),
				),
			)
		),
		"eventcode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.eventcode",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"isdealer" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.isdealer",		
			"config" => Array (
				"type" => "check",
			)
		),
		"contacted" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.contacted",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"internalnotes" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:geoclassauction/locallang_db.php:tx_geoclassauction_leads.internalnotes",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, auction, fe_user, attendanceday, attendancetime, vehicle, pleasecall, note, howheard, eventcode, isdealer, contacted, internalnotes")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>