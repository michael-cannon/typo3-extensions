<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2005 Franz Holzinger <kontakt@fholzinger.com>
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Part of the tt_products (Shopping System) extension.
 *
 * div functions
 *
 * $Id: class.tx_ttproducts_div.php,v 1.1.1.1 2010/04/15 10:04:12 peimic.comprock Exp $
 *
 * @author	Franz Holzinger <kontakt@fholzinger.com>
 * @package TYPO3
 * @subpackage tt_products
 *
 *
 */


class tx_ttproducts_div {

       /**
         * Sets JavaScript code in the additionalJavaScript array
         *
         * @param       string          $fieldname is the field in the table you want to create a JavaScript for
         * @return      void
         * @see
         */
    function setJS($fieldname) {
        global $TSFE;
        $js = '';
        $emailArr =  explode('|', $message = $this->pi_getLL('invalid email'));

		switch ($fieldname) {
			case 'email' :
						$js =
			'function test (eing) {
				var reg = /@/;
				var rc = true;
				if (!reg.exec(eing)) {
			 		rc = false;
			 	}
			 	return rc;
			}

			function checkEmail(element) {
				if (test(element.value)){
					return (true)
				}
/* Added els5: comma after the invalid address */
//				alert("'.$emailArr[0].'\'"+element.value+"'.$emailArr[1].'")
				alert("'.$emailArr[0].'\'"+element.value+"\''.$emailArr[1].'")
				return (false)
			}

			function checkParams (formObj) {
				var rc = true;
				for (var i = 0; i < formObj.length; i++) {
					if (formObj[i].type == "text") {
						var email = /email/i;
						if (email.exec(formObj[i].name)) {
							rc = checkEmail (formObj[i]);
						}
					}
					if (!rc) {
						break;
					}
				}
				return rc;
			}

			';
			break;
		}

	$TSFE->setJS ($fieldname, $js);
    } // setJS

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_products/lib/class.tx_ttproducts_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tt_products/lib/class.tx_ttproducts_div.php']);
}


?>
