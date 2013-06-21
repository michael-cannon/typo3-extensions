<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Michael Cannon <michael@peimic.com>
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

require_once(PATH_tslib.'class.tslib_pibase.php');

define('_JEXEC', 'TYPO3 is more flexible');
require_once (t3lib_extMgm::extPath('icsugarcrm') . 'res/sugarcases.php');

define('JPATH_BASE', 'TYPO3 is more flexible');
require_once( t3lib_extMgm::extPath('icsugarcrm') . "res/joomla/factory.php" );
require_once( t3lib_extMgm::extPath('icsugarcrm') . "res/joomla/object.php" );
require_once( t3lib_extMgm::extPath('icsugarcrm') . "res/joomla/table.php" );

require_once (t3lib_extMgm::extPath('icsugarcrm') . 'res/sugarcases.class.php');
require_once (t3lib_extMgm::extPath('icsugarcrm') . 'res/sugarportal.inc.php' );
require_once (t3lib_extMgm::extPath('icsugarcrm') . 'res/sugarinc/sugarUtils.php');

// The html class should be derived from a sugarapp class containing useful
// methods and configuration so it has to be included after the sugar includes
require_once (t3lib_extMgm::extPath('icsugarcrm') . 'res/sugarcases.html.php');


/**
 * Plugin 'Case Manager' for the 'icsugarcrm' extension.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_icsugarcrm
 */
