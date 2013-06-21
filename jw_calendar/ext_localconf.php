<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_jwcalendar_categories=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_jwcalendar_events=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_jwcalendar_pi1 = < plugin.tx_jwcalendar_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_jwcalendar_pi1.php','_pi1','list_type',1);
//"list_type",1 ist user
//"list_type",0 ist user_int 

$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_jwcalendar_events'][0] =
                array('fList' => 'begin,end,title,teaser,category',
                      'icon'  => 1,
                );

?>