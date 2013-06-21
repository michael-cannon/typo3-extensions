<?php

########################################################################
# Extension Manager/Repository config file for ext: "da_newsletter_subscription"
#
# Auto generated 03-11-2006 00:06
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Newsletter Subscription Module',
	'description' => 'A newsletter subscription module which allows you to create any number of categories and then allow frontend users to check off which categories they want to receive. From the backend you can download a CSV list of recipients for sending the mails in any application you like.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Kasper Skårhøj',
	'author_email' => 'kasper@typo3.com',
	'author_company' => 'Curby Soft Multimedia',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.1',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"b74c";s:14:"ext_tables.php";s:4:"e04e";s:14:"ext_tables.sql";s:4:"4423";s:40:"icon_tx_danewslettersubscription_cat.gif";s:4:"401f";s:16:"locallang_db.php";s:4:"a02c";s:7:"tca.php";s:4:"0047";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"eafa";s:14:"mod1/index.php";s:4:"e104";s:18:"mod1/locallang.php";s:4:"670f";s:22:"mod1/locallang_mod.php";s:4:"254e";s:19:"mod1/moduleicon.gif";s:4:"8074";s:45:"pi1/class.tx_danewslettersubscription_pi1.php";s:4:"18e6";s:17:"pi1/locallang.php";s:4:"b9b7";s:16:"pi1/template.gif";s:4:"4a1f";s:14:"doc/manual.sxw";s:4:"df89";s:20:"static/editorcfg.txt";s:4:"b784";s:16:"static/setup.txt";s:25:"pi2/class.tx_danewslettersubscription_pi2.php";s:4:"21fe";s:4:"d7fb"s:4:"afbe";}',
);

?>