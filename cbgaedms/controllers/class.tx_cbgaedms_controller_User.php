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
 * Class that implements the controller "User" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_User extends tx_cbgaedms_controller_common {

	var $targetControllers = array('FE_Users_EditAction','User_Access_EditAction');

    function tx_cbgaedms_controller_User($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }

	/**
	 * Implementation of FE_Users_EditAction()
	 */
    function FE_Users_EditAction() {
	if ( ! $this->adminOk() && ! $this->managerOk() )
		return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_fe_users');
        $model = new $modelClassName($this);
	$validator = $this->getValidator();

	if( ! $validator->ok() )
		return $this->FE_Users_FormAction($validator);

	$uid = $this->parameters->get('uid');
	if ( $uid )
		$model->update();
	else
		$model->insert();

	// MLC 20100115 if no uid, grab new
	$uid = ( $uid )
		? $uid
		: $GLOBALS['TYPO3_DB']->sql_insert_id();

	// Redirect. Always a good idea to prevent double entries by reload.
	$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
	$link = new $linkClassName();
	$link->designator($this->getDesignator());
	$link->destination($this->getDestination());
	$parameters = array(
		'action' => 'FE_Users_Cancel'
		, 'uid' => $uid
		, 'saved' => 'true'
	);
	$link->parameters($parameters);
	$link->noHash();
	$link->redirect();
    }

	/**
	 * Implementation of FE_Users_ListAction()
	 */
    function FE_Users_ListAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_FE_Users');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_Users');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_UsersEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $model = new $modelClassName($this);
        $model->load($this->parameters);
		$browser = $this->makeInstance('tx_lib_resultBrowserSpl', $this);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $browser->append($entry);
        }
     if ( ! $this->parameters->get('download') ) {
		$browser->buildAs('resultBrowser', $this->totalResultCountKey);
        $view = new $viewClassName($this, $browser);
        $view->render('FE_Users_List');
        
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
		
        return $out;
    } else {   
    	$model->loadfullresults($this->parameters);
		$browser = $this->makeInstance('tx_lib_resultBrowserSpl', $this);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $browser->append($entry);
        }
    $browser->buildAs('resultBrowser', $this->totalResultCountKey);
        $view = new $viewClassName($this, $browser);
        $view->render('FE_User_List_Download');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'FE_Users_List.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}        
    }

	/**
	 * Implementation of FE_Users_SearchAction()
	 */
    function FE_Users_SearchAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_FE_Users');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_Users');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_UsersEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        if ( ! $this->parameters->get('download') ) {   
        $view->render('FE_Users_Search');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
        } else {   
        	$view = new $viewClassName($this);
        $model = new $modelClassName($this);
       $model->load($this->parameters);
		$browser = $this->makeInstance('tx_lib_resultBrowserSpl', $this);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $browser->append($entry);
        }
    $browser->buildAs('resultBrowser', $this->totalResultCountKey);
        $view = new $viewClassName($this, $browser);
        $view->render('FE_User_List_Download');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'FE_Users_List.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}        
    }

	/**
	 * Implementation of FE_Users_ViewAction()
	 */
    function FE_Users_ViewAction() {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_FE_Users');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_Users');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_UsersEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('FE_Users_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
        
        
    }

	/**
	 * Implementation of User_Access_EditAction()
	 */
    function User_Access_EditAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $model = new $modelClassName($this);

		if ( $this->parameters->get('uid') )
			$model->accessUpdate();

		// Redirect. Always a good idea to prevent double entries by reload.
		$this->FE_Users_CancelAction();
    }


    function User_Access_FormAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_Access');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_AccessEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this, $object);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('User_Access_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of User_Access_ListAction()
	 */
    function User_Access_ListAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_Access');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_AccessEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('User_Access_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of User_Access_SearchAction()
	 */
    function User_Access_SearchAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_Access');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_AccessEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('User_Access_Search');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of User_Access_ViewAction()
	 */
    function User_Access_ViewAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_Access');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_User_AccessEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('User_Access_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of FE_Users_FormAction()
	 */
    function FE_Users_FormAction($object = null) {
	if ( ! $this->adminOk() && ! $this->managerOk() )
		return $this->Restricted_AccessAction();

	// MLC 20100115 prevent To Be Assigned being edited
	$uid = $this->parameters->get('uid');
	if ( $this->configurations->get('userToBeAssigned') == $uid ) {
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->getDestination());
		$parameters = array(
			'action' => 'FE_Users_Cancel'
			, 'uid' => $uid
			, 'saved' => 'notEditable'
		);
		$link->parameters($parameters);
		$link->noHash();
		$link->redirect();
	}

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_FE_Users');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_Users');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_FE_UsersEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this, $object);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('FE_Users_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
        
        
    }

    function FE_Users_CancelAction() {
		if ( ! $this->adminOk() && ! $this->managerOk() )
			return $this->Restricted_AccessAction();

		// Redirect. Always a good idea to prevent double entries by reload.
		$linkClassName = tx_div::makeInstanceClassName('tx_lib_link');
		$link = new $linkClassName();
		$link->designator($this->getDesignator());
		$link->destination($this->configurations->get('usersPid'));
		$parameters = array(
			'action' => 'FE_Users_View'
			, 'uid' => $this->parameters->get('uid')
			, 'agencyId' => $this->parameters->get('agencyId')
			, 'saved' => $this->parameters->get('saved')
		);
		$link->parameters($parameters);
		$link->noHash();
		$link->redirect();
    }

    function User_Access_CancelAction() {
		return $this->FE_Users_CancelAction();
    }
    
    
    
    
    //Added by LOU from Reporting Controller for Downloading
    function Download_Action($data, $filename) {
		$data = preg_replace('#&nbsp;#i', '', $data);
		$data = preg_replace('#<a[^>]*>([^<]+)</a>#i', '\1', $data);
		header('Content-type: application/force-download');
		header('Content-Transfer-Encoding: Binary');
		header('Content-length: '.strlen($data));
		header('Content-disposition: attachment; filename="'.$filename.'"');
		echo $data;
	}
    
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_User.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_User.php']);
}

?>
