<?php

########################################################################
# Extension Manager/Repository config file for ext: "aqnewsmeta"
#
# Auto generated 15-04-2009 00:56
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Acqal News Meta Helper',
	'description' => 'This extension updates the newsSubheader register if no teaser is available.

This is helpful for SEO purposes where each news entry should have a META description.',
	'category' => 'fe',
	'author' => 'Michael Cannon',
	'author_email' => 'michael@peimic.com',
	'shy' => '',
	'dependencies' => 'tt_news',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'Peimic.com',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:9:"CHANGELOG";s:4:"bebb";s:10:"README.txt";s:4:"ee2d";s:23:"class.tx_aqnewsmeta.php";s:4:"b542";s:12:"ext_icon.gif";s:4:"5078";s:17:"ext_localconf.php";s:4:"2eaf";s:14:"ext_tables.php";s:4:"2f20";s:31:"static/news__meta/constants.txt";s:4:"12dc";s:27:"static/news__meta/setup.txt";s:4:"d2b5";s:14:"doc/manual.sxw";s:4:"dccf";s:19:"doc/wizard_form.dat";s:4:"f6eb";s:20:"doc/wizard_form.html";s:4:"0097";}',
	'suggests' => array(
	),
);

?>