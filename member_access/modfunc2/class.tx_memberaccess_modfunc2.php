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
 * Module extension (addition to function menu) 'Registration Errors' for the 'member_access' extension.
 *
 * @author	 <>
 */



require_once(PATH_t3lib."class.t3lib_extobjbase.php");

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

/**
Displays the Registrations Errors report.
This is displayed in module Functions: Wizards: Registration Errors.

Registration errors are those errors which occur while people attempt to register
at one of the pages under /member-login.  This could be unregistered folks or those
trying to upgrade their membership in some way.  

The backend of the /member-login pages is handled by the sr_feuser_register extension
at ~/www/typo3conf/ext/sr_feuser_register.  That has been modified to save errors
(which are codes for the red text that pops up when you enter a wrong value).
into the table tx_memberaccess_registrationerrors.  See the pi1 (plugin 1) class
definition file for sr_feuser_register.

This report wizard gives the user the option of how old of data to report.
*/
class tx_memberaccess_modfunc2 extends t3lib_extobjbase {

	//global database pointer
	var $db;
	
	var $debug = false; //turn on/off debug output
	
	//aliases for fields E.g., "endtime AS expiredate"
	var $fieldAlias_usergroup = 'accesslevel';
	
	//name of the table containing the access control entries
	var $table_registrationerrors = 'tx_memberaccess_registrationerrors';
	
    var $reportRegistrationErrsOptionsDays = 14;
    var $reportRegistrationErrsOptionsDaysDefault = 14;

	function modMenu()	{
		global $LANG;
		
		return Array (
			"tx_memberaccess_modfunc2_check" => "",
		);		
	}

	/**
    * Just for debugging.
    */
    function debugtest($param) {
        global $TYPO3_CONF_VARS;
        echo $TYPO3_CONF_VARS;
        print_r ($TYPO3_CONF_VARS);
    }
    
    function main()	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		$this->db                = $GLOBALS['TYPO3_DB'];
		$theOutput.=$this->pObj->doc->spacer(5);
		$theOutput.=$this->pObj->doc->section($LANG->getLL("title"),$LANG->getLL('description'),0,1);
        
        
		// Cache the GET/POST option value variables
		$this->setValues();

		//First, display the options/download form.
		//This is displayed regardless of whether anything else is currently displaying or not.
		//If this is the first go-around, this is the only thing that will be displayed.
		$theOutput .= $this->showForm();

		// If this is the 2nd go-around and the user has clicked a button, a GET or POST
		// variable will be set telling us what to do.  Here we check for these variables
		// and act accordingly.
		
		
		// check for update/display command for access list
		if ( t3lib_div::_GP( 'tx_memberaccess_modfunc2_registrationerr_command_display' ) )
		{
			if ($this->debug) echo 'tx_memberaccess_modfunc2_registrationerr_command_display<br>';
			$theOutput .= $this->doDisplay( 'tx_memberaccess_modfunc2_registrationerr_command_display' );
		}
		
