<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Michael Cannon <michael@peimic.com>
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
 * Base controller class for extension "cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

tx_div::load('tx_lib_controller');

class tx_cbgaedms_controller_common extends tx_lib_controller {

	var $targetControllers = array();
	var $defaultDesignator = 'tx_cbgaedms';
	var $feUserAuth = null;
	var $userId = null;
	var $isAdmin = null;
	var $isManager = null;
	var $masterAgencyAdmin = null;
	var $isViewer = null;
	var $hasAccess = null;
	var $totalResultCountKey = 'totalResultCountKey';

	function tx_cbgaedms_controller_common($parameter1 = null, $parameter2 = null) {
		parent::tx_lib_controller($parameter1, $parameter2);
	}

	function updateIsAdmin() {
		if ( preg_match( "#\b{$this->userId}\b#", $this->masterAgencyAdmin ) ) {
			$this->isAdmin = true;
			$this->hasAccess = true;
		}
	}

	function accessSetup () {
		$this->feUserAuth = tx_div::getFrontEndUser();
		$this->userId = $this->feUserAuth->user['uid'];
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_common');
        $model = new $modelClassName($this);
        $model->setMasterAgencyAdmin();
	}

	function Restricted_AccessAction () {
		$viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Type');
		$view = new $viewClassName($this);
		$view->render('Restricted_Access');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
		$translator = new $translatorClassName($this, $view);
		$translator->setPathToLanguageFile($this->configurations->get('locallangPath'));
		$out = $translator->translateContent();
		return $out;
	}

	protected function getValidator($rules = 'validationRules') {
		// finding classnames
		$validatorClassName = tx_div::makeInstanceClassName('tx_lib_validator');
	
		// process 
		$validator = new $validatorClassName($this);
		$validator->loadFromSession($this->getClassName());
		$validator->overwriteArray($this->parameters);  
		$validator->useRules($rules . '.');
		$validator->validate();
		return $validator;
	}

	function setIsAdmin( $bool ) {
		$this->isAdmin = $bool;
	}

	function setIsManager( $bool ) {
		$this->isManager = $bool;
	}

	function getUserId () {
		if ( is_null($this->feUserAuth) )
			$this->accessSetup();

		return $this->userId;
	}

	function setIsViewer( $bool ) {
		$this->isViewer = $bool;
	}

	function getIsViewer() {
		return $this->isViewer;
	}

	function setMasterAgencyAdmin( $uid ) {
		$this->masterAgencyAdmin = $uid;
		$this->updateIsAdmin();
	}

	function getMasterAgencyAdmin() {
		return $this->masterAgencyAdmin;
	}

	function adminOk($uid = null) {
		if ( is_null($this->isAdmin) )
			$this->setupAccess($uid);

		return $this->isAdmin;
	}

	function managerOk($uid = null) {
		if ( is_null($this->isManager) )
			$this->setupAccess($uid);

		return $this->isManager;
	}

	function viewerOk($uid = null) {
		if ( is_null($this->isViewer) )
			$this->setupAccess($uid);

		return $this->isViewer;
	}

	function accessOk($uid = null) {
		if ( is_null($this->hasAccess) )
			$this->setupAccess($uid);

		return $this->hasAccess;
	}

	function setupAccess($uid = null, $table = 'tx_cbgaedms_agency') {
		if ( is_null($this->feUserAuth) )
			$this->accessSetup();

		if ( ! is_null( $this->isAdmin ) && $this->isAdmin )
			return;

		$user = $this->getUserId();

		$fields = "
			IF(FIND_IN_SET($user,a.administrator), 1, 0) admin
			, IF($user = a.incidentmanager OR FIND_IN_SET($user, a.alternateincidentmanagers), 1, 0) manager
			, IF(FIND_IN_SET($user,a.viewers), 1, 0) viewer
		";
		$tables = 'tx_cbgaedms_agency a';
		$where = 'a.hidden = 0 AND a.deleted = 0 ';

		switch ( $table ) {
			case 'tx_cbgaedms_agency':
			default:
				if ( is_null( $uid ) )
					$uid = $this->parameters->get('agencyId');

				if ( is_null( $uid ) ) 
					$uid = $this->parameters->get('uid');

				if ( ! is_null( $uid ) ) 
					$where .= ' AND a.uid = ' . $uid;
				else {
					$where .= " AND ( FIND_IN_SET($user, a.administrator)
						OR a.incidentmanager = $user
						OR FIND_IN_SET($user, a.alternateincidentmanagers)
						OR FIND_IN_SET($user, a.viewers))";
				}

				break;

			case 'tx_cbgaedms_doc':
				if ( is_null( $uid ) ) 
					$uid = $this->parameters->get('docId');
				$tables .= ' LEFT JOIN tx_cbgaedms_agency_documents_mm d ON a.uid = d.uid_local';
				$where .= ' AND d.uid_foreign = ' . $uid;
				break;

			case 'tx_cbgaedms_docversion':
				if ( is_null( $uid ) ) 
					$uid = $this->parameters->get('docId');
				$tables .= ' LEFT JOIN tx_cbgaedms_agency_documents_mm d ON a.uid = d.uid_local';
				$tables .= ' LEFT JOIN tx_cbgaedms_doc_version_mm v ON d.uid_foreign = v.uid_local';
				$where .= ' AND v.uid_foreign = ' . $uid;
				break;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where);

		if($result && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if ( $row['viewer'] )
				$this->setIsViewer(true);
			else
				$this->setIsViewer(false);

			if ( $row['manager'] )
				$this->setIsManager(true);
			else
				$this->setIsManager(false);

			if ( $row['admin'] )
				$this->setIsAdmin(true);
			else
				$this->setIsAdmin(false);
		} else {
			$this->setIsAdmin(false);
			$this->setIsManager(false);
			$this->setIsViewer(false);
		}

		$this->hasAccess = $this->isViewer || $this->isManager || $this->isAdmin;
	}

    function _ajaxResponse($data, $responseFileName='response.xml', $responseType='application/xml') {
        if (!is_array($data))  {
            $xmlResponse = '<?xml version="1.0" encoding="utf-8"?>' . 
            "<ajax-response><response>{$data}</response></ajax-response>";
		} else {
            $xmlResponse = '<?xml version="1.0"
encoding="utf-8"?><ajax-response>';
            foreach ($data as $dat) {
                $xmlResponse .= '<response>' . $dat . '</response>' . "\n";
            }
            $xmlResponse .= '</ajax-response>';
        }
        return $this->_sendContents($xmlResponse, $responseFileName, $responseType);
    }   
    function _sendContents($content, $name='', $type='application/octet-stream') {
        header('HTTP/1.0 200 Ok');
        header('Content-Type: '.$type);
        header('Content-Disposition: inline; filename='.$name);
        header('Content-length: '.strlen($content));       
	header('Expires: -1');
        header('Cache-Control: must-revalidate');
        header('Pragma: no-cache');
        echo $content;
        exit;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_common.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_common.php']);
}

?>
