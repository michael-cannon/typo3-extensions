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
 * Plugin 'Membership expiry processor' for the 'member_expiry' extension.
 *
 * @author Jaspreet Singh	<>
 * Notifies members that their memberships are expiring.  Upon expiry,
 * changes membership to free membership and notifies user of the same. 
 * $Id: class.tx_memberexpiry_pi1.php,v 1.1.1.1 2010/04/15 10:03:50 peimic.comprock Exp $
 */

require_once(PATH_tslib."class.tslib_pibase.php");

class tx_memberexpiry_pi1 extends tslib_pibase {
	var $prefixId = "tx_memberexpiry_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_memberexpiry_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "member_expiry";	// The extension key.
	
	/**Global database pointer*/
	var $db = null;
    
    //Sender info for emails.
    var $emailFromAddress		= '';
    var $emailFromName			= '';
	var $emailReplyToAddress	= '';
    
    //names of the templates for HTML mails
    var $templateExpiring = 'membership_expiring';
	var $templateExpired = 'membership_expired';
	var $templateFileExtension = 'tmpl';
	
	
	//Name by which to address the user if the name field is blank in the database.
	var $defaultMemberName = 'Member';
	
	var $debug = false; //turn on/off debugging output.
	
    //The names of TypoScript configuration keys which allow customizing the operation
    //of the plugin by setting values in backend template records.
    //The full names of these become something like
    //plugin.tx_memberexpiry_pi1.notification_interval_1=1
	//If these aren't set in TypoScript, the associated variables are set to the
	//defaults specified below.
    var $preExpiryNotificationInterval1Key	= 'preexpiry_notification_interval_1';
    var $preExpiryNotificationInterval2Key	= 'preexpiry_notification_interval_2';
    var $preExpiryNotificationInterval3Key	= 'preexpiry_notification_interval_3';
    var $postExpiryNotificationIntervalKey	= 'postexpiry_notification_interval';
	var $debugEmailAddressKey				= 'debug_email_address';
    var $bccEmailAddressKey					= 'bcc_email_address';

	//Configuration options.
	//Don't set these directly here.  Either set the value in TypoScript (see above)
	//or don't set in TypoScript but do set the default values below.    
    var $preExpiryNotificationInterval1;
    var $preExpiryNotificationInterval2;
    var $preExpiryNotificationInterval3;
    var $postExpiryNotificationInterval;
    var $debugEmailAddress;	//if set, don't send to recipients; send all mail here		
	var $bccEmailAddress;	//if set, send a copy of all mail to this address	

    //Defaults for configuration options.
	var $PREEXPIRY_NOTIFY_INTERVAL_1_DEFAULT = 1;
    var $PREEXPIRY_NOTIFY_INTERVAL_2_DEFAULT = 7;
    var $PREEXPIRY_NOTIFY_INTERVAL_3_DEFAULT = 14;
    var $POSTEXPIRY_NOTIFY_INTERVAL_DEFAULT = 1;
	var $DEBUG_EMAIL_ADDRESS_DEFAULT;
	var $BCC_EMAIL_ADDRESS_DEFAULT;

	//Constants.
	var $SECONDS_PER_DAY = 86400;
	//Group member IDs.  From fe_groups table, uid field.
	var $COMPLIMENTARY_MEMBER_GROUPID = 1;
	var $PROFESSIONAL_MEMBER_GROUPID = 2;
	var $COMPLIMENTARY_PROF_MEMBER_GROUPID = 4;
	//Conference groups array.  All of the IDs of groups which track conferences.
	var $conferenceGroupIDs = null;
	
	/**
	 * Membership processing start
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
			
		// current database object
        $this->db                = $GLOBALS[ 'TYPO3_DB' ];

		if ($this->debug) {
			$this->db->debugOutput    = true;
            echo '$content<br>';
            $this->debugOut($content);
            echo '$conf<br>';
            $this->debugOut($conf);
            echo 'end $conf<br>';
            
		}
		
		$this->initialize();
		
        $this->setTSConfValues();
        
		//Notify people whose memberships are expiring
		//Do this only if the action hasn't already executed.
		//If we execute, set a flag to that effect.
		$this->sendExpiryNotifications( $this->preExpiryNotificationInterval1 );
		$this->sendExpiryNotifications( $this->preExpiryNotificationInterval2 );
		$this->sendExpiryNotifications( $this->preExpiryNotificationInterval3 );
        
        //Notify people whose memberships expired and downgrade their memberships
        if ($this->debug) echo "\n<hr>Before Expired()<br>\n";
		$this->sendExpiredNotificationsAndDowngrade( $this->postExpiryNotificationInterval );
		if ($this->debug) echo "\n<br>After Expired()<hr>\n";
	}
	
	/**
	Performs misc. initialization functions.
	Should be called after instationation of class.
	*/
	function initialize() {
		//HARDCODE
		$this->conferenceGroupIDs = array( 9, 10, 11, 12 );
	}
	
	
	/**
    * Just some debugging output.
    */
    function debugOut($param)
    {
        if ( is_scalar($param) ){
            var_dump($param);
        }
        if ( is_array($param) ){
            print_r($param);
        }
		echo '<br />';
    }
    
