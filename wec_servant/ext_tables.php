<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$tempColumns = Array (
	"tx_wecservant_is_contact" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:wec_servant/locallang_db.xml:fe_users.tx_wecservant_is_contact",
		"config" => Array (
			"type" => "check",
		)
	),
);
t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
// t3lib_extMgm::addToAllTCAtypes("fe_users","tx_wecservant_is_contact");


t3lib_extMgm::allowTableOnStandardPages("tx_wecservant_minopp");
t3lib_extMgm::addToInsertRecords("tx_wecservant_minopp");

$TCA["tx_wecservant_minopp"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_servant/locallang_db.xml:tx_wecservant_minopp",
		"label" => "name",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField" => "sys_language_uid",
		"transOrigPointerField" => "l18n_parent",
		"transOrigDiffSourceField" => "l18n_diffsource",
		"default_sortby" => "ORDER BY name",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
			"starttime" => "starttime",
			"endtime" => "endtime",
		),
		"versioningWS" => TRUE,
		'versioning_followPages' => TRUE,
		"origUid" => "t3_origuid",
		"shadowColumnsForNewPlaceholders" => "sys_language_uid,l18n_parent",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icon_tx_wecservant_minopp.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "datetime, starttime, endtime, disabled, name, reference_code, description, youth_friendly, supervision_required, ministry_uid, contact_uid, location, times_needed, priority, skills, contact_info, misc_description, qualifications, openings, sys_language_uid, l18n_parent,  l18n_diffsource",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_wecservant_skills");
t3lib_extMgm::addToInsertRecords("tx_wecservant_skills");

$TCA["tx_wecservant_skills"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_servant/locallang_db.xml:tx_wecservant_skills",
		"label" => "name",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField" => "sys_language_uid",
		"transOrigPointerField" => "l18n_parent",
		"transOrigDiffSourceField" => "l18n_diffsource",
		"default_sortby" => "ORDER BY name",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"versioningWS" => TRUE,
		'versioning_followPages' => TRUE,
		"origUid" => "t3_origuid",
		"shadowColumnsForNewPlaceholders" => "sys_language_uid,l18n_parent",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icon_tx_wecservant_skills.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "disabled, name, description,group_by,sort_order,required_group, sys_language_uid, l18n_parent,  l18n_diffsource",
	)
);

$tempColumns2 = Array (
	"wecgroup_type" => Array (
		"label" => "LLL:EXT:wec_servant/locallang_db.xml:fe_groups.tx_wecgroup_type",
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array('',0),
			),
			"foreign_table" => "tx_wecgroup_type",
			"foreign_table_where" => "ORDER BY tx_wecgroup_type.uid",
			"size" => 1,
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	'tx_wecservant_ministryadministrator' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:wec_servant/locallang_db.xml:fe_groups.tx_wecservant_ministryadministrator',
		'config' => array (
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'be_users',
			'size' => 3,
			'minitems' => 0,
			'maxitems' => 99,
		)
	),
);
t3lib_div::loadTCA("fe_groups");
t3lib_extMgm::addTCAcolumns("fe_groups",$tempColumns2,1);
t3lib_extMgm::addToAllTCAtypes("fe_groups","wecgroup_type,tx_wecservant_ministryadministrator");


//t3lib_extMgm::allowTableOnStandardPages("tx_wecgroup_type");
$TCA["tx_wecgroup_type"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_servant/locallang_db.xml:tx_wecgroup_type",
		"label" => "name",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",
		"rootLevel" => 1, // is on root of site (needs to be because above needs to know where it is)
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icon_tx_wecservant_type.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "name, description",
	)
);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages,recursive';

t3lib_extMgm::addPlugin(Array('LLL:EXT:wec_servant/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="pi_flexform";
t3lib_extMgm::addPiFlexFormValue($_EXTKEY."_pi1", "FILE:EXT:wec_servant/flexform_ds.xml");

t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts/','WEC Servant Matcher (old) template');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/tsnew/','WEC Servant Matcher template');

$tempColumns = array (
	'tx_wecservant_shelbyid' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:wec_servant/locallang_db.xml:fe_users.tx_wecservant_shelbyid',
		'config' => array (
			'type' => 'input',
			'size' => '10',
			'max' => '10',
			'eval' => 'trim,int',
			'default' => '',
			'checkbox' => '0'
		)
	),
	'tx_wecservant_bg_check_date' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:wec_servant/locallang_db.xml:fe_users.tx_wecservant_bg_check_date',
		'config' => array (
			'type' => 'input',
			'size' => '10',
			'max' => '10',
			'eval' => 'date',
			'default' => '',
			'checkbox' => '0'
		)
	),
);


t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
// t3lib_extMgm::addToAllTCAtypes('fe_users','tx_wecservant_shelbyid;;;;1-1-1, tx_wecservant_bg_check_date');
$TCA['fe_users']['types']['0']['showitem'] = str_replace(', date_of_birth', ', tx_wecservant_is_contact, tx_wecservant_shelbyid, tx_wecservant_bg_check_date, date_of_birth', $TCA['fe_users']['types']['0']['showitem']);


if (TYPO3_MODE == 'BE')	{
	t3lib_extMgm::insertModuleFunction(
		'web_func',
		'tx_wecservant_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_wecservant_modfunc1.php',
		'LLL:EXT:wec_servant/locallang_db.xml:moduleFunction.tx_wecservant_modfunc1'
	);
}

?>