<?php

// TYPO3 CVS ID: $Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:36 peimic.comprock Exp $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','iConspect Baseline Installer');

// let non-admins edit TypoScript sys_templates
t3lib_div::loadTCA('sys_template');            
$TCA['sys_template']['ctrl']['adminOnly'] = 0;

// increase number of workspace related backend users and backend usergroups
t3lib_div::loadTCA('sys_workspace');
$TCA['sys_workspace']['columns']['adminusers']['config']['maxitems'] = 999;
$TCA['sys_workspace']['columns']['members']['config']['maxitems'] = 999;
$TCA['sys_workspace']['columns']['reviewers']['config']['maxitems'] = 999;

// increase number of workspace related db and file mount points
$TCA['sys_workspace']['columns']['db_mountpoints']['config']['maxitems'] = 999;
$TCA['sys_workspace']['columns']['file_mountpoints']['config']['maxitems'] = 999;

?>