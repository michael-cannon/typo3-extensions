<?php

// TYPO3 CVS ID: $Id: ext_tables.php,v 1.1.1.1 2010/04/15 10:03:12 peimic.comprock Exp $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','Peimic Site Starter');

// thanks to TYPO3 basics author Sven Burkert
$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cbstarter']);
$showFields = array();

if ($conf['shortcut.']['showStartStopField']) {
    $showFields[] = 'starttime';
    $showFields[] = 'endtime';
}

if ($conf['shortcut.']['showAccessField']) {
    $showFields[] = 'fe_group';
}

if (count($showFields)) {
    t3lib_extMgm::addToAllTCAtypes('pages', implode(',', $showFields) . ';;;;',
'4', 'after:shortcut_mode');
}

// let non-admins edit TypoScript sys_templates
t3lib_div::loadTCA('sys_template');            
$TCA['sys_template']['ctrl']['adminOnly'] = 0;

// increase number of workspace related backend users and backend usergroups
t3lib_div::loadTCA('sys_workspace');
$TCA['sys_workspace']['columns']['adminusers']['config']['maxitems'] = 100;
$TCA['sys_workspace']['columns']['members']['config']['maxitems'] = 100;
$TCA['sys_workspace']['columns']['reviewers']['config']['maxitems'] = 100;

// increase number of workspace related db and file mount points
$TCA['sys_workspace']['columns']['db_mountpoints']['config']['maxitems'] = 100;
$TCA['sys_workspace']['columns']['file_mountpoints']['config']['maxitems'] = 100;

// increase image size allowance and counts
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['columns']['image']['config']['max_size'] = 10000;

t3lib_div::loadTCA('tt_news');
$TCA['tt_news']['columns']['image']['config']['max_size'] = 10000;
$TCA['tt_news']['columns']['image']['config']['maxitems'] = 100;

?>