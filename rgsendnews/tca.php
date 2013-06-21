<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_rgsendnews_stat"] = array (
	"ctrl" => $TCA["tx_rgsendnews_stat"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,sender,receiver,newsid,comment,ip,recmail,sendmail,htmlmail"
	),
	"feInterface" => $TCA["tx_rgsendnews_stat"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"sender" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.sender",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",
			)
		),
		"crdate" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.crdate",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
				"eval" => "datetime",				
			)
		),		
		"receiver" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.receiver",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",
			)
		),
		"newsid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.newsid",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tt_news",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"comment" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.comment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"ip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.ip",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"recmail" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.recmail",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",
			)
		),
		"sendmail" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.sendmail",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",
			)
		),
		"htmlmail" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat.htmlmail",		
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden, sender;;1, receiver;;2, newsid;;3 ")
	),
	"palettes" => array (
		"1" => array("showitem" => "sendmail, ip, crdate"),
		"2" => array("showitem" => "recmail"),
		"3" => array("showitem" => "comment, htmlmail"),
	)
);
?>