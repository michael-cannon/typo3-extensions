<?php

	// Typo3 FE plugin class
	require_once(PATH_tslib.'class.tslib_pibase.php');

	// Developer API class
	require_once(t3lib_extMgm::extPath('api_macmade').'class.tx_apimacmade.php');

	class tx_newsshow_pi1 extends tslib_pibase {


		/***************************************************************
		 * SECTION 0 - VARIABLES
		 *
		 * Class variables for the plugin.
		 ***************************************************************/

		// Same as class name
		var $prefixId = 'tx_newsshow_pi1';

		// Path to this script relative to the extension dir
		var $scriptRelPath = 'pi1/class.tx_newsshow_pi1.php';

		// The extension key
		var $extKey = 'newsshow';

		// Upload directory
		var $uploadDir = 'uploads/tx_newsshow/';

		// Version of the Developer API required
		var $apimacmade_version = 1.9;





		/***************************************************************
		 * SECTION 1 - MAIN
		 *
		 * Functions for the initialization and the output of the plugin.
		 ***************************************************************/

		/**
		 * Returns the content object of the plugin.
		 *
		 * This function initialises the plugin "tx_newsshow_pi1", and
		 * launches the needed functions to correctly display the plugin.
		 *
		 * @param		$content			The content object
		 * @param		$conf				The TS setup
		 * @return		The content of the plugin.
		 * @see			setConfig
		 * @see			buildFlashCode
		 */
		function main($content,$conf) {

			// New instance of the macmade.net API
			$this->api = new tx_apimacmade($this);

			// Set class confArray TS from the function
			$this->conf = $conf;

			// Init flexform configuration of the plugin
			$this->pi_initPIflexForm();

			// Store flexform informations
			$this->piFlexForm = $this->cObj->data['pi_flexform'];

			// Set final configuration (TS or FF)
			$this->setConfig();

			// Build content
			$content = $this->buildFlashCode();

			// Return content
			return $this->pi_wrapInBaseClass($content);
		}

		/**
		 * Set configuration array.
		 *
		 * This function is used to set the final configuration array of the
		 * plugin, by providing a mapping array between the TS & the flexform
		 * configuration.
		 *
		 * @return		Void
		 */
		function setConfig() {

			// Mapping array for PI flexform
			$flex2conf = array(
				'news' => 'sDEF:news',
				'days' => 'sDEF:days',
				'darkdesign' => 'sDEF:dark',
				'show_last' => 'sDEF:show_last',
				'order_by' => 'sDEF:listOrderBy',
				'asc_desc' => 'sDEF:ascDesc',
				'category_mode' => 'sDEF:categoryMode',
				'category' => 'sDEF:categorySelection',
				'sideTop' => 'sSIDE:blog',
				'sideTopTitle' => 'sSIDE:blogTitle',
				'sideTopText' => 'sSIDE:blogText',
				'sideTopImage' => 'sSIDE:blogImage',
				'sideMiddle' => 'sSIDE:reader',
				'sideMiddleTitle' => 'sSIDE:readerTitle',
				'sideMiddleText' => 'sSIDE:readerText',
				'sideMiddleImage' => 'sSIDE:readerImage',
				'sideBottom' => 'sSIDE:bottom',
				'sideBottomTitle' => 'sSIDE:bottomTitle',
				'swfParams.' => array(
					'loop' => 'sFLASH:loop',
					'menu' => 'sFLASH:menu',
					'quality' => 'sFLASH:quality',
					'scale' => 'sFLASH:scale',
					'bgcolor' => 'sFLASH:bgcolor',
					'swliveconnect' => 'sFLASH:swliveconnect',
				),
				'playerParams.' => array(
					'timer' => 'sPLAYER:timer',
					'transition' => 'sPLAYER:transition',
					'random' => 'sPLAYER:random',
					'navigation' => 'sPLAYER:navigation',
				),
				'width' => 'sFLASH:width',
				'height' => 'sFLASH:height',
				'version' => 'sFLASH:version',
			);

			// Ovverride TS setup with flexform
			$this->conf = $this->api->fe_mergeTSconfFlex($flex2conf,$this->conf,$this->piFlexForm);

			$this->conf['swfParams.']['width'] = $this->conf['width'];
			$this->conf['swfParams.']['height'] = $this->conf['height'];
			$this->conf['swfParams.']['wmode'] = $this->conf['wmode'];
    		//$this->conf['width'] = 512;
			//$this->conf['height'] = 265;
			//$this->conf['wmode'] = 'opaque';
			// DEBUG ONLY - Output configuration array
			#$this->api->debug($this->conf,'MP3 Player: configuration array');
		}

		/**
		 * Returns the code for the flash file.
		 *
		 * This function creates the HTML code for the Macromedia Flash Plugin.
		 *
		 * @return		The complete HTML code used to display the flash file.
		 * @see			writeFlashObjectParams
		 */
		function buildFlashCode() {

			// Creating valid pathes for the player
			$swfPath = str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['jpgrotator']));

			$flVars['file'] = 'index.php?id=' . $GLOBALS['TSFE']->id
			                    . '&type=' . $this->conf['xmlPageId']
                                . '&side[top][uid]=' . $this->conf['sideTop']
                                . '&side[top][heading]=' . $this->conf['sideTopTitle']
                                . '&side[top][title]=' . $this->conf['sideTopText']
                                . '&side[top][image]=' . $this->conf['sideTopImage']
			                    . '&side[middle][uid]=' . $this->conf['sideMiddle']
			                    . '&side[middle][heading]=' . $this->conf['sideMiddleTitle']
                                . '&side[middle][title]=' . $this->conf['sideMiddleText']
                                . '&side[middle][image]=' . $this->conf['sideMiddleImage']
			                    . '&side[bottom]=' . $this->conf['sideBottom']
			                    . '&side[bottom_title]=' . $this->conf['sideBottomTitle']
			                    . '&main[blogsID]=' . $this->conf['blogsID'];

			$news = explode(',', $this->conf['news']);
			if(strlen($this->conf['news']) != 0 && array_search(-1, $news) === false)
			{
			    $flVars['file'] .= '&main[news]=' . $this->conf['news'];
			    if($this->conf['days'])
			        $flVars['file'] .= '&main[days]=' . $this->conf['days'];
			}
			else
			{
			    $flVars['file'] .= '&main[cat_mode]=' . $this->conf['category_mode'] .
			                        '&main[cat]=' . $this->conf['category'].
			                        '&main[sort]=' . $this->conf['order_by'].
			                        '&main[asc]=' . $this->conf['asc_desc'];

			    if($this->conf['days'])
			        $flVars['file'] .= '&main[days]=' . $this->conf['days'];
			    else
			        $flVars['file'] .= '&main[show_last]=' . $this->conf['show_last'];
			}
			// cbDebug( "flVars['file']", $flVars['file'] );

			$flVars['shuffle'] = $this->conf['playerParams.']['random'];
			$flVars['transition'] = $this->conf['playerParams.']['transition'];
			$flVars['rotatetime'] = $this->conf['playerParams.']['timer'];
			//$flVars['shownavigation'] = $this->conf['playerParams.']['navigation'];
			$flVars['backcolor'] = '0xffeeff';
			$flVars['lightcolor'] = '0x802F4B';
			$flVars['shownavigation'] = 'true';
			$flVars['showicons'] = 'false';
			$flVars['overstretch'] = 'true';
			$flVars['darkdesign'] = (isset($this->conf['darkdesign']))?(($this->conf['darkdesign'] == true)?'true':'false'):'false';

			foreach($flVars as $name=>$val)
			{
			    $res[] = $name.'='.urlencode($val);
			}

			// Add FlashVars param to TS
			$this->conf['swfParams.']['FlashVars'] = implode('&', $res);

			// Add movie param to TS
			$this->conf['swfParams.']['movie'] = $swfPath;

			// Storage
			$htmlCode = array();

			// Flash code
			$htmlCode[] = '<object style="margin-left:-10px;" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' . $this->conf['version'] . ',0,0,0" id="' . $this->prefixId . '" width="' . $this->conf['width'] . '" height="' . $this->conf['height'] . '" align="center">';
			$htmlCode[] = $this->writeFlashObjectParams();
			$htmlCode[] = '<embed src="' . $swfPath . '" FlashVars="' . $this->conf['swfParams.']['FlashVars'] . '" swliveconnect="' . $this->conf['swfParams.']['swliveconnect'] . '" loop="' . $this->conf['swfParams.']['loop'] . '" menu="' . $this->conf['swfParams.']['menu'] . '" wmode="'.$this->conf['wmode'].'" quality="' . $this->conf['swfParams.']['quality'] . '" scale="' . $this->conf['swfParams.']['scale'] . '" bgcolor="' . $this->conf['swfParams.']['bgcolor'] . '" width="' . $this->conf['width'] . '" height="' . $this->conf['height'] . '" name="' . $this->prefixId . '" align="top" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">';
			$htmlCode[] = '</embed>';
			$htmlCode[] = '</object>';

			// Return content
			return implode(chr(10),$htmlCode);
		}

		/**
		 * Returns param tags
		 *
		 * This function creates a param tag for each parameter specified in the
		 * setup field.
		 *
		 * @return		A param tag for each parameter.
		 */
		function writeFlashObjectParams() {

			// Storage
			$params = array();

			// Build HTML <param> tags from TS setup
			foreach($this->conf['swfParams.'] as $name => $value) {
				$params[] = '<param name="' . $name . '" value="' . $value . '">';
			}

			// Return tags
			return implode(chr(10),$params);
		}
	}

	/**
	 * XCLASS inclusion
	 */
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newsshow/pi1/class.tx_newsshow_pi1.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newsshow/pi1/class.tx_newsshow_pi1.php']);
	}
?>
