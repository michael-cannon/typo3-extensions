<?php

########################################################################
# Extension Manager/Repository config file for ext: "kb_tv_migrate"
#
# Auto generated 14-10-2009 22:58
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'KB TV Migrate',
	'description' => 'Automates the process of migrating an site using the Template Selector (Auto parser) method for generating pages to the Templa Voila style',
	'category' => 'module',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Bernhard Kraft',
	'author_email' => 'kraftb@kraftb.at',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.5.0-0.0.0',
			'php' => '3.0.0-0.0.0',
			'cms' => '',
			'templavoila' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:12:{s:35:"class.tx_kbtvmigrate_xmlrelhndl.php";s:4:"fa34";s:21:"ext_conf_template.txt";s:4:"fc12";s:12:"ext_icon.gif";s:4:"687b";s:17:"ext_localconf.php";s:4:"9f08";s:14:"ext_tables.php";s:4:"9f36";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"d38f";s:14:"mod1/index.php";s:4:"0060";s:18:"mod1/locallang.php";s:4:"17cc";s:22:"mod1/locallang_mod.php";s:4:"543e";s:19:"mod1/moduleicon.gif";s:4:"4b6d";s:13:"res/jsfunc.js";s:4:"958b";}',
);

?>