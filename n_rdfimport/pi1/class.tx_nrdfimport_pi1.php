<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 NePhie (tim.d'hooge@cronos.be)
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
 * Plugin 'RDF newsfeed' for the 'n_rdfimport' extension.
 *
 * @author	NePhie <tim.d'hooge@cronos.be>
 *
 * !!! Certain implementation ideas + rdf parsing code are borrowed from the cc_rdf_news_import extension !!!
 *
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_nrdfimport_pi1 extends tslib_pibase {
	var $prefixId = "tx_nrdfimport_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_nrdfimport_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "n_rdfimport";	// The extension key.
	
	
	//Declare the booleen variables associated with the particular tags
	var $blChannel = False;
	var $blTitle = False;
	var $blDescription = False;
	var $blLink = False;
	var $blLanguage = False;
	var $blRating = False;
	var $blCopyright = False;
	var $blPubDate = False;
	var $blLastBuild = False;
	var $blDocs = False;
	var $blManagingEditor = False;
	var $blWebmaster = False;
	var $blImage = False;
	var $blURL = False;
	var $blWidth = False;
	var $blHeight = False;
	var $blItem = False;
	var $blTextInput = False;
	var $blName = False;
	var $blSkipHours = False;
	var $blHour = False;
	var $blSkipDays = False;
	var $blDay = False;

	//Declare the data storage variables
	var $itemData = array();
	
		function _writeItem() {
		$temp = $this->itemData['ItemsCounter'];
		$this->itemData['Items'][$temp] = array("Title"=>$this->itemData['CurrentItemTitle'], "Link"=>$this->itemData['CurrentItemLink'], "Description"=>$this->itemData['CurrentItemDescription']);
		$this->itemData['CurrentItemTitle']="";
		$this->itemData['CurrentItemLink']="";
		$this->itemData['CurrentItemDescription']="";
		$this->itemData['ItemsCounter']++;
	}

	function _startElement($parser, $name, $attrs) {
			if ($name == "CHANNEL") {
				$this->blChannel = True;
			} elseif ($name == "TITLE") {
				$this->blTitle = True;
			} elseif ($name == "DESCRIPTION") {
				$this->blDescription = True;
			} elseif ($name == "LINK") {
				$this->blLink = True;
			} elseif ($name == "LANGUAGE") {
				$this->blLanguage = True;
			} elseif ($name == "RATING") {
				$this->blRating = True;
			} elseif ($name == "COPYRIGHT") {
				$this->blCopyright = True;
			} elseif ($name == "PUBDATE") {
				$this->blPubDate = True;
			} elseif ($name == "LASTBUILD") {
				$this->blLastBuild = True;
			} elseif ($name == "DOCS") {
				$this->blDocs = True;
			} elseif ($name == "MANAGINGEDITOR") {
				$this->blManagingEditor = True;
			} elseif ($name == "WEBMASTER") {
				$this->blWebmaster = True;
			} elseif ($name == "IMAGE") {
				$this->blImage = True;
			} elseif ($name == "URL") {
				$this->blURL = True;
			} elseif ($name == "WIDTH") {
				$this->blWidth = True;
			} elseif ($name == "HEIGHT") {
				$this->blHeight = True;
			} elseif ($name == "ITEM") {
				$this->blItem = True;
			} elseif ($name == "TEXTINPUT") {
				$this->blTextInput = True;
			} elseif ($name == "NAME") {
				$this->blName = True;
			} elseif ($name == "SKIPHOURS") {
				$this->blSkipHours = True;
			} elseif ($name == "HOUR") {
				$this->blHour = True;
			} elseif ($name == "SKIPDAYS") {
				$this->blSkipDays = True;
			} elseif ($name == "DAY") {
				$this->blDay = True;
			}
	}
	function _endElement($parser, $name) {
			if ($name == "CHANNEL") {
				$this->blChannel = False;
			} elseif ($name == "TITLE") {
				$this->blTitle = False;
			} elseif ($name == "DESCRIPTION") {
				$this->blDescription = False;
			} elseif ($name == "LINK") {
				$this->blLink = False;
			} elseif ($name == "LANGUAGE") {
				$this->blLanguage = False;
			} elseif ($name == "RATING") {
				$this->blRating = False;
			} elseif ($name == "COPYRIGHT") {
				$this->blCopyright = False;
			} elseif ($name == "PUBDATE") {
				$this->blPubDate = False;
			} elseif ($name == "LASTBUILD") {
				$this->blLastBuild = False;
			} elseif ($name == "DOCS") {
				$this->blDocs = False;
			} elseif ($name == "MANAGINGEDITOR") {
				$this->blManagingEditor = False;
			} elseif ($name == "WEBMASTER") {
				$this->blWebmaster = False;
			} elseif ($name == "IMAGE") {
				$this->blImage = False;
			} elseif ($name == "URL") {
				$this->blURL = False;
			} elseif ($name == "WIDTH") {
				$this->blWidth = False;
			} elseif ($name == "HEIGHT") {
				$this->blHeight = False;
			} elseif ($name == "ITEM") {
				$this->_writeItem();
				$this->blItem = False;
			} elseif ($name == "TEXTINPUT") {
				$this->blTextInput = False;
			} elseif ($name == "NAME") {
				$this->blName = False;
			} elseif ($name == "SKIPHOURS") {
				$this->blSkipHours = False;
			} elseif ($name == "HOUR") {
				$this->blHour = False;
			} elseif ($name == "SKIPDAYS") {
				$this->blSkipDays = False;
			} elseif ($name == "DAY") {
				$this->blDay = False;
			}
	}

	function _dataHandler($parser, $data) {
	  $data = chop($data);
	  
	  if ($data != "") {
	  	if ($this->blTitle) {
  			if ($this->blImage) {
  				$this->itemData['ChannelImageTitle'] .= $data;
  			} elseif ($this->blItem) {
  				$this->itemData['CurrentItemTitle'] .= $data;
  			} elseif ($this->blTextInput) {
  				$this->itemData['TextInputTitle'] .= $data;
  			} else {
  				$this->itemData['ChannelTitle'] .= $data;
  			}
	  	}

	  	if ($this->blLink) {
  			if ($this->blImage) {
  				$this->itemData['ChannelImageLink'] .= $data;
  			} elseif ($this->blItem) {
  				$this->itemData['CurrentItemLink'] .= $data;
  			} elseif ($this->blTextInput) {
  				$this->itemData['TextInputLink'] .= $data;
  			} else {
  				$this->itemData['ChannelTitleLink'] .= $data;
  			}
	  	}

	  	if ($this->blDescription) {
  			if ($this->blImage) {
  				$this->itemData['ChannelImageDescription'] .= $data;
  			} elseif ($this->blItem) {
  				$this->itemData['CurrentItemDescription'] .= $data;
  			} elseif ($this->blTextInput) {
  				$this->itemData['TextInputDescription'] .= $data;
  			} else {
  				$this->itemData['ChannelDescription'] .= $data;
  			}
	  	}

		if ($this->blLanguage) {
			$this->itemData['ChannelLanguage'] = $data;
		} elseif ($this->blRating) {
			$this->itemData['ChannelRating'] = $data;
		} elseif ($this->blCopyright) {
			$this->itemData['ChannelCopyright'] = $data;
		} elseif ($this->blPubDate) {
			$this->itemData['ChannelPubDate'] = $data;
		} elseif ($this->blLastBuild) {
			$this->itemData['ChannelLastBuildDate'] = $data;
		} elseif ($this->blDocs) {
			$this->itemData['ChannelDocs'] = $data;
		} elseif ($this->blManagingEditor) {
			$this->itemData['ChannelManagingEditor'] = $data;
		} elseif ($this->blWebmaster) {
			$this->itemData['ChannelWebmaster'] = $data;
		}

		if ($this->blURL) {
			$this->itemData['ChannelImageUrl'] = $data;
		} elseif ($this->blWidth) {
			$this->itemData['ChannelImageWidth'] = $data;
		} elseif ($this->blHeight) {
			$this->itemData['ChannelImageHeight'] = $data;
		}

		if ($this->blName) {
			$this->itemData['TextInputName'] = $data;
		}

		if ($this->blSkipHours && $this->blHour) {
			$temp = $this->itemData['ChannelSkipHoursCount'];
			$this->itemData['ChannelSkipHours'][$temp] = $data;
			$this->itemData['ChannelSkipHoursCount']++;
		}
		if ($this->blSkipDays && $this->blDay) {
			$temp = $this->itemData['ChannelSkipDaysCount'];
			$this->itemData['ChannelSkipDays'][$temp] = $data;
			$this->itemData['ChannelSkipDaysCount']++;
		}
	  }
	}

		//function to check proper Latin-1 encoding
	function _checkenc($data) {
		$tmp_pos = 0;
		$tmp_data = $data;
		$good_token = "&#"; //the good encoding we're looking for has this sequence after/including the crap out digit below
		$bad_token = "&"; //the digit that makes the parser crap out
		while ($tmp_pos <= strlen($tmp_data)) {
			$tmp_pos = strpos($tmp_data, $bad_token, $tmp_pos);
			if ($tmp_pos && substr($tmp_data, $tmp_pos, 2) != $good_token) { //if & is not followed by #, get rid of the &
				$tmp_data = substr_replace($tmp_data, substr($tmp_data, $tmp_pos+1, 1), $tmp_pos, 1);
				$tmp_pos++;
			} elseif ($tmp_pos && substr($tmp_data, $tmp_pos, 2) == $good_token) { //if & is followed by #, then we're okay... leave the data alone and move on
				$tmp_data = $tmp_data;
				$tmp_pos++;
			} else { //make sure the while loop will be broken
				$tmp_pos = strlen($data) + 100;
			}
		}
		return $tmp_data; //return the modified string
	}

	// Call to start the parsing of the file
	function parse($xmlData, $check_encoding) {
		$error = "";
		$xml_parser = xml_parser_create();
		xml_set_object($xml_parser,$this);
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, True);
		xml_set_element_handler($xml_parser,"_startElement","_endElement");
		xml_set_character_data_handler($xml_parser,"_dataHandler");

		if ($check_encoding) { $xmlData = $this->_checkenc($xmlData); }

		if (!xml_parse($xml_parser, $xmlData)) {
			$error = sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser));
				$this->blItem = false;
		}
