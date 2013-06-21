<?php

class ux_tx_ttnews extends tx_ttnews {
	function getItemMarkerArray($row, $textRenderObj = 'displaySingle') {
		global $TYPO3_CONF_VARS;

		//Esta modificion solo funciona con esta variable a true
		if ( $TYPO3_CONF_VARS['MAX'] == true ) {
			if ($this->conf['useSPidFromCategory'] && is_array($this->categories)) {
				$tmpcats = $this->categories;
				$catSPid = array_shift($tmpcats[$row['uid']]);
			}
			$tmp=explode('|', $this->pi_linkTP_keepPIvars('|', array('tt_news' => $row['uid'], 'backPid' => ($this->conf['dontUseBackPid']?null:$this->config['backPid'])), $this->allowCaching, ($this->conf['dontUseBackPid']?1:0),  $catSPid['single_pid']?$catSPid['single_pid']:$this->config['singlePid']));		
			$tmp=explode('href="',$tmp[0]);
			$tmp=explode('"',$tmp[1]);
			$link = $tmp[0];

		}
		$markerArray = parent::getItemMarkerArray($row, $textRenderObj);
		$markerArray['###LINK_SRC###'] = $link;


		//Si no tiene fichero o el fichero no existe no hacemos nada
		if ( !$this->checkPodcastAccess($row['tx_mcpodcast_access']) || strlen($row['tx_mcpodcast_mp3']) == 0 || !is_file(getcwd()."/uploads/tx_mcpodcast/".$row['tx_mcpodcast_mp3']) ) {
			$markerArray['###PODCAST###'] ="";
			return $markerArray;
		}
		
		$audioData=$this->getId3("uploads/tx_mcpodcast/".$row['tx_mcpodcast_mp3']);
		// Acualizamos la db con la info del fichero
		$updateArray['tx_mcpodcast_infotxt']=$audioData['title_player'];
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$GLOBALS['TYPO3_DB']->UPDATEquery('tt_news', 'uid='.$row['uid'], $updateArray));
		
		// Recuperamos el html y remplazamos lo que se necesite
		$podcastHtml = $this->cObj->fileResource('EXT:mc_podcast/posdcast.tmpl');
		
		$markerArray['###PODCAST###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($podcastHtml, '###PODCAST_PLAYER###'), $this->addSharp($audioData)); 

		return $markerArray;
	}
	
	function checkPodcastAccess ($l) {
		$tmp=explode("IN (",$this->cObj->enableFields('tt_news'));
		$tmp=explode(")",$tmp[1]);
		$groups = explode(",",$tmp[0]);

		return in_array((int)$l,$groups);
	}

	function getId3($filename) {
		require_once(t3lib_extMgm::extPath('mc_podcast') . 'getid3/getid3.php');

		// Initialize getID3 engine
		$this->getID3 =	new getID3;

		// Analyze file and store returned data in $ThisFileInfo
		$ThisFileInfo = $this->getID3->analyze($filename);
		getid3_lib::CopyTagsToComments($ThisFileInfo);
	
		$out['file']		=$filename;
		$out['title']		=@implode(",",$ThisFileInfo['comments']['title']);	
		$out['artist']  	=@implode(",",$ThisFileInfo['comments']['artist']);	
		$out['album']		=@implode(",",$ThisFileInfo['comments']['album']);	
		$out['genre']		=@implode(",",$ThisFileInfo['comments']['genre']);	
		$out['playtime_string']	=$ThisFileInfo['playtime_string'];	
		$out['playtime_seconds']=$ThisFileInfo['playtime_seconds'];	
		$out['mime_type']	=$ThisFileInfo['mime_type'];	
		$out['filesize']	=$ThisFileInfo['filesize'];	
		$out['codec']		=ucfirst($ThisFileInfo['audio']['dataformat']);
		$out['channels']	=$ThisFileInfo['audio']['channels'];
		$out['channelmode']	=ucfirst($ThisFileInfo['audio']['channelmode']);
		$out['bitrate_mode']	=$ThisFileInfo['audio']['bitrate_mode'];
		$out['bitrate']		=intval($ThisFileInfo['audio']['bitrate']/1000) ."kbits/s";
		$out['flash_player']	=t3lib_extMgm::siteRelPath('mc_podcast').'musicplayer.swf';
		$out['title_player']	=$out['title'] ." - " . $out['artist'];
		$out['title_player_url']=urlencode($out['title_player']);

		return $out;
	}

	function addSharp($a) {
		$i=0; $max=count($a); $keys=array_keys($a); 
		while ($i < $max) {
			$o["###".strtoupper($keys[$i])."###"]=$a[$keys[$i]];
			$i++;
		}
		return $o;
	}
}	
