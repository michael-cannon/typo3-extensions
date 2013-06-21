<?php

########################################################################
# Extension Manager/Repository config file for ext: "mth_feedit"
#
# Auto generated 21-09-2006 04:26
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'FE Form Edit API',
	'description' => 'This is a Frontend API for editing TYPO3 records in the frontend like you do it in the backend. All you have to do is to define the table and witch fields of the table it is possible to edit in the frontend, and the mth_feedit will generate the form for you with RTE (rtehtmlarea version 1.3.1 or above). This API is build on top og fe_adminLib. For working examples of how to use this API in your extension see news_feedit, joboffers_feedit and daimi_event.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.4.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Morten Tranberg Hansen',
	'author_email' => 'mth@daimi.au.dk',
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
	'_md5_values_when_last_written' => 'a:18:{s:9:"ChangeLog";s:4:"8d16";s:10:"README.txt";s:4:"ee2d";s:22:"class.tx_mthfeedit.php";s:4:"8873";s:37:"class.tx_mthfeedit.php.backup_no_tabs";s:4:"07fa";s:23:"class.tx_mthfeedit.php~";s:4:"4719";s:15:"ext_emconf.php~";s:4:"7da8";s:12:"ext_icon.gif";s:4:"1201";s:13:"locallang.php";s:4:"ddce";s:14:"locallang.php~";s:4:"b49a";s:17:"locallang_tca.php";s:4:"08c7";s:18:"locallang_tca.php~";s:4:"44a7";s:19:"res/flexform_ds.xml";s:4:"fe23";s:20:"res/flexform_ds.xml~";s:4:"ece4";s:28:"res/setFlexFormVariables.inc";s:4:"f194";s:29:"res/setFlexFormVariables.inc~";s:4:"749c";s:14:"doc/manual.sxw";s:4:"a175";s:19:"doc/wizard_form.dat";s:4:"b9ec";s:20:"doc/wizard_form.html";s:4:"536e";}',
);

?>