//echo $error;
		xml_parser_free($xml_parser);
		return $error;
	}

// ########### end of XML/RDF code ###########
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		switch((string)$conf["CMD"])	{
			case "singleView":
				list($t) = explode(":",$this->cObj->currentRecord);
				$this->internal["currentTable"]=$t;
				$this->internal["currentRow"]=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:
				if (strstr($this->cObj->currentRecord,"tt_content"))	{
					$conf["pidList"] = $this->cObj->data["pages"];
					$conf["recursive"] = $this->cObj->data["recursive"];
				}
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}
	
	/**
	 * [Put your description here]
	 */
	function listView($content,$conf)	{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		$FP = $this->conf["CMD"]=="FP" ? 1 : $this->cObj->data["tx_nrdfimport_mode"];
		
		$lConf = $this->conf["listView."];	// Local settings for the listView function

		if ($this->piVars["showUid"])	{	// If a single element should be displayed:
			$this->internal["currentTable"] = "tx_nrdfimport_feeds";
			$this->internal["currentRow"] = $this->pi_getRecord("tx_nrdfimport_feeds",$this->piVars["showUid"]);
			
			$content = $this->singleView($content,$conf);
			return $content;
		} elseif ( 'listing' == $FP && $this->cObj->data["tx_nrdfimport_feed"]) {
			$this->internal["currentRow"] =
			$this->pi_getRecord("tx_nrdfimport_feeds",$this->cObj->data["tx_nrdfimport_feed"]);
			
			$content = $this->singleView($content,$conf);
			return $content;
		} else {
			$items=array(
				"1"=> $this->pi_getLL("list_mode_1","Mode 1"),
				"2"=> $this->pi_getLL("list_mode_2","Mode 2"),
				"3"=> $this->pi_getLL("list_mode_3","Mode 3"),
			);
			if (!isset($this->piVars["pointer"]))	$this->piVars["pointer"]=0;
			if (!isset($this->piVars["mode"]))	$this->piVars["mode"]=1;
	
				// Initializing the query parameters:
			list($this->internal["orderBy"],$this->internal["descFlag"]) = explode(":",$this->piVars["sort"]);
			// Number of results to show in a listing.
			$this->internal["results_at_a_time"]=$this->cObj->data["tx_nrdfimport_count"];
			$this->internal["maxPages"]=t3lib_div::intInRange($lConf["maxPages"],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
			$this->internal["searchFieldList"]="name,url,poll_interval,cached_detail,cached_list";
			$this->internal["orderByList"]="name,uid,url,poll_interval";
			
				// Get number of records:
			$query = $this->pi_list_query("tx_nrdfimport_feeds",1);
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			list($this->internal["res_count"]) = mysql_fetch_row($res);
	
				// Make listing query, pass query to MySQL:
			$query = $this->pi_list_query("tx_nrdfimport_feeds");
			$res = mysql(TYPO3_db,$query);
			if (mysql_error())	debug(array(mysql_error(),$query));
			$this->internal["currentTable"] = "tx_nrdfimport_feeds";

			while($row = mysql_fetch_assoc($res))
			{
				$list[] = $row;
			}

			$content = $this->buildList($list,$FP);
			
			return $content;
		}
	}
	

	function getItemData($item)
	{
		$this->itemData = array();
		//initialize counters
		$this->itemData['ItemsCounter'] = 0;
		$this->itemData['ChannelSkipHoursCount'] = 0;
		$this->itemData['ChannelSkipDaysCount'] = 0;
		//initialize booleans
		$this->$blChannel = False;
		$this->$blTitle = False;
		$this->$blDescription = False;
		$this->$blLink = False;
		$this->$blLanguage = False;
		$this->$blRating = False;
		$this->$blCopyright = False;
		$this->$blPubDate = False;
		$this->$blLastBuild = False;
		$this->$blDocs = False;
		$this->$blManagingEditor = False;
		$this->$blWebmaster = False;
		$this->$blImage = False;
		$this->$blURL = False;
		$this->$blWidth = False;
		$this->$blHeight = False;
		$this->$blItem = False;
		$this->$blTextInput = False;
		$this->$blName = False;
		$this->$blSkipHours = False;
		$this->$blHour = False;
		$this->$blSkipDays = False;
		$this->$blDay = False;

		$rdfData = $this->getRdf($item['url']);
		$error = $this->parse($rdfData, FALSE);

		if (!$this->itemData['ItemsCounter'] OR $rdfData == "" OR $error != "") {
			return unserialize(stripslashes($item['cached_data']));
		}
		else
		{
			$query = "UPDATE tx_nrdfimport_feeds SET cached_data='" . addslashes(serialize($this->itemData)) . "', tstamp='".time()."' WHERE uid = (" .$item["uid"]. ")";
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
			
			return $this->itemData;
		}
	}

	function getRdf($url) {
		$rdfData = t3lib_div::getURL($url);

		if ($rdfData) {
			$cArr = explode("\n", $rdfData);
			$rdfData="";
			reset($cArr);
			$afterHeader=FALSE;
			while (list($key,$val) = each($cArr)) {
				if (strstr ($val,"<?xml")) { // this has to be the first line
					break;
				}
			    	unset($cArr[$key]);
			}
			$rdfData = implode("\n",$cArr);
		}

		return $rdfData;
	}

	function buildList($list,$FP)
	{
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$content = '';
		$content_item = '';
	
		if( $this->cObj->data["tx_nrdfimport_template"] )
		{
			$this->templateCode = $this->cObj->fileResource(
				'uploads/tx_nrdfimport/' . $this->cObj->data["tx_nrdfimport_template"]
			);
			$this->pi_tmpPageId = intval($this->cObj->data["pages"]);
		}

		elseif($FP == "frontpage")
		{
			$this->templateCode = $this->cObj->fileResource($this->conf["frontPage."]["templateFile"]);
			$this->pi_tmpPageId = intval($this->cObj->data["pages"]);
		}
		else
		{
			$this->templateCode = $this->cObj->fileResource($this->conf["list."]["templateFile"]);
		}

		$template = array();
		$template['total'] = $this->cObj->getSubpart($this->templateCode,"###LIST###");
		$template['item'] = $this->cObj->getSubpart($template['total'],"###LIST_ITEM###");
		$template['link'] = $this->cObj->getsubpart($template['item'],'###LIST_LINK###');
		
		$count = count($list);
		
		for($counter = 0; $counter < $count; $counter++)
		{
			$markerArray = array();
			$row = &$list[$counter];


			if ($row["cached_data"]=="" OR (time()-$row["poll_interval"] > $row["tstamp"]))		
			{
				$itemData = $this->getItemData($row);
			}
			else
			{
				$itemData = unserialize(stripslashes($row['cached_data']));
			}

			$markerArray = $this->getChannelMarkerArray($itemData);
			
			
			$link = $this->cObj->substituteMarkerArrayCached($template['link'],$markerArray,array(),array());
			$link = $this->pi_list_linkSingle($link,$row["uid"],1);
			$linkArray["###LIST_LINK###"] = $link;

			$output = $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, $linkArray, array());
			$output = $this->pi_getEditIcon($output,"name,url,poll_interval,cached_data","RSS import: " . $markerArray["###CHANNEL_TITLE###"],$row,"tx_nrdfimport_feeds");
			$content_item .= $output;
		}

		$subpartArray = array();
		$subpartArray["###CONTENT###"] = $content_item;
		$content .= $this->cObj->substituteMarkerArrayCached($template["total"], array(), $subpartArray, array());
		
		
		return $content;
	}
	
	function getChannelMarkerArray(&$itemData)
	{
			$markerArray["###CHANNEL_TITLE###"] = $itemData['ChannelTitle'];
			$markerArray["###CHANNEL_DESCRIPTION###"] = $itemData["ChannelDescription"];
			$markerArray["###CHANNEL_RATING###"] = $itemData["ChannelRating"];
			$markerArray["###CHANNEL_COPYRIGHT###"] = $itemData["Channelcopyright"];
			$markerArray["###CHANNEL_PUBDATE###"] = $itemData["ChannelPubDate"];
			$markerArray["###CHANNEL_LASTBUILD###"] = $itemData["ChannelLastBuildDate"];
			$markerArray["###CHANNEL_TITLELINK###"] = $itemData["ChannelTitleLink"];
			$markerArray["###CHANNEL_LANGUAGE###"] = $itemData["ChannelLanguage"];
			$markerArray["###CHANNEL_DOCS###"] = $itemData["ChannelDocs"];
			$markerArray["###CHANNEL_MANAGINGEDITOR###"] = $itemData["ChannelManagingEditor"];
			$markerArray["###CHANNEL_WEBMASTER###"] = $itemData["ChannelWebmaster"];
			$markerArray["###CHANNEL_IMAGETITLE###"] = $itemData["ChannelImageTitle"];
			$markerArray["###CHANNEL_IMAGEURL###"] = $itemData["ChannelImageUrl"];
			$markerArray["###CHANNEL_IMAGELINK###"] = $itemData["ChannelImageLink"];
			$markerArray["###CHANNEL_IMAGEWIDTH###"] = $itemData["ChannelImageWidth"];
			$markerArray["###CHANNEL_IMAGEHEIGHT###"] = $itemData["ChannelImageHeight"];
			$markerArray["###CHANNEL_IMAGEDESCRIPTION###"] = $itemData["ChannelimageDescription"];
			$markerArray["###CHANNEL_INPUTTITLE###"] = $itemData["ChannelInputTitle"];
			$markerArray["###CHANNEL_INPUTLINK###"] = $itemData["ChannelInputLink"];
			$markerArray["###CHANNEL_INPUTNAME###"] = $itemData["ChannelInputName"];
			$markerArray["###CHANNEL_INPUTDESCRIPTION###"] = $itemData["ChannelInputDescription"];

			return $markerArray;
	}
	
	function getItemMarkerArray(&$itemData)
	{
			$markerArray["###CHANNEL_ITEM_TITLE###"] = $itemData["Title"];
			$markerArray["###CHANNEL_ITEM_DESCRIPTION###"] = $itemData["Description"];
			$markerArray["###CHANNEL_ITEM_LINK###"] = $itemData["Link"];
			
			return $markerArray;
	}
	
	/**
	 * [Put your description here]
	 */
	function singleView($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$lConf = $this->conf["singleView."];
		$this->internal["results_at_a_time"]=$this->cObj->data["tx_nrdfimport_count"];
		
		$content = '';
		$feed = &$this->internal['currentRow'];
	
		// This sets the title of the page for use in indexed search results:
		if ($feed["name"])	$GLOBALS["TSFE"]->indexedDocTitle=$feed["name"];
	
		if( $this->cObj->data["tx_nrdfimport_template"] )
		{
			$this->templateCode = $this->cObj->fileResource(
				'uploads/tx_nrdfimport/' . $this->cObj->data["tx_nrdfimport_template"]
			);
		}

		elseif($this->cObj->data["tx_nrdfimport_mode"] == "listing")
		{
			$this->templateCode = $this->cObj->fileResource($this->conf["list."]["templateFile"]);
		}
		else
		{
			$this->templateCode = $this->cObj->fileResource($this->conf["detail."]["templateFile"]);
		}
		

		$template = array();
		$template['total'] = $this->cObj->getSubpart($this->templateCode,"###DETAIL###");
		$template['list'] = $this->cObj->getSubpart($template['total'],"###LIST###");
		$template['item'] = $this->cObj->getSubpart($template['list'],"###LIST_ITEM###");
		$template['channelLink'] = $this->cObj->getsubpart($template['total'],'###CHANNEL_LINK###');
		$template['listLink'] = $this->cObj->getsubpart($template['item'],'###LIST_LINK###');
		$template['listLink2'] = $this->cObj->getsubpart($template['item'],'###LIST_LINK2###');
	
		if($feed["cached_data"] == "" OR (time()-$feed["poll_interval"] > $feed["tstamp"]))
		{
			$itemData = $this->getItemData($feed);
		}
		else
		{
			$itemData = unserialize(stripslashes($feed["cached_data"]));
		}

		$content_item = '';
		
		$list = $itemData['Items'];
		$count = count($list);
		if($count > $this->internal["results_at_a_time"])
		{
			$count = $this->internal["results_at_a_time"];
		}
		
		for($counter = 0; $counter < $count; $counter++)
		{
			$markerArray = array();
			$row = &$list[$counter];
			

			$markerArray = $this->getItemMarkerArray($list[$counter]);
			
			$link = $this->cObj->substituteMarkerArrayCached($template['listLink'],$markerArray,array(),array());
			$link = $this->pi_linkToPage($link,$row["Link"],"_blank");
			$linkArray["###LIST_LINK###"] = $link;
			
			$link = $this->cObj->substituteMarkerArrayCached($template['listLink2'],$markerArray,array(),array());
			$link = $this->pi_linkToPage($link,$row["Link"],"_blank");
			$linkArray["###LIST_LINK2###"] = $link;
			
			$content_item .= $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, $linkArray, array());
		}

		$subpartArray = array();
		$subpartArray["###LIST###"] = $content_item;
		
		$channelMarkerArray = $this->getChannelMarkerArray($itemData);
		
		$channelMarkerArray["###CHANNEL_TITLE###"] = $this->pi_getEditIcon($channelMarkerArray["###CHANNEL_TITLE###"],"name,url,poll_interval,cached_data","RSS Import" . $channelMarkerArray["###CHANNEL_TITLE###"],$feed,"tx_nrdfimport_feeds");

		$channelLink = $this->cObj->substituteMarkerArrayCached($template['channelLink'],$channelMarkerArray,array(),array());
		$channelLink = $this->pi_linkToPage($channelLink,$itemData['ChannelTitleLink'],"_blank");
		$subpartArray["###CHANNEL_LINK###"] = $channelLink;
		
		
		$content .= $this->cObj->substituteMarkerArrayCached($template["total"], $channelMarkerArray, $subpartArray, array());
	
		return $content;
	}	
	
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/n_rdfimport/pi1/class.tx_nrdfimport_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/n_rdfimport/pi1/class.tx_nrdfimport_pi1.php"]);
}

?>
