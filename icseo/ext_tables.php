<?php
// TYPO3 CVS ID: $Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('pages');
$GLOBALS['TCA']['pages']['columns']['abstract']['config']['type'] = 'input';
$GLOBALS['TCA']['pages']['columns']['abstract']['config']['size'] = 70;
$GLOBALS['TCA']['pages']['columns']['abstract']['config']['max'] = 150;
$GLOBALS['TCA']['pages']['columns']['abstract']['config']['eval'] = 'trim';
unset($GLOBALS['TCA']['pages']['columns']['abstract']['config']['cols']);
unset($GLOBALS['TCA']['pages']['columns']['abstract']['config']['rows']);

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','iConspect SEO Installer');
?>