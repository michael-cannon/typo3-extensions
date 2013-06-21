<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <http://www.ringer.it/>
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
 * Hook for the 'rgsendnews' extension.
 *
 * @author	Georg Ringer <http://www.ringer.it/>
 */
class tx_rgsendnews_fe {

	// hook for tt_news
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.	
		$this->pObj = &$pObj;

		// just works in single view and if activated
		if ($pObj->conf['rgsendnews']==1 && $pObj->config['code'] == 'SINGLE') {
			$this->myConf = $pObj->conf['rgsendnews.'];
					
			// create the language markers 
			$l = $GLOBALS['TSFE']->lang;
			$lang = t3lib_div::readLLXMLfile('typo3conf/ext/rgsendnews/pi1/locallang.xml',$l);
						
			$ll = array('ll_recname', 'll_recemail', 'll_sendemail', 'll_sendname', 'll_text', 'll_html', 'll_submit', 'll_reset', 'll_close', 'll_header', 'll_sendlink', 'll_captcha'  );
			foreach ($ll as $key=>$value) {
				$privateMarkerArray['###'.strtoupper($value).'###'] = $lang[$l][$value] ? $lang[$l][$value] : $lang['default'][$value];
   		}
   		
   		// captcha
      if (t3lib_extMgm::isLoaded('captcha') && $this->myConf['useCaptcha'])	{
        $privateMarkerArray['###CAPTCHAPICTURE###'] = '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="" />';
      } else {
        $subpartArray['###CAPTCHA###'] = '';
      }   		

			// create the action url 
			$actionUrlConf = array();
			$actionUrlConf['parameter'] = intval($this->myConf['sendPage']);
			$actionUrlConf['returnLast'] = 'url';
			$actionUrlConf['additionalParams'] = '&type=3421';
      $privateMarkerArray['###ACTIONURL###'] = $pObj->cObj->typolink('',$actionUrlConf);

      $privateMarkerArray['###NEWS_UID###'] = $row['uid'];
      $privateMarkerArray['###NEWS_URL###'] =  t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
      
      $template = $pObj->cObj->getSubpart($pObj->cObj->fileResource($this->myConf['templateFile']), '###TEMPLATE###');
      $send.= $pObj->cObj->substituteMarkerArrayCached($template,$privateMarkerArray, $subpartArray, array());


			// include mootools library if available or do it yourself if not
			if (t3lib_extMgm::isLoaded('t3mootools'))    {
				require_once(t3lib_extMgm::extPath('t3mootools').'class.tx_t3mootools.php');
			} 	 
			if (defined('T3MOOTOOLS')) {
				tx_t3mootools::addMooJS();
			} else {
				$header = '<script src="'.$this->getPath($this->myConf['pathToMootools']).'" type="text/javascript"></script>';
			} 
	
			// include rest of css + js files to the header
			$header.= ($this->myConf['pathToJS']) ? '<script type="text/javascript" src="'.$this->getPath($this->myConf['pathToJS']).'"></script>' : '';	
			$header.= ($this->myConf['pathToCSS']) ? '<link rel="stylesheet" type="text/css" href="'.$this->getPath($this->myConf['pathToCSS']).'" />' : '';	
			$GLOBALS['TSFE']->additionalHeaderData['rgsendnews'] = $header;
																														  
			$markerArray['###NEWS_SEND###'] = $send;
			
		} else {
			$markerArray['###NEWS_SEND###'] = '';
		}
		
			// add the additional markers
			$vars = t3lib_div::_POST('rgsn');
			if (is_array($vars)) {
				foreach ($vars as $key=>$value) {
					$markerArray['###SEND_'.strtoupper($key).'###'] = $value;
	   		}		
	   	}
		
		return $markerArray;
	}

	/**
	 * Gets the path to a file, needed to translate the 'EXT:extkey' into the real path
	 *
	 * @param	string  $path: Path to the file
	 * @return the real path
	 */
  function getPath($path) {
    if (substr($path,0,4)=='EXT:') {
      $keyEndPos = strpos($path, '/', 6);
      $key = substr($path,4,$keyEndPos-4);
      $keyPath = t3lib_extMgm::siteRelpath($key);
      $newPath = $keyPath.substr($path,$keyEndPos+1);
      return $newPath;
    }	else {
      return $path;
    }
  }	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgnewsce/class.tx_rgnewsce_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgnewsce/class.tx_rgnewsce_fe.php']);
}

?>
