<?PHP
if (!defined('TYPO3_MODE')) die ('Access denied!');

//Clase extendiada
$TYPO3_CONF_VARS['FE']['XCLASS']['ext/tt_news/pi/class.tx_ttnews.php'] = t3lib_extMgm::extPath('mc_podcast').'class.ux_tx_ttnews.php';

//Pi1
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','tt_content.CSS_editor.ch.tx_mcpodcast_pi1 = < plugin.tx_mcpodcast_pi1.CSS_editor',43);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_mcpodcast_pi1.php','_pi1','list_type',1);
?>
