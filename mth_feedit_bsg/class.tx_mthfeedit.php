<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Morten Tranberg Hansen (mth@daimi.au.dk)
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
 * This is a API for crating and editing records in the frontend.
 * The API is build on top of fe_adminLib.
 * See documentation or extensions 'news_feedit' and 'joboffers_feedit' for examples how to use this API
 *
 * @author	Morten Tranberg Hansen <mth@daimi.au.dk>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_site.TYPO3_mainDir.'sysext/lang/lang.php');
require_once(PATH_t3lib.'class.t3lib_parsehtml_proc.php');
define(PATH_typo3,PATH_site.TYPO3_mainDir); // used in template.php
require_once(PATH_site.TYPO3_mainDir.'template.php');
if(t3lib_extmgm::isLoaded('rtehtmlarea')) require_once(t3lib_extMgm::extPath('rtehtmlarea').'pi2/class.tx_rtehtmlarea_pi2.php');
if(t3lib_extmgm::isLoaded('rlmp_dateselectlib')) require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');

class tx_mthfeedit {
  // Private fields
  var $prefixId = 'tx_mthfeedit';		// Same as class name
  var $scriptRelPath = 'class.tx_mthfeedit.php';	// Path to this script relative to the extension dir.
  var $extKey = 'mth_feedit';	// The extension key.

  var $conf;
  var $cObj; // contains an initialized cObj, so we can use the cObj functions whenever we want to.
  var $templateObj; // contains an initialized templateObj, so we can use the template functions whenever we want to.(ex with dynamic tabs)
  var $caller; // the caller
  var $table; // the table
  var $TCA; // contains the complete TCA of the $table
  var $cmd; // this is 'hopefully' the same value as fe_adminLib's cmd.
  var $id_field = ''; // the field from a record witch will identify the record.
  var $additionalJS_end = array(); // JS to be added after the end of the content.
#  var $LANG; // language object, used to translate 'complicated' label strings to the single label.


  // Fields for the RTE API
  var $strEntryField;

  var $RTEObj;
  var $docLarge = 0;
  var $RTEcounter = 0;
  var $formName;
  var $additionalJS_initial = '';// Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
  var $additionalJS_pre = array();// Additional JavaScript to be printed before the form (works in Mozilla/Firefox when included in head, but not in IE6)
  var $additionalJS_post = array();// Additional JavaScript to be printed after the form
  var $additionalJS_submit = array();// Additional JavaScript to be executed on submit
  var $PA = array(
		  'itemFormElName' =>  '',
		  'itemFormElValue' => '',
		  );
  var $specConf = array();
  var $thisConfig = array();
  var $RTEtypeVal = 'text';
  var $thePidValue;

  /**
   * [Put your description here]
   */
  function init($caller,$conf)	{
    $GLOBALS['TSFE']->set_no_cache();

    /**** SET PRIVATE VARS ****/
    $this->conf = $this->array_merge_recursive2($this->getDefaultConfArray(),$conf);
    $this->cObj = t3lib_div::makeInstance('tslib_cObj');
    $this->templateObj = t3lib_div::makeInstance('mediumDoc');
    $this->table = $conf["table"];
    $this->caller = $caller;

	$GLOBALS[$this->prefixId]['isOur'] = ($this->conf['pageRelation'] == $GLOBALS['TSFE']->page['uid']);

#    debug($this->conf);
    $this->cmd = (string)t3lib_div::_GP('cmd') ? (string)t3lib_div::_GP('cmd') : $this->conf['defaultCmd'];
    if (!$this->cmd) debug("WARNING:: NO COMMAND SPECIFIED FOR THE SCRIPT","NO COMMAND");

    $this->id_field = $GLOBALS["TCA"][$this->table]["ctrl"]["label"];

    // init $this->RTEObj if rtehtmlarea is availiable
    if(t3lib_extmgm::isLoaded('rtehtmlarea') && !$this->RTEObj)
      $this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');//&t3lib_BEfunc::RTEgetObj();//

    /**** LOAD LOCALLANG ****/
    // loads default locallang
    $this->LOCAL_LANG = $GLOBALS['TSFE']->readLLfile(t3lib_extMgm::extPath($this->extKey).'locallang.php');
    // loads callers locallang
    $this->LOCAL_LANG = t3lib_div::array_merge_recursive_overrule($this->LOCAL_LANG,$caller->LOCAL_LANG);
    // if LANG object is there, loads it with the frontend language from TSFE object
    if(is_object($GLOBALS['LANG'])) {
      $GLOBALS['LANG']->init($GLOBALS['TSFE']->lang);
    }

    /**** LOAD TCA_DESCR ****/
    if($this->conf['help']) {
      t3lib_extMgm::addLLrefForTCAdescr($this->table,$this->conf['help.']['file']);
      $GLOBALS['LANG']->loadSingleTableDescription($this->table);
      // set default values.
      $this->conf['help.']['type_keys'] = $this->conf['help.']['type_keys'] ? $this->conf['help.']['type_keys'] : 'description';
      $this->conf['help.']['window.']['width'] = intval($this->conf['help.']['window.']['width'])?intval($this->conf['help.']['window.']['width']):'300';
      $this->conf['help.']['window.']['height'] = intval($this->conf['help.']['window.']['height'])?intval($this->conf['help.']['window.']['height']):'40';
#      debug($GLOBALS['TCA_DESCR']);
    }

    /**** LOAD TCA ****/
    // <<< td; 21.03.2007; This code added for fulled support of news_feedit extension
    // function $GLOBALS['TSFE']->includeTCA() override $GLOBALS['TCA']['tt_news']['columns']['category']["config"]["foreign_table_where"] value
    // which needed for correctly working of "allowOnlyCategories" property
    $foreign_table_where = $GLOBALS['TCA']['tt_news']['columns']['category']["config"]["foreign_table_where"];
    $GLOBALS['TSFE']->includeTCA(); // THIS IS VERY IMPORTANT! If loaded here, it wont be loaded again and out extra TCA configurations will NOT be overriden
    if($GLOBALS[$this->prefixId]['isOur'] == true)
    {
    	if(!isset($GLOBALS['TCA']['tt_news']['columns']['category']["config"]["foreign_table_where"]))
    	    $GLOBALS['TCA']['tt_news']['columns']['category']["config"]["foreign_table_where"] = $foreign_table_where;
    	else
    	    $GLOBALS['TCA']['tt_news']['columns']['category']["config"]["foreign_table_where"] = $foreign_table_where.' '.$GLOBALS['TCA']['tt_news']['columns']['category']["config"]["foreign_table_where"];
    }
    // >>> td;

    t3lib_div::loadTCA($this->table);
    // Set private TCA var
    $this->TCA = &$GLOBALS["TCA"][$this->table];
#    debug($this->TCA,'TCA');

    /**** CONFIGURE TCA ****/
    $GLOBALS["TCA"][$this->table]["feInterface"]["fe_admin_fieldList"] = $this->conf['create.']['fields'] ? $this->conf['create.']['fields'].($this->conf['edit.']['fields']?','.$this->conf['edit.']['fields']:'') : $this->conf['edit.']['fields'];
    if($this->conf['fe_cruser_id'])
      $GLOBALS["TCA"][$this->table]['ctrl']['fe_cruser_id'] = $this->conf['fe_cruser_id'];
    if($this->conf['fe_crgroup_id'] && $this->conf['allowedGroups']) {
      $GLOBALS["TCA"][$this->table]['ctrl']['fe_crgroup_id'] = $this->conf['fe_crgroup_id'];
    }

#    debug($this->TCA);
#    debug(t3lib_div::_GP('FE'),'FE');
#    debug($_FILES,'$_FILES');
#    debug($this->conf);

    /**** Init language object (used for translation of labels) ****/
    $GLOBALS['TSFE']->initLLvars();

    /**** Init Robert Lemkes dateselectlib if it is loaded  ****/
    if(t3lib_extmgm::isLoaded('rlmp_dateselectlib')) tx_rlmpdateselectlib::includeLib();

    /**** DO ADDITIONAL REQUIRED STUFF FOR THE FIELDS ****/
    $fieldArray = explode(',',$this->conf[$this->cmd.'.']['show_fields']);
    foreach((array)$fieldArray as $fN) { //runs through the different fields
      // make sure --div-- is in allowed fields list
      $parts = explode(";",$fN);
      if($parts[0]=='--div--') {
	$this->conf[$this->cmd.'.']['fields'] .= $this->conf[$this->cmd.'.']['fields'] ? $this->conf[$this->cmd.'.']['fields'].','.$fN : $fN;
      }
      // do stuff according to type from TCA
      switch((string)$this->TCA['columns'][$fN]['config']['type']) {
      case 'group':
	if($this->TCA['columns'][$fN]['config']['internal_type']=='file') {
	  $this->conf[$this->cmd.'.']['fields'] .= ','.$fN.'_file'; // add the upload field to the allowed fields
	  $GLOBALS['TCA'][$this->table]['columns'][$fN.'_file']['config']['uploadfolder'] = $GLOBALS['TCA'][$this->table]['columns'][$fN]['config']['uploadfolder']; // the new upload field should have the same upload folder as the original field
	  $this->conf['parseValues.'][$fN.'_file'] = 'files['.ereg_replace(',',';',$this->TCA['columns'][$fN]['config']['allowed']).']['.$this->TCA['columns'][$fN]['config']['max_size'].']'; // adds the parse options for the new field, so it will be parsed as a file.
	}
      }
    }

    /**** CHECK IF LOGIN IS REQUIRED ****/
    if($this->conf['requireLogin'] && !$GLOBALS['TSFE']->loginUser) return $this->getLL("login_required_message");

    /**** FE ADMIN LIB ****/
    $feAConf = $this->conf;
    $feAConf["templateContent"]= $this->getDefaultTemplate(); // gets the default template
//    debug(array($feAConf["templateContent"]));


    $content = $this->cObj->cObjGetSingle('USER_INT',$feAConf);
    /**** ADDS THE REQUIRED JAVASCRIPTS ****/
    $content = $this->getJSBefore() . $content;
    $content .= $this->getJSAfter();

//    debug(array($content),'content');
    return $content;
  }

