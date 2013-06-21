<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2002 Kasper Skårhøj (kasper@typo3.com)
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
 * Plugin 'Newsletter categories List' for the 'da_newsletter_subscription' extension.
 *
 * @author	Poluciganova Tatiana <poluciganova@bcs-it.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_danewslettersubscription_pi2 extends tslib_pibase {
	var $prefixId = "tx_danewslettersubscription_pi2";		// Same as class name
	var $scriptRelPath = "pi2/class.tx_danewslettersubscription_pi2.php";	// Path to this script relative to the extension dir.
	var $extKey = "da_newsletter_subscription";	// The extension key.
	var $templateFile = '';

	var $emailAuth = array();
	/**
	 * Main function
	 */
	function main($content,$conf)	{
	  $this->templateFile = $this->cObj->fileResource('EXT:da_newsletter_subscription/pi2/record.html');
			// If no static template is included, show this error message:

			// Otherwise proceed:
		if (strstr($this->cObj->currentRecord,"tt_content"))	{
			$conf["pidList"] = $this->cObj->data["pages"];
			$conf["recursive"] = $this->cObj->data["recursive"];
		}
		return $this->pi_wrapInBaseClass($this->listView($content,$conf));
	}

	/**
	 * Listing categories
	 */
	function listView($content,$conf)	{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$Marker= array();
	  $template_row = $this->cObj->getSubpart($this->templateFile, '###TEMPLATE_CONTENT###');
	  $listNewsletter = $this->conf['listNewsletter'];


			// Select for listing:
		$pidList = $this->pi_getPidList($this->conf["pidList"],$this->conf["recursive"]);
		$query="SELECT * FROM tx_danewslettersubscription_cat WHERE pid IN (".$pidList.")".
				$this->cObj->enableFields("tx_danewslettersubscription_cat").
				" ORDER BY ".$this->returnSortingField().($this->conf["sorting_desc"]?" DESC":"");

		$content = '<script >
       function showPopUp(id, t) {
        var obj = document.getElementById(id);
        if (obj) {
         if (t==1) {
          obj.style.display = \'block\';
         } else {
          obj.style.display = \'none\';
         }
        }
       }
      </script><div class="list-newsletters">
      ';
		$row_link = '';
		$res = mysql(TYPO3_db,$query);
		if (mysql_error())	debug(array(mysql_error(),$query));
		$catList="0";
		while($row=mysql_fetch_assoc($res))	{
			$catList.=",".$row["uid"];
			$template_row = $this->cObj->getSubpart($this->templateFile, '###CONTENT###');

			$link= $this->pi_getPageLink($listNewsletter, '', array('tx_danewslettersubscription_pi3[cat]' => intval($row['uid'])));

			$row_link = '<a href="'.$link.'" class="newsletter_cat" onmouseover="showPopUp(\'catID'.$row["uid"].'\', 1)"  onmouseout="showPopUp(\'catID'.$row["uid"].'\', 0)">'.$row['title'].'</a>';
			$row_link .='<div id="catID'.$row["uid"].'" class = "popUp_newsletter" style="display: none; ">'.$row["descr"].'</div>';

			$Marker['###ROW###'] = $row_link;//['title'];
			$content .= $this->cObj->substituteMarkerArray($template_row, $Marker);
		}
		@mysql_data_seek($res,0);

		$content .= "</div>";

		return $content;

	}

		function returnSortingField()	{
		if (t3lib_div::inList("sorting,starttime,endtime,uid,crdate,title,description",$this->conf["sorting_field"]))	{
			return $this->conf["sorting_field"];
		} else return "sorting";
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi2/class.tx_danewslettersubscription_pi2.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi2/class.tx_danewslettersubscription_pi2.php"]);
}

?>