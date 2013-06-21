<?php
/****************************************************************
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
 * Plugin 'Comment Notification' for the 'comment_notify' extension.
 Notifies posters/commenters when new comments are posted to 
 articles/messageboards.
 @author	Jaspreet Singh <->
 $Id: class.tx_commentnotify_pi1.php,v 1.1.1.1 2010/04/15 10:03:19 peimic.comprock Exp $
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/class.tx_srfeuserregister_pi1.php');

class tx_commentnotify_pi1 extends tslib_pibase {
	var $prefixId = 'tx_commentnotify_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_commentnotify_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'comment_notify';	// The extension key.
	/** Typoscript conf */
	var $conf;
	/** Typo Global db variable */
	var $db;
	/** Whether to output debug info */
	var $debug = true;
	/** The URI where users are sent to to turn off notifications. 
	Example: support/notifications.html
	*/
	var $notificationOptionsURI;
	
	/** The amount of time to wait after an event before a notification
	is to be sent for that event. In seconds. 8 hours default. */
	var $waittime = 28800;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		$this->db = $GLOBALS['TYPO3_DB'];
		$this->notificationOptionsURI = 'support/notifications.html';
	
		$content='Comment Notification.';
		
		set_time_limit(120);
		$this->waittime = empty($this->conf['waittime'])
			? 8*60*60
			: $this->conf['waittime'];
		if ($this->debug) echo " this->waittime: $this->waittime.";
		//$this->test();
		$this->notificationIteration();
		
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	
	/**
	
	@return rows array
	*/
	function getNotificationsListForNewsCommenters() {
		
/*
SELECT DISTINCT guestbook.uid_tt_news
, guestbook.fe_userid
, users.uid AS fe_users_uid
, users.email AS email
FROM tx_veguestbook_entries AS guestbook
INNER JOIN fe_users AS users 
ON guestbook.fe_userid  = users.uid
WHERE (unix_timestamp() - guestbook.tstamp) <= 8*60*60
*/	
	}
	
	/**
	Test function.
	*/
	function test() {
		
		//$this->testNotify();
		//$this->testQueue();
		$this->testNotificationIteration();
		//$this->testCommaSeparatedList2Array();
		//$this->testURL();
		//echo "hello";
	}
	
	
	/**
	Test function
	@param
	@return
	*/
	function testURL() {
		
		$pid=4;
		$params = array(
			"view" => "single_thread"
			,"cat_uid" => 1
			,"conf_uid" => 1
			,"thread_uid" => 42
			,"page" => 1
			);
		
		$params = array(
			"view" => "single_thread"
			,"cat_uid" => 1
			,"conf_uid" => 1
			,"thread_uid" => 42
			,"page" => 1
			);

			
			$url = htmlspecialchars($this->cObj->getTypoLink_URL($pid,$params)); // run it through special chars for XHTML compliancy
		
		echo "URL: " . $url;
		
	}
	
	
	/**
	Test function
	@param
	@return
	*/
	function testCommaSeparatedList2Array() {
		
		$list1		= '';
		$array1		= $this->commaSeparatedList2Array( $list1 );
		$list2		= '234@example.com';
		$array2		= $this->commaSeparatedList2Array( $list2 );
		$list3		= '234@example.com';
		$array3		= $this->commaSeparatedList2Array( $list3 );
		$list4		= '234@example.com, adsf34@example.com';
		$array4		= $this->commaSeparatedList2Array( $list4 );
		$list5		= ' 234@example.com, 534@example.com, lkja@example.com ';
		$array5		= $this->commaSeparatedList2Array( $list5 );
		
		print_r( $array1 ); echo "<br>\n";
		print_r( $array2 ); echo "<br>\n";
		print_r( $array3 ); echo "<br>\n";
		print_r( $array4 ); echo "<br>\n";
		print_r( $array5 ); echo "<br>\n";
	}
	
	
	/**
	Test function.
	*/
	function testNotify() {
		
		//sendNotification($to, $toName, $from, $fromName, $bcc, $url, $username, $threadNotificationOffUrl, $globalNotificationOffUrl);
		$baseURL = $this->conf['baseURL'];
		$url = rtrim($baseURL, '/') . 'your-blogs/';
		$username = 'testsavvy10';
		$users_posts_uid = 5;
		$secret = 'asdfasdf';
		
		print_r($this->conf); //DEBUG
		$threadNotificationOffUrl = $this->getThreadNotificationOffUrl($baseURL, $username, $secret, $users_posts_uid);
		$globalNotificationOffUrl = $this->getGlobalNotificationOffUrl($baseURL, $username, $secret);
		
		$this->sendNotification("osd_sd@solutiondevelopment.biz", "", 'savvymiss@savvymiss.com', 'Savvy Miss', "js@solutiondevelopment.biz", $url, $username, $threadNotificationOffUrl, $globalNotificationOffUrl);
		
	}

	/**
	Test function.
	*/
	function testQueue() {
		
		echo "function testQueue() {\n";
		$newsID = 639;
		$lastPosterFeID = 25;
		$what = 2;
		$this->queueNotificationsForPost($newsID, $lastPosterFeID, $what);
		
	}
	
	/**
	Test function.
	*/
	function testNotificationIteration() {
		
		echo "testNotificationIteration() {\n";
		
		
		$this->notificationIteration();
		
	}
	
	/**
	*/
	function notificationIteration() {
	echo "function notificationIteration() {"; //jsdebug	
		
		$rows = $this->notificationIteration_getRows();
		$starttime = time();
		echo 'count:'; echo count($rows); //jsdebug
		foreach ( $rows as $row ) {
			
			//make sure PHP doesn't time out
			set_time_limit(30);
			//notify each person.
			$this->notifyCommenter($row['email'], $row['username'], $row['newsID'], 2, $row['url'], $row['password'], $row['users_posts_uid']);
			//reset status so as to not send the email again
			$this->notifyCommenter_resetStatus($row['users_posts_uid'], $starttime);
		}
		
	}
	
	/**
	Notify a single commenter.
	*/
	function notifyCommenter( $email, $username, $postID, $what, $url, $secret, $users_posts_uid) {
		
		$baseURL = $this->conf['baseURL'];
		//$url = $this->postIdToUrl($baseURL, $postID, $what);
		$url = rtrim($baseURL, '/') . $url;
		$to = empty($this->conf['debugEmail']) ? $email : $this->conf['debugEmail']; 
	//	$to = empty($this->conf['debugEmail']) ? 'osd_sd@solutiondevelopment.biz' : $this->conf['debugEmail']; //DEBUG

		//print_r($this->conf); //DEBUG
		$toName = $username; //DEBUG 
		$threadNotificationOffUrl = $this->getThreadNotificationOffUrl($baseURL, $username, $secret, $users_posts_uid);
		$globalNotificationOffUrl = $this->getGlobalNotificationOffUrl($baseURL, $username, $secret);
		//$bcc = "osd_sd@solutiondevelopment.biz";
		$bcc = null;
		
		//If a debug email value is set,
		if (!empty($this->conf['debugEmail']) ) {
			//send email to each of the debug email addresses
			$toArray = $this->commaSeparatedList2Array($this->conf['debugEmail']);
			foreach ($toArray as $to) {
				$this->sendNotification($to, $toName, 'savvymiss@savvymiss.com', 'Savvy Miss', $bcc, $url, $username, $threadNotificationOffUrl, $globalNotificationOffUrl);
			}
		} else {
			//otherwise send to the real recipient
//			$to = 'osd_sd@solutiondevelopment.biz'; //jsdebug
			$this->sendNotification($to, $toName, 'savvymiss@savvymiss.com', 'Savvy Miss', $bcc, $url, $username, $threadNotificationOffUrl, $globalNotificationOffUrl);
		}
		
		
		
	}
	
	/**
	Converts a comma-separated list to a an array of strings.
	Starting index is 0. Strings are returned trimmed.
	@param string Comma-separated list
	@return Array of strings.
	*/
	function commaSeparatedList2Array($csl) {
		
		$comma = ',';
		$array = explode($comma, $csl);
		$trimmedArray = array();
		foreach ($array as $item) {
			$trimmedArray[] = trim($item);
		}
		reset($trimmedArray); 
		return $trimmedArray;
		
	}
	
	
	/**
	Reset status so as to not send the email again
	*/
	function notifyCommenter_resetStatus($usersPostsID, $starttime) {
		echo "notifyCommenter_resetStatus($usersPostsID, $starttime) {"; //jsdebug
			
		$time = time();
		//status of 1 means email sent.
		$sql0 = "
			UPDATE tx_commentnotify_users_posts
			SET `status` = 1
			, notificationtime = '$time'
			WHERE 1
			AND `status` = 0
			AND users_posts_id = '$usersPostsID'"
		;

	$sql = "
UPDATE `tx_commentnotify_notifications`
SET `notifystatus` = '1'
, notificationtime = unix_timestamp()
WHERE `notifystatus` =0
AND users_posts_id = '$usersPostsID'
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
	Convert a postID to a URL.
	TODO
	@return the URL
	*/
	function postIdToUrl($baseURL, $postID, $what) {
		$url = $baseURL . 'your-blogs/';
		$x = $postID;
		$y = $what;
		return $url;
	}
	
	/**
	Convert a postID to a URL.
	TODO
	@return the URL
	*/
	function postIdToUrl_News() {
		
		
		
		
	}
	
	/**
	Takes an arbitrary SQL statemetn and returns rows in the manner of
	Typo3 exec_SELECTgetRows().
	Borrowed from Typo3 code. Thanks Kasper.
	@param string the SQL
	@return rows
	*/
	function execSqlGetRows($sql) {
		
		$res = $this->db->sql_query($sql);
		
		$output = null;
		unset($output);
		if (!$this->db->sql_error())	{
			$output = array();

			if ($uidIndexField)	{
				while($tempRow = $this->db->sql_fetch_assoc($res))	{
					$output[$tempRow[$uidIndexField]] = $tempRow;
				}
			} else {
				while($output[] = $this->db->sql_fetch_assoc($res));
				array_pop($output);
			}
		}
		return $output;
		
	}
	
	/**
	Get the rows for notification
	@return rows
	*/
	function notificationIteration_getRows() {
		
		return array_merge( 
			$this->notificationIteration_getRowsNews(),
			$this->notificationIteration_getRowsMessageboard()
			)
		;
		
	}
	
	
	/**
	Get the rows for notification - News articles
	@return rows
	*/
	function notificationIteration_getRowsNews() {
		
		/* Example of query
		
SELECT DISTINCT
notifications.users_posts_id AS users_posts_uid
, min(notifications.eventtime) AS eventtime
, users_posts.fe_userid AS fe_userid
, users.email AS email
, users.username AS username
, users.password AS password
, news.tx_commentnotify_internal_url AS url
, (SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	) AS last_notification_time
FROM tx_commentnotify_notifications AS notifications
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
, tx_veguestbook_entries AS guestbook
, tt_news AS news
WHERE 1
AND notifications.users_posts_id = users_posts.uid
AND users_posts.fe_userid = users.uid
AND users_posts.postid = guestbook.uid_tt_news
AND guestbook.uid_tt_news = news.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1
AND notifications.notifystatus = 0
AND users_posts.what = 2
AND notifications.eventtime <= UNIX_TIMESTAMP()-8*60*60
AND UNIX_TIMESTAMP()-8*60*60 >
(SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	)
GROUP BY users_posts_id, users_posts.fe_userid

UNION

SELECT DISTINCT
notifications.users_posts_id AS users_posts_uid
, min(notifications.eventtime) AS eventtime
, users_posts.fe_userid AS fe_userid
, users.email AS email
, users.username AS username
, users.password AS password
, news.tx_commentnotify_internal_url AS url
, (SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	) AS last_notification_time
FROM tx_commentnotify_notifications AS notifications
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
, tt_news AS news
WHERE 1
AND notifications.users_posts_id = users_posts.uid
AND users_posts.fe_userid = users.uid
AND users_posts.postid = news.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1
AND notifications.notifystatus = 0
AND users_posts.what = 4
AND notifications.eventtime <= UNIX_TIMESTAMP()-8*60*60
AND UNIX_TIMESTAMP()-8*60*60 >
(SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	)
GROUP BY fe_userid

*/
	//$waittime = 8*60*60;
	$waittime = $this->waittime;
	$internalCommentStartAnchor = '#tx-guestbook-list-commentstart';
	//this is to force going to the first page of the article
	$firstPageParameter = '?First';
	$urlSuffixes = $firstPageParameter . $internalCommentStartAnchor; 

		
		$sql = "
			SELECT DISTINCT
			notifications.users_posts_id AS users_posts_uid
			, min(notifications.eventtime) AS eventtime
			, users_posts.fe_userid AS fe_userid
			, users.email AS email
			, users.username AS username
			, users.password AS password
			, CONCAT(news.tx_commentnotify_internal_url, '$urlSuffixes') AS url
			, (SELECT max(notificationtime)
				FROM tx_commentnotify_notifications AS notifications2
				WHERE notifications.users_posts_id = notifications2.users_posts_id
				) AS last_notification_time
			FROM tx_commentnotify_notifications AS notifications
			, tx_commentnotify_users_posts AS users_posts
			, fe_users AS users
			, tx_veguestbook_entries AS guestbook
			, tt_news AS news
			WHERE 1
			AND notifications.users_posts_id = users_posts.uid
			AND users_posts.fe_userid = users.uid
			AND users_posts.postid = guestbook.uid_tt_news
			AND guestbook.uid_tt_news = news.uid
			AND users.tx_commentnotify_global_notify_enabled = 1
			AND users_posts.notifyenabled = 1
			AND notifications.notifystatus = 0
			AND users_posts.what = 2
AND notifications.eventtime <= UNIX_TIMESTAMP()-$waittime
AND UNIX_TIMESTAMP()-$waittime >
(SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	)
			GROUP BY users_posts_id, fe_userid

UNION

SELECT DISTINCT
notifications.users_posts_id AS users_posts_uid
, min(notifications.eventtime) AS eventtime
, users_posts.fe_userid AS fe_userid
, users.email AS email
, users.username AS username
, users.password AS password
, CONCAT(news.tx_commentnotify_internal_url, '$internalCommentStartAnchor') AS url
, (SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	) AS last_notification_time
FROM tx_commentnotify_notifications AS notifications
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
, tt_news AS news
WHERE 1
AND notifications.users_posts_id = users_posts.uid
AND users_posts.fe_userid = users.uid
AND users_posts.postid = news.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1
AND notifications.notifystatus = 0
AND users_posts.what = 4
AND notifications.eventtime <= UNIX_TIMESTAMP()-$waittime
AND UNIX_TIMESTAMP()-$waittime >
(SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	)
GROUP BY fe_userid
			"
		;
		
		$rows =  $this->execSqlGetRows($sql);
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}
		if ($this->debug) {
			echo $sql;
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
	Get the rows for notification - Messageboard postings
	@return rows
	*/
	function notificationIteration_getRowsMessageboard() {
		
		/* Example of query
		

SELECT DISTINCT
notifications.users_posts_id AS users_posts_uid
, min(notifications.eventtime) AS eventtime
, users_posts.fe_userid AS fe_userid
, users.email AS email
, users.username AS username
, users.password AS password
, mbThreads.tx_commentnotify_internal_url AS url
, (SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	) AS last_notification_time
FROM tx_commentnotify_notifications AS notifications
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
, tx_chcforum_post AS mbPosts
, tx_chcforum_thread AS mbThreads
WHERE 1
AND notifications.users_posts_id = users_posts.uid
AND users_posts.fe_userid = users.uid
AND users_posts.postid = mbPosts.thread_id
AND mbPosts.thread_id = mbThreads.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1
AND notifications.notifystatus = 0
AND users_posts.what = 1
AND UNIX_TIMESTAMP()-8*60*60 >
(SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	)
GROUP BY fe_userid


		*/
		//$waittime = 8*60*60;
		$waittime = $this->waittime;
		
		$sql = "

SELECT DISTINCT
notifications.users_posts_id AS users_posts_uid
, min(notifications.eventtime) AS eventtime
, users_posts.fe_userid AS fe_userid
, users.email AS email
, users.username AS username
, users.password AS password
, mbThreads.tx_commentnotify_internal_url AS url
, (SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	) AS last_notification_time
FROM tx_commentnotify_notifications AS notifications
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
, tx_chcforum_post AS mbPosts
, tx_chcforum_thread AS mbThreads
WHERE 1
AND notifications.users_posts_id = users_posts.uid
AND users_posts.fe_userid = users.uid
AND users_posts.postid = mbPosts.thread_id
AND mbPosts.thread_id = mbThreads.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1
AND notifications.notifystatus = 0
AND users_posts.what = 1
AND notifications.eventtime <= UNIX_TIMESTAMP()-$waittime
AND UNIX_TIMESTAMP()-$waittime >
(SELECT max(notificationtime)
	FROM tx_commentnotify_notifications AS notifications2
	WHERE notifications.users_posts_id = notifications2.users_posts_id
	)
GROUP BY fe_userid

			"
		;
		
		$rows =  $this->execSqlGetRows($sql);
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}
		if ($this->debug) {
			echo $sql;
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
	Returns a URL, which, after the user clicks it, notifications will be turned off
	for one specific thread for that user.
	*/
	function getThreadNotificationOffUrl($baseURL, $username, $secret, $users_posts_uid) {
		//need to know which thread and user (ie., posts_user UID).
		//page which handles commentnotify_notifyoff.
		//action=threadoff
		//$password = 'asdfasd';
		$notificationOptionsURI = $this->notificationOptionsURI;
		$verify = MD5($secret . $username); 
		return $baseURL . "$notificationOptionsURI?action=threadoff&id=$users_posts_uid&verify=$verify";
	}
	
	/**
	Returns a URL, which, after the user clicks it, notifications will be turned off
	globally (i.e., for all threads) for that user..
	*/
	function getGlobalNotificationOffUrl($baseURL, $username, $secret) {
		//need to know the users fe_users UID
		//page which handles commentnotify_notifyoff.
		//action=globaloff
		//$secret = 'asdfasd';
		$notificationOptionsURI = $this->notificationOptionsURI;
		
		$verify = MD5($secret . $username); 
		return $baseURL . "$notificationOptionsURI?action=globaloff&username=$username&verify=$verify";
	}
	
	
	/**
	Returns an MD5 string that is used to verify a user.
	*/
	function getVerifyString() {
		
	}
	

	/**
	Returns the HTML e-mail notification template.
	*/
	function getNotificationEmailTemplateHTML() {
		
		return  "
<title>SavvyMiss.com: New comments</title>		
<p>Hi ###USERNAME###,<p>

<p>Savvy Members are talking about your posting. Click on the following link to hear what they are saying: <a href='###VIEW_COMMENTS_URL###'>###VIEW_COMMENTS_URL###</a></p>

<p>Have a good day!</p>
<p>Savvy Miss</p>
<p><br />If you no longer want to be notified when a member comments on this posting <a href='###GLOBAL_COMMENTS_NOTIFICATION_OFF_URL###'>Click Here</a>.
<p>If you no longer want to be notified of  any member comments, log into <a href='http://savvymiss.com/'>http://savvymiss.com/</a> and then visit <a href='http://savvymiss.com/company/sign-up.html'>http://savvymiss.com/company/sign-up.html</a> .  Uncheck 'Receive Email Notifications of Member Comments'.</p>
		";
		
	}
	
	/**
	Returns the plain e-mail notification template.
	*/
	function getNotificationEmailTemplatePlain() {
	
		return "
Hi ###USERNAME###,

Savvy Members are talking about your posting. Click on the following link to hear what they are saying: ###VIEW_COMMENTS_URL### 

Have a good day!
Savvy Miss

If you no longer want to be notified when a member comments on this posting click here: ###THREAD_COMMENTS_NOTIFICATION_OFF_URL###.

If you no longer want to be notified of  any member comments, log into http://savvymiss.com/ and then visit http://savvymiss.com/company/sign-up.html .  Uncheck 'Receive Email Notifications of Member Comments'.
";
		
	}
	
	/**
	Sends a single notification to recipient.
	*/
	function sendNotification($to, $toName, $from, $fromName, $bcc, $url, $username, $threadNotificationOffUrl, $globalNotificationOffUrl)
	{
		if ($this->debug) var_dump($url); //DEBUG
		if ($this->debug) { echo "Sending email to $toName ( $to ) \$bcc=$bcc"; }

		$markers['###VIEW_COMMENTS_URL###'	] = $url;
		$markers['###USERNAME###'			] = $username;
		$markers['###GLOBAL_COMMENTS_NOTIFICATION_OFF_URL###'] = $threadNotificationOffUrl;
		$markers['###THREAD_COMMENTS_NOTIFICATION_OFF_URL###'] = $globalNotificationOffUrl;

		$HTMLTemplate	= $this->getNotificationEmailTemplateHTML(); 
		$plainTemplate	= $this->getNotificationEmailTemplatePlain();
		
		$HTMLContent	=  $this->cObj->substituteMarkerArray($HTMLTemplate, $markers);
		$plainContent	= $this->cObj->substituteMarkerArray($plainTemplate, $markers);
		$recipient = "$toName<$to>";
		$dummy = '';
		$fromEmail = $from;
		//$fromName = ; //same as function parameter
		$replyTo = '';
		$fileAttachment = '';
		
		tx_srfeuserregister_pi1::sendHTMLMail(
			$HTMLContent
			, $plainContent
			, $recipient
			, $dummy
			, $fromEmail
			, $fromName
			, $replyTo = ''
			, $fileAttachment = ''
			);
		
		//also send to blind carbon copy addresses if set 
		if (!empty($bcc)) {
			
			//make scalar into an array if it's not already
			if (is_scalar($bcc)) {
				$bcc = array( $bcc );
			}
			
			//so we can just iterate over the array and send a bcc
			//to each
			foreach( $bcc as $bccItem ) {
				
				$recipient = $bcc;
				tx_srfeuserregister_pi1::sendHTMLMail(
					$HTMLContent
					, $plainContent
					, $recipient
					, $dummy
					, $fromEmail
					, $fromName
					, $replyTo = ''
					, $fileAttachment = ''
					);
				
			}
			
		}

	}


	/**
	Sends a single notification to recipient.
	*/
	function sendNotificationBcc($to, $toName, $from, $fromName, $bcc, $url, $username, $threadNotificationOffUrl, $globalNotificationOffUrl) {
		
	}

	
	/**
	Sends a single e-mail to recipient.
	*/
	function sendEmail() {
		
		
	}
	
	
	/**
	Perform all necessary processing required when a new News comment is posted.
	*/
	function processForNewsComment( $newsID, $lastPosterFeID, $url) {
		$this->saveNewsURL($newsID, $url );
		$what = 2;
		$this->queueNotificationsForPost($newsID, $lastPosterFeID, $what);
	}
	
	/**
	Save a URL associated with a news article.
	@return void
	*/
	function saveNewsURL($newsID, $url ) {
		
		if (empty($url)) { return; }
		
					
		$sql = 
			"UPDATE tt_news 
			SET tx_commentnotify_internal_url = '$url'
			WHERE uid='$newsID'
			LIMIT 1"
		;
		
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
	}
	
	
	/**
	Queue notifications.
	@param int The tt_news.uid value that corresponds to the article that the
		poster just commented on.
	@param int the front-end user (table fe_user) uid value for the last poster.
		The last poster is important because we don't want to notify somebody who 
		just posted. He already knows he just posted.
	@param int What we're operating on. 2 for blogs/news, and 1 for messageboards
	@return none
	*/
	function queueNotificationsForPost($newsID, $lastPosterFeID, $what) {
	//need to know for which post, for which user
	
		$this->queueNotificationsForPost_usersPosts($newsID, $lastPosterFeID, $what);
		$this->queueNotificationsForPost_notifications($newsID, $lastPosterFeID, $what);
	
	}
	


	/**
	Queue notifications.
	@param int The tt_news.uid value that corresponds to the article that the
		poster just commented on.
	@param int the front-end user (table fe_user) uid value for the last poster.
		The last poster is important because we don't want to notify somebody who 
		just posted. He already knows he just posted.
	@param int What we're operating on. 2 for blogs/news, and 1 for messageboards
	@return none
	*/
	function queueNotificationsForPost_usersPosts($newsID, $lastPosterFeID, $what) {
		//need to know for which post, for which user
		
		/* Example of query:	
INSERT INTO tx_commentnotify_users_posts (fe_userid,postid,what, crdate, tstamp)

SELECT DISTINCT guestbook.fe_userid
,guestbook.uid_tt_news
, 2
, unix_timestamp()
, unix_timestamp()
FROM tx_veguestbook_entries AS guestbook
INNER JOIN fe_users AS users 
ON guestbook.fe_userid  = users.uid
WHERE 1
AND uid_tt_news=639
AND guestbook.fe_userid != 25
AND users.tx_commentnotify_global_notify_enabled = 1

UNION

SELECT DISTINCT news.tx_newsfeedit_fe_cruser_id
,news.uid
, 4
, unix_timestamp()
, unix_timestamp()
FROM tt_news AS news
INNER JOIN fe_users AS users 
ON news.tx_newsfeedit_fe_cruser_id  = users.uid
WHERE 1
AND news.uid = 639
AND news.tx_newsfeedit_fe_cruser_id != 25
AND users.tx_commentnotify_global_notify_enabled = 1

ON DUPLICATE KEY UPDATE tstamp = unix_timestamp();

	*/
	
		$sql = "
			INSERT INTO tx_commentnotify_users_posts (fe_userid,postid,what, crdate, tstamp)
			
			SELECT DISTINCT guestbook.fe_userid
			,guestbook.uid_tt_news
			, '$what'
			, unix_timestamp()
			, unix_timestamp()
			FROM tx_veguestbook_entries AS guestbook
			INNER JOIN fe_users AS users 
			ON guestbook.fe_userid  = users.uid
			WHERE 1
			AND uid_tt_news='$newsID'
			AND guestbook.fe_userid != '$lastPosterFeID'
			AND users.tx_commentnotify_global_notify_enabled = 1
			
UNION

SELECT DISTINCT news.tx_newsfeedit_fe_cruser_id
,news.uid
, 4
, unix_timestamp()
, unix_timestamp()
FROM tt_news AS news
INNER JOIN fe_users AS users 
ON news.tx_newsfeedit_fe_cruser_id  = users.uid
WHERE 1
AND news.uid = '$newsID'
AND news.tx_newsfeedit_fe_cruser_id != '$lastPosterFeID'
AND users.tx_commentnotify_global_notify_enabled = 1

ON DUPLICATE KEY UPDATE tstamp = unix_timestamp()"
			;
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
	}

	/**
	Queue notifications.
	@param int The tt_news.uid value that corresponds to the article that the
		poster just commented on.
	@param int the front-end user (table fe_user) uid value for the last poster.
		The last poster is important because we don't want to notify somebody who 
		just posted. He already knows he just posted.
	@param int What we're operating on. 2 for blogs/news, and 1 for messageboards
	@return none
	*/
	function queueNotificationsForPost_notifications($newsID, $lastPosterFeID, $what) {
		//need to know for which post, for which user
		
		/* Example of query:	
INSERT INTO tx_commentnotify_notifications(crdate, tstamp, users_posts_id, eventtime)

SELECT DISTINCT
unix_timestamp()
,unix_timestamp()
, users_posts.uid
, guestbook.crdate
FROM tx_veguestbook_entries AS guestbook
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
WHERE 1
AND guestbook.uid_tt_news=639
AND guestbook.fe_userid != 25
AND guestbook.uid_tt_news = users_posts.postid
AND guestbook.fe_userid = users_posts.fe_userid
AND 2=users_posts.what 
AND guestbook.fe_userid = users.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1

UNION

SELECT DISTINCT
unix_timestamp()
,unix_timestamp()
, users_posts.uid
, news.crdate
FROM tt_news AS news
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
WHERE 1
AND news.uid=639
AND news.tx_newsfeedit_fe_cruser_id != 25
AND news.uid = users_posts.postid
AND news.tx_newsfeedit_fe_cruser_id  = users_posts.fe_userid
AND 4=users_posts.what
AND news.tx_newsfeedit_fe_cruser_id  = users.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1


		*/
	
		$sql = "
			INSERT INTO tx_commentnotify_notifications(crdate, tstamp, users_posts_id, eventtime)
			
			SELECT DISTINCT
			unix_timestamp()
			,unix_timestamp()
			, users_posts.uid
			, guestbook.crdate
			FROM tx_veguestbook_entries AS guestbook
			, tx_commentnotify_users_posts AS users_posts
			, fe_users AS users
			WHERE 1
			AND guestbook.uid_tt_news='$newsID'
			AND guestbook.fe_userid != '$lastPosterFeID'
			AND guestbook.uid_tt_news = users_posts.postid
			AND guestbook.fe_userid = users_posts.fe_userid
			AND '$what'=users_posts.what 
			AND guestbook.fe_userid = users.uid
			AND users.tx_commentnotify_global_notify_enabled = 1
			AND users_posts.notifyenabled = 1
			
UNION

SELECT DISTINCT
unix_timestamp()
,unix_timestamp()
, users_posts.uid
, news.crdate
FROM tt_news AS news
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
WHERE 1
AND news.uid='$newsID'
AND news.tx_newsfeedit_fe_cruser_id != '$lastPosterFeID'
AND news.uid = users_posts.postid
AND news.tx_newsfeedit_fe_cruser_id  = users_posts.fe_userid
AND 4=users_posts.what
AND news.tx_newsfeedit_fe_cruser_id  = users.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1

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
	Perform all necessary processing required when a new Messageboard comment
	is posted.
	TODO
	*/
	function processForMessageboardPost( $threadID, $lastPosterFeID, $url) {
		$this->saveMessageboardURL($threadID, $url );
		$what = 1;
		$this->queueNotificationsForMessageboard($threadID, $lastPosterFeID, $what);
	}
	
	/**
	Save a URL associated with a messageboard thread.
	Done.
	@return void
	*/
	function saveMessageboardURL($threadID, $url ) {
		
		if (empty($url)) { return; }
		
		
		$sql = 
			"UPDATE tx_chcforum_thread 
			SET tx_commentnotify_internal_url = '$url'
			WHERE uid='$threadID'
			LIMIT 1"
		;
		
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
	}
	
	
	/**
	Queue notifications.
	TODO
	@param int The tt_news.uid value that corresponds to the article that the
		poster just commented on.
	@param int the front-end user (table fe_user) uid value for the last poster.
		The last poster is important because we don't want to notify somebody who 
		just posted. He already knows he just posted.
	@param int What we're operating on. 2 for blogs/news, and 1 for messageboards
	@return none
	*/
	function queueNotificationsForMessageboard($threadID, $lastPosterFeID, $what) {
	//need to know for which post, for which user
	
		$this->queueNotificationsForMessageboard_usersPosts($threadID, $lastPosterFeID, $what);
		$this->queueNotificationsForMessageboard_notifications($threadID, $lastPosterFeID, $what);
	
	}
	


	/**
	Queue notifications.
	TODO
	@param int The tt_news.uid value that corresponds to the article that the
		poster just commented on.
	@param int the front-end user (table fe_user) uid value for the last poster.
		The last poster is important because we don't want to notify somebody who 
		just posted. He already knows he just posted.
	@param int What we're operating on. 2 for blogs/news, and 1 for messageboards
	@return none
	*/
	function queueNotificationsForMessageboard_usersPosts($threadID, $lastPosterFeID, $what) {
		//need to know for which post, for which user
		
		/* Example of query:	
		INSERT INTO tx_commentnotify_users_posts (fe_userid,postid,what, crdate, tstamp)
		
SELECT DISTINCT mbPosts.post_author
, mbPosts.thread_id
, 1
, unix_timestamp()
, unix_timestamp()
FROM tx_chcforum_post AS mbPosts
INNER JOIN fe_users AS users 
ON mbPosts.fe_userid  = users.uid
WHERE 1
AND mbPosts.thread_id = 42
AND mbPosts.post_author != 391
AND users.tx_commentnotify_global_notify_enabled = 1

ON DUPLICATE KEY UPDATE tstamp = unix_timestamp();
		*/
	
		$sql = "
			INSERT INTO tx_commentnotify_users_posts (fe_userid,postid,what, crdate, tstamp)
			

			SELECT DISTINCT mbPosts.post_author
			, mbPosts.thread_id
			, '$what'
			, unix_timestamp()
			, unix_timestamp()
			FROM tx_chcforum_post AS mbPosts
			INNER JOIN fe_users AS users 
			ON mbPosts.post_author  = users.uid
			WHERE 1
			AND mbPosts.thread_id = '$threadID'
			AND mbPosts.post_author != '$lastPosterFeID'
			AND users.tx_commentnotify_global_notify_enabled = 1
			
			
			ON DUPLICATE KEY UPDATE tstamp = unix_timestamp()"
			;
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
	}

	/**
	Queue notifications.
	TODO
	@param int The tt_news.uid value that corresponds to the article that the
		poster just commented on.
	@param int the front-end user (table fe_user) uid value for the last poster.
		The last poster is important because we don't want to notify somebody who 
		just posted. He already knows he just posted.
	@param int What we're operating on. 2 for blogs/news, and 1 for messageboards
	@return none
	*/
	function queueNotificationsForMessageboard_notifications($threadID, $lastPosterFeID, $what) {
		//need to know for which post, for which user
		
		/* Example of query:	
		INSERT INTO tx_commentnotify_notifications(crdate, tstamp, users_posts_id, eventtime)
		
SELECT DISTINCT
unix_timestamp()
,unix_timestamp()
, users_posts.uid
, mbPosts.crdate
FROM tx_chcforum_post AS mbPosts
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
WHERE 1
AND mbPosts.thread_id = 42
AND mbPosts.post_author != 391
AND mbPosts.thread_id = users_posts.postid
AND mbPosts.post_author = users_posts.fe_userid
AND 1=users_posts.what 
AND mbPosts.post_author = users.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1

		
		*/
	
		$sql = "
			INSERT INTO tx_commentnotify_notifications(crdate, tstamp, users_posts_id, eventtime)
			
SELECT DISTINCT
unix_timestamp()
,unix_timestamp()
, users_posts.uid
, mbPosts.crdate
FROM tx_chcforum_post AS mbPosts
, tx_commentnotify_users_posts AS users_posts
, fe_users AS users
WHERE 1
AND mbPosts.thread_id = '$threadID'
AND mbPosts.post_author != '$lastPosterFeID'
AND mbPosts.thread_id = users_posts.postid
AND mbPosts.post_author = users_posts.fe_userid
AND '$what'=users_posts.what 
AND mbPosts.post_author = users.uid
AND users.tx_commentnotify_global_notify_enabled = 1
AND users_posts.notifyenabled = 1

			
			"
			;
		$result = $this->db->sql_query( $sql );
		if ($this->debug) {
			echo $sql;
			echo '<br>Query result:/' ; var_dump($result); echo '/';
			echo '<br>Affected rows:' . mysql_affected_rows();
		}
		
	}
	
	
	function notification(){
		
	}
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main0($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comment_notify/pi1/class.tx_commentnotify_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comment_notify/pi1/class.tx_commentnotify_pi1.php']);
}

?>