    /**
    * Retrieve values from TypoScript backend configuration and cache them as
    * necessary for use as parameters in the operation of the plugin.
    * If TypoScript values are not set, use the defaults.
    * No parameters.  Gets its value from the class $conf variable, which should
    * be set to the Typo3 $conf variable passed to main() before calling this 
    * function.
    * @return void
    */
    function setTSConfValues() {
        $this->preExpiryNotificationInterval1 = $this->conf[$this->preExpiryNotificationInterval1Key];
        if (!isset($this->preExpiryNotificationInterval1)) {
            $this->preExpiryNotificationInterval1 = $this->PREEXPIRY_NOTIFY_INTERVAL_1_DEFAULT;
        }

        $this->preExpiryNotificationInterval2 = $this->conf[$this->preExpiryNotificationInterval2Key];
        if (!isset($this->preExpiryNotificationInterval2)) {
            $this->preExpiryNotificationInterval2 = $this->PREEXPIRY_NOTIFY_INTERVAL_2_DEFAULT;
        }

        $this->preExpiryNotificationInterval3 = $this->conf[$this->preExpiryNotificationInterval3Key];
        if (!isset($this->preExpiryNotificationInterval3)) {
            $this->preExpiryNotificationInterval3 = $this->PREEXPIRY_NOTIFY_INTERVAL_3_DEFAULT;
        }

        $this->postExpiryNotificationInterval = $this->conf[$this->postExpiryNotificationIntervalKey];
        if (!isset($this->postExpiryNotificationInterval)) {
            $this->postExpiryNotificationInterval = $this->POSTEXPIRY_NOTIFY_INTERVAL_DEFAULT;
        }
        
        $this->debugEmailAddress = $this->conf[$this->debugEmailAddressKey];
        if (!isset($this->debugEmailAddress)) {
            $this->debugEmailAddress = $this->DEBUG_EMAIL_ADDRESS_DEFAULT;
        }

        $this->bccEmailAddress = $this->conf[$this->bccEmailAddressKey];
        if (!isset($this->bccEmailAddress)) {
            $this->bccEmailAddress = $this->BCC_EMAIL_ADDRESS_DEFAULT;
        }

        if ($this->debug) {
            
            echo "<br> this - >preExpiryNotificationInterval1 $this->preExpiryNotificationInterval1<br>";
            echo " this - >preExpiryNotificationInterval2 $this->preExpiryNotificationInterval2<br>";
            echo " this - >preExpiryNotificationInterval3 $this->preExpiryNotificationInterval3<br>";
            
            echo " this - >postExpiryNotificationInterval $this->postExpiryNotificationInterval<br>";
			echo " this - >debugEmailAddress 			$this->debugEmailAddress<br>";
			echo " this - >bccEmailAddress $this->bccEmailAddress<br>";
            
        }
    }
    
    
    /**
    * Send notifications to members whose memberships are expiring in x no. of days.
	* This applies only to professional and complimentary-professional memberships.
    * @param int number of days in advance to warn about expiration.
    * @return void
    */
	function sendExpiryNotifications( $days ) {
		
		if ($this->debug) echo "sendExpiryNotifications( $days )";
		
		//What is "now/today" for the purposes of expired "today" or 1 day from 
		//"today"? :   We will define it as 1 minute past 12 midnight on today's date.
		//This is so the definition of "now" isn't constantly moving as we
		//do processing.
		$today = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
		if ($this->debug) {
            echo '$today ' . $today; //debug
			echo '<br />';
			echo date( 'r', $today );
			echo '<br />';
            echo ' time() ' . time(); //debug
			echo '<br />';
        }
		
        
		//Example: Need to know which items expire 1 day from today.  
        //min below would be today + 1day
        //max would be min + 1 day.
        //Then find all rows that have endtime between min and max
		$expiry_period_min = $today + ($days * $this->SECONDS_PER_DAY);
		$expiry_period_max = $expiry_period_min - 1 + $this->SECONDS_PER_DAY;
        
		if ($this->debug) {
			cbDebug( 'expiry_period_min', date( 'r', $expiry_period_min ) );	
			cbDebug( 'expiry_period_max', date( 'r', $expiry_period_max ) );	
        }

        /* Example query:
        SELECT name, email, endtime
        FROM fe_users
        WHERE endtime >=1111317260
        AND endtime <1111403660
        AND ( usergroup REGEXP "[[:<:]]2[[:>:]]" OR usergroup REGEXP "[[:<:]]4[[:>:]]" )
		AND NOT (tx_memberexpiry_emailsenttime >= 1111317260 and  
			tx_memberexpiry_emailsenttime < 1111403660)
        */
        
        $where = "endtime >= $expiry_period_min and endtime < $expiry_period_max ";
		$where .= 'AND ( usergroup REGEXP "[[:<:]]'.$this->PROFESSIONAL_MEMBER_GROUPID.'[[:>:]]" ' 
			. 'OR usergroup REGEXP "[[:<:]]'.$this->COMPLIMENTARY_PROF_MEMBER_GROUPID.'[[:>:]]" )';
		$where .= " AND NOT ( tx_memberexpiry_emailsenttime = $today ) ";
		$columns = 'uid, first_name name, email, endtime, usergroup, username, deleted, disable';
        $query	=  $this->db->SELECTquery($columns, 'fe_users', $where );
		cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
		cbDebug( 'query', $query );	
        $expiring_rows =  $this->db->exec_SELECTgetRows($columns, 'fe_users', $where );
        if ($this->debug) {
			print_r( $expiring_rows ); echo "\n<br>"; //debug
			echo $where;
		}
        
		$expireDate						= $expiry_period_min;
        $expiry_formatted_date_full		= $this->getExpireTimeFormatted('date', $expireDate); 
		$expiry_days_text 					= $this->getExpireTimeFormatted('days', $days);
		$expiry_formatted_date_noyear 	= $this->getExpireTimeFormatted('date_noyear', $expireDate);
        $replacement_patterns = array(
			"/\#\#\#MEMBER_NAME\#\#\#/"
			, "/\#\#\#MEMBERSHIP_EXPIRY_DATE_FULL\#\#\#/"
			, "/\#\#\#USER_NAME\#\#\#/"
			, "/\#\#\#MEMBERSHIP_EXPIRY_DAYS\#\#\#/"
			, "/\#\#\#MEMBERSHIP_EXPIRY_DATE_NOYEAR\#\#\#/"
			);  
        
        //For each expiring row, send an email.
        //debug
        if ($this->debug) echo "\n<br>Printing rows";
        foreach ( $expiring_rows as $row ) {

            set_time_limit(30);
			
			//compensate for blank name fields
			$name1 = $row['name'] == '' ? $this->defaultMemberName : $row['name'];
			$name = $this->capitalizeWords($name1);
			$user_name = $row['username'];
			
            //debug
            if ($this->debug) {
				echo '<br>';
				print_r( $row );
				echo 'Dear ' . $name ;
				echo 'Your membership expires ' . $expiry_formatted_date_full . '.';
				echo '<br />';
            }
			
			//note: reference $replacement_patterns above
            $replacement_text = array(
				$name
				, $expiry_formatted_date_full
				, $user_name
				, $expiry_days_text
				, $expiry_formatted_date_noyear
			);
            $htmlBody = $this->prepareTemplate($this->templateExpiring, 
                $replacement_patterns, $replacement_text
            );
            
			// don't email old person
			if ( ! $row['deleted'] && ! $row['disable'] )
			{
            	$this->sendEmail( $htmlBody, $row['email'] );
			}
			$this->logEmailSent( $row['uid'], $today );
        }
        

	}
	
    
	/**
    * Send notifications that memberships are expired.
	* This applies only to professional and complimentary-professional memberships.
	* Downgrade the memberships of people whose memberships are expired to 
	* free membership.
    * @param int the window of time over which this function operates in days
	*   i.e., there if set 1, it will process rows which have an end date from
	*	(today - 86400 seconds) to today.  In this case, this whole script should
	*	be run every day.  Or set $days to 7 and run the script every week.
    * @return void
    */
	function sendExpiredNotificationsAndDowngrade( $days )
	{
		if ($this->debug) echo "sendExpiredNotificationsAndDowngrade( $days )";
		
		//What is "now/today" for the purposes of expired "today" or 1 day from 
		//"today"? :   We will define it as 1 minute past 12 midnight on today's date.
		//This is so the definition of "now" isn't constantly moving as we
		//do processing.
		$today = mktime(0, 1, 0, date("m")  , date("d"), date("Y"));
		if ($this->debug) {
			echo '$today ' . $today; //debug
			echo ' time() ' . time(); //debug
			echo '<br />';
		}
		
        
		//Example: Need to know which items expired. 1 day from today.  
        //min below would be today - 1day
        //max would be today
        //Then find all rows that have endtime between min and max
		$expired_period_min = $today - ($days * $this->SECONDS_PER_DAY);
		$expired_period_max = $today;
        
		if ($this->debug) {
			cbDebug( 'expired_period_min', date( 'r', $expired_period_min ) );	
			cbDebug( 'expired_period_max', date( 'r', $expired_period_max ) );	
        }

        /* Example query:
		SELECT uid, username, name, email, endtime
		FROM fe_users
		WHERE endtime >=1121144460
		AND endtime <1121230860
        AND ( usergroup REGEXP "[[:<:]]2[[:>:]]" OR usergroup REGEXP "[[:<:]]4[[:>:]]" )  
		AND NOT (tx_memberexpiry_emailsenttime >= 1121144460 and  
			tx_memberexpiry_emailsenttime < 1121230860)
        */
        
		// capture missed expiired folks as well
        $where = "endtime < $expired_period_max ";
		// at some point exclude non-expiring
        // $where = "endtime < $expired_period_max and endtime != 0";
		$where .= 'AND ( usergroup REGEXP "[[:<:]]'.$this->PROFESSIONAL_MEMBER_GROUPID.'[[:>:]]" ' 
			. 'OR usergroup REGEXP "[[:<:]]'.$this->COMPLIMENTARY_PROF_MEMBER_GROUPID.'[[:>:]]" )';
		$where .= " AND NOT ( tx_memberexpiry_emailsenttime = $today ) ";
		$columns = 'uid, first_name name, email, endtime, usergroup, username, deleted, disable';
        $query =  $this->db->SELECTquery($columns, 'fe_users', $where );
		cbDebug( 'query', $query );	
        $expired_rows =  $this->db->exec_SELECTgetRows($columns, 'fe_users', $where );
        if ($this->debug) {
			print_r( $expired_rows ); echo "\n<br>"; //debug
			echo $where;
		}
        
        //For each expired row, send an email and build the update statement
		//that will downgrade prof. and free prof. to free membership.
		
        $replacement_patterns = array("/\#\#\#MEMBER_NAME\#\#\#/", "/\#\#\#MEMBERSHIP_EXPIRY_DATE\#\#\#/", "/\#\#\#USER_NAME\#\#\#/");
        
        //debug
        if ($this->debug) echo "\n<br>Printing rows";
        foreach ( $expired_rows as $row ) {
            set_time_limit(30);
			// if we know endtime, use it, else go back in time and look like
			// clean up
        	$expired_formatted_date = ( 0 != $row['endtime'] )
										?  $this->getExpireTimeFormatted('date', $row['endtime'] )
        								: $this->getExpireTimeFormatted('date', $expired_period_min ); 
			
			//compensate for blank name fields
			$name1 = $row['name'] == '' ? $this->defaultMemberName : $row['name'];
			$name = $this->capitalizeWords($name1);
			$user_name = $row['username'];
			
            //debug
            if ($this->debug) {
				echo '<br>';
				print_r( $row );
				echo 'Dear ' . $name ;
				echo 'Your membership expired ' . $expired_formatted_date . '.';
            }
			
			$replacement_text = array($name , $expired_formatted_date, $user_name);
            $htmlBody = $this->prepareTemplate($this->templateExpired, 
                $replacement_patterns, $replacement_text
            );
            
			// don't email old person
			if ( ! $row['deleted'] && ! $row['disable'] )
			{
            	$this->sendEmail( $htmlBody, $row['email'] );
			}
			$this->logEmailSent( $row['uid'], $today );
			$this->expireMembership($row['uid'], $row['usergroup'], $row['endtime']);
        }
        
    }
	
	
	/**
	* Expires a user's membership.
	* If a user is a member of the professional or complimentary professional groups,
	* he is changed to being a member of the complimentary membership group.
	* The endtime value is set to 0 and the old endtime value is placed in the
	* tx_memberexpiry_expiretime field.  Starttime is also set to 0.
	* @param uid of the row
	* @param the old usergroup value
	* @param the old endtime
	* @return void
	*/
	function expireMembership($uid, $usergroup, $endtime)
	{
		/*Example update query:
		Change prof./free prof. members to free and move the endtime to expired field:
		UPDATE fe_users
		SET usergroup='1,9', tx_memberexpiry_expired=1, tx_memberexpiry_expiretime=[endtime], endtime=0
			starttime=0;
		WHERE uid=6461
		*/

		// Drop the professional and complimentary professional access from this users groups list
		// and change it to free membership.
		// Since the usergroup value is a comma-separated values string, we explode on the comma,
		// process, and then implode into a string again.
		$comma = ",";
		$usergroupsArray = explode( $comma, $usergroup );
		$newUsergroupsArray = array();
		foreach ($usergroupsArray as $groupid) {
			if ( $groupid == $this->COMPLIMENTARY_PROF_MEMBER_GROUPID  || $groupid == $this->PROFESSIONAL_MEMBER_GROUPID ) { 
				 $newUsergroupsArray[] = $this->COMPLIMENTARY_MEMBER_GROUPID;
			} else if (in_array( $groupid, $this->conferenceGroupIDs) ) { 
				// if the group is a conference group, drop it.
				// do nothing (i.e., drop the group from the new group string)
			} else { //otherwise leave it the same
				$newUsergroupsArray[] = $groupid;
			}
		}
		$newUsergroup = implode( $comma, $newUsergroupsArray );
		
		//run the query
		$fields_values = array(
			'usergroup' => $newUsergroup
			, 'tx_memberexpiry_expiretime' => $endtime
			, 'tx_memberexpiry_expired' => 1
			, 'endtime' => 0
			, 'starttime' => 0
		);
		if ($this->debug) {
			echo "\n<br>Old/New user group: /" . $usergroup . '/' . $newUsergroup . '/';
			
			echo $this->db->UPDATEquery( 'fe_users', 'uid='.$uid, $fields_values);	
		}
		$this->db->exec_UPDATEquery( 'fe_users', 'uid='.$uid, $fields_values);
	}
	
    
	/**
	* Generates the formatted text for the expire date.
	* This could be either in the form "in 5 days" or "June 5, 2000", depending on the 
	* value of $type.
    * This function handles singular/plural of days correctly (i.e., "in 1 day", 
    * "in 2 days").
	* @param the desired text output type.  'days' for style "in 5 days", 'date' for
	*	"June 5, 2000", 'date_noyear' for "June 5"
	* @param if $type is 'date' or 'date_noyear', the expire date/time in Unix time.
	*	If $type is	'days', the no. of days in which expiry will occur.
	* @return the formatted date text
	*/
	function getExpireTimeFormatted($type, $expiretime)
	{	
		
		$formattedText = '';
		switch ($type) {
			case 'date': 
                $formattedText = date('F j, Y', $expiretime ); break;
			case 'date_noyear': 
                $formattedText = date('F j', $expiretime ); break;
			case 'days':
                $daysText = (1 == $expiretime ) ? 'day' : 'days';  
                $formattedText = "in $expiretime $daysText"; break;
			default:
				$formattedText = date('F j, Y', $expiretime ); 
				if ($this->debug) echo 'Error in getExpireTimeFormatted.';
				break;
		}
		
		return $formattedText;
	}
	
	
    /**
    * Send the notification email.
	* If $this->debugEmailAddress is set, the email will be sent to the debug address,
	* not to the intended recipient.
	* If $this->bccEmailAddress is set, a copy of the email will be sent to the bcc
	* address.
	* (If both are set, 1 email will be sent to debug address, 1 to bcc address.)
    * @param HTML text to send
    * @param email of recipient
    * @return void
    */
    function sendEmail($htmlBody, $to){
        $plainBody = $this->htmlToPlainText( $htmlBody );
		
		//main e-mail
		if (isset($this->debugEmailAddress) && $this->debugEmailAddress !='') {
			$to = $this->debugEmailAddress;
		}
		if ($to != '') {
			tx_srfeuserregister_pi1::sendHTMLMail(
										$htmlBody
										, $plainBody
										, $to
										, '' // cc
										, $this->conf['emailFromAddress'] 
										, $this->conf['emailFromName'] 
										, $this->conf['emailReplyToAddress'] 
			);
			if ($this->debug) {
				echo "Sending mail to $to";
			}
		}

		//blind carbon copy
		if (isset($this->bccEmailAddress) && $this->bccEmailAddress !='') {
			$toBcc = $this->bccEmailAddress;
			tx_srfeuserregister_pi1::sendHTMLMail(
										$htmlBody
										, $plainBody
										, $toBcc
										, '' // cc
										, $this->conf['emailFromAddress'] 
										, $this->conf['emailFromName'] 
										, $this->conf['emailReplyToAddress'] 
			);
			if ($this->debug) {
				echo "Sending mail to $toBcc";
			}
		}
        
    }
	
