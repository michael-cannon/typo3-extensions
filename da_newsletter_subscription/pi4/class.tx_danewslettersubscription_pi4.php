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
 * Plugin 'Newsletter' for the 'da_newsletter_subscription' extension.
 *
 * @author	Tatiana Policiganova <poluciganova@bcs-it.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_danewslettersubscription_pi4 extends tslib_pibase {
  var $prefixId = "tx_danewslettersubscription_pi4";		// Same as class name
  var $scriptRelPath = "pi4/class.tx_danewslettersubscription_pi4.php";	// Path to this script relative to the extension dir.
  var $extKey = "da_newsletter_subscription";	// The extension key.

  var $templateFile ='';
  /**
	 * Main function
	 */
  function main($content,$conf)	{

    // Otherwise proceed:
    if (strstr($this->cObj->currentRecord,"tt_content"))	{
      $conf["pidList"] = $this->cObj->data["pages"];
      $conf["recursive"] = $this->cObj->data["recursive"];
    }

    return $this->pi_wrapInBaseClass($this->listView($content,$conf));
  }

  /**
	 * Listing
	 */
  function listView($content,$conf)
  {
    $this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
    $this->pi_setPiVarDefaults();
    $this->templateFile = $this->cObj->fileResource('EXT:da_newsletter_subscription/pi4/newsletter.html');

    $query_newslet = "SELECT * FROM tx_danewslettersubscription_newsletter WHERE uid = ".$this->piVars['newsletter'].$this->cObj->enableFields("tx_danewslettersubscription_newsletter");
    $res_newslet = $GLOBALS['TYPO3_DB']->sql_query($query_newslet);

    if($res_newslet)
    {
      $row_newslet = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_newslet);

      $markerArray['###NEWSLETTER_TITLE###'] = htmlspecialchars($row_newslet['title']);
      $markerArray['###NEWSLETTER_DATE###'] = date("F j, Y",$row_newslet['starttime']?$row_newslet['starttime']:$row_newslet['crdate']);
      $markerArray['###NEWSLETTER_CONTENT###'] = $row_newslet['html_body'];

      $file = $row_newslet['link_file'];
      if($file)
      {


      $markerArray['###LINK_PDF###'] = '<a href="uploads/media/'.$file.'">Download PDF</a>';
      }
      else $markerArray['###LINK_PDF###'] = "";
      $content = $content = $this->cObj->substituteMarkerArray($this->templateFile, $markerArray);
    }
    return $content;
  }

}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi4/class.tx_danewslettersubscription_pi4.php"])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi4/class.tx_danewslettersubscription_pi4.php"]);
}

?>