<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Mischa Heiﬂmann <typo3.2008@heissmann.org>
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
 * Class/Function which manipulates the item-array for table/field tx_powermailcond_rules_fieldname.
 *
 * @author	Mischa Heiﬂmann <typo3.2008@heissmann.org>
 * @package	TYPO3
 * @subpackage	tx_powermailcond
 */
class tx_powermailcond_tx_powermailcond_rules_fieldname {
	// show all fields in the backend
	function fieldname(&$params, &$pObj)	{
		// Adding an item!
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$select_fields = 'uid, title',
			$from_table = 'tx_powermail_fields',
			$where_clause = 'hidden = 0 AND deleted = 0',
			$groupBy = '',
			$orderBy = '',
			$limit = ''
		);
		if($res != '' || $res > 0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$params['items'][] = array($pObj->sL($row['title']).' ('.$row['uid'].')', $row['uid']);
			}
		}
	}
	
	// show als fieldsets in the backend
	function fieldsetname(&$params, &$pObj)	{
		// Adding an item!
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$select_fields = 'uid, title',
			$from_table = 'tx_powermail_fieldsets',
			$where_clause = 'hidden = 0 AND deleted = 0',
			$groupBy = '',
			$orderBy = '',
			$limit = ''
		);
		if($res != '' || $res > 0) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$params['items'][] = array($pObj->sL($row['title']).' ('.$row['uid'].')', $row['uid']);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/class.tx_powermailcond_tx_powermailcond_rules_fieldname.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail_cond/class.tx_powermailcond_tx_powermailcond_rules_fieldname.php']);
}

?>