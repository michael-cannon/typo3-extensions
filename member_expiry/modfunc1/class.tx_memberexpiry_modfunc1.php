<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004  ()
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
 * Module extension (addition to function menu) 'Membership Expiry Report' for the 'member_expiry' extension.
 *
 * @author	Jaspreet Singh <>
 * $Id: class.tx_memberexpiry_modfunc1.php,v 1.1.1.1 2010/04/15 10:03:50 peimic.comprock Exp $
 */



require_once(PATH_t3lib."class.t3lib_extobjbase.php");

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

/**
The report wizard for Expiry Reports.
Shows up at Functions: Wizard: Membership Expiry Reports.
Shows an expiring members and renewals report.
Allows for HTML display and CSV download.
Parameterized by no. of days.
*/
class tx_memberexpiry_modfunc1 extends t3lib_extobjbase {
	
    //global database pointer
    var $db;
	
	var $debug = false; //turn on/off debug output
    
    // default size for HTML form element input box
    var $inputSize = 20;
    
 	//aliases for fields E.g., "endtime AS expiredate"
	var $fieldAlias_endtime = 'expiredate';
	
	//option values set by the forms.  They are cached here and redisplayed to the 
	//user on the second time around after he submits the form.
	var $reportExpiringOptionsDays = 14;
	var $reportRenewalsOptionsDays = 14;
	//default value in case this is the first time the form is being displayed.
	var $reportExpiringOptionsDaysDefault = 14;
	var $reportRenewalsOptionsDaysDefault = 14;
	
    function modMenu()	{
		global $LANG;
		
		return Array (
			"tx_memberexpiry_modfunc1_check" => "",
		);		
	}

	/**
    * Main entry point for the report wizard.
    * @return a string containing all the output, which Typo3 will wrap into a 
    * form element inside the functions/wizard area.
    */
    function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		$this->db                = $GLOBALS['TYPO3_DB'];
		$theOutput.=$this->pObj->doc->spacer(5);
		$theOutput.=$this->pObj->doc->section($LANG->getLL("title"),$LANG->getLL('description'),0,1);
		
		
		
		// If this is the 2nd go-around and the user has clicked a button, a GET or POST
		// variable will be set telling us what to do.  Here we check for these variables
		// and act accordingly.
		
		// first, cache the GET/POST option value variables
		$this->setValues();
		
		//Display the report options for display/download.
		//These are displayed regardless of whether a report is currently displaying or not.
		//If there is no report, this will allow the user to pick a report and set its options.
		//If there is already a report, this allows the user to show the same or another
		//report with possibly different options.
		$theOutput .= $this->showOptions();

		// check for display command for expiry report
        if ( t3lib_div::_GP( 'tx_memberexpiry_modfunc1_expiryreport_command_display' ) )
        {
            $theOutput .= $this->doDisplay( 'tx_memberexpiry_modfunc1_expiryreport_command_display' );
        }
		
		// check for display command for renewals report
        elseif ( t3lib_div::_GP( 'tx_memberexpiry_modfunc1_renewalsreport_command_display' ) )
        {
            $theOutput .= $this->doDisplay( 'tx_memberexpiry_modfunc1_renewalsreport_command_display' );
        }
		
		// check for download command for expiry report
        elseif ( t3lib_div::_GP( 'tx_memberexpiry_modfunc1_expiryreport_command_download' ) )
        {
            $theOutput .= $this->doDownload( 'tx_memberexpiry_modfunc1_expiryreport_command_download' );
        }
		
