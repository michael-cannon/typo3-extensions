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
 * Class that implements the controller "Document" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_Document extends tx_cbgaedms_controller_common {

	var $targetControllers = array('Document_EditAction','Document_Versions_EditAction');
	var $agencyId = null;

    function tx_cbgaedms_controller_Document($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }

	/**
	 * Implementation of Document_EditAction()
	 */
    function Document_EditAction() {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

		// pass newfile to parameters as _FILES are kept separate
		if ( isset( $_FILES ) )
			$this->parameters->set('newfile', $_FILES['tx_cbgaedms']['name']['newfile']);

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $model = new $modelClassName($this);

		if ( $this->parameters->get('uid') )
			$validator = $this->getValidator('validationRulesEdit');
		else
			$validator = $this->getValidator();

		if( ! $validator->ok() )
			return $this->Document_FormAction($validator);

		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());

		if ( $this->parameters->get('uid') ) {
			$model->update();

			if ( ! $this->parameters->get('hidden') ) {
				$link->destination($this->configurations->get('documentPid'));
				$parameters = array(
					'action' => 'Document_View'
					, 'uid' => $this->parameters->get('uid')
				);
			} else {
				$link->destination($this->configurations->get('locationsPid'));
				$parameters = array(
					'action' => 'Location_View'
					, 'uid' => $this->parameters->get('agencyId')
				);
			}
		} else {
			$model->insert();
			$link->destination($this->configurations->get('locationsPid'));
			$parameters = array(
				'action' => 'Location_View'
				, 'uid' => $this->parameters->get('agencyId')
			);
		}
		$link->parameters($parameters);
		$link->noHash();
		$link->redirect();
    }

    function Document_Versions_CancelAction() {
		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->configurations->get('documentPid'));
		$parameters = array(
			'action' => 'Document_View'
			, 'uid' => $this->parameters->get('docId')
		);
		$link->parameters($parameters);
		$link->noHash();
		$link->redirect();
    }

    function Document_CancelAction() {
		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->configurations->get('locationsPid'));
		$parameters = array(
			'action' => 'Location_View'
			, 'uid' => $this->parameters->get('agencyId')
		);
		$link->parameters($parameters);
		$link->noHash();
		$link->redirect();
    }

	/**
	 * Implementation of Document_ListAction()
	 */
    function Document_ListAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_DocumentEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $model = new $modelClassName($this);
        $model->load($this->parameters);
		$browser = $this->makeInstance('tx_lib_resultBrowserSpl', $this);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $browser->append($entry);
        }
		$browser->buildAs('resultBrowser', $this->totalResultCountKey);
        $view = new $viewClassName($this, $browser);
        $view->render('Document_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_SearchAction()
	 */
    function Document_SearchAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_DocumentEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_Search');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_Versions_EditAction()
	 */
    function Document_Versions_EditAction() {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

		// pass newfile to parameters as _FILES are kept separate
		if ( isset( $_FILES ) )
			$this->parameters->set('newfile', $_FILES['tx_cbgaedms']['name']['newfile']);

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $model = new $modelClassName($this);
		$validator = $this->getValidator('validationRulesVersion');
		if( ! $validator->ok() )
			return $this->Document_Versions_FormAction($validator);

		$docId = $this->parameters->get('docId');
		$model->insertDocumentVersion($docId);

		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->configurations->get('documentPid'));
		$parameters = array(
			'action' => 'Document_View'
			, 'uid' => $docId
			, 'saved' => 'true'
		);
		$link->parameters($parameters);
		$link->noHash();
		$link->redirect();
    }

	/**
	 * Implementation of Document_Versions_ListAction()
	 */
    function Document_Versions_ListAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_docversion');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Versions');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_VersionsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_Versions_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_Versions_SearchAction()
	 */
    function Document_Versions_SearchAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_docversion');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Versions');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_VersionsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_Versions_Search');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_Versions_ViewAction()
	 */
    function Document_Versions_ViewAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Versions');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_VersionsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
		$this->parameters->set('versionsview', true);
        $model->loadAgencyDocs($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_Versions_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_ViewAction()
	 */
    function Document_ViewAction() {
		if ( ! $this->accessOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_DocumentEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
		$this->parameters->set('docview', true);
        $model->loadAgencyDocs($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_FormAction()
	 */
    function Document_FormAction($object = null) {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_DocumentEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $model = new $modelClassName($this);

        $view = new $viewClassName($this, $object);
		$view->storeToSession($this->getClassName());

		// show details for editing
		if ( $this->parameters->get('uid') ) {
			$this->parameters->set('docview', true);
			$model->loadAgencyDocs($this->parameters);
		} else {
			// show blank form for new
			$model->loadNew($this->parameters);
		}

		for($model->rewind(); $model->valid(); $model->next()) {
			$entry = new $entryClassName($model->current(), $this);
			$view->append($entry);
		}

        $view->render('Document_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	function Document_Version_DownloadAction () {
		// check for location access
		$uid = $this->parameters->get('uid');

		if ( ! $uid || ! $this->accessOk( true ) )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_docversion');
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new tx_lib_objectBase($model->current(), $this);
        }

		// grab version file name
		$file = $entry->get('file');

		$currentPath = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/';
		$uploadPath = $this->configurations->get('uploadPath');
		$filePath = $currentPath . $uploadPath;
		$fileNamePath = $filePath . $file;

		// grab original filename
		$filename = $entry->get('filename');
		$filenameParts = explode('.', $filename);
		$filenameExt = array_pop( $filenameParts );
		$filename = preg_replace('/\s|\W/', '-', implode('.', $filenameParts));
		$filename .= '.' . $filenameExt;

		// send version file with origin
		header('Content-type: application/force-download');
		header('Content-Transfer-Encoding: Binary');
		header('Content-length: '.filesize($fileNamePath));
		header('Content-disposition: attachment; filename="'.$filename.'"');
		readfile($fileNamePath);
	}

	function adminOk() {
		if ( parent::adminOk() )
			return true;

		if ( $this->parameters->get('agencyId') )
			$this->setupAccess($this->parameters->get('agencyId'));
		else
			$this->setupAccess($this->parameters->get('uid'), 'tx_cbgaedms_doc');

		return parent::adminOk();
	}

	function managerOk() {
		if ( parent::managerOk() )
			return true;

		if ( $this->parameters->get('agencyId') )
			$this->setupAccess($this->parameters->get('agencyId'));
		else
			$this->setupAccess($this->parameters->get('uid'), 'tx_cbgaedms_doc');

		return parent::managerOk();
	}

	function accessOk( $docVersion = false ) {
		if ( parent::accessOk() )
			return true;

		if ( $this->parameters->get('agencyId') )
			$this->setupAccess($this->parameters->get('agencyId'));
		elseif ( ! $docVersion )
			$this->setupAccess($this->parameters->get('uid'), 'tx_cbgaedms_doc');
		else
			$this->setupAccess($this->parameters->get('uid'), 'tx_cbgaedms_docversion');

		return parent::accessOk();
	}

    function Document_Versions_FormAction($object = null) {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

		if ( ! $this->parameters->get('docId') )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doc');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Versions');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_VersionsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $model = new $modelClassName($this);

        $view = new $viewClassName($this, $object);
		$view->storeToSession($this->getClassName());

		// show details for editing
		$this->parameters->set('uid', $this->parameters->get('docId'));
		$this->parameters->set('docview', true);
		$model->loadAgencyDocs($this->parameters);

		for($model->rewind(); $model->valid(); $model->next()) {
			$entry = new $entryClassName($model->current(), $this);
			$view->append($entry);
		}

        $view->render('Document_Versions_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Document.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Document.php']);
}

?>
