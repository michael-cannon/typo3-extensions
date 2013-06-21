<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Michael Cannon <mc@aihr.us>
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
 *
 *
 *   56: class tx_wecservant_modfunc1 extends t3lib_extobjbase
 *   64:     function modMenu()
 *   77:     function reportMailer()
 *  199:     function prepareWriteReport()
 *  211:     function main()
 *  315:     function getDateOptions()
 *  344:     function getMinistryOptions()
 *  383:     function hasRegistrants()
 *  393:     function prepareReportData()
 *  496:     function writeReport( $report, $filename )
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(PATH_t3lib.'class.t3lib_extobjbase.php');


/**
 * Module extension (addition to function menu) 'WEC Servant Reporting' for the 'wec_servant' extension.
 *
 * @author	Michael Cannon <mc@aihr.us>
 * @package	TYPO3
 * @subpackage	tx_wecservant
 */
class tx_wecservant_modfunc1 extends t3lib_extobjbase {
	var $extDir	= '';

	/**
	 * Returns the module menu
	 *
	 * @return	Array		with menuitems
	 */
	function modMenu()	{
		global $LANG;

		return Array (
			"tx_wecservant_modfunc1_check" => "",
		);
	}

	/**
	 * Basically run through the ministries and for each, run the report and email it.
	 *
	 * @return	string	results
	 */
	function reportMailer() {
		global $TYPO3_DB, $LANG;

		// try to garner poc from group membership
		$select					= '
			g.title ministry,
			g.uid ministry_id,
			g.tx_wecservant_ministryadministrator admins
		';
		$from					= '
			tx_wecgroup_type t
			LEFT JOIN fe_groups g ON t.uid = g.wecgroup_type
		';
		// t.uid = 1 denotes Ministry
		$where					= '
			1 = 1
			AND t.uid = 1
			AND g.hidden = 0
			AND g.deleted = 0
		';

		if ( count( $this->selectedMinistries ) ) {
			$where	.= ' AND g.uid IN (';
			$where	.= implode( ',', $this->selectedMinistries );
			$where	.= ')';
		}

		$groupBy				= '';
		$orderBy				= '';
		$limit					= '';

		// pull ministries id
		$rows					= $TYPO3_DB->exec_SELECTgetRows(
			$select,
			$from,
			$where,
			$groupBy,
			$orderBy,
			$limit
		);

		$ministryCount			= 0;
		$sendDetails			= array();
		$origSelectedMinistries	= $this->selectedMinistries;

		foreach ( $rows as $entry ) {
			$admins				= $entry['admins'];
			$emails				= array();

			if ( ! empty( $admins ) ) {
				// try to garner poc from group membership
				$select			= '
					u.email
				';
				$from			= '
					be_users u
				';
				$where			= "
					1 = 1
					AND u.disable = 0
					AND u.deleted = 0
					AND FIND_IN_SET (u.uid, '{$admins}')
				";
				// TODO make this work
				// $where			.= ' AND ' . $TYPO3_DB->listQuery($admins,'u.ui','be_users');
				$groupBy		= '';
				$orderBy		= '';

				$res			= $TYPO3_DB->exec_SELECTquery(
					$select,
					$from,
					$where,
					$groupBy,
					$orderBy
				);

				while( true == ( $row = $TYPO3_DB->sql_fetch_assoc( $res ) ) ) {
					$emails[]	= $row['email'];
				}
			}

			if ( empty( $emails ) ) {
				$emails[]		= $LANG->getLL('mailer.noadmin');
			}

			// run report
			$this->selectedMinistries  	= array( $entry['ministry_id'] );
			$this->prepareWriteReport();

			$mailer				= t3lib_div::makeInstance('t3lib_htmlmail');
			$mailer->start();
			$mailer->useBase64();

			$mailer->subject	= sprintf( $LANG->getLL('email.subject'), $entry['ministry'] );
			$mailer->from_name	= $LANG->getLL('email.from.name');
			$mailer->from_email	= $LANG->getLL('email.from');

			$mailer->addPlain( sprintf( $LANG->getLL('email.body'), $entry['ministry'] ) );
			$mailer->addAttachment( $this->extDir . $this->filename );
			$mailer->setHeaders();
			$mailer->setContent();
			$mailer->setRecipient( $emails );
			$mailer->sendtheMail();

			unlink( $this->extDir . $this->filename );
			$this->selectedMinistries  	= array();
			$ministryCount++;

			$sendDetails[]		= sprintf( $LANG->getLL('mailer.sentto'), $entry['ministry'], implode( ', ', $emails ) );
		}

		$this->selectedMinistries	= $origSelectedMinistries;

		$details				= array(
			'header'			=> $LANG->getLL('mailer.header'),
			'description'		=> sprintf( $LANG->getLL('mailer.done'), $ministryCount ),
			'sentinfo'			=> $sendDetails
		);

		return $details;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function prepareWriteReport() {
		$reportData				= $this->prepareReportData();

		// formats report data into Excel compatible spreadsheet
		$this->writeReport( $reportData, $this->filename );
	}

	/**
	 * Main method of the module
	 *
	 * @return	HTML
	 */
	function main()	{
		// global $SOBE, $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS, $TYPO3_DB;
		global $LANG, $TYPO3_DB;

		// MLC determine if current page contains registrant records to report upon
		$regCount		= $this->hasRegistrants();
		if ( $regCount ) {
			$directions	= $LANG->getLL('directions');
		} else {
			$directions	= $LANG->getLL('directions.noregistrants');
		}

		$theOutput	.= $this->pObj->doc->spacer(5);
		$theOutput	.= $this->pObj->doc->section( $LANG->getLL('title'), $directions, 0, 1 );

		// no registrants, bail till they're found
		if ( ! $regCount ) {
			return $theOutput;
		}

		$this->init();

		// MLC check for delete request
		$delete					= t3lib_div::_GP('delete');
		if ( $LANG->getLL('btnDeleteReport') == $delete ) {
			unlink( $this->extDir . $this->filename );
		}

		$doMailer					= t3lib_div::_GP('doMailer');
		if ( ! empty( $doMailer ) && $LANG->getLL('btnDoMailer') == $doMailer ) {
			$details			= $this->reportMailer();
			$description		= $details['description'];
			$description		.= '<br /><br />';
			$sentinfo			= implode( '<br />', $details['sentinfo'] );
			$description		.= $sentinfo;

			$flashDetails		= t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$description,
				$details['header'],
				t3lib_FlashMessage::OK,
				true
			);
			t3lib_FlashMessageQueue::addMessage( $flashDetails );
			$theOutput			.= t3lib_FlashMessageQueue::renderFlashMessages();
		}

		// MLC check for download request
		$download				= t3lib_div::_GP('download');
		if ( ! empty( $download ) && $LANG->getLL('btnSubmit') == $download ) {
			$this->prepareWriteReport();
		}

		$options	= array();

		// MLC show select list of ministries
		$options[]	= $this->getMinistryOptions();
		$options[]	= '<br />';
		$options[]	= '<br />';

		// MLC show date range options
		$options[]	= $this->getDateOptions();
		$options[]	= '<br />';
		$options[]	= '<br />';

		$dates		= '';
		$options[]	= $dates;

		// MLC show download report button
		$options[]	= '<input type="submit" name="download" value="' . $LANG->getLL('btnSubmit') . '" />';
		$options[]	= '&nbsp;';
		$options[]	= '<input type="submit" name="doMailer" value="' . $LANG->getLL('btnDoMailer') . '" />';
		$options[]	= '&nbsp;';
		$options[]	= '<input type="reset" value="' . $LANG->getLL('btnReset') . '" />';

		if ( file_exists( $this->extDir . $this->filename ) ) {
			// MLC delete report
			$options[]	= '&nbsp;';
			$options[]	= '<input type="submit" name="delete" value="' . $LANG->getLL('btnDeleteReport') . '" />';

			$path		= '/typo3conf/ext/wec_servant/';
			$options[]	= '<h3><blink><a style="color: red;" href="' . $path . $this->filename . '">' . $LANG->getLL('downloadReport') . '</a></blink></h3>';
		}

		$theOutput	.= $this->pObj->doc->spacer(5);
		$theOutput	.= $this->pObj->doc->section( $LANG->getLL('options'), implode('', $options ), 0, 1 );

		return $theOutput;
	}

