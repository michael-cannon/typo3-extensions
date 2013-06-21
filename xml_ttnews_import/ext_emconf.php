<?php

########################################################################
# Extension Manager/Repository config file for ext: "xml_ttnews_import"
#
# Auto generated 12-07-2007 14:43
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Import an RSS feed into tt_news',
	'description' => '',
	'category' => 'be',
	'shy' => 0,
	'dependencies' => 'tt_news,cc_rdf_news_import',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'cm1',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Schopfer Olivier',
	'author_email' => 'ops@wcc-coe.org',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.3.2',
	'_md5_values_when_last_written' => 'a:37:{s:9:"ChangeLog";s:4:"d00c";s:10:"README.txt";s:4:"ee2d";s:32:"class.tx_xmlttnewsimport_cm1.php";s:4:"6e8e";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"d0ed";s:14:"ext_tables.php";s:4:"690b";s:14:"ext_tables.sql";s:4:"cc97";s:13:"locallang.php";s:4:"3d32";s:16:"locallang_db.php";s:4:"e828";s:11:"CVS/Entries";s:4:"dda3";s:14:"CVS/Repository";s:4:"d082";s:8:"CVS/Root";s:4:"4932";s:20:"cm1/.#dbinfo.php.1.1";s:4:"6ba2";s:19:"cm1/.#index.php.1.9";s:4:"6403";s:31:"cm1/AssociationHelper.class.php";s:4:"95e6";s:43:"cm1/NewsfeedItemsCategoriesHelper.class.php";s:4:"7b7f";s:32:"cm1/XMLImportUtilities.class.php";s:4:"029d";s:13:"cm1/clear.gif";s:4:"cc11";s:15:"cm1/cm_icon.gif";s:4:"8074";s:12:"cm1/conf.php";s:4:"7c5c";s:14:"cm1/dbinfo.php";s:4:"86bc";s:16:"cm1/dbinfo.php.0";s:4:"6849";s:16:"cm1/dbinfo.php.1";s:4:"74a7";s:16:"cm1/dbinfo.php.3";s:4:"74a7";s:14:"cm1/error_log0";s:4:"c55d";s:14:"cm1/error_log1";s:4:"bcf3";s:13:"cm1/index.php";s:4:"4190";s:17:"cm1/locallang.php";s:4:"f524";s:15:"cm1/CVS/Entries";s:4:"f969";s:18:"cm1/CVS/Repository";s:4:"8ec2";s:12:"cm1/CVS/Root";s:4:"4932";s:14:"doc/manual.sxw";s:4:"b5ed";s:19:"doc/wizard_form.dat";s:4:"8656";s:20:"doc/wizard_form.html";s:4:"db92";s:15:"doc/CVS/Entries";s:4:"e880";s:18:"doc/CVS/Repository";s:4:"0568";s:12:"doc/CVS/Root";s:4:"4932";}',
	'constraints' => array(
		'depends' => array(
			'tt_news' => '',
			'cc_rdf_news_import' => '',
			'php' => '3.0.0-',
			'typo3' => '3.5.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>