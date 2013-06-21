<?php

########################################################################
# Extension Manager/Repository config file for ext: "member_expiry"
#
# Auto generated 05-07-2007 11:04
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Membership Expiry',
	'description' => 'Membership Expiry Processing',
	'category' => 'plugin',
	'author' => '',
	'author_email' => '',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.1',
	'_md5_values_when_last_written' => 'a:15:{s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"0b02";s:14:"ext_tables.php";s:4:"b373";s:14:"ext_tables.sql";s:4:"d810";s:16:"locallang_db.php";s:4:"082d";s:7:"tca.php";s:4:"f339";s:43:"modfunc1/class.tx_memberexpiry_modfunc1.php";s:4:"bd9b";s:22:"modfunc1/locallang.php";s:4:"c9f9";s:19:"doc/wizard_form.dat";s:4:"e236";s:20:"doc/wizard_form.html";s:4:"a29e";s:33:"pi1/class.tx_memberexpiry_pi1.php";s:4:"a022";s:17:"pi1/locallang.php";s:4:"2506";s:27:"pi1/membership_expired.tmpl";s:4:"2e63";s:28:"pi1/membership_expiring.tmpl";s:4:"930d";s:24:"pi1/static/editorcfg.txt";s:4:"a008";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>