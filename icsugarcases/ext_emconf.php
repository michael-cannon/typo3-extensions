<?php

########################################################################
# Extension Manager/Repository config file for ext: "icsugarcases"
#
# Auto generated 12-11-2009 03:23
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'iConspect SugarCRM Cases Portal',
	'description' => 'SugarCRM case management portal for TYPO3 based upon the like named Joomla module.',
	'category' => 'plugin',
	'author' => 'Michael Cannon',
	'author_email' => 'michael@peimic.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'pagebrowse' => ''
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:68:{s:9:"ChangeLog";s:4:"6883";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"5078";s:17:"ext_localconf.php";s:4:"4d6c";s:14:"ext_tables.php";s:4:"ca27";s:14:"ext_tables.sql";s:4:"81b5";s:51:"icon_tx_icsugarcases_sugar_portal_configuration.gif";s:4:"475a";s:13:"locallang.xml";s:4:"f7a4";s:16:"locallang_db.xml";s:4:"eb08";s:7:"tca.php";s:4:"2967";s:19:"doc/wizard_form.dat";s:4:"4b5e";s:20:"doc/wizard_form.html";s:4:"4e17";s:42:"tmp/com_sugarcases_beta/com_sugarcases.xml";s:4:"2173";s:34:"tmp/com_sugarcases_beta/index.html";s:4:"1c7b";s:46:"tmp/com_sugarcases_beta/install.sugarcases.php";s:4:"bf82";s:44:"tmp/com_sugarcases_beta/sugarcases.class.php";s:4:"b913";s:43:"tmp/com_sugarcases_beta/sugarcases.html.php";s:4:"e6a7";s:38:"tmp/com_sugarcases_beta/sugarcases.php";s:4:"01cd";s:43:"tmp/com_sugarcases_beta/sugarportal.inc.php";s:4:"4f12";s:45:"tmp/com_sugarcases_beta/sugarapp/sugarApp.php";s:4:"773d";s:48:"tmp/com_sugarcases_beta/sugarapp/sugarAppBug.php";s:4:"f522";s:49:"tmp/com_sugarcases_beta/sugarapp/sugarAppCase.php";s:4:"348e";s:46:"tmp/com_sugarcases_beta/sugarapp/sugarHTML.php";s:4:"5239";s:55:"tmp/com_sugarcases_beta/admin/admin.sugarcases.html.php";s:4:"8027";s:50:"tmp/com_sugarcases_beta/admin/admin.sugarcases.php";s:4:"5731";s:40:"tmp/com_sugarcases_beta/admin/index.html";s:4:"1c7b";s:55:"tmp/com_sugarcases_beta/admin/install.mysql.nonutf8.sql";s:4:"34bb";s:57:"tmp/com_sugarcases_beta/admin/toolbar.sugarcases.html.php";s:4:"86ef";s:52:"tmp/com_sugarcases_beta/admin/toolbar.sugarcases.php";s:4:"15b6";s:57:"tmp/com_sugarcases_beta/admin/uninstall.mysql.nonutf8.sql";s:4:"49d7";s:49:"tmp/com_sugarcases_beta/sugarinc/sugarAccount.php";s:4:"8238";s:45:"tmp/com_sugarcases_beta/sugarinc/sugarBug.php";s:4:"7f3d";s:46:"tmp/com_sugarcases_beta/sugarinc/sugarCase.php";s:4:"ac92";s:55:"tmp/com_sugarcases_beta/sugarinc/sugarCommunication.php";s:4:"89dd";s:55:"tmp/com_sugarcases_beta/sugarinc/sugarConfiguration.php";s:4:"f19d";s:49:"tmp/com_sugarcases_beta/sugarinc/sugarContact.php";s:4:"0342";s:44:"tmp/com_sugarcases_beta/sugarinc/sugarDB.php";s:4:"4539";s:50:"tmp/com_sugarcases_beta/sugarinc/sugarDownload.php";s:4:"9feb";s:47:"tmp/com_sugarcases_beta/sugarinc/sugarError.php";s:4:"1560";s:47:"tmp/com_sugarcases_beta/sugarinc/sugarLeads.php";s:4:"74f1";s:46:"tmp/com_sugarcases_beta/sugarinc/sugarNote.php";s:4:"c2f4";s:46:"tmp/com_sugarcases_beta/sugarinc/sugarUser.php";s:4:"26a7";s:47:"tmp/com_sugarcases_beta/sugarinc/sugarUtils.php";s:4:"db57";s:41:"static/sugarcrm_case_portal/constants.txt";s:4:"7472";s:37:"static/sugarcrm_case_portal/setup.txt";s:4:"540f";s:24:"res/sugarcases.class.php";s:4:"ce5b";s:25:"res/sugarapp/sugarApp.php";s:4:"60ba";s:28:"res/sugarapp/sugarAppBug.php";s:4:"e0d2";s:29:"res/sugarapp/sugarAppCase.php";s:4:"e083";s:26:"res/sugarapp/sugarHTML.php";s:4:"9e18";s:29:"res/sugarinc/sugarAccount.php";s:4:"ed01";s:25:"res/sugarinc/sugarBug.php";s:4:"da63";s:26:"res/sugarinc/sugarCase.php";s:4:"0dff";s:35:"res/sugarinc/sugarCommunication.php";s:4:"c6db";s:35:"res/sugarinc/sugarConfiguration.php";s:4:"0694";s:29:"res/sugarinc/sugarContact.php";s:4:"2d7f";s:24:"res/sugarinc/sugarDB.php";s:4:"4539";s:30:"res/sugarinc/sugarDownload.php";s:4:"9feb";s:27:"res/sugarinc/sugarError.php";s:4:"8093";s:27:"res/sugarinc/sugarLeads.php";s:4:"46a5";s:26:"res/sugarinc/sugarNote.php";s:4:"e939";s:26:"res/sugarinc/sugarUser.php";s:4:"3cee";s:27:"res/sugarinc/sugarUtils.php";s:4:"db57";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:33:"pi1/class.tx_icsugarcases_pi1.php";s:4:"1fc7";s:41:"pi1/class.tx_icsugarcases_pi1_wizicon.php";s:4:"c147";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"5c05";}',
	'suggests' => array(
	),
);

?>