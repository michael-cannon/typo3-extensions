<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Peimic.com (http://peimic.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Class 'ux_tslib_fe' for the 'affiliate_tracker' extension set.
 *
 * @author	Suman Debnath <suman@srijan.in>
 * @author	Michael Cannon <michael@peimic.com>
 */

class ux_tslib_fe extends tslib_fe {
	function initFEuser() {
		parent::initFEuser();

		//File containing the relevant class
		require_once(t3lib_extMgm::extPath('affiliate_tracker').'classes/class.tx_affiliatetracker_base.php');

		//Grab our tac code
		$tac = trim(t3lib_div::_GP('tac'));

		if (!$tac) {
			if (isset($_SERVER['REQUEST_URI']) && preg_match('#\btac\b(/|=)([0-9A-Za-z]+)#', $_SERVER['REQUEST_URI'], $uriArgs)) {
				$tac = trim($uriArgs[2]);
			}
		}

		//Grab the FE user id. Should be 0 if not logged in.
		$feuser_id = intval($GLOBALS['TSFE']->fe_user->user['uid']);

		//Creating a new object of type 'tx_affiliatetracker_base'
		$affiliatetracker_obj = new tx_affiliatetracker_base();

		//Calling the function responsible for evaluating incoming data and entry
		$affiliatetracker_obj->recordAffiliateData($tac, $feuser_id);

		//Destroying object. Not necessary, but good practice since not needed anymore
		unset($affiliatetracker_obj);
	}
}
?>
