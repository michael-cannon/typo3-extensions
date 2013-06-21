<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_cbgaedms_doctype"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doctype',		
		'label'     => 'doctype',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY doctype",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbgaedms_doctype.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, doctype, description, required, agency",
	)
);

$TCA["tx_cbgaedms_doc"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_doc',		
		'label'     => 'doc',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY doc",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbgaedms_doc.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, doc, doctype, description, version, feuser",
	)
);

$TCA["tx_cbgaedms_docversion"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_docversion',		
		'label'     => 'versiontitle',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY docversion DESC",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbgaedms_docversion.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, versiontitle, docversion, filename, file, description, feuser",
	)
);

$TCA["tx_cbgaedms_agency"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_agency',		
		'label'     => 'agency',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY agency",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbgaedms_agency.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, starttime, endtime, fe_group, agency, agencysilo, country, address, address2, city, state, postalcode, numberofemployees, officephone, officefax, administrator, parentagency, documents, incidentmanager, alternateincidentmanagers, buildingpoc, buildingpocphone, buildingpocphoneafterhours, buildingalternatepoc, buildingalternatepocphone, buildingalternatepocphoneafterhours, emergencycall, emergencybridgeline, passcode, chairpasscode, securityphone, receptionphone, phone247us, phone247nonus, feuser, viewers",
	)
);

$TCA["tx_cbgaedms_silo"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_silo',		
		'label'     => 'silo',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY silo",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbgaedms_silo.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, silo, description, agency",
	)
);

$tempColumns = Array (
	"tx_cbgaedms_mobilephone" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbgaedms/locallang_db.xml:fe_users.tx_cbgaedms_mobilephone",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
	"tx_cbgaedms_homephone" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cbgaedms/locallang_db.xml:fe_users.tx_cbgaedms_homephone",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_cbgaedms_mobilephone;;;;1-1-1, tx_cbgaedms_homephone");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_mvc1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_mvc1']='pi_flexform';


t3lib_extMgm::addStaticFile('cbgaedms', './configurations/mvc1', 'EDMS: Locations');


t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_mvc1', 'FILE:EXT:cbgaedms/configurations/mvc1/flexform.xml');


t3lib_extMgm::addPlugin(array('LLL:EXT:cbgaedms/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_mvc1'),'list_type');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_mvc2']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_mvc2']='pi_flexform';


t3lib_extMgm::addStaticFile('cbgaedms', './configurations/mvc2', 'EDMS: Users');


t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_mvc2', 'FILE:EXT:cbgaedms/configurations/mvc2/flexform.xml');


t3lib_extMgm::addPlugin(array('LLL:EXT:cbgaedms/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_mvc2'),'list_type');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_mvc3']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_mvc3']='pi_flexform';


t3lib_extMgm::addStaticFile('cbgaedms', './configurations/mvc3', 'EDMS: Documents');


t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_mvc3', 'FILE:EXT:cbgaedms/configurations/mvc3/flexform.xml');


t3lib_extMgm::addPlugin(array('LLL:EXT:cbgaedms/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_mvc3'),'list_type');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_mvc4']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_mvc4']='pi_flexform';


t3lib_extMgm::addStaticFile('cbgaedms', './configurations/mvc4', 'EDMS: Reporting');


t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_mvc4', 'FILE:EXT:cbgaedms/configurations/mvc4/flexform.xml');


t3lib_extMgm::addPlugin(array('LLL:EXT:cbgaedms/locallang_db.xml:tt_content.list_type_pi4', $_EXTKEY.'_mvc4'),'list_type');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_mvc5']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_mvc5']='pi_flexform';


t3lib_extMgm::addStaticFile('cbgaedms', './configurations/mvc5', 'EDMS: Control Panel');


t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_mvc5', 'FILE:EXT:cbgaedms/configurations/mvc5/flexform.xml');


t3lib_extMgm::addPlugin(array('LLL:EXT:cbgaedms/locallang_db.xml:tt_content.list_type_pi5', $_EXTKEY.'_mvc5'),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY, './configurations', 'EDMS: Base');

$TCA["tx_cbgaedms_reports"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:cbgaedms/locallang_db.xml:tx_cbgaedms_reports',        
        'label'     => 'report',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => TRUE, 
        'origUid' => 't3_origuid',
        'languageField'            => 'sys_language_uid',    
        'transOrigPointerField'    => 'l18n_parent',    
        'transOrigDiffSourceField' => 'l18n_diffsource',    
        'default_sortby' => "ORDER BY tstamp DESC",    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_cbgaedms_reports.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource,
hidden, report, frequency, recipients, parentagency, messagebody, reporton",
    )
);

?>
