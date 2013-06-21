<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Morten Tranberg Hansen (mth@daimi.au.dk)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'FE news' for the 'news_feedit' extension.
 *
 *
 * @author	Morten Tranberg Hansen <mth@daimi.au.dk>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('mth_feedit').'class.tx_mthfeedit.php');

class tx_newsfeedit_pi1 extends tslib_pibase {
  var $prefixId = 'tx_newsfeedit_pi1';		// Same as class name
  var $scriptRelPath = 'pi1/class.tx_newsfeedit_pi1.php';	// Path to this script relative to the extension dir.
  var $extKey = 'news_feedit';	// The extension key.
  var $pi_checkCHash = TRUE;
  var $templateFile = "";
  var $mthfeedit;

  /**
   * Main method
   */
  function main($content,$conf)	{
    $this->conf=$conf;            // Store configuration
    $this->pi_setPiVarDefaults(); // Set default piVars from TS
    $this->pi_initPIflexForm();   // Init FlexForm configuration for plugin
    $this->pi_loadLL();           // Loading language-labels

#    $GLOBALS['TSFE']->set_no_cache();

    $this->templateFile = t3lib_div::getURL(t3lib_extMgm::extPath('news_feedit').'res/template.html');

    /**** SET VARIABLES FROM FLEXFORM ****/
    include(t3lib_extMgm::extPath('mth_feedit','res/setFlexFormVariables.inc'));

    /**** SET news_feedit confs. NOTE this is extra confs (has nothing to do with mth_feedit) ****/
    $table = $this->conf['mthfeedit.']['table'];
    t3lib_div::loadTCA($table);
    if($this->conf['allowOnlyCategories']) {
      $GLOBALS['TCA']['tt_news']['columns']['category']['config']['foreign_table_where'] = 'AND '.$GLOBALS['TCA']['tt_news']['columns']['category']['config']['foreign_table'].'.uid IN ('.implode(',',t3lib_div::intExplode(',',$this->conf['allowOnlyCategories'])).')';
    }

    /**** INIT mthfeedit ****/
    $this->mthfeedit = t3lib_div::makeInstance('tx_mthfeedit');
    /* @var $mthfeedit tx_mthfeedit */
    $content = $this->mthfeedit->init($this, $this->conf['mthfeedit.']);

    return $this->pi_wrapInBaseClass($content);
  }

  function getEditMenuTemplate()
  {
    $template = $this->cObj->getSubpart($this->templateFile, '###MENU###');
    return $template;
  }
/*
  function getEditTemplate()
  {
    $template = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_EDIT###');
    $template = '<!-- ###TEMPLATE_EDIT### begin -->'.$template.'<!-- ###TEMPLATE_EDIT### end -->';
    return $template;
  }

  function getCreateTemplate()
  {
	$RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');

	$pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();
	$thisConfig = $pageTSConfig['RTE.']['default.']['FE.'];
	$specialConf = $this->getFieldSpecialConf('tt_news', 'FE[tt_news][bodytext]');

	$rteInfo['itemFormElName'] = "FE[tt_news][bodytext]";
	$rteInfo['itemFormElValue'] = '<h1>Hello world</h1>';

	$thePidValue = $GLOBALS['TSFE']->id;

	debug($_POST['FE']['tt_news'], '$_POST["FE"]["tt_news"]');

	$rteHTML = $RTEObj->drawRTE(
		$this->mthfeedit,
		'tt_news',
		'FE[tt_news][bodytext]',
		$row=array(),
		$rteInfo,
		$specialConf,
		$thisConfig,
		'text',
		'',
		$thePidValue);
	$template = $this->cObj->getSubpart($this->templateFile, '###CREATE###');
	$template = $content = $this->cObj->substituteMarkerArrayCached($template, array('###RTE_FORM###' => $rteHTML));
    return $template;
  }

  function getFieldSpecialConf($table,$fN) {
    $specialConf = array();
    $TCA = $GLOBALS["TCA"][$table];

    // Get the type value
    $type = 0; // default value
    $typeField = $TCA['ctrl']['type'];
    $uid = t3lib_div::_GET('rU');
    if($typeField && $uid) { // get the type from the database else use default value
      $rec = $GLOBALS['TSFE']->sys_page->getRawRecord($table,$uid);
      $type = intval($rec[$typeField]);
    }

    // get the special configurations and check for an existing richtext configuration
    $showitem = $TCA['types'][$type]['showitem'] ? explode(',',$TCA['types'][$type]['showitem']) : explode(',',$TCA['types'][1]['showitem']); // if ['types'][$type] we should try with ['types'][1] according to TCA doc
    foreach((array)$showitem as $fieldConfig) {
      $fC = explode(';',$fieldConfig);
      if(trim($fC[0])==$fN) {                      // if field is $fN
	foreach(explode(':',$fC[3]) as $sC) {
	  if(substr($sC,0,8)=='richtext') {        // if there is a richtext configuration we found what we were looking for
	    $buttons = substr(trim($sC),9,strlen(trim($sC))-10);
	    $specialConf['richtext']['parameters'] = t3lib_div::trimExplode('|',$buttons);

	  } else if(substr($sC,0,13)=='rte_transform') {
	    $transConf = substr(trim($sC),14,strlen(trim($sC))-15);
	    $specialConf['rte_transform']['parameters'] = t3lib_div::trimExplode('|',$transConf);
	  }
	}
      }
    }
    return $specialConf;
  }*/
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/news_feedit/pi1/class.tx_newsfeedit_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/news_feedit/pi1/class.tx_newsfeedit_pi1.php']);
}

    /*
    $items = array();
    $items["title"]["label"] = "Title";
    $items["title"]["helptext"] = "The helptext";
    $items["title"]["error_msg"] = "The error msg";
    $items["image"]["label"] = "Images";
    $items["image"]["helptext"] = "The helptext";
    $items["image"]["error_msg"] = "The error msg";
    $items["category"]["label"] = "Category";
    $items["category"]["helptext"] = "The helptext";
    $items["category"]["error_msg"] = "The error msg";

    $form = new tx_cwtfeedit_pi1("tt_news", $items, 147, $GLOBALS['TSFE']->fe_user->user['uid'], $back_id, $back_values, $this, 'news_feedit');

    $content = "<table>";
    $content.= $form->getFormHeader();
    $content.= $form->getElement("title");
    $content.= $form->getElement("image");
    $content.= $form->getElement("category");
    $content.= $form->getFormFooter();
    $content.= "</table>";*/

?>