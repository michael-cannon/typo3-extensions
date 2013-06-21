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
 * Class that implements the controller "Reporting" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_Reporting extends tx_cbgaedms_controller_common {

	var $targetControllers = array();

    function tx_cbgaedms_controller_Reporting($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }


	/**
	 * Implementation of Report_Document_Latest_ChangesAction()
	 */
    function Report_Document_Latest_ChangesAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_docversion');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Reporting');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_ReportingEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
		$period = $this->parameters->get('period');
		if ( ! $period )
			$this->parameters->set('period', 'month');

        $model->loadLatestDocDetails($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }

		if ( ! $this->parameters->get('download') ) {
        	$view->render('Report_Document_Latest_Changes');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			return $out;
		} else {
        	$view->render('Report_Document_Latest_Changes_Detail');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'Report_Document_Latest_Changes.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}
    }

	/**
	 * Implementation of Report_Document_TypesAction()
	 */
    function Report_Document_TypesAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_doctype');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Reporting');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_ReportingEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }

		if ( ! $this->parameters->get('download') ) {
        	$view->render('Report_Document_Types');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			return $out;
		} else {
        	$view->render('Report_Document_Types_Detail');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'Report_Document_Types.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}
    }

	/**
	 * Implementation of Report_LocationAction()
	 */
    function Report_LocationAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Reporting');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_ReportingEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
		if ( $this->parameters->get('agencyStr') ) {
			$model = new $modelClassName($this);
			$model->loadView($this->parameters);
			for($model->rewind(); $model->valid(); $model->next()) {
				$entry = new $entryClassName($model->current(), $this);
				$view->append($entry);
			}
		}

		if ( ! $this->parameters->get('download') ) {
        	$view->render('Report_Location');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			return $out;
		} else {
        	$view->render('Report_Location_Detail');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'Report_Location.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}
    }

	/**
	 * Implementation of Report_Location_DocumentsAction()
	 */
    function Report_Location_DocumentsAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_docversion');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Reporting');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_ReportingEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
		if ( $this->parameters->get('agencyStr') ) {
			$model = new $modelClassName($this);
        	$model->loadLatestDocDetails($this->parameters);
			for($model->rewind(); $model->valid(); $model->next()) {
				$entry = new $entryClassName($model->current(), $this);
				$view->append($entry);
			}
		}

		if ( ! $this->parameters->get('download') ) {
        	$view->render('Report_Location_Documents');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			return $out;
		} else {
        	$view->render('Report_Document_Latest_Changes_Detail');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'Report_Location_Documents.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}
    }

	/**
	 * Implementation of Report_Location_ProfilesAction()
	 */
    function Report_Location_ProfilesAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_agency');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Reporting');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_ReportingEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->loadView($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
		
		if ( ! $this->parameters->get('download') ) {
			$view->render('Report_Location_Profiles');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			return $out;
		} else {
			$view->render('Report_Location_Profiles_Detail');
			$translator = new $translatorClassName($this, $view);
			$out = $translator->translateContent();
			$filename = 'Report_Location_Profiles.xls';
			if ( $this->parameters->get('download') == 1)
				$this->Download_Action($out, $filename);
			else
				return array('out' =>$out, 'filename' => $filename);
		}
    }

	function Download_Action($data, $filename) {
		$data = preg_replace('#&nbsp;#i', '', $data);
		$data = preg_replace('#<a[^>]*>([^<]+)</a>#i', '\1', $data);
		header('Content-type: application/force-download');
		header('Content-Transfer-Encoding: Binary');
		header('Content-length: '.strlen($data));
		header('Content-disposition: attachment; filename="'.$filename.'"');
		echo $data;
	}

    function Control_Panel_ReportsAction() {
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
        $view->render('Control_Panel_Reports');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Reporting.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Reporting.php']);
}

?>