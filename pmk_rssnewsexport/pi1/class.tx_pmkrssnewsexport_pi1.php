<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Michael Keukert (pmk@naklar.de)
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
 * Plugin 'RSS Newsfeed Export' for the 'pmk_rssnewsexport' extension.
 *
 * @author	Michael Keukert <pmk@naklar.de>
 *
 * Based on cm_rdfexport by Christoph Moeller (chris@byters.de)
 * XML code borrowed and modified from Benjamin Fischer's "XML_for_Flash" extension, 
 * which is based on Kasper Skaarhoej's XML class functions. Thx for that!
 * $Id: class.tx_pmkrssnewsexport_pi1.php,v 1.1.1.1 2010/04/15 10:03:57 peimic.comprock Exp $
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_pmkrssnewsexport_pi1 extends tslib_pibase {
	var $prefixId = "tx_pmkrssnewsexport_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_pmkrssnewsexport_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "pmk_rssnewsexport";	// The extension key.
	var $topLevelName ="";
	var $XMLIndent=0;
	var $Icode="";
	var $XML_recFields = array();
	var $XMLdebug=1;
	var $cObj;
	var $conf = array();
	var $includeNonEmptyValues=0;	// if set, all fields from records are rendered no matter their content. If not set, only "true" (that is "" or zero) fields make it to the document.
	var $lines=array();
	var $header;
	var $content;
	var $footer;
	var $categories = array();
	var $user;
	var $pass;
	var $feed_id;
	var $typeNum = 334; // if you need to change the page type, this is the place to do it. Don't forget to change it in the template too...
	var $newsitem;
	var $language;
	var $rssversion;
	var $feedimage;

	function make_xml($content,$conf)	{ 
		$this->conf=$conf; 
		$this->user=$_GET["user"];
		$this->pass=$_GET["pass"];
		$this->feed_id=$_GET["feed_id"];
		if ($this->is_feed($this->feed_id)) {
			if (isset($this->feed_id) && $this->feed_requires_login($this->feed_id) && isset($this->user) && isset($this->pass) && $this->check_login($this->user,$this->pass)) {
				$className=t3lib_div::makeInstanceClassName("tx_pmkrssnewsexport_pi1"); 
				$xmlObj = new $className("rss_export");
				$xmlObj->XMLdebug=0; 
				$xmlObj->conf = $conf; 
				$xmlObj->categories = $this->getNewsCategories(); 
				$xmlObj->rssversion = $this->feed_rss_version($this->feed_id);
				$xmlObj->setRecFields("tt_news","title,short,bodytext,author_email,ext_url,category,tstamp"); //uid changed to ext_url jsmod 
				// Creating top level object 
				$xmlObj->renderHeader(); 
				// Add page content information 
				$xmlObj->renderRecords("tt_news",$this->getContentResult("tt_news",$xmlObj->rssversion)); 
				//$xmlObj->newLevel("content_records"); 
				$xmlObj->renderFooter(); 
				return $xmlObj->getResult();
			}
			else if (isset($this->feed_id) && !$this->feed_requires_login($this->feed_id)) {
				$className=t3lib_div::makeInstanceClassName("tx_pmkrssnewsexport_pi1"); 
				$xmlObj = new $className("rss_export");
				$xmlObj->XMLdebug=0; 
				$xmlObj->conf = $conf; 
				$xmlObj->categories = $this->getNewsCategories(); 
				$xmlObj->rssversion = $this->feed_rss_version($this->feed_id);
 				$xmlObj->setRecFields("tt_news","title,short,bodytext,author_email,ext_url,category,tstamp"); //uid changed to ext_url jsmod 
				// Creating top level object 
				$xmlObj->renderHeader(); 
				// Add page content information 
				$xmlObj->renderRecords("tt_news",$this->getContentResult("tt_news",$xmlObj->rssversion)); 
				//$xmlObj->newLevel("content_records"); 
				$xmlObj->renderFooter(); 
				return $xmlObj->getResult();
			}
			else {
				$error_xml='<?xml version="1.0" encoding="ISO-8859-1"?>
<rss version="'.$this->rssversion.'">
<channel>
 <title>'.$this->conf["feedTitle"].' - ACCESS NOT ALLOWED</title>
 <link>'.$this->conf["feedLink"].'</link>
 <description>'.$this->conf["feedDescription"].' - ACCESS NOT ALLOWED</description>
</channel>
</rss>
';
				return $error_xml;
			}
		}
		else {
			$error_xml='<?xml version="1.0" encoding="ISO-8859-1"?>
<rss version="'.$this->rssversion.'">
<channel>
 <title>'.$this->conf["feedTitle"].' - ERROR IN FEED URL</title>
 <link>'.$this->conf["feedLink"].'</link>
<description>'.$this->conf["feedDescription"].' - ERROR IN FEED URL</description>
</channel>
</rss>
';
			return $error_xml;
		}
	}
 
	function getContentResult($table,$rssversion) { 
		global $TCA; 
		if ($TCA[$table])	{ 
			$linkTable = 'tt_news_cat_mm'; //js: The table where many-many relationship is defined for newsitems
			if ($this->conf["newsPidList"]=='') { 
				$news_select = " WHERE 1=1"; 
			} 
			else { 
				$news_select = " WHERE pid IN (".$this->conf["newsPidList"].")"; 
			} 
								
			if ($this->conf["newsCatList"]=='') { 
				$cat_select = ""; 
			} 
			else { 
				$cat_select = " AND category IN (".$this->conf["newsCatList"].")"; 
			} 
			 
			//js added a new config item. Normally, set either newsCatList or newsCatListMm, but not both.
			if ($this->conf["newsCatListMm"]=='') { 
				$cat_select_mm = ""; 
			} 
			else { 
				$cat_select_mm = " AND $linkTable.uid_local = $table.uid " 
					. " AND $linkTable.uid_foreign IN (".$this->conf["newsCatListMm"].")"; 
			} 

			if ($this->conf["newsItemOrderBy"]=='') { 
				$orderBy = ""; 
			} 
			else { 
				$orderBy = " ORDER BY ".$this->conf["newsItemOrderBy"]; 
			} 
			
			// only 15 items in RSS 0.91 
			$limit = $this->conf["newsItemCount"];

			if ($this->conf["newsItemCount"]=='') {  
				$limit = "";
			}
			else { 
				if ($limit > "15" and $rssversion=="0.91")
					$limit = "15";
				$limit = " LIMIT 0,".$limit; 
			}
			
			if ($limit=="" and $rssversion=="0.91")
				$limit = " LIMIT 0,15";

			$orderBy_limit = $orderBy.$limit; 
			$query = "SELECT * FROM ".$table . ', ' . $linkTable 
				.$news_select . $cat_select
				.$cat_select_mm 
				. $this->cObj->enableFields($table).$orderBy_limit; //jsmod 
			//echo $query; //debug
			$res = mysql(TYPO3_db,$query); 
			return $res; 
		} 
	} 
 
	function getNewsCategories() { 
		// get all categories into array 
		$query = "select * from tt_news_cat where 1=1"; 
		$res = mysql(TYPO3_db,$query); 
		//echo mysql_error(); 
		$categories=array(); 
		while($row = mysql_fetch_assoc($res))   { 
			$categories[$row["uid"]] = $row["title"]; 
		} 
		return $categories; 
	} 

	function main($content,$conf) {
		$this->pi_loadLL();
		
		switch((string)$this->cObj->data["tx_pmkrssnewsexport_is_protected"]) {
		case "1":
			if (isset($GLOBALS["TSFE"]->fe_user->user["username"])) {
				$content = $this->pi_getLL("rss_protected_at_this_url");
				$content .= '<a href="index.php?id='
					.$GLOBALS["TSFE"]->id.'&type='
					.$this->typeNum
					.'&feed_id='.$this->cObj->data["uid"]
					.'&user='.$GLOBALS["TSFE"]->fe_user->user["username"]
					.'&pass='.md5($GLOBALS["TSFE"]->fe_user->user["password"])
					.'&no_cache=1" target="_blank">'.$this->pi_getLL("rss_click_here").'</a>';
			}
			else {
				$content = $this->pi_getLL("rss_protected_not_logged_in");
			}
			break;
		default:
			$content = $this->pi_getLL("rss_openaccess_at_this_url");
			$content .= '<a href="index.php?id='
			.$GLOBALS["TSFE"]->id
			.'&type='
			.$this->typeNum
			.'&feed_id='.$this->cObj->data["uid"]
			.'&no_cache=1" target="_blank">'.$this->pi_getLL("rss_click_here").'</a>';
			$GLOBALS['TSFE']->additionalHeaderData ['pmk_rssnewsfeedexport'] = '<link rel="alternate" type="application/rss+xml" title="'
			.'RSS_feed_ID_'.$this->cObj->data["uid"]
			.'" href="index.php?id='
			.$GLOBALS["TSFE"]->id
			.'&type='
			.$this->typeNum
			.'&feed_id='.$this->cObj->data["uid"]
			.'&no_cache=1">';
		}
		return $this->pi_wrapInBaseClass($content);
	}
	
	function check_login($user,$pass) {
		$query="SELECT * FROM fe_users WHERE username='".$user."' AND disable !=1 AND deleted !=1";
		$res=mysql(TYPO3_db,$query);
		if ($res) {
			$userinfo=mysql_fetch_object($res);
			if ($pass==md5($userinfo->password)) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	function feed_requires_login ($feed_id) {
		$query="SELECT * FROM tt_content WHERE uid=".$feed_id." AND list_type='pmk_rssnewsexport_pi1' AND deleted !=1 AND hidden !=1";
		$res=mysql(TYPO3_db,$query);
		if ($res) {
			$feed_info=mysql_fetch_object($res);
			if ($feed_info->tx_pmkrssnewsexport_is_protected=='1') {
				return true; 
			}
			else return false;
		}	
		else return false;
	}

	function feed_rss_version ($feed_id) {
		$query="SELECT * FROM tt_content WHERE uid=".$feed_id." AND list_type='pmk_rssnewsexport_pi1' AND deleted !=1 AND hidden !=1";
		$res=mysql(TYPO3_db,$query);
		if ($res) {
			$feed_info=mysql_fetch_object($res);
			switch ($feed_info->tx_pmkrssnewsexport_rss_version) {
			  case 0: return '0.91'; break;
			  case 1: return '2.0'; break;
			  }
		}	
		else return '0.91';
	}

	function is_feed($feed_id) {
		$query="SELECT * FROM tt_content WHERE uid=".$feed_id." AND list_type='pmk_rssnewsexport_pi1' AND deleted !=1 AND hidden !=1";
		$res=mysql(TYPO3_db,$query);
		
		if ($res) {
			$feed_okay=mysql_fetch_object($res);
			if ($feed_okay->list_type=='pmk_rssnewsexport_pi1') {
				return true;
			}
		}
		else return false;
	}

	function setRecFields($table,$list)	{
		$this->XML_recFields[$table]=$list;
	}

	function getResult()	{
		$this->content = implode(chr(10),$this->lines);
		$this->content .= $this->footer;
		return $this->output($this->content);
	}

	function renderHeader()	{

		$feedimage = $this->conf["feedImage"];

		// RSS 0.91 requires a feed image to be present. If none is
		// configured, a default image will be taken
		if ($this->rssversion=='0.91' and $feedimage=='') {
			$feedimage = $this->conf["feedLink"].'/typo3conf/ext/'.$this->extKey.'/rss091.png';
		}

		$this->lines[]='<?xml version="1.0" encoding="ISO-8859-1"?>
<rss version="'.$this->rssversion.'">
<channel>
 <title>'.htmlspecialchars(substr($this->conf["feedTitle"],0,100)).'</title>
 <link>'.substr($this->conf["feedLink"],0,500).'</link>
 <description>'.htmlspecialchars($this->conf["feedDescription"]).'</description>
 <language>'.($this->conf["feedLanguage"]?$this->conf["feedLanguage"]:'en').'</language>
 <generator>PMK RSS Newsfeed Export (pmk_rssnewsexport) running on Typo3 CMS '.$GLOBALS["TYPO_VERSION"].'</generator>
 <docs>';
		if ($this->rssversion=='2.0') 
			$this->lines[].='http://blogs.law.harvard.edu/tech/rss';
		else $this->lines[].='http://backend.userland.com/rss091';

		$this->lines[].='</docs>
 <copyright>'.$this->conf["feedCopyright"].'</copyright>
 <managingEditor>'.$this->conf["feedManagingEditor"].'</managingEditor>
 <webMaster>'.$this->conf["feedWebMaster"].'</webMaster>';

		if ($feedimage != '') {
			$this->lines[].='			
 <image>
   <title>'.substr($this->conf["feedTitle"],0,100).'</title> 
   <url>'.$feedimage.'</url> 
   <link>'.substr($this->conf["feedLink"],0,500).'</link> 
 </image>
';
		}
	}

	function renderFooter()	{
		//$this->newLevel($this->topLevelName,0);
		$this->footer="
</channel>
</rss>";
	}

	function newLevel($name,$beginEndFlag=0,$params=array())	{
		if ($beginEndFlag)	{
			$pList="";
			if (count($params))	{
				$par=array();
				reset($params);
				while(list($key,$val)=each($params))	{
					$par[]=$key.'=$val';
				}
				$pList=" ".implode(" ",$par);
			}
			$this->lines[]=$this->Icode.'<'.$name.$pList.'>';
			$this->indent(1);
		} else {
			$this->indent(0);
			$this->lines[]=$this->Icode.'</'.$name.'>';
		}
	}

	function output()	{
		if ($this->XMLdebug)	{
			return '<pre>'.$this->content.'</pre>
			<hr><font color=red>Size: '.strlen($this->content).'</font>';
		} else {
			return $this->content;
		}
	}

	function indent($b)	{
		if ($b)	$this->XMLIndent++; else $this->XMLIndent--;
		$this->Icode="";
		for ($a=0;$a<$this->XMLIndent;$a++)	{
			$this->Icode.=chr(9);
		}
		return $this->Icode;
	}

	function renderRecords($table,$res) {
		while($row = mysql_fetch_assoc($res))	{
			$this->addRecord($table,$row);
		}
	}

	function addRecord($table,$row)	{
		$this->lines[]='<item>';
			$this->indent(1);
			$this->getRowInXML($table,$row);
			$this->indent(0);
		$this->lines[]='</item>';
	}

	function getRowInXML($table,$row)	{
		$fields = t3lib_div::trimExplode(",",$this->XML_recFields[$table],1);
		reset($fields);
		unset($this->newsitem);
		while(list(,$field)=each($fields))	{
			//if ($row[$field] || $this->includeNonEmptyValues)	{
				$this->lines[]=$this->Icode.$this->fieldWrap($field,$this->substNewline($row[$field]));
			//}
		}
	}

	function substNewline($string)	{
		return ereg_replace(chr(10),"",$string);
	}

	function fieldWrap($field,$value)	{
		switch($field) {
			case "tstamp":
				setlocale (LC_TIME, "en_US"); //js mod 
				return '<pubDate>'.strftime("%a, %d %b %Y %H:%M:%S %Z", $value).'</pubDate>';
				break;
			case "author_email":
				if ($this->conf["feedAuthorOverride"]!='') {
					$value=$this->conf["feedAuthorOverride"];
				}
				if ($value!='')  
          return '<author>'.$value.'</author>';
				break; 
			case "category":
				return '<category>'.$this->categories["$value"].'</category>';
				break;
			case "ext_url":
				return '<link>'.htmlspecialchars($value).'</link>
					<guid isPermaLink="true">'.htmlspecialchars($value).'</guid>'; //.$this->conf["feedItemLinkPrefix"] removed from before $value jsmod (2 places)
				break;
			case "short":
				$this->newsitem=$value;
				return '';
			break;
			case "bodytext":
				$this->newsitem.=' '.$value;
				if ($this->conf["feedItemDescLength"]=='') {
					$descr_length=100;
				}
				else {
					$descr_length=$this->conf["feedItemDescLength"];
					if (($this->rssversion=='0.91') and ($descr_length > 500))
						$descr_length=497;
				}
				if (strlen($this->newsitem) > $descr_length)
				  $points='...';
				return '<description>'.htmlspecialchars(substr($this->newsitem,0,$descr_length).$points).'</description>';
				break;
			default:
				return '<'.$field.'>'.htmlspecialchars($value).'</'.$field.'>';
		}	
	}
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/pmk_rssnewsexport/pi1/class.tx_pmkrssnewsexport_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/pmk_rssnewsexport/pi1/class.tx_pmkrssnewsexport_pi1.php"]);
}

?>