	/**
	Log the fact that we sent this user an e-mail.
	This is logged to field tx_memberexpiry_emailsenttime in fe_users.
	@param string uid of the user to whom mail was sent
	@param int Time to save in the log entry
	@return void
	*/
	function logEmailSent( $uid, $time ) {
		/*Example update query:
		Change prof./free prof. members to free and move the endtime to expired field:
		UPDATE fe_users
		SET tx_memberexpiry_emailsenttime = 1111317260 
		WHERE uid=6461
		*/
		
		$emailSentTime = $time;
		
		if ($uid && $uid!=0) {
			//run the query
			$fields_values = array(
				'tx_memberexpiry_emailsenttime' => $emailSentTime
			);
			if ($this->debug) {
				echo "\n<br>function logEmailSent( $uid ) {";
				echo $this->db->UPDATEquery( 'fe_users', 'uid='.$uid, $fields_values);	
			}
 			$this->db->exec_UPDATEquery( 'fe_users', 'uid='.$uid, $fields_values);
			
		}
		
	}

    /**
    * Converts HTML to plain text.  Taken with thanks from PHP help.
    * @param HTML text
    * @return plain text
    */
	function htmlToPlainText( $htmlBody )
    {
		
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
				"'<[\/\!]*?[^<>]*?>'si",           // Strip out HTML tags
				"'([\r\n])[\s]+'",                 // Strip out white space
				"'&(quot|#34);'i",                 // Replace HTML entities
				"'&(amp|#38);'i",
				"'&(lt|#60);'i",
				"'&(gt|#62);'i",
				"'&(nbsp|#160);'i",
				"'&(iexcl|#161);'i",
				"'&(cent|#162);'i",
				"'&(pound|#163);'i",
				"'&(copy|#169);'i",
				"'&#(\d+);'e");                    // evaluate as php
		
