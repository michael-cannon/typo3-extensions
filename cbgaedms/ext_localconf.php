<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbgaedms_doctype=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbgaedms_doc=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbgaedms_docversion=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbgaedms_agency=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbgaedms_silo=1
');
t3lib_extMgm::addUserTSConfig('
    options.saveDocNew.tx_cbgaedms_reports=1
');
require_once(t3lib_extMgm::extPath('div') . 'class.tx_div.php');
if(TYPO3_MODE == 'FE') tx_div::autoLoadAll($_EXTKEY);
?>
