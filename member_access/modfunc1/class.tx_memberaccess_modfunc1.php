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
 * Module extension (addition to function menu) 'Membership Access Tool' for the 'member_access' extension.
 *
 * @author	Jaspreet Singh <>
 * $Id: class.tx_memberaccess_modfunc1.php,v 1.1.1.1 2010/04/15 10:03:50 peimic.comprock Exp $
 */



require_once(PATH_t3lib."class.t3lib_extobjbase.php");

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

/**
Displays the Membership Access Tool.
This is displayed in module Functions: Wizards: Membership Access Tool.

This tool provides a way to upload a list of users who are authorized to access
specified resources.

After the user uploads an access list, the entire current list (with the changes applied)
is displayed.

One can also download the access list in a comma-separated values format.

Access list data is taken from the user's upload and saved into table
tx_memberaccess_acl.  Rows that already exist (by matching on email) have additional
access (as specified in the upload) applied to them.
Then the access levels in table tx_memberaccess_acl are applied (added) to those
already in table fe_users.

For persons with an access control entry in tx_memberaccess_acl who already have a
user account, their account will thus have been updated and they will have received
the desired permissions.

For persons who haven't yet created an account, their additional access permissions
will get applied at the time their account is created.

This happens in the sr_feuser_register extension, which handles registration
at ~/www/typo3conf/ext/sr_feuser_register.

*/
class tx_memberaccess_modfunc1 extends t3lib_extobjbase {

	//global database pointer
	var $db;
	
	var $debug = false; //turn on/off debug output
	
	// default size for HTML form element input box
	var $inputSize = 20;
	
	//aliases for fields E.g., "endtime AS expiredate"
	var $fieldAlias_usergroup = 'accesslevel';
	var $fieldAlias_endtime   = 'DesiredEndtime';
	
	//name of the table containing the access control entries
	var $table_member_acl = 'tx_memberaccess_acl';
	
	//The position of the email field in the uploaded data rows.
	//starting with first index==0
	//The reason we do this is to (hopefully) be able to change the order of
	//the fields without any other changes in the code.
	var $uploadedDataFieldPositionName = 0;
	var $uploadedDataFieldPositionCompany = 1;
	var $uploadedDataFieldPositionEmail = 2;
	var $uploadedDataFieldPositionAccessLevel = 3;
	var $uploadedDataFieldPositionEndtime = 4;
	var $uploadedDataFieldPositionEndtimeExtension = 5;
	
	//access list is cached here after reading it from the table identified in
	// $table_member_acl 
	var $accessListCache;
	
	//usergroup settings.  Tied to the uid of the various groups in table fe_groups
	var $corporateGroup 		= '';
	var $professionalGroup 		= '';
	var $proguestGroup 	= '';
	/** UID of the free member group */ 
	var $memberGroup 			= '';
	/** array of all UIDs of groups that are considered visitor groups.
		Normally, '5','6','7'.
		Must be an array of strings since it is used with array_intersect */
	var $visitorGroupsArray;
	/** array of all UIDs of groups that are considered conference groups.
		Normally, '9','10','11','12'.
		Must be an array of strings since it is used with array_intersect */
	var $conferenceGroupsArray;
	
	
	function modMenu()	{
		global $LANG;
		
		return Array (
			"tx_memberaccess_modfunc1_check" => "",
		);		
	}

	/**
	* Main entry point for the member access tool.
	* @return a string containing all the output, which Typo3 will wrap into a 
	* form element inside the functions/wizard area.
	*/
	function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		$this->initialize();
		
		$this->db                = $GLOBALS['TYPO3_DB'];
		$theOutput.=$this->pObj->doc->spacer(5);
		$theOutput.=$this->pObj->doc->section($LANG->getLL("title"),$LANG->getLL('description'),0,1);
		
		
		//Otherwise, display the upload/download form.
		//This is displayed regardless of whether anything else is currently displaying or not.
		//If this is the first go-around, this is the only thing that will be displayed.
		$theOutput .= $this->showForm();
		
		// If this is the 2nd go-around and the user has clicked a button, a GET or POST
		// variable will be set telling us what to do.  Here we check for these variables
		// and act accordingly.
		
		// first, cache the GET/POST option value variables
		$this->setValues();
		
		// check for update/display command for access list
		if ( t3lib_div::_GP( 'tx_memberaccess_modfunc1_accesstool_command_updatedisplay' ) )
		{
			if ($this->debug) echo 'tx_memberaccess_modfunc1_accesstool_command_updatedisplay<br>';
			$theOutput .= $this->doUpdateDisplay( 'tx_memberaccess_modfunc1_accesstool_command_updatedisplay' );
		}
		
		// check for download command(s) (download current access list)
		if ( t3lib_div::_GP( 'tx_memberaccess_modfunc1_accesstool_command_download_reporting' ) ) {
			$theOutput .= $this->doDownload( 'tx_memberaccess_modfunc1_accesstool_command_download_reporting' );
		}
		if ( t3lib_div::_GP( 'tx_memberaccess_modfunc1_accesstool_command_download_aclmodification' ) ) {
			$theOutput .= $this->doDownload( 'tx_memberaccess_modfunc1_accesstool_command_download_aclmodification' );
		}
		
