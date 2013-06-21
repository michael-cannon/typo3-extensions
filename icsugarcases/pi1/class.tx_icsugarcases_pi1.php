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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

define( '_VALID_SUGAR', true );
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarapp/sugarApp.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarapp/sugarAppCase.php');

define( '_MYNAMEIS', 'com_sugarcases' );
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarcases.class.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarinc/sugarConfiguration.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarinc/sugarError.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarinc/sugarCommunication.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarinc/sugarCase.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarinc/sugarContact.php');
require_once (t3lib_extMgm::extPath('icsugarcases') . 'res/sugarinc/sugarNote.php');

/**
 * Plugin 'SugarCRM Case Portal' for the 'icsugarcases' extension.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_icsugarcases
 */
class tx_icsugarcases_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_icsugarcases_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_icsugarcases_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'icsugarcases';	// The extension key.
	var $pi_checkCHash = true;

	var $caseApp	= null;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$task = $this->piVars['task'] ? $this->piVars['task'] : 'home';
		$initCase = $this->initCaseApp($task);
		$task = ( true === $initCase ) ? $task : 'error';

		$content = '';

		switch ( $task ) {
			case 'error':
				$content .= $initCase;
				break;

			case 'download':
				$content .= $this->doNoteDownload();
				break;

			case 'newnote':
				$content .= $this->doNewNoteSave();
				break;

			case 'newcase':
				$content .= $this->getPortalNavigation();
				$content .= $this->displayNewCase();
				break;

			case 'newcasesave':
				$content .= $this->doNewCaseSave();
				break;

			case 'editcase':
				if ( isset( $this->piVars['caseID'] ) ) {
					$content .= $this->displayEditCase();
				} else {
					$content .= $this->displayNewCase();
				}
				break;

			case 'editcasesave':
				$content .= $this->doEditCaseSave();
				break;

			case 'search':
				$content .= $this->getPortalNavigation();
				$content .= $this->displaySearch();
				break;

			case 'refresh':
				$this->caseApp->stopSession();
				$this->caseApp->startSession();

			case 'home':
			default:
				$content .= $this->getPortalNavigation();
				$content .= $this->displayHome();
				break;
		}

		return $content;
	}

	function doNoteDownload() {
        if( $this->piVars['noteid'] ) {
			$theFile = $this->caseApp->getNoteAttachment($this->piVars['moduleid'],$this->piVars['noteid']);
            $file = base64_decode($theFile->file);

            $discard = ob_end_clean();
            $content_dispo_header = "Content-Disposition: attachment; filename=\"".$theFile->filename."\"";

            header($content_dispo_header);
            header("Content-Type: application/force-download");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Content-Length: ".strlen($file));
			echo $file;
            exit();
		}

		// no file to download
		header('Location: ' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
		exit();
	}

	function doEditCaseSave() {
        if( isset($this->piVars['name']) && ! empty($this->piVars['name']) ) {
            $cases = $this->caseApp->modify($this->piVars);
			$urlConf = array(
				'parameter' => $GLOBALS['TSFE']->id
				, 'additionalParams' => '&'
					. $this->prefixId.'[task]='.$this->conf['edit.']['case.']['save.']['redirect']
					. '&'.$this->prefixId.'[caseID]='.$cases['id']
				, 'useCacheHash' => true
				, 'returnLast' => 'url'
			);
			header('Location: ' . $this->cObj->typoLink('', $urlConf));
			exit();
        }

		// operation cancelled
		header('Location: ' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
		exit();
	}

	function doNewNoteSave() {
        if( isset($this->piVars['name']) && ! empty($this->piVars['name']) ) {
        	$cases = $this->caseApp->createNote($this->piVars['caseID'], $this->piVars, $_FILES[$this->prefixId]);
			$urlConf = array(
				'parameter' => $GLOBALS['TSFE']->id
				, 'additionalParams' => '&'
					. $this->prefixId.'[task]='.$this->conf['new.']['note.']['save.']['redirect']
					. '&'.$this->prefixId.'[caseID]='.$this->piVars['caseID']
				, 'useCacheHash' => true
				, 'returnLast' => 'url'
			);
			header('Location: ' . $this->cObj->typoLink('', $urlConf) . '#notes');
			exit();
        }
	}

	function doNewCaseSave() {
        if( isset($this->piVars['name']) && ! empty($this->piVars['name']) ) {
            $cases = $this->caseApp->create($this->piVars);
			$urlConf = array(
				'parameter' => $GLOBALS['TSFE']->id
				, 'additionalParams' => '&'
					. $this->prefixId.'[task]='.$this->conf['new.']['case.']['save.']['redirect']
					. '&'.$this->prefixId.'[caseID]='.$cases['id']
				, 'useCacheHash' => true
				, 'returnLast' => 'url'
			);
			header('Location: ' . $this->cObj->typoLink('', $urlConf));
			exit();
        }

		// operation cancelled
		header('Location: ' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
		exit();
	}

	function displayNewCase() {
		$data = array(
			'template' => 'newCase'
			, 'header'
			, 'content'
			, 'ACTION_LINK' => $this->pi_getPageLink($GLOBALS['TSFE']->id)
			, 'SAVE_TYPE' => 'newcasesave'
			, 'CASE_ID' => ''
			, 'PREFIXID' => $this->prefixId
			, 'save'
			, 'is_required'
			, 'reset'
			, 'cancel'
			, 'name'
			, 'description'
		);

		return $this->populateTemplate($data);
	}

	function displayEditCase() {
		$case = $this->caseApp->get($this->piVars['caseID']);

		$data = array(
			'template' => 'editCase'
			, 'dataself' => $case[0]
			, 'header'
			, 'content'
			, 'ACTION_LINK' => $this->pi_getPageLink($GLOBALS['TSFE']->id)
			, 'SAVE_TYPE' => 'editcasesave'
			, 'CASE_ID' => $this->piVars['caseID']
			, 'PREFIXID' => $this->prefixId
			, 'save'
			, 'is_required'
			, 'reset'
			, 'cancel'
			, 'id'
			, 'case_number'
			, 'date_entered'
			, 'date_modified'
			, 'name'
			, 'account_name'
			, 'account_id'
			, 'status'
			, 'priority'
			, 'description'
			, 'resolution'
			, 'assigned_user_name'
			, 'assigned_user_id'
			, 'modified_by_name'
			, 'modified_user_id'
			, 'created_by_name'
			, 'created_by'
			, 'deleted'
			, 'note_name'
			, 'note_new_note'
			, 'note_subject'
			, 'note_description'
			, 'note_filename'
			, 'note_save'
			, 'NOTE_SAVE_TYPE' => 'newnote'
			, 'EMBED_FLAG' => '0'
			, 'notes' => 'noNotes'
		);

		$notes = $case[1];
		$notesCount = count($notes);
		$urlConf = array(
			'parameter' => $GLOBALS['TSFE']->id
			, 'useCacheHash' => true
		);

		for ( $i = 0; $i < $notesCount; $i++ ) {
			$notes[$i]['description'] = nl2br( $notes[$i]['description'] );
			$filename = $notes[$i]['filename'];
			if ( ! empty( $filename ) ) {
				$urlConf['additionalParams'] = '&'
					. $this->prefixId.'[task]=download'
					. '&'.$this->prefixId.'[noteid]='.$notes[$i]['id']
					. '&'.$this->prefixId.'[moduleid]='.$case[0]['id'];
				$notes[$i]['FILENAME_LINK'] = $this->cObj->typoLink($filename, $urlConf);
			} else {
				$notes[$i]['FILENAME_LINK'] = '';
			}
		}

		if ( 0 < $notesCount ) {
			unset($data['notes']);
			$data['notes.subpart'] = array(
				'template' => 'notesList'
				, 'langKey' => 'notes'
				, 'note.subpart' => array(
					'template' => 'note'
					, 'langKey' => 'notes'
					, 'data.noteEntryList' => $notes
					, 'name'
					, 'date_modified'
					, 'description'
					, 'filename'
				)
			);
		}

		return $this->populateTemplate($data);
	}

	function displaySearch() {
		$data = array(
			'template' => 'search'
			, 'header'
			, 'content'
		);

		return $this->populateTemplate($data);
	}

	function displayHome() {
    	$select_fields = $this->getDataToPass('list');
		$limitstart = $this->piVars['page']
			? intval( $this->piVars['page'] )
			: 0;
		$limit = $this->piVars['limit']
			? intval( $this->piVars['limit'] )
			: $this->conf['limit'];
        $cases = $this->caseApp->getAll($select_fields, $limitstart * $limit, $limit);

		$total_count = $cases['total_count'];
		unset($cases['total_count']);

        if ( 1 > $total_count ) {
			$data = array(
				'template' => 'home'
				, 'header'
				, 'content' => 'noCasesFound'
			);

			return $this->populateTemplate($data);
        }

		$urlConf = array(
			'parameter' => $GLOBALS['TSFE']->id
			, 'additionalParams' => '&'
			, 'useCacheHash' => true
		);

		// link cases
		for ( $i = 0; $i < $total_count; $i++ ) {
			$urlConf['additionalParams'] = "&{$this->prefixId}[task]=editcase&{$this->prefixId}[caseID]=".$cases[$i]['id'];

			$cases[$i]['CASEEDIT_LINK'] = $this->cObj->typoLink($cases[$i]['case_number'], $urlConf);
		}

        $totalCases = $this->caseApp->getAll(array('id'));
        $totalCases = $totalCases['total_count'];

		$data = array(
			'template' => 'home'
			, 'header'
			, 'content'
			, 'CASE_PAGEBROWSER' => $this->getPageBrowser($limit, $totalCases)
			, 'cases.subpart' => array(
				'template' => 'casesList'
				, 'langKey' => 'cases'
				, 'id'
				, 'case_number'
				, 'date_entered'
				, 'date_modified'
				, 'name'
				, 'account_name'
				, 'account_id'
				, 'status'
				, 'priority'
				, 'description'
				, 'resolution'
				, 'assigned_user_name'
				, 'assigned_user_id'
				, 'modified_by_name'
				, 'modified_user_id'
				, 'created_by_name'
				, 'created_by'
				, 'deleted'
				, 'case.subpart' => array(
					'template' => 'case'
					, 'data.caseEntryList' => $cases
				)
			)
		);

		return $this->populateTemplate($data);
	}

	function getDataToPass($type = 'all') {
		$columns = array(
			'id'
			, 'case_number'
			, 'date_entered'
			, 'date_modified'
			, 'name'
			, 'account_name'
			, 'account_id'
			, 'status'
			, 'priority'
			, 'description'
			, 'resolution'
			, 'assigned_user_name'
			, 'assigned_user_id'
			, 'modified_by_name'
			, 'modified_user_id'
			, 'created_by_name'
			, 'created_by'
			, 'deleted'
		);

		return $columns;
	}

	function populateTemplate( $data, $isData = false, $template = false ) {
		$template = $template ? $template : $data['template'];
		unset( $data['template'] );

		$file = $this->conf['template.'][$template];
		$templateCode = $this->cObj->fileResource($file);

		if ( isset( $data['langKey'] ) ) {
			$langKey = $data['langKey'] . '.';
			unset( $data['langKey'] );
		} else {
			$langKey = $template . '.';
		}

		$markerArray = array();
		foreach( $data as $key => $value ) {
			$key = ! is_integer( $key ) ? $key : $value;

			if ( preg_match('/([^\n]+)\.subpart$/', $key, $marker ) ) {
				// parse subtemplate
				$markerArray['###'.strtoupper($marker[1]).'###'] = $this->populateTemplate( $value );
			} elseif ( preg_match('/^data\.(\w+)/', $key, $marker ) ) {
				// cycle through data entries
				foreach ( $value as $i ) {
					$markerArray['###'.strtoupper($template).'###'] .= $this->populateTemplate( $i, true, $marker[1] );
				}
			} elseif ( preg_match('/^dataself$/', $key ) ) {
				// cycle through data entries
				foreach ( $value as $dKey => $dValue) {
					$markerArray['###VALUE_'.strtoupper($dKey).'###'] = $dValue;
				}
			} elseif ( $isData ) {
				$markerArray['###VALUE_'.strtoupper($key).'###'] = $value;
			} elseif ( preg_match('/[[:lower:]]+/', $key ) ) {
				$markerArray['###'.strtoupper($key).'###'] = $this->pi_getLL($langKey.$value);
			} else {
				// keys that are UPPERCASE already denote data as value and
				// don't need language label grabbing
				$markerArray['###'.$key.'###'] = $value;
			}
		}

		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray);

		return $templateCode;
	}

	function initCaseApp($task) {
		$user = ( isset( $GLOBALS[ 'TSFE' ]->fe_user->user ) )
			? $GLOBALS[ 'TSFE' ]->fe_user->user
			: array();

		if ( ! isset( $user['uid'] ) || 1 > $user['uid'] ) {
			$data = array(
				'template' => 'error'
				, 'header'
				, 'content' => 'userNotLoggedIn'
			);

			return $this->populateTemplate($data);
		}

		$this->caseApp = new sugarAppCase($this->piVars, $user['username'], $task);
		$this->caseApp->login();

		if( ! $this->caseApp->sugarAuthorizedPortalUser ) {
			$data = array(
				'template' => 'error'
				, 'header'
				, 'content' => 'userNotAuthorized'
			);

			return $this->populateTemplate($data);
		}

		// Now we'll finish configuring the application object
		$neededFormFields = array(
			'option' => _MYNAMEIS
			, 'Itemid' => $this->piVars['Itemid']
		);

		$this->caseApp->setNeededFormFields($neededFormFields);
		$this->caseApp->setSessionStartCallback('startSugarSession');
		$this->caseApp->setSessionStopCallback('stopSugarSession');

		// Session management, logs into the sugar soap server and gets the session ID
		$this->caseApp->startSession();

		return true;
	}

	/**
	 * Creates a page browser
	 *
	 * @param	int		$rpp	Record per page
	 * @param	int		$rowCount	Number of rows on the current page
	 * @return	string		Generated HTML
	 */
	function getPageBrowser($rpp, $rowCount) {
		$numberOfPages = intval($rowCount/$rpp) + (($rowCount % $rpp) == 0 ? 0 : 1);
		$pageBrowserKind = $this->conf['pageBrowser'];
		$pageBrowserConfig = $this->conf['pageBrowser.'];

		if (!$pageBrowserKind || !is_array($pageBrowserConfig)) {
			$result = $this->pi_getLL('no_page_browser');
		} else {
			$pageBrowserConfig += array(
				'pageParameterName' => $this->prefixId . '|page',
				'numberOfPages' => $numberOfPages,
			);
			// Get page browser
			$cObj = t3lib_div::makeInstance('tslib_cObj');
			$cObj->start(array(), '');
			$result = $cObj->cObjGetSingle($pageBrowserKind, $pageBrowserConfig);
		}

		$data = array(
			'template' => 'pageBrowser'
			, 'PAGEBROWSER' => $result
		);

		return $this->populateTemplate($data);
	}

	function getPortalNavigation() {
		$id = $GLOBALS['TSFE']->id;
		$urlConf = array(
			// Target page uid
			'parameter' => $id

			// Set additional parameters
			, 'additionalParams' => '&'

			// We must add cHash because we use parameters
			, 'useCacheHash' => true

			// We want link only
			, 'returnLast' => 'url'
		);

		$newUrlConf = $urlConf;
		$newUrlConf['additionalParams'] = "&{$this->prefixId}[task]=newcase";

		$searchUrlConf = $urlConf;
		$searchUrlParams['additionalParams'] = "&{$this->prefixId}[task]=search";

		$data = array(
			'template' => 'portalNav'
			, 'home'
			, 'LINK_HOME' => $this->pi_getPageLink($id)
			, 'new'
			, 'LINK_NEW' => $this->cObj->typoLink('', $newUrlConf)
			, 'search'
			, 'LINK_SEARCH' => $this->cObj->typoLink('', $searchUrlConf)
		);

		return $this->populateTemplate($data);
	}
}


// Session management function
function startSugarSession(&$caseApp) {
    if(!isset($_SESSION)){
        session_start();
    }
    // Check to see if we already have a sugar session
    if(isset($_SESSION['sugar_session'])){
        $caseApp->setSugarSessionID($_SESSION['sugar_session']);
    } else {
        // If not, create one and get going
        $caseApp->createSession();
        $_SESSION['sugar_session'] = $caseApp->getSugarSessionID();
    }
}

function stopSugarSession(&$caseApp) {
    if(!isset($_SESSION)){
        session_start();
    }
    if(empty($caseApp->sessionID) && isset($_SESSION['sugar_session'])){
        $caseApp->setSugarSessionID($_SESSION['sugar_session']);
    }
    $caseApp->closeSession();
    unset($_SESSION['sugar_session']);
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/icsugarcases/pi1/class.tx_icsugarcases_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/icsugarcases/pi1/class.tx_icsugarcases_pi1.php']);
}

?>