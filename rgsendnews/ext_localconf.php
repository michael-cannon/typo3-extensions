<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
    tt_content.CSS_editor.ch.tx_rgsendnews_pi1 = < plugin.tx_rgsendnews_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_rgsendnews_pi1.php','_pi1','list_type',0);

// hook for tt_news
if (TYPO3_MODE == 'FE')    {
    require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_rgsendnews_fe.php');
}
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]   = 'tx_rgsendnews_fe';

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_rgsendnews_ajax'] = 'EXT:rgsendnews/class.tx_rgsendnews_ajax.php';


?>
