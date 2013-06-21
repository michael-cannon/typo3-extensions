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
 * Class that implements the controller "Document_Type" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_Document_Type extends tx_cbgaedms_controller_common {

	var $targetControllers = array('Document_Type_EditAction');

    function tx_cbgaedms_controller_Document_Type($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }

	/**
	 * Implementation of Document_Type_EditAction()
	 */
    function Document_Type_EditAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

		// The data come in as form parameters upon the first call ...
		$validator = $this->getValidator();
		if( ! $validator->ok() )
			return $this->Document_Type_FormAction($validator);
		
		$modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doctype');
        $model = new $modelClassName($this);

		if ( $this->parameters->get('uid') )
			$model->update();
		else
			$model->insert();

		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->getDestination());
		$link->noHash();
		$link->redirect();
    }

    function Document_Type_ListClearAction() {
		$this->parameters->set('uid', null);
        return $this->Document_Type_ListAction();
    }

	/**
	 * Implementation of Document_Type_ListAction()
	 */
    function Document_Type_ListAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doctype');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Type');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_TypeEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }

        $view->render('Document_Type_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_Type_SearchAction()
	 */
    function Document_Type_SearchAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doctype');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Type');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_TypeEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_Type_Search');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_Type_ViewAction()
	 */
    function Document_Type_ViewAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doctype');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Type');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_TypeEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Document_Type_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Document_Type_FormAction()
	 */
    function Document_Type_FormAction($object = null) {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doctype');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_Type');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Document_TypeEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this, $object);
		$view->storeToSession($this->getClassName());

        $model = new $modelClassName($this);

		// show details for editing
		if ( $this->parameters->get('uid') ) {
	        $model->load($this->parameters);
			for($model->rewind(); $model->valid(); $model->next()) {
				$entry = new $entryClassName($model->current(), $this);
				$view->append($entry);
			}
		} else {
			// show blank form for new
			$entry = new $entryClassName(null, $this);
			$view->append($entry);
		}

        $view->render('Document_Type_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Document_Type.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Document_Type.php']);
}

?>