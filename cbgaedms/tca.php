<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_cbgaedms_doctype"] = array (
	"ctrl" => $TCA["tx_cbgaedms_doctype"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,doctype,description"
	),
	"feInterface" => $TCA["tx_cbgaedms_doctype"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
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
				'foreign_table'       => 'tx_cbgaedms_doctype',
				'foreign_table_where' => 'AND tx_cbgaedms_doctype.pid=###CURRENT_PID### AND tx_cbgaedms_doctype.sys_language_uid IN (-1,0) AND tx_cbgaedms_doctype.hidden = 0 AND tx_cbgaedms_doctype.deleted = 0 ORDER BY tx_cbgaedms_doctype.doctype',
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
		'required' => array (		
			'exclude' => 1,
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doctype.required",		
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"doctype" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doctype.doctype",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doctype.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"agency" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doctype.agency",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_cbgaedms_agency",	
				"foreign_table_where" => "AND tx_cbgaedms_agency.pid=###CURRENT_PID### AND tx_cbgaedms_agency.hidden = 0 AND tx_cbgaedms_agency.deleted = 0 ORDER BY tx_cbgaedms_agency.agency",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, doctype, description,required,agency")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_cbgaedms_doc"] = array (
	"ctrl" => $TCA["tx_cbgaedms_doc"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,doc,doctype,description,version,feuser"
	),
	"feInterface" => $TCA["tx_cbgaedms_doc"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
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
				'foreign_table'       => 'tx_cbgaedms_doc',
				'foreign_table_where' => 'AND tx_cbgaedms_doc.pid=###CURRENT_PID### AND tx_cbgaedms_doc.sys_language_uid IN (-1,0) AND tx_cbgaedms_doc.hidden = 0 AND tx_cbgaedms_doc.deleted = 0 ORDER BY tx_cbgaedms_doc.doc',
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
				'eval'     => 'date',
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
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"doc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doc.doc",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"doctype" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doc.doctype",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_cbgaedms_doctype",	
				'foreign_table_where' => 'AND tx_cbgaedms_doctype.pid=###CURRENT_PID### AND tx_cbgaedms_doctype.hidden = 0 AND tx_cbgaedms_doctype.deleted = 0 ORDER BY tx_cbgaedms_doctype.doctype',
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
							"table"=>"tx_cbgaedms_doctype",
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
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doc.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"version" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doc.version",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_cbgaedms_docversion",	
				"foreign_table_where" => "AND tx_cbgaedms_docversion.pid=###CURRENT_PID### AND tx_cbgaedms_docversion.hidden = 0 AND tx_cbgaedms_docversion.deleted = 0 ORDER BY tx_cbgaedms_docversion.docversion DESC",
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_cbgaedms_doc_version_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_cbgaedms_docversion",
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
		"feuser" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doc.feuser",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
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
							"table"=>"fe_users",
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
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, doc, doctype, description, version, feuser")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_cbgaedms_docversion"] = array (
	"ctrl" => $TCA["tx_cbgaedms_docversion"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,versiontitle,docversion,filename,file,description,feuser"
	),
	"feInterface" => $TCA["tx_cbgaedms_docversion"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
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
				'foreign_table'       => 'tx_cbgaedms_docversion',
				'foreign_table_where' => 'AND tx_cbgaedms_docversion.pid=###CURRENT_PID### AND tx_cbgaedms_docversion.sys_language_uid IN (-1,0) AND tx_cbgaedms_docversion.hidden = 0 AND tx_cbgaedms_docversion.deleted = 0 ORDER BY tx_cbgaedms_docversion.docversion DESC',
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
				'eval'     => 'date',
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
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"versiontitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion.versiontitle",		
			"config" => Array (
				"type" => "none",
			)
		),
		"docversion" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion.docversion",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,int",
			)
		),
		"filename" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion.filename",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		"file" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion.file",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 100000,	
				"uploadfolder" => "uploads/tx_cbgaedms",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"feuser" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion.feuser",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
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
							"table"=>"fe_users",
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
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, versiontitle, docversion, filename, file, description, feuser")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_cbgaedms_agency"] = array (
	"ctrl" => $TCA["tx_cbgaedms_agency"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,starttime,endtime,fe_group,agency,agencysilo,country,address,address2,city,state,postalcode,numberofemployees,officephone,officefax,administrator,parentagency,documents,incidentmanager,alternateincidentmanagers,buildingpoc,buildingpocphone,buildingpocphoneafterhours,buildingalternatepoc,buildingalternatepocphone,buildingalternatepocphoneafterhours,emergencycall,emergencybridgeline,passcode,chairpasscode,securityphone,receptionphone,phone247us,phone247nonus,feuser,viewers"
	),
	"feInterface" => $TCA["tx_cbgaedms_agency"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
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
				'foreign_table'       => 'tx_cbgaedms_agency',
				'foreign_table_where' => 'AND tx_cbgaedms_agency.pid=###CURRENT_PID### AND tx_cbgaedms_agency.sys_language_uid IN (-1,0) AND tx_cbgaedms_agency.hidden = 0 AND tx_cbgaedms_agency.deleted = 0',
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
				'eval'     => 'date',
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
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"agency" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.agency",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"agencysilo" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.agencysilo",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_cbgaedms_silo",	
				"foreign_table_where" => "AND tx_cbgaedms_silo.pid=###CURRENT_PID### AND tx_cbgaedms_silo.hidden = 0 AND tx_cbgaedms_silo.deleted = 0 ORDER BY tx_cbgaedms_silo.silo",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"country" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.country",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "static_countries",	
				"foreign_table_where" => "ORDER BY static_countries.cn_short_en",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"address" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.address",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"address2" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.address2",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"city" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.city",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"state" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.state",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "static_country_zones",	
				"foreign_table_where" => "ORDER BY static_country_zones.zn_name_local",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"postalcode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.postalcode",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"numberofemployees" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.numberofemployees",		
			"config" => Array (
				"type" => "input",	
				"size" => "6",	
				"eval" => "int",
			)
		),
		"officephone" => Array (		
			"exclude" => 1,		
				"items" => Array (
					Array("",0),
				),
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.officephone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"officefax" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.officefax",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"administrator" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.administrator",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
				"size" => 15,	
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
							"table"=>"fe_users",
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
		"parentagency" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.parentagency",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_cbgaedms_agency",	
				"foreign_table_where" => "AND tx_cbgaedms_agency.pid=###CURRENT_PID### AND tx_cbgaedms_agency.hidden = 0 AND tx_cbgaedms_agency.deleted = 0 ORDER BY tx_cbgaedms_agency.agency",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"documents" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.documents",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_cbgaedms_doc",	
				"foreign_table_where" => "AND tx_cbgaedms_doc.hidden = 0 and tx_cbgaedms_doc.deleted = 0 ORDER BY tx_cbgaedms_doc.doc",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_cbgaedms_agency_documents_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_cbgaedms_doc",
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
		"incidentmanager" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.incidentmanager",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
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
							"table"=>"fe_users",
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
		"buildingpoc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.buildingpoc",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"buildingpocphone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.buildingpocphone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"buildingpocphoneafterhours" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.buildingpocphoneafterhours",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"buildingalternatepoc" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.buildingalternatepoc",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"buildingalternatepocphone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.buildingalternatepocphone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"buildingalternatepocphoneafterhours" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.buildingalternatepocphoneafterhours",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"emergencycall" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.emergencycall",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"emergencybridgeline" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.emergencybridgeline",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"passcode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.passcode",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"chairpasscode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.chairpasscode",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",
			)
		),
		"securityphone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.securityphone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"receptionphone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.receptionphone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"phone247us" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.phone247us",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"phone247nonus" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.phone247nonus",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"feuser" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.feuser",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
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
							"table"=>"fe_users",
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
		"alternateincidentmanagers" => Array (
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.alternateincidentmanagers",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
				"size" => 15,	
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
							"table"=>"fe_users",
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
		"viewers" => Array (
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency.viewers",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
				"size" => 15,	
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
							"table"=>"fe_users",
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
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, agency, agencysilo, country, address, address2, city, state, postalcode, numberofemployees, officephone, officefax, administrator, parentagency, documents, incidentmanager, alternateincidentmanagers, buildingpoc, buildingpocphone, buildingpocphoneafterhours, buildingalternatepoc, buildingalternatepocphone, buildingalternatepocphoneafterhours, emergencycall, emergencybridgeline, passcode, chairpasscode, securityphone, receptionphone, phone247us, phone247nonus, feuser,viewers")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_cbgaedms_silo"] = array (
	"ctrl" => $TCA["tx_cbgaedms_silo"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,silo,description"
	),
	"feInterface" => $TCA["tx_cbgaedms_silo"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (		
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
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
				'foreign_table'       => 'tx_cbgaedms_silo',
				'foreign_table_where' => 'AND tx_cbgaedms_silo.pid=###CURRENT_PID### AND tx_cbgaedms_silo.sys_language_uid IN (-1,0) AND tx_cbgaedms_silo.hidden = 0 AND tx_cbgaedms_silo.deleted = 0',
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
		"silo" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_silo.silo",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_silo.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"agency" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_silo.agency",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_cbgaedms_agency",	
				"foreign_table_where" => "AND tx_cbgaedms_agency.pid=###CURRENT_PID### AND tx_cbgaedms_agency.hidden = 0 AND tx_cbgaedms_agency.deleted = 0 ORDER BY tx_cbgaedms_agency.agency",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, silo, description, agency")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA["tx_cbgaedms_reports"] = array (
    "ctrl" => $TCA["tx_cbgaedms_reports"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" =>
"sys_language_uid,l18n_parent,l18n_diffsource,hidden,report,frequency,recipients,parentagency,messagebody,reporton"
    ),
    "feInterface" => $TCA["tx_cbgaedms_reports"]["feInterface"],
    "columns" => array (
        't3ver_label' => array (        
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
            'config' => array (
                'type' => 'input',
                'size' => '30',
                'max'  => '30',
            )
        ),
        'sys_language_uid' => array (        
            'exclude' => 1,
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array (
                'type'                => 'select',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages',
-1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value',
0)
                )
            )
        ),
        'l18n_parent' => array (        
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       =>
'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config'      => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                ),
                'foreign_table'       => 'tx_cbgaedms_reports',
                'foreign_table_where' => 'AND
tx_cbgaedms_reports.pid=###CURRENT_PID### AND
tx_cbgaedms_reports.sys_language_uid IN (-1,0)',
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
        "report" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.report",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.report.I.0",
"3",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_report_0.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.report.I.1",
"1",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_report_1.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.report.I.2",
"2",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_report_2.gif"),
                ),
                "size" => 1,    
                "maxitems" => 1,
            )
        ),
        "frequency" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency.I.0",
