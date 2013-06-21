<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout';
t3lib_extMgm::addPlugin(array('LLL:EXT:sr_feuser_register/locallang_db.php:tt_content.list_type', $_EXTKEY.'_pi1'),'list_type');

/**
 * Setting up country, country subdivision, preferred language, first_name and last_name in fe_users table
 * Adjusting some maximum lengths to conform to specifications of payment gateways (ref.: Authorize.net)
 * Adding module_sys_dmail_html, if not added by extension Direct mail
 $Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:04:04 peimic.comprock Exp $
 */

t3lib_div::loadTCA('fe_users');
$TCA['fe_users']['columns']['username']['config']['eval'] = 'nospace,uniqueInPid,required';
$TCA['fe_users']['columns']['password']['config']['eval'] = 'nospace,required';
$TCA['fe_users']['columns']['name']['config']['max'] = '100';
$TCA['fe_users']['columns']['name']['config']['size'] = '20';
$TCA['fe_users']['columns']['company']['config']['max'] = '50';
$TCA['fe_users']['columns']['city']['config']['max'] = '40';
$TCA['fe_users']['columns']['country']['config']['max'] = '60';
$TCA['fe_users']['columns']['zip']['config']['size'] = '15';
$TCA['fe_users']['columns']['zip']['config']['max'] = '20';
$TCA['fe_users']['columns']['email']['config']['max'] = '255';
$TCA['fe_users']['columns']['telephone']['config']['max'] = '25';
$TCA['fe_users']['columns']['fax']['config']['max'] = '25';

// MLC increase view area
$TCA['fe_users']['columns']['usergroup']['config']['size'] = '5';
$TCA['fe_users']['columns']['starttime']['config']['size'] = '12';
$TCA['fe_users']['columns']['endtime']['config']['size'] = '12';

if(!is_array($TCA['fe_users']['columns']['module_sys_dmail_html'])) { $TCA['fe_users']['columns']['module_sys_dmail_html'] = array();}
$TCA['fe_users']['columns']['module_sys_dmail_html']['exclude'] = 0;
$TCA['fe_users']['columns']['module_sys_dmail_html']['label'] = 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.module_sys_dmail_html';
$TCA['fe_users']['columns']['module_sys_dmail_html']['config'] = array('type'=>'check', 'default' => '1');

$TCA['fe_users']['columns']['image']['config']['uploadfolder'] = 'uploads/tx_srfeuserregister';

t3lib_extMgm::addTCAcolumns('fe_users', array(
		'payment_method' => array (
			'exclude' => 0,
			'label' =>
			'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.payment_method',
			'config' => array (
				'type' => 'radio'
				, 'items' => array (
					array( 'Credit card', 'credit_card' )
					, array( 'Phone', 'phone' )
//					, array( 'Free', 'free' )
				)
//				, 'default'	=> 'free'
			)
		),
		'static_info_country' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.static_info_country',
			'config' => array (
				'type' => 'input',
				'size' => '5',
				'max' => '3',
				'eval' => '',
				'default' => ''
			)
		),
		'zone' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.zone',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '40',
				'eval' => 'trim',
				'default' => ''
			)
		),
		'language' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.language',
			'config' => array (
				'type' => 'input',
				'size' => '4',
				'max' => '2',
				'eval' => '',
				'default' => ''
			)
		),
		'first_name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.first_name',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'trim',
				'default' => ''
			)
		),
		'last_name' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.last_name',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'trim',
				'default' => ''
			)
		),
		'date_of_birth' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.date_of_birth',
			'config' => array (
				'type' => 'input',
				"size" => "10",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => ''
			)
		),
		'cc_number' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.cc_number',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '20',
				'eval' => 'nospace',
			)
		),
		'cc_expiry' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.cc_expiry',
			'config' => array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'eval' => 'nospace',
			)
		),
		'cc_name' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.cc_name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
			)
		),
		'referrer_uri' => array (
			'exclude' => 1,
			'label' =>
			'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.referrer_uri',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max' => '255',
			)
		),
		'join_agree' => array (
			'exclude' => 1,
			'label' =>
			'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.join_agree',
			'config' => array (
				'type' => 'input',
				'size' => '10',
				'max' => '10',
			)
		),
		'cc_type' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.cc_type',
			'config' => array (
				'type' => 'input',
				'size' => '20',
				'max' => '20',
				'eval' => 'trim',
			)
		),
		'internal_note' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.internal_note',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'processed' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.processed',
			'config' => Array (
				'type' => 'check',
				'default' => '0',
			)
		),
		'paid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.paid',
			'config' => Array (
				'type' => 'check',
				'default' => '0',
			)
		),
		'use_intend' => Array (
			'exclude' => 1,
//			'label' => 'LLL:EXT:sr_feuser_register/locallang_db.php:fe_users.use_intend',
			'label' => 'dasdsa',
			'config' => Array (
				"type" => "check",
				'default' => '0',
			)
		),
	)
);

