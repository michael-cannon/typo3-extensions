<?php
  /***************************************************************
   *  Copyright notice
   *
   *  (c) 2005 Norman Seibert (seibert@entios.de)
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
   * Plugin 'SugarCRM Test' for the 'eu_sugarcrm' extension.
   *
   * @author	Christian Lerrahn (Cerebrum (Aust) Pty. Ltd.) <christian.lerrahn@cerebrum.com.au>
   * @author	Norman Seibert <seibert@entios.de>
   */


require_once(PATH_tslib.'class.tslib_pibase.php');
include_once(t3lib_extMgm::extPath('eu_sugarcrm').'/class.tx_eusugarcrm_base.php');

class tx_eusugarcrm_pi1 extends tslib_pibase {
  var $prefixId = 'tx_eusugarcrm_pi1';		// Same as class name
  var $scriptRelPath = 'pi1/class.tx_eusugarcrm_pi1.php';	// Path to this script relative to the extension dir.
  var $extKey = 'eu_sugarcrm';	// The extension key.
	
  /**
   * [Put your description here]
   */
  function main($content,$conf)	{
    $this->conf=$conf;
    $this->pi_setPiVarDefaults();
    $this->pi_loadLL();
    $this->pi_initPIflexForm(); // init flexforms to $this->cObj->data['pi_flexform']
    $this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
    //		$CODE = $this->cObj->data['select_key'];
    $testparam = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'testparam', 'sDEF');
    $what_to_display = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		
    if (is_object($serviceObj = new tx_eusugarcrm_base())) {
      if ($testparam || !$what_to_display)
	$content = '<b>'.$serviceObj->test($testparam).'</b>';
      if (t3lib_div::inList($what_to_display,'SERVER_VERSION'))
	$content = '<p>The server version is '.$serviceObj->get_server_version().' ('.$serviceObj->get_sugar_flavor().').</p>';
      if (t3lib_div::inList($what_to_display,'LOG_IN') || t3lib_div::inList($what_to_display,'LIST_USERS') || t3lib_div::inList($what_to_display,'LIST_MODULES') || t3lib_div::inList($what_to_display,'LIST_USERS') || t3lib_div::inList($what_to_display,'LIST_MODULE_DETAILS')) {
	if ($sessionID = $serviceObj->login()) {
	  $content .= '<p>Logged in to SugarCRM successfully!</p>';
	  if (t3lib_div::inList($what_to_display,'LIST_USERS')) {
	    $arrUsers = $serviceObj->user_list();
	    $content .= '<p>SugarCRM users:</p>';
	    $content .= '<table border=1>';
	    while(list(, $user) = each($arrUsers)) {
	      $content .= '<tr><td colspan=2><b>'.$user['user_name'].'</b></td></tr>';
	      while(list($key, $val) = each($user)) {
		$content .= '<tr><td>'.$key.':</td><td>'.$val.'</td></tr>';
	      }
	      $content .= '</tr>';
	    }
	    $content .= '</table>';
	  }
	  if (t3lib_div::inList($what_to_display,'LIST_MODULES') || t3lib_div::inList($what_to_display,'LIST_MODULE_DETAILS')) {
	    $arrModules = $serviceObj->module_list();
	    $content .= '<p>SugarCRM modules:</p>';
	    $content .= '<table border=1>';
	    while(list(, $mod) = each($arrModules['modules'])) {
	      $content .= '<tr><td colspan=2><b>'.$mod.'</b></td></tr>';
	      if (t3lib_div::inList($what_to_display,'LIST_MODULE_DETAILS')) {
		$arrFields = $serviceObj->field_list($mod);
		if ($arrFields) {
		  $content .= '<tr><td><strong>name</strong></td>
								<td><strong>type</strong></td>
								<td><strong>required?</strong></td>
								<td><strong>options</strong></td>
							</tr>';
		  while(list(, $field) = each($arrFields['module_fields'])) {
		    $content .= '<tr><td>'.$field['name'].':</td>
								<td>'.$field['type'].'</td>
								<td>'.$field['required'].'</td>
								<td>';
		    while(list(, $opt) = each($field['options'])) {
		      $content .= '<div>'.$opt['value'].'</div>';
		    }
		    $content .= '</td>
							</tr>';
		  }
		}
	      }
	    }
	    $content .= '</table>';
	  }
	}
	else {
	  $content .= '<p>Failed to log in to SugarCRM!</p>';
	}
      }
      $serviceObj->logout();
    }
    return $this->pi_wrapInBaseClass($content);
  }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/eu_sugarcrm/pi1/class.tx_eusugarcrm_pi1.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/eu_sugarcrm/pi1/class.tx_eusugarcrm_pi1.php']);
 }

?>
