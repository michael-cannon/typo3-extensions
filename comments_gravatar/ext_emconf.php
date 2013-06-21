<?php

########################################################################
# Extension Manager/Repository config file for ext: "comments_gravatar"
#
# Auto generated 03-07-2009 19:38
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Gravatars for Comments',
	'description' => 'Enable the viewing of Gravatar icons with comment records.',
	'category' => 'fe',
	'author' => 'Michael Cannon',
	'author_email' => 'michael@peimic.com',
	'shy' => '',
	'dependencies' => 'comments',
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
	'version' => '1.1.0',
	'constraints' => array(
		'depends' => array(
			'comments' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:13:{s:9:"CHANGELOG";s:4:"9d3b";s:10:"README.txt";s:4:"a4c5";s:29:"class.tx_commentsgravatar.php";s:4:"94c7";s:12:"ext_icon.gif";s:4:"5078";s:17:"ext_localconf.php";s:4:"ac59";s:14:"ext_tables.php";s:4:"3ddf";s:14:"doc/manual.sxw";s:4:"2df5";s:19:"doc/wizard_form.dat";s:4:"70ac";s:20:"doc/wizard_form.html";s:4:"26c8";s:18:"res/nopic_50_f.jpg";s:4:"577c";s:21:"res/pi1_template.html";s:4:"6b7a";s:39:"static/comments__gravatar/constants.txt";s:4:"077a";s:35:"static/comments__gravatar/setup.txt";s:4:"237b";}',
	'suggests' => array(
	),
);

?>