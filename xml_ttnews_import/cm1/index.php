<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Schopfer Olivier (ops@wcc-coe.org)
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
 * xml_ttnews_import module cm1
 *
 * @author        Schopfer Olivier <ops@wcc-coe.org>
 * World Council of Churches - www.wcc-coe.org
 *
 * Plugin 'News feed import into tt_news' 
 * Most of the code taken from the 'cc_rdf_news_import' extension 
 * by René Fritz [r.fritz@colorcube.de]
 * www.colorcube.de - typo3lab.colorcube.de - www.typo3.info
 * The rdf parser is ripped from rdflib from Jason Williams, jason@nerdzine.net, (GPL license)
 *
 *
 *
 */



// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
// $BACK_PATH = 'D:/Typo3/typo3_src-3.8.0/typo3/';
// $PATH_tslib = 'D:/Typo3/typo3_src-3.8.0/typo3/sysext/cms/tslib/';
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:xml_ttnews_import/cm1/locallang.php');
#include ('locallang.php');
//require_once(PATH_tslib."class.tslib_pibase.php"); // ????? Olivier Schopfer
//require_once(PATH_tslib.'tslib_content.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');

//js functionality for many-many relationship for news item category
require_once(t3lib_extMgm::extPath('xml_ttnews_import').'cm1/NewsfeedItemsCategoriesHelper.class.php');

//js functionality for misc utility functions
require_once(t3lib_extMgm::extPath('xml_ttnews_import').'cm1/XMLImportUtilities.class.php');

        // ....(But no access check here...)
        // DEFAULT initialization of a module [END]
/**
* $Id: index.php,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $
*/
class tx_xmlttnewsimport_cm1 extends t3lib_SCbase {

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
        var $CurrentItemDate;

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
        
        var $content;


		/** 
		Provides functionality for many-many relationship for news item category.
		Instance of NewsfeedItemsCategoriesHelper. js.
		*/
		var $newsfeedItemsCategoriesHelper;

      
		/** Constructor
		@author Jaspreet Singh
		*/
		function tx_xmlttnewsimport_cm1() {
			
			$this->newsfeedItemsCategoriesHelper = new NewsfeedItemsCategoriesHelper();
			
		}
		
		/**
         * Adds items to the ->MOD_MENU array. Used for the function menu selector.
         */
        function menuConfig()        {
                global $LANG;
                $this->MOD_MENU = Array (
                        'function' => Array (
                                '1' => $LANG->getLL('function1')
                                //'2' => $LANG->getLL('function2'),
                                //'3' => $LANG->getLL('function3'),
                        )
                );
                parent::menuConfig();
        }


