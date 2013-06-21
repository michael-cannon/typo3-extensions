<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Kasper Skårhøj (kasper@curbysoft.dk)
*  (c) 2006 Evgeniy Beskonchin (inf2k@bcs-it.com)
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
 * Plugin 'Sponsored by1' for the 'sponsor_content_scheduler' extension.
 *
 * @author    Kasper Skårhøj <kasper@curbysoft.dk>
 * @author    Evgeniy Beskonchin <inf2k@bcs-it.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_sponsorcontentscheduler_pi2 extends tslib_pibase {
    var $prefixId = "tx_sponsorcontentscheduler_pi2";        // Same as class name
    var $scriptRelPath = "pi2/class.tx_sponsorcontentscheduler_pi2.php";    // Path to this script relative to the extension dir.
    var $extKey = "sponsor_content_scheduler";    // The extension key.
	var $templateFile = 'EXT:sponsor_content_scheduler/templates/sponsored_by.html';
	var $sponsor;

    /**
     * Main function
     */
    function main($content,$conf)    {
    	if ($conf['templateFile']) $this->templateFile= $conf['templateFile'];
        list($t) = explode(":",$this->cObj->currentRecord);
        $this->internal["currentTable"]=$t;
        $this->internal["currentRow"]=$this->cObj->data;
        $this->sponsor= $this->internal["currentRow"]["tx_t3consultancies_sponsor"];
        return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
    }

    /**
     * Show single sponsor
     */
    function singleView($content,$conf)    {
    	global $TYPO3_DB;
        $this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

	$res= $TYPO3_DB->sql_query('SELECT * FROM tx_sponsorcontentscheduler_bulletin WHERE sponsor_id='.$this->sponsor.' AND hidden=0 AND deleted=0 LIMIT 1');
        $row= $TYPO3_DB->sql_fetch_assoc($res);
        if (!$row) return $this->pi_getLL('noSponsor');

		$template= $this->cObj->fileResource($this->templateFile);

	if($row['link_location'] != '' and $row['link_text'] != '')

		$markerArray= array(
			"###ID###" => $row['sponsor_id'],
			"###TITLE###" => htmlspecialchars($row['company_name'],ENT_NOQUOTES),
			"###DESCRIPTION###" => htmlspecialchars($row['description'], ENT_NOQUOTES),
			"###LOGO###" => 'uploads/tx_sponsorcontentscheduler/bulletins/'.$row['default_logo'],
			"###CALL_TO_ACTION_LINK###" => htmlspecialchars($row['link_location'],ENT_COMPAT),
			"###CALL_TO_ACTION_TEXT###" => htmlspecialchars($row['link_text'],ENT_NOQUOTES),

		);
	else
	{
		$template = $this->cObj->substituteSubpart($template, '###BODY_LINK###', '');
		$markerArray= array(
			"###ID###" => $row['sponsor_id'],
			"###TITLE###" => htmlspecialchars($row['company_name'],ENT_NOQUOTES),
			"###DESCRIPTION###" => htmlspecialchars($row['description'], ENT_NOQUOTES),
			"###LOGO###" => 'uploads/tx_sponsorcontentscheduler/bulletins/'.$row['default_logo'],
		);

	}

                              
        return $this->cObj->substituteMarkerArrayCached($template, $markerArray, array());
    }

    function strcap($text, $width){
    	if (strlen($text)<= $width) return $text;
    	else return substr($text, 0, $width-3).'...';
    }
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sponsor_content_scheduler/pi2/class.sponsorcontentscheduler_pi2.php"])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sponsor_content_scheduler/pi2/class.sponsozcontentscheduler_pi2.php"]);
}

?>