	function init() {
		global $LANG;
		$LANG->includeLLFile('EXT:wec_servant/modfunc1/locallang.xml');

		$this->extDir				= t3lib_extMgm::extPath('wec_servant');
		$this->selectedMinistries	= t3lib_div::_GP('ministries');
		if ( ! is_array( $this->selectedMinistries ) ) {
			$this->selectedMinistries	= array();
		}

		$this->dateStartOrig	= t3lib_div::_GP('dateStart');
		if ( empty( $this->dateStartOrig ) ) {
			$this->dateStart	= false;
		} else {
			$this->dateStart	= strtotime( $this->dateStartOrig, time() );
		}

		$this->dateStopOrig		= t3lib_div::_GP('dateStop');
		if ( empty( $this->dateStopOrig ) ) {
			$this->dateStop		= false;
		} else {
			$this->dateStop		= strtotime( $this->dateStopOrig, time() );
		}

		// report filename
		$this->filename			= $LANG->getLL('reportFilename') . '.xls';
	}

	/**
	 * Returns array of date options
	 *
	 * return	array
	 *
	 * @return	[type]		...
	 */
	function getDateOptions() {
		global $LANG;

		$options	= array();
		$options[]	= '<h4>' . $LANG->getLL('dateRange') . '</h4>';
		$options[]	= '<p>' . $LANG->getLL('directions.dates') . ' <a href="' . $LANG->getLL('directions.dates.reference.url') . '" target="_blank">' . $LANG->getLL('directions.dates.reference') . '</a></p>';

		$options[]	= '<label for="dateStart">' . $LANG->getLL('directions.dates.start') . '</label>';
		$options[]	= '<input type="input" id="dateStart" name="dateStart"';
		$options[]	= ' value="' . $this->dateStartOrig . '"';
		$options[]	= ' />';
		$options[]	= '&nbsp;';

		$options[]	= '<label for="dateStop">' . $LANG->getLL('directions.dates.stop') . '</label>';
		$options[]	= '<input type="input" id="dateStop" name="dateStop"';
		$options[]	= ' value="' . $this->dateStopOrig . '"';
		$options[]	= ' />';


		return implode( $options );
	}

