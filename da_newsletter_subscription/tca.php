<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_danewslettersubscription_cat"] = Array (
	"ctrl" => $TCA["tx_danewslettersubscription_cat"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,descr"
	),
	"feInterface" => $TCA["tx_danewslettersubscription_cat"]["feInterface"],
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
			"exclude" => 0,
			"label" => "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_cat.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"max" => "255",
				"eval" => "required,trim",
			)
		),
		"descr" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_cat.descr",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"editor" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_cat.editor",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "fe_users",
				"foreign_table_where" => "AND FIND_IN_SET( '".FE_GROUP_EDITOR."', `usergroup` ) ORDER BY fe_users.last_name",
				"size" => 1,
        "minitems" => 0,
        "maxitems" => 1,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, descr;;;;3-3-3, editor;;;;3-3-3")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);

$TCA["tx_danewslettersubscription_newsletter"] = Array (
	"ctrl" => $TCA["tx_danewslettersubscription_newsletter"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,link_file,category, descr, html_body"
	),
	"feInterface" => $TCA["tx_danewslettersubscription_newsletter"]["feInterface"],
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
			"exclude" => 0,
			"label" => "LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_newsletter.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"max" => "255",
				"eval" => "required,trim",
			)
		),
		"link_file" => Array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_newsletter.file',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	// Must be empty for disallowed to work.
				'disallowed' => 'php,php3',
				'max_size' => '10000',
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '1',
				'autoSizeMax' => '1',
				'maxitems' => '1',
				'minitems' => '0'
			)
		),
		"html_body" => Array (
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_newsletter.html_body',
			'config' => Array (
				'type' => 'text',
				'cols' => '80',
				'rows' => '15',
			)
		),
'category' => Array (
			'exclude' => 1,
		#	'l10n_mode' => 'exclude', // the localizalion mode will be handled by the userfunction
			'label' => 'LLL:EXT:da_newsletter_subscription/locallang_db.php:tx_danewslettersubscription_newsletter.category',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_danewslettersubscription_cat',
				 "size" => 1,
         "minitems" => 0,
         "maxitems" => 1,

			)
		),
	),

	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, category,
		link_file;;;;4-4-4,html_body")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime, fe_group")
	)
);
?>