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
 * Class that implements the controller "Location" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_Location extends tx_cbgaedms_controller_common {

	var $targetControllers = array('Location_EditAction');

    function tx_cbgaedms_controller_Location($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }

	/**
	 * Implementation of Location_EditAction()
	 */
    function Location_EditAction() {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $model = new $modelClassName($this);

		$validator = $this->getValidator();
		if( ! $validator->ok() )
			return $this->Location_FormAction($validator);

		if ( $uid = $this->parameters->get('uid') )
			$model->update();
		else
			$uid = $model->insert();

		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->getDestination());
		if ( ! $this->parameters->get('hidden') ) {
			$parameters = array(
				'action' => 'Location_View'
				, 'uid' => $uid
				, 'saved' => 'true'
			);
			$link->parameters($parameters);
		}
		$link->noHash();
		$link->redirect();
    }

    function Location_ListClearAction() {
		$this->parameters->clear();
        return $this->Location_ListAction();
    }

	/**
	 * Implementation of Location_ListAction()
	 */
    function Location_ListAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Location');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_LocationEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $model = new $modelClassName($this);
        $model->loadList($this->parameters);
		$browser = $this->makeInstance('tx_lib_resultBrowserSpl', $this);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $browser->append($entry);
        }
		$browser->buildAs('resultBrowser', $this->totalResultCountKey);
        $view = new $viewClassName($this, $browser);
        $view->render('Location_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

    function State_ListAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Location');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_LocationEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
		$countryId = preg_replace( '#[^\d]#', '', $_REQUEST['countryId']);
		$this->parameters->set('countryId', $countryId);
        $model->loadCountryZones($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('State_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $this->_ajaxResponse($out);
    }

	/**
	 * Implementation of Location_SearchAction()
	 */
    function Location_SearchAction() {
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Location');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $view->render('Location_Search');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Location_ViewAction()
	 */
    function Location_ViewAction() {
		if ( ! $this->accessOk($this->parameters->get('uid') ) )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Location');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_LocationEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);

        $model->loadView($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
		$view->set('googleMapApi', $this->configurations['googleMapApi']);
        $view->render('Location_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Location_FormAction()
	 */
    function Location_FormAction($object = null) {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Location');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_LocationEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $model = new $modelClassName($this);

        $view = new $viewClassName($this, $object);
		$view->storeToSession($this->getClassName());

		// show details for editing
		if ( $this->parameters->get('uid') ) {
			$model->loadView($this->parameters);
			for($model->rewind(); $model->valid(); $model->next()) {
				$entry = new $entryClassName($model->current(), $this);
				$view->append($entry);
			}
		} else {
			// show blank form for new
			$entry = new $entryClassName(null, $this);
			$entry->set('incidentmanager', (integer) $this->configurations->get('userToBeAssigned') );
			$view->append($entry);
		}

        $view->render('Location_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Location.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Location.php']);
}

?>