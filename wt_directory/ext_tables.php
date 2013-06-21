<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';


t3lib_extMgm::addPlugin(array('LLL:EXT:wt_directory/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:wt_directory/be/flexform_ds_pi1.xml');


if (TYPO3_MODE=="BE") {
	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_wtdirectory_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_wtdirectory_pi1_wizicon.php';
	include_once(t3lib_extMgm::extPath("wt_directory")."be/class.user_be_fields.php");
	include_once(t3lib_extMgm::extPath("wt_directory")."be/class.user_be_abcfields.php");
	include_once(t3lib_extMgm::extPath("wt_directory")."be/class.user_be_googlemapmsg.php");
	include_once(t3lib_extMgm::extPath("tt_address")."class.tx_ttaddress_treeview.php");
}
?>