		$replace = array ("",
				 "",
				 "\\1",
				 "\"",
				 "&",
				 "<",
				 ">",
				 " ",
				 chr(161),
				 chr(162),
				 chr(163),
				 chr(169),
				 "chr(\\1)");
		
		$text = preg_replace($search, $replace, $htmlBody);

		return $text;
    }
    
    /**
    * Prepares an HTML temlate by replacing patterns with replacement text.  
    * @param name of the template (without extension)
    * @param an array of patterns
    * @param an array of replacements
    * @return the template
    */
    function prepareTemplate($templateName, $replacement_patterns, $replacement_text)
    {
		$template = $this->getTemplate($templateName);
        return preg_replace($replacement_patterns, $replacement_text, $template);

    }
    
	/**
    * Returns an HTML temlate for HTML mails  
    * @param name of the template (without extension)
    * @return the template
    */
    function getTemplate($templateName)
    {
		$fileName =  $this->getScriptDirectory() . '/' .
			$templateName . '.' . $this->templateFileExtension;
		$templateData ='';
		
		if ($this->debug) {
			
			if ( file_exists($fileName) ) {
				echo "\n<br>Template file exists: " . $fileName;
			} else {
				echo "\n<br>Template file does not exist: " . $fileName;	
			}
			echo ' <br> scriptdir' . $this->getScriptDirectory();
			echo ' <br> self:' . $_SERVER['PHP_SELF'];
		}
		//Read info fr the file if it exists and filesize isn't 0
		if ( file_exists($fileName) && ($fileSize = filesize($fileName)) ) {
			
			
			$fileData = file_get_contents($fileName);
			//if ($this->debug) echo $fileData;
			$templateData = $fileData;
		} else {  // otherwise get template from the fallback
			$templateData = $this->getTemplateBackup( $templateName );
		}
		
		
		return $templateData;
	}
	
	/**
	* Returns the directory where the script is located.
	* @return the script directory.
	*/
	function getScriptDirectory()
	{
		// $_SERVER["PATH_TRANSLATED"] doesn't work because it Typo3 doesn't
		// execute extensions directly; rather it executes them from /index.php.
		return dirname(__FILE__);
	}
		
	/**
	Change the case of the first letters of the words in the passed string to upper
	case.
	@param string The string whose words are to be capitalized
	@return the passed string with words capitalized
	*/
	function capitalizeWords($name) {
		return ucwords(strtolower($name)); 
	}

	
	/**
	* Returns today's date for the purposes of reporting.  This is a fixed time
	* corresponding to 12:01 midnight, which is the time used throughout the expiry
	* email notification plugin.
	* @param whether or not the return value should be formatted
	* @return if $formatted is false, the date in Unix time.  If $formatted is true,
	*	the date as formatted text.
	*/
    function getToday($formatted=false)
    {
		$today = mktime(0, 1, 0, date("m")  , date("d"), date("Y"));
		if ($this->debug && false) {
			echo '$today ' . $today; //debug
			echo ' time() ' . time(); //debug
		}
        if (false==$formatted) {
            $retVal = $today;
        } else {
			$retVal = $this->getExpireTimeFormatted('date', $today); 
		}
		return $retVal;
    }
	
	
	/**
    * Returns an HTML temlate for HTML mails  
    * This is the backup, just in case the separate template file isn't available.
	* @param name of the template (without extension)
    * @return the template
    */
    function getTemplateBackup($templateName)
    {
        if ($templateName == $this->templateExpiring) {
            $returnValue =  '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta name="generator" content="
HTML Tidy for Windows (vers 1st April 2002), see www.w3.org">
<title>BPM Institute Membership Is Expiring</title>
<link href="http://www.bpminstitute.org/styles.css" rel="
stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="
text/html; charset=iso-8859-1">
<!--backup template-->
<!-- Membership expiring notification email template -->
<style type="text/css">

.textfield {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
}
body {
        margin: 0;      background-image:
url("http://www.bpminstitute.org/images/back-ground-newsletter.gif");
background-color: #ffffff;      font-size: 10px;
}
div {
        margin: 0;      padding: 0;
}
table {
        margin: 0;      padding: 0;     border: 0;
}
td {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #666666; font-weight: normal;    margin: 0;
padding: 0;
}
p {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        font-weight: normal;    margin: 6px 0;
}
.logintext {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 9px; color: #FFFFFF;
}
.smalltext {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #999999; font-weight: bold;
}
.smalltextwhite {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #ffffff; font-weight: normal;
}
.sectiontitles-grey {
        font-family: Arial, Helvetica, sans-serif;      font-size: 12px;
font-weight: bold;      color: #5D5D5D; text-transform: uppercase;
letter-spacing: 1px;
}
.sectiontitles-red {
        font-family: Arial, Helvetica, sans-serif;      font-size: 12px;
font-weight: bold;      color: #D00000; text-transform: uppercase;
letter-spacing: 1px;
}
.smalltext-grey {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #666666; font-weight: normal;
}
.smalltext-black {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #000000; font-weight: normal;
}
.more {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
font-weight: bold;      color: #D00000;
}
.morelink a:link
, A.morelink:link {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
color: #D00000; font-weight: bold;
}
.morelink a:visited
,A.morelink:visited {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
color: #999999; font-weight: normal;    font-weight: bold;
}
.morelink a:hover
,A.morelink:hover {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
color: #333333; font-weight: normal;    font-weight: bold;
}
A.fnav:link {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #ffffff; font-weight: bold;
}
A.fnav:visited {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #cccccc; font-weight: bold;
}
A.fnav:hover {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #333333; font-weight: bold;
}

A.copynav:link {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #999999; font-weight: normal;
}
A.copynav:visited {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #333333; font-weight: normal;
}
A.copynav:hover {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #333333; font-weight: normal;
}
A.reglink:link {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #0000FF; font-weight: normal;
}
A.reglink:visited {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #333333; font-weight: normal;
}
A.reglink:hover {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #D00000; font-weight: normal;
}
A.reglink-small:link {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #0000FF; font-weight: normal;
}
A.reglink-small:visited {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #333333; font-weight: normal;
}
A.reglink-small:hover {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #D00000; font-weight: normal;
}
.home-login a:link
, A.home-login:link {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #999999; font-weight: bold;
}
.home-login a:visited
, A.home-login:visited { 
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #333333; font-weight: bold;
}
.home-login a:hover
, A.home-login:hover {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #666666; font-weight: bold;
}
.td-lightgrey {
        background-color: #E3E3E3;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.copyrighttext {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #999999; font-weight: bold;
}
.td-medgrey {
        background-color: #999999;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-darkgrey {
        background-color: #666666;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-white {
        background-color: #FFFFFF;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-red {
        background-color: #D00000;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-black {
        background-color: #000000;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.memberbox {
        background-color: #8C9DA8;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;        border: thin solid #000000;
}

</style>
</head>
<body background="
http://www.bpminstitute.org/images/back-ground-newsletter.gif">
<font size="1" face="Verdana, Arial, Helvetica, sans-serif">To ensure you
receive future messages from BPM Institute, please add roundtable@example.com to your address 
book and list of trusted senders.</font><br>
<br>
<br>
<table width="566" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="100%" class="td-white"> <table width="100%" border="0"
cellpadding="0" cellspacing="0">

        <tr class="td-white"> 
          <td width="1%"><img src="
http://www.bpminstitute.org/images/fl-1-1.jpg" width="475" height="
15" alt=""></td>
          <td width="1%"><img src="
http://www.bpminstitute.org/images/arrow.gif" width="10" height="
15" alt=""></td>
          <td colspan="2" class="td-white"><a href="
http://www.bpminstitute.org/" class="home-login">home</a></td>
          <td width="1%"><img src="
http://www.bpminstitute.org/images/arrow.gif" width="10" height="
15" alt=""></td>
          <td width="4%" colspan="3" class="td-white"><a href="
http://www.bpminstitute.org/join.html" class="
home-login">join</a>&nbsp;</td>
        </tr>
      </table></td>

  </tr>
  <tr> 
    <td class="td-black"> <table width="100%" border="0" cellpadding="0"
cellspacing="0">
        <tr> 
          <td class="td-black"><a href="http://www.bpminstitute.org/"><img
src="http://www.bpminstitute.org/images/logo.jpg" alt="
BPM Institute" width="178" height="76" border="0"></a></td>
          <td width="388" rowspan="2" align="right" class="td-black"><img
src="http://www.bpminstitute.org/images/bpm_11.jpg" width="388"
height="99" alt=""></td>
        </tr>
        <tr> 
          <td class="td-black"> <table width="100%" border="0" cellspacing="0"
cellpadding="0">

              <tr> 
                <td width="5"><img src="
http://www.bpminstitute.org/images/clock_left.jpg" width="5"
height="18"></td>
                <td width="173" align="left" valign="middle" class="td-black"> 
                  &nbsp;&nbsp;&nbsp;</td>
              </tr>
              <tr> 
                <td colspan="2" width="178"><img src="
http://www.bpminstitute.org/images/clock_bottom.jpg" width="178"
height="5"></td>
              </tr>
            </table></td>
        </tr>

      </table></td>
  </tr>
  <tr> 
    <td class="td-white"> <table width="100%" border="0" cellspacing="0"
cellpadding="0">
        <tr> 
          <td><img src="http://www.bpminstitute.org/images/spacer.gif" width="
1" height="4"></td>
        </tr>
        <tr> 
          <td background="
http://www.bpminstitute.org/images/spacer_black.gif"><img src="
http://www.bpminstitute.org/images/spacer.gif" width="1" height="
1"></td>
        </tr>

        <tr> 
          <td><img src="http://www.bpminstitute.org/images/spacer.gif" width="
1" height="5"></td>
        </tr>
      </table></td>
  </tr>
  <tr align="left" class="td-white"> 
    <td valign="top" class="td-white"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="10" align="left" valign="top" class="smalltext-grey"><img
src="
http://www.bpminstitute.org/images/spacer.gif" width="10" height="
1"></td>
          <td align="left" valign="top" class="smalltext-grey"><table
width="100%" border="0" cellspacing="0" cellpadding="0">

              <tr> 
                <td width="1"><img src="
http://www.bpminstitute.org/images/spacer.gif" width="1" height="
8"></td>
                <td class="sectiontitles-red"><a name="
editor"></a>Membership Expiring</td>
              </tr>
              <tr class="td-red"> 
                <td><img src="http://www.bpminstitute.org/images/spacer.gif"
width="
1" height="2"></td>
                <td><img src="http://www.bpminstitute.org/images/spacer.gif"
width="
1" height="1"></td>
              </tr>
            </table>

            <br> <p>Dear ###MEMBER_NAME###: </p>

            <p>Your 
                professional membership will expire ###MEMBERSHIP_EXPIRY_DATE###. Please <a 
href="https://www.bpminstitute.org/member-login/join/secure-join.html">renew</a>
                now. If you do not renew, 
                you will still have complimentary-level membership.

            
            <br clear="all">
            <p>&nbsp;            </p>
            <p>&nbsp;</p>
            <p align="left">&nbsp;</p></td>
          <td width="10" align="left" valign="top"
class="smalltext-grey">&nbsp;</td>

        </tr>
      </table>
    </td>
  </tr>
  <tr class="td-white"> 
    <td align="center" valign="middle" class="td-medgrey"> <img
src="http://www.bpminstitute.org/images/spacer.gif" width="1"
height="20" align="absmiddle"><a href="
http://www.bpminstitute.org/company/about-us/" class="fnav">About us</a> : <a
href="http://www.bpminstitute.org/company/contact/"
class="fnav">Contacts</a> : <a href="
http://www.bpminstitute.org/company/advertise/" class="
fnav">Advertise</a> : <a href="
http://www.bpminstitute.org/company/partners/" class="
fnav">Partners</a></td>

  </tr>
  <tr class="td-white"> 
    <td align="center" valign="middle" class="
td-lightgrey"><span class="copyrighttext"><img src="
http://www.bpminstitute.org/images/spacer.gif" width="1" height="
20" align="absmiddle">BPM Institute &copy; 2005 &bull; <a href="
http://bpminstitute.org/support/privacy-policy.html" class="
copynav">Privacy Policy</a> &bull; <a href="
http://www.bpminstitute.org/privacy.html" class="copynav"></a><a
href="http://bpminstitute.org/support/privacy-policy.html" class="
copynav">Terms Of Use</a><br>
      386 West Main Street &bull; <a href="
http://bpminstitute.org/support/privacy-policy.html" class="
copynav"></a>Northboro, MA 01532 Ph: 508-393-3266</span></td>

  </tr>
</table>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

</body>
</html>

            
            ';
        } else if ($templateName == $this->templateExpired) {
            $returnValue = '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta name="generator" content="
HTML Tidy for Windows (vers 1st April 2002), see www.w3.org">
<title>BPM Institute Membership Has Expired</title>
<link href="http://www.bpminstitute.org/styles.css" rel="
stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="
text/html; charset=iso-8859-1">
<!--backup template-->
<!-- Membership expired notification email template ###MEMBERSHIP_EXPIRY_DATE###  -->
<style type="text/css">

.textfield {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
}
body {
        margin: 0;      background-image:
url("http://www.bpminstitute.org/images/back-ground-newsletter.gif");
background-color: #ffffff;      font-size: 10px;
}
div {
        margin: 0;      padding: 0;
}
table {
        margin: 0;      padding: 0;     border: 0;
}
td {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #666666; font-weight: normal;    margin: 0;
padding: 0;
}
p {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        font-weight: normal;    margin: 6px 0;
}
.logintext {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 9px; color: #FFFFFF;
}
.smalltext {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #999999; font-weight: bold;
}
.smalltextwhite {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #ffffff; font-weight: normal;
}
.sectiontitles-grey {
        font-family: Arial, Helvetica, sans-serif;      font-size: 12px;
font-weight: bold;      color: #5D5D5D; text-transform: uppercase;
letter-spacing: 1px;
}
.sectiontitles-red {
        font-family: Arial, Helvetica, sans-serif;      font-size: 12px;
font-weight: bold;      color: #D00000; text-transform: uppercase;
letter-spacing: 1px;
}
.smalltext-grey {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #666666; font-weight: normal;
}
.smalltext-black {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #000000; font-weight: normal;
}
.more {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
font-weight: bold;      color: #D00000;
}
.morelink a:link
, A.morelink:link {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
color: #D00000; font-weight: bold;
}
.morelink a:visited
,A.morelink:visited {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
color: #999999; font-weight: normal;    font-weight: bold;
}
.morelink a:hover
,A.morelink:hover {
        font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 10px;
color: #333333; font-weight: normal;    font-weight: bold;
}
A.fnav:link {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #ffffff; font-weight: bold;
}
A.fnav:visited {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #cccccc; font-weight: bold;
}
A.fnav:hover {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #333333; font-weight: bold;
}

A.copynav:link {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #999999; font-weight: normal;
}
A.copynav:visited {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #333333; font-weight: normal;
}
A.copynav:hover {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #333333; font-weight: normal;
}
A.reglink:link {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #0000FF; font-weight: normal;
}
A.reglink:visited {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #333333; font-weight: normal;
}
A.reglink:hover {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 11px;        color: #D00000; font-weight: normal;
}
A.reglink-small:link {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #0000FF; font-weight: normal;
}
A.reglink-small:visited {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #333333; font-weight: normal;
}
A.reglink-small:hover {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #D00000; font-weight: normal;
}
.home-login a:link
, A.home-login:link {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #999999; font-weight: bold;
}
.home-login a:visited
, A.home-login:visited { 
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #333333; font-weight: bold;
}
.home-login a:hover
, A.home-login:hover {
        font-family: Geneva, Verdana, Arial, Helvetica, sans-serif;
font-size: 10px;        color: #666666; font-weight: bold;
}
.td-lightgrey {
        background-color: #E3E3E3;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.copyrighttext {
        font-family: Arial, Helvetica, sans-serif;      font-size: 10px;
color: #999999; font-weight: bold;
}
.td-medgrey {
        background-color: #999999;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-darkgrey {
        background-color: #666666;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-white {
        background-color: #FFFFFF;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-red {
        background-color: #D00000;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.td-black {
        background-color: #000000;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;
}
.memberbox {
        background-color: #8C9DA8;      font-family: Arial, Helvetica,
sans-serif;      font-size: 10px;        border: thin solid #000000;
}

</style>
</head>
<body background="
http://www.bpminstitute.org/images/back-ground-newsletter.gif">
<font size="1" face="Verdana, Arial, Helvetica, sans-serif">To ensure you
receive future messages from BPM Institute, please add roundtable@example.com to your address 
book and list of trusted senders.</font><br>
<br>
<br>
<table width="566" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="100%" class="td-white"> <table width="100%" border="0"
cellpadding="0" cellspacing="0">

        <tr class="td-white"> 
          <td width="1%"><img src="
http://www.bpminstitute.org/images/fl-1-1.jpg" width="475" height="
15" alt=""></td>
          <td width="1%"><img src="
http://www.bpminstitute.org/images/arrow.gif" width="10" height="
15" alt=""></td>
          <td colspan="2" class="td-white"><a href="
http://www.bpminstitute.org/" class="home-login">home</a></td>
          <td width="1%"><img src="
http://www.bpminstitute.org/images/arrow.gif" width="10" height="
15" alt=""></td>
          <td width="4%" colspan="3" class="td-white"><a href="
http://www.bpminstitute.org/join.html" class="
home-login">join</a>&nbsp;</td>
        </tr>
      </table></td>

  </tr>
  <tr> 
    <td class="td-black"> <table width="100%" border="0" cellpadding="0"
cellspacing="0">
        <tr> 
          <td class="td-black"><a href="http://www.bpminstitute.org/"><img
src="http://www.bpminstitute.org/images/logo.jpg" alt="
BPM Institute" width="178" height="76" border="0"></a></td>
          <td width="388" rowspan="2" align="right" class="td-black"><img
src="http://www.bpminstitute.org/images/bpm_11.jpg" width="388"
height="99" alt=""></td>
        </tr>
        <tr> 
          <td class="td-black"> <table width="100%" border="0" cellspacing="0"
cellpadding="0">

              <tr> 
                <td width="5"><img src="
http://www.bpminstitute.org/images/clock_left.jpg" width="5"
height="18"></td>
                <td width="173" align="left" valign="middle" class="td-black"> 
                  &nbsp;&nbsp;&nbsp;</td>
              </tr>
              <tr> 
                <td colspan="2" width="178"><img src="
http://www.bpminstitute.org/images/clock_bottom.jpg" width="178"
height="5"></td>
              </tr>
            </table></td>
        </tr>

      </table></td>
  </tr>
  <tr> 
    <td class="td-white"> <table width="100%" border="0" cellspacing="0"
cellpadding="0">
        <tr> 
          <td><img src="http://www.bpminstitute.org/images/spacer.gif" width="
1" height="4"></td>
        </tr>
        <tr> 
          <td background="
http://www.bpminstitute.org/images/spacer_black.gif"><img src="
http://www.bpminstitute.org/images/spacer.gif" width="1" height="
1"></td>
        </tr>

        <tr> 
          <td><img src="http://www.bpminstitute.org/images/spacer.gif" width="
1" height="5"></td>
        </tr>
      </table></td>
  </tr>
  <tr align="left" class="td-white"> 
    <td valign="top" class="td-white"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="10" align="left" valign="top" class="smalltext-grey"><img
src="
http://www.bpminstitute.org/images/spacer.gif" width="10" height="
1"></td>
          <td align="left" valign="top" class="smalltext-grey"><table
width="100%" border="0" cellspacing="0" cellpadding="0">

              <tr> 
                <td width="1"><img src="
http://www.bpminstitute.org/images/spacer.gif" width="1" height="
8"></td>
                <td class="sectiontitles-red"><a name="
editor"></a>Membership Expired</td>
              </tr>
              <tr class="td-red"> 
                <td><img src="http://www.bpminstitute.org/images/spacer.gif"
width="
1" height="2"></td>
                <td><img src="http://www.bpminstitute.org/images/spacer.gif"
width="
1" height="1"></td>
              </tr>
            </table>

            <br> <p>Dear ###MEMBER_NAME###: </p>

            <p>Your professional membership has expired. 
                 
                You can still renew <a 
href="https://www.bpminstitute.org/member-login/join/secure-join.html">here</a>, though
                in the meantime your complimentary membership is active.
                

            
            <br clear="all">
            <p>&nbsp;            </p>
            <p>&nbsp;</p>
            <p align="left">&nbsp;</p></td>
          <td width="10" align="left" valign="top"
class="smalltext-grey">&nbsp;</td>

        </tr>
      </table>
    </td>
  </tr>
  <tr class="td-white"> 
    <td align="center" valign="middle" class="td-medgrey"> <img
src="http://www.bpminstitute.org/images/spacer.gif" width="1"
height="20" align="absmiddle"><a href="
http://www.bpminstitute.org/company/about-us/" class="fnav">About us</a> : <a
href="http://www.bpminstitute.org/company/contact/"
class="fnav">Contacts</a> : <a href="
http://www.bpminstitute.org/company/advertise/" class="
fnav">Advertise</a> : <a href="
http://www.bpminstitute.org/company/partners/" class="
fnav">Partners</a></td>

  </tr>
  <tr class="td-white"> 
    <td align="center" valign="middle" class="
td-lightgrey"><span class="copyrighttext"><img src="
http://www.bpminstitute.org/images/spacer.gif" width="1" height="
20" align="absmiddle">BPM Institute &copy; 2005 &bull; <a href="
http://bpminstitute.org/support/privacy-policy.html" class="
copynav">Privacy Policy</a> &bull; <a href="
http://www.bpminstitute.org/privacy.html" class="copynav"></a><a
href="http://bpminstitute.org/support/privacy-policy.html" class="
copynav">Terms Of Use</a><br>
      386 West Main Street &bull; <a href="
http://bpminstitute.org/support/privacy-policy.html" class="
copynav"></a>Northboro, MA 01532 Ph: 508-393-3266</span></td>

  </tr>
</table>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

<p>&nbsp;</p>

</body>
</html>
';
        }
        return $returnValue;
    }
    
    
    /**
    * Just checks to see if the plugin is reachable from frontend.
    * Call with parameters from main().
    */
    function testPlugin($content, $conf)
	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
			
		// current database object
        $this->db                = $GLOBALS[ 'TYPO3_DB' ];
        $this->db->debugOutput    = true;

		$content='
			<strong>These are some test paragraphs:</strong><BR>
			<p>This is line 1</p>
			<p>This is line 2</p>
	
			<h3>This is a form:</h3>
			<form action="'.$this->pi_getPageLink($GLOBALS["TSFE"]->id).'" method="POST">
				<input type="hidden" name="no_cache" value="1">
				<input type="text" name="'.$this->prefixId.'[input_field]" value="'.htmlspecialchars($this->piVars["input_field"]).'">
				<input type="submit" name="'.$this->prefixId.'[submit_button]" value="'.htmlspecialchars($this->pi_getLL("submit_button_label")).'">
			</form>
			<BR>
			<p>You can click here to '.$this->pi_linkToPage("get to this page again",$GLOBALS["TSFE"]->id).'</p>
		';
	
		return $this->pi_wrapInBaseClass($content);
	
	}
	
	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_expiry/pi1/class.tx_memberexpiry_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/member_expiry/pi1/class.tx_memberexpiry_pi1.php"]);
}

?>