class tx_icsugarcrm_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_icsugarcrm_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_icsugarcrm_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'icsugarcrm';	// The extension key.
	var $pi_checkCHash = false;
	var $uploadfolder = 'uploads/tx_icsugarcrm/';

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->pi_loadLL();
		$this->pi_setPiVarDefaults();
		$this->pi_initPIflexForm();
		
		$content = '';
		
		// $conf['uid'] = $this->cObj->data['uid'];
		// $conf['file'] = 'http://www.youtube' . trim($conf['delayedCokkies']) . '.com/v/'.$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'videoID', 'sDEF');
		// $conf['vars.']['rel'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'rel', 'sDEF');

		define('_MYNAMEIS', 'com_sugarcases');

		$task = t3lib_div::_GP('task');
		$run_search = t3lib_div::_GP('run_search');
		$caseID = t3lib_div::_GP('caseID');
		$Itemid = t3lib_div::_GP('Itemid');
		$limit 		= intval( t3lib_div::_GP('limit') );
		$limit 		= $limit ? $limit : 10;
		$limitstart = intval( t3lib_div::_GP('limitstart') );
		$limitstart = $limitstart ? $limitstart : 0;
		$searchType = t3lib_div::_GP('searchType');
		$searchType = $searchType ? $searchType : 'searchable';

		$my	= ( isset( $GLOBALS[ 'TSFE' ]->fe_user->user ) )
				? $GLOBALS[ 'TSFE' ]->fe_user->user
				: array();

		// no user, no work, no more sugar stuff
		if (!isset( $my[ 'uid' ] )) {
			$content = $this->cObj->stdWrap($this->pi_getLL('error'), $this->conf['heading.']);
			$content .= $this->cObj->stdWrap($this->pi_getLL('noUserLoggedIn'), $this->conf['content.']);
			return $content;
		}
		t3lib_div::debug("new sugar app<br/>");

		// Joomla says we can be here, now we make the application object and keep going
		$caseApp = new sugarAppCase($_REQUEST, $my['username'], $task);
		t3lib_div::debug("new sugar app done<br/>");

		$caseApp->login(); //timing out here!!!!!!!!!!!!!
		t3lib_div::debug("logged in<br/>");

		// Do sugar permission check
		if( ! $caseApp->sugarAuthorizedPortalUser ) {
			$content = $this->cObj->stdWrap($this->pi_getLL('error'), $this->conf['heading.']);
			$content .= $this->cObj->stdWrap($this->pi_getLL('userNotAuthorized'), $this->conf['content.']);
			return $content;
		}

		// Now we'll finish configuring the application object
		$neededFormFields = array('option'=>_MYNAMEIS,
								  'Itemid'=>$Itemid);


		$caseApp->setNeededFormFields($neededFormFields);
		$caseApp->setSessionStartCallback('startSugarSession');
		$caseApp->setSessionStopCallback('stopSugarSession');
		// Session management, logs into the sugar soap server and gets the session ID
		$caseApp->startSession();
		//$caseApp->Initialize();

		$presentation = new HTML_sugar_portal();

		// You *must* configure the presentation layer like this, it's how you get the configurable options
		// put in properly
		$caseApp->configurePresentationLayer($presentation);

		switch( $task ) {
			case "new":
				$columns = getColumnData($caseApp);
				$presentation->RenderNewForm($columns);
				break;

			case "search":
				// First get the columns that are wanted.  We could use a simple query for this,
				// but let's keep all the querying in one place, shall we?
				$columnData = getColumnData($caseApp);
				$columns = $columnData['selected'];
				$searchcolumns = array();
				$numberSearch == false;
				foreach($columns as $column) {
					if($column[$searchType] == 1) {
						if( isset( $_REQUEST[$column['field']]) ) {
							// [IC] eggsurplus: if an integer is passed in the search box on the top assume they want to search by the number...not name
							if(isset($_REQUEST["quicksearch"]) && $column['field'] == "name" && is_numeric(t3lib_div::_GP($column['field']))) {
								$searchcolumns["case_number"] = t3lib_div::_GP($column['field']);
								$searchcolumns[$column['field']] = '';
								$numberSearch == true;
							} else {
								$searchcolumns[$column['field']] = t3lib_div::_GP($column['field']);
							}
						} else if(!isset($_REQUEST["quicksearch"]) || $numberSearch == false || $column['field'] != "case_number") { // [IC] just in case case_number is called after..prevent overwrite
							$searchcolumns[$column['field']] = '';
						}
					}
				}


				$filter = array();
				foreach ($searchcolumns as $field => $search_value) {
					if (!empty($search_value)) {
						$searchcolumns_query[$field] = $search_value;
						if(strpos($field,'id') !== false) {
							$filter[] = array('name'=>$field,'value'=>$search_value,'operator'=>'=','value_array'=>'');	
						} else {
							$filter[] = array('name'=>$field,'value'=>'%'.$search_value.'%','operator'=>'LIKE','value_array'=>'');	
						}
					}
				}       
				
				$cases = array();
				//total for pagination - 2007/03/07
				$total = 0;
				if (!empty($filter)) { 
					$select_fields = getDataToPass($caseApp,'list');
					$cases = $caseApp->search($filter, $select_fields, $limitstart, $limit);
					$total = $limit+1; //need to add 1 or JPagination will set to All
					if($presentation->fullpagination == 1) $total = $cases['total_count'];
				} else if(!empty($run_search) && $run_search == 'true') {
				  //prompt user to enter some sort of filter
				  $_SESSION['search_error'] = "At least one search filter is required. Please enter a search filter and try again.";
				  //unset($_SESSION['search_error']);
				 // return null;
				}

				require_once (t3lib_extMgm::extPath('icsugarcrm') .  'res/class.JPagination.php');
				$pageNav = t3lib_div::makeInstance('JPagination');
				$pageNav->__construct( $total, $limitstart, $limit );

				$presentation->RenderSearchForm($cases, $searchcolumns, $columnData, $pageNav, $searchcolumns_query, $searchType);
				break;

			case "edit":
				$columns = getColumnData($caseApp);

				if(isset($caseID) && ! $caseID) {
					$presentation->RenderNewForm($columns);
				} else {
					list($thiscase,$notes) = $caseApp->get($caseID);
					$presentation->Render($columns, $thiscase, $notes);
				}
				break;

			case "newnote":
				// First add the new note
				$cases = $caseApp->createNote(t3lib_div::_POST('caseID'), t3lib_div::_POST(), $_FILES);

			   t3lib_div::debug($cases . "<br />");

				$linkArray = array(
					'parameter' => $GLOBALS[ 'TSFE' ]->id 
					, 'returnLast' => 'url'
					, 'no_cache' => 0
					, 'useCacheHash' => 0
					, 'additionalParams' => '&task=edit&caseID=' . t3lib_div::_POST('caseID')
				); 
				
				// broke error checking
				if($caseApp->sugarError) {
					t3lib_div::debug($sugarComm->getErrorText());
				}

				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->cObj->typolink( '', $linkArray); 
				header( 'Location: ' . $url ); 
				exit();
				break;

			case "error":
				t3lib_div::debug( $sugarConf->getBrokeMessage() );
				break;

			case "saveedit":
				$linkArray = array(
					'parameter' => $GLOBALS[ 'TSFE' ]->id 
					, 'returnLast' => 'url'
					, 'no_cache' => 0
					, 'useCacheHash' => 0
					, 'additionalParams' => '&task=edit&caseID=' . $cases['id']
				); 
				
				if( t3lib_div::_POST('button')=='Save' ) {
					$cases = $caseApp->modify(t3lib_div::_POST());
				} else {
					unset($linkArray['additionalParams']);
				}

				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->cObj->typolink( '', $linkArray); 
				header( 'Location: ' . $url ); 
				exit();
				break;

			case "savenew":
				$linkArray = array(
					'parameter' => $GLOBALS[ 'TSFE' ]->id 
					, 'returnLast' => 'url'
					, 'no_cache' => 0
					, 'useCacheHash' => 0
					, 'additionalParams' => '&task=edit&caseID=' . $cases['id']
				); 
				
				if( t3lib_div::_POST('button')=='Save' ) {
					$cases = $caseApp->create(t3lib_div::_POST());
				} else {
					unset($linkArray['additionalParams']);
				}

				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->cObj->typolink( '', $linkArray); 
				header( 'Location: ' . $url ); 
				exit();
				break;

			case "download":
				if(!empty($_REQUEST['noteid'])) {
					$theFile = $caseApp->getNoteAttachment(t3lib_div::_GP('moduleid'),t3lib_div::_GP('noteid'));
					$file = base64_decode($theFile->file);

					$discard = ob_end_clean();
					$content_dispo_header = "Content-Disposition: attachment; filename=\"".$theFile->filename."\"";

					header($content_dispo_header);
					header("Content-Type: application/force-download");
					header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
					header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Pragma: public");
					header("Content-Length: ".strlen($file));
					echo $file;
					die();
				} else {
					$linkArray = array(
						'parameter' => $GLOBALS[ 'TSFE' ]->id 
						, 'returnLast' => 'url'
						, 'no_cache' => 0
						, 'useCacheHash' => 0
					); 
				
					$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->cObj->typolink( '', $linkArray); 
					header( 'Location: ' . $url ); 
					exit();
				}
				break;

			case "refresh":
				$caseApp->stopSession();
				$caseApp->startSession();
				break;

			case "home":
			default:
				$select_fields = getDataToPass($caseApp,'list');
				//$limitStr = "$limitstart,$limit"; //no longer needed
				$cases = $caseApp->getAll($select_fields,$limitstart, $limit);
				$total = $limit+1; //need to add 1 or JPagination will set to All
				if($presentation->fullpagination == 1) $total = $cases['total_count']; //need to do a customization in Sugar to return this...should be added by default in future versions

				if (empty($pageNav))
				{
					require_once (t3lib_extMgm::extPath('icsugarcrm') .  'res/class.JPagination.php');
					$pageNav = t3lib_div::makeInstance('JPagination');
					$pageNav->__construct( $total, $limitstart, $limit );
				}

				$columns = getColumnData($caseApp);

				if($caseApp->err) {
					t3lib_div::debug( $sugarComm->getErrorText() );
					return true;
				}

				$presentation->frontpage($cases, $caseApp, $columns, $pageNav);
				break;
		}

		$caseApp->logout();

		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/icsugarcrm/pi1/class.tx_icsugarcrm_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/icsugarcrm/pi1/class.tx_icsugarcrm_pi1.php']);
}

?>