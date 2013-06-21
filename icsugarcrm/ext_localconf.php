<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_icsugarcrm_pi1.php','_pi1','list_type',1);
?>
