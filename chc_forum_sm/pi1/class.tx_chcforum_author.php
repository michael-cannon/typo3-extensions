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
	* The author class is instantiated when we need to get some information about
	* the author of a post.
	*
	* Note: Both author and user class constructors need to be cleaned up somewhat. 
	* We only need some of the author information -- and we probably don't want to get the info 
	* We don't need from the db.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_author extends tx_chcforum_user {
		 
		var $uid;
		var $pid;
		var $tstamp;
		var $username;
		var $usergroup;
		var $disable;
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
		* Author constructor -- initializes the author object and returns true of we're pulling data from the DB.
		*
		* @param integer  $author_id: the author's uid.
		* @param object  $cObj: cObj that gets passed to every constructor in the forum.
		* @return boolean  true if the DB query returned anything.
		*/
		function tx_chcforum_author ($author_id = false, $cObj) {
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;

			// bring in the user object.
			$this->user = $this->fconf['user'];

			$this->internal['results_at_a_time'] = 1000;
			if (!$author_id) {
				return;
			}

			// use getRecord because these records are stored on a different page...
			$row_array = $this->pi_getRecord('fe_users', $author_id, 0);
			
			if ($row_array) {
				foreach ($row_array as $attr => $value) {
					$this->$attr = $value;
				}

                // MLC vaguely protect emails as usernames
                if ( $this->email == $this->username )
                {
                    $this->username = str_replace( '@'
                                        , '[at]'
                                        , $this->username
                                    );
                }

                return true;
            }
		}
		 
		/**
		* Called by display -- creates the HTML for the author profile. Unlike many display
		* functions, this one doesn't have it's own template. Instead, it uses various kinds 
		* of message objects.
		*
		* @return string  HTML for author profile.
		*/
		function display () {

			// if the profile has been disabled in fconf, return an error message.
			if ($this->fconf['disable_profile'] != false) {
				$text = tx_chcforum_shared::lang('err_profiles_disabled');
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$message = new $tx_chcforum_message($this->cObj, $text, 'error');
				$out .= $message->display();
				return $out;						
			}
			 
			if ($this->conf['cwtCommunityIntegrated'] == true) {
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_contact_hdr'), 'profile_hdr_big');
				$out.= $message->display();
		
				$out.= '<div class="tx-chcforum-pi1-profileBorder">';
				$out.= tx_chcforum_shared::getCWTcommunity('PROFILE');
				$out.= '</div>';
				$out.= '<br />';
				
				$links_text .= $this->returnCwtBuddyLink().'<br />';
				$links_text .= $this->returnCwtPMLink().'<br />';
				$links_pm_buddyadd = new $tx_chcforum_message($this->cObj, $links_text, 'profile_hdr');
				$links_pm_buddyadd = $links_pm_buddyadd->display();

				$out = str_replace('<{pm_buddy_links}>',$links_pm_buddyadd,$out);

				// cwt community won't show the profile to a non-logged in user.
				// to keep things consistent, we won't show forum posts to a non-
				// logged in user either (forum post count that is).
				if ($this->user->uid) {
					$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
					$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_forum_hdr'), 'profile_hdr_big');
					$out .= $message->display();
					$out .= '<div class="tx-chcforum-pi1-profileBorder"><table><tr><th>';
					$out .= tx_chcforum_shared::lang('author_total_posts').'</th><td>'.$this->return_total_posts().'</td></tr></table>';
					$out .= '</div>';
					$out .= '<br />';
				}
				return $out;
				
			} else { // normal chc_forum profile view
				if ($this->fconf['use_username'] == true) {
					$hdr = tx_chcforum_shared::lang('profile_hdr').': '.$this->username;
				} else {
					$hdr = tx_chcforum_shared::lang('profile_hdr').': '.$this->name.' ['.$this->username.']';
				}
				$img = $this->return_img_tag();
				#$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				#$message = new $tx_chcforum_message($this->cObj, $hdr);
				#$out .= $message->display();
				#$out .= '<br />';
				#$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				#$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_contact_hdr'), 'profile_hdr_big');
				#$out .= $message->display();
				$out .= '<div class="tx-chcforum-pi1-profileBorder">';
				$out .= '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td valign="top" align="left">';
				if ($this->www && $this->fconf['disable_website'] == false) $out .= tx_chcforum_shared::lang('profile_website_lbl').' <a href="'.$this->www.'" target="_blank">'.$this->www.'</a><br />';
				if ($this->email && $this->fconf['disable_email'] == false) $out .= tx_chcforum_shared::lang('profile_emailaddr').' '.$this->cObj->getTypoLink($this->email, $this->email).'<br />';
				if ($this->tx_chcforum_yahoo && $this->fconf['disable_yahoo'] == false) $out .= 'Yahoo '.tx_chcforum_shared::lang('profile_form_screen_name').' '.$this->tx_chcforum_yahoo.'<br />';
				if ($this->tx_chcforum_aim && $this->fconf['disable_aim'] == false) $out .= 'AIM '.tx_chcforum_shared::lang('profile_form_screen_name').' '.$this->tx_chcforum_aim.'<br />';
				if ($this->tx_chcforum_msn && $this->fconf['disable_msn'] == false) $out .= 'MSN '.tx_chcforum_shared::lang('profile_form_screen_name').' '.$this->tx_chcforum_msn.'<br />';
				if ($this->tx_chcforum_customim && $this->fconf['custom_im']) $out .= $this->fconf['custom_im'].' '.tx_chcforum_shared::lang('profile_form_screen_name').' '.$this->tx_chcforum_customim.'<br />';
				$out .= '</td><td align="right">';
				if ($img && $this->fconf['disable_img'] == false) $out .= $img;
				$out .= '</td></tr></table>';
				$out .= '</div>';
				$out .= '<br />';
	
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('profile_forum_hdr'), 'profile_hdr_big');
				$out .= $message->display();
				$out .= '<div class="tx-chcforum-pi1-profileBorder"><table><tr><th>';
				$out .= tx_chcforum_shared::lang('author_total_posts').'</th><td>'.$this->return_total_posts().'</td></tr></table>';
				$out .= '</div>';
				$out .= '<br />';
			}
	
			
			return $out;
		}
		 
		function returnCwtBuddyLink() {
			if ($this->conf['cwtCommunityIntegrated'] == true) {
				$params = array ('action' => 'getviewbuddylistadd',
					'buddy_uid' => $this->uid);
				$img_path = $this->fconf['tmpl_img_path'].'user_pm_add_buddy.'.$this->fconf['image_ext_type'];
				$title = '<img border="0" src="'.$img_path.'">'.tx_chcforum_shared::lang('cwt_buddyadd_btn');
				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);
				return $out;
			}	
		}
		
		function returnCwtPMLink($post_uid = false) {
			if ($this->conf['cwtCommunityIntegrated'] == true) {
				$params = array ('action' => 'getviewmessagesnew',
					'recipient_uid' => $this->uid);
				if ($post_uid) $params['post_uid'] = $post_uid;
				$img_path = $this->fconf['tmpl_img_path'].'user_pm_message_new.'.$this->fconf['image_ext_type'];
				$title = '<img border="0" src="'.$img_path.'">'.tx_chcforum_shared::lang('cwt_message_new');
				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);
				return $out;
			}				
		}

		/**
		* Calculates the total number of posts made by this author
		*
		* @return integer  total posts by this author.
		*/
		function return_total_posts() {

			if ($this->uid) {
				$addWhere = "post_author=$this->uid";
				$where = tx_chcforum_shared::buildWhere('tx_chcforum_post',$addWhere);
				$qresults = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)','tx_chcforum_post',$where);
				$cnt = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qresults);
				$GLOBALS['TYPO3_DB']->sql_free_result($qresults);
				
				$cnt = $cnt['count(*)'];
				return $cnt;
			} else {
				return false;
			}
		}

		function return_crdate() {
			if ($this->crdate) {
				$format = $this->fconf['date_format'];
				$date = $this->strftime($format, $this->crdate);
				return $date;
			}
		}

		
		/**
		* This method tells us whether the author is also a frontend user.
		*
		* @return boolean returns true of the author is a frontend user, false if not.
		*/
		
		function is_feuser() {
			if ($this->uid) {
				return true;
			} else {
				return false;
			}			
		}
		 
		/**
		* Returns the HTML for the author image
		*
		* @param integer  $height: the maximum height for the image.
		* @return string  author image HTML.  
		*/
		function return_img_tag($height = 75) {
			
			// check for custom image field
			if ($this->fconf['alt_img_field']) {
				$field = $this->fconf['alt_img_field'];
			} else {
				$field = 'image';					
			}

			// load the TCA.
			t3lib_div::loadTCA("fe_users");
			
			// check for custom image path
			if ($this->fconf['alt_img_path']) {
				$img_path = './'.$this->fconf['alt_img_path'];
				$img_path_rel = $this->fconf['alt_img_path'];
			} else {
				#$img_path = './uploads/pics';				
				$img_path = './'.$GLOBALS['TCA']['fe_users']['columns']['image']['config']['uploadfolder'];
				$img_path_rel = $this->fconf['alt_img_path'];

			}
			if (!empty($this->$field) && t3lib_div::validPathStr($this->$field) == true) {
				$img = explode(',', $this->$field);

				if (file_exists($img_path.'/'.$img[0]) == true) {
					$image_conf = $this->conf['userImg.'];		
					$image_conf['file'] = $img_path_rel.'/'.$img[0];
					$image_conf['altText'] = tx_chcforum_shared::lang('alt_userpic');
					$image_conf['titleText'] = 'userpic';
					$image_conf['params'] = 'class="userpic"';
					$gen_img_path = $this->cObj->IMG_RESOURCE($image_conf);
					// if image processing isn't working, go ahead and just show the
					// file as is.
					if (!$gen_img_path) {
						$out = '<img title="userpic" alt="'.tx_chcforum_shared::lang('alt_userpic').'" class="userPic" src="'.$img_path.'/'.$img[0].'" height="'.$height.'" />';
					} else {
						$out = '<img title="userpic" alt="'.tx_chcforum_shared::lang('alt_userpic').'" class="userPic" src="'.$gen_img_path.'" height="'.$height.'" />';					
					}
				}
			}
			return $out;
		}
		
		function return_email_link ($title) {
			$address = $this->email;
			// Make replacements for @ and . in address string.
			$out = $this->cObj->getTypoLink($title,$address);			
			#$address = str_replace('@', '&#64;', $address);
			#$address = str_replace('.', '&#46;', $address);
			#$out = '<a href="mailto'.'&#58'.$address.'">'.$title.'</a>';
			return $out;
		}
		
	 /** 
		* I'm making name be passable via the function because we might want to use the name in the post table rather than
		* the name in the fe_users table -- in case the user has been deleted from fe_users, this way we can still display
		* the name in the cat or conference list. This can also be called on its own, without instantiating the method.
		*
		* @param boolean $name: the name that you want to compose the link. If there isn't one, use $this->name.
		* @return string html with author's name wrapped in a link to profile view of this author.
		*/
		function return_name_link ($name = false, $uid = false) {
			if (!$name && $this->fconf['use_username']) $name = $this->username;
			if (!$name) $name = $this->name;
			if (!$name) $name = $this->username; // we'll use the username is name isn't available.
			if (is_numeric($uid)) {
				$hash_uid = tx_chcforum_shared::encode($uid);
			} else {
				$hash_uid = tx_chcforum_shared::encode($this->uid);
			}

			$params = array ('view' => 'profile', 'author' => $hash_uid);

			$title = $name;
			if ($this->fconf['disable_profile'] != false) {
				return $title;
			} else {
				return tx_chcforum_shared::makeLink($params, $title);
			}
		}
	}

	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_author.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_author.php']);
	}
	 
?>
