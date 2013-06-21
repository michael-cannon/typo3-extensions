<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002 René Fritz (r.fritz@colorcube.de)
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
 * Plugin 'News feed import' for the 'cc_rdf_news_import' extension.
 *
 *
 * The rdf parser is ripped from rdflib from Jason Williams, jason@nerdzine.net, (GPL license)
 *
 *
 * @author	René Fritz [r.fritz@colorcube.de]
 *
 * www.colorcube.de - typo3lab.colorcube.de - www.typo3.info
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_ccrdfnewsimport_pi1 extends tslib_pibase {

	var $conf;

	var $allowCaching;

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
	var $Items = array();
	var $ItemsCounter;
	var $CurrentItemTitle;
	var $CurrentItemLink;
	var $CurrentItemDescription;

	var $ChannelTitle;
	var $ChannelDescription;
	var $ChannelTitleLink;
	var $ChannelLanguage;
	var $ChannelRating;
	var $ChannelCopyright;
	var $ChannelPubDate;
	var $ChannelLastBuildDate;
	var $ChannelDocs;
	var $ChannelManagingEditor;
	var $ChannelWebmaster;

	var $ChannelImageTitle;
	var $ChannelImageUrl;
	var $ChannelImageLink;
	var $ChannelImageWidth;
	var $ChannelImageHeight;
	var $ChannelImageDescription;

	var $ChannelSkipHours = array();
	var $ChannelSkipDays = array();
	var $ChannelSkipHoursCount;
	var $ChannelSkipDaysCount;

	var $TextInputTitle;
	var $TextInputDescription;
	var $TextInputName;
	var $TextInputLink;


		// get rdf/xml data from an url and return it
	function getrdf($rdfUrl,$timeout) {
		$rdfData="";
		$url = parse_url($rdfUrl);
		$fp = fsockopen($url['host'], "80", &$errno, &$errstr, $timeout);
		if ($fp) {
			fputs($fp, "GET " . $url['path'] . " HTTP/1.1\r\nHost: " . $url['host'] . "\r\n\r\n");
			while(!feof($fp))
			$rdfData .= fgets($fp, 128);
		}

			// strip HTTP header
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

	function _writeItem() {
		$this->Items[$this->ItemsCounter] = array("Title"=>$this->CurrentItemTitle, "Link"=>$this->CurrentItemLink, "Description"=>$this->CurrentItemDescription);
		$this->CurrentItemTitle="";
		$this->CurrentItemLink="";
		$this->CurrentItemDescription="";
		$this->ItemsCounter++;
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
  				$this->ChannelImageTitle .= $data;
  			} elseif ($this->blItem) {
  				$this->CurrentItemTitle .= $data;
  			} elseif ($this->blTextInput) {
  				$this->TextInputTitle .= $data;
  			} else {
  				$this->ChannelTitle .= $data;
  			}
	  	}

	  	if ($this->blLink) {
  			if ($this->blImage) {
  				$this->ChannelImageLink .= $data;
  			} elseif ($this->blItem) {
  				$this->CurrentItemLink .= $data;
  			} elseif ($this->blTextInput) {
  				$this->TextInputLink .= $data;
  			} else {
  				$this->ChannelTitleLink .= $data;
  			}
	  	}

	  	if ($this->blDescription) {
  			if ($this->blImage) {
  				$this->ChannelImageDescription .= $data;
  			} elseif ($this->blItem) {
  				$this->CurrentItemDescription .= $data;
  			} elseif ($this->blTextInput) {
  				$this->TextInputDescription .= $data;
  			} else {
  				$this->ChannelDescription .= $data;
  			}
	  	}

		if ($this->blLanguage) {
			$this->ChannelLanguage = $data;
		} elseif ($this->blRating) {
			$this->ChannelRating = $data;
		} elseif ($this->blCopyright) {
			$this->ChannelCopyright = $data;
		} elseif ($this->blPubDate) {
			$this->ChannelPubDate = $data;
		} elseif ($this->blLastBuild) {
			$this->ChannelLastBuildDate = $data;
		} elseif ($this->blDocs) {
			$this->ChannelDocs = $data;
		} elseif ($this->blManagingEditor) {
			$this->ChannelManagingEditor = $data;
		} elseif ($this->blWebmaster) {
			$this->ChannelWebmaster = $data;
		}

		if ($this->blURL) {
			$this->ChannelImageUrl = $data;
		} elseif ($this->blWidth) {
			$this->ChannelImageWidth = $data;
		} elseif ($this->blHeight) {
			$this->ChannelImageHeight = $data;
		}

		if ($this->blName) {
			$this->TextInputName = $data;
		}

		if ($this->blSkipHours && $this->blHour) {
			$this->ChannelSkipHours[$this->ChannelSkipHoursCount] = $data;
			$this->ChannelSkipHoursCount++;
		}
		if ($this->blSkipDays && $this->blDay) {
			$this->ChannelSkipDays[$this->ChannelSkipDaysCount] = $data;
			$this->ChannelSkipDaysCount++;
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
		xml_set_object($xml_parser,&$this);
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, True);
		xml_set_element_handler($xml_parser,"_startElement","_endElement");
		xml_set_character_data_handler($xml_parser,"_dataHandler");

		if ($check_encoding) { $xmlData = $this->_checkenc($xmlData); }

		if (!xml_parse($xml_parser, $xmlData)) {
			$error = sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($xml_parser)),
				xml_get_current_line_number($xml_parser));
		}