		return $theOutput;
	}
	
	/**
	Initializes some class level variables. 
	*/
	function initialize() {
		if ($this->debug) {
			echo "function initialize() {";
		}
		$this->setDebugStatus();
		
		//note: TODO hardcodes
		$this->corporateGroup             		= '16';
		$this->professionalGroup          		= '2';
		$this->proguestGroup         		= '4';
		$this->memberGroup						= '1';
		// SELECT UNIX_TIMESTAMP('2006-8-19 23:59:59');
		//$this->complimentaryEndtime				= '1156049999';
		$this->visitorGroupsArray				= array('5','6','7');
		$this->conferenceGroupsArray			= array('9','10','11','12');
	}

	/**
    * Turn on debug status conditionally.
    * Put your IP here to see debug information.
    * @return void
    * @author js
    */
    function setDebugStatus(){
        //change the true below to false to always not show debug info
        //echo  '$ _SERVER[ REMOTE_ADDR ]' . $_SERVER[ 'REMOTE_ADDR' ];
		if ( true && ('59.94.211.80' == $_SERVER[ 'REMOTE_ADDR' ] 
			|| '70.84.110.68' == $_SERVER[ 'REMOTE_ADDR' ]
			)
		) {
            $this->debug = true;
        }
    }

	/**
	* Returns a string containing form elements allowing user to upload an access
	* list or download the current list.
	* @return the form elements string
	*/
	function showForm()
	{
		$output = '';
		
		$output .= $this->showFormUpload();
		$output .= $this->showFormDownload();
		
		return $output;
	}

	/**
	* Returns a string containing form elements allowing user to upload an access list.
	* @return the form elements string
	*/
	function showFormUpload()
	{
		global $LANG;
		$output = '';
		
		$break 						= '<br />';
		
		$title						= $this->pObj->doc->section( 
										$LANG->getLL('form.upload.sectiontitle')
										, $LANG->getLL('form.upload.helptext')
										, 0
										, 1
										);
										
		//The access list text input box. Show blank (i.e. don't show prev. data if user already pushed Submit)
		$accesslist					= $this->pObj->doc->section(
										$LANG->getLL('form.upload.accesslist.caption')
										, $this->getFormElementAccesslist('tx_memberaccess_modfunc1_accesstool_form_accesslist', '')
										, 1
										);

		//This is a static table of the most relevant user groups/codes to assist people in entering accesslevel codes 
		$helperTable				= $this->getFormElementUserGroupHelperTable('tx_memberaccess_modfunc1_accesstool_form_usergrouphelpertable');										
										
		//This is a static table of the most relevant user groups/codes to assist people in entering accesslevel codes 
		$endtimeExtension			= $this->getFormElementEndtimeExtension('tx_memberaccess_modfunc1_accesstool_form_endtimeextension');										


		//Upload button
		$submit                    = '<input type="submit"
										name="tx_memberaccess_modfunc1_accesstool_command_updatedisplay"
										value="'
									. $LANG->getLL( 'form.upload.options.display.buttontext' )
									. '" />';

		//assemble the form elements in order
		$output .= $title
					. $this->pObj->doc->spacer(5)
					. $accesslist
					. $this->pObj->doc->spacer(5)
					. $helperTable
					//. $this->pObj->doc->spacer(5)
					//. $endtimeExtension
					. $this->pObj->doc->spacer(5)
					. $submit;
		return $output;
		
	}

	/**
	 * Returns a string containing the form element for access list.
	 *
	 * @param string field name
	 * @param initial value of the element. Blank by default.
	 * @return the form element string
	 */
	function getFormElementAccessList ( $fieldname, $value='' )
	{
		$rows = 20;
		$columns = 85;
		
		$string                    = 
			'<textarea '
				.' name="' . $fieldname . '"'
				.' rows="' . $rows .'"' 
				.' cols="' . $columns .'"' 
				.' >' . $value   
				.'</textarea>'
		;

		return $string;
	}
	

	/**
	 * Returns a string containing the form element for usergroup helper table.
	 * This is a static table of the most relevant user groups/codes to assist
	 * people in entering accesslevel codes.
	 * @param string field name
	 * @return the form element string
	 */
	function getFormElementUserGroupHelperTable ( $elementName='' )
	{
		$rows = 20;
		$columns = 85;
		global $LANG;
		
		$string                    = $LANG->getLL('form.upload.helptext2');

		return $string;
	}
	

	/**
	 * Returns a string containing the form element for endtime extension.
	 *
	 * @param string field name
	 * @param initial value of the element. Blank by default.
	 * @return the form element string
	 */
	function getFormElementEndtimeExtension ( $fieldname, $value='' )
	{
		global $LANG;
		
		$helptext                  	= $LANG->getLL('form.upload.endtimeextension');
		
		$string                    	=
			$helptext 
				.'<input '
				.' type="text"'
				.' name="' . $fieldname .'"' 
				.' value="' . $value .'"'
				.'>'
		;

		return $string;
	}

	/**
	* Returns a string containing form elements allowing user to download the current access list.
	* @return the form elements string
	*/
	function showFormDownload()
	{
		global $LANG;
		$output = '';
		
		$break 						= '<br />';
		
		$title						= $this->pObj->doc->section( 
										$LANG->getLL('form.download.sectiontitle')
										, $LANG->getLL('form.download.helptext')
										, 0
										, 1
										);
										
										
		//Download buttons
		$buttonDownloadForReporting = '<input type="submit"
										name="tx_memberaccess_modfunc1_accesstool_command_download_reporting"
										value="'
									. $LANG->getLL( 'form.download.options.button.reporting.buttontext' )
									. '" />';

											//Download button
		$buttonDownloadForACLModification = '<input type="submit"
										name="tx_memberaccess_modfunc1_accesstool_command_download_aclmodification"
										value="'
									. $LANG->getLL( 'form.download.options.button.aclmodification.buttontext' )
									. '" />';

		//assemble the form elements in order
		$output 	.= $title
					. $this->pObj->doc->spacer(5)
					. $buttonDownloadForReporting
					. $buttonDownloadForACLModification
					;
		return $output;
		
	}


	/**
	 * Takes the access list uploaded by the user, updates the database with it,
	 * and displays the resulting current access list.
	 * 
		Method:
		
		Iterate over the tsv (tab-separated values) upload and save the rows:
		If there is already a row with the same email address, check the accesslevel
		field.  For each comma-separated usergroup value in the upload, append the
		values that don't already exist in the accesslevel field.  (So it basically
		becomes a kind of UNION).
		
		Iterate over the just-saved table and for each row:
		Check if there is a email match in fe_users
		If so, examine fe_users.usergroup.  For each comma-separated usergroup value in 
		member_acl.accesslevel, check if it's already there in fe_users.usergroup.  If not, append it.
	 * @param what to display (same as the submit button's value).
	 * @return a string containing the display of the resulting access list.
	 */
	function doUpdateDisplay( $what )
	{
		if ($this->debug) {
			echo 'doUpdateDisplay()';
			$this->testAccessLevel();
			$this->db->debugOutput = true;
		}
		
		$output = '';
		
		if ('tx_memberaccess_modfunc1_accesstool_command_updatedisplay' == $what) {
			$this->updateAccessList();
			$this->updateFeUserGroupsExtended();
			$output .= $this->displayAccessList();
		}
		
		return $output;
	}

	/**
	Iterate over the tsv (tab-separated values) upload and save the rows:
	If there is already a row with the same email address, check the accesslevel
	field.  For each comma-separated usergroup value in the upload, append the
	values that don't already exist in the accesslevel field.  (So it basically
	becomes a kind of UNION).
	@return void
	*/
	function updateAccessList()
	{
		if ($this->debug) { echo ' function updateAccessList() '; }
		$uploadedACL = $this->getPOSTedAccessListAsRowArray();

		// revoke current access
		// return access groupers to complimentary if needed
		// reset end time to 0 for comps
		$row					= $uploadedACL[ 0 ];
		$desiredAccessLevel		= trim($row[$this->uploadedDataFieldPositionAccessLevel]);

		// clear out pro guests first
		// return to comp member level
		$query					= "
			UPDATE fe_users
			SET usergroup = REPLACE(usergroup, ',{$desiredAccessLevel}'
					, '')
				, usergroup = REPLACE(usergroup, '{$desiredAccessLevel}'
					, '')
				, usergroup = REPLACE(usergroup, ',{$this->proguestGroup}'
					, ',{$this->memberGroup}')
				, usergroup = REPLACE(usergroup, '{$this->proguestGroup}'
					, '{$this->memberGroup}')
				, endtime = 0
			WHERE 1 = 1 
				AND usergroup REGEXP '[[:<:]]{$desiredAccessLevel}[[:>:]]'
				AND (
					usergroup REGEXP '[[:<:]]{$this->proguestGroup}[[:>:]]'
					OR
					usergroup REGEXP '[[:<:]]{$this->memberGroup}[[:>:]]'
				)
		";

		if ( ! $this->db->sql_query( trim($query) ) )
		{
			cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
			cbDebug( 'query', $query );	
		}

		// clear out pros
		$query					= "
			UPDATE fe_users
			SET usergroup = REPLACE(usergroup, ',{$desiredAccessLevel}'
					, '')
				, usergroup = REPLACE(usergroup, '{$desiredAccessLevel}'
					, '')
			WHERE 1 = 1 
				AND usergroup REGEXP '[[:<:]]{$desiredAccessLevel}[[:>:]]'
				AND usergroup REGEXP '[[:<:]]{$this->professionalGroup}[[:>:]]'
		";

		if ( ! $this->db->sql_query( trim($query) ) )
		{
			cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
			cbDebug( 'query', $query );	
		}

		// clear out acl
		$query					= "
			DELETE FROM {$this->table_member_acl}
			WHERE 1 = 1 
				AND accesslevel REGEXP '[[:<:]]{$desiredAccessLevel}[[:>:]]'
		";

		if ( ! $this->db->sql_query( trim($query) ) )
		{
			cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
			cbDebug( 'query', $query );	
		}

		$replaceQuery = '';
		
		foreach ($uploadedACL as $row) {
			if ($this->debug) {
				echo 'updateAccessList()';
				echo '$row: ';
				print_r ($row);
				echo '<br>';
			}
			//if there is a match on email between uploaded acl and the existing acl
			
			//get the accesslevel field for the record identified by this email
			//that's already stored in the database (if any) 
			
			$email = $row[$this->uploadedDataFieldPositionEmail];
			$name = $row[$this->uploadedDataFieldPositionName];
			$company = $row[$this->uploadedDataFieldPositionCompany];
			$desiredAccessLevel = trim($row[$this->uploadedDataFieldPositionAccessLevel]);
			$desiredEndTime = trim($row[$this->uploadedDataFieldPositionEndtime]);
			$endtimeExtension = trim($row[$this->uploadedDataFieldPositionEndtimeExtension]);
			
			if ($this->debug) {
				echo '$row[2]: /' . $email . '/';
			}
			
			//To know the existing access level, we have to read the existing
			//Access Control Entry, if any.
			$existingAce = $this->getExistingAce($email);
			if(false==$existingAce) {
				$currentAccessLevel = '';
			} else {
				$currentAccessLevel = trim($existingAce['accesslevel']);
			}
			
			
			//append any accesslevels present in the uploaded ace not present in the existing ace 
			//appendAce
			$newAccessLevel = $this->appendAccessLevel( strval($currentAccessLevel), strval($desiredAccessLevel) );
			if ($this->debug) {
				echo '$newAccessLevel /' . $newAccessLevel . '/<br>';
			}
			
			//The incoming desiredEndtime is something like '08/23/2006'
			//convert that to Unixtime.  Note: strtotime converts 0 to the current time for some
			//reason (instead of the Unix epoch), so we handle 0 specially.
			$desiredEndTime2 = (0 == $desiredEndTime)
				? 0
				: strtotime($desiredEndTime)
				;
			
			//build REPLACE INTO query
			//note: this depends on there being a unique index on email on this table.
			//REPLACE INTO $table_member_acl
			//SET email = '$email', name = '$name', company = '$company', accesslevel='$accesslevel',
			//endtime='$endtime'
			//endtimeExtension = '$endtimeExtension' 
			//For some reason, PHP doesn't want to execute multiple REPLACE queries all at once,
			//even though phpmyadmin will gladly execute the query if the same query string is cut and
			//pasted into it.
			//So instead of building one big REPLACE INTO query, we execute 1 query for every
			//acl row (which could be 500 some rows).
			$replaceQuery = " REPLACE INTO $this->table_member_acl"
							. " SET email = \"$email\", name = \"$name\"," 
							. " company = \"$company\", accesslevel=\"$newAccessLevel\"  "
							. " , endtime='$desiredEndTime2', endtimeExtension = \"$endtimeExtension\"  ";			//execute the replace query
			$result = $this->db->sql_query( trim($replaceQuery) );
			if ($this->debug) {
				echo $replaceQuery;
				echo '<br>Query result:/' ; var_dump($result); echo '/';
				echo '<br>Affected rows:' . mysql_affected_rows();
			}
			
		}
		
		if ($this->debug && false) {
			echo $replaceQuery;
			echo 'Query result:/' ; var_dump($result); echo '/';
			echo 'Affected rows:' . mysql_affected_rows();
			
			//$testquery = 'INSERT INTO tx_memberaccess_acl(name, company, email, accesslevel) VALUES ("David Hame2rmesh", "ABN AMRO Mort2gagae Group", "david.hamermes2h@abnamro.com", "9");';
			$testquery = <<<EOD
				REPLACE INTO tx_memberaccess_acl SET email = "lod@brainstorm-group.com", name = "Linda O'Donnell", company = "BrainStorm G2orup, Inc.", accesslevel="9,12" ;
EOD;
			$testquery = <<<EOD
				REPLACE INTO tx_memberaccess_acl SET email = "lod@brainstorm-group.com", name = "Linda O'Donnell", company = "BrainStorm G2orup, Inc.", accesslevel="9,12" ; 
				REPLACE INTO tx_memberaccess_acl SET email = "Darryl_Hahn@CSAA.com", name = "Darryl H2ahn", company = "A2AA", accesslevel="10,19" ;
EOD;
			$testquery = <<<EOD
				REPLACE INTO tx_memberaccess_acl SET email = "lod@brainstorm-group.com", name = "Linda O'Donnell", company = "BrainStorm G2orup, Inc.", accesslevel="9,12"  
				REPLACE INTO tx_memberaccess_acl SET email = "Darryl_Hahn@CSAA.com", name = "Darryl H2ahn", company = "A2AA", accesslevel="10,19" 
EOD;

			echo $testquery;
			$result=false;
			//$result = $this->db->sql_query( $testquery );
			echo '<br>test Query result:/'; var_dump($result); echo '/';
			echo 'test query Affected rows:' . mysql_affected_rows();
			
			
		}
		
	}
	
	
	/**
	* Converts the POSTed access list to an array of arrays and returns it.
	* No parameter is passed; rather, it gets its data from the global POST functions.
	* Doesn't add blank lines or lines where the email value is blank.
	* @return the POSTed access list to an array of arrays.
	*/
	function getPOSTedAccessListAsRowArray()
	{
		//This is a big string with newlines separating the lines, tabs separating the fields
		$uploadedACL = t3lib_div::_POST( 'tx_memberaccess_modfunc1_accesstool_form_accesslist' );
		
		if($this->debug) echo $uploadedACL; //should show a big string
		
		//Transform into an array of tab-separated strings
		$linefeed = chr(10); //"\n"
		$uploadedACL_array = explode($linefeed, $uploadedACL);
		
		if($this->debug) echo print_r($uploadedACL_array); //should show an array
		
		//Convert each line to an array, as well
		$uploadedACL_rowarray 	= array();
		$tab 					= chr(9); //"\t"
		foreach ($uploadedACL_array as $line) {
			if ($line != '') {
				$row = explode($tab, $line);
				//only add if the email field isn't blank
				if ($row[$this->uploadedDataFieldPositionEmail] != '' ) {
					$uploadedACL_rowarray[] = $row;
				}
			}
		}
		
		return $uploadedACL_rowarray;

	}





	
	/**
	* Finds and returns an existing access control entry (ACE), if any.
	* The search is done on the $this->table_member_acl table.
	* Doesn't return the row if its deleted column is set to 1.
	* @param the email on which to search for an existing record.
	* @return A row with fieldnames as keys and fieldvalues as values if an existing 
	* matching record is found.  Otherwise, false.
	*/
	function getExistingAce($email)
	{
		$returnValue = false;
		if (''==$email) {
			return $returnValue;
		}
		
		//use caching here.
		//if this is the first time around, the cache won't be set yet.
		//so fill it in.
		if (!isset($this->accessListCache)) {
			$this->accessListCache = $this->getRowsAccessList();
		} //for subsequent go-arounds, we won't need to do a SELECT
		
		$emailPath = $this->array_search_recursive2( $email, $this->accessListCache, 'email' );
		if (null==$emailPath) {
			if ($this->debug) {
				echo 'Not found: /'.$email.'/<br>';
			}
		} else {
			
			//note that $emailPath will be an array with values like Array ( [0] => 2 [1] => 2 )
			//the elements of the path are used as indexes to the data array
			//We only care about the first index, though, as it tells us the row no.
			//We return the entire row
			$returnValue = $this->accessListCache[$emailPath[0]];
			
			//there should be 2 elements in the path
			if ($this->debug) {
				if (($elementCount = count($emailPath)) != 2) {
					echo 'Error: expecting 2 elements in $emailPath; actually ' . $elementCount; 
				}
				$accesslevel = $this->accessListCache[$emailPath[0]]['accesslevel'];
				echo 'Found: User-accesslevel/'.$email.'/'.$accesslevel.'/<br>';
			}
			
		}
		
		if ($this->debug) {
			//echo print_r($this->accessListCache);
			echo 'count($this->accessListCache): /' . count($this->accessListCache). '/';
			echo '$emailPath /' ; print_r($emailPath); echo '/ <br>';
			
		}
		
		return $returnValue;
	}
	
	/**
	Converts an endtime, which may be relative or absolute, into 
	an absolute value as expressed in unixtime (seconds since the epoch).

	Relative endtime means a number which indicates the days from now on which
	the user's membership should end.
	
	Absolute value means a date-time string in the following format:
		
	If the passed value is 0, 0 is returned.
	
	If the passed value is blank, blank is returned.
	@param endtime as entered by user
	@return the converted endtime in unixtime format
	*/
	function convertEndtimeRelativeToAbsolute($endtime) {
		$convertedEndtime = '';
		
		if (''==$endtime) {
			return $endtime; 
		} else if (0 == $endtime) {
			return $endtime;
		}
		
		//Determine whether we are dealing with a relative or absolute endtime.
		if (preg_match('/[a-z]/i', $endtime)) { //Absolute
			$convertedEndtime = strtotime($endtime);
		} else { //Relative
			$convertedEndtime = strtotime("+$endtime day");
		}
		
		return $convertedEndtime;
	
	}
	
	/**
	Multidimensional array search.
	Taken with thanks from PHP help.
	Recursively descend an arbitrarily deep multidimensional
	array, stopping at the first occurence of scalar $needle.
	(will infinitely recurse on self-referential structures)
	@param the string to search for
	@param the array in which to search
	@return the path to $needle as an array (list) of keys. 
		If not found, return null.
	*/
	function array_search_recursive( $needle, $haystack )
	{
	  $path = NULL;
	  $keys = array_keys($haystack);
	  while (!$path && (list($toss,$k)=each($keys))) {
		 $v = $haystack[$k];
		 if (is_scalar($v)) {
			if ($v===$needle) {
			   $path = array($k);
			}
		 } elseif (is_array($v)) {
			if ($path=$this->array_search_recursive( $needle, $v )) {
			   array_unshift($path,$k);
			}
		 }
	  }
	  return $path;
	}


	/**
	Multidimensional array search w/ key restriction.
	Taken with thanks from PHP help.
	Recursively descend an arbitrarily deep multidimensional
	array, stopping at the first occurence of scalar $needle.
	(will infinitely recurse on self-referential structures).
	This version allows for restricting the search to a single key.
	@param the string to search for
	@param the array in which to search
	@param key in which to search.  Blank by default.
	@return the path to $needle as an array (list) of keys. 
		If not found, return null.
	*/
	function array_search_recursive2($needle, $haystack, $key_lookin="") {
		$path = NULL;
		if (!empty($key_lookin) && array_key_exists($key_lookin, $haystack) && $needle === $haystack[$key_lookin]) {
			$path[] = $key_lookin;
		} else {
			foreach($haystack as $key => $val) {
				if (is_scalar($val) && $val === $needle && empty($key_lookin)) {
					$path[] = $key;
					break;
				} 
				elseif (is_array($val) && $path = $this->array_search_recursive2($needle, $val, $key_lookin)) {
					array_unshift($path, $key);
					break;
				}
			}
		}
		return $path;
	}

	

	/**
	* Unit test for appendAccessLevel(). Sends output to echo.
	*/
	function testAccessLevel() {
		$this->appendAccessLevel('','');
		$this->appendAccessLevel('9','');
		$this->appendAccessLevel('','9');
		$this->appendAccessLevel('9','10');
		$this->appendAccessLevel('10','10');
		$this->appendAccessLevel('9,10','9');
		$this->appendAccessLevel('9,11','10');
	}

	/**
	* Updates a current access level to also contain a desired access level.
	* @param string. the current access level.  E.g., "9,1" (without the quotes)
	* @param string. the desired additional access level.  E.g, "10"
	* @param string. levels that are ignored if present in $currentAccessLevel and are not
	*	propagated to $desiredAccessLevel.  E.g., "1,4".  Defaults to blank string.
	* @return string. the new combined access level. E.g., "9,10"
	*/
	function appendAccessLevel($currentAccessLevel, $desiredAccessLevel, $ignoredLevels='')
	{

		if ($this->debug) {
			echo '<br> $currentAccessLevel ';
			var_dump($currentAccessLevel);
			echo '<br> $desiredAccessLevel ';
			var_dump($desiredAccessLevel);
			echo '<br> $ignoredLevels ';
			var_dump($ignoredLevels);
			echo '<br>';
		}
		
		// Since the accesslevel/usergroups value is a comma-separated values string, we explode on the comma,
		// process, and then implode into a string again.
		//Note how we trim spaces--the search algorithm below won't work if there are
		//extra spaces: they'll be seen as a different value
		$comma = ",";
		$currentAccessLevelArray = explode( $comma, trim($currentAccessLevel) );
		$desiredAccessLevelArray = explode( $comma, trim($desiredAccessLevel) );
		$ignoredAccessLevelArray = explode( $comma, trim($ignoredLevels) );
		//start off w/ current access level as a base, minus the levels to be ignored
		$newAccessLevelArray = array_diff($currentAccessLevelArray, $ignoredAccessLevelArray) ;  
		if ($this->debug) {
			//92// echo "\$newAccessLevelArray before processing $newAccessLevelArray \n<br>";
			//echo "$ newAccessLevelArray before processing $newAccessLevelArray \\n<br>";
			var_dump($newAccessLevelArray);
		}

		//if any single desired access level doesn't already exist, add it to the new access level array
		foreach ($desiredAccessLevelArray as $groupid) {
			if (!in_array($groupid, $newAccessLevelArray)) {
				$newAccessLevelArray[] = $groupid;
			}
		}
		
		$newAccessLevel = implode( $comma, $newAccessLevelArray );
		$newAccessLevelTrimmed = trim($newAccessLevel,$comma); 

		if($this->debug) {
			echo '<br><br>Current:'.$currentAccessLevel; print_r ($currentAccessLevelArray);
			echo '<br>Desired:'.$desiredAccessLevel; print_r ($desiredAccessLevelArray); 
			echo '<br>New:'.$newAccessLevelTrimmed; print_r ($newAccessLevelArray );
		}
		return $newAccessLevelTrimmed;
	}
	
	
	/**
	Propagate user access levels from memberaccess_acl table to fe_users table. 
	Method:
	Iterate over the the memberaccess_acl table and for each row:
	Check if there is a email match in fe_users
	If so, examine fe_users.usergroup.  For each comma-separated usergroup value in 
	member_acl.accesslevel, check if it's already there in fe_users.usergroup.  If not, append it.
	Doesn't update visitor records.
	@param int The uid of the record to process.  If not specified, operates over all matches
		between tables memberaccess_acl and fe_users
	@return void
	*/
	function updateFeUserGroupsExtended($uid = null)
	{
		if ($this->debug) { echo " function updateFeUserGroupsExtended($uid = null) "; } 
		//get the rows which are in both tables, correlated by email
		$rows = $this->getRowsFeUserUpdate($uid);
		$updateQuery = '';
		
		$comma = ',';
		
		/*
		What the update query will look like:
		UPDATE fe_users
		SET usergroup = '9,10'
		WHERE uid = [uid] 
		*/
		//iterate over these rows;
		//create an update query which will add the desired access level to the current access level
		foreach ( $rows as $row ) {

			$currentGroup = $row['current_accesslevel'];
			//don't append additional usergroups for visitor records
			$currentGroupArray = explode($comma,$currentGroup); 
			$isVisitor = 0 < count(array_intersect( $currentGroupArray,  $this->visitorGroupsArray ));
			if ( $isVisitor ) {
				if ($this->debug) {
					echo 'visitor ' . $row['feusers_uid'] . '- discarding';
				}
				// MLC continue allows the foreach to continue, but not break
				// out of it
				continue;
			}
			
			$groupWithConferenceAppended = $this->appendAccessLevel($currentGroup, $row['desired_accesslevel']);
			$uid = $row['feusers_uid'];
			$currentEndtime = $row['endtime'];
			$endtimeDesired = $row['desired_endtime'];
			$endtimeExtension = $row['endtimeExtension'];
			$newUsergroup = '';
			$newEndtime = '0';
			
			if ($this->debug) {
				//92//echo "\n \$uid $uid \$currentGroup $currentGroup \$currentEndtime  $currentEndtime \$groupWithConferenceAppended $groupWithConferenceAppended"; 
			}
			
			switch(1) {
			//if pro, add conference
			case (preg_match( "#\b$this->professionalGroup\b#" ,  $currentGroup)) :
				if ($this->debug) { echo "prof "; }
				//nothing to do
				//groupWithConferenceAppended already has conference added
				$newUsergroup 	= $groupWithConferenceAppended;
				$newEndtime 	= $currentEndtime;
				break;
			//else if complimentary, add conference; extend enddate if necessary
			case (preg_match( "#\b$this->proguestGroup\b#" , $currentGroup )) :
				if ($this->debug) { echo "compl "; }

				$newUsergroup 	= $groupWithConferenceAppended;
				
				$newEndtime	= $this->getNewEndtime( $currentEndtime, $endtimeDesired, $endtimeExtension );
				
				/*
				//if the current endtime is blank, use the specified endtime
				//if specified is less, keep the current
				//if specified is more, use specified
				if (is_null($currentEndtime) || ''==$currentEndtime || 0==$currentEndtime) {
					//$newEndtime = $this->complimentaryEndtime;
					$newEndtime	= $this->getNewEndtime( $currentEndtime, $endtimeDesired, $endtimeExtension );
				} else if ($this->complimentaryEndtime < $currentEndtime ){
					$newEndtime = $currentEndtime;
				} else if ($this->complimentaryEndtime >= $currentEndtime ){
					//$newEndtime = $this->complimentaryEndtime;
					$newEndtime	= $this->getNewEndtime( $currentEndtime, $endtimeDesired, $endtimeExtension );
				}
				*/
				
				break;
			//else if member, add complimentary until enddate; add conference
			case (preg_match( "#\b$this->memberGroup\b#" , $currentGroup )) :
				if ($this->debug) { echo "free "; }
				$newUsergroup 	= $this->appendAccessLevel( $groupWithConferenceAppended, $this->proguestGroup);
				//$newEndtime 	= $this->complimentaryEndtime;
				$newEndtime	= $this->getNewEndtime( $currentEndtime, $endtimeDesired, $endtimeExtension );

				break;
			//else if new (not anything above), give complimentary until endtime; add conference
			default:
				if ($this->debug) { echo "default case "; }
				$newUsergroup 	= $this->appendAccessLevel( $groupWithConferenceAppended, $this->proguestGroup);
				//$newEndtime 	= $this->complimentaryEndtime;
				$newEndtime	= $this->getNewEndtime( $currentEndtime, $endtimeDesired, $endtimeExtension );
			}

			// MLC remove free/comp conflict
			if ( preg_match( "#\b$this->proguestGroup\b#"
				, $newUsergroup )
			)
			{
				$newUsergroup	= preg_replace(
									"#\b{$this->memberGroup}\b,?#"
									, ''
									, $newUsergroup
								);
			}

			// MLC remove ny good, ny request conflict
			if ( preg_match( "#\b12\b#"
				, $newUsergroup )
			)
			{
				$newUsergroup	= preg_replace(
									"#\b19\b,?#"
									, ''
									, $newUsergroup
								);
			}
				
			$fields_values 	= array('usergroup' => $newUsergroup
				, 'endtime' => $newEndtime
			);
			$table = 'fe_users';
			$where = "uid = $uid";
			
			
			$result = $this->db->exec_UPDATEquery($table,$where,$fields_values);

			if ($this->debug) {
				echo '<br>Query result:/' ; var_dump($result); echo '/';
				echo '<br>Affected rows:' . mysql_affected_rows();
				$query =  $this->db->UPDATEquery($table,$where,$fields_values);
				echo $query;
				$updateQuery .= $query . '; ';
			}
		}
		
		if ($this->debug) echo $updateQuery;
		if ($this->debug) { echo " end function updateFeUserGroupsExtended($uid = null) "; }
	}

	/**
	Propagate user access levels from memberaccess_acl table to fe_users table. 
	Method:
	Iterate over the the memberaccess_acl table and for each row:
	Check if there is a email match in fe_users
	If so, examine fe_users.usergroup.  For each comma-separated usergroup value in 
	member_acl.accesslevel, check if it's already there in fe_users.usergroup.  If not, append it.
	*/
	function updateFeUserGroups($uid = null)
	{
		//get the rows which are in both tables, correlated by email
		$rows = $this->getRowsFeUserUpdate($uid);
		$updateQuery = '';
		
		/*
		What the update query will look like:
		UPDATE fe_users
		SET usergroup = '9,10'
		WHERE uid = [uid] 
		*/
		//iterate over these rows;
		//create an update query which will add the desired access level to the current access level
		foreach ( $rows as $row ) {

			$newUsergroup = $this->appendAccessLevel($row['current_accesslevel'], $row['desired_accesslevel']);
			$uid = $row['feusers_uid'];
			$fields_values = array('usergroup' => $newUsergroup);
			$table = 'fe_users';
			$where = "uid = $uid";
			$result = $this->db->exec_UPDATEquery($table,$where,$fields_values);

			if ($this->debug) {
				echo '<br>Query result:/' ; var_dump($result); echo '/';
				echo '<br>Affected rows:' . mysql_affected_rows();
				$query =  $this->db->UPDATEquery($table,$where,$fields_values);
				$updateQuery .= $query . '; ';
			}
		}

		if ($this->debug) echo $updateQuery;
	}
	
	/**
	* Retrieves and returns the data rows for the working query which will be used 
	* for updating fe_usergroups.
	* @return the data rows as an array of arrays.
	*/
	function getRowsFeUserUpdate($uid = null)
	{
		if ($this->debug) {
			echo "getRowsFeUserUpdate($uid)<br>";
		}
		
		/*
		Whole query looks like this:
		SELECT fe_users.uid feusers_uid, fe_users.email AS feusers_email, fe_users.usergroup AS current_accesslevel, 
			tx_memberaccess_acl.email AS memberaccess_email, tx_memberaccess_acl.accesslevel AS desired_accesslevel,
			tx_memberaccess_acl.endtimeExtension AS endtimeExtension, tx_memberaccess_acl.endtime AS desired_endtime
		FROM tx_memberaccess_acl, fe_users
		WHERE tx_memberaccess_acl.email = fe_users.email
		*/
		$where = "fe_users.email = $this->table_member_acl.email";
		$where .= is_null($uid) ? '' : " AND fe_users.uid = $uid";
		$where .= " AND fe_users.deleted != 1  AND $this->table_member_acl.deleted !=1 AND fe_users.disable != 1";
		$where .= " AND $this->table_member_acl.endtime <= " . time();
		$columns = "fe_users.uid feusers_uid, fe_users.email AS feusers_email, "
			."fe_users.usergroup AS current_accesslevel, $this->table_member_acl.email AS memberaccess_email, "
			."$this->table_member_acl.accesslevel AS desired_accesslevel"
			." , $this->table_member_acl.endtimeExtension AS endtimeExtension, $this->table_member_acl.endtime AS desired_endtime "
			.", fe_users.endtime AS endtime ";
		$from = "fe_users, $this->table_member_acl";
		$groupby = '';
		$orderby = '';
		$rows =  $this->db->exec_SELECTgetRows($columns, $from, $where, $groupby, $orderby  );
		
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}

		if ($this->debug) {
			echo $where;
			echo 'Rows result:';
			foreach ( $rows as $row ) {
				print_r($row);
				//$output .= '';
			}
			reset( $rows );
		}
		return $rows;
	}

	
	/**
	Returns new endtime for a particular user based on current and desired endtimes.
	@param the current endtime from fe_users in Unixtime
	@param the desired endtime as specified in field tx_memberaccess_acl.endtime
	@param the desired endtime extension as specified in field tx_memberaccess_acl.endtimeExtension
	@return newendtime as Unix time
	*/
	function getNewEndtime( $currentEndtime, $endtimeDesired, $endtimeExtension )  {
		
		//Assume not professional,
		if ($this->debug) { echo "function getNewEndtime( currentEndtime $currentEndtime, endtimeDesired $endtimeDesired, endtimeExtension $endtimeExtension )  {"; }
			
		$newEndtime = null;
		
		//check if that record has endtimeExtension set.
		//if (!empty($endtimeExtension) && 0 != $endtimeExtension) {
		if (!empty($endtimeExtension)) {
			if ($this->debug) { echo " endtimeExtension is set "; }
			//If set, add endtimeExtension no. of days to the current enddate.
			$strToTimeString = "+$endtimeExtension day";
			if ($this->debug) { echo $strToTimeString ; }
			$newEndtime = strtotime( $strToTimeString );
		//} else if (!empty($endtimeDesired) && 0 != $endtimeDesired) {
		} else if (!empty($endtimeDesired)) {
			if ($this->debug) { echo " endtimeDesired is set "; }
			//If endtimeExtension not set, if the record has endtime set,
			//set the enddate to that date.
			//but only if the desired enddate is greater than current enddate
			
			//if the current endtime is blank, use the specified endtime
			//if specified is less, keep the current
			//if specified is more, use specified
			if (is_null($currentEndtime) || ''==$currentEndtime || 0==$currentEndtime) {
				$newEndtime = $endtimeDesired;
			} else if ($endtimeDesired < $currentEndtime ){
				$newEndtime = $currentEndtime;
			} else if ($endtimeDesired >= $currentEndtime ){
				$newEndtime = $endtimeDesired;
			}
			
		} else {
			if ($this->debug) { echo " neither are set "; }
			//if neither endtimeExtension nor endtime are set, leave the endtime as-is.
			$newEndtime = $currentEndtime;
			
		}
		
		if ($this->debug) { assert(!is_null($newEndtime)); }
		
		return $newEndtime;
		
	}
	
	
	/**
	* Displays the current member access list.
	* @return a string containing the member access list report in HTML.
	*/
	function displayAccessList()
	{
		global $LANG;
		
		//get the rows
		$rows				= $this->getRowsAccessList();
		$fieldsToFormat 	= array($this->fieldAlias_usergroup, $this->fieldAlias_endtime);
		$rowsFormatted		= $this->formatRowsAccessList($rows, $fieldsToFormat);
		
		//and format the data, and convert to HTML with a title, before returning them.
		return cbArr2Html( $rowsFormatted, $LANG->getLL( 'report.accesslist.title' ) );
	}

	/**
	* Retrieves and returns the data rows for the access list stored in the database.
	* @return the data rows as an array of arrays.
	*/
	function getRowsAccessList()
	{
		if ($this->debug) {
			echo 'getRowsAccessList()<br>';
		}
		
		$where = ' deleted != 1 ';
		$columns = 'name, company, email, accesslevel, endtime AS DesiredEndtime, endtimeExtension';
		$groupby = '';
		$orderby = 'name, company, email, accesslevel, endtime';
		$rows =  $this->db->exec_SELECTgetRows($columns, $this->table_member_acl, $where, $groupby, $orderby  );
		
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}

		if ($this->debug) {
			echo $where;
			echo 'Rows result:';
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
	* @param an array of strings which specifies which fields should be formatted
	* @return an array of rows with formatted data.
	*/
	function formatRowsAccessList( &$rows, $fieldsToFormat )
	{
		//copy the original rows array to a working array
		$rowsFormatted = $rows;
		if ($this->debug) {
			echo 'formatRowsAccessList( &$rows )';
			echo '<br>count($rows): ' . count($rows);
			//print_r($rows);
			echo ' fieldsToFormat:'; var_dump($fieldsToFormat);
			
		}
		
		//for each row, substitute formatted text in the working array
		for( $i=0; $i<count($rows); $i++ ) {
			
			$row = $rows[$i];
			//usergroup
			if (in_array($this->fieldAlias_usergroup, $fieldsToFormat )){
				$rowsFormatted[$i][$this->fieldAlias_usergroup] = $this->formatUserGroup($row[$this->fieldAlias_usergroup]);
			}
			
			//endtime: print 0 if 0, otherwise something like '08/12/2006'
			if (in_array($this->fieldAlias_endtime, $fieldsToFormat )) {
				$rowsFormatted[$i][$this->fieldAlias_endtime] = (0==$row[$this->fieldAlias_endtime])
					? '0'
					: date("m/d/Y", $rowsFormatted[$i][$this->fieldAlias_endtime])
					;
			}
			
			if ($this->debug) {
				echo ' iteration '.$i;
				echo ' ug:'.$rowsFormatted[$i][$this->fieldAlias_usergroup];
				echo ' et:'.$rowsFormatted[$i][$this->fieldAlias_endtime];
			}
		}
		return $rowsFormatted;
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
				'1'						=> 'Complimentary Member'
				, '2'					=> 'Professional Member'
				, '4'					=> 'Prof. Guest Member'
				, '5'					=> 'Visitor, White Paper'
				, '6'					=> 'Visitor, Round Table'
				, '7'					=> 'Visitor, Presentation'
				, '8'					=> 'Editor Preview'
				, '9'					=> 'Chicago Conference'
				, '10'					=> 'San Francisco Conference'
				, '11'					=> 'Washington DC Conference'
				, '12'					=> 'New York Conference'
				, '13'					=> '-'	// SOA comp
				, '14'					=> '~'	// SOA pro
				, '15'					=> '*'	// SOA member
				, '16'					=> 'Corporate Membership'
				, '17'					=> 'BPM Institute'	// BPM domain
				, '18'					=> 'SOA Institute'	// SOA domain
				, '19'					=> ''	// Conference requesters
				, '20'					=> ''	// BSG only
				, '21'					=> ''	// Sales login
				, '22'					=> ''	// Sponsor login 
				, '23'					=> ''	// TBD
				, '24'					=> ''	// TBD
				, '25'					=> ''	// TBD
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
	 * Determine which download is being requested and redirect to it.
	 * Note: if testing this, $this->debug needs to be false, since any echoes
	 * will interfere with the file being sent to the browser.
	 * @return void
	 */
	function doDownload ( $what )
	{
		switch ($what) {
			case 'tx_memberaccess_modfunc1_accesstool_command_download_reporting': 
				$this->downloadAccessListForReporting();
				break;
			case 'tx_memberaccess_modfunc1_accesstool_command_download_aclmodification': 
				$this->downloadAccessListForACLModification();
				break;
			default:
				//not supposed to happen
				if ($this->debug) echo 'Improper case (doDownload ( $what ))';
		}
	}

	/**
	 * Offer up the current access list for download to the user for reporting purposes.
	 * This is given to the user as a comma-separated values file, suitable for
	 * import into Excel.
	 * This version substitutes group names in place of group codes.
	 *
	 * @return void
	 */
	function downloadAccessListForReporting()
	{
		$rows				= $this->getRowsAccessList();
		$fieldsToFormat 	= array($this->fieldAlias_usergroup, $this->fieldAlias_endtime);
		$rowsFormatted		= $this->formatRowsAccessList($rows, $fieldsToFormat);

		// convert rows to csv for download
		$rowsCsv                = '';

		foreach ( $rowsFormatted as $key => $value )
		{
			$rowsCsv            .= cbMkCsvString( $value );
		}

		$filename				= $this->getDownloadFilename( 'Member_AccessList_Report_' );
		
		// download members list
		// send to browser as download
		cbBrowserDownload( $filename, $rowsCsv );
		exit();
	}

	/**
	 * Offer up the current access list for download to the user for ACL modification
	 * purposes. This version substitutes group codes, not group names; this
	 * makes it easy to change or add access control entries (since these have to
	 * use the group code, not the name).
	 * This is given to the user as a comma-separated values file, suitable for
	 * import into Excel.
	 * 
	 *
	 * @return void
	 */
	function downloadAccessListForACLModification()
	{
		$rows0                	= $this->getRowsAccessList();
		$fieldsToFormat 		= array($this->fieldAlias_endtime);
		$rows					= $this->formatRowsAccessList($rows0, $fieldsToFormat);


		// convert rows to csv for download
		$rowsCsv                = '';

		foreach ( $rows as $key => $value )
		{
			$rowsCsv            .= cbMkCsvString( $value );
		}

		$filename				= $this->getDownloadFilename( 'Member_AccessList_' );
		
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
	* Cache GET/POST values in class variables.
	* (Not used in this wizard, but it's handy to have in place.)
	*/
	function setValues(){}

}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_access/modfunc1/class.tx_memberaccess_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_access/modfunc1/class.tx_memberaccess_modfunc1.php"]);
}

?>
