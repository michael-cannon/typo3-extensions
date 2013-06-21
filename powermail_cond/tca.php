<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_powermailcond_conditions"] = array (
	"ctrl" => $TCA["tx_powermailcond_conditions"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,title,line,rules"
	),
	"feInterface" => $TCA["tx_powermailcond_conditions"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_powermailcond_conditions',
				'foreign_table_where' => 'AND tx_powermailcond_conditions.pid=###CURRENT_PID### AND tx_powermailcond_conditions.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				//'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				//'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"line" => Array (
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.0", ""), // all
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.1", "1"), // line 1
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.2", "2"), // line 2
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.3", "3"), // line 3
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.4", "4"), // line 4
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.5", "5"), // line 5
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.6", "6"), // line 6
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.7", "7"), // line 7
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.8", "8"), // line 8
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.9", "9"), // line 9
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.10", "10"), // line 10
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.11", "11"), // line 11
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.12", "12"), // line 12
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.13", "13"), // line 13
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.14", "14"), // line 14
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.15", "15"), // line 15
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.16", "16"), // line 16
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.17", "17"), // line 17
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.18", "18"), // line 18
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.19", "19"), // line 19
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.20", "20"), // line 20
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.21", "21"), // line 21
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.22", "22"), // line 22
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.23", "23"), // line 23
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.24", "24"), // line 24
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.line.25", "25"), // line 25
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"rules" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.rules",		
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_powermailcond_rules",
				"foreign_table_where" => "AND tx_powermailcond_rules.pid=###CURRENT_PID### ORDER BY tx_powermailcond_rules.uid",
				"foreign_field" => "conditions",
				"maxitems" => 99,
				'appearance' => array(
					'collapseAll' => 1,
					'expandSingle' => 1,
					'useSortable' => 1,
					'newRecordLinkAddTitle' => 1,
					'newRecordLinkPosition' => 'both',
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, --palette--;;2, rules;;;;3-3-3, ")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime"),
		"2" => array("showitem" => "title, line")
	)
);



$TCA["tx_powermailcond_rules"] = array (
	"ctrl" => $TCA["tx_powermailcond_rules"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,title,conjunction,ops,condstring,fieldname,fieldsetname"
	),
	"feInterface" => $TCA["tx_powermailcond_rules"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				//'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				//'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		/*
		"conjunction" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.conjunction",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.conjunction.I.0", "0"), // empty
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.conjunction.I.1", "1"), // AND
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.conjunction.I.2", "2"), // OR
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		*/
		"ops" => Array (
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("", ""), // empty
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.8", "8"), // is set
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.9", "9"), // is not set
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.0", "0"), // is equal
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.1", "1"), // is not equal
					//Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.2", "2"), // is greater than
					//Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.3", "3"), // is greater equal than
					//Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.4", "4"), // is lower than
					//Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.5", "5"), // is lower equal than
					//Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.6", "6"), // contains
					//Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator.I.7", "7"), // contains not
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"conditions" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_conditions.rules",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_powermailcond_conditions",
				"foreign_table_where" => "AND tx_powermailcond_conditions.pid=###CURRENT_PID### ORDER BY tx_powermailcond_conditions.sorting",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"condstring" => Array (
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.condstring",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "2",
			)
		),
        "actions" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action.I.0", "0"), // hide
                    Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action.I.1", "1"), // unhide
                    Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action.I.2", "2"), // make to mandatory JS
                    //Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action.I.3", "3"), // activate
                    //Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action.I.4", "4", t3lib_extMgm::extRelPath("powermail_cond")."selicon_tx_powermailcond_rules_action_2.gif"), // deactivate
                    //Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.action.I.5", "5", t3lib_extMgm::extRelPath("powermail_cond")."selicon_tx_powermailcond_rules_action_3.gif"), // activate
                ),
                "size" => 1,
                "maxitems" => 1,
            )
        ),
		"fieldname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.fieldname",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.fieldname.I.0", "0"),
				),
				"itemsProcFunc" => "tx_powermailcond_tx_powermailcond_rules_fieldname->fieldname",	
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"fieldsetname" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.fieldsetname",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.fieldsetname.I.0", "0"),
				),
				"itemsProcFunc" => "tx_powermailcond_tx_powermailcond_rules_fieldname->fieldsetname",	
				"size" => 1,	
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, conjunction;;;;3-3-3, actions;LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.operator;2;;1-1-1, --palette--;LLL:EXT:powermail_cond/locallang_db.xml:tx_powermailcond_rules.fieldname;3;;1-1-1")
	),
	"palettes" => array (
		'1' => array('showitem' => 'starttime, endtime'),
		'2' => array('showitem' => 'ops, condstring'),
		'3' => array('showitem' => 'fieldname, fieldsetname')
	)
);
?>