<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Ritesh Gurung (ritesh@srijan.in)
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
 * Plugin 'Sponsor Content Scheduler' for the 'sponsor_content_scheduler' extension.
 *
 * @author	Ritesh Gurung <ritesh@srijan.in>
 * 
 */

require_once(PATH_tslib."class.tslib_pibase.php");
require_once(PATH_t3lib."class.t3lib_tcemain.php");
require_once(PATH_t3lib."class.t3lib_befunc.php");

class tx_sponsorcontentscheduler_base extends tslib_pibase{
	
	var $prefixId = "tx_sponsorcontentscheduler_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_sponsorcontentscheduler_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "sponsor_content_scheduler";	// The extension key.
	var $tablePrefix = 'tx_sponsorcontentscheduler_';
	var $tmpl_script;
	var $tb_user="fe_users";
	var $tb_consultancies = "tx_t3consultancies";
	var $__loggedInUserId;
	var $tb_highRes = "tx_sponsorcontentscheduler_highresimages";
	var $tb_Bulletin = "tx_sponsorcontentscheduler_bulletin";
	var $__loggedInSponsorId;
	var $bulletinUploadFolder;
	var $sponsorPageId;
	
	function init(){
		$script_path = t3lib_extMgm::siteRelPath($this->extKey).'/script.js';
		if (file_exists($script_path))
		{
			if ($fp = fopen($script_path, 'r'))
			{
				$this->tmpl_script = trim(fread($fp, filesize($script_path)));
				fclose($fp);
			}
		}
		$this->__loggedInUserId=$GLOBALS['TSFE']->fe_user->user['uid'];
		$this->__loggedInSponsorId = $this->__getSponsorRec();
		$this->bulletinUploadFolder = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/uploads/tx_sponsorcontentscheduler/bulletins/";
		$this->sponsorPageId = $this->conf['sponsorLoginPageId'];
	}
	
	function __uploadHighRes(){
		$data_row='';
		if($this->piVars['mode']=="del"){
			if(is_array($this->piVars['pic'])){
				foreach($this->piVars['pic'] as $uid){
					$updateField = array(
						'deleted' => 1,
					);
					$where = "$this->tb_highRes.uid = $uid";
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tb_highRes,$where,$updateField);
				}
			}
		}
		if($_FILES[$this->prefixId]['name']['pic']!=''){
			
			$image_name = $_FILES[$this->prefixId]['name']['pic'];
			$insertFields = array(
				"tstamp" => time(),
				"pic" => $image_name,
				"sponsor_id" => $this->__loggedInSponsorId,
				"fe_user_id" => $this->__loggedInUserId,
			);
			$destinationFolder = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/uploads/".substr($this->tablePrefix,0,-1)."/".$_FILES[$this->prefixId][name][pic];
			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tb_highRes, $insertFields);
			if($res){
				
				move_uploaded_file($_FILES[$this->prefixId][tmp_name][pic], $destinationFolder);
			}			
			// Redirect to the returning page
//			header("Location: ".t3lib_div::_POST('redirect_url'));
		}
		//Get data for images Upload
		$records=$this->__fetchImgUploadData();
		
		$typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;
		$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=upload_high_res";
		$actionUrl = $this->cObj->typoLink_URL($typolink_conf_main);
		$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=upload_high_res&".$this->prefixId."[mode]=del";
		$delUrl = $this->cObj->typoLink_URL($typolink_conf_main);
		
		$this->templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'templates/sponsor/imgUpload_highRes.html');
		
		$template['headers'] = $this->cObj->getSubpart($this->templateCode, '###UPLOFILEHEADER###');
		$template['dataheader'] = $this->cObj->getSubpart($this->templateCode, '###FILEUPLOADDATA###');
		$template['data'] = $this->cObj->getSubpart($template['dataheader'], '###FILEUPLOADDATAINNER###');
		$template['upload'] = $this->cObj->getSubpart($this->templateCode, '###FILEUPLOAD###');
		$markerArrupload['###ACTION###']=$actionUrl;
		$markerArrupload['###REDIRECTURL###']=(t3lib_div::_POST('redirect_url')=='')?t3lib_div::getIndpEnv('HTTP_REFERER'):t3lib_div::_POST('redirect_url');
		$markerArrupload['###EXTKEY###']=$this->prefixId;

		if (!empty($records)){
			foreach ($records as $rowArr){
				$__imgSiteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL')."uploads/".substr($this->tablePrefix,0,-1)."/".$rowArr['pic'];
				$__imgSiteLocation = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/uploads/".substr($this->tablePrefix,0,-1)."/".$rowArr['pic'];
				$mysock = getimagesize($__imgSiteLocation);
				$markerArrRec['###FILENAME###']="<a href='javascript:void(0)' onclick='newwindow=window.open(\"$__imgSiteUrl\",\"ImagePreview\",\"height=".$mysock[1].",width=".$mysock[0]."\");if (window.focus) {newwindow.focus()};'>".$rowArr['pic']."</a>";
				$markerArrRec['###DATED###']=date('m-d-Y',$rowArr['tstamp']);
				$markerArrRec['###EXTKEY###']=$this->prefixId;
				$markerArrRec['###UID###']=$rowArr['uid'];
				$data .= $this->cObj->substituteMarkerArrayCached($template['data'], $markerArrRec);
			}
		}