		// check for download command (download current access list)
		elseif ( t3lib_div::_GP( 'tx_memberaccess_modfunc2_registrationerr_command_download' ) )
		{
			$theOutput .= $this->doDownload( 'tx_memberaccess_modfunc2_registrationerr_command_download' );
		}
		
		
		return $theOutput;
	}

	/**
	* Shows report options.
	* @return void
    * 
	*/
	function showForm()
	{
		$output = '';
		
		$output .= $this->showOptionsRegistrationErrReport();
		
		return $output;
	}

	/**
	* Returns a string containing the form elements for the options for the registration error
	* report.
	* @return the form elements string
	*/
	function showOptionsRegistrationErrReport()
	{
		global $LANG;
		$output = '';
		
		$break 						= '<br />';
		
								
		
		//Show title section with a bar background and uppercase title text
		$title						= $this->pObj->doc->section( 
										$LANG->getLL('report.registrationerr.options.sectiontitle')
										, $LANG->getLL('report.registrationerr.options.helptext')
										, 0
										, 1
										);
										
										
		//For the days input box, we redisplay the previously selected value, if any.  Otherwise the default.
		//This section shown w/o a bar background and with non-uppercase text
		$daysValue					= $this->reportRegistrationErrsOptionsDays;   
		$days 						= $this->pObj->doc->section(
										$LANG->getLL('report.registrationerr.options.days.caption')
										, $this->getFormElementDays('tx_memberaccess_modfunc2_registrationerr_options_days', $daysValue)
										, 1
										);
		//2 submit buttons: 1 for display, the other for download, plus a reset button.
		$submit                    = '<input type="submit"
                                        name="tx_memberaccess_modfunc2_registrationerr_command_display"
                                        value="'
                                    . $LANG->getLL( 'report.registrationerr.options.display.buttontext' )
                                    . '" />';
        $submit                    .= '&nbsp;';
        $submit                    .= '<input type="submit"
                                        name="tx_memberaccess_modfunc2_registrationerr_command_download"
                                        value="'
                                    . $LANG->getLL( 'report.registrationerr.options.download.buttontext' )
                                    . '" />';
        $submit                    .= '&nbsp;';
        $submit                    .= '<input type="reset" value="'
                                    . $LANG->getLL( 'report.registrationerr.options.reset.buttontext' )
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
		if ($this->debug) {echo "days:/" . t3lib_div::_GP( 'tx_memberaccess_modfunc2_registrationerr_options_days' ) . "/"; }
		$this->reportRegistrationErrsOptionsDays = t3lib_div::_GP( 'tx_memberaccess_modfunc2_registrationerr_options_days' );
		if (''==$this->reportRegistrationErrsOptionsDays) {
			$this->reportRegistrationErrsOptionsDays = $this->reportRegistrationErrsOptionsDaysDefault; 
		}
	}

	/**
	* Displays report(s)
	* @param the command specifying which report to display.  This is the same
	* 	value as is sent by the submit button for the asssociated report.
	*/
	function doDisplay( $what ) 
	{
		$output = '';
		
		if ('tx_memberaccess_modfunc2_registrationerr_command_display' == $what) {
			$output .= $this->registrationErrsReportDisplay();
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
		if ('tx_memberaccess_modfunc2_registrationerr_command_download' == $what) {
			$output .= $this->registrationErrsReportDownload();
		}
    }
	
    /**
     * Offer up the registration errors report for download to the user.
     *
     * @return void
     */
    function registrationErrsReportDownload()
    {
        global $LANG;
        
        $rows                = $rows = $this->registrationErrsReportGetRows();
        $rowsFormatted      = $this->registrationErrsReportFormatRows($rows);

        // convert rows to csv for download
        $rowsCsv                = '';

        foreach ( $rowsFormatted as $key => $value )
        {
            $rowsCsv            .= cbMkCsvString( $value );
        }

		$filename				= $this->getDownloadFilename(
            $LANG->getLL( 'report.registrationerr.downloads.basefilename' )
        );
		
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
	*/
	function getDownloadFilename( $basename ) {
		
		$dateFormatted 		= date('F j Y', time() );  
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
	function registrationErrsReportDisplay() 
	{
		global $LANG;
		
		//get the rows
		$rows = $this->registrationErrsReportGetRows();
		
		//and format the data, and convert to HTML with a title, before returning them.
		return cbArr2Html( $this->registrationErrsReportFormatRows($rows), $LANG->getLL( 'report.registrationerrs.title' ) );
	}

	/**
	* Retrieves and returns the data rows for the registration errors report.
    * This is parameterized on the no. of days back to retrieve errors for, but 
    * parameter is taken from class variables.
	* @return the data rows.
	*/
	function registrationErrsReportGetRows()
	{
		if ($this->debug) {
			echo 'registrationErrsReportGetRows()<br>';
		}

		$SECONDS_PER_DAY = 86400;

		$days = $this->reportRegistrationErrsOptionsDays;
		
		$today = time();
		
		//Set the time interval min/max
        $errortime_max = $today;
		$errortime_min = $errortime_max - $days*$SECONDS_PER_DAY;
		
		$columns = 'email, errortime, errors ';
        $table = $this->table_registrationerrors;
		$where = "errortime >= $errortime_min and errortime < $errortime_max ";
        $where .= ' AND hidden != 1 ';
		$groupby = '';
		$orderby = 'errortime DESC, email ASC';
		$rows =  $this->db->exec_SELECTgetRows($columns, $table, $where, $groupby, $orderby  );
		
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}
		if ($this->debug) {
			echo $columns; echo $where; 
			echo 'Registration errors rows:';
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
	function registrationErrsReportFormatRows( &$rows )
	{
		//copy the original rows array to a working array
		$rowsFormatted = $rows;
		if ($this->debug) {
			echo 'registrationErrsReportFormatRows( &$rows )';
			echo 'count($rows): ' . count($rows);
		}
		
		//for each row, substitute formatted text in the working array
		for( $i=0; $i<count($rows); $i++ ) {
			
			$row = $rows[$i];
			//time
			$rowsFormatted[$i]['errortime'] = $this->formatEventTime('time', $row['errortime']);
			
			if ($this->debug) {
				echo ' iteration '.$i;
				echo ' '.$rowsFormatted[$i]['errortime'];
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
	*	'days', the no. of days in which event will occur.
	* @return the formatted date text
	*/
	function formatEventTime($type, $eventtime)
	{	
		
		$formattedText = '';
		switch ($type) {
			case 'date': $formattedText = date('F j, Y', $eventtime ); break;
			case 'time': $formattedText = date('F j, Y H:i:s', $eventtime ); break;
			case 'days': $formattedText = "$eventtime days"; break;
			default: $formattedText = date('F j, Y', $eventtime ); 
				echo 'Error in getEventTimeFormatted.';
				break;
		}
		
		return $formattedText;
	}







}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_access/modfunc2/class.tx_memberaccess_modfunc2.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_access/modfunc2/class.tx_memberaccess_modfunc2.php"]);
}

?>
