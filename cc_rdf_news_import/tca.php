<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
//$Id: tca.php,v 1.1.1.1 2010/04/15 10:03:15 peimic.comprock Exp $
$TCA["tx_ccrdfnewsimport"] = Array (
	"ctrl" => $TCA["tx_ccrdfnewsimport"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,url"
	),
	"columns" => Array (
		"hidden" => Array (		## WOP:[tables][1][add_hidden]
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["hidden"],
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		## WOP:[tables][1][add_starttime]
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["starttime"],
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		## WOP:[tables][1][add_endtime]
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["endtime"],
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
		"fe_group" => Array (		## WOP:[tables][1][add_access]
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["fe_group"],
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("", 0),
					Array($LANG_GENERAL_LABELS["hide_at_login"], -1),
					Array($LANG_GENERAL_LABELS["any_login"], -2),
					Array($LANG_GENERAL_LABELS["usergroups"], "--div--")
				),
				"foreign_table" => "fe_groups"
			)
		),
		"title" => Array (
			"label" => $LANG_GENERAL_LABELS["title"],
			"config" => Array (
				"type" => "input",
				"size" => "40",
				"max" => "256"
			)
		),
		"url" => Array (
			"label" => "RDF-Url",
			"config" => Array (
				"type" => "input",
				"size" => "40",
				"max" => "2000"
			)
		),
		"intervall" => Array (
			"label" => "Reget after xxx second:||RDF holen (intervall, sek.):",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "12",
				"eval" => "int",
				"default" => 7200
			)
		),
		"bodytext" => Array (
			"label" => "cached content||RDF-Daten (cached):",
			"config" => Array (
				"type" => "text",
				"cols" => "48",
				"rows" => "5"
			)
		),
		"errors" => Array (
			"label" => "error count:||Anzahl Fehler:",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "12",
				"eval" => "int",
				"default" => 0
			)
		),
		"lastError" => Array (
			"label" => "last error||Letzter Fehler:",
			"config" => Array (
				"type" => "input",
				"size" => "40",
				"max" => "256"
			)
		),
		"banlist" => Array (
			"label" => "Banned Items (Comma-separated list)",
			"config" => Array (
				"type" => "input",
				"size" => "40",
				"max" => "2000"
			)
		),
	),
	"types" => Array (
		"1" => Array("showitem" => " title;;1, url, intervall, bodytext, errors, lastError, banlist")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "hidden, starttime, endtime, fe_group"),
	)

#	"types" => Array (
#		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
#	),
#	"palettes" => Array (
#		"1" => Array("showitem" => "starttime, endtime, fe_group")
#	)
);
?>
