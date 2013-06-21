<?php

########################################################################
# Extension Manager/Repository config file for ext: "powermail_cond"
#
# Auto generated 16-04-2010 05:48
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Powermail Conditions',
	'description' => 'Add Javascript Conditions to powermail fields and fieldsets - Show/Hide Fields/Fieldsets',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.5.1',
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
	'author' => 'Alex Kellner',
	'author_email' => 'alexander.kellner@einpraegsam.net',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'powermail' => '1.4.12-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:21:"ext_conf_template.txt";s:4:"2558";s:12:"ext_icon.gif";s:4:"014a";s:17:"ext_localconf.php";s:4:"33ba";s:14:"ext_tables.php";s:4:"8dc8";s:14:"ext_tables.sql";s:4:"721c";s:36:"icon_tx_powermailcond_conditions.gif";s:4:"bd1e";s:31:"icon_tx_powermailcond_rules.gif";s:4:"a2f9";s:16:"locallang_db.xml";s:4:"a5cb";s:7:"tca.php";s:4:"8579";s:14:"doc/manual.sxw";s:4:"8ac2";s:43:"lib/class.tx_powermailcond_confirmation.php";s:4:"b170";s:37:"lib/class.tx_powermailcond_fields.php";s:4:"9ea2";s:40:"lib/class.tx_powermailcond_fieldsets.php";s:4:"1bf7";s:20:"js/powermail_cond.js";s:4:"3335";s:62:"be/class.tx_powermailcond_tx_powermailcond_rules_fieldname.php";s:4:"8b5e";}',
);

?>