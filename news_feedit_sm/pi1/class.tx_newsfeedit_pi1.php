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
  
  /**
   * Main method
   */
  function main($content,$conf)	{
    $this->conf=$conf;            // Store configuration
    $this->pi_setPiVarDefaults(); // Set default piVars from TS
    $this->pi_initPIflexForm();   // Init FlexForm configuration for plugin
    $this->pi_loadLL();           // Loading language-labels

#    $GLOBALS['TSFE']->set_no_cache();  

    /**** SET VARIABLES FROM FLEXFORM ****/
    include(t3lib_extMgm::extPath('mth_feedit','res/setFlexFormVariables.inc'));
    
    /**** SET news_feedit confs. NOTE this is extra confs (has nothing to do with mth_feedit) ****/
    $table = $this->conf['mthfeedit.']['table'];
    t3lib_div::loadTCA($table);
    if($this->conf['allowOnlyCategories']) {
      $GLOBALS['TCA']['tt_news']['columns']['category']['config']['foreign_table_where'] = 'AND '.$GLOBALS['TCA']['tt_news']['columns']['category']['config']['foreign_table'].'.uid IN ('.implode(',',t3lib_div::intExplode(',',$this->conf['allowOnlyCategories'])).')';
    }

    /**** INIT mthfeedit ****/
    $mthfeedit = t3lib_div::makeInstance('tx_mthfeedit');
    $content = $mthfeedit->init($this,$this->conf['mthfeedit.']);
   
    return $this->pi_wrapInBaseClass($content);
  }
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