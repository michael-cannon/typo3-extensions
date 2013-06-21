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
   * Service 'sugarCRM Integration' for the 'eu_sugarcrm' extension.
   *
   * @author	Christian Lerrahn (Cerebrum (Aust) Pty. Ltd.) <christian.lerrahn@cerebrum.com.au>
   * @author	Norman Seibert <seibert@entios.de>
   */

require_once(t3lib_extMgm::extPath('eim2nusoap') .'nusoap/lib/nusoap.php');

class tx_eusugarcrm_base {
  var $prefixId = 'tx_eusugarcrm_base';		// Same as class name
  var $scriptRelPath = 'class.tx_eusugarcrm_base.php';	// Path to this script relative to the extension dir.
  var $extKey = 'eu_sugarcrm';	// The extension key.
	
  var $conf;
  var $sessionID;
	
  /**
   * [Put your description here]
   */
  function tx_eusugarcrm_base()	{
    global $TYPO3_CONF_VARS;
		
    $this->conf = $TYPO3_CONF_VARS['SVCONF']['sugarCRM']['tx_eusugarcrm_sv1'];
		
    if ($this->conf['useProxy']) {
      $proxyServer = $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer'];
      if ($proxyServer) {
	$arrProxyServer = split(':', $proxyServer);
	$proxyHost = $arrProxyServer[1];
	$proxyHost = str_replace('//', '', $proxyHost);
	$proxyPort = $arrProxyServer[2];
	$proxyPort = str_replace('/', '', $proxyPort);
      }
      $proxyCredentials = $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyUserPass'];
      if ($proxyCredentials) {
	$arrproxyCredentials = split(':', $proxyCredentials);
	$proxyUser = $arrproxyCredentials[0];
	$proxyPass = $arrproxyCredentials[1];
      }
    }
    $this->nusoapclient = new nusoap_client($this->conf['URL'].'?wsdl', true, $proxyHost, $proxyPort, $proxyUser, $proxyPass);
	
    return $available;
  }
	
  function test($teststring) {
    $parameters = array($teststring);
    $result = $this->nusoapclient->call('test', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      $content = 'Result: '.$result;
    } else {
      $content = 'Error: '.$err;
    }
    return $content;
  }

  function get_server_version() {
    $result = $this->nusoapclient->call('get_server_version');
    if (!$err = $this->nusoapclient->getError()) {
      $content = $result;
    } else {
      $content = 'ERROR: '.$err;
    }
    return $content;
  }

  function get_sugar_flavor() {
    $result = $this->nusoapclient->call('get_sugar_flavor');
    if (!$err = $this->nusoapclient->getError()) {
      $content = $result;
    } else {
      $content = 'ERROR: '.$err;
    }
    return $content;
  }

  function login() {
    $parameters = array(
			'user_auth' => array(
					     'user_name' => $this->conf['userID'],
					     'password' => md5($this->conf['userPW']),
					     'version' => '.01'
					     ),
			'application_name' => $this->extKey
			);
    $result = $this->nusoapclient->call('login', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      if ($result['error']['number']) {
	debug($result['error']);
	return false;
      } else {
	$this->sessionID = $result['id'];
	return true;
      }
    } else {
      debug($err);
      return false;
    }
  }
	
  function logout() {
    $parameters = array(
			'session' => $this->sessionID
			);
    $result = $this->nusoapclient->call('logout', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      if ($result['error']['number']) {
	debug($result['error']);
	return false;
      } else {
	$this->sessionID = null;
	return true;
      }
    } else {
      debug($err);
      return false;
    }
  }
	
  function contact_by_email($email) {
    $parameters = array(
			'user_name' => $this->conf['userID'],
			'password' => md5($this->conf['userPW']),
			'search_string' => $email,
			'modules' => array('Leads', 'Contacts'),
			'0',
			'10'
			);
    $result = $this->nusoapclient->call('search_by_module', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function create_lead($firstname, $name, $email) {
    $parameters = array(
			'user_name' => $this->conf['userID'],
			'password' => md5($this->conf['userPW']),
			'first_name' => $firstname,
			'last_name' => $name,
			'email_address' => $email
			);
    $result = $this->nusoapclient->call('create_lead', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function create_account($name, $phone, $website) {
    $parameters = array(
			'user_name' => $this->conf['userID'],
			'password' => md5($this->conf['userPW']),
			'name' => $name,
			'phone' => $phone,
			'website' => $website
			);
    $result = $this->nusoapclient->call('create_account', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function create_case($name) {
    $parameters = array(
			'user_name' => $this->conf['userID'],
			'password' => md5($this->conf['userPW']),
			'name' => $name
			);
    $result = $this->nusoapclient->call('create_case', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function create_note($name) {
    $parameters = array(
			'user_name' => $this->conf['userID'],
			'password' => md5($this->conf['userPW']),
			'name' => $name
			);
    $result = $this->nusoapclient->call('create_note', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function set_entry($module, $arrValues) {
    $i = 0;
    while(list($key, $val) = each($arrValues)) {
      $name_values[$i]['name'] = $key;
      $name_values[$i]['value'] = $val;
      $i++;
    }
    $parameters = array(
			'session' => $this->sessionID,
			'module_name' => $module,
			'name_value_list' => $name_values
			);
    //debug($parameters);
    $result = $this->nusoapclient->call('set_entry', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      if ($result['error']['number']) {
	debug($result['error']);
	return false;
      } else {
	return $result;
      }
    } else {
      debug($err);
      return false;
    }
  }
	
  function set_relationship($module1, $id1, $module2, $id2) {
    $parameters = array(
			'session' => $this->sessionID,
			'set_relationship_value' => array(
							  'module1' => $module1,
							  'module1_id' => $id1,
							  'module2' => $module2,
							  'module2_id' => $id2
							  ),
			);
    $result = $this->nusoapclient->call('set_relationship', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      if ($result['error']['number']) {
	debug($result['error']);
	return false;
      } else {
	return true;
      }
    } else {
      debug($err);
      return false;
    }
  }

  function user_by_email($email) {
    if ($result = $this->user_list()) {
      while(list(, $user) = each($result)) {
	if (strtolower($user['email_address']) == strtolower($email)) return $user;
      }
    }
    return false;
  }
	
  function user_by_id($id) {
    if ($result = $this->user_list()) {
      while(list(, $user) = each($result)) {
	if (strtolower($user['id']) == strtolower($id)) return $user;
      }
    }
    return false;
  }
	
  function user_list() {
    $parameters = array(
			'user_name' => $this->conf['userID'],
			'password' => md5($this->conf['userPW'])
			);
    $result = $this->nusoapclient->call('user_list', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function module_list() {
    $parameters = array(
			'session' => $this->sessionID
			);
    $result = $this->nusoapclient->call('get_available_modules', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } else {
      debug($err);
      return false;
    }
  }
	
  function field_list($mod) {
    $parameters = array(
			'session' => $this->sessionID,
			'module_name' => $mod
			);
    $result = $this->nusoapclient->call('get_module_fields', $parameters);
    if (!$err = $this->nusoapclient->getError()) {
      return $result;
    } elseif ($result) {
      debug($err);
      return false;
    }
  }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/eu_sugarcrm/class.tx_eusugarcrm_base.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/eu_sugarcrm/class.tx_eusugarcrm_base.php']);
 }

?>
