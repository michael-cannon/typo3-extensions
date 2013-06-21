<?php

	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2004 Zach Davis (zach@crito.org)
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
	* User class contains the attributes and methods for dealing with
	* in the chc_forum extension, including all of the extension's 
	* authentication methods.
	*
	* @author Zach Davis <zach@crito.org>
	*/

	class tx_chcforum_user extends tx_chcforum_pi1 {

		var $uid;
		var $pid;
		var $tstamp;
		var $username;
		var $password;
		var $usergroup;
		var $disable;
		var $starttime;
		var $endtime;
		var $name;
		var $address;
		var $telephone;
		var $fax;
		var $email;
		var $crdate;
		var $cruser_id;
		var $deleted;
		var $title;
		var $zip;
		var $city;
		var $country;
		var $www;
		var $company;
		var $image;
		var $fe_cruser_id;
		var $lastlogin;
		var $is_online;
		var $tx_chcforum_forum_admin;
		var $tx_chcforum_aim;
		var $tx_chcforum_yahoo;
		var $tx_chcforum_msn;
		var $cObj;

	 /**
		* We do NOT need to get everything for the user from the DB -- in fact, all we need is the
		* uid, the email address, and the group(s). fix this -- don't touch the DB on creating
		* the user object (that way you can create it often, for authentication, without touching
		* the DB each time and making extra overhead. Make a set_att(fieldname) method -- use this
		* to get username, etc rather than accessing it directly.
		* You also need to tone down the author object -- it's pulling up way to much information.
		* User object constructor. Pulls the current users ID# and their usergroup(s).
		*
		* @param object $cObj: the cObj that gets passed to every constructor.
		* @return void
		*/
		function tx_chcforum_user ($cObj) {

			$this->cObj = $cObj; 
			$this->conf = $this->cObj->conf;

			// added for nested frontend groups support
			$this->groupData = $GLOBALS['TSFE']->fe_user->groupData;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;
			#if (intval(phpversion()) < 5) unset($this->cObj->fconf);			 

			$this->internal['results_at_a_time'] = 1000;
			$this->uid = $GLOBALS['TSFE']->fe_user->user['uid'];
			$this->groups = $GLOBALS['TSFE']->fe_user->user['usergroup'];
			if ($this->uid) {
				$row_array = $this->pi_getRecord('fe_users', $this->uid, 1);
				foreach ($row_array as $attr => $value) {
					$this->$attr = $value;
				}
			}	

			// handle new posts tracking
			// we have a logged in user. Get the session conf and the user conf.
			if ($GLOBALS["TSFE"]->loginUser) {
				$pid = $this->conf['pidList'];
				
				$this->chcForumSesConf = $GLOBALS["TSFE"]->fe_user->getKey("ses","chcForumSesConf");
				$this->chcForumUsrConf = $GLOBALS["TSFE"]->fe_user->getKey("user","chcForumUsrConf");
/*
print 'session conf<br/>';
t3lib_div::debug($this->chcForumSesConf);
print '<br/>';
print 'user conf<br/>';
t3lib_div::debug($this->chcForumUsrConf);
print '<br/>';
print '<br/>';
print '<br/>';
*/
			} 
			// if we have a logged in user, and there is no last_visit value in the session
			// conf, set it to whatever the last visit value is in the user conf. We should
			// also delete and posts read in the posts_read table at this point.
			if ($GLOBALS["TSFE"]->loginUser && !$this->chcForumSesConf[$pid]['last_visit']) {
				// if we don't have a value for the last visit, get it from the user
				// conf and set the session to that value.
				$this->chcForumSesConf[$pid]['last_visit'] = $this->chcForumUsrConf[$pid]['this_visit'];

				$GLOBALS["TSFE"]->fe_user->setKey("ses","chcForumSesConf", $this->chcForumSesConf);

				$table = 'tx_chcforum_posts_read';
				$where = 'feuser_uid='.$this->uid;
				$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			}
			// set this_visit on every page load.
			if ($GLOBALS["TSFE"]->loginUser) {
				$this->setThisVisit();
			}

			$this->last_visit = intval($this->chcForumSesConf[$pid]['last_visit']);
/*
print 'session conf<br/>';
t3lib_div::debug($this->chcForumSesConf);
print '<br/>';
print 'user conf<br/>';
t3lib_div::debug($this->chcForumUsrConf);
print '<br/>';
*/
		}
		

		function setThisVisit() {
			$pid = $this->conf['pidList'];
			$this_visit = time();
			$this->chcForumUsrConf[$pid]['this_visit'] = $this_visit;
			$GLOBALS["TSFE"]->fe_user->setKey("user","chcForumUsrConf", $this->chcForumUsrConf);			
		}

		function rate_post($rateSelect,$ratePostUID) {
			if ($this->fconf['allow_rating'] == true)  {
				$ip = t3lib_div::getIndpEnv('REMOTE_ADDR');
				// has the user rated this post before?
				$table = 'tx_chcforum_ratings';
				$fields = 'rating';
				if ($this->uid) {
					$addWhere = 'post_uid='.$ratePostUID.' AND rater_uid='.$this->uid;				
				} else {
					$addWhere = 'post_uid='.$ratePostUID.' AND rater_ip="'.$ip.'"';
				}
				$where = $addWhere;
				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($results) == 0) {
					// no rating by this user, go ahead and add it.
					$dataArr['post_uid'] = $ratePostUID;
					$dataArr['rater_ip'] = $ip;
					$dataArr['rater_uid'] = $this->uid;
					$dataArr['rating'] = $rateSelect;
					$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$dataArr);
				}			
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
			}
		}

		/**
		* set a list of all threads that are closed.
		*/
		function set_closed_threads() {

			$closed = array();
			$table = 'tx_chcforum_thread';
			$fields = 'uid';
			$addWhere = 'thread_closed = 1';
			$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				$closed[] = $row['uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($results);
			$this->closed_threads = $closed;			
		}

		/**
		* Output the profile form. This output is composed of a series of message objects, more or less.
		*
		* @return string  html for profile form.
		*/
		function profile_form ($cwt_version = false) {
			if ($this->fconf['disable_profile'] == true) return;

			// rg: changed to add external GETvars to the form url
			// used for forum / ttnews integration. Thanks Rupi!
			if ($this->conf['chcAddParams']) {
    			$paramArray = tx_chcforum_shared::getAddParams($this->conf['chcAddParams']);
			}

			$out .= '<form action="'.htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',$paramArray)).'" enctype="multipart/form-data" name="profileform" id="profileform" method="post">';

			$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
			if ($cwt_version == true) { // one message for cwt, one for default
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_form_forum_hdr'), 'profile_hdr_big');
			} else {
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_form_hdr'), 'profile_hdr_big');
			}
			$out .= $message->display();

			$out .= '<div class="tx-chcforum-pi1-profileBorder">';
			if ($this->fconf['mailer_disable'] != true) {

				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_form_email'), 'profile_hdr');
				$out .= $message->display();
				$out .= tx_chcforum_shared::lang('profile_email_msg').'<br /><br />';
				$confs = $this->get_viewable_confs();
				if ($confs) {
					// output conf checkboxes
					$selected_confs = $this->get_conf_prefs();
					foreach ($confs as $a_conf) {
						if (is_array($selected_confs) && in_array($a_conf[uid], $selected_confs)) {
							$out .= '<input type="checkbox" checked="checked" name="profile_c[mailer_confs][]" value="'.$a_conf['uid'].'" /> '.$a_conf['conference_name'].'<br />';
						} else {
							$out .= '<input type="checkbox" name="profile_c[mailer_confs][]" value="'.$a_conf['uid'].'" /> '.$a_conf['conference_name'].'<br />';
						}
					}
				}
				$out .= '<br />';
				$content = true;
			}

			if ($this->fconf['disable_img'] != true and $this->fconf['disable_img_edit'] != true) {
				if ($cwt_version == false) {
					$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_form_img'), 'profile_hdr');
					$out .= $message->display();
					$out .= tx_chcforum_shared::lang('profile_img_msg').'<br /><br />';
					$out .= '<input name="profile" type="file" /><br />';
					$out .= '<br />';
					$content = true;
				}
			}

			if ($this->fconf['disable_yahoo'] != true or $this->fconf['disable_aim'] != true or $this->fconf['disable_msn'] != true or $this->fconf['custom_im']) {
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_form_im'), 'profile_hdr');
				$out .= $message->display();
				$out .= tx_chcforum_shared::lang('profile_im_msg').'<br />';
				$out .= '<table border="0" cellspacing="0" cellpadding="0">';
				if ($this->fconf['disable_yahoo'] != true) $out .= '<tr><td>Yahoo:</td><td><input type="text" name="profile[yahoo]" value="'.$this->tx_chcforum_yahoo.'" /><br /></td></tr>';
				if ($this->fconf['disable_aim'] != true) $out .= '<tr><td>AIM:</td><td><input type="text" name="profile[aim]" value="'.$this->tx_chcforum_aim.'" /><br /></td></tr>';
				if ($this->fconf['disable_msn'] != true) $out .= '<tr><td>MSN:</td><td><input type="text" name="profile[msn]" value="'.$this->tx_chcforum_msn.'" /><br /></td></tr>';
				if ($this->fconf['custom_im']) $out .= '<tr><td>'.$this->fconf['custom_im'].':</td><td><input type="text" name="profile[customim]" value="'.$this->tx_chcforum_customim.'" /><br /></td></tr>';
				$out .= '</table><br />';
				$content = true;
			}

			if ($this->fconf['disable_email'] != true) {			 
				if ($cwt_version == false) {
					$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_emailaddr'), 'profile_hdr');
					$out .= $message->display();
					$out .= tx_chcforum_shared::lang('profile_emailaddr_msg').'<br /><br />';
					$out .= tx_chcforum_shared::lang('profile_emailaddr').'<input size="50" type="text" name="profile[email]" value="'.$this->email.'" /><br /><br />';
					$content = true;
				}
			}

			if ($this->fconf['disable_website'] != true) {
				if ($cwt_version == false) {
					$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_website'), 'profile_hdr');
					$out .= $message->display();
					$out .= tx_chcforum_shared::lang('profile_website_msg').'<br /><br />';
					$out .= tx_chcforum_shared::lang('profile_website_lbl').' <input size="50" type="text" name="profile[www]" value="'.$this->www.'" /><br /><br />';
					$content = true;
				}
			}

			// if there weren't any fields available, return nothing
			if ($content != true) return;

			if ($cwt_version == false) {
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_submit'), 'profile_hdr');
				$out .= $message->display();
			}
			$out .= '<input type="hidden" name="view" value="profile" />';
			$out .= '<input type="hidden" name="author" value="'.tx_chcforum_shared::encode($this->uid).'" />';
			$out .= '<input type="hidden" name="profile_h[author]" value="'.$this->uid.'" />';
			$out .= '<input type="hidden" name="profile_h[submit]" value="submit" />';
			$out .= '<input type="submit" name="submit" value="'.tx_chcforum_shared::lang('profile_submit').'" />';
			$out .= '</form>';
			$out .= '</div><br />';
			return $out;
		}

	// mark all messages read
	function mark_read() {		
			if ($GLOBALS["TSFE"]->loginUser) {
				$pid = $this->conf['pidList'];
				$this->chcForumSesConf[$pid]['last_visit'] = time();
				$GLOBALS["TSFE"]->fe_user->setKey("ses","chcForumSesConf", $this->chcForumSesConf);
				$table = 'tx_chcforum_posts_read';
				$where = 'feuser_uid='.$this->uid;
				$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
				$this->last_visit = intval($this->chcForumSesConf[$pid]['last_visit']);
			}
	}	

	function mark_thread_read($thread_uid) {
		$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
		$thread = new $tx_chcforum_thread($thread_uid, $this->cObj);
		$post_uids = $thread->return_all_post_ids();
		if (is_array($post_uids)) {
			foreach ($post_uids as $post_uid) {
				$this->check_new($post_uid);
			}
		}
	}

	 /**
	  * Processes the profile form submission.
	  * Note: $pv (post vars) and $pf (post files) gets passed via the display object.
		*
		* @param array $pv: post variables gets passed in via the display object (from pi1)
		* @param array $pf: post file information gets passed via the display object (from pi1)
		* @param integer $max_user_imag: if this has been set in fconf, it gets passed from the display object. Otherwise it defaults to 102400 -- it's the max size, in bytes, allowed for user image uploads. 
		* @return void
		*/
		function process_profile_form ($pv, $pf, $max_user_img = 102400) {			
			$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
			if ($pv['submit'] && $pv['author'] == $this->uid) {
				// make sure the author we're viewing is this user. Confirm submit.
				// translate max_user_img from kb to b
				if ($max_user_img != 102400) $max_user_img = $max_user_img * 1024;

				// Validate the conf mailer prefs. -- [1] checks whether the value is numeric and [2] checks whether
				// this conference can actually be viewed by the user.
				if ($pv['mailer_confs']) {
					foreach ($pv[mailer_confs] as $conf_uid) {
						if (is_numeric($conf_uid)) $fconf[1] = true;
						if ($this->can_read_conf($conf_uid) == true) $fconf[2] = true;
						if ($fconf[1] != true or $fconf[2] != true) $invalid = true; // ok, so the fconf data is valid.	 
					}
					if ($invalid != true) $fconf_valid = true;
				} else {
					// mailer confs is empty -- still need to process...
					$fconf_valid = true;
				}

				// Validate the data that goes in the FEUSER record
				// Validate the user image
				if ($pf['profile']['name'] && $pf['profile']['type'] && $pf['profile']['tmp_name'] && $pf['profile']['error'] == 0 && $pf['profile']['size']) {
					if (t3lib_div::validPathStr(t3lib_div::fixWindowsFilePath($pf['profile']['tmp_name'])) && $pf['profile']['size'] <= $max_user_img) $img[0] = true;
					//$error = 'size'; // check file size and name -- finish adding these errors later
					if (t3lib_div::validPathStr(t3lib_div::fixWindowsFilePath($pf['profile']['tmp_name'])) && is_uploaded_file($pf['profile']['tmp_name'])) $img[1] = true;
					//$error = 'exist';// check if file exists
					if ($pf['profile']['type'] == "image/gif" OR $pf['profile']['type'] == "image/pjpeg" OR $pf['profile']['type'] == 'image/jpeg') $img[2] = true;
					//$error = 'mime';
					$extension = strtolower(substr(strrchr($pf['profile']['name'], '.'), 1));
					if (t3lib_div::inList('gif,jpeg,jpg', $extension) == true) $img[3] = true;
				}

				if (($img[0] == true) && ($img[1] == true) && ($img[2] == true) && ($img[3] == true)) $img_valid = true;

				// Start the queries...
				if ($this->fconf['mailer_disable'] != true && $fconf_valid == true) {
					// Update the conf mailer prefs
					$forum_pid = $this->cObj->conf['pidList'];
					$where = "user_uid=$this->uid AND forum_uid=$forum_pid";
					$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_user_conf', $where); // delete all rows for this user

					if ($pv['mailer_confs']) {
						foreach ($pv[mailer_confs] as $conf_uid) {
							$fconf_data_arr = array ('user_uid' => $this->uid,
								'mailer_confs' => $conf_uid,
								'forum_uid' => $this->cObj->conf['pidList']);
							$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_chcforum_user_conf', $fconf_data_arr);
							$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_user_conf', "user_uid=$this->uid AND NOT forum_uid AND mailer_confs=$conf_uid"); // delete all rows for this user
						}
					}
				}

				// if there's an image -- handle it.
				if ($img_valid == true) {
					// check for alternate image field.
					if ($this->fconf['alt_img_field']) {
						$field = $this->fconf['alt_img_field'];
					} else {
						$field = 'image';					
					}

					// load the TCA.
					t3lib_div::loadTCA("fe_users");

					// check for custom image path
					if ($this->fconf['alt_img_path']) {
						$img_path = $this->fconf['alt_img_path'];
					} else {
						$img_path = $GLOBALS['TCA']['fe_users']['columns'][$field]['config']['uploadfolder'];
					}

					// check to make sure the file exists -- thanks to Urs for the fix.
					if (is_file('./'.$img_path.'/'.$this->$field)) {
						// if this user already has an image, we need to delete the file.
						unlink('./'.$img_path.'/'.$this->$field);
					}

					// Update the Img file
					$hashed_name = md5($this->name.$this->uid).'.'.substr(strrchr($pf['profile']['name'], '.'), 1);
					$rel_path = $img_path.'/'.$hashed_name;
					$uploadfile = t3lib_div::getFileAbsFileName($rel_path);
					$tmp_file = t3lib_div::upload_to_tempfile($pf['profile']['tmp_name']);
					t3lib_div::upload_copy_move($tmp_file, $uploadfile);
					$feuser_data_arr = array ($field => $hashed_name);
					if ($tmp_file) t3lib_div::unlink_tempfile($tmp_file);
					} else {
					if ($pf['profile']['tmp_name']) unlink($pf['profile']['tmp_name']);
					}

				$feuser_data_arr['tx_chcforum_yahoo'] = $pv['yahoo'];
				$feuser_data_arr['tx_chcforum_aim'] = $pv['aim'];
				$feuser_data_arr['tx_chcforum_msn'] = $pv['msn'];
				$feuser_data_arr['tx_chcforum_customim'] = $pv['customim'];

				// validate website
				if (preg_match('#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $pv['www']) || !$pv['www']) {
					$feuser_data_arr['www'] = $pv['www'];
				} else {
					$error .= '<br />'.tx_chcforum_shared::lang('profile_invalid_www');
				}

				// validate email
				if (t3lib_div::validEmail($pv['email']) == true || !$pv['email']) {
					$feuser_data_arr['email'] = $pv['email'];
				} else {
					$error .= '<br />'.tx_chcforum_shared::lang('profile_invalid_email');
				}

				if ($this->fconf['disable_email'] == true)	unset($feuser_data_arr['email']);
				if ($this->fconf['disable_website'] == true)	unset($feuser_data_arr['www']);
				if ($this->fconf['disable_msn'] == true)	unset($feuser_data_arr['tx_chcforum_msn']);
				if ($this->fconf['disable_yahoo'] == true)	unset($feuser_data_arr['tx_chcforum_yahoo']);
				if ($this->fconf['disable_aim'] == true)	unset($feuser_data_arr['tx_chcforum_aim']);

				// if cwt community is being used, we want to remove www and email
				// from the data array, since they're handled in the cwt form.
				if ($this->conf['cwtCommunityIntegrated'] == true) {
					unset($feuser_data_arr['www']);
					unset($feuser_data_arr['email']);
				}

				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', "uid=$this->uid", $feuser_data_arr);

				if ($error) {
					$this->success_msg = '<span class="tx-chcforum-pi1-forumTextBig">'.tx_chcforum_shared::lang('profile_fail').'</span>'.$error;
				} else {
					$this->success_msg = '<span class="tx-chcforum-pi1-forumTextBig">'.tx_chcforum_shared::lang('profile_success').'</span>';
				}

				$message = new $tx_chcforum_message($this->cObj, $this->success_msg, 'link');
				$out .= $message->display();				
				RETURN $out;				
			}
		}


		/**
		* Get a list of confs that this user can view.
		*
		* @return array  conferences that this user have permission to view.
		*/
		function get_viewable_confs ($ids_only = false) {
			if ($ids_only == false) {
				$fields = '*';
			} else {
				$fields = 'uid';
			}
			$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'tx_chcforum_conference', 'deleted=0');
			if ($qresults) {
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
					if ($this->can_read_conf($row['uid']) == true) {
						$viewable_confs[] = $row;
					}
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($qresults);
			return $viewable_confs;
		}


		/**
		* Sets the posts read attribute for this user
		*
		* @return void
		*/
    function set_posts_read () {
        if ($this->uid) {
            // the following 6 lines haven't changed since the old version -- I've
            // just added new code begining with the $count variable.
            $query = "SELECT post_uid FROM tx_chcforum_posts_read WHERE feuser_uid=$this->uid";
            $results = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
            if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));
            while ($row = mysql_fetch_assoc($results)) {
                $posts_read_rows[] = $row['post_uid'];
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($results);
            $count = count($posts_read_rows);
            if ($count > 1) {
                // There shouldn't ever really be more than one row per user in this table.
                // If there is, odds are it's because the data in the table is left over
                // from an old version of the forum (in older versions, we stored date for
                // one read post on each row, rather than in a serialized array. The
                // following lines take all this data and condense it into a serialized
                // array and stick it back in.

                // first, delete all the rows in the post table where uid = $this->uid
                $posts_read = $posts_read_rows;
                $table = 'tx_chcforum_posts_read';
                $where = 'feuser_uid='.$this->uid;
                $GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);

                // then, add the new posts read array
                $data_arr['post_uid'] = serialize($posts_read);
                $data_arr['feuser_uid'] = $this->uid;
                $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$data_arr);
            } elseif ($count < 1) {
                $this->posts_read = array();
            }    elseif ($count == 1) {
                $posts_read = unserialize($posts_read_rows[0]);                    
            }
        }
        $this->posts_read = $posts_read;
        if (!is_array($this->posts_read)) $this->posts_read = array();
    }

		function get_all_viewable_threads() {
			$confs = $this->get_viewable_confs(true);
			if (is_array($confs)) {
				foreach ($confs as $v) {
					if (!$tx_chcforum_conference) $tx_chcforum_conference = t3lib_div::makeInstanceClassName("tx_chcforum_conference");
					$a_conf = new $tx_chcforum_conference($v[uid], $this->cObj);
					$threads = $a_conf->return_all_thread_ids();
					if (is_array($threads)) {
						foreach ($threads as $k => $v) {
							$all_threads[] = $v;
						}
					}
				}
			}
			return $all_threads;
		}
				
		function return_all_new_post_uids() {
			if ($this->uid) {
				$confs = $this->get_viewable_confs(true);
				$all_ids = array();
				if(is_array($confs)) {
					foreach ($confs as $v) {
						$uid = $v[uid];
						$this->current_conf_id = $uid;
						if (!$this->posts_read) $this->set_posts_read();
						$ids = $this->return_new_in_conf(true);
						if (!is_array($ids)) $ids = array();
						if (!is_array($all_ids)) $all_ids = array();
						$all_ids = array_merge($ids,$all_ids);
					}
				}
			}
			return($all_ids);
		}

		// counts the number of new messages
		function count_all_new() {
			if ($this->uid) {
				$count = 0;
				$confs = $this->get_viewable_confs(true);
				if(is_array($confs)) {
					foreach ($confs as $v) {
						$uid = $v[uid];
						$count = $count + $this->check_new($uid,'conf');
					}
				}
				return $count;
			}
		}

		/**
		* Checks for new posts in a conference, thread, or post. Returns count.
		*
		* @param integer  $id: uid for the conf, thrd, or post
		* @param string  $kind: what kind of uid is this? either conf, thread, or post.
		* @return integer  returns the count of new posts.
		*/
		function check_new ($id, $kind = 'post') {
			if ($id) {
				switch ($kind) {
					case 'conf':
						$this->current_conf_id = $id;
						if (!$this->posts_read) $this->set_posts_read();
						$count = $this->count_new_in_conf();
					break;

					case 'thread':
						$this->current_thread_id = $id;
						if (!$this->posts_read) $this->set_posts_read();
						$count = $this->count_new_in_thread();
					break;

					case 'post':
					default:
						$this->current_post_id = $id;
						if (!$this->posts_read) $this->set_posts_read();
						if ($this->is_new_post() == true) $this->add_post_read();
					break;
				}
			}

			return $count;
		}

		/**
		* Checks if current_post_id attribute represents a new (not read) post.
		*
		* @return boolean  true if it's new, false if it's not.
		*/
		function is_new_post () {
			if (!$this->posts_read) $this->set_posts_read();
				if (!empty($this->posts_read) && is_array($this->posts_read) && in_array($this->current_post_id, $this->posts_read)) {
				return false;
			} else {
				return true;
			}
		}

		/**
		* Counts new posts in a thread.
		*
		* @return integer  returns the count of new threads or, if there aren't any, returns false.
		*/
		function count_new_in_thread () {
			if (!$this->posts_read) $this->set_posts_read();
			if (isset($this->current_thread_id) && $this->uid) {
				$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
				$thread = new $tx_chcforum_thread($this->current_thread_id, $this->cObj);
				$posts_since_last_visit = $thread->return_all_post_ids_since($this->last_visit);
				if (is_array($posts_since_last_visit) && is_array($this->posts_read)) {
					$unread = array_diff($posts_since_last_visit, $this->posts_read);
				} 
				return count($unread);
			} else {
				return false;
			}
		}

		/**
		* Returns the count of new posts in a conference
		*
		* @return integer  returns count if there are any new posts. If none, returns false.
		*/
		function count_new_in_conf() {
			$total = 0;
			if (isset($this->current_conf_id) && $this->uid) {
				$tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
				$conf = new $tx_chcforum_conference($this->current_conf_id, $this->cObj);
				$posts_since_last_visit = $conf->return_post_ids_since($this->last_visit);
			}
			if (!is_array($this->posts_read)) $this->posts_read = array();
			if (is_array($posts_since_last_visit)) {
				$total = count(array_diff($posts_since_last_visit,$this->posts_read));
				#t3lib_div::debug(array_diff($posts_since_last_visit,$this->posts_read));
			}
			return $total;
		}


 		function get_threads_with_new() {
			$new_posts = $this->return_all_new_post_uids();
			if (is_array($new_posts)) {
				foreach ($new_posts as $k => $v) {
					$threads[] = $v['thread_uid'];
				}
			}
			if (is_array($threads)) $tmp = array_unique($threads);
			if (is_array($tmp)) $thread_ids = array_values($tmp);
			return $thread_ids;
		}
				
		// returns post ids by default. Set threads to true to return threads with new posts
		function return_new_in_conf($include_threads = false) {
			$total = 0;
			if (isset($this->current_conf_id) && $this->uid) {
				$tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
				$conf = new $tx_chcforum_conference($this->current_conf_id, $this->cObj);
	   		$posts_since_last_visit = $conf->return_post_ids_since($this->last_visit,false,false,true);
				if (is_array($posts_since_last_visit)) {
					foreach ($posts_since_last_visit as $k => $v) {
						$posts_since_last_ids_only[] = $v['post_uid'];
						$posts_to_threads[$v['post_uid']] = $v['thread_uid']; 
					}
				}
			}
			if (!is_array($this->posts_read)) $this->posts_read = array();
			if (is_array($posts_since_last_visit)) {
				$ids = array_diff($posts_since_last_ids_only,$this->posts_read);
				if ($include_threads == true && is_array($ids)) {
					foreach ($ids as $k => $v) {
						$tmp = array();
						$tmp['thread_uid'] = $posts_to_threads[$v];
						$tmp['post_uid'] = $v;
						$out[] = $tmp;
					}
					$ids = $out;
				}
			}
			return $ids;
		}


		/**
		* Adds current_post_id attribute to posts read table.
		*
		* @return void
		*/
		function add_post_read ($current_post_id = false) {
			// set current post id
			if ($current_post_id) $this->current_post_id = $current_post_id;
			if (empty($this->posts_read)) {
				$this->set_posts_read();
			}

			if ($this->uid) {
				// add post to posts_read array
				$this->posts_read[] = $this->current_post_id;
				// first, delete all the rows in the post table where uid = $this->uid
				$posts_read = $posts_read_rows;
				$table = 'tx_chcforum_posts_read';
				$where = 'feuser_uid='.$this->uid;
				$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);

				// then, add the new posts read array
				$data_arr['post_uid'] = serialize($this->posts_read);
				$data_arr['feuser_uid'] = $this->uid;
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$data_arr);
			}
		}

		function auth_against_fgroup($fgroup_uid) {

			$table = 'tx_chcforum_forumgroup';
			$fields = '*';
			$addWhere = "uid=$fgroup_uid";
			$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			if (mysql_error()) debug(array(mysql_error(), $query1));
			// For each forum group, check and see if this user belongs to it
			if ($results) $row = mysql_fetch_assoc($results);
			$GLOBALS['TYPO3_DB']->sql_free_result($results);
			// Do the auth for each forum
			// Explode string containing users and groups for this forum group
			if ($row['forumgroup_users']) $auth_users = explode(',', $row['forumgroup_users']);
			if ($row['forumgroup_groups']) $auth_groups = explode(',', $row['forumgroup_groups']);

			// set up the fegroups array
			if (is_array($this->groupData['title'])) {
				 foreach ($this->groupData['title'] as $k => $v) {
				 		$user_groups[] = $k;
				 }				 
			}

			// Does this user's ID belong to the list of user IDs allowed to access this forum (as per the FG)?
			if (is_array($auth_users) and in_array($this->uid, $auth_users)) {
				$fg_user_authenticated = true;
			}
			// Does this user belong to all the groups that belong to this forumgroup?
			if (!empty($auth_groups) && !empty($user_groups)) {
				foreach ($auth_groups as $value) {
					// For each of the groups belonging to this forumgroup, see if it is contained in the 
					// user_groups array
					if (is_array($user_groups) && !in_array($value, $user_groups)) {
						$fg_group_authenticated = false;
						break; // If the group isn't in the user_group array, stop this loop
					} else {
						// As long as the loop hasn't stopped, continue to authenticate
						$fg_group_authenticated = true;
					}
				}
			}
			if ($fg_group_authenticated == true or $fg_user_authenticated == true) return true;
		}		

		function watch_conf($conf_uid) {
			if (!$conf_uid) return false;
			if ($this->uid && $this->can_read_conf($conf_uid)) {
				$conf_prefs_array = $this->get_conf_prefs();
				if (!is_array($conf_prefs_array)) $conf_prefs_array = array();
				if (is_array($conf_prefs_array) && !in_array($conf_uid,$conf_prefs_array)) {
					$conf_prefs_array[] = $conf_uid;
					$this->update_conf_prefs($conf_prefs_array);
				}
				return true;
			}
		}

		function ignore_conf($conf_uid) {
			if (!$conf_uid) return false;
			$conf_prefs_array = $this->get_conf_prefs();
			if ($this->uid && is_array($conf_prefs_array) && in_array($conf_uid,$conf_prefs_array)) {
				$key = array_search($conf_uid,$conf_prefs_array);
				unset($conf_prefs_array[$key]);
				$this->update_conf_prefs($conf_prefs_array);
				return true;				
			}
		}

		function watch_thread($thread_uid,$conf_uid) {
			if (!$thread_uid) return false;
			if ($this->uid && $this->can_read_conf($conf_uid)) {
				$thread_prefs_array = $this->get_thread_prefs();
				if (!is_array($threads_prefs_array)) $threads_prefs_array = array();
				if (!in_array($thread_uid,$threads_prefs_array)) {
					$thread_prefs_array[] = $thread_uid;
					$this->update_thread_prefs($thread_prefs_array);
				} 					
				return true;
			}			
		}

		function ignore_thread($thread_uid) {
			if (!$thread_uid) return false;
			$thread_prefs_array = $this->get_thread_prefs();
			if ($this->uid && is_array($thread_prefs_array) && in_array($thread_uid,$thread_prefs_array)) {
				$key = array_search($thread_uid,$thread_prefs_array);
				unset($thread_prefs_array[$key]);
				$this->update_thread_prefs($thread_prefs_array);
				return true;				
			}			
		}

		/**
		* Get the user preferences for which email confs.
		*
		* @return array  conferences that the user has opted to follow via email.
		*/
		function get_conf_prefs() {
			if ($this->uid) {
				$forum_pid = $this->cObj->conf['pidList'];
				$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_user_conf', "user_uid=$this->uid AND forum_uid=$forum_pid");
				if ($qresults) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
						$conf_prefs[] = $row['mailer_confs'];
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qresults);
				}
				if (!$conf_prefs) {
					$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_user_conf', "user_uid=$this->uid AND NOT forum_uid");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
						$conf_prefs[] = $row['mailer_confs'];
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qresults);
				}
			}
			return $conf_prefs;
		}

		function get_thread_prefs() {
			if ($this->uid) {
				$forum_pid = $this->cObj->conf['pidList'];
				$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_user_thread', "user_uid=$this->uid AND forum_uid=$forum_pid");
				if ($qresults) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
						$thread_prefs[] = $row['mailer_threads'];
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qresults);
				}
				if (!$thread_prefs) {
					$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_chcforum_user_thread', "user_uid=$this->uid AND NOT forum_uid");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults)) {
						$thread_prefs[] = $row['mailer_threads'];
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($qresults);
				}
			}
			return $thread_prefs;
		}

		function update_thread_prefs($thread_prefs_array) {
			if ($this->fconf['mailer_disable'] != true) {
				// Update the thread mailer prefs
				$forum_pid = $this->cObj->conf['pidList'];
				$where = "user_uid=$this->uid AND forum_uid=$forum_pid";
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_user_thread', $where); // delete all rows for this user
				if ($thread_prefs_array) {
					foreach ($thread_prefs_array as $thread_uid) {
						$fconf_data_arr = array (
							'user_uid' => $this->uid,
							'mailer_threads' => $thread_uid,
							'forum_uid' => $this->cObj->conf['pidList']
						);
						$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_chcforum_user_thread', $fconf_data_arr);
						$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_user_thread', "user_uid=$this->uid AND NOT forum_uid AND mailer_threads=$thread_uid"); // delete all rows for this user
					}
				}
			}			
		}

		function update_conf_prefs($conf_prefs_array) {
			if ($this->fconf['mailer_disable'] != true) {
				// Update the conf mailer prefs
				$forum_pid = $this->cObj->conf['pidList'];
				$where = "user_uid=$this->uid AND forum_uid=$forum_pid";
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_user_conf', $where); // delete all rows for this user

				if ($conf_prefs_array) {
					foreach ($conf_prefs_array as $conf_uid) {
						$fconf_data_arr = array (
							'user_uid' => $this->uid,
							'mailer_confs' => $conf_uid,
							'forum_uid' => $this->cObj->conf['pidList']
						);
						$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_chcforum_user_conf', $fconf_data_arr);
						$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_user_conf', "user_uid=$this->uid AND NOT forum_uid AND mailer_confs=$conf_uid"); // delete all rows for this user
					}
				}
			}
		}


		function set_access_array() {

			// get all forum groups that this user belongs to.
			$this->access_array = array();
			$this->access_array['groups'] = array();

			$table = 'tx_chcforum_forumgroup';
			$fields = 'uid,forumgroup_title';
			$addWhere = "";
			$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				if ($this->auth_against_fgroup($row['uid']) == true) {
					$this->access_array['groups'][] = $row['uid'];					
				}
			}

			$GLOBALS['TYPO3_DB']->sql_free_result($results);

			// get all read/writable cats
			$table = 'tx_chcforum_category';
			$fields = 'uid, auth_forumgroup_r';
			$addWhere = "";
			$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				if (!$row['auth_forumgroup_r']) $this->access_array['cats'][$row['uid']]['r'] = true;
				if (!$row['auth_forumgroup_r']) $this->access_array['cats'][$row['uid']]['w'] = true;
				if ($row['auth_forumgroup_r']) {
					// Explode string containing the IDs of the forum groups that can view this conference
					$groups = explode(',', $row['auth_forumgroup_r']);
					if (is_array($groups)) {					
						foreach ($groups as $group_uid) {
							if (in_array($group_uid,$this->access_array['groups']) == true) {
								$this->access_array['cats'][$row['uid']]['r'] = true;
								$this->access_array['cats'][$row['uid']]['w'] = true;								
							}
						}
					}
				}
			}			
			$GLOBALS['TYPO3_DB']->sql_free_result($results);

			// get all read/writable confs
			$table = 'tx_chcforum_conference';
			$fields = 'uid, cat_id, conference_public_r, conference_public_w, auth_forumgroup_r, auth_forumgroup_w, auth_feuser_mod, auth_forumgroup_attach';
			$addWhere = "";
			$where = tx_chcforum_shared::buildWhere($table,$addWhere);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				// check if the user can view the parent category. If not, the
				// user will not be allowed to view the conference.
				if (is_array($this->access_array['cats']) && array_key_exists($row['cat_id'],$this->access_array['cats']) == true) {
					// if the conference is public_read
					// give the user (logged in or not) 
					// read access to the conference.
					if ($row['conference_public_r'] == '1') {
						$this->access_array['confs'][$row['uid']]['r'] = true;
					}
					// if the conference is public_write 
					// give the user (logged in or not)
					// write access to the conference.
					if ($row['conference_public_w'] == '1') {
						$this->access_array['confs'][$row['uid']]['w'] = true;
					}

					// if there are no read forum groups assigned,
					// and there are no write forum groups assigned,
					// and the conference is not public, give read
					// access to logged-in users only.
					if ($row['auth_forumgroup_r'] == false && $row['auth_forumgroup_w'] == false && $this->uid == true) {
						$this->access_array['confs'][$row['uid']]['r'] = true;											
					}

					// if there are no write forum groups assigned,
					// and the conference is not public, give write
					// access to logged-in users only.
					if ($row['auth_forumgroup_r'] == false && $row['auth_forumgroup_w'] == false && $row['auth_forumgroup_r'] == false && $this->uid == true) {
						$this->access_array['confs'][$row['uid']]['w'] = true;											
					}

					// if there are forum groups set for this category, we need
					// to check against them.
					if ($row['auth_forumgroup_r']) {
						unset($groups);
						// Explode string containing the IDs of the forum groups that can view this conference
						$groups = explode(',', $row['auth_forumgroup_r']);
						if (is_array($groups)) {					
							foreach ($groups as $group_uid) {
								if (in_array($group_uid,$this->access_array['groups']) == true) {
									$this->access_array['confs'][$row['uid']]['r'] = true;
								}
							}
						}
					}

					if ($row['auth_forumgroup_w']) {
						unset($groups);
						// Explode string containing the IDs of the forum groups that can view this conference
						$groups = explode(',', $row['auth_forumgroup_w']);
						if (is_array($groups)) {					
							foreach ($groups as $group_uid) {
								if (in_array($group_uid,$this->access_array['groups']) == true) {
									$this->access_array['confs'][$row['uid']]['r'] = true;
									$this->access_array['confs'][$row['uid']]['w'] = true;
								}
							}
						}
					}

					// check whether or not the user can moderate this conference
					if ($row['auth_feuser_mod']) {
						unset($users);
						$users = explode(',', $row['auth_feuser_mod']);
						if (is_array($users)) {
							foreach ($users as $user_uid) {
								if (in_array($this->uid,$users) == true) {
									$this->access_array['confs'][$row['uid']]['m'] = true;
								}
							}
						}
					}

					// check whether or not the user can moderate this conference
					if ($row['auth_forumgroup_attach']) {
						unset($groups);
						$groups = explode(',', $row['auth_forumgroup_attach']);
						if (is_array($groups)) {
							foreach ($groups as $group_uid) {
								if (in_array($group_uid,$this->access_array['groups']) == true) {
									$this->access_array['confs'][$row['uid']]['a'] = true;
								}
							}
						}
					}
				} // close category check if statement
			}	
			$GLOBALS['TYPO3_DB']->sql_free_result($results);

		}

		function can_mod_cat($cat_uid) {
			if (!$this->access_array) $this->set_access_array();
			if ($this->access_array['cats'][$cat_uid]['m'] == true) {
				return true;
			} else {
				return false;
			}
		}	

		function can_write_cat($cat_uid) {
			if (!$this->access_array) $this->set_access_array();
			if ($this->access_array['cats'][$cat_uid]['w'] == true or
				$this->access_array['cats'][$cat_uid]['m'] == true) 
			{
				return true;
			} else {
				return false;
			}
		}		

		function can_read_cat($cat_uid) {
			if (!$this->access_array) $this->set_access_array();
			// if the user can read or write or moderate the category, then the user can read it
			if ($this->access_array['cats'][$cat_uid]['r'] == true or 
				$this->access_array['cats'][$cat_uid]['w'] == true or
				$this->access_array['cats'][$cat_uid]['m'] == true) 
			{
				return true;
			} else {
				return false;
			}
		}

		function can_mod_conf($conf_uid) {
			if (!$this->access_array) $this->set_access_array();
			// if user can read, write, or moderate the conference, then the user can read it.
			if ($this->access_array['confs'][$conf_uid]['m'] == true) {
				return true;
			} else {
				return false;
			}
		}

		function can_write_conf($conf_uid) {
			if (!$this->access_array) $this->set_access_array();
			// if the user can write or moderate the conference, then the user can write to it.
			if ($this->access_array['confs'][$conf_uid]['w'] == true or
				$this->access_array['confs'][$conf_uid]['m'] == true)
			{
				return true;
			} else {
				return false;
			}
		}

		function can_read_conf($conf_uid) {
			if (!$this->access_array) $this->set_access_array();
			// if user can read, write, or moderate the conference, then the user can read it.
			if ($this->access_array['confs'][$conf_uid]['r'] == true or 
				$this->access_array['confs'][$conf_uid]['w'] == true or
				$this->access_array['confs'][$conf_uid]['m'] == true)
			{
				return true;
			} else {
				return false;
			}
		}

		function can_attach_conf($conf_uid) {
			if (!$this->access_array) $this->set_access_array();
			// if user can read, write, or moderate the conference, then the user can read it.
			if ($this->access_array['confs'][$conf_uid]['a'] == true) {
				return true;
			} else {
				return false;
			}
		}

		function can_edit_post($post_uid) {
			$success = false;
			if ($this->uid) { // a user must be logged in to edit a post...
				$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
				$post = new $tx_chcforum_post ($post_uid, $this->cObj);
				$conf_uid = $post->conference_id;

				// if the user can mod, he/she is allowed to edit the post.
				if ($this->can_mod_conf($conf_uid) == true) {
					$success = true;
					return $success;
				}

				// the user can't mod, but maybe he/she is allowed to edit own posts
				// in this conference.
				if ($post->post_author == $this->uid) {
					// the user is the post author, but are author edits allowed?
					$tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
					$conference = new $tx_chcforum_conference($conf_uid, $this->cObj);
					if ($conference->conference_allow_user_edits == true) {
						$success = true; // user == author and conf allows edits, so let's authenticate.
					}
				} else {
					$success = false; // this user is not the post author
				}
			} else {
				$success = false; // this user isn't really a logged in user, so no editing allowed.
			}
			return $success;
		}


} // closes class

	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_user.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_user.php']);
	}

?>
