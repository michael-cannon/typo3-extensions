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
 * Class that implements the controller "Control_Panel" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_Control_Panel extends tx_cbgaedms_controller_common {

	var $targetControllers = array();

    function tx_cbgaedms_controller_Control_Panel($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }

    function Control_Panel_DirectorAction() {
		if ( $this->adminOk() )
			return $this->Control_Panel_AdminAction();
		else
			return $this->Control_Panel_ViewerAction();
	}

	/**
	 * Implementation of Control_Panel_AdminAction()
	 */
    function Control_Panel_AdminAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_Panel');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_PanelEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Control_Panel_Admin');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Control_Panel_ManagerAction()
	 *
	 * MLC not used
	 */
    function Control_Panel_ManagerAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_Panel');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_PanelEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Control_Panel_Manager');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
	}

	/**
	 * Implementation of Control_Panel_ViewerAction()
	 */
    function Control_Panel_ViewerAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_Panel');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_PanelEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Control_Panel_Viewer');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Control_Panel_MiniAction()
	 */
    function Control_Panel_MiniAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_useraccess');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_Panel');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Control_PanelEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
		$this->parameters->set( 'userId', $this->getUserId() );
		$this->parameters->set( 'loadAgencyEntries', true );
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
		if ( $this->accessOk() ) {
			$view->render('Control_Panel_Mini');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			return $out;
		}

		return '';
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Control_Panel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Control_Panel.php']);
}

?>