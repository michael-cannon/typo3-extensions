<?php

########################################################################
# Extension Manager/Repository config file for ext: "cbhtmlmailsmtp"
#
# Auto generated 02-09-2008 12:40
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'HTML Mail via SMTP',
	'description' => 'Adds configurable SMTP host to t3lib_htmlmail. Requires TYPO3 4.0.0+ and a global installed PEAR::Mail.',
	'category' => 'module',
	'shy' => 0,
	'version' => '0.0.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Michael Cannon',
	'author_email' => 'michael@peimic.com',
	'author_company' => 'Peimic.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:6:{s:9:"ChangeLog";s:4:"8943";s:20:"class.ux_t3lib_htlmail.php";s:4:"a4fc";s:21:"ext_conf_template.txt";s:4:"bd0d";s:12:"ext_icon.gif";s:4:"b0fa";s:17:"ext_localconf.php";s:4:"10b7";s:14:"doc/manual.sxw";s:4:"ea9b";}',
);

?>