  /*
   * Default configurations for fe_adminLib
   */
  function getDefaultConfArray() {
    return array(
		 'userFunc' => 'user_feAdmin->init',
		 'includeLibs' => 'media/scripts/fe_adminLib.inc',
		 'userFunc_updateArray' => 'tx_mthfeedit->updateArray',
		 'evalFunc' => 'tx_mthfeedit->processDataArray',

		 'create' => 1,
		 'create.' => array(
				    'preview' => 1,
				    'userFunc_afterSave' => 'tx_mthfeedit->afterSave',
				    ),
		 'edit' => 1,
		 'edit.' => array(
				  'preview' => 1,
				  'menuLockPid' => 1,
				  'userFunc_afterSave' => 'tx_mthfeedit->afterSave',
				  ),
		 'delete' => 1,
		 'delete.' => array(
				  'preview' => 1,
				  ),
		 'setfixed' => 1,
		 'setfixed.' => array(
				      'approve.' => array(
							  'hidden' => 0,
							  '_FIELDLIST' => 'uid,pid',
							  ),
				      'DELETE' => 1,
				      'DELETE.' => array(
							  '_FIELDLIST' => 'uid,pid',
							  ),
				    ),

		 'no_cache' => 1,
		 'no_header' => 0,
		 'keep_piVars' => '',
		 'defaultCmd' => 'edit',
		 'infomail' => 0,
		 'required_marker' => '*',
		 'clearCacheOfPages' => $GLOBALS['TSFE']->id,
		 );
  }


  /**********************************************************************************************
   * TEMPLATE FUNCTIONS
   **********************************************************************************************/

  /**
   * Gets a default template made from the TCA.
   * The template there is returned depends on what $this->cmd is.
   */
  function getDefaultTemplate()	{
    $callerMethods = get_class_methods(get_class($this->caller));

    $template = array_search('getrequiredtemplate',$callerMethods) || array_search('getRequiredTemplate',$callerMethods)?
      $this->caller->getRequiredTemplate() : $this->getRequiredTemplate();
    $template .= array_search('getemailtemplate',$callerMethods) || array_search('getEmailTemplate',$callerMethods)?
      $this->caller->getEmailTemplate() : $this->getEmailTemplate();
    switch((string)$this->cmd) {
    case 'edit':
      $template .= array_search('getedittemplate',$callerMethods) || array_search('getEditTemplate',$callerMethods)?
	$this->caller->getEditTemplate() : $this->getEditTemplate();
      $template .= array_search('geteditmenutemplate',$callerMethods) || array_search('getEditMenuTemplate',$callerMethods)?
	$this->caller->getEditMenuTemplate() : $this->getEditMenuTemplate();
      break;
    case 'create':
      $template .= array_search('getcreatetemplate',$callerMethods) || array_search('getCreateTemplate',$callerMethods)?
	$this->caller->getCreateTemplate() : $this->getCreateTemplate();
      break;
    case 'delete':
      $template .= array_search('getdeletetemplate',$callerMethods) || array_search('getDeleteTemplate',$callerMethods)?
	$this->caller->getDeleteTemplate() : $this->getDeleteTemplate();
      break;
    case 'setfixed':
      $template .= array_search('getsetfixedtemplate',$callerMethods) || array_search('getSetfixedTemplate',$callerMethods)?
	$this->caller->getSetfixedTemplate() : $this->getSetfixedTemplate();
      break;
    default:
      debug('mth_feedit->getDefaultTemplate():: No template found for cmd='.$this->cmd,'No Template');
      $template = '';
    }
//    debug(array($template));
    return $template;
  }


  /**
   * Makes the form content from the TCA according to the configuration for the $cmd
   * @param	string		The cmd. Should be 'edit' or 'create'.
   */
  function makeHTMLForm($cmd)	{
    $fields = array_intersect( array_unique(t3lib_div::trimExplode(",",$this->conf[$cmd.'.']['show_fields'],1)) , array_unique(t3lib_div::trimExplode(",",$this->conf[$cmd.'.']['fields'],1)));
    $reqFields = array_intersect( array_unique(t3lib_div::trimExplode(",",$this->conf[$cmd.'.']["required"],1)) , array_unique(t3lib_div::trimExplode(",",$this->conf[$cmd.'.']['show_fields'],1)));

    $out_array = array();
    $out_sheet = 0;

#    debug($fields,'fields');
	while(list(,$fN)=each($fields))
	{
		$parts = explode(';',$fN);
		$fN = $parts[0];

		if($fN=='--div--')
		{
			if($this->conf["divide2tabs"])
			{
				$out_sheet++;
				$out_array[$out_sheet] = array();
				$out_array[$out_sheet]['title'] = $this->getLL($parts[1],'Tab '.$out_sheet);
			}
		}else{
#			debug(is_object($GLOBALS['TBE_TEMPLATE']));
			$fieldCode = $this->getFormFieldCode($cmd,$fN);
			if($fieldCode)
			{
				// NOTE: There are two ways to make a field required. The new way is to include 'required' in evalValues for a field. The old one is to have the the field in the required list.
				//       The new way take precedence over the old way. So if the new field has some evalValues, it makes no different if the field is in the required list or not.
				$feData=t3lib_div::_GP('FE');
				if($this->conf[$cmd.'.']['evalValues.'][$fN])
				{        // evalValues defined
					$msg = is_array($feData[$this->table]) ? '<div'.$this->caller->pi_classParam('form-error-field').'>###EVAL_ERROR_FIELD_'.$fN.'###</div>' : '';  // if no data is sent, no data is evaluated, and then no error msg should be displayed
					$reqMarker = in_array('required',t3lib_div::trimExplode(',',$this->conf[$cmd.'.']['evalValues.'][$fN])) ? $this->conf['required_marker'] : '';
				}elseif (in_array($fN,$reqFields)){	// No evalValues, but field listed in required list.
					$msg = '<!--###SUB_REQUIRED_FIELD_'.$fN.'###--><div'.$this->caller->pi_classParam('form-required-message').'>'.$this->getLL("required_message").'</div><!--###SUB_REQUIRED_FIELD_'.$fN.'###-->';
					$reqMarker = $this->conf['required_marker'];
				}else{
					$msg = '';
					$reqMarker = '';
				}
				$helpIcon = ($this->conf['help'] ? '<div'.$this->caller->pi_classParam('form-help-icon').'>'.$this->helpIcon($fN).'</div>' : '');

				$fieldLabel = $this->getLLFromLabel($this->TCA["columns"][$fN]["label"]);
				$out_array[$out_sheet][]='<div class="'.$this->caller->pi_getClassName('form-row').' '.$this->caller->pi_getClassName('form-row-'.$fN).'">
					<div class="'.$this->caller->pi_getClassName('form-label').' '.$this->caller->pi_getClassName('form-label-'.$fN).'">
					<span style="color:red">'.$reqMarker.'</span>
					'.$fieldLabel. '
					'.$helpIcon.'
					</div>
					<div'.$this->caller->pi_classParam('form-field').'>'.$fieldCode.'</div>
					'.$msg.'
					</div>';
			}
		}
	}

    if ($out_sheet>0) {	 // There were --div-- dividers around. Create parts array for the tab menu:
      $parts = array();
      foreach($out_array as $idx => $sheetContent)	{
	unset($sheetContent['title']);
	$parts[] = array(
			 'label' => $out_array[$idx]['title'],
			 'content' => '<table border="0" cellspacing="0" cellpadding="0" width="100%">'.
			 implode(chr(10),$sheetContent).
			 '</table>'
			 );
      }

      $content = $this->addCallersPiVars($this->templateObj->getDynTabMenu($parts, 'TCEforms:'.$table.':'.$row['uid']));
    } else {	        // Only one, so just implode:
      $content = is_array($out_array[$out_sheet]) ? $this->addCallersPiVars(implode(chr(10),$out_array[$out_sheet])) : 'makeHTMLForm() :: No form generated! (Proberly no fields defined in typoscript option show_fields)';
    }
    return $content;
  }