	/**
	 * Returns array of ministry options
	 *
	 * return	array
	 *
	 * @return	[type]		...
	 */
	function getMinistryOptions() {
		global $LANG;

		$options	= array();
		$options[]	= '<h4>' . $LANG->getLL('ministries') . '</h4>';
		$options[]	= '<p>' . $LANG->getLL('directions.ministries') . '</p>';
		$options[]	= '<select name="ministries[]" size="10" multiple="multiple">';

		$minOpts	= t3lib_befunc::getRecordsByField('fe_groups', 'wecgroup_type', 1, '', '',  'title');

		foreach ( $minOpts as $key => $min ) {
			$uid		= $min['uid'];

			$option		= '';
			$option		.= '<option value="';
			$option		.= $uid;
			$option		.= '"';

			if ( in_array( $uid, $this->selectedMinistries ) ) {
				$option		.= ' selected="selected"';
			}

			$option		.= '>';
			$option		.= $min['title'];
			$option		.= '</option>';
			$options[]	= $option;
		}

		$options[]	= '</select>';
		$options[]	= $ministries;

		return implode( $options );
	}

	/**
	 * Looks to if current selected page contains registrants.
	 *
	 * @return	int/boolean		count of registrants found or false if none
	 */
	function hasRegistrants() {
		$where	= 'r.pid = ' .intval($this->pObj->id);

		return t3lib_befunc::getRecordRaw( 'tx_wecservant_registrant r', $where );
	}
	/**
	 * Pulls and rough formats report data
	 *
	 * @return	string
	 */
	function prepareReportData() {
		global $TYPO3_DB, $LANG;

		$report		= array();

		// MLC make db query
		// MLC grab all ministries and opportunities, not just those with registrants
		// do so by polling all or limited ministries first
		// then pull registrants
		$where	= 'mo.ministry_uid = g.uid';

		if ( count( $this->selectedMinistries ) ) {
			$where	.= ' AND g.uid IN (';
			$where	.= implode( ',', $this->selectedMinistries );
			$where	.= ')';
		}

		$res = $TYPO3_DB->exec_SELECTquery(
			'g.uid ministryid, g.title ministry, mo.uid opportunityid, mo.name opportunity, mo.reference_code, mo.openings needed, mo.youth_friendly, mo.supervision_required',
			'fe_groups g, tx_wecservant_minopp mo',
			$where,
			'', // group by ministries
			'g.title, mo.name' // sort by minop, last name, first
		);

		// MLC create report parts
		$date			= date('n/j/Y');

		while( TRUE == ( $row = $TYPO3_DB->sql_fetch_assoc( $res ) ) ) {
			// has current ministry been prepared for?
			// if not, create high-level entries of date, title
			$curMinistry	= $row['ministryid'];
			if ( ! isset( $report[ $curMinistry ] ) ) {
				$report[ $curMinistry ]['date']				= $date;
				$report[ $curMinistry ]['ministry']			= $row['ministry'];
				$report[ $curMinistry ]['opportunities']	= array();
			}

			// has current ministry opportunity been prepared for?
			// if not, create high-level entries of name, needed, fulfilled
			$curMinOp		= $row['opportunityid'];
			if ( ! isset( $report[ $curMinistry ]['opportunities'][ $curMinOp ] ) ) {
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['opportunity']	= $row['opportunity'];
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['reference_code']	= $row['reference_code'];
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['needed']			= $row['needed'];
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['fulfilled']		= 0;
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['registrants']	= array();
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['youth_friendly']	= $row['youth_friendly'];
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['supervision_required']	= $row['supervision_required'];
			}

			// MLC grab registrant details
			$where	= 'r.pid = ' .intval($this->pObj->id);
			$where	.= ' AND r.minopp = '. $curMinOp;
			$where	.= ' AND r.user_id = u.uid';

			// keep duplicates out of reporting
			$where	.= ' AND duplicate = 0';

			if ( $this->dateStart ) {
				$where	.= ' AND r.crdate >= ' . $this->dateStart;
			}

			if ( $this->dateStop ) {
				$where	.= ' AND r.crdate <= ' . $this->dateStop;
			}

			$regRes = $TYPO3_DB->exec_SELECTquery(
				'u.tx_wecservant_shelbyid shelbyid, u.first_name, u.last_name, u.email, u.telephone, r.youth, r.parents_name, r.crdate, r.waitlist',
				'tx_wecservant_registrant r, fe_users u',
				$where,
				'',
				'u.last_name, u.first_name' // sort by minop, last name, first
			);

			while( TRUE == ( $regRow = $TYPO3_DB->sql_fetch_assoc( $regRes ) ) ) {
				// push registrant details to lower level
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['registrants'][]	= array(
					'shelbyid'		=> $regRow['shelbyid'],
					'first_name'	=> $regRow['first_name'],
					'last_name'		=> $regRow['last_name'],
					'email'			=> $regRow['email'],
					'telephone'		=> $regRow['telephone'],
					'youth'			=> $regRow['youth'] ? $LANG->getLL('reportHeaderYouth.yes') : $LANG->getLL('reportHeaderYouth.no'),
					'parents_name'	=> $regRow['parents_name'],
					'date'			=> date('n/j/Y', $regRow['crdate']),
					'waitlist'		=> $regRow['waitlist'] ? $LANG->getLL('reportHeaderYouth.yes') : $LANG->getLL('reportHeaderYouth.no'),
				);

				// keep track of fulfilled by counting registrants
				$report[ $curMinistry ]['opportunities'][ $curMinOp ]['fulfilled']++;
			}
		}

		return $report;
	}

