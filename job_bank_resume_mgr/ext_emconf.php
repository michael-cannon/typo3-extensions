<?php

########################################################################
# Extension Manager/Repository config file for ext: "job_bank_resume_mgr"
#
# Auto generated 16-07-2007 17:04
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Job Bank Resume Manager',
	'description' => 'Stores Resume for a Job',
	'category' => 'plugin',
	'author' => 'Ritesh Gurung',
	'author_email' => 'ritesh@srijan.in',
	'shy' => '',
	'dependencies' => 'cms,job_bank',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.1',
	'_md5_values_when_last_written' => 'a:16:{s:21:"ext_conf_template.txt";s:4:"c273";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"019e";s:14:"ext_tables.php";s:4:"a7e1";s:14:"ext_tables.sql";s:4:"2da4";s:28:"ext_typoscript_constants.txt";s:4:"65b0";s:24:"ext_typoscript_setup.txt";s:4:"81b1";s:33:"icon_tx_jobbankresumemgr_info.gif";s:4:"401f";s:16:"locallang_db.php";s:4:"7818";s:8:"logo.jpg";s:4:"95b4";s:9:"main.tmpl";s:4:"1df5";s:7:"tca.php";s:4:"0517";s:23:"pi1/class.phpmailer.php";s:4:"7d88";s:18:"pi1/class.smtp.php";s:4:"4b45";s:37:"pi1/class.tx_jobbankresumemgr_pi1.php";s:4:"d6f1";s:17:"pi1/locallang.php";s:4:"7545";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'job_bank' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>