  /**
   * Returns a help icon for the field
   * @param	string		The field to get the help icon for
   * @param	boolean		The help icon with link to javascript popup, with help in.
   */
  function helpIcon($field) {
    //    debug($this->conf);
    $cshArray = $GLOBALS['TCA_DESCR'][$this->table]['columns'][$field];
    if(!is_array($cshArray)) return '';

    $label = $this->getLLFromLabel($this->TCA['columns'][$field]['label']);
    $fieldDescription = '<p'.$this->caller->pi_classParam('help-header').'>'.$label. '</p>';
    foreach(t3lib_div::trimExplode(',',$this->conf['help.']['type_keys'],1) as $type_key) {
      $fieldDescription .= $cshArray[$type_key] ? '<p'.$this->caller->pi_classParam('help-description').'>'.$cshArray[$type_key] . '</p>' : '';
    }

    if(empty($fieldDescription)) return '';

    else {
      //      $aOnClick = 'confirm(\''.$fieldDescription.'\');return false;';
      $fieldDescription = '<html><head><title>'.$label.'</title><style type="text/css">'.preg_replace("(\r)"," ",preg_replace("(\n)"," ",$this->conf['help.']['window.']['style'])).'</style></head><body'.$this->caller->pi_classParam('help-body').'><div'.$this->caller->pi_classParam('help-text').'>'.preg_replace("(\r)"," ",preg_replace("(\n)"," ",$fieldDescription)).'</div></body></html>';
      $script = '
      <script type="text/javascript">
      /*<![CDATA[*/
	function '.$this->caller->prefixId.'_helpWindow_'.$field.'() {
           top.vHWin=window.open(\'\',\'viewFieldHelpFE\',\'height='.$this->conf['help.']['window.']['height'].',width='.$this->conf['help.']['window.']['width'].',status=0,menubar=0,scrollbars=1\');
           top.vHWin.document.writeln(\''.$fieldDescription.'\');
           top.vHWin.document.close();
           top.vHWin.focus();
        }
      /*]]>*/
      </script>';

      require_once(PATH_t3lib . 'class.t3lib_iconworks.php');
      return
	$script.'<a href="#" onclick="'.htmlspecialchars($this->caller->prefixId.'_helpWindow_'.$field.'();return false;').'">'.
	'<img'.t3lib_iconWorks::skinImg('typo3/','gfx/helpbubble.gif','width="14" height="14"').' hspace="2" border="0" class="absmiddle"'.($GLOBALS['CLIENT']['FORMSTYLE']?' style="cursor:help;"':'').' alt="" />'.
	'</a>';
    }
  }

  /**
   * Makes a preview of the form content according to the configuration for the $cmd
   * @param	string		The cmd. Should be 'edit' or 'create' or 'all'.
   * @param	boolean		Should the output be wrapped in html or not.
   */
  function makeHTMLPreview($cmd, $withHTML = true) {
    $fields = (string)$cmd=='all' ? array_unique(t3lib_div::trimExplode(",",($this->conf['create.']['show_fields'] ? $this->conf['create.']['show_fields'].($this->conf['edit.']['show_fields']?','.$this->conf['edit.']['show_fields']:'') : $this->conf['edit.']['show_fields']))) : array_unique(t3lib_div::trimExplode(",",$this->conf[$cmd.'.']['show_fields']));
    $result = array();

    $hiddenFields = array();
    foreach((array)$fields as $fN) {

    	$parts = explode(';',$fN);
      if(($parts[0]=='--div--')) continue;


      $label = $this->getLLFromLabel($this->TCA['columns'][$fN]['label']);
      $fieldCode = $this->getPreviewFieldCode($cmd, $fN, $withHTML);

      // filter out empty fields or replace empty values by a default label (this label is not saved to DB))

      $feData = t3lib_div::_POST('FE');

      if(empty($feData[$this->table][$fN]) && ($this->TCA["columns"][$fN]["config"]["type"]=='input' || $this->TCA["columns"][$fN]["config"]["type"]=='text'))
      {
		$fieldCode = $this->getLL('no_value');                           // replace fieldcode with default label
		if($this->conf[$cmd.'.']['preview.']['dontDisplayEmptyFields'] || ($cmd == 'all' && ($this->conf['create.']['preview.']['dontDisplayEmptyFields'] || $this->conf['edit.']['preview.']['dontDisplayEmptyFields'])))
		{
			continue;

		}
      }

      if(!$withHTML) {
	$result[] = $label.chr(10).$fieldCode.chr(10);
      } else {
	$result[] = '<div'.$this->caller->pi_classParam('preview-row').'>
                     <div class="'.$this->caller->pi_getClassName('preview-label').' '.$this->caller->pi_getClassName('preview-label-'.$fN).'">
                       <strong>'.$label.'</strong>
                     </div>
                     <div class="'.$this->caller->pi_getClassName('preview-value').' '.$this->caller->pi_getClassName('preview-value-'.$fN).'">
                     '.$fieldCode.'
                     </div>
                     </div>';
#       $hiddenFields[] = '<input type="hidden" name="FE['.$this->table.']['.$fN.']" />';
      }
    }

#    $result[] = ''.implode(chr(10),$hiddenFields);

    // Keep callers piVars
    if($withHTML)
      return $this->addCallersPiVars(implode(chr(10),$result));
    else
      return implode(chr(10),$result);
  }

  /**
   * A dummy methor for making a NON HTML preview of the form content according to the configurations for the $cmd
   * @param	string		The cmd. Should be 'edit' or 'create'.
   */
  function makeTEXTPreview($cmd) {
    return $this->makeHTMLPreview($cmd,false);
  }

  /**
   * Add callers piVars as hidden input fields to the result array
   * @param	string		The string to add piVars as input fields to
   * @result	string		The result string with added piVars input fields
   */
  function addCallersPiVars($result) {
    $keep_piVars = t3lib_div::trimExplode(',',$this->conf['keep_piVars']);
    foreach($keep_piVars as $piVar) {
      if(!empty($piVar))
	$result .= '<input type="hidden" name="'.$this->caller->prefixId.'['.$piVar.']" value="'.$this->caller->piVars[$piVar].'" />';
    }
    return $result;
  }

  /**
   * Gets the PREVIEW fieldcode for field ($fN) of the form. This depends on the fields type.
   * @param	string		The field to get the fieldcode for.
   * @param	boolean		Should the output be with html (input fields) or not.
   */
  function getPreviewFieldCode($cmd,$fN,$withHTML) {
#    debug('getPreviewFieldCode():: fN='.$fN.', cmd='.$cmd,'function call');
    $fieldName = 'FE['.$this->table.']['.$fN.']';
    $type = $this->TCA["columns"][$fN]["config"]["type"];
    $feData = t3lib_div::_POST('FE');

#    debug(intval($feData[$this->table]['datetime']),'datetime');
    switch((string)$type) {
    case "input":
      $evalValuesArr = t3lib_div::trimExplode(',',$this->conf[$cmd.'.']['evalValues.'][$fN]);
      $displayTwice = false;
      $isPassword = false;
      foreach((array)$evalValuesArr as $eval) {
	switch((string)$eval) {
	case 'twice':
	  $displayTwice = true;
	  break;
	case 'password':
	  $isPassword = true;
	  break;
	}
      }

      $values = '###FIELD_'.$fN.'###';
      $feData = t3lib_div::_POST("FE");
      // Format the values.                TODO: This only shows the date on a nice format if it is send to the page, not if it is from an overrideValue.
      if($isPassword) $values = '********';
      else if($this->TCA['columns'][$fN]["config"]["eval"]=='date' && !empty($feData[$this->table][$fN])) {
	$values = strftime("%e-%m-%Y",$feData[$this->table][$fN]);
      } else if($this->TCA['columns'][$fN]["config"]["eval"]=='datetime' && !empty($feData[$this->table][$fN])) {
	$values = strftime("%H:%M %e-%m-%Y",$feData[$this->table][$fN]);
      }

      if($displayTwice) {
	$fieldName_again = 'FE['.$this->table.']['.$fN.'_again]';
	return $withHTML?'<input type="hidden" name="'.$fieldName.'" /><input type="hidden" name="'.$fieldName_again.'" />'.$values:$values;
      } else {
	return $withHTML?'<input type="hidden" name="'.$fieldName.'" />'.$values:$values;
      }
      break;
    case "group":
      return $withHTML?'<input type="hidden" name="'.$fieldName.'" /><input type="hidden" name="FE['.$this->table.']['.$fN.'_file]" />###FIELD_'.$fN.'###,###FIELD_'.$fN.'_file###':'###FIELD_'.$fN.'###, ###FIELD_'.$fN.'_file###';
      break;
    case "select":
      $values = '###FIELD_'.$fN.'###';
      if($this->TCA['columns'][$fN]["config"]["foreign_table"]) {  // reference to elements from another table
	$label = $GLOBALS["TCA"][$this->TCA['columns'][$fN]["config"]["foreign_table"]]["ctrl"]["label"];
	$feData = t3lib_div::_POST('FE');
	if($feData[$this->table][$fN]) {
	  $uids = t3lib_div::trimExplode(',',$feData[$this->table][$fN]);
	  $orClause = '';
	  foreach($uids as $uid) $orClause .= $orClause ? 'OR uid LIKE \''.$uid.'\'' : 'uid = \''.$uid.'\'';
	  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->TCA['columns'][$fN]["config"]["foreign_table"],$orClause);
	  if($GLOBALS['TYPO3_DB']->sql_error()) debug($GLOBALS['TYPO3_DB']->sql_error(),'sql error');
	  $values = '';
	  while($resRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	    $values .= $values ? ', ' . $resRow[$label] : $resRow[$label];
	  }
	}
      } elseif($this->TCA['columns'][$fN]["config"]["items"]) {                // fixed items

	// get items array..
	$items = $this->TCA['columns'][$fN]["config"]["items"];
	if($this->TCA['columns'][$fN]["config"]["itemsProcFunc"]) {     // if itemsProcFunc is set to fill the items array
	  $options = '';
	  $params = $this->TCA['columns'][$fN];
	  $params['items'] = &$items;
	  t3lib_div::callUserFunction($this->TCA['columns'][$fN]["config"]["itemsProcFunc"], $params, $this);
	}

	// find the right label according to the value, compared to the $items
	$feData = t3lib_div::_POST('FE');
	if($feData[$this->table][$fN]) {
	  $vals = t3lib_div::trimExplode(',',$feData[$this->table][$fN]);
	  $values = '';
	  foreach($items as $item) {
	    if(!empty($item)) {
	      list($label,$val) = $item;
	      if(in_array($val,$vals)) {
		$values .= $values ? ', ' . $label : $label;
	      }
	    }
	  }
	}
      }
      return $withHTML?'<input type="hidden" name="'.$fieldName.'" />'.$values:$values;
      break;
    case "text":
      $values = '###FIELD_'.$fN.'###';
      $feData = t3lib_div::_POST('FE');
      if($feData[$this->table]['_TRANSFORM_'.$fN]) { // if rte output, we need to process it instead of parsing it through htmlspecialchar as the other values gets.
	$dataArr = $feData[$this->table];
	$dataArr = $this->rteProcessDataArr($dataArr, $this->table, $fN, 'db');
	$dataArr = $this->rteProcessDataArr($dataArr, $this->table, $fN, 'rte');
	$values = $withHTML ? $dataArr[$fN] : strip_tags($dataArr[$fN]);
      }
      return $withHTML?'<input type="hidden" name="'.$fieldName.'" />'.$values:$values;
      break;
    default:
      return $withHTML?'<input type="hidden" name="'.$fieldName.'" />###FIELD_'.$fN.'###':'###FIELD_'.$fN.'###';
      break;
    }
  }


  /**
   * Gets the fieldcode for field ($fN) of the form. This depends on the fields type.
   * @param	string		The cmd. Should be 'edit' or 'create'.
   * @param	string		The field to get the fieldcode for.
   */
  function getFormFieldCode($cmd,$fN)
  {
#    $this->TCA['columns']['title']["config"]["eval"] = 'date';
	$fieldName = 'FE['.$this->table.']['.$fN.']';
	$class = 'class="'.$this->caller->pi_getClassName('form-data-'.$fN).' '.$this->caller->pi_getClassName('form-data').'"';
	$defaultParams = ' name="'.$fieldName.'"'.$class;
	$type = $this->TCA["columns"][$fN]["config"]["type"];
#    debug($this->TCA['columns']);
	switch((string)$type)
	{
		case "input":
			$onChange = 'onBlur="feedit_'.$this->table.'_formGet('."'".$fieldName."','".$this->TCA['columns'][$fN]["config"]["eval"]."','".$is_in."','".$checkbox."','".$checkboxVal."','".$checkbox_off."');".'"';
			$evalValuesArr = t3lib_div::trimExplode(',',$this->conf[$cmd.'.']['evalValues.'][$fN]);
			$displayTwice = false;
			$isPassword = false;

			foreach((array)$evalValuesArr as $eval)
			{
				switch((string)$eval)
				{
					case 'twice':
						$displayTwice = true;
					break;
					case 'password':
						$isPassword = true;
					break;
				}
			}

			$type = 'text';
			if($isPassword)$type = 'password';
#			debug($this->TCA['columns'][$fN]['config'],'config '.$fN);
			if($displayTwice)
			{
				$fieldName_again = 'FE['.$this->table.']['.$fN.'_again]';
				$onChange_again = 'onBlur="feedit_'.$this->table.'_formGet('."'".$fieldName_again."','".$this->TCA['columns'][$fN]["config"]["eval"]."','".$is_in."','".$checkbox."','".$checkboxVal."','".$checkbox_off."');".'"';

				$this->additionalJS_end['feedit_'.$fN.'_set_data'] = 'feedit_'.$this->table.'_formSet('."'".$fieldName."','".$this->TCA['columns'][$fN]["config"]["eval"]."','".$is_in."','".$checkbox. "','".$checkboxVal."','".$checkbox_off."')".';';
				$this->additionalJS_end['feedit_'.$fN.'_again_set_data'] = 'feedit_'.$this->table.'_formSet('."'".$fieldName_again."','".$this->TCA['columns'][$fN]["config"]["eval"]."','".$is_in."','".$checkbox. "','".$checkboxVal."','".$checkbox_off."')".';';
				return '
					<input type="'.$type.'" name="'.$fieldName.'_feVal" '.$class.' maxlength="'.$this->TCA["columns"][$fN]["config"]["max"].'" '.$onChange.' />
					<input type="hidden" name="'.$fieldName.'" />
					<input type="'.$type.'" name="'.$fieldName_again.'_feVal" '.$class.' maxlength="'.$this->TCA["columns"][$fN]["config"]["max"].'" '.$onChange_again.' />
					<input type="hidden" name="'.$fieldName_again.'" />
				';
			}else{
				// add author name to create template
				$fieldValue = '';
				$onsubmitCode = '';
				if($fN == 'title')
				{
					$onsubmitCode = ' onchange="feedit_tt_news_formGet(\'FE[tt_news][author]\',\'trim\',\'\',\'\',\'\',\'\');feedit_tt_news_formGet(\'FE[tt_news][datetime]\',\'datetime\',\'\',\'\',\'\',\'\');"';
				}
				if($GLOBALS[$this->prefixId]['isOur'] && $fN=='author' && $cmd=='create')
				{
					$sql = "SELECT CONCAT(`fisrt_name`, ' ', `last_name`) as `name` FROM `fe_users` WHERE uid=".$GLOBALS['TSFE']->fe_user->user['uid'];
					$sql = "SELECT `name` FROM `fe_users` WHERE uid=".$GLOBALS['TSFE']->fe_user->user['uid'];
					$sqlResult = $GLOBALS['TYPO3_DB']->sql_query($sql);

					$fieldValue = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sqlResult);
					$fieldValue = " value='{$fieldValue['name']}'";
				}elseif($GLOBALS[$this->prefixId]['isOur'] && $fN=='datetime'){
					// date() returns format like this: 9:03 31-3-2007
					$fieldValue = ' value="'.date('G:i j-n-Y').'"';
				}else{
					$this->additionalJS_end['feedit_'.$fN.'_set_data'] = 'feedit_'.$this->table.'_formSet('."'".$fieldName."','".$this->TCA['columns'][$fN]["config"]["eval"]."','".$is_in."','".$checkbox. "','".$checkboxVal."','".$checkbox_off."')".';';
				}
				return '
					<input size="40" type="'.$type.'"'.$onsubmitCode.' name="'.$fieldName.'_feVal" id="'.$fieldName.'_feVal" '.$class.' maxlength="'.$this->TCA["columns"][$fN]["config"]["max"].'" '.$onChange.$fieldValue.' />'.'
					<input type="hidden" name="'.$fieldName.'" />'.
					// inserts button for rlmp_dateselectlib
					(
						t3lib_extmgm::isLoaded('rlmp_dateselectlib') && !empty($this->TCA['columns'][$fN]["config"]["eval"])
						? (
							is_int(array_search('date',t3lib_div::trimExplode(',',$this->TCA['columns'][$fN]["config"]["eval"])))
							? tx_rlmpdateselectlib::getInputButton($fieldName.'_feVal',array('calConf.'=>array('inputFieldDateTimeFormat'=>'%e-%m-%Y')))
							: (
								is_int(array_search('datetime',t3lib_div::trimExplode(',',$this->TCA['columns'][$fN]["config"]["eval"])))
	    						? tx_rlmpdateselectlib::getInputButton($fieldName.'_feVal',array('calConf.'=>array('inputFieldDateTimeFormat'=>'%H:%M %e-%m-%Y')))
								: ''
							)
						)
						: ''
					);
			}
			break;
		case "text":
			// Get the specialConf for the field. Placed in type array.
			$specialConf = $this->getFieldSpecialConf($this->table,$fN);

			/**** USE RTE OR NOT  ****/
			if(!empty($specialConf) && is_object($this->RTEObj) && $this->RTEObj->isAvailable())
			{   // use RTE
				$this->RTEcounter++;
				$this->formName = $this->table.'_form';
				$this->strEntryField = $fN;
				$this->PA['itemFormElName'] = $fieldName;
				$feData = t3lib_div::_POST('FE');
				$this->PA['itemFormElValue'] = $feData[$this->table][$fN];
				$this->specConf = $specialConf;

				$pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();

				# THIS WILL NOT WORK YET! mail sendt to author of rtehtmlarea STAN
				/*	if(is_array($pageTSConfig['RTE.']['default.']['FE.']) && is_array($pageTSConfig['RTE.']['config.'][$this->table.'.'][$fN.'.']['FE.'])) {
				$this->thisConfig = t3lib_div::array_merge_recursive_overrule($pageTSConfig['RTE.']['default.']['FE.'],
				$pageTSConfig['RTE.']['config.'][$this->table.'.'][$fN.'.']['FE.']);
				} else {*/
				$this->thisConfig = $pageTSConfig['RTE.']['default.']['FE.'];
				//	}

				$this->thePidValue = $GLOBALS['TSFE']->id;
				$RTEItem = $this->RTEObj->drawRTE($this,$this->table,$fN,$row=array(), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);
				return $RTEItem . '<div'.$this->caller->pi_classParam('rte-clearer').'></div>';
			}else{	// dont use RTE
				return '<textarea'.$defaultParams.' cols="'.$this->TCA["columns"][$fN]["config"]["cols"].'" rows="'.$this->TCA["columns"][$fN]["config"]["rows"].'" wrap="VIRTUAL"></textarea>';
			}
			break;
		case "check":
			if($this->TCA['columns'][$fN]['config']['cols']>1)
				debug("getFormFieldCode():: WARNING, checkbox have more cols, not implementet yet.");
			return '<input type="hidden" '.$defaultParams.' value="0"><input type="checkbox" '.$defaultParams.' value="1">';
			break;
		case "group":
			if($this->TCA['columns'][$fN]["config"]["internal_type"]=='file')
			{
				// fetch data from table
				$feData = t3lib_div::_POST('FE');
				$uid = $feData[$this->table]['uid'] ? $feData[$this->table]['uid'] : t3lib_div::_GET('rU');
				$rec = $GLOBALS['TSFE']->sys_page->getRawRecord($this->table,$uid);

				// make option tags from existing data.
				$options = "";
				foreach(explode(",",$rec[$fN]) as $opt)
					$options .= '<option value="'.$opt.'">'.$opt.'</option>';

				$result .= '<select size="'.$this->TCA['columns'][$fN]["config"]["size"].'" name="FE['.$this->table.']['.$fN.']_select" style="width:250px;">
					'.$options.'
					</select>
					<input type="hidden" name="'.$fieldName.'">
					<a onClick="feedit_manipulateGroup(\''.$fieldName.'\');return false;"><img border="0" src="typo3/gfx/group_clear.gif"></a>
				';

				#	unset($result);
				if($this->TCA['columns'][$fN]["config"]["maxitems"]>sizeof(explode(",",$rec[$fN])))
				{
					$result .= $this->TCA['columns'][$fN]["config"]["allowed"].'<input type="file" name="FE['.$this->table.']['.$fN.'_file][]" />';
				}
				return $result;
			}else{
				debug("getFormFieldCode()::GROUP TYPE 'DB' NOT SUPPORTET YET");
			}
			break;
		case "select":
			$feData = t3lib_div::_POST('FE');
			$uid = $feData[$this->table]['uid'] ? $feData[$this->table]['uid'] : t3lib_div::_GET('rU');
			$rec = $GLOBALS['TSFE']->sys_page->getRawRecord($this->table,$uid);

			if($this->TCA['columns'][$fN]["config"]["foreign_table"])	// reference to elements from another table
			{
				$label = $GLOBALS["TCA"][$this->TCA['columns'][$fN]["config"]["foreign_table"]]["ctrl"]["label"];

				$foreignTable = $this->TCA['columns'][$fN]["config"]["foreign_table"];
				$whereClause = $this->TCA['columns'][$fN]["config"]["foreign_table_where"];

				$storageAndSiteroot = $GLOBALS["TSFE"]->getStorageSiterootPids();
				$whereClause = str_replace('###CURRENT_PID###',intval($storageAndSiteroot["_STORAGE_PID"])/*intval($GLOBALS["TSFE"]->page['uid'])*/,$whereClause); // replaced with STORAGE_PID cause it makes more sense ;)

#				$whereClause = str_replace('###THIS_UID###',,$whereClause);
#				$whereClause = str_replace('###THIS_CID###',,$whereClause);
				$whereClause = str_replace('###STORAGE_PID###',intval($storageAndSiteroot["_STORAGE_PID"]),$whereClause);
				$whereClause = str_replace('###SITEROOT###',intval($storageAndSiteroot['_SITEROOT']),$whereClause);

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$foreignTable,'1=1 '.$this->cObj->enableFields($foreignTable).' '.$whereClause);
				if ($GLOBALS['TYPO3_DB']->sql_error())
					debug(array($GLOBALS['TYPO3_DB']->sql_error()),'getFormFieldCode()::field='.$fN);

				// gets uids of selected records.
				$uids = $this->getUidsOfSelectedRecords($rec,$fN,$this->table);
//				debug($uids,'uids');

				while($resRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
				{
					$selected = in_array($resRow["uid"],$uids)?"selected":"";
					$options .= '<option value="'.$resRow["uid"].'" '.$selected.'>'.$resRow[$label].'</option>';
				}
			}elseif($this->TCA['columns'][$fN]["config"]["items"]){	// fixed items
				// Get selected uids.
				$uids = array();
				if($feData[$this->table][$fN])	// from post var
				{
					$uids = explode(",",$feData[$this->table][$fN]);
				}elseif(!is_null($rec)){	// clean from DB
					$uids = explode(",",$rec[$fN]);
				} elseif($cmd=='create' && ($this->TCA['columns'][$fN]['config']['default'] || $this->conf[$cmd.'.']['defaultValues.'][$fN])) { // default value taken from mthfeedit typoscript or TCA
					$uids = explode(",",$this->conf[$cmd.'.']['defaultValues.'][$fN]?$this->conf[$cmd.'.']['defaultValues.'][$fN]:$this->TCA['columns'][$fN]['config']['default']);
				}

				$items = $this->TCA['columns'][$fN]["config"]["items"];
				$options = '<option value="0">-----</option>';

				if($this->TCA['columns'][$fN]["config"]["itemsProcFunc"]) {     // if itemsProcFunc is set to fill the select box
					$options = '';
					$params = $this->TCA['columns'][$fN];
					$params['items'] = &$items;
					t3lib_div::callUserFunction($this->TCA['columns'][$fN]["config"]["itemsProcFunc"], $params, $this);
				}


				foreach((array)$items as $key => $item) {
					$selected = in_array($item[1],$uids)?"selected":"";
					if($key!=0)
						$options .= '<option value="'.$item[1].'"'.$selected.'>'.$this->getLLFromLabel($item[0]).'</option>';
				}
			} else {	// unknown TCA config
				$options = '<option><em>Unknown TCA-configuration</em></option>';
			}

			if($this->TCA['columns'][$fN]["config"]["size"]) {
				$size = ' size="'.$this->TCA['columns'][$fN]["config"]["size"].'" ';

				if($this->TCA['columns'][$fN]["config"]["maxitems"]>1) {
					$size .= ' multiple ';
					$onChange = ' onBlur="feedit_manipulateMultipleSelect(\''.$fieldName.'\')" ';
					$hr = '<input type="hidden" name="'.$fieldName.'" value="'.implode(",",$uids).'">';
					$name = substr($name,0,-1).'_select" ';
				}
			}
			$row .= '<select '.$size.' '.$onChange.' name="FE['.$this->table.']['.$fN.']_select">
				'.$options.'
				</select>'.$hr;
			return $row;
			break;
	case "user":
		debug("getFormFieldCode():: user fields not implementet yet.");
	case "radio":
		debug("getFormFieldCode():: radio buttons not implementet yet.");
	case "flex":
		debug("getFormFieldCode():: flex fields not implementet yet.");
	default:
		debug("getFormFieldCode():: Unknown type (".$type.") with field ".$fN);
		return '<input type="text"'.$defaultParams.' />';
		break;
	}
	}



