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
 * Service "Unique news displayer" for the "cab_uniquenews" extension.
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 */



require_once(PATH_t3lib.'class.t3lib_svbase.php');

class tx_cabuniquenews_sv1 extends t3lib_svbase {
	var $prefixId = 'tx_cabuniquenews_sv1';		// Same as class name
	var $scriptRelPath = 'sv1/class.tx_cabuniquenews_sv1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'cab_uniquenews';	// The extension key.
	
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
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_uniquenews/sv1/class.tx_cabuniquenews_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cab_uniquenews/sv1/class.tx_cabuniquenews_sv1.php']);
}

?>