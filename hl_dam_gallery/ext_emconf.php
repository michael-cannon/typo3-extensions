<?php

########################################################################
# Extension Manager/Repository config file for ext: "hl_dam_gallery"
#
# Auto generated 30-12-2006 18:56
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM Photo Gallery',
	'description' => 'FE plugin, that extends existing elements (image, text /w image) to a fully featured gallery.  Can also be used as a replacement for the showimage-popup window.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.3.0',
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
	'author' => 'Heiner Lamprecht',
	'author_email' => 'typo3@heiner-lamprecht.net',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'dam' => '',
			'dam_ttcontent' => '',
			'css_styled_content' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"162b";s:10:"README.txt";s:4:"9fa9";s:26:"class.ux_tslib_content.php";s:4:"aef6";s:12:"ext_icon.gif";s:4:"0575";s:17:"ext_localconf.php";s:4:"6bdb";s:14:"ext_tables.php";s:4:"b650";s:14:"ext_tables.sql";s:4:"dc1e";s:24:"ext_typoscript_setup.txt";s:4:"8d3f";s:13:"locallang.xml";s:4:"98cf";s:16:"locallang_db.xml";s:4:"b96e";s:14:"doc/manual.sxw";s:4:"0209";s:19:"doc/wizard_form.dat";s:4:"2280";s:20:"doc/wizard_form.html";s:4:"88b4";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:33:"pi1/class.tx_hldamgallery_pi1.php";s:4:"7cc3";s:41:"pi1/class.tx_hldamgallery_pi1_wizicon.php";s:4:"515e";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"5712";s:24:"pi1/static/editorcfg.txt";s:4:"e9b2";}',
);

?>