"1",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_frequency_0.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency.I.1",
"7",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_frequency_1.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency.I.2",
"28",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_frequency_2.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency.I.3",
"90",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_frequency_3.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency.I.4",
"183",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_frequency_4.gif"),
                    Array("LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.frequency.I.5",
"365",
t3lib_extMgm::extRelPath("cbgaedms")."selicon_tx_cbgaedms_reports_frequency_5.gif"),
                ),
                "size" => 1,    
                "maxitems" => 1,
            )
        ),
        "recipients" => Array (        
            "exclude" => 1,        
            "label" =>
"LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.recipients",        
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.disable = 0 AND fe_users.deleted = 0 ORDER BY fe_users.last_name, fe_users.first_name",	
				"size" => 15,	
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
							"table"=>"fe_users",
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
        "parentagency" => Array (        
			"exclude" => 1,		
            "label" => "LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.parentagency",        
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_cbgaedms_agency",	
				"foreign_table_where" => "AND tx_cbgaedms_agency.pid=###CURRENT_PID### AND tx_cbgaedms_agency.hidden = 0 AND tx_cbgaedms_agency.deleted = 0 ORDER BY tx_cbgaedms_agency.agency",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
        "messagebody" => Array (        
            "exclude" => 1,        
            "label" =>
"LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.messagebody",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "reporton" => Array (        
            "exclude" => 1,        
            "label" =>
"LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports.reporton",        
            "config" => Array (
                "type" => "check",
                "default" => 1,
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent,
l18n_diffsource, hidden;;1, report, frequency, recipients, parentagency,
messagebody, reporton")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);
?>
