<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Chetan Thapliyal (chetan@srijan.in)
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
 * bahag_photogallery module cm1
 *
 * @author	Chetan Thapliyal <chetan@srijan.in>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:bahag_photogallery/cm1/locallang.php');
#include ('locallang.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once (t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_graphics.php');
require_once (t3lib_extMgm::extPath('bahag_photogallery').'classes/class.tx_bahag_photogallery_common.php');


	// ....(But no access check here...)
	// DEFAULT initialization of a module [END]


class tx_bahagphotogallery_cm1 extends t3lib_SCbase {
    var $prefixId = "tx_bahagphotogallery";
    
	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */

    /**
     * GET and POST variables
     *
     * @access private
     * @var array
     */
     var $GPVars;

    /**
     * Path to template file
     *
     * @access private
     * @var string
     */
     var $templateFile;

    /**
     * Variable to store current action for script
     *
     * @access private
     * @var string
     */
     var $cmd;

    /**
     * Object to `tx_bahag_photogallery_common` class
     * Contains generic functions
     *
     * @access private
     * @var class
     */
     var $utils;

    /**
     * Object to `tx_bahag_photogallery_graphics` class
     * Contains functions related to image processing
     *
     * @access private
     * @var class
     */
     var $graphics;


	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to 
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		
		// Initialize call variables
        $this->init();

		// Draw the header.
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="POST">';

		// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';

		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			if ($BE_USER->user['admin'] && !$this->id)	{
				$this->pageinfo=array('title' => '[root-level]','uid'=>0,'pid'=>0);
			}

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);

			// Hide function dropdown at the top right corner of the module
			//$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		}
		$this->content.=$this->doc->spacer(10);
	}
	
	/**
	 * Initialze class variables
	 *
	 * @access private
	 */
	function init() {
        // Get all the GET and POST variable in single array
		$this->GPVars = t3lib_div::_GET();
		$this->GPVars = array_merge( $this->GPVars, t3lib_div::_POST());
		
		// Assign template file location
		$this->templateFile = t3lib_extMgm::extPath('bahag_photogallery').'templates/iptc.htm';
		
		// Set IPTC tags to be used for Images
		$extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bahag_photogallery']);
        $this->reqIPTCDataItems = ( $extConf['requiredIptcData'] !== '')
                                  ? array_map( 'trim', explode( ',', $extConf['requiredIptcData']))
                                  : array();
        
        // Current action/command for script           
   		$this->cmd = $this->GPVars['cmd'];
   		
   		// Instance of library holding generic utility functions
   		$this->utils = t3lib_div::makeInstance( 'tx_bahag_photogallery_common');
   		
   		// Instance of image processing library
   		$this->graphics = t3lib_div::makeInstance( 'tx_bahag_photogallery_graphics');
	}
	
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	function moduleContent()	{

        switch ( $this->cmd) {
            case 'previewImg':
                $this->getPreviewImage( $this->GPVars['img']);
                
                break;
            case 'writeIPTC':
                $this->graphics->writeImgIPTCData( $this->GPVars['img'], $this->GPVars['tx_bahagphotogallery']['iptc']);
                
            default:
                // Get form to insert image IPTC data
                $content = $this->getAddIPTCPage( $this->GPVars['img']);
        }
        
        // Add/Edit section title to the content				
		$this->content.=$this->doc->section('Add/Edit Image IPTC Data:',$content,0,1);
	}
    
    /**
     * Function to get add IPTC page. 
     * This page inserts IPTC data in selected image.
     *
     * @access private
     * @param string    Path to image file
     */     
     function getAddIPTCPage( $image) {
        global $LANG;
         
        $IPTCData = '';

        $template = array();
        $template['all'] = file_get_contents( $this->templateFile);
        
        if ( $template['all'] !== '') {

             // If currently selected image supports IPTC data
             if ( $this->graphics->imgSupportsIPTC( $this->GPVars['img'])) {

                 // Extract template for IPTC data
                 $template['iptc'] = $this->utils->getSubTemplate( $template['all'], 'IMG_IPTC_DATA_TEMPLATE');
                 
                 // Obtain required IPTC data tags
                 $tags = $this->reqIPTCDataItems;
                 
                 // Get default values in case user is returning to the same page after submitting data
                 $defaultVals = $this->graphics->getImgIPTCData( $image, $tags);
                 
                 // Obtain sub-template for single IPTC data item
                 $template['iptcDataItem'] = $this->utils->getSubTemplate( $template['iptc'], 'IPTC_DATA_ITEM');
                 
                 if ( is_array( $tags) && !empty( $tags)) {
                     $IPTCDataItems = '';
                     
                     foreach ( $tags as $key => $value) {
                         $replace = array(
                         'tag' => $LANG->getLL($value),
                         'value' => '<input name="'.$this->prefixId.'[iptc]['.$value.']" type="text" value="'.$defaultVals[$value].'" size="24" />'
                         );
                         
                         $IPTCDataItems .= $this->utils->replaceTplMarkers( $template['iptcDataItem'], $replace);
                     }
                     
                     unset( $replace);
                     $IPTCData = $this->utils->replaceTplMarkers( $template['iptc'], array( 'IPTC_DATA_ITEM' => $IPTCDataItems), true);

                     // Set source of current image to embed after resizing
                     $imgUrl = $GLOBALS['TYPO3_MOD_PATH'].'index.php?cmd=previewImg&img='.urlencode( $this->GPVars['img']);
                     
                     $replace = array(
                     'IMAGE' => '<img src="'.$imgUrl.'" border="0" />',
                     'SUBMIT' => '<input type="submit" name="submit" value="Submit" />',
                     'HIDDEN' => '<input type="hidden" name="cmd" value="writeIPTC">
                                  <input type="hidden" name="img" value="'.$this->GPVars['img'].'">'
                     );
                     
                     $IPTCData = $this->utils->replaceTplMarkers( $IPTCData, $replace);
                     unset( $replace);
                 }
             } else {

                 // Display error if image doesn't support IPTC
                 $IPTCData = $this->utils->getSubTemplate( $template['all'], 'IPTC_NOT_SUPPORTED_TEMPLATE');
             }
         }
         
         
         return $IPTCData;    
    }
    
    /**
     * Function to get the preview of image for which
     * adding/editing IPTC data
     *
     * @access private
     */     
     function getPreviewImage( $image) {
        $inlineImg = '';
        
        // Clear output buffer
        ob_clean();

        // Get image information. This function is also responsible for checking whether
        // provided file is an image file or not.
        $imgInfo = $this->graphics->getImageInfo( $image);
            
        if ( is_array( $imgInfo) && !empty( $imgInfo)) {
            $inlineImg = $this->graphics->getInlineImageThumb( $image);

            // Set header for inline image
            header('Content-type: '. image_type_to_mime_type( $imgInfo[2]));
        }
        
        die( $inlineImg);
     }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bahag_photogallery/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/bahag_photogallery/cm1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_bahagphotogallery_cm1');
$SOBE->init();


$SOBE->main();
$SOBE->printContent();

?>
