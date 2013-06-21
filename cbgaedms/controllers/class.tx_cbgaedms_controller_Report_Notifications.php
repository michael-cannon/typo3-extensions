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
 * Class that implements the controller "Report_Notifications" for tx_cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

// tx_div::load('tx_cbgaedms_controller_common');
include_once('class.tx_cbgaedms_controller_common.php');

class tx_cbgaedms_controller_Report_Notifications extends tx_cbgaedms_controller_common {

	var $targetControllers = array('Report_Notifications_EditAction');

    function tx_cbgaedms_controller_Report_Notifications($parameter1 = null, $parameter2 = null) {
        parent::tx_cbgaedms_controller_common($parameter1, $parameter2);
    }

	/**
	 * Implementation of Report_Notifications_EditAction()
	 */
    function Report_Notifications_EditAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

		// The data come in as form parameters upon the first call ...
		$validator = $this->getValidator();
		if( ! $validator->ok() )
			return $this->Report_Notifications_FormAction($validator);
		
		$modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_reports');
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

    function Report_Notifications_ListClearAction() {
		$this->parameters->set('uid', null);
        return $this->Report_Notifications_ListAction();
    }

	/**
	 * Implementation of Report_Notifications_ListAction()
	 */
    function Report_Notifications_ListAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_reports');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_Notifications');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_NotificationsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }

        $view->render('Report_Notifications_List');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Report_Notifications_ViewAction()
	 */
    function Report_Notifications_ViewAction() {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_reports');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_Notifications');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_NotificationsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->load($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
        }
        $view->render('Report_Notifications_View');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	/**
	 * Implementation of Report_Notifications_FormAction()
	 */
    function Report_Notifications_FormAction($object = null) {
		if ( ! $this->adminOk() )
			return $this->Restricted_AccessAction();

        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_reports');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_Notifications');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_NotificationsEntry');
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

        $view->render('Report_Notifications_Form');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

    function Report_Notifications_CronAction() {
        $modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_reports');
        $viewClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_Notifications');
        $entryClassName = tx_div::makeInstanceClassName('tx_cbgaedms_view_Report_NotificationsEntry');
		$translatorClassName = tx_div::makeInstanceClassName('tx_lib_translator');
        $view = new $viewClassName($this);
        $model = new $modelClassName($this);
        $model->cronLoad($this->parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $entry = new $entryClassName($model->current(), $this);
            $view->append($entry);
			$this->sendReport($entry);
        }

        $view->render('Report_Notifications_Cron');
		$translator = new $translatorClassName($this, $view);
		$out = $translator->translateContent();
        return $out;
    }

	function sendReport($entry) {
		$report = $this->getReport($entry);
		$emailFrom = $this->configurations->get('emailFrom');
		$emailSubject = $this->configurations->get('emailSubject');
		$emailSubject .= ' ' . $entry->asReportType($entry->asInteger('report'), true);
		$emailTo = $entry->asUsersEmail('recipients');
		$messageBody = $entry->get('messagebody');

		$filename = $report['filename'];
		$data = $report['out'];
		$data = preg_replace('#&nbsp;#i', '', $data);
		// remove links
		$data = preg_replace('#<a[^>]*>([^<]+)</a>#i', '\1', $data);
		$fileType = 'application/xls'; 

		require_once(dirname(__FILE__).'/../res/htmlMimeMail.php');

		$mail = new htmlMimeMail();
		$mail->setFrom($emailFrom);
		$mail->setSubject($emailSubject);
		$mail->setText($messageBody);
		$mail->setTextCharset('utf-8');
		$mail->setHeadCharset('utf-8');
		$mail->setTextEncoding('8bit');
		$mail->addAttachment($data, $filename, $fileType);
		$result = $mail->send(explode(',', $emailTo));
	}

	function getReport($entry) {

		$action = '';
		switch( $entry->get('report' ) ) {
			case 1:
				$action = 'Report_Document_TypesAction';
				break;
			case 2:
				$action = 'Report_Document_Latest_ChangesAction';
				break;
			case 3:
				$action = 'Report_Location_ProfilesAction';
				break;
		}

		$parameters = new tx_lib_parameters($this);
		$parameters->set('download', 2);
		$controller = new tx_cbgaedms_controller_Reporting();
		$controller->main(null, $this->configurations, null, $parameters);

		return $controller->$action();
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Report_Notifications.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/controllers/class.tx_cbgaedms_controller_Report_Notifications.php']);
}

?>