	/**
	 * Saves report in XLS format to server
	 *
	 * @param	array		report parts by ministry, opportunities, opportunity, registrants
	 * @param	string		report filename
	 * @return	void
	 */
	function writeReport( $report, $filename ) {
		global $LANG;

		require_once 'Spreadsheet/Excel/Writer.php';

		// MLC TODO check for file write permissions

		$xls	= new Spreadsheet_Excel_Writer( $this->extDir . $filename );
		// $xls->send($filename);
		$sheet	=& $xls->addWorksheet( $LANG->getLL('reportWorksheetTitle') );

		$titleFormat	=& $xls->addFormat();
		// $titleFormat->setFontFamily('Helvetica');
		$titleFormat->setBold();
		// $titleFormat->setSize('13');
		// $titleFormat->setColor('navy');
		// $titleFormat->setBottom(2);
		// $titleFormat->setBottomColor('navy');

		$errorFormat	=& $xls->addFormat();
		$errorFormat->setColor('red');

		if ( ! is_array( $report ) ) {
			$sheet->write( 1, 0, $LANG->getLL('reportNoDataFound') );
			$xls->close();
			return;
		}

		// spreadsheet coordinates, remember working from top left to bottom right
		$y	= 0; // AKA 1

		// MLC cycle through ministries
		foreach ( $report as $ministries ) {
			$x	= 0; // AKA A

			$sheet->write( $y, $x++, $LANG->getLL('reportHeaderDate'), $titleFormat );
			$sheet->write( $y, $x++, $ministries['date'], $titleFormat );
			$sheet->write( $y, $x++, $LANG->getLL('reportHeaderMinistry'), $titleFormat );
			$sheet->write( $y, $x++, $ministries['ministry'], $titleFormat );

			// MLC cycle through opportunities
			foreach ( $ministries['opportunities'] as $opportunities => $opportunity ) {
				// next row
				$y++;
				$x	= 2; // AKA C

				$sheet->write( $y, $x++, $LANG->getLL('reportHeaderOpportunity') );
				$sheet->write( $y, $x++, $opportunity['opportunity'] );
				$sheet->write( $y, $x++, $LANG->getLL('reportHeaderReferenceCode') );
				$sheet->write( $y, $x++, $opportunity['reference_code'] );
				$sheet->write( $y, $x++, $LANG->getLL('reportHeaderNumberNeeded') );
				$needed	= $opportunity['needed'];
				if ( '' != $needed && 0 < $needed ) {
					$sheet->write( $y, $x++, $opportunity['needed'], $errorFormat );
				} else {
					$sheet->write( $y, $x++, $opportunity['needed'] );
				}
				$sheet->write( $y, $x++, $LANG->getLL('reportHeaderNumberFulfilled') );
				$fulfilled	= $opportunity['fulfilled'];
				if ( '' != $fulfilled && 0 == $fulfilled ) {
					$sheet->write( $y, $x++, $opportunity['fulfilled'], $errorFormat );
				} else {
					$sheet->write( $y, $x++, $opportunity['fulfilled'] );
				}

				$sheet->write( $y, $x++, $LANG->getLL('reportHeaderKidFriendly') );
				// yes or no from 1 or 0
				$youth_friendly			= $opportunity['youth_friendly'] ? $LANG->getLL('reportHeaderKidFriendly.yes') : $LANG->getLL('reportHeaderKidFriendly.no');
				$sheet->write( $y, $x++, $youth_friendly );

				$sheet->write( $y, $x++, $LANG->getLL('reportHeaderSupervisionRequired') );
				$supervision_required	= $opportunity['supervision_required'] ? $LANG->getLL('reportHeaderSupervisionRequired.yes') : $LANG->getLL('reportHeaderSupervisionRequired.no');
				$sheet->write( $y, $x++, $supervision_required );

				// MLC cycle through registrants
				$registrantsHeaderRow	= true;
				foreach ( $opportunity['registrants'] as $registrants => $registrant ) {
					if ( $registrantsHeaderRow ) {
						// next row
						$y++;
						$x	= 2; // AKA C
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderShelbyId') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderFirstName') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderLastName') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderEmail') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderPhone') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderYouth') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderParentsName') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderVolunteerDate') );
						$sheet->write( $y, $x++, $LANG->getLL('reportHeaderWaitlist') );
						$registrantsHeaderRow	= false;
					}

					// next row
					$y++;
					$x	= 2; // AKA C

					$sheet->write( $y, $x++, $registrant['shelbyid'] );
					$sheet->write( $y, $x++, $registrant['first_name'] );
					$sheet->write( $y, $x++, $registrant['last_name'] );
					$sheet->write( $y, $x++, $registrant['email'] );
					$sheet->write( $y, $x++, $registrant['telephone'] );
					$sheet->write( $y, $x++, $registrant['youth'] );
					$sheet->write( $y, $x++, $registrant['parents_name'] );
					$sheet->write( $y, $x++, $registrant['date'] );
					$sheet->write( $y, $x++, $registrant['waitlist'] );
				}

				// blank row between opportunities
				$y++;
			}

			// blank row between ministries
			$y++;
		}

		$xls->close();
		return;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_servant/modfunc1/class.tx_wecservant_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_servant/modfunc1/class.tx_wecservant_modfunc1.php']);
}

?>