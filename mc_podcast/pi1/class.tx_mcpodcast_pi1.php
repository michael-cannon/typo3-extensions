<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Máximo Cuadros Ortiz (mcuadros@gmail.com)
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
 * Plugin 'Podcast RSS Feed' for the 'mc_podcast' extension.
 *
 * @author	Máximo Cuadros Ortiz <mcuadros@gmail.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_mcpodcast_pi1 extends tslib_pibase {
	var $prefixId = 'tx_mcpodcast_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_mcpodcast_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'mc_podcast';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	function main($content,$conf)	{
		$this->config=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$pidList=$this->pi_getPidList($this->cObj->data['pages'],$this->cObj->data['recursive']);
		if ( strlen($pidList) != 0 ) {
			$sqlPid = " pid IN (".$pidList.") AND ";
		}	

		$this->server = $_SERVER['HTTP_HOST'];	

		$ux_tx_ttnews = new ux_tx_ttnews;
		$this->drawHeader();	

                $tmp=explode("IN (",$this->cObj->enableFields('tt_news'));
                $tmp=explode(")",$tmp[1]);
		$sqlAccess =  " AND tx_mcpodcast_access IN (".$tmp[0].") ";
		if ( !is_numeric($this->config['limit']) ) { $this->config['limit']=10; }
		// MLC 20090108 access model changed in newer tt_news, so dropped for
		// now
		$query = "SELECT * FROM tt_news WHERE ".$sqlPid." tx_mcpodcast_mp3 != '' ".$this->cObj->enableFields("tt_news")." ORDER BY crdate DESC LIMIT ".$this->config['limit'];
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		while($res && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$row['audioInfo']= $ux_tx_ttnews->getId3("uploads/tx_mcpodcast/".$row['tx_mcpodcast_mp3']);
			$this->drawItem($row);
		}

	
		header('Content-type: text/xml; charset=UTF-8');	
		$this->drawFooter();

		echo utf8_encode(implode("\n",$this->output));
		exit();
	}
	function drawItem($row) {
		$this->output[]="\t\t<item>";
		$this->output[]="\t\t\t<title>".$row['title']."</title>";
		$this->output[]="\t\t\t<enclosure url=\"http://".$this->server."/".$row['audioInfo']['file']."\" length=\"".$row['audioInfo']['filesize']."\" type=\"".$row['audioInfo']['mime_type']."\" />";
		$this->output[]="\t\t\t<guid>http://".$this->server."/".$row['audioInfo']['file']."</guid>";
		$this->output[]="\t\t<pubDate>".date('r',$row['crdate'])."</pubDate >";

		if ( (int)$this->config['itunes'] == 1 ) {
			if ( strlen($row['audioInfo']['artist']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:author>".$row['audioInfo']['artist']."</itunes:author>";
			}
			if ( strlen($row['audioInfo']['title_player']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:subtitle>".$row['audioInfo']['title_player']."</itunes:subtitle>";
			}
			if ( strlen($row['short']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:summary>".substr($row['short'],0,4000)."</itunes:summary>";
			}
			if ( strlen($row['audioInfo']['playtime_string']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:duration>".$row['audioInfo']['playtime_string']."</itunes:duration>";
			}
			if ( strlen($row['keywords']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:keywords>".$row['keywords']."</itunes:keywords>";
			}
		}

		$this->output[]="\t\t</item>";

	}
	function drawHeader() {
		$this->output[]='<?xml version="1.0" encoding="UTF-8"?>';
		if ( (int)$this->config['itunes'] == 1 ) {
			$this->output[] = '<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">';
		} else {
			$this->output[] = '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
		}
		$this->output[] = "\t<channel>";
		// $this->output[]="\t\t<atom:link href=\"http://".$this->server."\" rel=\"self\" type=\"application/rss+xml\" />";

		if ( strlen($this->config['ttl']) != 0 ) { $this->output[]="\t\t<ttl>".$this->config['ttl']."</ttl>"; }
		if ( strlen($this->config['title']) != 0 ) { $this->output[]="\t\t<title>".$this->config['title']."</title>"; }
		if ( strlen($this->config['link']) != 0 ) { $this->output[]="\t\t<link>".$this->config['link']."</link>"; }
		if ( strlen($this->config['copyright']) != 0 ) { $this->output[]="\t\t<copyright>".$this->config['copyright']."</copyright>"; }
		if ( strlen($this->config['language']) != 0 ) { $this->output[]="\t\t<language>".$this->config['language']."</language>"; }
		if ( strlen($this->config['description']) != 0 ) { $this->output[]="\t\t<description>".$this->config['description']."</description>"; }

		if ( $this->config['itunes'] == 1 ) {
			if ( strlen($this->config['itunes.']['subtitle']) != 0 ) { $this->output[]="\t\t<itunes:subtitle>".$this->config['itunes.']['subtitle']."</itunes:subtitle>"; }
			if ( strlen($this->config['itunes.']['author']) != 0 ) { $this->output[]="\t\t<itunes:author>".$this->config['itunes.']['author']."</itunes:author>"; }
			if ( strlen($this->config['itunes.']['summary']) != 0 ) { $this->output[]="\t\t<itunes:summary>".$this->config['itunes.']['summary']."</itunes:summary>"; }
			if ( strlen($this->config['itunes.']['image']) != 0 ) { 
				$this->output[]="\t\t<itunes:image href=\"http://".$this->server."/".$this->config['itunes.']['image']."\" />";
			}	
			if ( strlen($this->config['itunes.']['explicit']) == 0 || $this->config['itunes.']['explicit'] == 0 ) { 
				$this->config['itunes.']['explicit']="No"; 
			} else {
				$this->config['itunes.']['explicit']="Yes";
			}
			$this->output[]="\t\t<itunes:explicit>".$this->config['itunes.']['explicit']."</itunes:explicit>"; 
			

			if ( strlen($this->config['itunes.']['author']) != 0 || strlen($this->config['itunes.']['email']) != 0  ) { 
				$this->output[]="\t\t<itunes:owner>";
				if ( strlen($this->config['itunes.']['author']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:name>".$this->config['itunes.']['author']."</itunes:name>";
				}
				if ( strlen($this->config['itunes.']['email']) != 0 ) { 
					$this->output[]="\t\t\t<itunes:email>".$this->config['itunes.']['email']."</itunes:email>";
				}
				$this->output[]="\t\t</itunes:owner>";

			}
			if ( strlen($this->config['itunes.']['category']) != 0 ) { 
				$tmp=explode("|",$this->config['itunes.']['category']);
				$i=0; $max=count($tmp); 
				if ( $max > 3 ) { $max=3; }
				while ($i < $max) {
					$this->output[]="\t\t<itunes:category text=\"".htmlentities($tmp[$i])."\"/>"; 
					$i++;
					}	
			}
		}


	}
	function drawFooter() {
		$this->output[] = "\t</channel>";
		$this->output[] = "</rss>";
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mc_podcast/pi1/class.tx_mcpodcast_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mc_podcast/pi1/class.tx_mcpodcast_pi1.php']);
}

?>
