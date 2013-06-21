<?php

if ( !defined('PATH_tslib')) {
	define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));
	define('PATH_site', dirname(PATH_thisScript).'/');
	define('PATH_t3lib', PATH_site.'t3lib/');
	define('PATH_tslib', PATH_site.'tslib/');
}

require_once( t3lib_extMgm::extPath('bahag_photogallery').'class.tx_bahag_photogallery_base.php');

class tx_bahag_photogallery_default_flexdata extends tx_bahag_photogallery_base {
	function processDatamap_preProcessFieldArray($status, $table, $id, &$fieldArray, &$obj) {
		// Get flexform xml data and convert it to nested array
		$flexXml = $fieldArray['pi_flexform'];
		$flexArray = t3lib_div::xml2array( $flexXml);

		// Convert nested flexform array to flat array
		$this->pluginConf = $this->getFlexData( $flexArray);
		//$this->initGalleryConf( $this->pluginConf);
        debug( $this->pluginConf);
        return;

		if ( $this->pluginConf['refreshGalleryCache'] === 1) {
			if( strcmp( $this->pluginConf['galleryPath'], '')) {
				$this->rootGalleryPath = $this->pluginConf['galleryPath'];
			}

			// Generate array structure of gallery
			$this->genGalleryStruc();
			
			if ( is_array( $this->galleries)) {
				foreach ( $this->galleries as $galleyIndex => $galleryContent) {
					// Update gallery cache
					$this->createCache( $galleyIndex, $this->getThumbsConf(), $this->thumbsDirName);
					$this->createCache( $galleyIndex, $this->getLargeThumbsConf(), $this->largeThumbsDirName);
					$this->createCache( $galleyIndex, $this->getImagesConf(), $this->imgCacheDirName);
				}
			}
			
			// Reset `Clear Photo Gallery Cache` field back to 0
			$flexArray['data']['sDEF']['lDEF']['refreshGalleryCache'] = 0;
			$fieldArray['pi_flexform'] = t3lib_div::array2xml( $flexArray, '', 0,'T3FlexForms');
		}
	}
	
	/**
	 * Function to convert nested flexform array to flat array
	 *
	 * @access private
	 * @param array $flexArray	Array representation of xml flexform data
	 * @return array	Array containing flexform data in flat array
	 */
	 function getFlexData( $flexArray) {
	 	$piFlexForm = $flexArray;
		$flexData = array();
	 	
		foreach ( $piFlexForm['data'] as $sheet => $data ) {
			foreach ( $data as $lang => $value ) {
				foreach ( $value as $key => $val ) {
					$flexData[$key] = ( is_numeric( $val['vDEF']))
									  ? intval( $val['vDEF'])
									  : $val['vDEF'];
				}
			}
		}

		return $flexData;
	 }
}

?>