        // get rdf/xml data from an url and return it
        function getrdf($url, $timeout) {
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

        function _writeItem() {
			$date = preg_replace( '#^[A-Za-z]+, #'
						, ''
						, preg_replace( '#\s+[A-Za-z]+$#'
							, '' 
							, trim( $this->ChannelPubDate )
					)
				);
			$desc = stripslashes( html_entity_decode(
						trim ( $this->CurrentItemDescription)
				) );

			$this->Items[$this->ItemsCounter] = array(
				"Title"=>stripslashes($this->CurrentItemTitle)
				, "Link"=>$this->CurrentItemLink
				, "Description"=>$desc
				, "Date"=>$date
			);
			$this->CurrentItemTitle="";
			$this->CurrentItemLink="";
			$this->CurrentItemDescription="";
			$this->CurrentItemDate="";
			$this->ItemsCounter++;
        }

        function _startElement($parser, $name, $attrs) {
                        //t3lib_div::devLog('XML Start element: '.$name, 'xml_ttnews_import');
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
                        } elseif ($name == "LASTBUILDDATE") {
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
                        //t3lib_div::devLog('XML End element: '.$name, 'xml_ttnews_import');
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
                        } elseif ($name == "LASTBUILDDATE") {
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
          //t3lib_div::devLog('XML data handler: '.$data, 'xml_ttnews_import');
          // $data = chop($data);
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
                xml_parser_set_option($xml_parser, XML_OPTION_TARGET_ENCODING, 'ISO-8859-1');
                
                xml_set_element_handler($xml_parser,"_startElement","_endElement");
                xml_set_character_data_handler($xml_parser,"_dataHandler");
                //t3lib_div::devLog('XML parser started', 'xml_ttnews_import');

                if ($check_encoding) { $xmlData = $this->_checkenc($xmlData); }

                if (!xml_parse($xml_parser, $xmlData)) {
                        //$error = sprintf("XML error: %s at line %d",
                        //        xml_error_string(xml_get_error_code($xml_parser)),
                        //        xml_get_current_line_number($xml_parser));
                        t3lib_div::devLog('XML parser error for '.$item['url'].': '.$error, 'xml_ttnews_import');
                }
                //echo $error;
                xml_parser_free($xml_parser);
                //t3lib_div::devLog('XML parser ended, result: '.$error, 'xml_ttnews_import');
                return $error;
        }

// ########### end of XML/RDF code ###########



        /**
         * Main function of the module.  
         */
        function main()        {
                global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
				
                //print_r($TYPO3_CONF_VARS);//TODO debug
				
				// Draw the header.
                t3lib_div::devLog('Started Main Module', 'xml_ttnews_import');
                $this->doc = t3lib_div::makeInstance('mediumDoc');
                $this->doc->backPath = $BACK_PATH;
                $this->doc->form='<form action="" method="POST">';

                // JavaScript
                $this->doc->JScode = '
                        <script language="javascript" type="text/javascript">
                                script_ended = 0;
                                function jumpToUrl(URL)        {
                                        document.location = URL;
                                }
                        </script>
                ';

                $this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
                $access = is_array($this->pageinfo) ? 1 : 0;
                //if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))        {
                        if ($BE_USER->user['admin'] && !$this->id)        {
                                $this->pageinfo=array('title' => '[root-level]','uid'=>0,'pid'=>0);
                        }

                        $headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

                        $this->content.=$this->doc->startPage($LANG->getLL('title'));
                        $this->content.=$this->doc->header($LANG->getLL('title'));
                        $this->content.=$this->doc->spacer(5);
                        $this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
                        $this->content.=$this->doc->divider(5);


                        // Render content:
                        $this->moduleContent();


                        // ShortCut
                        if ($BE_USER->mayMakeShortcut())        {
                                $this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
                        }
                //} else {
               //        t3lib_div::devLog('No proper access', 'xml_ttnews_import');
                //        $this->content.= 'No proper access';
                  
                //}
                $this->content.=$this->doc->spacer(10);
        }
        function printContent()        {

                $this->content.=$this->doc->endPage();
                echo $this->content;
        }

        function moduleContent()        {
                t3lib_div::devLog('Started ModuleContent', 'xml_ttnews_import');
                $content='<div align=center><strong>XML to tt_news importer</strong></div><BR>
                Results of "'.substr(t3lib_extMgm::extPath('xml_ttnews_import'),strlen(PATH_site)).'cm1/index.php" are written to tt_news records!
                <HR>';
                $this->content.=$this->doc->section('Import results:',$content,0,1);
                // And the actual call to the function
                $this->content.=$this->importRSS();
        } // moduleContent()

        
        function importRSS()        {
                // Adapted from rdsnewsimport main function

                // *************************************
                // *** setting configuration values:
                // *************************************
                $conf = array();
                $conf["dontParseContent"] = 0;
                $conf["allowCaching"] = 0;
                $conf["imageWrapIfAny"]="";
                $conf["display."]["date_stdWrap."]["strftime"] = '%A, %e. %B %Y';
                $conf["renderObj"]="COA";
                $conf["renderObj."]["10"]="HTML";
                $conf["renderObj."]["10."]["value"]= '<p class="mtni"><b>###CHANNEL_TITLE###</b></p> <p class="mtni">###CHANNEL_DESCRIPTION###</p> ###CHANNEL_ITEMS###'; 
                $conf["renderObj."]["10."]["value."]["wrap"]='<tr><td style="padding:8px">|</td></tr>';
                $conf["renderObj."]["10."]["value."]["wrap2"]='<table border=0 cellpadding=0 cellspacing=0 width=400><tr height=1><td bgcolor="#888888"><img src="clear.gif" height=1 width=1 border=0></td></tr>|<tr height=1><td bgcolor="#888888"><img src="clear.gif" height=1 width=1 border=0></td></tr></table><br>';
                $conf["renderItemObj"]='COA';
                $conf["renderItemObj."]["10"]='HTML';
                $conf["renderItemObj."]["10"]["value"]='<p class="st_list"><a class="light" href="###CH_ITEM_URL###" target="_blank">###CH_ITEM_TITLE###</a>&nbsp;###CH_ITEM_DATE###</p>';
                $conf["renderItemObj."]["20"]='HTML';
                $conf["renderItemObj."]["20."]["value"]='<p>###CH_ITEM_DESCRIPTION###</p>';
                
                $RSScontent = "";
                t3lib_div::devLog('Started RSS import.', 'xml_ttnews_import');
                //$this->conf = $conf;
                
                // $this->enableFields = $this->cObj->enableFields("tx_ccrdfnewsimport");
                $this->dontParseContent = $conf["dontParseContent"];
                
                // Olivier Schopfer 20.4.2005
                $lConf = $conf['display.'];

                $this->allowCaching = $conf["allowCaching"]?1:0;
                
                // Open current RSS records based on their pid matching the id of the current page
                $id = $_GET['id'];
                $fields = '*';
                $tables = 'tx_ccrdfnewsimport';
                $where = 'uid='.$id  //added check for enable fields js
						. ' AND hidden != 1 AND deleted != 1 ';
				// $this->cObj->enableFields("tx_ccrdfnewsimport"); doesn't work here
                $qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, '', '', '');
                $item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults); // we will only get the first record

//debug($conf);
//debug($item);

