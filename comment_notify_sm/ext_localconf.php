<?php
#$Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:19 peimic.comprock Exp $
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_commentnotify_pi1 = < plugin.tx_commentnotify_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_commentnotify_pi1.php','_pi1','list_type',0);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
    tt_content.CSS_editor.ch.tx_commentnotify_pi2 = < plugin.tx_commentnotify_pi2.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_commentnotify_pi2.php','_pi2','list_type',0);


?>