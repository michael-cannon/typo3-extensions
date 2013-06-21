<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Jaspreet Singh <->
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
 * Plugin 'Comment Notification One-Click Handler' for the 'comment_notify' extension.
Handles turning off comments notification when user clicks the provided URLs
in an e-mail that he receives from the other comment_notify plugin. 
 * @author    Jaspreet Singh <->
 $Id: class.tx_commentnotify_pi2.php,v 1.1.1.1 2010/04/15 10:03:19 peimic.comprock Exp $
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_commentnotify_pi2 extends tslib_pibase {
    var $prefixId = 'tx_commentnotify_pi2';        // Same as class name
    var $scriptRelPath = 'pi2/class.tx_commentnotify_pi2.php';    // Path to this script relative to the extension dir.
    var $extKey = 'comment_notify';    // The extension key.
	/** Typoscript conf */
	var $conf;
	/** Typo Global db variable */
	var $db;
	/** Whether to output debug info */
	var $debug = false;
    
	
	/**
     * The main method of the PlugIn
     *
     * @param    string        $content: The PlugIn content
     * @param    array        $conf: The PlugIn configuration
     * @return    The content that is displayed on the website
     */
    function main($content,$conf)    {
        $this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj=1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		$this->init();
		//$this->test();
		
		$action = $gpvars['action'] = (t3lib_div::_GP('action')); // for cwt_community integration
		if ('globaloff' == $action) {
			
			$verifyKey = (t3lib_div::_GP('verify'));
			$username = (t3lib_div::_GP('username'));
			$this->turnOffGlobalNotificationForUser( $username, $verifyKey );
			//if ($this->debug) { echo "Global off: \$username $username \$verifyKey $verifyKey"; }  
			
			$message = 'All comment notifications have been turned off.';
			
		} else if ('threadoff' == $action) {
			
			$verifyKey = (t3lib_div::_GP('verify'));
			$users_posts_uid = (t3lib_div::_GP('id'));
			$this->turnOffThreadNotificationForUserPost( $users_posts_uid, $verifyKey );
			//if ($this->debug) { echo "Thread off: \$users_posts_uid $users_posts_uid \$verifyKey $verifyKey"; }
			
			$message = 'Notifications for this thread have been turned off.';
			
		} else {
			
			//not supposed to get here unless user is fiddling with URL strings
			$message = 'Error.';
			
		}
		
    
        $content=$message;
    
        return $this->pi_wrapInBaseClass($content);
    }
	
	/**
	Initialize values
	*/
	function init() {

		$this->db = $GLOBALS['TYPO3_DB'];
		
	}
	
	/**
	Main test function
	*/
	function test() {

		$this->testTurnOffGlobalNotificationForUser2();
		$this->testTurnOffThreadNotificationForUserPost2();
		
	}

	/**
	Test function
	*/
	function testTurnOffGlobalNotificationForUser() {
		
		$verifyKey = '6acf59396e185634a56a40c2571492fc'; 
		$this->turnOffGlobalNotificationForUser( 'testsavvy15', $verifyKey );

	}
	
	/**
	Test function. Expect failure.
	*/
	function testTurnOffGlobalNotificationForUser2() {
		
		$verifyKey = '5acf59396e185634a56a40c2571492fc'; 
		$this->turnOffGlobalNotificationForUser( 'testsavvy15', $verifyKey );

	}

	/**
	Test function
	*/
	function testTurnOffThreadNotificationForUserPost() {
		
		$users_posts_uid = 4; 
		$verifyKey = '6acf59396e185634a56a40c2571492fc'; 
		$this->turnOffThreadNotificationForUserPost( $users_posts_uid, $verifyKey );
		
	}

	/**
	Test function. Expect failure.
	*/
	function testTurnOffThreadNotificationForUserPost2() {
		
		$users_posts_uid = 4; 
		$verifyKey = '2acf59396e185634a56a40c2571492fc'; 
		$this->turnOffThreadNotificationForUserPost( $users_posts_uid, $verifyKey );
		
	}
	
	
	/**
	@param int the userID.  Table/field fe_users.uid.
	@param string verification key.  MD5 of a secret piece of information
		that a possible atacker would not be able to make up.
	*/
	function turnOffGlobalNotificationForUser( $username, $verifyKey ) {
		
/* Example
UPDATE fe_users AS users
SET tx_commentnotify_global_notify_enabled = 0
WHERE 1
AND username = 'testsavvy15'
AND md5('asdfasdtestsavvy15') = '2e19e51b021f3ed28955d2def4cf0d63'
LIMIT 1
*/

$sql = "
UPDATE fe_users AS users
SET tx_commentnotify_global_notify_enabled = 0
WHERE 1
AND username = '$username'
AND md5(concat(users.password, users.username)) = '$verifyKey'
LIMIT 1
"
;
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
		
		
	}
	
	/**
	@param int the userID.  Table/field fe_users.uid.
	@param string verification key.  MD5 of a secret piece of information
		that a possible atacker would not be able to make up.
	*/
	function turnOffThreadNotificationForUserPost( $users_posts_uid, $verifyKey ) {
/* Example query
UPDATE tx_commentnotify_users_posts AS users_posts
INNER JOIN fe_users AS users
ON users_posts.fe_userid = users.uid 
SET notifyenabled = 0
WHERE 1
AND users_posts.uid = 1
AND md5(concat('asdfasd', users.username)) = '2e19e51b021f3ed28955d2def4cf0d63'

*/

$sql = "
UPDATE tx_commentnotify_users_posts AS users_posts
INNER JOIN fe_users AS users
ON users_posts.fe_userid = users.uid 
SET notifyenabled = 0
WHERE 1
AND users_posts.uid = $users_posts_uid
AND md5(concat(users.password, users.username)) = '$verifyKey'
"
;
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
		
		
	}

	
    /**
     * The main method of the PlugIn
     *
     * @param    string        $content: The PlugIn content
     * @param    array        $conf: The PlugIn configuration
     * @return    The content that is displayed on the website
     */
    function main0($content,$conf)    {
        $this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj=1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
    
        $content='
            <strong>This is a few paragraphs:</strong><br />
            <p>This is line 1</p>
            <p>This is line 2</p>
    
            <h3>This is a form:</h3>
            <form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="POST">
                <input type="hidden" name="no_cache" value="1">
                <input type="text" name="'.$this->prefixId.'[input_field]" value="'.htmlspecialchars($this->piVars['input_field']).'">
                <input type="submit" name="'.$this->prefixId.'[submit_button]" value="'.htmlspecialchars($this->pi_getLL('submit_button_label')).'">
            </form>
            <br />
            <p>You can click here to '.$this->pi_linkToPage('get to this page again',$GLOBALS['TSFE']->id).'</p>
        ';
    
        return $this->pi_wrapInBaseClass($content);
    }
    
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comment_notify/pi2/class.tx_commentnotify_pi2.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comment_notify/pi2/class.tx_commentnotify_pi2.php']);
}

?>