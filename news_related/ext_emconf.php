<?php

########################################################################
# Extension Manager/Repository config file for ext: "news_related"
#
# Auto generated 06-09-2008 20:54
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Automated Related News Display',
	'description' => 'Display titles of related news articles based upon category of originating news item. Limits imposed by category versus totals articles so that each category has opportunity to have news item displayed versus being cutoff.',
	'category' => 'fe',
	'shy' => 0,
	'dependencies' => 'tt_news',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Michael Cannon',
	'author_email' => 'michael@peimic.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.0',
	'_md5_values_when_last_written' => 'a:13:{s:9:"CHANGELOG";s:4:"5ab8";s:24:"class.tx_newsrelated.php";s:4:"faf8";s:12:"ext_icon.gif";s:4:"b328";s:17:"ext_localconf.php";s:4:"94e2";s:14:"ext_tables.php";s:4:"aa00";s:14:"ext_tables.sql";s:4:"c129";s:28:"ext_typoscript_constants.txt";s:4:"98f5";s:24:"ext_typoscript_setup.txt";s:4:"d6f4";s:13:"locallang.php";s:4:"7097";s:16:"locallang_db.php";s:4:"d410";s:17:"news_related.tmpl";s:4:"b00d";s:19:"doc/wizard_form.dat";s:4:"646e";s:20:"doc/wizard_form.html";s:4:"c74d";}',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '',
			'php' => '3.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>