<?php

########################################################################
# Extension Manager/Repository config file for ext "newsreadedcount".
#
# Auto generated 02-05-2011 04:26
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'News readed counter',
	'description' => 'Counts, how often a news is shown in the singleview',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.3.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Jens Hirschfeld',
	'author_email' => 'Jens.Hirschfeld@KeepOut.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"ChangeLog";s:4:"5530";s:10:"README.txt";s:4:"9951";s:12:"ext_icon.gif";s:4:"92a0";s:17:"ext_localconf.php";s:4:"13eb";s:14:"ext_tables.php";s:4:"8758";s:14:"ext_tables.sql";s:4:"cc33";s:16:"locallang_db.xml";s:4:"d3c6";s:20:"locallang_db_old.php";s:4:"376c";s:14:"doc/manual.sxw";s:4:"bd1c";s:19:"doc/wizard_form.dat";s:4:"e61d";s:20:"doc/wizard_form.html";s:4:"7cfa";s:36:"pi1/class.tx_newsreadedcount_pi1.php";s:4:"cd53";s:17:"pi1/locallang.xml";s:4:"97fa";}',
);

?>