<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbdiningguide_price=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbdiningguide_cuisine=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbdiningguide_meals=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbdiningguide_neighborhood=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_cbdiningguide_specialty=1
');
?>