  function getEditMenuTemplate() {
    return '
<!-- ###TEMPLATE_EDITMENU### begin -->
	'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-editmenu').'">'.$this->getLL("edit_menu_header").'</h1>').'
	<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-editmenu').'">'.$this->getLL("edit_menu_description").'</div>
	<div'.$this->caller->pi_classParam('editmenu-list').'>
		<!-- ###ALLITEMS### begin -->
			<!-- ###ITEM### begin -->
				<div><a href="###FORM_URL###&rU=###FIELD_uid###&cmd=edit&backURL=###FORM_URL_ENC###'.rawurlencode('&cmd=edit').'">###FIELD_'.strtolower($this->id_field).'###</a></div>
			<!-- ###ITEM### end -->
		<!-- ###ALLITEMS### end -->
	</div>
	<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-editmenu').'"><div><a href="###FORM_URL###&cmd=create">'.$this->getLL("edit_menu_createnew_label").'</a></div></div>
<!-- ###TEMPLATE_EDITMENU### -->

<!-- ###TEMPLATE_EDITMENU_NOITEMS### begin -->
	'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-editmenu-noitems').'">'.$this->getLL("edit_menu_noitems_header").'</h1>').'
	<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-editmenu-noitems').'">'.$this->getLL("edit_menu_noitems_description").'</div>
	<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-editmenu-noitems').'"><div><a href="###FORM_URL###&cmd=create">'.$this->getLL("edit_menu_createnew_label").'</a></div></div>
