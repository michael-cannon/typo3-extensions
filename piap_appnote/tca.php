<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_piapappnote_notes"] = Array (
	"ctrl" => $TCA["tx_piapappnote_notes"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,noteid,title,description,datetime,author,pdffile,zipfiles,categories,devices,versions,specialstart,specialend,specialpriority,related_appnotes"
	),
	"feInterface" => $TCA["tx_piapappnote_notes"]["feInterface"],
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
		"noteid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.noteid",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,nospace,unique",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"datetime" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.datetime",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"author" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.author",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"pdffile" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.pdffile",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "pdf",
				"uploadfolder" => "uploads/tx_piapappnote",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"zipfiles" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.zipfiles",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "zip",
				"uploadfolder" => "uploads/tx_piapappnote",
				"show_thumbs" => 1,	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"categories" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.categories",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_categories",	
				"foreign_table_where" => "AND tx_piapappnote_categories.pid=###CURRENT_PID### ORDER BY tx_piapappnote_categories.uid",	
				"itemsProcFunc" => "tx_piapappnote_be_tree_select2->main",
				"size" => 11,	
				"minitems" => 0,
				"maxitems" => 100,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_piapappnote_categories",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"devices" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.devices",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_devices",	
				"foreign_table_where" => "AND tx_piapappnote_devices.pid=###CURRENT_PID### ORDER BY tx_piapappnote_devices.uid",	
				"itemsProcFunc" => "tx_piapappnote_be_tree_select2->main",
				"size" => 11,	
				"minitems" => 0,
				"maxitems" => 100,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_piapappnote_devices",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"versions" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.versions",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_versions",	
				"foreign_table_where" => "AND tx_piapappnote_versions.pid=###CURRENT_PID### ORDER BY tx_piapappnote_versions.uid",	
				"itemsProcFunc" => "tx_piapappnote_be_tree_select->main",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_piapappnote_versions",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"specialstart" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialstart",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"specialend" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialend",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"specialpriority" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialpriority",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialpriority.I.0", "0"),
					Array("LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialpriority.I.1", "10"),
					Array("LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialpriority.I.2", "20"),
					Array("LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.specialpriority.I.3", "30"),
				),
			)
		),
		"related_appnotes" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_notes.related_appnotes",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_notes",	
				"foreign_table_where" => "AND tx_piapappnote_notes.pid=###CURRENT_PID### ORDER BY tx_piapappnote_notes.noteid",	
				"itemsProcFunc" => "tx_piapappnote_be_prevent_circ_ref->main",
				"size" => 10,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, noteid, title;;;;2-2-2, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, datetime, author, pdffile, zipfiles, categories, devices, versions, specialstart, specialend, specialpriority, related_appnotes")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_piapappnote_categories"] = Array (
	"ctrl" => $TCA["tx_piapappnote_categories"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,description,childof"
	),
	"feInterface" => $TCA["tx_piapappnote_categories"]["feInterface"],
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
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_categories.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_categories.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"childof" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_categories.childof",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_categories",
				"foreign_table_where" => "AND tx_piapappnote_categories.pid=###CURRENT_PID### ORDER BY tx_piapappnote_categories.uid",
				"itemsProcFunc" => "tx_piapappnote_be_tree_select->main",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, childof")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_piapappnote_devices"] = Array (
	"ctrl" => $TCA["tx_piapappnote_devices"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,description,childof,link"
	),
	"feInterface" => $TCA["tx_piapappnote_devices"]["feInterface"],
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
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_devices.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_devices.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"childof" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_devices.childof",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_devices",
				"foreign_table_where" => "AND tx_piapappnote_devices.pid=###CURRENT_PID### ORDER BY tx_piapappnote_devices.uid",
				"itemsProcFunc" => "tx_piapappnote_be_tree_select->main",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"link" => Array (
			"exclude" => 0,
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_devices.link",
			"config" => Array (
				"type" => "input",
				"size" => "15",
				"max" => "255",
				"checkbox" => "",
				"eval" => "trim",
				"wizards" => Array(
					"_PADDING" => 2,
					"link" => Array(
						"type" => "popup",
						"title" => "Link",
						"icon" => "link_popup.gif",
						"script" => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, childof, link")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_piapappnote_versions"] = Array (
	"ctrl" => $TCA["tx_piapappnote_versions"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,description,childof"
	),
	"feInterface" => $TCA["tx_piapappnote_versions"]["feInterface"],
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
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_versions.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_versions.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"childof" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_versions.childof",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_piapappnote_versions",
				"foreign_table_where" => "AND tx_piapappnote_versions.pid=###CURRENT_PID### ORDER BY tx_piapappnote_versions.uid",
				"itemsProcFunc" => "tx_piapappnote_be_tree_select->main",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts];3-3-3, childof")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_piapappnote_zips"] = Array (
	"ctrl" => $TCA["tx_piapappnote_zips"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "file"
	),
	"feInterface" => $TCA["tx_piapappnote_zips"]["feInterface"],
	"columns" => Array (
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_zips.file",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "file;;;;1-1-1")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_piapappnote_pdfs"] = Array (
	"ctrl" => $TCA["tx_piapappnote_pdfs"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "file"
	),
	"feInterface" => $TCA["tx_piapappnote_pdfs"]["feInterface"],
	"columns" => Array (
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:piap_appnote/locallang_db.php:tx_piapappnote_pdfs.file",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "file;;;;1-1-1")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>
