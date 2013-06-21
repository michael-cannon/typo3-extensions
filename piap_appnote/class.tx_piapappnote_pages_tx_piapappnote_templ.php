<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Anton (anton@bcs-it.com)
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
 * Class/Function which manipulates the item-array for table/field pages_tx_piapappnote_templ.
 *
 * @author	Anton <anton@bcs-it.com>
 */



						class tx_piapappnote_pages_tx_piapappnote_templ {
							function main(&$params,&$pObj)	{
/*								debug("Hello World!",1);
								debug("\$params:",1);
								debug($params);
								debug("\$pObj:",1);
								debug($pObj);
	*/
									// Adding an item!
								$params["items"][]=Array($pObj->sL("Added label by PHP function|Tilføjet Dansk tekst med PHP funktion"), 999);

								// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
							}
						}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/piap_appnote/class.tx_piapappnote_pages_tx_piapappnote_templ.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/piap_appnote/class.tx_piapappnote_pages_tx_piapappnote_templ.php']);
}

?>