//		$this->_debug(t3lib_div::getIndpEnv('HTTP_REFERER'));
		
		$markerArrcontainer['###FILEUPLOADDATAINNER###'] = $data;
		$markerArr1['###ACTIONDELETE###'] = $delUrl;
		$markerArr1['###UPLOADFILECONTENT###'] = $this->cObj->substituteMarkerArrayCached($template['dataheader'],$markerArrcontainer,$markerArrcontainer);
		
		
		$content = $this->cObj->substituteMarkerArrayCached($template['headers'], $markerArr1);
		$content .= $this->cObj->substituteMarkerArrayCached($template['upload'], $markerArrupload);
		$typolink_conf_main['parameter'] = $this->sponsorPageId;
		$typolink_conf_main['additionalParams']='';
		$content .=$this->cObj->typoLink('<br/> Return to Main',$typolink_conf_main);
		return $content;
	}
	
	
	function __renderBulletin() {
	    if (empty($this->piVars['pic'])) {
	    	unset($this->piVars['pic']);
	    }
		if($this->piVars['insertData']){
			$this->__insertBulletin();
		}
		if($this->piVars['mode']){
			$this->__processBulletinMode();
		}
		$typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;
		$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=bulletin_conf_logo";
//		$package_list .= "<li>".$this->cObj->typoLink('Upload High-Res Logos',$typolink_conf_main)."</li>";
		$templateBulletin = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."/templates/sponsor/bulletin.html");
		//Template Render Section Starts
		$template['header'] = $this->cObj->getSubpart($templateBulletin, '###BULLETINHEADER###');
		$template['sample'] = $this->cObj->getSubpart($templateBulletin, '###SAMPLESPONSERSHIP###');
		$template['logoheader'] = $this->cObj->getSubpart($templateBulletin, '###LOGOHEADER###');
		$template['logodata'] = $this->cObj->getSubpart($template['logoheader'], '###LOGOHEADERDATA###');
		//Template Render Section Ends
		
		//Content Section
		$markerArray['###COMPANY_NAME###'] = $this->__getSponsorRec('title');
		$markerArray['###DESCRIPTION###'] = '';
		$markerArray['###LINK_LOCATION###'] = '';
		$markerArray['###LINK_TEXT###'] = '';
		if($this->__getBulletinRecords()!=''){
			$records = $this->__getBulletinRecords();
			$markerArray['###COMPANY_NAME###'] = ($records['company_name']=='')?$this->__getSponsorRec('title'):$records['company_name'];
			$markerArray['###DESCRIPTION###'] = $this->br2nl($records['description']);
			$markerArray['###LINK_LOCATION###'] = $records['link_location'];
			$markerArray['###LINK_TEXT###'] = $records['link_text'];
		}
		$markerArray['###UPLOADLINK###'] = $this->cObj->typoLink_URL($typolink_conf_main);
		$markerArray['###LOGOOPTION###'] = $this->__renderBulletinLogoList($template);
		$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=bulletin_conf";
		$markerArray['###ACTION###'] = $this->cObj->typoLink_URL($typolink_conf_main);
		
		$markerArray['###EXTKEY###'] = $this->prefixId;
		//Content Section Ends
		
		$content = $this->cObj->substituteMarkerArrayCached($template['header'], $markerArray);
		$content .= $this->__generateSampleBulletin($template['sample']);
		$typolink_conf_main['parameter'] = $this->sponsorPageId;
		$typolink_conf_main['additionalParams']='';
		$content .=$this->cObj->typoLink('<br/> Return to Main',$typolink_conf_main);
		return $content;
	}
	
	
	function __renderBulletinLogoList($template){
		$typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;
		$content='';
		$logoLocation = t3lib_div::getIndpEnv('TYPO3_SITE_URL')."/uploads/tx_sponsorcontentscheduler/bulletins/";
		if($this->__getBulletinRecords()!=''){
			$data_row='';
			$row = $this->__getBulletinRecords();
			if($row['pic']!=''){
				$imagesArr = explode(',',$row['pic']);
				foreach($imagesArr as $image){
					if($image!=''){
						$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=bulletin_conf&".$this->prefixId."[mode]=delLogo&".$this->prefixId."[logoId]=".md5($image);
						$largeThumbsConf = array(
								'width' => 200,
								'height' => 200,
						        'newImgPath' => t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/typo3temp/'.$image,
							);
						$imagePath = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/uploads/tx_sponsorcontentscheduler/bulletins/".$image;
						$imageFile = $this->transformImage( $imagePath, $largeThumbsConf);
						$mysock = t3lib_stdgraphic::getImageDimensions($logoLocation.$image);
						list($width, $height, $type, $attr) = @getimagesize($logoLocation.$image);
						/*$imageFile = $this->conf['image.'];
						$imageFile['file'] = $logoLocation.$image;
						$imageFile['file.']['maxW'] = 200;
						$imageFile['file.']['maxH'] = 200;*/
						$subMarkerArray['###EXTKEY###'] = $this->prefixId;
						$subMarkerArray['###DEFAULTPIC###'] = $image;
						$subMarkerArray['###CHECKED###'] = '';
						$subMarkerArray['###DELETEOPTION###'] = $this->cObj->typoLink('Delete',$typolink_conf_main);
						$subMarkerArray['###PIC###'] = "<img src = '".t3lib_div::getIndpEnv('TYPO3_SITE_URL').'/typo3temp/'.$image."'>";
	//					$subMarkerArray['###PIC###'] = $this->cObj->IMAGE($imageFile);
						if($image == $row['default_logo']){
							$subMarkerArray['###CHECKED###'] = 'Checked';
						}
						
						$data_row.= $this->cObj->substituteMarkerArrayCached($template['logodata'], $subMarkerArray);
					}
				}
				$markerArray['###LOGOHEADERDATA###'] = $data_row;
				$content = $this->cObj->substituteMarkerArrayCached($template['logoheader'], array(),$markerArray);
			}
			
		}
		if($content==''){
			$content = 'No Logo Uploaded';
		}
		return $content;
	}
	
	function __processBulletinMode(){
		switch($this->piVars['mode']){
			case 'delLogo':
				$fields = array(
					"$this->tb_Bulletin.pic" => $this->__delBulletinLogo()
				);
				$this->__updateBulletinRecords($fields);
				break;
		}
	}
	
	function __delBulletinLogo()
	{
			if($this->__getBulletinRecords()!=''){
				$row = $this->__getBulletinRecords();
				if($row['pic']!=''){
					$imagesArr = explode(',',$row['pic']);
					$__arrImages=array();
					foreach($imagesArr as $image){
						
						if(md5($image) == $this->piVars['logoId']){
							$__fileToDelete = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/uploads/tx_sponsorcontentscheduler/bulletins/".$image;
							$__fileToDeleteTemp = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/typo3temp/".$image;
							@unlink($__fileToDelete);
							@unlink($__fileToDeleteTemp);
						}else{
							$__arrImages[] = $image;
						}
					}
				}
			}
			return implode(',',$__arrImages);
	}
	
	function __renderBulletinLogo(){
		if($_FILES[$this->prefixId]['name']['pic']!=''){
			
			$image_name = $_FILES[$this->prefixId]['name']['pic'];
			$destinationFolder = $this->bulletinUploadFolder.$_FILES[$this->prefixId][name][pic];
			if($this->__getBulletinRecords()==''){
				$insertField['tstamp'] = time();
				$insertField['crdate'] = time();
				$insertField['sponsor_id'] = $this->__loggedInSponsorId;
				$insertField['pic'] = $image_name;
				$insertField['default_logo'] = $image_name;
				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tb_Bulletin,$insertField);
			}else{
				
				$row = $this->__getBulletinRecords();
				$where = "$this->tb_Bulletin.sponsor_id = '".$this->__loggedInSponsorId."'";
				if (!empty($row['pic'])) {
					$insertField['pic'] = $row['pic'].','.$image_name;
				} else {
				    $insertField['pic'] = $image_name;
				}
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tb_Bulletin,$where,$insertField);
			}
			
			if($res){
				
				move_uploaded_file($_FILES[$this->prefixId][tmp_name][pic], $destinationFolder);
				$this->piVars['default_logo'] = $image_name;
				$this->piVars['pic'] = $insertField['pic'];
			}			
		}
		
		$typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;
		$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=bulletin_conf_logo";
		$templateBulletin = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey)."/templates/sponsor/bulletin.html");
		$template['header'] = $this->cObj->getSubpart($templateBulletin, '###UPLOADLOGO###');
		$markerArray['###REDIRECTURL###']=t3lib_div::getIndpEnv('HTTP_REFERER');
		$markerArray['###ACTION###'] = $this->cObj->typoLink_URL($typolink_conf_main);
		$markerArray['###EXTKEY###'] = $this->prefixId;
		$content = $this->cObj->substituteMarkerArrayCached($template['header'], $markerArray);
		return $content;
	}
	
	function __insertBulletin(){
		foreach($this->piVars as $field=>$valueField){
			if(($field !="insertData") && ($field !="action")){
				$insertField[$field]=($field=="description")?strip_tags($valueField):$valueField;
				
			}
		}
		
		if($this->__getBulletinRecords()==''){
			$insertField['tstamp'] = time();
			$insertField['crdate'] = time();
			$insertField['sponsor_id'] = $this->__loggedInSponsorId;
			$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tb_Bulletin,$insertField);
		}else{
			$where = "$this->tb_Bulletin.sponsor_id = '".$this->__loggedInSponsorId."'";
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->tb_Bulletin,$where,$insertField);
		}
		
	}
	
	function __getSponsorRec($field=''){
		if($field ==''){
			$field = 'uid';
		}
		$sponsorName=($this->__getSponsorId($field)=='')?$this->__getSponsorById($field):$this->__getSponsorId($field);
		return $sponsorName;
	}
	function __getBulletinRecords(){
		$fields = "$this->tb_Bulletin.*";
		$table	= "$this->tb_Bulletin";
		$where	= "$this->tb_Bulletin.sponsor_id = '".$this->__getSponsorRec()."'";
		$result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where);
		if($result){
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		}
		return $row;
		
	}
	
	function __updateBulletinRecords($fields){
		$table	= "$this->tb_Bulletin";
		$where	= "$this->tb_Bulletin.sponsor_id = '".$this->__getSponsorRec()."'";
		$result=$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
	}
	
	
	
	function __getSponsorId($returnField=''){
		$table="$this->tb_user,$this->tb_consultancies";
		$columns="$this->tb_user.username,$this->tb_consultancies.uid,$this->tb_consultancies.title, $this->tb_consultancies.logo,$this->tb_consultancies.fe_owner_user" ;
		//$where="fe_users.uid='$user_id' AND fe_users.tx_sponsorcontentscheduler_sponsor_id=tx_t3consultancies.uid";
		$where="$this->tb_user.uid='$this->__loggedInUserId' AND $this->tb_consultancies.uid=$this->tb_user.tx_sponsorcontentscheduler_sponsor_id";
//			$this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($columns, $table, $where));
		$result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
		if($result){
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		}
		if($returnField==''){
			return $row['uid'];
		}else{
			return $row[$returnField];
		}
	}
	
	
	function __generateSampleBulletin($template){
		$content='';
		$logoLocation = t3lib_div::getIndpEnv('TYPO3_SITE_URL')."/typo3temp/";
		$logoLocationAbsolute = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/typo3temp/";
		if($this->__getBulletinRecords()!=''){
			$row = $this->__getBulletinRecords();
			
				foreach($row as $markerKey=>$markerVal){
				    if (strtoupper($markerKey) == 'DESCRIPTION') {
				    	$markerArray["###".strtoupper($markerKey)."###"] = nl2br($markerVal);
				    } else {
				    	$markerArray["###".strtoupper($markerKey)."###"] = $markerVal;
				    }
				}
				$markerArray['###IMAGEFILE###']='';
				if(file_exists($logoLocationAbsolute.$markerArray['###DEFAULT_LOGO###'])){
					$markerArray['###IMAGEFILE###'] = ($markerArray['###DEFAULT_LOGO###']!='')?"<IMG SRC='$logoLocation".$markerArray['###DEFAULT_LOGO###']."'>":'';
				}
				$markerArray['###COMPANY_NAME###']=($markerArray['###COMPANY_NAME###']=='')?$this->__getSponsorRec('title'):$markerArray['###COMPANY_NAME###'];
				$content = $this->cObj->substituteMarkerArrayCached($template, $markerArray);
			
		}
		return $content;
		
	}
	function __getSponsorById($returnField=''){
		$table="$this->tb_consultancies";
		$columns="$this->tb_consultancies.uid,$this->tb_consultancies.title, $this->tb_consultancies.logo";
		$where = "$this->tb_consultancies.tx_sponsorcontentscheduler_owner_id = $this->__loggedInUserId";
		$result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
		if($result){
			$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		}
//		$this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($columns, $table, $where));
		if($returnField==''){
			return $row['uid'];
		}else{
			return $row[$returnField];
		}
	}
	
	function __fetchImgUploadData(){
		$returnVal=array();
		$fields = "*";
		$table = "$this->tb_highRes";
		$where = "$this->tb_highRes.fe_user_id = $this->__loggedInUserId and $this->tb_highRes.sponsor_id = $this->__loggedInSponsorId and !$this->tb_highRes.deleted";
//		$where .= $this->cObj->enableFields($this->tb_highRes);

//		$this->_debug($GLOBALS['TYPO3_DB']->SELECTquery($fields, $table, $where));
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where);
		if($result){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$returnVal[] = $row;
			}
		}
		return $returnVal;
	}
	
	function br2nl($text)
	{
	   return  preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $text);
	}
	
	
	function transformImage( $imagePath, $conf) {

		$status = false;	// execution status of function

		if(is_array($conf) && !empty($conf)) {
			if($GLOBALS['TYPO3_CONF_VARS']['GFX']['image_processing'] == 1) {
				if($GLOBALS['TYPO3_CONF_VARS']['GFX']['im'] == 1) {
					$command = '';
					$options = '';

					$imPath = $this->get_im_path();
					$command = 'convert';

					if ( !empty( $conf['width']) || !empty( $conf['height'])) {
						if ( false !== ($actualImgDim = getimagesize( $imagePath))) {
							if ( !empty( $conf['width'])) {
								$scalingFactor = $conf['width'] / $actualImgDim[0];
								$conf['height'] = ceil( $actualImgDim[1] * $scalingFactor);
							} else {
								$scalingFactor = $conf['height'] / $actualImgDim[1];
								$conf['width'] = ceil( $actualImgDim[0] * $scalingFactor);
							}
						}

						$options .= ' -geometry '.$conf['width'].'x'.$conf['height'];
					}

					if ( !empty( $conf['grayscale'])) {
						$options .= ' -type grayscale ';
					}

					if ( !empty( $conf['quality'])) {
						$options .= ' -quality '.$conf['quality'];
					}

					if ( !empty( $conf['depth'])) {
						$options .= ' -depth '.$conf['depth'];
					}

					/*
					Changes by Suman:
					1. Removed the str_replace for spaces
					2. Used $sourceImg and $targetImg where not used earlier
					3. Corrected slashes to be system-independent
					*/
					$sourceImg = $imagePath;
					$targetImg = $conf['newImgPath'];
					$sourceImg = '"'.$imagePath.'"';
					$sourceImg = str_replace('/', DIRECTORY_SEPARATOR, $sourceImg);
					$targetImg = '"'.$conf['newImgPath'].'"';
					$targetImg = str_replace('/', DIRECTORY_SEPARATOR, $targetImg);
					$command = $imPath.$command.' '.$options.' '.$sourceImg;


					if ( !empty( $conf['watermarkImage'])) {
						$watermarkImgDim = getimagesize( $conf['watermarkImage']);
						$r1 = $conf['width'] / $conf['height'];
						$r2 = $watermarkImgDim[0] / $watermarkImgDim[1];

						if ( $r1 > $r2) {
							$scaledWMImgDim['width'] = ceil( $r2 * $conf['height']);
							$scaledWMImgDim['height'] = $conf['height'];
						} elseif ( $r1 > $r2) {
							$scaledWMImgDim['height'] = ceil( $r2 * $conf['width']);
							$scaledWMImgDim['width'] = $conf['width'];
						} else {
							$scaledWMImgDim['width'] = $conf['width'];
							$scaledWMImgDim['height'] = $conf['height'];
						}
						$command .= ' miff:- | '.$imPath.'composite -dissolve '.$conf['dissolvePercent'].' "'.$conf['watermarkImage'].'" -gravity center -geometry '.$scaledWMImgDim['width'].'x'.$scaledWMImgDim['height'].' -';
					}

					// $command .= ' miff:- | '.$imPath.'convert -frame 10x10+5+5 -mattecolor "#CCCCCC" -';
					$command .= ' '.$targetImg;

//					 echo $command.'<br><br>';

					// $objStdGraphics = new t3lib_stdGraphic();

					/*if( false !== $objStdGraphics->imageMagickExec( $sourceImg,$targetImg,$options)){
					$status = true;
					}*/

					if( false !== popen($command,'r')){
						$status = true;
					}
				} else {
					die('Imagemagick is not enabled.');
				}
			} else {
				die('Image processing is not enabled in Typo3.');
			}
		}

		return $status;
	}
	
	function get_im_path() {
		return $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'];

	}
	
	function downloadLeads()
    {
        global $TYPO3_DB;
        
        $res = $TYPO3_DB->sql_query(sprintf("SELECT title FROM tt_news WHERE uid = %s", $this->piVars['lead_id']));
        if ($res) {
        	$row = $TYPO3_DB->sql_fetch_assoc($res);
        	$filename = $row['title'];
        	$filename = strtolower($filename);
        	$filename = substr($filename, 0, 20);
        	$filename = preg_replace("#[^[:alnum:]]#", '-', $filename);
        }
        
        $filename = 'leads-'.$filename.'-'.$this->piVars['lead_id'].'-'.date('Ymd').'.xls';
        
        $members = $this->getLeadData();
		$htmlData = cbArr2Html($members, 'Leads Report', false, false);

        ini_set('zlib.output_compression', 'Off');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$filename.';');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen( $htmlData ));
		echo $htmlData;
		exit();
	}
	
	
	function downloadEvents()
    {
        global $TYPO3_DB;
        
        $res = $TYPO3_DB->sql_query(sprintf("SELECT title FROM tt_news WHERE uid = %s", $this->piVars['news_id']));
        if ($res) {
        	$row = $TYPO3_DB->sql_fetch_assoc($res);
        	$filename = $row['title'];
        	$filename = strtolower($filename);
        	$filename = substr($filename, 0, 20);
        	$filename = preg_replace("#[^[:alnum:]]#", '-', $filename);
        }
        
        $filename = 'participants-'.$filename.'-'.$this->piVars['news_id'].'-'.date('Ymd').'.xls';
        
		$members = $this->getEventData();
		$htmlData = cbArr2Html($members, 'Participants Report', false, false);

        ini_set('zlib.output_compression', 'Off');

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$filename.';');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen( $htmlData ));
		echo $htmlData;
		exit();
	}
	
    function getLeadData()
    {
        global $TYPO3_DB;
        $query = sprintf("
            SELECT
				  n.title                                 AS Document_Title,
	FROM_UNIXTIME(n.datetime, '%%M %%e, %%Y')             AS Document_Date,
	FROM_UNIXTIME(l.crdate,   '%%M %%e, %%Y %%l:%%i %%p') AS Lead_Date,
				  c.title                                 AS Sponsor,
				  u.first_name                            AS First_Name,
				  u.last_name                             AS Last_Name,
				  u.email                                 AS Email,
				  u.title                                 AS Title,
				  u.company                               AS Company,
				  u.address                               AS Address,
				  u.city                                  AS City,
				  u.zone                                  AS Zone,
				  u.zip                                   AS ZIP,
				 sc.cn_short_en                           AS Country,
				  u.telephone                             AS Phone,
				  u.fax                                   AS Fax
            FROM
                tx_newslead_leads                 l
                LEFT JOIN      fe_users                u ON             l.fe_user_id = u.uid
                LEFT JOIN      tt_news                 n ON                l.news_id = n.uid
                LEFT JOIN tx_t3consultancies      c ON n.tx_newssponsor_sponsor = c.uid
                LEFT JOIN static_countries       sc ON    u.static_info_country = sc.cn_iso_3
            WHERE
                l.hidden = 0
                AND l.deleted = 0
                AND l.leadsent = 1
                AND u.disable = 0
                AND u.deleted = 0
                AND n.hidden = 0
                AND n.deleted = 0
                AND n.uid IN (%s)
            GROUP BY
				u.email
			/*
                n.title ASC,
                FROM_UNIXTIME(l.date, '%%Y %%m') DESC
				*/
				",
                $this->piVars['lead_id']);
        $res = $TYPO3_DB->sql_query( $query );

        $members = array();

        if ($res && $data = $TYPO3_DB->sql_fetch_assoc($res))
        {
            // Headers
            $headers = array();
            foreach ($data as $key => $value)
                $headers[] = cbMkReadableStr($key);

            $members[] = $headers;

            // Data
            do {
                if (isset($data['address']))
                    $data['address'] = str_replace("\n" , ' ', $data['address']);
                $members[] = $data;
            } while ($data = $TYPO3_DB->sql_fetch_assoc($res));

            $TYPO3_DB->sql_free_result($res);
        }
        return $members;
    }
	
	
	function getEventData ()
	{
		$sql = "
			SELECT DISTINCT
				FROM_UNIXTIME(p.crdate, '%M %e, %Y %l:%i %p') date
				, u.first_name 
				, u.last_name 
				, u.email 
				, u.title 
				, u.company 
				, u.address 
				, u.city 
				, u.zone state 
				, u.zip 
				, sc.cn_iso_3 
				, sc.cn_short_en country 
				, u.telephone 
				, u.fax 
				, u.www
				, n.title event_title
			FROM tx_newseventregister_participants p
				INNER JOIN fe_users u ON p.fe_user_id = u.uid
				INNER JOIN tt_news n ON p.news_id = n.uid
				LEFT JOIN tx_t3consultancies c ON n.tx_newssponsor_sponsor = c.uid
				LEFT JOIN static_countries sc ON u.static_info_country = sc.cn_iso_3
			WHERE 1 = 1
				AND p.hidden = 0
				AND p.deleted = 0
				AND u.disable = 0
				AND u.deleted = 0
				AND n.hidden = 0
				AND n.deleted = 0
				AND p.news_id='".$this->piVars[news_id]."'
			ORDER BY p.crdate DESC
		";

		// query database
		$result					= $GLOBALS['TYPO3_DB']->sql_query( $sql );
		
		$members				= array();

		// push results into single rows CSV format
		if ( $result && $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $result ) )
		{
			// create headings
			$headers			= array_flip( array_keys( $data ) );
			unset( $headers[ 'cn_iso_3' ] );
			$headers			= array_flip( $headers );

			$headerCount		= count( $headers );

			for ( $i = 0; $i < $headerCount; $i++ )
			{
				$headers[ $i ]	= cbMkReadableStr( $headers[ $i ] );
			}

			$members[]			= $headers;

			// cycle through memeber results
			do
			{
				// cbPrint2( 'data', $data );

				// load zone name if one
//				$this->setZoneName( $data );

				// address needs new lines stripped out
				if ( isset( $data[ 'address' ] ) )
				{
					$data[ 'address' ]	= str_replace( "\n"
											, ' '
											, $data[ 'address' ]
										);
				}

				unset( $data[ 'cn_iso_3' ] );
				// push member onto members array
				$members[]				= $data;

				// once cycle complete, all done
			} while ( $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $result ) );

			// free up our result set
			$GLOBALS['TYPO3_DB']->sql_free_result( $result );
		}
		
		return $members;
	}
}

?>
