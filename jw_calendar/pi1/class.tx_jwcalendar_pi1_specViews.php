<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Jens Witt (jwitt@witttec.de)
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
 *
 * @author	Jens Witt <jwitt@witttec.de>
 */
 /**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(t3lib_extMgm::extPath('jw_calendar').'pi1/class.tx_jwcalendar_pi1_library.php');

class tx_jwcalendar_pi1_specViews extends tx_jwcalendar_pi1_library{

	function tx_jwcalendar_pi1_specViews($cObj,$conf,$jwOptions){
	}

  /* Workflows */

	function getForm(){
		return "";		
  	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_specViews.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jw_calendar/pi1/class.tx_jwcalendar_pi1_specViews.php']);
}
?>