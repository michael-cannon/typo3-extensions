<?php

########################################################################
# Extension Manager/Repository config file for ext: "mc_googlesitemap"
#
# Auto generated 15-11-2007 01:32
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Google Sitemap for Pages and Contents',
	'description' => 'XML Generator for Google\'s sitemaps , can be used for pages or contents. All sitemaps options available.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.4.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content,pages',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Maximo Cuadros [Gobernalia Global Net S.A - GrupoBBVA]',
	'author_email' => 'maximo.cuadros@grupobbva.com',
	'author_company' => 'Gobernalia Global Net S.A (GrupoBBVA)',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '3.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:14:{s:33:"class.tx_mcgooglesitemap_base.php";s:4:"151f";s:68:"class.tx_mcgooglesitemap_tt_content_tx_mcgooglesitemap_objective.php";s:4:"07b8";s:12:"ext_icon.gif";s:4:"f533";s:17:"ext_localconf.php";s:4:"76c0";s:14:"ext_tables.php";s:4:"216f";s:14:"ext_tables.sql";s:4:"2649";s:28:"ext_typoscript_constants.txt";s:4:"a341";s:24:"ext_typoscript_setup.txt";s:4:"5cae";s:16:"locallang_db.php";s:4:"6752";s:8:"test.php";s:4:"2f13";s:14:"doc/manual.sxw";s:4:"d824";s:36:"pi2/class.tx_mcgooglesitemap_pi2.php";s:4:"6715";s:36:"pi1/class.tx_mcgooglesitemap_pi1.php";s:4:"163c";s:36:"pi3/class.tx_mcgooglesitemap_pi3.php";s:4:"e8ba";}',
);

?>