<!-- ###TEMPLATE_EDITMENU_NOITEMS### -->
';
  }

  function getEditTemplate() {
    $HTMLFormEdit = $this->makeHTMLForm('edit');
    $HTMLPreviewEdit = $this->makeHTMLPreview('edit');

    if($this->conf['delete.']['preview']) {
      $deleteLink = '['.$this->conf['delete.']['preview'].']<div><a href="###FORM_URL###&cmd=delete&preview=1&backURL=###FORM_URL_ENC###&rU=###REC_UID###">'.$this->getLL("edit_delete_label").'</a></div>';
    } else {
      $deleteLink = '<div><a href="###FORM_URL###&cmd=delete&backURL=###FORM_URL_ENC###&rU=###REC_UID###" onClick="return confirm(\''.$this->getLL("edit_delete_confirm").'\');">'.$this->getLL("edit_delete_label").'</a></div>';
      $deleteLink = '<form action="###FORM_URL###&cmd=delete&backURL=###FORM_URL_ENC###&rU=###REC_UID###" onSubmit="return confirm(\''.$this->getLL("edit_delete_confirm").'\');"><input type="submit" value="'.$this->getLL("edit_delete_label").'" /></form>';
    }

    // <<< td
    $onSubmit = '';
    if($GLOBALS[$this->prefixId]['isOur'])$onSubmit = ' onsubmit="feedit_tt_news_formGet(\'FE[tt_news][author]\',\'trim\',\'\',\'\',\'\',\'\');feedit_tt_news_formGet(\'FE[tt_news][datetime]\',\'datetime\',\'\',\'\',\'\',\'\');"';
    // >>>

    if(!$this->conf['delete']) $deleteLink = '';
    return '
<!-- ###TEMPLATE_EDIT### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-edit').'">'.$this->getLL("edit_header_prefix").' "###FIELD_'.strtolower($this->id_field).'###"</h1>').'
'.($this->conf['text_in_top_of_form']?'<div'.$this->caller->pi_classParam('form-text').'>'.$this->cObj->stdWrap($this->conf['text_in_top_of_form'],$this->conf['text_in_top_of_form.']).'</div>':'').'
<div'.$this->caller->pi_classParam('form-wrap').'>
<form'.$onSubmit.' style="padding:10px" name="'.$this->table.'_form"  method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'" onsubmit="'.implode(';', $this->additionalJS_submit).'">
'.$HTMLFormEdit.'
<div'.$this->caller->pi_classParam('form-row').'>
   ###HIDDENFIELDS###
   <br /><input type="Submit" name="submit" value="'.($this->conf['edit.']['preview']?$this->getLL("edit_submit_label"):$this->getLL("edit_preview_submit_label")).'"'.$this->caller->pi_classParam('form-submit').'>
</div>
</form>

</div>
<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-edit').'">
'./*$deleteLink.*/'
<div style="margin:10px"><a href="###FORM_URL###&cmd=edit">'.$this->getLL("back_label").'</a></div>
</div>
<!-- ###TEMPLATE_EDIT### end-->

<!-- ###TEMPLATE_EDIT_PREVIEW### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-edit-preview').'">'.$this->getLL("edit_header_prefix").' "###FIELD_'.strtolower($this->id_field).'###"</h1>').'
'.($this->conf['text_in_top_of_preview']?'<div'.$this->caller->pi_classParam('preview-text').'>'.$this->cObj->stdWrap($this->conf['text_in_top_of_preview'],$this->conf['text_in_top_of_preview.']).'</div>':'').'
<div'.$this->caller->pi_classParam('preview-wrap').'>
<form name="'.$this->table.'_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
'.$HTMLPreviewEdit.'
<div'.$this->caller->pi_classParam('preview-row').'>
    ###HIDDENFIELDS###
    <br /><input type="Submit" name="doNotSave" value="'.$this->getLL("edit_preview_donotsave_label").'"'.$this->caller->pi_classParam('preview-donotsave').'>
    <input type="Submit" name="submit" value="'.$this->getLL("edit_preview_submit_label").'"'.$this->caller->pi_classParam('preview-submit').'>
</div>
</form>
</div>
<!-- ###TEMPLATE_EDIT_PREVIEW### end-->

<!-- ###TEMPLATE_EDIT_SAVED### begin-->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-edit-saved').'">'.$this->getLL("edit_saved_header").'</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-edit-saved').'">'.$this->getLL("edit_saved_message").'</div>
<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-edit-saved').'"><div><a href="###FORM_URL###&cmd=edit">'.$this->getLL("back_label").'</a></div></div>
<!-- ###TEMPLATE_EDIT_SAVED### end-->
';
  }


  function getCreateTemplate() {
    $HTMLFormCreate = $this->makeHTMLForm('create');
    $HTMLPreviewCreate = $this->makeHTMLPreview('create');

    return '
<!-- ###TEMPLATE_CREATE_LOGIN### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-create-login').'">'.$this->getLL("create_header_prefix").' '.$this->getLLFromLabel($this->TCA["ctrl"]["title"]).'</h1>').'
'.($this->conf['text_in_top_of_form']?'<div'.$this->caller->pi_classParam('form-text').'>'.$this->cObj->stdWrap($this->conf['text_in_top_of_form'],$this->conf['text_in_top_of_form.']).'</div>':'').'
<div'.$this->caller->pi_classParam('form-wrap').'>
<form style="padding:10px" name="'.$this->table.'_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'" onsubmit="'.implode(';', $this->additionalJS_submit).'">
'.$HTMLFormCreate.'
<div'.$this->caller->pi_classParam('form-row').'>
   ###HIDDENFIELDS###
   <br /><input type="Submit" name="submit" value="'.($this->conf['create.']['preview']?$this->getLL("create_submit_label"):$this->getLL("create_preview_submit_label")).'"'.$this->caller->pi_classParam('form-submit').'>
</div>
</form>

</div>
<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-create-login').'"><div><a href="###FORM_URL###&cmd=edit">'.$this->getLL("create_edit_link").'</a></div></div>
<!-- ###TEMPLATE_CREATE_LOGIN### end-->


<!-- ###TEMPLATE_CREATE_LOGIN_PREVIEW### begin-->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-create-login-preview').'">'.$this->getLL("create_header_prefix").' '.$this->getLLFromLabel($this->TCA["ctrl"]["title"]).'</h1>').'
'.($this->conf['text_in_top_of_preview']?'<div'.$this->caller->pi_classParam('preview-text').'>'.$this->cObj->stdWrap($this->conf['text_in_top_of_preview'],$this->conf['text_in_top_of_preview.']).'</div>':'').'
<div'.$this->caller->pi_classParam('preview-wrap').'>
<form style="padding:10px" name="'.$this->table.'_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
'.$HTMLPreviewCreate.'
<div'.$this->caller->pi_classParam('preview-row').'>
    ###HIDDENFIELDS###
    <br /><input type="Submit" name="doNotSave" value="'.$this->getLL("create_preview_donotsave_label").'"'.$this->caller->pi_classParam('preview-donotsave').'>
    <input type="Submit" name="submit" value="'.$this->getLL("create_preview_submit_label").'"'.$this->caller->pi_classParam('preview-submit').'>
</div>
</form>
</div>
<!-- ###TEMPLATE_CREATE_LOGIN_PREVIEW### end-->

<!-- ###TEMPLATE_CREATE### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-create').'">'.$this->getLL("create_header_prefix").' '.$this->getLLFromLabel($this->TCA["ctrl"]["title"]).'</h1>').'
'.($this->conf['text_in_top_of_form']?'<div'.$this->caller->pi_classParam('form-text').'>'.$this->cObj->stdWrap($this->conf['text_in_top_of_form'],$this->conf['text_in_top_of_form.']).'</div>':'').'
<div'.$this->caller->pi_classParam('form-wrap').'>
<form style="padding:10px" name="'.$this->table.'_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'" onsubmit="'.implode(';', $this->additionalJS_submit).'">
'.$HTMLFormCreate.'
<div'.$this->caller->pi_classParam('form-row').'>
   ###HIDDENFIELDS###
   <br /><input type="Submit" name="submit" value="'.($this->conf['create.']['preview']?$this->getLL("create_submit_label"):$this->getLL("create_preview_submit_label")).'"'.$this->caller->pi_classParam('form-submit').'>
</div>
</form>

</div>
<!-- ###TEMPLATE_CREATE### end-->


<!-- ###TEMPLATE_CREATE_PREVIEW### begin-->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-create-preview').'">'.$this->getLL("create_header_prefix").' '.$this->getLLFromLabel($this->TCA["ctrl"]["title"]).'</h1>').'
'.($this->conf['text_in_top_of_preview']?'<div'.$this->caller->pi_classParam('preview-text').'>'.$this->cObj->stdWrap($this->conf['text_in_top_of_preview'],$this->conf['text_in_top_of_preview.']).'</div>':'').'
<div'.$this->caller->pi_classParam('preview-wrap').'>
<form name="'.$this->table.'_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
'.$HTMLPreviewCreate.'
<div'.$this->caller->pi_classParam('preview-row').'>
    ###HIDDENFIELDS###
    <br /><input type="Submit" name="doNotSave" value="'.$this->getLL("create_preview_donotsave_label").'"'.$this->caller->pi_classParam('preview-donotsave').'>
    <input type="Submit" name="submit" value="'.$this->getLL("create_preview_submit_label").'"'.$this->caller->pi_classParam('preview-submit').'>
</div>
</form>
</div>
<!-- ###TEMPLATE_CREATE_PREVIEW### end-->

<!-- ###TEMPLATE_CREATE_SAVED### begin-->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-create-saved').'">'.$this->getLL("create_saved_header").'</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-create-saved').'">'.$this->getLL("create_saved_message").'</div>
<script type="text/javascript">
	setTimeout("redirect_to(\'###FORM_URL###\')", 2000);
	function redirect_to(url){document.location=url}
</script>
<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-create-saved').'">If the page does not automatically reload, please click <a href="###FORM_URL###">here</a></div>
<!-- ###TEMPLATE_CREATE_SAVED### end-->
';
  }

  function getDeleteTemplate() {
    return '
    <!-- ###TEMPLATE_DELETE_PREVIEW### begin-->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-delete-preview').'">'.$this->getLL("delete_preview_header").'</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-delete-preview').'">'.$this->getLL("delete_preview_message").'</div>
<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-delete-preview').'">
<div><a href="###FORM_URL###&cmd=delete&backURL=###FORM_URL_ENC###&rU=###REC_UID###">'.$this->getLL("delete_preview_delete_label").'</a></div>
<div><a href="###FORM_URL###&cmd=edit&rU=###REC_UID###&backURL=###BACK_URL###" >'.$this->getLL("delete_preview_dont_delete_label").'</a></div>
</div>
<!-- ###TEMPLATE_DELETE_PREVIEW### end-->

<!-- ###TEMPLATE_DELETE_SAVED### begin-->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-delete-saved').'">'.$this->getLL("delete_saved_header").'</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-delete-saved').'">'.$this->getLL("delete_saved_message").'</div>
<script type="text/javascript">
	setTimeout("redirect_to(\'###FORM_URL###\')", 2000);
	function redirect_to(url){document.location=url}
</script>
<div class="'.$this->caller->pi_getClassName('link').' '.$this->caller->pi_getClassName('link-create-saved').'">If the page does not automatically reload, please click <a href="###FORM_URL###">here</a></div>
<!-- ###TEMPLATE_DELETE_SAVED### end-->
';
  }


  function getSetfixedTemplate() {
    return '
<!-- ###TEMPLATE_SETFIXED_OK### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-setfixed-ok').'">Record Approved</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-setfixed-ok').'">Record ###FIELD_'.$this->id_field.'###, has been approved.<br/><br/>'.nl2br($this->makeTEXTPreview('all')).'</div>
<!-- ###TEMPLATE_SETFIXED_OK### end-->

<!-- ###TEMPLATE_SETFIXED_OK_DELETE### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-setfixed-ok-delete').'">Record Deleted</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-setfixed-ok').'">Record ###FIELD_'.$this->id_field.'###, has been deleted.<br/><br/>'.nl2br($this->makeTEXTPreview('all')).'</div>
<!-- ###TEMPLATE_SETFIXED_OK_DELETE### end-->

<!-- ###TEMPLATE_SETFIXED_FAILED### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-setfixed-failed').'">Setfixed failed!</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-setfixed-failed').'">Failed: May happen if you click the setfixed link a second time (if the record has changed since the setfixed link was generated this error will happen!)</div>
<!-- ###TEMPLATE_SETFIXED_FAILED### end-->
';
  }

  function getEmailTemplate() {
    return '
<!-- ###EMAIL_TEMPLATE_CREATE_SAVED### begin -->
<html>
<title>[Auto Generated Message] Your informations has been saved.</title>

	<!--###SUB_RECORD###-->
        You have submitted the following informations at '.t3lib_div::getIndpEnv('TYPO3_SITE_URL').':'.chr(10).'
	'.$this->makeHTMLPreview('all').'
	<!--###SUB_RECORD###-->
</html>
<!-- ###EMAIL_TEMPLATE_CREATE_SAVED### end-->

<!-- ###EMAIL_TEMPLATE_CREATE_SAVED-ADMIN### begin -->
<html>
<title>BSG: New author article submission</title>

	<!--###SUB_RECORD###-->
	'.$this->makeHTMLPreview('all').'
<br />

	Approve:
	###THIS_URL######FORM_URL######SYS_SETFIXED_approve###
<br />
<br />
	Delete:
	###THIS_URL######FORM_URL######SYS_SETFIXED_DELETE###
	<!--###SUB_RECORD###-->
</html>
<!-- ###EMAIL_TEMPLATE_CREATE_SAVED-ADMIN### end-->

<!-- ###EMAIL_TEMPLATE_EDIT_SAVED-ADMIN### begin -->
<html>
<title>BSG: Author article modification</title>

	<!--###SUB_RECORD###-->
	'.$this->makeHTMLPreview('all').'
<br />
	Approve:
	###THIS_URL######FORM_URL######SYS_SETFIXED_approve###
<br />
<br />
	Delete:
	###THIS_URL######FORM_URL######SYS_SETFIXED_DELETE###
	<!--###SUB_RECORD###-->
</html>
<!-- ###EMAIL_TEMPLATE_EDIT_SAVED-ADMIN### end-->



<!-- ###EMAIL_TEMPLATE_SETFIXED_DELETE### begin -->
Consultancy DELETED!

<!--###SUB_RECORD###-->
Record name: ###FIELD_'.$this->id_field.'###

Your entry has been deleted by the admin for some reason.

- kind regards.
<!--###SUB_RECORD###-->
<!-- ###EMAIL_TEMPLATE_SETFIXED_DELETE### begin -->



<!-- ###EMAIL_TEMPLATE_SETFIXED_approve### begin -->
Consultancy approved

<!--###SUB_RECORD###-->

Record name: ###FIELD_'.$this->id_field.'###

Your consultancy entry has been approved!

- kind regards.
<!--###SUB_RECORD###-->
<!-- ###EMAIL_TEMPLATE_SETFIXED_approve### begin -->
';
}


  function getRequiredTemplate() {
    return '
<!-- ###TEMPLATE_AUTH### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-auth').'">Authentication failed</h1>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-auth').'">Of some reason the authentication failed. </div>
<!-- ###TEMPLATE_AUTH### end-->

<!-- ###TEMPLATE_NO_PERMISSIONS### -->
'.($this->conf['no_header']?'':'<h1 class="'.$this->caller->pi_getClassName('header').' '.$this->caller->pi_getClassName('header-no-permissions').'">No permissions to edit record</h3>').'
<div class="'.$this->caller->pi_getClassName('message').' '.$this->caller->pi_getClassName('message-no-permissions').'">Sorry, you did not have permissions to edit the record.</div>
<!-- ###TEMPLATE_NO_PERMISSIONS### end-->
';
  }



  /**********************************************************************************************
   * FUNCTIONS CALLED FROM fe_adminLib
   **********************************************************************************************/


  /*
   * This function has the data array passed to it in $content.
   * Must return the data array again.
   * The function runs through the fields from fe_adminLibs dataArr and does som required processing for the different fields types.
   */
  function processDataArray($content,$conf) {
#    debug($content,'processDataArray content');
#    debug($conf,   'processDataArray conf');
    $fe_adminLib = &$conf['parentObj'];
    $dataArr = $content;
    $table = $fe_adminLib->theTable;
    foreach((array)$dataArr as $fN=>$value) {
      switch((string)$GLOBALS['TCA'][$table]['columns'][$fN]['config']['type']) {
      case 'input':
	// if evaltype is date or datetime and overrideValue is 'now' we transform it into the current timestamp + the int following 'now'.
	// Example: if override value is now+30 we transform it into a timestamp representing the day 30 days from today
	if(($GLOBALS['TCA'][$table]['columns'][$fN]['config']['eval']=='date' || $GLOBALS['TCA'][$table]['columns'][$fN]['config']['eval']=='datetime') &&
	   substr($fe_adminLib->conf[$fe_adminLib->cmdKey."."]["overrideValues."][$fN],0,3) == 'now') {
	  $dataArr[$fN] = time() + 24*60*60*intval(substr($fe_adminLib->conf[$fe_adminLib->cmdKey."."]["overrideValues."][$fN],3));
	}
	break;
      case 'text':
	$dataArr = $this->rteProcessDataArr($dataArr, $table, $fN, 'db');
	break;
      }
    }
    return $dataArr;
  }



  /*
   * This function has the value-array passed to it before the value array is used to construct the update-JavaScript statements in fe_adminLib.
   * Must return the value-array again.
   * The function runs through the fields from fe_adminLibs dataArr and does som required stuff for the different fields types
   */
  function updateArray($content,$conf) {
#    debug($content,'updateArray content');
#    debug($conf,'updateArray conf');
    $fe_adminLib = &$conf['parentObj'];
    $dataArr = $content;
    $table = $fe_adminLib->theTable;

    foreach((array)$dataArr as $fN=>$value) {
      switch((string)$GLOBALS['TCA'][$table]['columns'][$fN]['config']['type']) {
      case 'group':
	// we need to update the additional field $fN.'_file'.
	$fe_adminLib->additionalUpdateFields .= ','.$fN.'_file';
	break;
      case 'input':
	// if evaltype is date or datetime and defaultValue is 'now' we transform it into the current timestamp + the int following 'now'.
	// Example: if default value is now+30 we transform it into a timestamp representing the day 30 days from today
	if(($GLOBALS['TCA'][$table]['columns'][$fN]['config']['eval']=='date' || $GLOBALS['TCA'][$table]['columns'][$fN]['config']['eval']=='datetime') &&
	   substr($fe_adminLib->conf[$fe_adminLib->cmdKey."."]["defaultValues."][$fN],0,3) == 'now'/* && empty($dataArr[$fN])*/) {
	  $dataArr[$fN] = time() + 24*60*60*intval(substr($fe_adminLib->conf[$fe_adminLib->cmdKey."."]["defaultValues."][$fN],3));
	}
	break;
      case 'text':
	$dataArr = $this->rteProcessDataArr($dataArr, $table, $fN, 'rte');
	break;
      case 'select':
	$feData = t3lib_div::_POST('FE');
	$uid = $feData[$table]['uid'] ? $feData[$table]['uid'] : t3lib_div::_GET('rU');
	if($GLOBALS['TCA'][$table]['columns'][$fN]["config"]["foreign_table"] &&
	   $GLOBALS['TCA'][$table]['columns'][$fN]["config"]["MM"] &&
	   $uid) {

	  $dataArr[$fN] = implode(',',$this->getUidsOfSelectedRecords($dataArr,$fN,$fe_adminLib->theTable));
	}
	break;
      }
    }

	return $dataArr;
  }

  /*
   * This funtion is called after a record is saved in fe_adminLib.
   * The function runs through the fields from fe_adminLibs dataArr and does som required stuff for the different fields types
   */
  function afterSave($content,$conf)	{
#    debug($content,'content');
#    debug($conf,'conf');
#    debug($_FILES,'$_FILES');

    $fe_adminLib = &$conf['parentObj'];
    $dataArr = $fe_adminLib->dataArr;
    $table = $fe_adminLib->theTable;

    foreach((array)$dataArr as $fN=>$value) {
      switch((string)$GLOBALS['TCA'][$table]['columns'][$fN]['config']['type']) {
      case 'group':
	if($GLOBALS['TCA'][$table]['columns'][$fN]['config']['internal_type']=='file') { //internal_type=file
	  /**** DELETED FILES ****/
	  // if files are deleted in the field, we also want to make sure they are deleted on the server
	  if($content['origRec']) {
	    $diff = array_diff( explode(',',$content['origRec'][$fN]), explode(',',$content['rec'][$fN]) );
	    foreach((array)$diff as $file) {
	      $uploadPath = $GLOBALS['TCA'][$table]['columns'][$fN]['config']['uploadfolder'];
	      @unlink(PATH_site.$uploadPath.'/'.$file);
	    }
	  }
	  /**** UPLOADED FILES ****/
	  // if a new file is uploaded, we need to add to the database.
	  $file = $dataArr[$fN.'_file'];
	  if($file) {
	    $fV = $content['rec'][$fN] ? $content['rec'][$fN].','.$file : $file;
	    $cObj = t3lib_div::makeInstance('tslib_cObj');
	    $cObj->DBgetUpdate($table, $content['rec']['uid'], array($fN=>$fV), $fN, TRUE);
	  }
	}
	break;
      case 'select':
	if($GLOBALS['TCA'][$table]['columns'][$fN]['config']['MM']) { //its a MM relation
	  $uids = explode(',',$dataArr[$fN]);
	  $uid = $content['rec']['uid'];
	  $mmTable = $GLOBALS['TCA'][$table]['columns'][$fN]['config']['MM'];
	  // update the $fN in $table
	  $cObj = t3lib_div::makeInstance('tslib_cObj');
	  $cObj->DBgetUpdate($table, $uid, array($fN=>count($uids)), $fN, TRUE);
	  // update the MM table
	  $GLOBALS['TYPO3_DB']->exec_DELETEquery($mmTable,'uid_local='.intval($uid));
	  foreach((array)$uids as $foreign_uid) {
	    $GLOBALS['TYPO3_DB']->exec_INSERTquery($mmTable,array('uid_local'=>intval($uid),'uid_foreign'=>intval($foreign_uid)));
	  }
	}
	break;
      }
    }
  }


  /**********************************************************************************************
   * JAVASCRIPT FUNCTIONS
   **********************************************************************************************/

  function getJSBefore() {
    $formName = $this->table.'_form';
    $result .=  '<script type="text/javascript" src="t3lib/jsfunc.evalfield.js"></script>
	    <script type="text/javascript">
	        function typoSetup() {
					this.passwordDummy = "********";
					this.decimalSign = ".";
				}
		var TS = new typoSetup();
	        var evalFunc = new evalFunc();

            function feedit_'.$formName.'Set(theField, evallist, is_in, checkbox, checkboxValue,checkbox_off){
/*alert("SET:" + theField+": "+document.'.$formName.'[theField].value);*/
	      var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
              var feValField = theField+"_feVal";

if(!(document.'.$formName.' && document.'.$formName.'[theField] && document.'.$formName.'[feValField])) return;

      theValue = document.'.$formName.'[theField].value;
/*              valField = theField.substring(0,theField.length-1)+"_hrv]";
	      document.'.$formName.'[theField].value = theValue;
alert(theValue); */
      document.'.$formName.'[feValField].value = evalFunc.outputObjValue(theFObj, theValue);

/*alert(theField+": "+document.'.$formName.'[theField].value);
alert(feValField+": "+document.'.$formName.'[feValField].value);*/
	    }

	    function feedit_'.$formName.'Get(theField, evallist, is_in, checkbox, checkboxValue,checkbox_off){
/*alert("GET: " + theField);*/
	      var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
      if (checkbox_off){
		document.'.$formName.'[theField].value=checkboxValue;
	      }else{
		document.'.$formName.'[theField].value = evalFunc.evalObjValue(theFObj, document.'.$formName.'[theField+"_feVal"].value);
                  /*if(document.'.$formName.'[theField].value.length==0)
                  for(idx=1; eval = feedit_split(evallist,",",idx);idx++);
                     if(eval == "required") {
                       alert("Feltet skal udfyldes");
                }*/
	      }
	     feedit_'.$formName.'Set(theField, evallist, is_in, checkbox, checkboxValue,checkbox_off);
	    }
            function feedit_manipulateMultipleSelect(theField) {
               selObj = document.'.$formName.'[theField+"_select"];
               val = selObj.value;
               list = document.'.$formName.'[theField].value;
               newList = "";
               for(i=0;i<selObj.length;i++) {
                  if(selObj.options[i].selected == true) {
                     newList += selObj.options[i].value+",";
                  }
               }
               if(newList.length!=0)
                 newList = newList.substring(0,newList.length-1);
               document.'.$formName.'[theField].value = newList;

            }

            function feedit_manipulateGroup(theField) {
               selObj = document.'.$formName.'[theField+"_select"];
               val = selObj.value;
               list = document.'.$formName.'[theField].value;
               newList = "";
               for(i=0;i<selObj.length;i++) {
                  if(selObj.options[i].selected == false) {
                     newList += selObj.options[i].value+",";
                  } else {
                     rem_i = i;
                  }
               }
               if(newList.length!=0)
                 newList = newList.substring(0,newList.length-1);
alert(newList);
               document.'.$formName.'[theField].value = newList;
               selObj.options[rem_i] = null;

            }


            function feedit_split(theStr1, delim, index) {
               var theStr = ""+theStr1;
               var lengthOfDelim = delim.length;
               sPos = -lengthOfDelim;
               if (index<1) {index=1;}
               for (var a=1; a<index; a++){
                   sPos = theStr.indexOf(delim, sPos+lengthOfDelim);
                   if (sPos==-1){return null;}
               }
               ePos = theStr.indexOf(delim, sPos+lengthOfDelim);
               if(ePos == -1) {ePos = theStr.length;}
               return (theStr.substring(sPos+lengthOfDelim,ePos));
            }
	    </script>
';

    $result .= $this->additionalJS_initial.'<script type="text/javascript">'. implode(chr(10), $this->additionalJS_pre).'</script>';

    if($this->conf['divide2tabs'])
      $result .= $this->templateObj->getDynTabMenuJScode();

    return $result;
  }

  function getJSAfter() {
    return
  '<script type="text/javascript">'.implode(chr(10), $this->additionalJS_post).'</script>'.chr(10).
  '<script type="text/javascript">'.implode(chr(10), $this->additionalJS_end).'</script>';
  }

  /**********************************************************************************************
   * HELPER FUNCTIONS
   **********************************************************************************************/

  /**
   * Gets the special configurations for a field. The configurations placed in the type array.
   *
   * @param $table string the current table
   * @param $fN string the fieldname to get the configurations for
   * @return array the specialconf array
   */
  function getFieldSpecialConf($table,$fN) {
    $specialConf = array();
    $TCA = $GLOBALS["TCA"][$table];

    // Get the type value
    $type = 0; // default value
    $typeField = $TCA['ctrl']['type'];
    $uid = t3lib_div::_GET('rU');
    if($typeField && $uid) { // get the type from the database else use default value
      $rec = $GLOBALS['TSFE']->sys_page->getRawRecord($table,$uid);
      $type = intval($rec[$typeField]);
    }

    // get the special configurations and check for an existing richtext configuration
    $showitem = $TCA['types'][$type]['showitem'] ? explode(',',$TCA['types'][$type]['showitem']) : explode(',',$TCA['types'][1]['showitem']); // if ['types'][$type] we should try with ['types'][1] according to TCA doc
    foreach((array)$showitem as $fieldConfig) {
      $fC = explode(';',$fieldConfig);
      if(trim($fC[0])==$fN) {                      // if field is $fN
	foreach(explode(':',$fC[3]) as $sC) {
	  if(substr($sC,0,8)=='richtext') {        // if there is a richtext configuration we found what we were looking for
	    $buttons = substr(trim($sC),9,strlen(trim($sC))-10);
	    $specialConf['richtext']['parameters'] = t3lib_div::trimExplode('|',$buttons);

	  } else if(substr($sC,0,13)=='rte_transform') {
	    $transConf = substr(trim($sC),14,strlen(trim($sC))-15);
	    $specialConf['rte_transform']['parameters'] = t3lib_div::trimExplode('|',$transConf);
	  }
	}
      }
    }
    return $specialConf;
  }

  /**
   * Processes the field $fN in $dataArr be the rte mode $mode,
   * according to the Page TS RTE.default.FE
   *
   * @param $dataArr array the dataArray
   * @param $table string the table currently working on
   * @param $fN string the fieldname of the table
   * @param $mode string the transformation direction: either 'rte' or 'db'
   * @return array the modified dataArr
   */
  function rteProcessDataArr($dataArr, $table, $fN, $mode) {
    if(t3lib_extmgm::isLoaded('rtehtmlarea') && !$this->RTEObj)
      $this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');

    if(!empty($dataArr['_TRANSFORM_'.$fN]) && is_object($this->RTEObj) && $this->RTEObj->isAvailable()) {
      $pageTSConfig = $GLOBALS['TSFE']->getPagesTSconfig();

# THIS WILL NOT WORK YET! mail sendt to author of rtehtmlarea STAN
      /*	if(is_array($pageTSConfig['RTE.']['default.']['FE.']) && is_array($pageTSConfig['RTE.']['config.'][$this->table.'.'][$fN.'.']['FE.'])) {
	  $this->thisConfig = t3lib_div::array_merge_recursive_overrule($pageTSConfig['RTE.']['default.']['FE.'],
									$pageTSConfig['RTE.']['config.'][$this->table.'.'][$fN.'.']['FE.']);
	} else {*/
      $this->thisConfig = $pageTSConfig['RTE.']['default.']['FE.'];
//	}

      $this->thePidValue = $GLOBALS['TSFE']->id;
#	  $this->specConf = array('richtext' => 1, 'rte_transform' => array('parameters' => array('mode=ts_css','flag=rte_enabled')));
      $this->specConf = $this->getFieldSpecialConf($table,$fN);
      $dataArr[$fN] = $this->RTEObj->transformContent($mode,$dataArr[$fN],$table,$fN,$dataArr,$this->specConf,$this->thisConfig,'',$this->thePidValue);
    }

    return $dataArr;
  }

  /* Gets the uids if the selected record in a field of type SELECT.
   * This function takes care of MM-relations too.
   * (table is sendt along, as the function is called from updateArray too)
   * @param	string		The record
   * @param	string		The field of type SELECT in the record
   * @returns   array           Array of uids
   */
  function getUidsOfSelectedRecords($rec,$fN,$table) {
    global $TCA;
    $feData = t3lib_div::_POST('FE');
    $uid = $feData[$table]['uid'] ? $feData[$table]['uid'] : t3lib_div::_GET('rU');

    $uids = array();
    if($feData[$table][$fN]) {                                // from post var
      $uids = explode(",",$feData[$table][$fN]);
    } elseif($TCA[$table]['columns'][$fN]["config"]["MM"] && $uid) {  // from mm-relation
      $MMres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$TCA[$table]['columns'][$fN]["config"]["MM"],'uid_local='.$uid,'','sorting');
      if ($GLOBALS['TYPO3_DB']->sql_error())	debug(array($GLOBALS['TYPO3_DB']->sql_error(),$query),'getFormFieldCode()::field='.$fN);

      if($GLOBALS['TYPO3_DB']->sql_num_rows($MMres)!=$rec[$fN])
	debug("Wrong number of selections reached");
      while($MMrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($MMres))
	$uids[] = $MMrow["uid_foreign"];
    } else {                                                        // clean from DB
      $uids = explode(",",$rec[$fN]);
    }
    return $uids;
  }

  function getLLFromLabel($label) {
    return $GLOBALS['TSFE']->sL($label);
  }

  function getLL($key,$alt='')	{
    $label = $GLOBALS['TSFE']->getLLL($key,$this->LOCAL_LANG);
    return $label ? $label : $alt;
  }

  /**
   * array_merge_recursive2()
   *
   * Similar to array_merge_recursive but keyed-valued are always overwritten.
   * Empty values is also overwritten.
   * Priority goes to the 2nd array.
   *
   * @param $paArray1 array
   * @param $paArray2 array
   * @return array
   */
  function array_merge_recursive2($paArray1, $paArray2) {
    if (!is_array($paArray1) or !is_array($paArray2)) {
      return is_null($paArray2)?$paArray1:$paArray2;
    }
    foreach ($paArray2 AS $sKey2 => $sValue2) {
      $paArray1[$sKey2] = $this->array_merge_recursive2(@$paArray1[$sKey2],
							$sValue2);
    }
    return $paArray1;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mth_feedit/class.tx_mthfeedit.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mth_feedit/class.tx_mthfeedit.php']);
}

?>