                // *************************************
                // *** doing the things...:
                // *************************************

                if (is_array($item)) {
                        t3lib_div::devLog('RSS descriptor opened successfully', 'xml_ttnews_import');
                        //initialize counters
                        $this->ItemsCounter = 0;
                        $this->ChannelSkipHoursCount = 0;
                        $this->ChannelSkipDaysCount = 0;

                        //get data from the net
                        $xmlData = $this->getrdf($item["url"], 5);
                        //t3lib_div::devLog('XML data for URL '.$item['url'].': '.$xmlData, 'xml_ttnews_import');
                        $error = $this->parse($xmlData, FALSE);

                        // if there was an error we output the old cached data
                        if (!$this->ItemsCounter OR $xmlData == "" OR $error != "") {

                                //$query = "UPDATE tx_ccrdfnewsimport SET lastError='" . addslashes($error) . "', errors='" . ($item["errors"]+1) . "' WHERE uid = (" .$item["uid"]. ")";
                                //$res = mysql(TYPO3_db,$query);
                                //echo mysql_error();
                                t3lib_div::devLog('No XML returned from URL '.$item['url'], 'xml_ttnews_import');
                                $RSScontent.='No XML returned from URL '.$item['url'].'<br>';

                        } else {
                                t3lib_div::devLog('Processing '.$this->ItemsCounter.' items', 'xml_ttnews_import');
                                $RSScontent.= $this->ItemsCounter.' items have been processed from stream:<br /><a href="'.$item['url'].'" target="_blank">'.$item['url'].'</a><br />';
                                $rendered = '<table border=1><tr><td><strong>title</strong></td><td><strong>unid</strong></td><td><strong>status</strong></td></tr>';

                                // render the items from the rdf channel and write them to tt_news records
                                $renderedItems="";
                                $count=0;
                                $xmlImportUtilities = new XMLImportUtilities();
                                while ($count < $this->ItemsCounter)
{
	// Set the news record with imported values
	// t3lib_div::devLog('Processing item n°'.$count, 'xml_ttnews_import');
	$RSScontentdebug .= "---" . $this->ItemsCounter . ": raw: " .  $this->Items[$count]["Date"] . "\n<br>strtotime: " . strtotime($this->Items[$count]["Date"]) . "\n<br> substr: " 
		. substr($this->Items[$count]["Date"],0,-9) . "\n<br>";
	$fixedLink 		= $xmlImportUtilities->fixGoogleNewsURL( $this->Items[$count]["Link"] );
	$fixedLink		= ( $fixedLink )
						? $fixedLink
						: preg_replace( '#[^[:alnum:]]#', ''
							, $this->Items[$count]["Title"] );
	$fixedLink 		= urldecode( $fixedLink );
			
	$pid = ($item['tx_xmlttnewsimport_targetpid'])?$item['tx_xmlttnewsimport_targetpid']:$item['pid'];  // By default, Id of current page
	
	// See if this record already exists in tt_news 
	$fields = 'uid'; //js only using this one field, so don't get *, just uid
	$tables = 'tt_news';
	$where = 'pid='.$pid
		." AND deleted=0 AND tx_xmlttnewsimport_xmlunid= '$fixedLink' ";
		//.substr( $this->Items[$count]["Link"], strpos($this->Items[$count]["Link"], "/index/")+7).'"';
	t3lib_div::devLog('searching tt_news record where '.$where, 'xml_ttnews_import');
	$debugQuery = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, '', '', '');
	//echo "see if record exists <br>\n"; echo  $debugQuery; //tmp dbg
	$nresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, '', '', '');
	//print_r($nresults);
	$nitem = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($nresults); // Should be only one record
	//print_r($nitem);
	//var_dump($nitem);
	//echo "blah";
	// - updated and rearranged the isItemNew logic. - js 
	$isItemNew = false;
	if (is_array($nitem)) { //if row exists, it's not a new item
		$isItemNew = false;
	} else {
		$isItemNew = true;
	}
	
	//js: We only put the fields here that should be changed upon import or re-import.
	//These fields get refreshed upon an UPDATE

	// MLC 20070713 internal or external news items
	// 0 is normal news  
	// 2 is external 
	$type						= ( $fixedLink )
									? 2
									: 0;
	$type						= 0;

	$newsRecord = array(
			'pid'=> $pid,
			'tstamp' => time(),
			//'crdate' => strtotime(substr($this->Items[$count]["Date"],0,-9)),
			//'datetime' => strtotime(substr($this->Items[$count]["Date"],0,-9)),
			//js doesn't work for some reason in the orig. xml_ttnews.  Changed to a simple strtotime without the substr
			'crdate' => strtotime($this->Items[$count]["Date"]),
			'datetime' => strtotime($this->Items[$count]["Date"]),
			'author' => $xmlImportUtilities->extractGoogleNewsAuthor($this->Items[$count]["Title"]),
			'type' => $type,
			//'ext_url' => $this->Items[$count]["Link"],
			'ext_url' =>  $fixedLink,
			'tx_xmlttnewsimport_xmlunid' =>  $fixedLink,

	);
	
	//The fields below are only added to $newsRecord if we are adding a new item (doing an INSERT).
	//If the newsitem is just being added, apply the hidden/deleted logic, as well as some other fields
	//that are likely to be edited. - js 
	if ($isItemNew) {
		$newItemFields = array(
			'title' => $xmlImportUtilities->fixGoogleNewsTitle($this->Items[$count]["Title"]),
			'bodytext' => $this->Items[$count]["Description"],
			'short' => $xmlImportUtilities->fixGoogleNewsDescription($this->Items[$count]["Description"]),
			'hidden' => '0', //tmp js
			'deleted' => $xmlImportUtilities->shouldItemBeDeletedUponImport($this->Items[$count]["Title"], $_GET['id'])
			);
		//merge these fields in with the newsRecord so they'll be INSERTed as well.
		$newsRecord = array_merge( $newsRecord, $newItemFields);
		
	} // Otherwise, the newsitem previously exists, so it'll be UPDATEd, but we
	//don't want nor need to change the hidden or deleted fields.  If they haven't been changed manually,
	//leave them be.  If they have been changed, we don't want to wipe out the changes. 
	
	if (!$isItemNew) {
			// update the existing record in tt_news
			unset($newsRecord['crdate']); // Don't change item's creation date
			unset($newsRecord['type']); // Don't change item's type (in case it has been further edited)
			$tmp = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($tables, $where, $newsRecord);
			if($tmp) {
					t3lib_div::devLog('tt_news record '.$this->Items[$count]["Unid"].' updated<br />', 'xml_ttnews_import');
					$rendered .= '<tr><td>'. $xmlImportUtilities->fixGoogleNewsTitle($this->Items[$count]["Title"]) .'</td><td>'.$newsRecord['tx_xmlttnewsimport_xmlunid'].'</td><td>updated</td></tr>';
			} else {
				   $RSScontent.= 'tt_news record '.$this->Items[$count]["Unid"].' NOT updated<br />';
					t3lib_div::devLog('tt_news record '.$this->Items[$count]["Unid"].' NOT updated<br />', 'xml_ttnews_import');
			}
	} else {
			// Create a new record in tt_news
			$tmp = $GLOBALS['TYPO3_DB']->exec_INSERTquery($tables, $newsRecord);

			//js take the categories associated with the newsfeed definition and apply them to
			//the news item we just brought in.
			//print_r( $item ); //debug
			//print_r( $nitem ); //debug
			$newsfeedID = $item['uid'];
			$newsItemID = empty($nitem['uid']) ? $GLOBALS['TYPO3_DB']->sql_insert_id() : $nitem['uid'];
			$this->newsfeedItemsCategoriesHelper->propagateNewsfeedCategoriesToItem($newsfeedID, $newsItemID);
			//echo "<br>\n \$newsfeedID $newsfeedID \$newsItemID $newsItemID \n" ; //debug

			if($tmp) {
					t3lib_div::devLog('Inserted new tt_news record '.$this->Items[$count]["Unid"], 'xml_ttnews_import');
					$rendered .= '<tr><td>'.$newsRecord['title'].'</td><td>'.$newsRecord['tx_xmlttnewsimport_xmlunid'].'</td><td>inserted</td></tr>';
			} else {
					$RSScontent.= 'Could not insert new tt_news record '.$this->Items[$count]["Unid"].'<br />';
					t3lib_div::devLog('Could not insert new tt_news record '.$this->Items[$count]["Unid"], 'xml_ttnews_import');
			}
	}
	
	$count++;
} // end while
        
                                $rendered.= '</tr></table><br><br>';
                                $RSScontent .= $rendered;
                        }
                } else {
                  t3lib_div::devLog("Couldn't open RSS stream", 'xml_ttnews_import');
                    $RSScontent .= "Couldn't open RSS stream" ;
                };
                //$RSScontent = "<table border=1><tr><td>".$RSScontent.'</td></tr><tr><td bgcolor="#ffcccc">'.$this->getEditPanel($this->cObj->data,"tx_ccrdfnewsimport").'</td></tr></table>';
                return $RSScontent;
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
} // End class  

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xml_ttnews_import/cm1/index.php'])        {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/xml_ttnews_import/cm1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_xmlttnewsimport_cm1');
$SOBE->init();

//echo "xmlImport";
$SOBE->main();
$SOBE->printContent();

?>
