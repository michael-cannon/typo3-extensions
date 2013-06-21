<?php
if (!defined ('TYPO3_MODE')) die('Access denied.');

$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_htmlmail.php']=t3lib_extMgm::extPath($_EXTKEY).'class.ux_t3lib_htlmail.php';

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['smtpHost'] = $_EXTCONF['smtpHost'] ? $_EXTCONF['smtpHost'] : 'localhost';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['smtpPort'] = $_EXTCONF['smtpPort'] ? $_EXTCONF['smtpPort'] : '25';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['smtpAuth'] = $_EXTCONF['smtpAuth'] ? $_EXTCONF['smtpAuth'] : 0;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['smtpUser'] = $_EXTCONF['smtpUser'] ? $_EXTCONF['smtpUser'] : '';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['smtpPass'] = $_EXTCONF['smtpPass'] ? $_EXTCONF['smtpPass'] : '';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['smtpPersist'] = $_EXTCONF['smtpPersist'] ? $_EXTCONF['smtpPersist'] : '';
?>