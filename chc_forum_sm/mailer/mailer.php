<?php

        # TO DO:
        # write process log function
        # clear out the queue function
        # add tstamp to message sent

        # Set this variable correctly to reflect the relative path to localconf.php,
        # which is stored in the typo3conf folder. The default setting should suffice
        # if you have installed this extension locally rather than globally.
        $path_to_localconf = '../../../localconf.php';
        $path_to_t3lib_div = '../../../../t3lib/class.t3lib_div.php';

        # Uncomment this if you want to debug
        #$debug = 1;

        /**
        * DB Connection information.
        */
        require_once("$path_to_localconf");
        $db_name = $typo_db;
        $db_user = $typo_db_username;
        $db_pw = $typo_db_password;
        $db_host = $typo_db_host;
        $db_connection = mysql_connect($db_host, $db_user, $db_pw) or die (mysql_error());
        $db_select = mysql_select_db ($db_name) or die (mysql_error());

        /**
        * The following code represents the main action of this script.
        */

        // Check the log. It should be empty. If it isn't process its contents.
        checkLog();

        // Select all the posts from the posts table that haven't been sent out yet.
        $posts_not_sent = getMessages();

        // Begin a loop through each message
        if ($posts_not_sent) {
                foreach ($posts_not_sent as $message) {
                        // Insert each message in the mail queue and set the sent flag to true in the post table
                        addPostToQueue($message);
                }
        }

        // Time to send whatever is in the queue.
        $queued_not_sent = getQueue();

        // Begin a loop through each message in the queue
        if ($queued_not_sent) {
                // For each message
                foreach ($queued_not_sent as $message) {

												// set configuration for this message's forum.
                        if ($message['pid'] != $current_pid) {
																$fconf = getFconf($message['pid']);
                                if ($fconf['mailer_disable']['vDEF'] == 1) die;
																$forum_url = $fconf['mailer_forum_url']['vDEF'].'index.php?id='.$fconf['forum_instance_pid'];

												        $mail_from = $fconf['mailer_email']['vDEF'];
                                $current_pid = $message['pid'];                                
                        }

                        // Get the recipient list
                        $all_recipients = getRecipients($message);

                        // And build the message
                        $text = $message[post_text];
                        $subject = $message[post_subject];
                        $author = getAuthor($message[post_author]);
                        $author_email = $author[email];
                        $author_name = $author[name];
                        $day_time = getDayTime($message[post_tstamp]);
                        $this_message = makeMessage($text, $author_name, $author_email, $day_time, $name, $email, $message['conf_uid'], $message['thread_uid']);

                        // Update the log so that it contains all the users
                        if ($all_recipients) {
                                foreach ($all_recipients as $recipient) {
                                        $query = "INSERT INTO tx_chcforum_mail_log VALUES ('$recipient[uid]', '$message[uid]')";
                                        $query_results = @mysql_query($query);
                                        if ($debug) {
                                                print "<strong>Added to Log:</strong> The following query was run to insert the queue ID and recipient IDs into the Log<br>";
                                                print $query;
                                                print "<br><br>";
                                        }
                                }
                        }

                        // For each recipient, send the message.

                        if ($all_recipients) {
                                foreach ($all_recipients as $recipient) {
                                        $email = $recipient[email];
                                        $name = $recipient[name];
                                        if ($debug) {
                                                print "Message is being sent to <strong>$recipient[email]</strong><br><br>";
                                                print "<strong>The message is</strong>:<br>";
                                                print $this_message.'<br><br>';
                                        }
                                        // The line that actually sends the message!
                                        sendMessage($email, $subject, $this_message);

                                        // And delete the entry in the log
                                        $query = "DELETE FROM tx_chcforum_mail_log WHERE recipient_uid=$recipient[uid] AND message_uid=$message[uid]";
                                        $query_results = @mysql_query($query);
                                        if ($debug) {
                                                print "<strong>Deleted from Log:</strong> The following query was run to delete the queue ID and recipient IDs from the Log<br>";
                                                print $query;
                                                print "<br><br>";
                                        }
                                }
                        }
                      	$query1 = "UPDATE tx_chcforum_mail_queue SET sent_flag=1 WHERE uid=$message[uid]";
                        $query_results = @mysql_query($query1);
                }
        }


        /**
        * [Describe function...]
        *
        * @param [type]  $text: ...
        * @param [type]  $author_name: ...
        * @param [type]  $author_email: ...
        * @param [type]  $day_time: ...
        * @param [type]  $name: ...
        * @param [type]  $email: ...
        * @param [type]  $conf_uid: ...
        * @param [type]  $thread_uid: ...
        * @return [type]  ...
        */
        function makeMessage($text, $author_name, $author_email, $day_time, $name, $email, $conf_uid, $thread_uid) {
								global $fconf;

								$message = $fconf['mailer_msg_tmpl']['vDEF'];
                if(!$message) {
                	$message = "Posted by: {author_name}\nConference: {conference}\nThread: {thread}\n\n{text}\n\n\nThis message was sent because you have opted to receive new posts via email.\n{link}";       	
                }
                
                $conference = getConference($conf_uid);
                $thread = getThread($thread_uid);

                $link_conf[view] = 'single_thread';
                $link_conf[thread_uid] = $thread_uid;

                $link_conf[linktext] = 'please click here';
                $link = makeLink($link_conf);
								
								$author_name = htmlspecialchars_decode($author_name);
								$conference = htmlspecialchars_decode($conference);
								$thread = htmlspecialchars_decode($thread);
								$text = htmlspecialchars_decode($text);
								
								// escape brackets
								$author_name = str_replace('{','{/',$author_name);
								$author_name = str_replace('}','\}',$author_name);
								$conference =str_replace('{','{/',$conference);
								$conference = str_replace('}','\}',$conference);
								$thread = str_replace('{','{/',$thread);
								$thread = str_replace('}','\}',$thread);
								$text = str_replace('{','{/',$text);
								$text = str_replace('}','\}',$text);
								
								$message = str_replace('{author_name}',$author_name,$message);
								$message = str_replace('{conference}',$conference,$message);
								$message = str_replace('{thread}',$thread,$message);
								$message = str_replace('{text}',$text,$message);
								$message = str_replace('{link}',$link,$message);
								
								$message = str_replace('{/','{',$message);
								$message = str_replace('\}','}',$message);

                return $message;
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $link_conf: ...
        * @return [type]  ...
        */
        function makeLink($link_conf) {
                global $forum_url;

                $internal_content = '';

                if ($link_conf['view']) {
                        $add_view = '&view='.makeHash($link_conf['view']);
                } else {
                        $add_view = '';
                }

                if ($link_conf['thread_uid']) {
                        $add_uid .= '&thread_uid='.makeHash($link_conf['thread_uid']);
                }

                if ($link_conf['linktext']) {
                        $add_ltext = $link_conf['linktext'];
                }
                
                $flag = '&flag=last';

                $internal_content = $forum_url.'&no_cache=1'.$add_view.$add_uid.$flag;

                return $internal_content;
        }
			
			
			
				function htmlspecialchars_decode($value)	{
					$value = str_replace('&gt;','>',$value);
					$value = str_replace('&lt;','<',$value);
					$value = str_replace('&quot;','"',$value);
					$value = str_replace('&amp;','&',$value);
					return $value;
				}



        /**
        * [Describe function...]
        *
        * @param [type]  $id: ...
        * @return [type]  ...
        */
        function getConference($id) {
                $query = "SELECT conference_name FROM tx_chcforum_conference WHERE uid=$id";
                $query_results = @mysql_query($query);
                while ($name = @mysql_fetch_assoc($query_results)) {
                        $data_out = $name[conference_name];
                }
                return $data_out;
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $id: ...
        * @return [type]  ...
        */
        function getThread($id) {
                $query = "SELECT thread_subject FROM tx_chcforum_thread WHERE uid=$id";
                $query_results = @mysql_query($query);
                while ($name = @mysql_fetch_assoc($query_results)) {
                        $data_out = $name[thread_subject];
                }
                return $data_out;
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $email: ...
        * @param [type]  $subject: ...
        * @param [type]  $message: ...
        * @return [type]  ...
        */
        function sendMessage($email, $subject, $message) {
								global $mail_from;
                mail($email, $subject, $message,
                        "From: ".$mail_from."\r\n" . "Reply-To: $email\r\n" . 'X-Mailer: PHP/' . phpversion());
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $author_id: ...
        * @return [type]  ...
        */
        function getAuthor($author_id) {
                $query = "SELECT name,email FROM fe_users WHERE uid=$author_id";
                $query_results = @mysql_query($query);
                while ($user = @mysql_fetch_assoc($query_results)) {
                        $data_out = $user;
                }
                return $data_out;
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $tstamp: ...
        * @return [type]  ...
        */
        function getDayTime ($tstamp) {
                $date = strftime('%b %d %Y', $tstamp);
                return $date;
        }

				function getOneRecipient($feuser_uid) {
								$query = "SELECT uid,username,usergroup,name,email FROM fe_users WHERE uid=$feuser_uid";
								$query_results = @mysql_query($query);
								$user = @mysql_fetch_assoc($query_results);
								return $user;					
			  }


        /**
        * Get the recipients for a message in the queue
        *
        * @param [type]  $message: ...
        * @return [type]  ...
        */        
        function getRecipients($message) {
								$data_out = array();
								$uids = array();
                
                // get recipients who are watching this conference
                $query = "SELECT * from tx_chcforum_user_conf WHERE mailer_confs=$message[conf_uid]";
                $results = mysql_query($query);
                while ($uid = mysql_fetch_assoc($results)) {
                        $query = "SELECT uid,username,usergroup,name,email FROM fe_users WHERE uid=$uid[user_uid]";
                        $query_results = @mysql_query($query);
                        while ($user = @mysql_fetch_assoc($query_results)) {
                                if (confAuth($user[usergroup], $user[uid], $message[conf_uid]) == 'true') {
                                        $data_out[] = $user;
                                        $uids[] = $user['uid'];
                                }
                        }
                }
								
								// get recipients who are watching this thread
                $query = "SELECT * from tx_chcforum_user_thread WHERE mailer_threads=$message[thread_uid]";
                t3lib_div::debug($query);
                $results = mysql_query($query);
                while ($uid = mysql_fetch_assoc($results)) {
                	$query = "SELECT uid,username,usergroup,name,email FROM fe_users WHERE uid=$uid[user_uid]";
                  $query_results = @mysql_query($query);
                  while ($user = @mysql_fetch_assoc($query_results)) {
                  	if (confAuth($user[usergroup], $user[uid], $message[conf_uid]) == 'true') {
											if (!in_array($user['uid'],$uids)) $data_out[] = $user;
										}
									}
                }

                return $data_out;
        }

        /**
        * This function returns true if the logged in user can access the conference with the uid
        * of $conf_id. Returns false if user cannot access it -- we probably need to update this to
        * reflect the changes that have been made to category access -- this should include a check 
        * of whether or not the user can access the parent category. Not a major problem, but something
        * that should be fixed for the sake of consistency.
        *
        * @param [type]  $user_groups: ...
        * @param [type]  $user_uid: ...
        * @param [type]  $conf_uid: ...
        * @return [type]  ...
        */
        function confAuth($user_groups, $user_uid, $conf_uid) {
                if (!empty($user_uid)) {
                        $user_authenticated = 0;
                        $group_authenticatede = 0;
                        $success = 'false';
                        $query = "SELECT * FROM tx_chcforum_conference WHERE uid=$conf_uid";
                        $results = @mysql_query($query);
                        if (!empty($results)) {
                                while ($row = mysql_fetch_assoc($results)) {
                                        if (!empty($row['auth_forumgroup_r'])) {
                                                // Explode string containing the IDs of the forum groups that can view this conference
                                                $groups = explode(',', $row['auth_forumgroup_r']);
                                                // For each forumgroup attached to the conference, get the info for that forum
                                                foreach ($groups as $value) {
                                                        $query1 = "SELECT * FROM tx_chcforum_forumgroup WHERE uid=$value";
                                                        $results1 = @mysql_query($query1);
                                                        // For each forum group, check and see if this user belongs to it
                                                        while ($row1 = mysql_fetch_assoc($results1)) {
                                                                // Do the auth for each forum
                                                                // Explode string containing users and groups for this forum group
                                                                $auth_users = explode(',', $row1['forumgroup_users']);
                                                                $auth_groups = explode(',', $row1['forumgroup_groups']);
                                                                $user_groups = explode(',', $user_groups);
                                                                // Does this user's ID along to the list of user IDs allowed to access this forum (as per the FG)?
                                                                if (in_array($user_uid, $auth_users)) {
                                                                        $fg_user_authenticated = 1;
                                                                }
                                                                // Does this user belong to all the groups that belong to this forumgroup?
                                                                foreach ($auth_groups as $value) {
                                                                        // For each of the groups belonging to this forumgroup, see if it is contained in the user_groups array
                                                                        if (!in_array($value, $user_groups)) {
                                                                                $fg_group_authenticated = 0;
                                                                                break; // If the group isn't in the user_group array, stop this loop
                                                                        } else {
                                                                                // As long as the loop hasn't stopped, continue to authenticate
                                                                                $fg_group_authenticated = 1;
                                                                        }
                                                                }
                                                        }
                                                        // If, coming out of the authentication subroutine, the user is authenticated, set $auth to true. This value
                                                        // cannot be unset after this, since the user only needs to belong to one forum group to have access to the
                                                        // conference (although she must have access to all the groups within the forumgroup to be authenticated.
                                                        if ($fg_group_authenticated == 1 or $fg_user_authenticated == 1) {
                                                                $conf_auth = 1;
                                                        }
                                                }
                                                if ($conf_auth == 1) {
                                                        $success = 'true';
                                                }
                                        } else {
                                                $success = 'true';
                                        } // No user groups for the conference. Go ahead and authenticate
                                } // closes while loop
                        }
                } else {
                        $success = 'false';
                } // closes if loop
                return $success;
        } // closes function



        /**
        * Get all messages in the queue that have not been sent.
        *
        * @return [type]  ...
        */
        function getQueue() {
                $query = 'SELECT * FROM tx_chcforum_mail_queue WHERE sent_flag=0';
                $query_results = @mysql_query($query);
                while ($row = @mysql_fetch_assoc($query_results)) {
                        $data_array[] = $row;
                }
                return $data_array;
        }


        /**
        * Adds the message stored in $message to the mail queue, and sets the sent flag to true in the posts table. From this point on,
        * the message is in the hands of the mailer, and out of the domain of the forum proper.
        *
        * @param [type]  $message: ...
        * @return [type]  ...
        */
        function addPostToQueue($message) {
                $subject = addslashes($message['post_subject']);
                $text = addslashes($message['post_text']);
                $author = addslashes($message['post_author']);
                $pid = ($message['pid']);

                global $debug;
                $tstamp = time();
                $query = "INSERT INTO tx_chcforum_mail_queue
                        (uid,
                        pid,
                        conf_uid,
                        thread_uid,
                        post_uid,
                        post_author,
                        post_subject,
                        post_text,
                        post_tstamp,
                        tstamp, sent_flag)
                        VALUES (
                        '',
                        '$pid',
                        '$message[conference_id]',
                        '$message[thread_id]',
                        '$message[uid]',
                        '$author',
                        '$subject',
                        '$text',
                        '$message[tstamp]',
                        '$tstamp', '0')";
                if ($debug) {
                        print "<strong>addPostToQueue:</strong> Message is added to the queue with the following query:<br>";
                        print $query;
                        print "<br><br>";
                }
                $query_results = mysql_query($query);
                $query2 = "UPDATE tx_chcforum_post SET post_sent_flag=1 WHERE uid=$message[uid]";
                $query_results = @mysql_query($query2);

        }

        /**
        * Check the log to make sure it's empty. If it's not, process whatever is in it.
        *
        * @return [type]  ...
        */
        function checkLog() {
                $query = 'SELECT * FROM tx_chcforum_mail_log';
                $query_results = @mysql_query($query);
                while ($row = @mysql_fetch_assoc($query_results)) {
                        if ($row) {
                                processLogRow($row);
                        }
                }
        }
       
        /**
        * Check the log to make sure it's empty. If it's not, process whatever is in it.
        * 
        * @param array $log_data this array contains a row from the log table (user uid and message uid).
        * @return [type]  ...
        */
        function processLogRow($log_data) {
        			 // recip is the user receiving a message, and queue is the id of the queue entry being sent.
               $recip = $log_data['recipient_uid'];
               $queue = $log_data['message_uid'];
               $query = "SELECT * FROM tx_chcforum_mail_queue WHERE uid = $queue";
               $results = mysql_query($query);
               if ($results) $message = mysql_fetch_assoc($results);

               // build the message
               $text = $message[post_text];
               $subject = $message[post_subject];
               $author = getAuthor($message[post_author]);
               $author_email = $author[email];
               $author_name = $author[name];
               $day_time = getDayTime($message[post_tstamp]);
               $this_message = makeMessage($text, $author_name, $author_email, $day_time, $name, $email, $message['conf_uid'], $message['thread_uid']);
							 $this_recip = getOneRecipient($recip);
							 
							 // Get the email and name.
							 $email = $this_recipient['email'];
							 $name = $this_recipient['name'];
							 
							 // Send the message
							 sendMessage($email, $subject, $this_message);
            
            	 // Delete this line from the log
               $query = "DELETE FROM tx_chcforum_mail_log WHERE recipient_uid=$recip AND message_uid=$message[uid]";
               $query_results = @mysql_query($query); 
        }

        /**
        * [Describe function...]
        *
        * @return [type]  ...


        */
        function getMessages() {
                global $debug;
                $query = 'SELECT * FROM tx_chcforum_post WHERE post_sent_flag=0';
                $query_results = @mysql_query($query);
                while ($row = @mysql_fetch_assoc($query_results)) {
                        $data_array[] = $row;

                }
                if ($debug && $row) {
                        print "<br><br><strong>getMessages:</strong> Gets the information for message, puts it in array that looks like this:<br>";
                        print debug($data_array);
                        print "<br><br>";
                }
                return $data_array;
        }

        // Makes a hash out of string -- used to obscure variable data passed through URL.
        function makeHash($string) {
                $hash = base64_encode(serialize($string));
                #return $hash;
                return $string;
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $pid: ...
        * @return [type]  ...
        */
        function getFconf($pid) {

					global $path_to_t3lib_div;
	        require_once("$path_to_t3lib_div");

					// get the forum plugin record where starting pages value is the same
					// as the pid for this message
					$fields = 'tt_content.pid as forum_instance_pid,tt_content.pi_flexform AS flex';
					$tables = 'tt_content';
					$where = 'tt_content.list_type="chc_forum_pi1" AND tt_content.deleted=0 AND tt_content.pages='.$pid;
					$query = "SELECT ".$fields." FROM ".$tables." WHERE ".$where;
					$query_results = @mysql_query($query);
					$row = @mysql_fetch_assoc($query_results);
				
					// if starting point didn't return any records, look for general records
					// storage page.
					if (!$row) {
						$tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
						$where = 'tt_content.list_type="chc_forum_pi1" AND tt_content.deleted=0 AND pages.storage_pid='.$pid;
						$query = "SELECT ".$fields." FROM ".$tables." WHERE ".$where;
						$query_results = @mysql_query($query);
						// note: it is convievable that someone would set up 2 forums, both pointing
						// at the same storage folder. And it's possible that these forums will have
						// different settings. However, in this case, we'll just use the first one
						// that the mailer finds... I'm not sure how else to deal with this.
						$row = @mysql_fetch_assoc($query_results);
					}
					
					if ($row['flex']) $flex_arr = t3lib_div::xml2array($row['flex']);
					$flex_arr['data']['s_mailer']['lDEF']['forum_instance_pid'] = $row['forum_instance_pid'];
					return $flex_arr['data']['s_mailer']['lDEF'];
        }

        /**
        * [Describe function...]
        *
        * @param [type]  $$array: ...
        * @return [type]  ...
        */
        function mailer_debug(&$array) {
                if (!empty($array)) {
                        foreach($array as $key => $value) {
                                if (is_array($value)) {
                                        echo "<li>Array:<blockquote>";
                                        debug($value);
                                        echo "</blockquote>";
                                } elseif(is_object($value)) {
                                        echo "<li>Object:<blockquote>";
                                        debug($value);
                                        echo "</blockquote>";
                                } else {
                                        echo "<li>[" . $key . '] ' . $value;
                                }
                        }
                }
        }
?>