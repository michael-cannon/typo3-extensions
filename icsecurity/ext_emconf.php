<?php 
########################################################################
# Extension Manager/Repository config file for ext: "icsecurity"
#
# Auto generated 02-04-2009 23:43
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'iConspect Security Helper',
	'description' => '',
	'category' => 'misc',
	'author' => 'Michael Cannon',
	'author_email' => 'michael@peimic.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'Peimic.com',
	'version' => '0.0.1',
	'constraints' => array(
		'depends' => array(
 			'captcha' => '',
 			'wt_spamshield' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
 			'wt_doorman' => '',
 			'be_acl' => '',
 			'https_enforcer' => '',
 			'security_check' => '',
			'timtab_badbehavior' => '',
 			'security_check' => '',
		),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(
	),
);

?>