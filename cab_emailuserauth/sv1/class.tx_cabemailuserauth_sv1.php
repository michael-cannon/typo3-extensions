<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Martin-Pierre Frenette <typo3@cablan.net>
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
 * Service "E-mail and Username fe_user authentication" for the "cab_emailuserauth" extension.
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 */


require_once(PATH_t3lib.'class.t3lib_svbase.php');

class tx_cabemailuserauth_sv1 extends t3lib_svbase {
	var $prefixId = 'tx_cabemailuserauth_sv1';		// Same as class name
	var $scriptRelPath = 'sv1/class.tx_cabemailuserauth_sv1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'cab_emailuserauth';	// The extension key.
	
	var $user = NULL;
		
	/**
	 * [Put your description here]
	 *
	 * @return	[type]		...
	 */
				function init()	{
				

					$available = parent::init();
	
					// Here you can initialize your class.
	
					// The class have to do a strict check if the service is available.
					// The needed external programs are already checked in the parent class.
	
					// If there's no reason for initialization you can remove this function.
	
					return $available;
				}
	
				/**
	 * [Put your description here]
	 * performs the service processing
	 *
	 * @param	string		Content which should be processed.
	 * @param	string		Content type
	 * @param	array		Configuration array
	 * @return	boolean
	 */
				function process($content='', $type='', $conf=array())	{
	
					// Depending on the service type there's not a process() function.
					// You have to implement the API of that service type.
	
					return FALSE;
				}
				
				
				
	function initAuth($subType, $loginData, $authInfo, $t3userAuth){
	
	
	$this->user = NULL;
	$status = addslashes($loginData['status']);
	$uname = addslashes($loginData['uname']);
	$uident = addslashes($loginData['uident']);
	$chalvalue = addslashes($loginData['chalvalue']);
	
	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery( '*', 'fe_users', 'password="'.$uident.'" AND (username="'.$uname.'" OR email="'.$uname.'")', '', 'uid DESC' );
	if ( $result != NULL && $GLOBALS['TYPO3_DB']->sql_num_rows($result) > 0 )	{
		$this->user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
	}
	
	
	}
	function getUser(){
		return $this->user;
	}
    function authUser()    {
        if ( $this->user != NULL )
        {
            return 1;
        }
    }
				
				
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_emailuserauth/sv1/class.tx_cabemailuserauth_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_emailuserauth/sv1/class.tx_cabemailuserauth_sv1.php']);
}

?>