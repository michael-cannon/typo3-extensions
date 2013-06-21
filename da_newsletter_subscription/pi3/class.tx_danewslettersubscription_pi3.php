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
 * Plugin 'Newsletters by category' for the 'da_newsletter_subscription' extension.
 *
 * @author	Tatiana Policiganova <poluciganova@bcs-it.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_danewslettersubscription_pi3 extends tslib_pibase {
  var $prefixId = "tx_danewslettersubscription_pi3";		// Same as class name
  var $scriptRelPath = "pi3/class.tx_danewslettersubscription_pi3.php";	// Path to this script relative to the extension dir.
  var $extKey = "da_newsletter_subscription";	// The extension key.

  var $emailAuth = array();
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
    $this->pi_loadLL();

    $query_cat = "SELECT * FROM tx_danewslettersubscription_cat WHERE uid = ".$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['cat'], '');
    $res_cat = $GLOBALS['TYPO3_DB']->sql_query($query_cat);
    $row_cat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_cat);

	// MLC 20070517 phelps requests Newsletter Issues only
    // $content = '<div><h1 class="csc-firstHeader">'.$this->pi_getLL("title").$row_cat['title'].'</h1></div>'.$row_cat['descr'].'<br><ul>';
    $content = '<div><h1 class="csc-firstHeader">Newsletter Issues</h1></div>'.$row_cat['descr'].'<br><ul>';

    $query = "SELECT COUNT(DISTINCT(uid)) FROM tx_danewslettersubscription_newsletter WHERE deleted = 0 AND hidden = 0 AND category = ".$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['cat'], '').' AND (starttime <='.mktime().' OR starttime=0) AND ( endtime>'.mktime().' OR endtime=0 )';

    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

    $newsletter_count = $row[0];

    if($this->conf['limit']>0 && $newsletter_count>$this->conf['limit'])
    {
      $pointer = 0;
      $page_count = ceil($newsletter_count/$this->conf['limit']);

      if($this->piVars['pointer'])
      {
        $pointer = intval($this->piVars['pointer']);
      }

      $conf['begin'] = $pointer * $this->conf['limit'];
      $conf['max'] = $this->conf['limit'];

      $queryParts = $this->cObj->getQuery('tx_danewslettersubscription_newsletter', $conf, TRUE);

      if($queryParts['LIMIT'])
        $query = "SELECT * FROM tx_danewslettersubscription_newsletter WHERE deleted = 0 AND hidden = 0 AND category = ".$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['cat'], '').' AND (starttime <='.mktime().' OR starttime=0) AND ( endtime>'.mktime().' OR endtime=0 ) ORDER BY starttime DESC LIMIT '.$queryParts['LIMIT'];
      else
        $query = "SELECT * FROM tx_danewslettersubscription_newsletter WHERE deleted = 0 AND hidden = 0 AND category = ".$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['cat'], ' ').' AND (starttime <='.mktime().' OR starttime=0) AND ( endtime>'.mktime().' OR endtime=0 ) ORDER BY starttime DESC';


      $res = $GLOBALS['TYPO3_DB']->sql_query($query);

      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
      {
        if($row['html_body'])
        {
          $content.='<li>'.str_replace('pi3','pi4',$this->pi_linkTP_keepPIvars($row['title'], array('pointer'=>null, 'newsletter' => $row['uid']),0,0, $this->conf['newsletter'])).'</li>';
        }
        else if($row['link_file'])
          $content.= '<li><a href="uploads/media/'.$row['link_file'].'" target="_blank">'.$row['title'].'</a><br></li>';
        else
          $content.= '<li>'.$row['title'].'</li>';
      }

      $content .= '<center><table><tr>';

      //------ page browser -------
      $links=array();
      $maxPage = $this->conf['maxPage'];

      if($pointer>0)
      $links[]='<td nowrap="nowrap"><p>'.$this->pi_linkTP_keepPIvars('< Previous', array('pointer'=>($pointer-1?$pointer-1:'')),$this->allowCaching).'</p></td>';


      $limit_new = ($maxPage*floor($pointer/$maxPage))+$maxPage;
      for($a=$maxPage*floor($pointer/$maxPage);$a<$limit_new && $a<$page_count;$a++)
      {
        $links[]='<td nowrap="nowrap"><p>'.($pointer==$a?'<u>'.trim($a+1).'</u>':
        $this->pi_linkTP_keepPIvars(trim(($a+1)),array('pointer'=>($a?$a:'')),$this->allowCaching)).'</p></td>';
      }

      if($pointer < $page_count-1)
      {
        $links[] = '<td nowrap="nowrap"><p>'. $this->pi_linkTP_keepPIvars('Next >', array('pointer'=>($pointer+1?$pointer+1:'')),$this->allowCaching).'</p></td>';
      }

      $content .= implode('',$links).'</tr></table></center>';
    }
     //------ page browser -------
    else
    {
      $query = "SELECT * FROM tx_danewslettersubscription_newsletter WHERE deleted = 0 AND hidden = 0 AND category = ".$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['cat'],' ').' AND (starttime <='.mktime().' OR starttime=0) AND ( endtime>'.mktime().' OR endtime=0 ) ORDER BY starttime DESC';
      $res = $GLOBALS['TYPO3_DB']->sql_query($query);

      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
      {
        if($row['html_body'])
        {
          $content.='<li>'.str_replace('pi3','pi4',$this->pi_linkTP_keepPIvars($row['title'], array('pointer'=>null, 'newsletter' => $row['uid']),0,0, $this->conf['newsletter'])).'</li>';
        }
        else if($row['link_file'])	  $content.= '<li><a href="uploads/media/'.$row['link_file'].'" target="_blank">'.$row['title'].'</a><br></li>';
        else $content.= '<li>'.$row['title'].'</li>';
      }
    }

    // Returns the content from the plugin.
    if($this->conf['backPid']) return $content.'</ul><br><br><br><a href="'.$this->pi_getPageLink($this->conf['backPid'], '', array()).'">BACK</a>';
    else return $content.'</ul><br><br><br><a href="javascript:history.back(-1)">BACK</a>';
  }

}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi3/class.tx_danewslettersubscription_pi3.php"])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi3/class.tx_danewslettersubscription_pi3.php"]);
}

?>