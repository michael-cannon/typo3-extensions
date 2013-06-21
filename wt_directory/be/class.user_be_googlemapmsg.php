<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Alexander Kellner <alexander.kellner@einpraegsam.net>
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

class user_be_googlemapmsg {
	
	function googlemapmsg($PA, $fobj) {
		if (!t3lib_extMgm::isLoaded('rggooglemap',0)) $content = '<img src="'.t3lib_extMgm::extRelPath("wt_directory").'files/icon_error.gif" alt="error" />'.' If you want to use googlemap links, please install extension rggooglemap first';
		else $content = '<img src="'.t3lib_extMgm::extRelPath("wt_directory").'files/icon_ok.gif" alt="ok" />';
		
		return $content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_googlemapmsg.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_directory/be/class.user_be_googlemapmsg.php']);
}

?>
