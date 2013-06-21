<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_lifegroups=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_categories=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_interests=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_recurrences=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_days=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_semesters=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_tfclifegroups_ages=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_tfclifegroups_pi1 = < plugin.tx_tfclifegroups_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_tfclifegroups_pi1.php','_pi1','list_type',1);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_tfclifegroups_lifegroups = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_tfclifegroups_lifegroups.CMD = singleView
',43);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_tfclifegroups_pi2 = < plugin.tx_tfclifegroups_pi2.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_tfclifegroups_pi2.php','_pi2','list_type',1);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_tfclifegroups_pi3 = < plugin.tx_tfclifegroups_pi3.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_tfclifegroups_pi3.php','_pi3','list_type',1);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
    tt_content.CSS_editor.ch.tx_tfclifegroups_pi4 = < plugin.tx_tfclifegroups_pi4.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi4/class.tx_tfclifegroups_pi4.php','_pi4','list_type',0);
?>