//echo $error;
		xml_parser_free($xml_parser);
		return $error;
	}

// ########### end of XML/RDF code ###########



	function main($content,$conf)	{
		// we use USER_INT so that's not neccessary
		//$GLOBALS["TSFE"]->set_no_cache();


		// *************************************
		// *** getting configuration values:
		// *************************************
		$this->conf = $conf;

		$this->enableFields = $this->cObj->enableFields("tx_ccrdfnewsimport");
		$this->dontParseContent = $this->conf["dontParseContent"];

		$this->allowCaching = $this->conf["allowCaching"]?1:0;

			// If the current record should be displayed.
		if ($this->conf["displayCurrentRecord"])	{
			$item = $this->cObj->data;
		}
//debug($conf);
//debug($item);

		// *************************************
		// *** doing the things...:
		// *************************************

		if (is_array($item)) {
//$xmlData = $rdf->getrdf("http://freshmeat.net/backend/fm.rdf", 5);
//http://slashdot.org/slashdot.rdf

			if (!$this->allowCaching OR $item["bodytext"]=="" OR ($this->allowCaching AND (time()-$item["intervall"] > $item["tstamp"]))) {

				//initialize counters
				$this->ItemsCounter = 0;
				$this->ChannelSkipHoursCount = 0;
				$this->ChannelSkipDaysCount = 0;

					//get data from the net
				$xmlData = $this->getrdf($item["url"], 5);
				$error = $this->parse($xmlData, FALSE);

					// if there was an error we output the old cached data
				if (!$this->ItemsCounter OR $xmlData == "" OR $error != "") {

					$query = "UPDATE tx_ccrdfnewsimport SET lastError='" . addslashes($error) . "', errors='" . ($item["errors"]+1) . "' WHERE uid = (" .$item["uid"]. ")";
					$res = mysql(TYPO3_db,$query);
					echo mysql_error();
					$content.=$item["bodytext"];

				} else {

					$markerArray=array();

					$renderCode = $this->cObj->cObjGetSingle ($this->conf["renderObj"], $this->conf["renderObj."]);
					$renderItemCode = $this->cObj->cObjGetSingle ($this->conf["renderItemObj"], $this->conf["renderItemObj."]);

						// render the items fromthe rdf channel
					$renderedItems="";
					$count=0;
					while ($count < $this->ItemsCounter) {
						$itemMarkerArray=array();
						$itemMarkerArray["###CH_ITEM_URL###"] = $this->Items[$count]["Link"];
						$itemMarkerArray["###CH_ITEM_TITLE###"] = $this->Items[$count]["Title"];
						$itemMarkerArray["###CH_ITEM_DESCRIPTION###"] = $this->Items[$count]["Description"];
						$itemMarkerArray["###CH_ITEM_LINK###"] = "<a href=\"".$this->Items[$count]["Link"]."\">".$this->Items[$count]["Title"]."</a>";
						$count++;

						$renderedItems .= $this->cObj->substituteMarkerArray ($renderItemCode, $itemMarkerArray);
					}

						// set markers
					$markerArray["###CHANNEL_IMAGE###"]="";
					if ($cc)	{
						$markerArray["###CHANNEL_IMAGE###"] = $this->cObj->wrap(trim($this->getImageHTML()),$lConf["imageWrapIfAny"]);
					}
					$markerArray["###CHANNEL_TEXTINPUT###"] = $this->cObj->stdWrap($this->getInputHTML (),$lConf["input_stdWrap."]);

					$markerArray["###CHANNEL_TITLE###"] = $this->cObj->stdWrap($this->ChannelTitle,$lConf["chTitle_stdWrap."]);
					$markerArray["###CHANNEL_DESCRIPTION###"] = $this->cObj->stdWrap($this->ChannelDescription,$lConf["chDescription_stdWrap."]);
					$markerArray["###CHANNEL_RATING###"] = $this->cObj->stdWrap($this->ChannelRating,$lConf["chRating_stdWrap."]);
					$markerArray["###CHANNEL_COPYRIGHT###"] = $this->cObj->stdWrap($this->ChannelCopyright,$lConf["chcopyright_stdWrap."]);
					$markerArray["###CHANNEL_PUBDATE###"] = $this->cObj->stdWrap($this->ChannelPubDate,$lConf["chPubdate_stdWrap."]);
					$markerArray["###CHANNEL_LASTBUILD###"] = $this->cObj->stdWrap($this->ChannelLastBuildDate,$lConf["chLastbuild_stdWrap."]);

					$markerArray["###CHANNEL_ITEMS###"] = $this->cObj->stdWrap($renderedItems,$lConf["itemList_stdWrap."]);

					$rendered = $this->cObj->substituteMarkerArray ($renderCode, $markerArray);

						// write new content for caching
					if ($this->allowCaching) {
						$query = "UPDATE tx_ccrdfnewsimport SET bodytext='" . addslashes($rendered) . "', tstamp='".time()."' WHERE uid = (".$item["uid"].")";
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
					}
					$content .= $rendered;
				}

			} else {
//debug("used cached content");
					// use cached content
				$content .= $item["bodytext"];
			}
		}
		if($GLOBALS["TSFE"]->beUserLogin) {
			$content = "<table border=0><tr><td>".$content.'</td></tr><tr><td bgcolor="#ffcccc">'.$this->getEditPanel($this->cObj->data,"tx_ccrdfnewsimport").'</td></tr></table>';
		}
		return $content;
	}


	function getImageHTML ($hrefParams="", $imgParams="") {
		$tmpString = "<a href=\"$this->ChannelImageLink\" $hrefParams><img src=\"$this->ChannelImageUrl\" border=\"0\"";
		if ($this->ChannelImageWidth != "" && $this->ChannelImageHeight != "") {
			$tmpString .= " width=\"$this->ChannelImageWidth\" height=\"$this->ChannelImageHeight\"";
		}
		return $tmpString." $imgParams></a>";
	}

	function getInputHTML () { //#### needs improvement
		$tmpString = "";
		if ($this->TextInputLink != "") {
			$tmpString .= "<form action=\"$this->TextInputLink\" method=\"post\">";
			$tmpString .= "<strong>$this->TextInputTitle</strong><br>";
			$tmpString .= "<input type=\"text\" name=\"$this->TextInputName\">";
			$tmpString .= "</form>";
		}
		return $tmpString;
	}


	function getEditPanel($row,$tablename)	{
	
			// Create local cObj if not set:
		if (!is_object($this->new_cObj))	{
			$this->new_cObj = t3lib_div::makeInstance("tslib_cObj");
			$this->new_cObj->setParent($this->cObj->data,$this->cObj->currentRecord);
		}
		
			// Initialize the cObj object with current row
		$this->new_cObj->start($row,$tablename);
		
	
			// Setting TypoScript values in the $conf array. See documentation in TSref for the EDITPANEL cObject.
		$conf=Array();
		$conf["allow"] = "edit,new,delete,move,hide";
		
		$panel = $this->new_cObj->cObjGetSingle("EDITPANEL",$conf,"editpanel");
		
		return $panel;
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cc_rdf_news_import/pi1/class.tx_ccrdfnewsimport_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cc_rdf_news_import/pi1/class.tx_ccrdfnewsimport_pi1.php"]);
}

?>