		// check for download command for renewals report
        elseif ( t3lib_div::_GP( 'tx_memberexpiry_modfunc1_renewalsreport_command_download' ) )
        {
            $theOutput .= $this->doDownload( 'tx_memberexpiry_modfunc1_renewalsreport_command_download' );
        }
		

		
		return $theOutput;
	}
	
	/**
	* Shows report options.
	* @return void
    * 
	*/
	function showOptions()
	{
		$output = '';
		
		$output .= $this->showOptionsExpiryReport();
		$output .= $this->showOptionsRenewalReport();
		
		return $output;
	}
	
	/**
	* Returns a string containing the form elements for the options for the expiry
	* report.
	* @return the form elements string
	*/
	function showOptionsExpiryReport()
	{
		global $LANG;
		$output = '';
		
		$break 						= '<br />';
		
								
		
		//Show title section with a bar background and uppercase title text
		$title						= $this->pObj->doc->section( 
										$LANG->getLL('report.expiry.options.sectiontitle')
										, $LANG->getLL('report.expiry.options.helptext')
										, 0
										, 1
										);
										
										
		//For the days input box, we redisplay the previously selected value, if any.  Otherwise the default.
		//This section shown w/o a bar background and with non-uppercase text
		$daysValue					= $this->reportExpiringOptionsDays; //isset($this->reportExpiringOptionsDays) ? $this->reportExpiringOptionsDays : $this->reportExpiringOptionsDaysDefault;   
		$days 						= $this->pObj->doc->section(
										$LANG->getLL('report.expiry.options.days.caption')
										, $this->getFormElementDays('tx_memberexpiry_modfunc1_expiryreport_options_days', $daysValue)
										, 1
										);
		//2 submit buttons: 1 for display, the other for download, plus a reset button.
		$submit                    = '<input type="submit"
                                        name="tx_memberexpiry_modfunc1_expiryreport_command_display"
                                        value="'
                                    . $LANG->getLL( 'report.expiry.options.display.buttontext' )
                                    . '" />';
        $submit                    .= '&nbsp;';
        $submit                    .= '<input type="submit"
                                        name="tx_memberexpiry_modfunc1_expiryreport_command_download"
                                        value="'
                                    . $LANG->getLL( 'report.expiry.options.download.buttontext' )
                                    . '" />';
        $submit                    .= '&nbsp;';
        $submit                    .= '<input type="reset" value="'
                                    . $LANG->getLL( 'report.expiry.options.reset.buttontext' )
                                    . '" />';

		//assemble the form elements in order
		$output .= $title
					. $this->pObj->doc->spacer(5)
					. $days
					. $this->pObj->doc->spacer(5)
					. $submit;
		return $output;
	}

	/**
     * Returns string containing the form element for days.
     *
     * @param string field name
	 * @param initial value of the element. Blank by default.
     * @return the form element string
     */
    function getFormElementDays ( $fieldname, $value='' )
    {
        $string                    = 
            '<input type="text"'
                .' name="' . $fieldname . '"'
                .' size="' . $this->inputSize .'"' 
				.' value="' . $value . '" />'
        ;

        return $string;
    }

	
	/**
	* Cache the values from GET/POST to class variables.
	*/
	function setValues()
	{
		$this->reportExpiringOptionsDays = t3lib_div::_GP( 'tx_memberexpiry_modfunc1_expiryreport_options_days' );
		if (''==$this->reportExpiringOptionsDays) {
			$this->reportExpiringOptionsDays = $this->reportExpiringOptionsDaysDefault; 
		}
		$this->reportRenewalsOptionsDays = t3lib_div::_GP( 'tx_memberexpiry_modfunc1_renewalsreport_options_days' );
		if (''==$this->reportRenewalsOptionsDays) {
			$this->reportRenewalsOptionsDays = $this->reportRenewalsOptionsDaysDefault; 
		}
	}
	
	/**
	* Returns a string containing the form elements for the options for the rewewals
	* report.
	* @return the form elements string
	*/
	function showOptionsRenewalReport()
	{
		global $LANG;
		$output = '';
		
		$break 						= '<br />';
		
										
		
		//Show title section with a bar background and uppercase title text
		$title						= $this->pObj->doc->section( 
										$LANG->getLL('report.renewals.options.sectiontitle')
										, $LANG->getLL('report.renewals.options.helptext')
										, 0
										, 1
										);
		//For the days input box, we redisplay the previously selected value, if any.  Otherwise the default.
		//This section shown w/o a bar background and with non-uppercase text
		$daysValue					= $this->reportRenewalsOptionsDays; //(isset($this->reportRenewalsOptionsDays)) ? $this->reportRenewalsOptionsDays : $this->reportRenewalsOptionsDaysDefault;   
		$days 						= $this->pObj->doc->section(
										$LANG->getLL('report.renewals.options.days.caption')
										, $this->getFormElementDays('tx_memberexpiry_modfunc1_renewalsreport_options_days', $daysValue)
										, 1
										);
		//2 submit buttons: 1 for display, the other for download, plus a reset button.
		$submit                    = '<input type="submit"
                                        name="tx_memberexpiry_modfunc1_renewalsreport_command_display"
                                        value="'
                                    . $LANG->getLL( 'report.renewals.options.display.buttontext' )
                                    . '" />';
        $submit                    .= '&nbsp;';
        $submit                    .= '<input type="submit"
                                        name="tx_memberexpiry_modfunc1_renewalsreport_command_download"
                                        value="'
                                    . $LANG->getLL( 'report.renewals.options.download.buttontext' )
                                    . '" />';
        $submit                    .= '&nbsp;';
        $submit                    .= '<input type="reset" value="'
                                    . $LANG->getLL( 'report.renewals.options.reset.buttontext' )
                                    . '" />';

									
		//assemble the form elements in order
		$output .= $title
					. $this->pObj->doc->spacer(5)
					. $days
					. $this->pObj->doc->spacer(5)
					. $submit;
		return $output;

	}
	

	/**
	* Displays report(s)
	* @param the command specifying which report to display.  This is the same
	* 	value as is sent by the submit button for the asssociated report.
	*/
	function doDisplay( $what ) 
	{
		$output = '';
		
		if ('tx_memberexpiry_modfunc1_expiryreport_command_display' == $what) {
			$output .= $this->expiryReportDisplay();
		}
		if ('tx_memberexpiry_modfunc1_renewalsreport_command_display' == $what) {
			$output .= $this->renewalsReportDisplay();
		}
		
		return $output;
	}
	
    /**
     * Determine which download is being requested and redirect to it.
     *
     * @return void
     */
    function doDownload ( $what )
    {
		if ('tx_memberexpiry_modfunc1_expiryreport_command_download' == $what) {
			$output .= $this->expiryReportDownload();
		}
		if ('tx_memberexpiry_modfunc1_renewalsreport_command_download' == $what) {
			$output .= $this->renewalsReportDownload();
		}
    }
	
    /**
     * Offer up the expiry report for download to the user.
     *
     * @return void
     */
    function expiryReportDownload()
    {
        $rows                = $rows = $this->expiryReportGetRows();
        $rowsFormatted      = $this->expiryReportFormatRows($rows);

        // convert rows to csv for download
        $rowsCsv                = '';

        foreach ( $rowsFormatted as $key => $value )
        {
            $rowsCsv            .= cbMkCsvString( $value );
        }

		$filename				= $this->getReportFilename( 'Report_Expiring_Members_' );
		
        // download members list
        // send to browser as download
        cbBrowserDownload( $filename, $rowsCsv );
        exit();
    }

    /**
     * Offer up the renewals report for download to the user.
     *
     * @return void
     */
    function renewalsReportDownload()
    {
        $rows                = $rows = $this->renewalsReportGetRows();
        $rowsFormatted      = $this->renewalsReportFormatRows($rows);

        // convert rows to csv for download
        $rowsCsv                = '';

        foreach ( $rowsFormatted as $key => $value )
        {
            $rowsCsv            .= cbMkCsvString( $value );
        }

		$filename				= $this->getReportFilename( 'Report_Renewals_' );
		
        // download members list
        // send to browser as download
        cbBrowserDownload( $filename, $rowsCsv );
        exit();
    }

	
	
	/**
	* Creates a filename for report download files.  It does this by attaching
	* the date and filename extension to the basic report name
	* @param the basic report name.
	* @return the filename
	*
	*/
	function getReportFilename( $basename ) {
        
		$dateFormatted 		= date('F j Y', $this->getToday() );  
		$filename              = $basename . $dateFormatted . '.csv';
		$filename              = $this->spaceToUnderscore( $filename );
		return $filename;
	}
	
	/**
	* Converts embedded spaces in the passed string to underscores.
	* @param the string having having spaces
	* @return the string with spaces converted to underscores
	*/
	function spaceToUnderscore($string)
	{
		return preg_replace( '/ /', '_', $string);
	}

    
    /**
	* Displays the expiring members report.
	* @return void
	*/
	function expiryReportDisplay() 
	{
		global $LANG;
		
		//get the rows
		$rows = $this->expiryReportGetRows();
		
		//and format the data, and convert to HTML with a title, before returning them.
		return cbArr2Html( $this->expiryReportFormatRows($rows), $LANG->getLL( 'report.expiry.title' ) );
	}
	
	/**
	* Displays the membership renewals report.
	* @return void
	*/
	function renewalsReportDisplay() 
	{
		global $LANG;
		
		//get the rows
		$rows = $this->renewalsReportGetRows();
		
		//and format the data, and convert to HTML with a title, before returning them.
		return cbArr2Html( $this->renewalsReportFormatRows($rows), $LANG->getLL( 'report.renewals.title' ) );
	}

	
	/**
	* Returns today's date for the purposes of reporting.  This is a fixed time
	* corresponding to 12:01 midnight, which is the same time used by the expiry
	* email notification plugin.
	* @param whether or not the return value should be formatted
	* @return if $formatted is false, the date in Unix time.  If $formatted is true,
	*	the date as formatted text.
	*/
    function getToday($formatted=false)
    {
		$today = mktime(0, 1, 0, date("m")  , date("d"), date("Y"));
		if ($this->debug) {
			echo '$today ' . $today; //debug
			echo ' time() ' . time(); //debug
		}
        if (false==$formatted) {
            $retVal = $today;
        } else {
			$retVal = $this->formatEventTime('date', $today); 
		}
		return $retVal;
    }
    
    /**
	* Retrieves and returns the data rows for the expiry report.
	* @return the data rows.
	*/
	function expiryReportGetRows()
	{
		if ($this->debug) {
			echo 'expiryReportGetRows()<br>';
		}

		$SECONDS_PER_DAY = 86400;
		$COMPLIMENTARY_MEMBER_GROUPID = 1;
		$PROFESSIONAL_MEMBER_GROUPID = 2;
		$COMPLIMENTARY_PROF_MEMBER_GROUPID =4;

		$days = $this->reportExpiringOptionsDays;
		$today = $this->getToday();
		
        
		//Example: Need to know which items expire 1 day from today.  
        //min below would be today + 1day
        //max would be min + 1 day.
        //Then find all rows that have endtime between min and max
		$expiry_period_min = $today;
		$expiry_period_max = $expiry_period_min + $days*$SECONDS_PER_DAY;

		
        $where = "endtime >= $expiry_period_min and endtime < $expiry_period_max ";
		$where .= 'AND ( usergroup REGEXP "[[:<:]]'.$PROFESSIONAL_MEMBER_GROUPID.'[[:>:]]" ' 
			. 'OR usergroup REGEXP "[[:<:]]'.$COMPLIMENTARY_PROF_MEMBER_GROUPID.'[[:>:]]" )';
		$where .= ' AND deleted != 1  AND disable !=1 ';
		$columns = 'name, email, company, endtime AS expiredate, usergroup,  telephone, title';
        $groupby = '';
		$orderby = 'expiredate, name';
		$expiring_rows =  $this->db->exec_SELECTgetRows($columns, 'fe_users', $where, $groupby, $orderby  );
		
		if (!is_array($expiring_rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$expiring_rows=array();
		}

		if ($this->debug) {
			echo $where;
			echo 'Expiring rows:';
			foreach ( $expiring_rows as $row ) {
				print_r($row);
				//$output .= '';
			}
			reset( $expiring_rows );
		}
		return $expiring_rows;
	}
	
	/**
	* Iterates over query results to return human readable text.
	* Substitutes replacement text for id numbers.
	* Depends on certain fieldnames in the query/results being the same as those
	* specified in this function.
	* @param an array of rows with unformatted data
	* @return an array of rows with formatted data.
	*/
	function expiryReportFormatRows( &$rows )
	{
		//copy the original rows array to a working array
		$rowsFormatted = $rows;
		if ($this->debug) {
			echo 'expiryReportFormatRows( &$rows )';
			echo 'count($rows): ' . count($rows);
		}
		
		//for each row, substitute formatted text in the working array
		for( $i=0; $i<count($rows); $i++ ) {
			
			$row = $rows[$i];
			//time
			$rowsFormatted[$i][$this->fieldAlias_endtime] = $this->formatEventTime('date', $row[$this->fieldAlias_endtime]);
			//usergroup
			$rowsFormatted[$i]['usergroup'] = $this->formatUserGroup($row['usergroup']);
			
			if ($this->debug) {
				echo ' iteration '.$i;
				echo ' '.$rowsFormatted[$i][$this->fieldAlias_endtime];
				echo ' '.$rowsFormatted[$i]['usergroup'];
			}
		}
		return $rowsFormatted;
	}
	
	/**
	* Generates the formatted text for the event date.
	* This could be either in the form "in 5 days" or "June 5, 2000", depending on the 
	* value of $type.
	* @param the desired text output type.  'days' for style "in 5 days", 'date' for
	*	"June 5, 2000".
	* @param if $type is 'date', the date/time in Unix time.  If $type is
	*	'days', the no. of days in which expiry will occur.
	* @return the formatted date text
	*/
	function formatEventTime($type, $eventtime)
	{	
		
		$formattedText = '';
		switch ($type) {
			case 'date': $formattedText = date('F j, Y', $eventtime ); break;
			case 'days': $formattedText = "$eventtime days"; break;
			default: $formattedText = date('F j, Y', $eventtime ); 
				echo 'Error in getEventTimeFormatted.';
				break;
		}
		
		return $formattedText;
	}

	/**
	* Generates formatted text for user group.
	* This could be either in the form "in 5 days" or "June 5, 2000", depending on the 
	* value of $type.
	* @param the user group as a series of comma-separated numbers.
	*	e.g., "2,9,10".
	* @return the formatted user group text.  E.g., "Professional Member, BPM - Chicago 2005, BPM - San Francisco 2005"
	*/
	function formatUserGroup($usergroup)
	{	
		
		$usergroupTranslationTable = array(
                '1'                        => 'Member'
                , '2'                    => 'Professional Member'
                , '4'                    => 'Complimentary'
                , '5'                    => 'Visitor, White Paper'
                , '6'                    => 'Visitor, Round Table'
                , '7'                    => 'Visitor, Presentation'
                , '8'                    => 'Editor Preview'
                , '9'                    => 'BPM - Chicago 2005'
                , '10'                    => 'BPM - San Francisco 2005'
                , '11'                    => 'BPM - Washington 2005'
        );

		//change commas to slash
		$usergroup = preg_replace( "/,/", ' / ', $usergroup); 

		//replace the usergroup numbers with names.
		foreach ($usergroupTranslationTable as $searchText => $replacementText) {
			$usergroup = preg_replace( "#\b$searchText\b#", $replacementText, $usergroup );
		}
		
									
		if ($this->debug) {
			echo ' usergroup:' . $usergroup;
		}
		
		return $usergroup;
	}
	
	
	/**
	* Retrieves and returns the data rows for the renewals report.
	* @return the data rows.
	*/
	function renewalsReportGetRows()
	{
		if ($this->debug) {
			echo 'renewalsReportGetRows()<br>';
		}

		$SECONDS_PER_DAY = 86400;
		$COMPLIMENTARY_MEMBER_GROUPID = 1;
		$PROFESSIONAL_MEMBER_GROUPID = 2;
		$COMPLIMENTARY_PROF_MEMBER_GROUPID =4;

		$days = $this->reportRenewalsOptionsDays;
		
		$today = $this->getToday();
		
        //The date of renewal is marked by the starttime field.  This should be 
		//between today and the no. of days back we want to query.
		$starttime_max = $today;
		$starttime_min = $starttime_max - $days*$SECONDS_PER_DAY;
		
        //Expiretime shouldn't 0 because that would indicate a completely new registration, not a renewal.
		//Group should be Prof., because we're tracking who became a paying member.
		$where = "starttime >= $starttime_min and starttime < $starttime_max "
			. " AND tx_memberexpiry_expiretime != 0 " 
			. ' AND ( usergroup REGEXP "[[:<:]]'.$PROFESSIONAL_MEMBER_GROUPID.'[[:>:]]" )' 
		;
        $where .= ' AND deleted != 1  AND disable !=1 ';
		$columns = 'name, email, company, starttime AS renewaldate, tx_memberexpiry_expiretime AS expiredate, ROUND((starttime-tx_memberexpiry_expiretime)/86400) AS HowSoonRenewed, usergroup ';
        $groupby = '';
		$orderby = 'renewaldate, name';
		$rows =  $this->db->exec_SELECTgetRows($columns, 'fe_users', $where, $groupby, $orderby  );
		
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}
		if ($this->debug) {
			echo $columns; echo $where; 
			echo 'Renewals rows:';
			foreach ( $rows as $row ) {
				print_r($row);
				//$output .= '';
			}
			reset( $rows );
		}
		return $rows;
	}

	/**
	* Iterates over query results to return human readable text.
	* Substitutes replacement text for id numbers.
	* Depends on certain fieldnames in the query/results being the same as those
	* specified in this function.
	* @param an array of rows with unformatted data
	* @return an array of rows with formatted data.
	*/
	function renewalsReportFormatRows( &$rows )
	{
		//copy the original rows array to a working array
		$rowsFormatted = $rows;
		if ($this->debug) {
			echo 'renewalsReportFormatRows( &$rows )';
			echo 'count($rows): ' . count($rows);
		}
		
		//for each row, substitute formatted text in the working array
		for( $i=0; $i<count($rows); $i++ ) {
			
			$row = $rows[$i];
			//time
			$rowsFormatted[$i]['renewaldate'] = $this->formatEventTime('date', $row['renewaldate']);
			$rowsFormatted[$i]['expiredate'] = $this->formatEventTime('date', $row['expiredate']);
			//usergroup
			$rowsFormatted[$i]['usergroup'] = $this->formatUserGroup($row['usergroup']);
			
			if ($this->debug) {
				echo ' iteration: '.$i;
				echo ' renewaldate:'.$rowsFormatted[$i]['renewaldate'];
				echo ' usergroup:'.$rowsFormatted[$i]['usergroup'];
			}
		}
		return $rowsFormatted;
	}

	/**
	* Just a test function that displays a checkbox.
	*/
	function test() 
	{
		global $LANG;
		
		$menu=array();
		$menu[]=t3lib_BEfunc::getFuncCheck( $this->pObj->id,
            "SET[tx_memberexpiry_modfunc1_check]",
            $this->pObj->MOD_SETTINGS["tx_memberexpiry_modfunc1_check"]
            )
            . $LANG->getLL("checklabel")
        ;
		$theOutput.=$this->pObj->doc->spacer(5);
		$theOutput.=$this->pObj->doc->section("Menu",implode(" - ",$menu),0,1);
		
	}

}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_expiry/modfunc1/class.tx_memberexpiry_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_expiry/modfunc1/class.tx_memberexpiry_modfunc1.php"]);
}

?>
