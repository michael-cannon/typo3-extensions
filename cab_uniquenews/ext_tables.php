<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addService($_EXTKEY,  '' /* sv type */,  'tx_cabuniquenews_sv1' /* sv key */,
		array(

			'title' => 'Unique news displayer',
			'description' => '',

			'subtype' => '',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_cabuniquenews_sv1.php',
			'className' => 'tx_cabuniquenews_sv1',
		)
	);
    
    
if (TYPO3_MODE=='BE')    {    
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('UNIQUE_LIST', 'UNIQUE_LIST');
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['what_to_display'][] = array('UNIQUE_LATEST', 'UNIQUE_LATEST');

}    
    
?>