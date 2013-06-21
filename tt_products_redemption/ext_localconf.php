<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
t3lib_extMgm::addPageTSConfig('
	# Default Page TSconfig
');
t3lib_extMgm::addUserTSConfig('
	# Default User TSconfig
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_ttproductsredemption_codes=1
');
?>