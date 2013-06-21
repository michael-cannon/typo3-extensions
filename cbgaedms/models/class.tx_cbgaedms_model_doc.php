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
 * Class that implements the model for table tx_cbgaedms_doc.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_model_common');
include_once('class.tx_cbgaedms_model_common.php');

class tx_cbgaedms_model_doc extends tx_cbgaedms_model_common {

	function tx_cbgaedms_model_doc($controller = null, $parameter = null) {
		parent::tx_cbgaedms_model_common($controller, $parameter);
	}

	function load($parameters = null) {
		$fields = 'd.uid
			, d.doc
			, d.doctype
			, d.description
		';
		$tables = '';
		$tables = 'tx_cbgaedms_doc d
			LEFT JOIN tx_cbgaedms_agency_documents_mm admm ON d.uid = admm.uid_foreign
			LEFT JOIN tx_cbgaedms_agency a ON a.uid = admm.uid_local
		';
		$where = 'd.hidden = 0 AND d.deleted = 0 ';
		$where .= '';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();
		$groupBy = null;
		$orderBy = 'd.doc ASC';
		$limit = (integer) $this->controller->parameters->get('offset');
		$limit .= ', ' . (integer) $this->controller->configurations->get('resultsPerView');

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND d.uid = ' . $uid;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy, $limit);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$doctype = $this->loadDocument_TypeEntry($row['doctype']);
				$entry->set('doctype', $doctype);
				$version = $this->loadDocument_VersionLatestEntry($row['uid']);
				$entry->set('version', $version);
				$this->append($entry);
			}
		}

		// query total results
		$query = $GLOBALS['TYPO3_DB']->SELECTquery('count(*)', $tables, $where, $groupBy, $orderBy);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);
		if($result) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
			// We use the controllers register to store this special value.
			$this->controller->set($this->controller->totalResultCountKey, current($row));
		}   
	}

	function loadNew($parameters = null) {
		$fields = 'a.agency
			, a.uid agencyId
		';
		$tables = 'tx_cbgaedms_agency a
		';
		$groupBy = null;
		$orderBy = '';
		$where = '1 = 1';

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $agency = $parameters->get('agencyId') ) {
				$where .= ' AND a.uid = ' . $agency;
			}
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}

	function loadAgencyDocs($parameters = null) {
		$fields = 'd.uid
			, d.doc
			, d.doctype
			, d.description
			, d.version
			, CONCAT(f.first_name, " ", f.last_name) feuser
			, d.crdate
			, a.agency
			, a.uid agencyId
			, CONCAT(ff.first_name, " ", ff.last_name) incidentmanager
		';
		$tables = 'tx_cbgaedms_doc d
			LEFT JOIN tx_cbgaedms_agency_documents_mm admm ON d.uid = admm.uid_foreign
			LEFT JOIN tx_cbgaedms_agency a ON a.uid = admm.uid_local
			LEFT JOIN fe_users f ON d.feuser = f.uid
			LEFT JOIN fe_users ff ON a.incidentmanager = ff.uid
		';
		$groupBy = null;
		$orderBy = 'd.doc ASC';
		$where = 'd.hidden = 0 AND d.deleted = 0 ';

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $parameters->get('docview') && $uid = $parameters->get('uid') ) {
				$where .= ' AND d.uid = ' . $uid;
			} elseif ( $parameters->get('versionsview') && $uid = $parameters->get('uid') ) {
				$tables .= '
					LEFT JOIN tx_cbgaedms_doc_version_mm dvmm ON d.uid = dvmm.uid_local
				';
				$where .= ' AND dvmm.uid_foreign = ' . $uid;
			} elseif ( $uid = $parameters->get('agencyId') ) {
				$where .= ' AND a.uid = ' . $uid;
			}
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);

				$doctype = $this->loadDocument_TypeEntry($row['doctype']);
				$entry->set('doctypeEntry', $doctype);

				$version = $this->loadDocument_VersionLatestEntry($row['uid']);
				$entry->set('versionEntry', $version);

				if ( ! $parameters->get('versionsview') ) {
					$versions = $this->loadDocument_VersionEntries($row['uid']);
				} else {
					$versions = $this->loadDocument_VersionEntry($parameters->get('uid'));
				}
				$entry->set('versions', $versions);

				$this->append($entry);
			}
		}
	}

	function managerOk($uid) {
		return $this->managerOk($uid, $table = 'tx_cbgaedms_doc');
	}

	function preparedParameters ( $insert = false ) {
		$parameters = $this->controller->parameters;
		$parameterArray = array();
		$fields = 'hidden,doc,doctype,description';
		foreach($parameters->selectHashArray($fields) as $key => $value) {
			$parameterArray[$key] = htmlspecialchars(trim($value));
		}
		$parameterArray['hidden'] = ('' == $parameterArray['hidden'])
									? 0
									: 1;
		$parameterArray['tstamp'] = time();

		if ( $insert ) {
			$parameterArray['crdate'] = time();
			$parameterArray['pid'] = $this->controller->configurations['storagePid'];
			$parameterArray['feuser'] = $this->controller->getUserId();
			$parameterArray['version'] = 1;
		}

		return $parameterArray;
	}

	function update() {
		$parameters = $this->controller->parameters;
		$updateArray = $this->preparedParameters();
		$where = 'uid = ' . $parameters->get('uid');
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_cbgaedms_doc', $where, $updateArray);
		$GLOBALS['TYPO3_DB']->sql_query($query);
	}

	function insert() {
		$parameters = $this->controller->parameters;
		$insertArray = $this->preparedParameters(true);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_doc', $insertArray);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);
		$docUid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		// insert into tx_cbgaedms_agency_documents_mm
		// relate agency uid_local, doc uid_foreign
		$insertArray = array(
			'uid_local' => $parameters->get('agencyId')
			, 'uid_foreign' => $docUid
		);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_agency_documents_mm', $insertArray);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);

		$this->insertDocumentVersion( $docUid );
	}

	function insertDocumentVersion( $docUid ) {
		$parameters = $this->controller->parameters;

		// upload newfile to uploads/tx_cbgaedms
		$currentPath = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/';
		$uploadPath = $this->controller->configurations->get('uploadPath');
		$filePath = $currentPath . $uploadPath;
		$fileName = $parameters->get('newfile');

		// create unique filename
		$fileNameParts = explode('.', $fileName);
		$fileNameExt = array_pop( $fileNameParts );
		$fileName = implode('.', $fileNameParts)
			. '.' . uniqid()
			. '.' 						. $fileNameExt;
		$fileNamePath = $filePath . $fileName;

		// upload file under new/old name
		if ( ! move_uploaded_file($_FILES['tx_cbgaedms']['tmp_name']['newfile'], $fileNamePath)) {
			echo "File upload failed. Try creating a new version\n";
		}

		$this->controller->parameters->set('newfilename', $fileName);

		// insert version
		$insertArray = $this->preparedVersionParameters($docUid);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_docversion', $insertArray);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);
		$versionUid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		// insert doc version mm
		$insertArray = array(
			'uid_local' => $docUid
			, 'uid_foreign' => $versionUid
		);
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_cbgaedms_doc_version_mm', $insertArray);
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);
	}

	function preparedVersionParameters ( $docUid ) {
		$version = $this->loadDocument_VersionLatestEntry($docUid);
		$currentVersion = $version->get('docversion');
		$docVersion = $currentVersion ? ++$currentVersion : 1;;

		$parameters = $this->controller->parameters;
		$parameterArray = array();
		$fields = 'hidden,description';
		foreach($parameters->selectHashArray($fields) as $key => $value) {
			$parameterArray[$key] = htmlspecialchars(trim($value));
		}
		if ( $parameters->get('versiondescription') )
			$parameterArray['description'] = $parameters->get('versiondescription');

		$parameterArray['hidden'] = ('' == $parameterArray['hidden'])
									? 0
									: 1;
		$parameterArray['tstamp'] = $parameterArray['crdate'] = time();

		$parameterArray['pid'] = $this->controller->configurations['storagePid'];
		$parameterArray['feuser'] = $this->controller->getUserId();
		$parameterArray['docversion'] = $docVersion;
		$parameterArray['filename'] = $parameters->get('newfile');
		$parameterArray['file'] = $parameters->get('newfilename');
		$parameterArray['versiontitle'] = 'A' . $parameters->get('agencyId')
			. '-D' . $docUid
			. '-V' . $docVersion
			. '-' . $parameters->get('newfile');

		return $parameterArray;
	}

	function loadRelatedAgency($parameters = null) {
		$fields = 'a.uid agencyId
		';
		$tables = 'tx_cbgaedms_doc d
			LEFT JOIN tx_cbgaedms_agency_documents_mm admm ON d.uid = admm.uid_foreign
			LEFT JOIN tx_cbgaedms_agency a ON a.uid = admm.uid_local
		';
		$groupBy = null;
		$orderBy = null;
		$where = 'd.hidden = 0 AND d.deleted = 0 ';
		$where .= $this->getWhereAgencyAccess();
		$where .= $this->getWhereMasterAgencyChildren();

		// variable settings
		if($parameters) {
			// do query modifications according to incoming parameters here.
			if ( $uid = $parameters->get('uid') )
				$where .= ' AND d.uid = ' . $uid;
		}

		// query
		// $query = $GLOBALS['TYPO3_DB']->SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ ); cbDebug( 'query', $query );	
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $where, $groupBy, $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$entry = new tx_lib_object($row);
				$this->append($entry);
			}
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_doc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/models/class.tx_cbgaedms_model_doc.php']);
}

?>
