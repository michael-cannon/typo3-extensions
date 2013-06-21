<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][]
=
'EXT:job_bank/classes/class.tx_jobbank_tcemainproc.php:tx_jobbank_tcemainproc';

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_jobbank_list=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg","
	tt_content.CSS_editor.ch.tx_jobbank_pi1 = < plugin.tx_jobbank_pi1.CSS_editor
",43);


t3lib_extMgm::addPItoST43($_EXTKEY,"pi1/class.tx_jobbank_pi1.php","_pi1","list_type",1);
?>