// MLC showRecordFieldList
$TCA['fe_users']['interface']['showRecordFieldList'] .= ',zone,static_info_country,language';
$TCA['fe_users']['interface']['showRecordFieldList'] .= ',first_name,last_name,date_of_birth';

//js
$TCA['fe_users']['interface']['showRecordFieldList'] .= ',paid';

if(!strstr($TCA['fe_users']['interface']['showRecordFieldList']
	, 'module_sys_dmail_html'))
{
	$TCA['fe_users']['interface']['showRecordFieldList'] .= ',module_sys_dmail_html';
}

// MLC fe_admin_fieldList corrections
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',zone,static_info_country,language,payment_method';
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',first_name,last_name';
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',image,disable,date_of_birth';
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',cc_number,cc_expiry,cc_name,cc_type,join_agree,referrer_uri,payment_method,processed,paid,starttime,endtime';

// MLC redemption and tt_products recall
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',redemptionCode,tt_products';

// MLC conference group changes
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',usergroupaddon';

// MLC 20081202 recaptcha
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',captcha';

// js newsfeed fields recall
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',newsfeedUse,newsfeedOption,newsfeedTopics,newsfeedComments';

if(!strstr($TCA['fe_users']['feInterface']['fe_admin_fieldList']
	, 'module_sys_dmail_html'))
{
	$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',module_sys_dmail_html';
}

// MLC show items determines where it's placed on the back-end
$TCA['fe_users']['types']['0']['showitem'] = str_replace('country', 'zone,static_info_country,language', $TCA['fe_users']['types']['0']['showitem']);

// MLC remove name,country from palette 2
$TCA['fe_users']['palettes']['2']['showitem'] = str_replace('title,company', 'name,country', $TCA['fe_users']['palettes']['2']['showitem']);
$TCA['fe_users']['palettes']['2']['showitem'] = str_replace('title,company', '', $TCA['fe_users']['palettes']['2']['showitem']);

// MLC replace name with first_name, don't confuse name with username
$TCA['fe_users']['types']['0']['showitem'] = preg_replace('/\bname\b/', 'first_name', $TCA['fe_users']['types']['0']['showitem']);

// MLC remove email to place after last_name
$TCA['fe_users']['types']['0']['showitem'] = str_replace('email', '', $TCA['fe_users']['types']['0']['showitem']);

// MLC append address with last_name,date_of_birth,email,title,company
$TCA['fe_users']['types']['0']['showitem'] = str_replace('address', 'last_name,date_of_birth,email,title,company,address', $TCA['fe_users']['types']['0']['showitem']);
if(!strstr($TCA['fe_users']['types']['0']['showitem'], 'module_sys_dmail_html')) { $TCA['fe_users']['types']['0']['showitem'] = str_replace('www', 'www,module_sys_dmail_html', $TCA['fe_users']['types']['0']['showitem']); }

// MLC add membership information
// plugin payment_method
$TCA['fe_users']['types']['0']['showitem'] = str_replace('usergroup', 'usergroup,processed,payment_method,paid', $TCA['fe_users']['types']['0']['showitem']);

// MLC add payment information
$TCA['fe_users']['types']['0']['showitem'] = str_replace('module_sys_dmail_html', 'module_sys_dmail_html,internal_note;;;;3-3-3,join_agree,referrer_uri,cc_type;;;;3-3-3,cc_number,cc_expiry,cc_name', $TCA['fe_users']['types']['0']['showitem']);

?>
