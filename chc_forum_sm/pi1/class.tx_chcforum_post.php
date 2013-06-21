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
	* Post class contains the attributes and methods for handling and
	* displaying posts in the chc_forum extension.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_post extends tx_chcforum_pi1 {
		 
		var $uid;
		var $pid;
		var $tstamp;
		var $crdate;
		var $cruser_id;
		var $deleted;
		var $hidden;
		var $category_id;
		var $conference_id;
		var $thread_id;
		var $post_author;
		var $post_author_name;
		var $post_author_email;
		var $post_subject;
		var $post_author_ip;
		var $post_edit_tstamp;
		var $post_edit_count;
		var $post_attached;
		var $post_text;
		var $preview;
		 
		var $conf_auth;
		var $cObj;
		 
	 	/** 
		* Note to self:
		* I think you need to put security in display rather than post -- as it is, the program is making a new user object each time
		* it displays a post -- so in thread view, that means it's creating 10 user objects (one for each post). Better to do this once
		* in display. It's impossible to display anything, anyhow -- everything gets done view the view variable, and if that stops output,
		* there's no need to do it in the objects. The only place where this might be an issue is the form class -- perhaps we could double
		* The auth there.... anyhow, you'll have to take all this stuff out of the classes, since you put this user creation techniques in the conf
		* thread cat and post classes -- it might be ok to keep it in cat and conf -- but not in thread and post -- too much overhead.
		*
		* Constructs the post object -- pulls the record from the database.
		*
		* @param integer $post_id: the uid of the record we're grabbing from the post table.
		* @param object $cObj: the cObj that gets passed to all the constructors
		* @param boolean $preview: if this is set to true, the $this->is_preview is also set to true, which limits how this post is displayed.
		* @return void
		*/
		function tx_chcforum_post ($post_id, $cObj, $preview = false) {
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;

			// bring in the user object.
			$this->user = $this->fconf['user'];

			$this->internal['results_at_a_time'] = 1000;
			if (!$post_id) {
				return;
			}

			$addWhere = "uid=$post_id";
			$table = 'tx_chcforum_post';
			$fields = '*';
			$limit = '1';
			$where = tx_chcforum_shared::buildWhere($table,$addWhere,1);
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by,$limit);
			$row_array = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results);
			$GLOBALS['TYPO3_DB']->sql_free_result($results);

			if ($row_array) {
				foreach ($row_array as $attr => $value) {
					$this->$attr = $value;
				}
			}
			if ($preview == true) $this->is_preview = true;
			  
			// sometimes we need to make sure a post is valid -- that we actually have a post record. This attribute answers this for us.
			if ($this->post_author && $this->post_subject && $this->post_text) $this->is_valid = true;
		}
		 
	 /**
		* As far as I know, this is no longer used and should be removed... I'm not even sure what,
		* exactly, this is meant to do. It seems to me that it returns all the posts that have been
		* read by the user... delete in next release.
		*
		* @return boolean  true if the thread (post) is new?
		*/
		function get_threads_new() {
			$tx_chcforum_user = t3lib_div::makeInstanceClassName("tx_chcforum_user");
			$user = new $tx_chcforum_user();
			$is_new = false;
			$query = "SELECT * FROM tx_chcforum_postread WHERE feuser_uid=$user->uid AND post_uid=$this->uid";
			$results = mysql(TYPO3_db, $query);
			if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));
				$data = mysql_fetch_assoc($results);
			if (!empty($data)) {
				$is_new = true;
			} else {
				$user->add_post_read($this->uid);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($results);
			return $is_new;
		}
		 
	 /**
		* Returns some summary information on the post -- used in list views
		*
		* @param string $view: this string tells us where the data is going to be used. Valid values are 'all_cats' or 'single_conf'.
		* @return string returns either date/time, thread link, author if in cat view or date/time, age, author if in conf view.
		*/
		function return_post_info ($view = 'all_cats') {
			switch ($view) {
				case 'all_cats':
				$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
				$thread = new $tx_chcforum_thread($this->thread_id, $this->cObj);
				$out = $this->conf['posts.']['post_info_string.']['all_cats'];
				break;
				
				case 'single_conf':
				$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
				$thread = new $tx_chcforum_thread($this->thread_id, $this->cObj);
				$out = $this->conf['posts.']['post_info_string.']['single_conf'];
				break;
			}

			$markers['{DATE}'] = $this->return_post_date();
			$markers['{TIME}'] = $this->return_post_time();
			$markers['{IN}'] = tx_chcforum_shared::lang('return_post_info_in');
			$markers['{THREAD_LINK}'] = $thread->thread_link(true); 
			$markers['{BY}'] = tx_chcforum_shared::lang('return_post_info_by');
			$markers['{POSTED}'] = $values[] = tx_chcforum_shared::lang('return_post_info_posted');
			$markers['{AGE}'] = $values[] = $this->return_post_age();			
			$markers['{AGO}'] = tx_chcforum_shared::lang('return_post_info_ago');

			if ($this->post_author) {
				$tx_chcforum_author= t3lib_div::makeInstanceClassName("tx_chcforum_author");
				$author = new $tx_chcforum_author($this->post_author, $this->cObj);
				$markers['{AUTHOR_LINK}'] = $author->return_name_link();
			} else {
				$markers['{AUTHOR_LINK}'] = $this->post_author_name;
			}

			$out = $this->cObj->substituteMarkerArrayCached($out,$markers);
			return $out;
		}
	
	 /** 
		* Note -- This function assumes that it can trust the information in the post object. New posts can be created
		* Either from the DB, and we can assume that that data is trusted, since it gets validated before being submitted --
		* or, a post can be created in a preview, and this information must also be validated first, because we don't
		* want untrusted user data being displayed without being checked first.
		* If this->preview is set to true, this function will display somewhat less information.
		*
		* @return string returns the html display of this post. 
		*/
		function display_post () {

  		// create author object
  		$tx_chcforum_author= t3lib_div::makeInstanceClassName("tx_chcforum_author");
  		$this->author = new $tx_chcforum_author($this->post_author, $this->cObj);
	  	
	  	$img_tag = $this->author->return_img_tag();
			 
			if (!$this->tmpl_path) $this->tmpl_path = tx_chcforum_shared::setTemplatePath();
				$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
				$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'single_post.tpl');
			$tmpl->prepare();			
			
			$tmpl->assign('anchor', '<a name="'.$this->uid.'"></a>');			
			$tmpl->assign('img_tag', $img_tag);
			$tmpl->assign('author_lbl', tx_chcforum_shared::lang('display_post_author') );
			$tmpl->assign('date_lbl', tx_chcforum_shared::lang('display_post_date'));
			$tmpl->assign('subject_lbl', tx_chcforum_shared::lang('display_post_subject'));
			$tmpl->assign('author_name', $this->return_post_author_link());
			$tmpl->assign('date', $this->return_post_date());
			$tmpl->assign('time', $this->return_post_time());
			$tmpl->assign('subject', $this->post_subject);
			$im_links = $this->build_im_links();
			$tmpl->assign('aim_link', $im_links['aim']);
			$tmpl->assign('yahoo_link', $im_links['yahoo']);
			$tmpl->assign('msn_link', $im_links['msn']);
			$tmpl->assign('customim_link', $im_links['customim']);
				
			  // if the post is hidden, note that it's hidden.
			  if ($this->hidden == 1) {
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('post_hidden'), 'error_no_border');
				$tmpl->assign('message',$message->display());
		
			  }


			// Don't include the quote, reply, edit or delete button if this is a preview.
			if ($this->preview != true) {
				if (!in_array($this->thread_id,$this->user->closed_threads)) $tmpl->assign('quote_link', $this->return_quote_link());
				if (!in_array($this->thread_id,$this->user->closed_threads)) $tmpl->assign('reply_link', $this->return_reply_link());

				#if ($this->user->can_mod_conf($this->conference_id) == true) $tmpl->assign('admin_edit_link', $this->admin_edit_link());
				if ($this->user->can_edit_post($this->uid) == true) $tmpl->assign('admin_edit_link', $this->admin_edit_link());
				// if user is a moderator
				if ($this->user->can_mod_conf($this->conference_id) == true) {
					$tmpl->assign('admin_delete_link', $this->admin_delete_link());
					$tmpl->assign('admin_unhide_link', $this->admin_unhide_link());
				}
			}
			$tmpl->assign('parsed_post_body', $this->return_parsed_text());
			
			// if a file is attached and the file exists, display it.			
			if ($this->post_attached && file_exists(t3lib_div::getFileAbsFileName('uploads/tx_chcforum/'.$this->post_attached))) {
				$tmpl->newBlock('attachment');
				$tmpl->assign('attachment', $this->return_attachment_html());
			}

			// if ratings are enabled, show the ratings section
			if ($this->fconf['allow_rating'] == true && $this->preview != true) {

				// get any params from typoscript -- used for forum / tt_news integration.
				if ($this->conf['chcAddParams']) {
	    			$paramArray = tx_chcforum_shared::getAddParams($this->conf['chcAddParams']);
				}	
				$action = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',$paramArray));
				
				// setup the options
				$i = 0;
				$options = '<select name="rateSelect">';
				while ($i <= 5) {
					$label = tx_chcforum_shared::lang('display_post_rate_option'.$i);
					$options.= '<option value="'.$i.'">'.$label.'</option>';
					$i++;
				}
				$options .= '</select>';
				$options .= '<input type="hidden" name="ratePostUID" value="'.$this->uid.'" />';
		
				$rating = $this->getRating();
				$starCount = round($rating['score']);
				$voteCount = $rating['votes'];

				$i = 0;
					while ($i < 5) {
					if ($i < $starCount) {
						$stars.= str_replace('###path###',$this->fconf['tmpl_img_path'].'star.png',$this->conf['rating.']['imghtml']);
					} else {
						if ($this->conf['rating.']['showEmpties'] == true) $stars.= str_replace('###path###',$this->fconf['tmpl_img_path'].'star_empty.png',$this->conf['rating.']['imghtml']);					
				}
					$i++;
				}

				// make the score string
				if ($voteCount == 1) $lbl = tx_chcforum_shared::lang('display_post_rate_votecount_singular');
				if ($voteCount > 1) $lbl = tx_chcforum_shared::lang('display_post_rate_votecount_plural');
				if ($voteCount == 0) $lbl = tx_chcforum_shared::lang('display_post_rate_votecount_zero');
				
				if ($voteCount >= 1) {
					$str = $this->conf['rating.']['voteCountString.']['oneOrMore'];				
				} else {
					$str = $this->conf['rating.']['voteCountString.']['ifEmpty'];								
				}
				$score = str_replace('###LABEL###',$lbl,$str);
				$score = str_replace('###COUNT###',$voteCount,$score);
				$score = str_replace('###AVG###',number_format($rating['score'],$this->conf['rating.']['voteCountString.']['avgDecimals']),$score);
					
				$tmpl->newBlock('rate');
				$tmpl->assign('stars',$stars);
				if ($this->conf['rating.']['voteCountString.']['showString'] == true) $tmpl->assign('score',$score);
				$tmpl->assign('action',$this->return_rate_action());
				$tmpl->assign('rate_label',tx_chcforum_shared::lang('display_post_rate_label'));
				$tmpl->assign('rate_select',$options);
				$tmpl->assign('rate_submit_value',tx_chcforum_shared::lang('display_post_rate_submit_label'));
			}

			// back to root of template	
  		$tmpl->gotoBlock('_ROOT');


			// Deal with extra markers, not currently used in default template, including
			// author join date, author location, and total posts by author. Code contributed
			// by Ralf Sobbe, modified by Zach.
			if($this->author->is_feuser() == true) {
  			$tmpl->assign('author_join_lbl', tx_chcforum_shared::lang('display_post_author_join'));
  			$tmpl->assign('author_join', $this->author->return_crdate());
  			if($author->city) {
  			  $tmpl->assign('author_location_lbl', tx_chcforum_shared::lang('display_post_author_location'));
  			  $tmpl->assign('author_location', $this->author->city);
			  }
  			$tmpl->assign('author_post_cnt_lbl', tx_chcforum_shared::lang('display_post_author_post_cnt'));
  			$tmpl->assign('author_post_cnt', $this->author->return_total_posts());

				// if the author is a user, and cwt_community integration is enabled
				// add a "add buddy icon"
				$tmpl->assign('cwt_buddylist_link',$this->author->returnCwtBuddyLink());

				// we can also add a PM button.
				$tmpl->assign('cwt_user_pm_message_new',$this->author->returnCwtPMLink($this->uid));


		  } else {
	  		  $tmpl->assign('author_join_lbl', tx_chcforum_shared::lang('display-post_guest'));
		  }
		  
		  return $tmpl->getOutputContent();
		}


		// returnsd the rating for a post as a rounded int.
		function getRating($dontRound = false) {
			if ($this->uid) {
				$table = 'tx_chcforum_ratings';
				$fields = 'AVG(rating) as avg, COUNT(*) as count';
				$addWhere = 'post_uid='.$this->uid;
				$where = $addWhere;
				$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$group_by,$order_by);
				$rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results);
				$rating['score'] = $rows['avg'];
				$rating['votes'] = $rows['count'];
				$GLOBALS['TYPO3_DB']->sql_free_result($results);
				return $rating;
			}
		}

		 
		/**
		* Used internally -- get the author name from the author object and
		* wraps it in a profile link. If there is no author for this post (it was
		* made anonymously) then we get the name from the post record.
		*
		* @return string author name with link (if author was logged in user) to profile.
		*/
		function return_post_author_link() {
			if ($this->author->is_feuser() == true) {
				return $this->author->return_name_link();

			} else {
				return $this->post_author_name;
			}
		}
		 
		/**
		* Returns the html needed to display the attachment, if there is one.
		*
		* @return string  Returns the html needed to display the attachment, if there is one.
		*/
		function return_attachment_html() {
			if ($this->post_attached) {
				$img_path = './typo3/gfx/fileicons/';
				
				$extension = strtolower(substr(strrchr($this->post_attached, '.'), 1));
				$icon_file = $img_path.$extension.'.gif';
				$file_path = './uploads/tx_chcforum/'.$this->post_attached;
				if (file_exists($icon_file)) {
					$image = $icon_file;
				} else {
					$image = $img_path.'default.gif';
				}
				$out = '<a href="'.$file_path.'"><img title="attachment" alt="'.tx_chcforum_shared::lang('alt_attachment').'" border="0" src="'.$image.'" /></a>';
				$out .= tx_chcforum_shared::lang('form_label_attachment').'<br />';
				$out .= '<a href="'.$file_path.'">'.$this->post_attached.'</a>';
				return $out;
			}
		}

		/**
		* Returns HTML for the IM links for this author, if they exist.
		*
		* @return string returns a string with HTML for IM links. If the links don't exist, returns false.
		*/
		function build_im_links() {
			// get fconf if it's not set already.
			if ($this->uid) {
				$base_img_path = $this->fconf['tmpl_img_path'];

				if ($this->author) {
					$author = $this->author;
				} else {
					if ($this->post_author) {
						$tx_chcforum_author= t3lib_div::makeInstanceClassName("tx_chcforum_author");
						$author = new $tx_chcforum_author($this->post_author, $this->cObj);
					} else {
						return false;
					}
				}
				
				if ($this->fconf['disable_aim'] != true) $aim = $author->tx_chcforum_aim;
				if ($this->fconf['disable_yahoo'] != true) $yahoo = $author->tx_chcforum_yahoo;
				if ($this->fconf['disable_msn'] != true) $msn = $author->tx_chcforum_msn;
				if ($this->fconf['custom_im']) $customim = $author->tx_chcforum_customim;
				
				$c = '</a>';

				if (!empty($aim)) {
					$im['aim'] .= '<a href="#" onclick="alert(\''.tx_chcforum_shared::lang('build_im_links_aim').'\n'.$aim.'.\'); return false;">';
					$im['aim'] .= '<img src="'.$base_img_path.'chat.'.$this->fconf['image_ext_type'].'">';
					$im['aim'] .= tx_chcforum_shared::lang('btn_aim');
					$im['aim'] .= $c;
				
				}
				if (!empty($yahoo)) {
					$im['yahoo'] .= '<a id="link" href="#" onclick="alert(\''.tx_chcforum_shared::lang('build_im_links_yahoo').'\n'.$yahoo.'.\'); return false;">';
					$im['yahoo'] .= '<img src="'.$base_img_path.'chat.'.$this->fconf['image_ext_type'].'">';
					$im['yahoo'] .= tx_chcforum_shared::lang('btn_yahoo');
					$im['yahoo'] .= $c;
				}
				if (!empty($msn)) {
					$im['msn'] .= '<a href="#" onclick="alert(\''.tx_chcforum_shared::lang('build_im_links_msn').'\n'.$msn.'.\'); return false;">';
					$im['msn'] .= '<img src="'.$base_img_path.'chat.'.$this->fconf['image_ext_type'].'">';
					$im['msn'] .= tx_chcforum_shared::lang('btn_msn');
					$im['msn'] .= $c;
				}
				if (!empty($customim)) {
					$im['customim'] .= '<a href="#" onclick="alert(\''.tx_chcforum_shared::lang('build_im_links_custom_1').' '.$this->fconf['custom_im'].' '.tx_chcforum_shared::lang('build_im_links_custom_2').'\n'.$customim.'.\'); return false;">';
					$im['customim'] .= '<img src="'.$base_img_path.'chat.'.$this->fconf['image_ext_type'].'">';
					$im['customim'] .= $this->fconf['custom_im'];
					$im['customim'] .= $c;
				}
				return $im;
			} else {
				return false;
			}
		}
		 
	 /**
		* Translates $this->crdate to readable date
		*
		* @return string  Returns the date as %b %d %Y.
		*/
		function return_post_date() {
			if ($this->crdate) {
				$format = $this->fconf['date_format'];
 				if (!$format) $format = '%b %d %Y'; // default date format
				$date = $this->strftime($format, $this->crdate);
				return $date;
			}
		}
		 
	 /**
		* Translates $this->crdate to the age of the post
		*
		* @return string  Returns post age.
		*/
		function return_post_age() {
			if ($this->crdate) {
				$current_time = time();
				$diff = ($current_time - $this->crdate);
				$seconds = $this->strftime('%s', $diff);

				$labels = tx_chcforum_shared::lang('return_post_age_labels');
				$labelArr = explode('|', $labels);
				if ($seconds < 3600) {
					$seconds = round ($seconds/60).$labelArr[0];
				} elseif ($seconds < 24 * 3600) {
					$seconds = round ($seconds/3600).$labelArr[1];
				} elseif ($seconds < 365 * 24 * 3600) {
					$seconds = round ($seconds/(24 * 3600)).$labelArr[2];
				} else {
					$seconds = round ($seconds/(365 * 24 * 3600)).$labelArr[3];
				}
				return $seconds;
			} else {
				return false;
			}
		}
		 
	 /**
		* Translates $this->crdate to readable time
		*
		* @return string  Returns the time as %I:%M %p.
		*/
		function return_post_time() {
			if ($this->crdate) {
				$tx_chcforum_fconf = t3lib_div::makeInstanceClassName("tx_chcforum_fconf");
				$format = $this->fconf['time_format'];
 				if (!$format) $format = '%I:%M %p'; // default time format
				$time = $this->strftime($format, $this->crdate);
				return $time;
			} else {
				return false;
			}
		}
		 
		/**
		* Returns HTML for the quote reply link
		*
		* @return string returns button linking to quote view for this post.
		*/
		function return_quote_link() {
			if ($this->uid && $this->user->can_write_conf($this->conference_id) == true) {
					$params = array ('view' => 'single_post',
					'cat_uid' => $this->category_id,
					'conf_uid' => $this->conference_id,
					'thread_uid' => $this->thread_id,
					'post_uid' => $this->uid,
					'flag' => 'quote');
				$img_path = $this->fconf['tmpl_img_path'].'quote.'.$this->fconf['image_ext_type'];
				$title = '<img src="'.$img_path.'"> '.tx_chcforum_shared::lang('btn_quote');
				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);
				return $out;
			} else {
				return false;
			}
		}

		function return_rate_action() {
			// I don't generally do this, but this seems the easiest
			// way of getting the view and page into this function.
			// generally speaking, posts don't need to know where they're
			// located -- rather than finding every spot where a post is
			// made and adding the view into it's constructor, we might as
			// well just get it from the GP vars. Of course, we need to 
			// validate it just as we would in pi1 -- ultimately, I suppose
			// that all the pi1 validation routines should be put into
			// shared (or a separate class) so that these GP vars can be
			// accessed easily from anywhere without replicating the
			// validation routines.
			$view = htmlspecialchars(t3lib_div::_GP('view'));
			if (!in_array($gpvars['view'],array('all_cats','single_cat','single_conf',
					'single_thread','single_post','edit_post','profile','search','ulist','new'))) $gpvars['view'] = 'single_thread';
			$page = htmlspecialchars(t3lib_div::_GP('page'));
			if ($gpvars['page'] < 0 or !is_numeric($gpvars['page'])) $gpvars['page'] = 0;
			$params = array (
				'view' => $view,
				'cat_uid' => $this->category_id,
				'conf_uid' => $this->conference_id,
				'thread_uid' => $this->thread_id,
				'page' => $page,
				'flag' => 'rate'				
			);
			$out.= tx_chcforum_shared::makeLink($params, $title, $attr, true);
			$out.= '#'.$this->uid;
			return $out;
		}

		/**
		* Returns HTML for the admin edit link
		*
		* @return string returns button linking to edit view for this post.
		*/
		function admin_edit_link() {
			if ($this->uid) {
				$params = array ('view' => 'edit_post',
					'cat_uid' => $this->category_id,
					'conf_uid' => $this->conference_id,
					'thread_uid' => $this->thread_id,
					'post_uid' => $this->uid,
					'flag' => 'edit');
				$img_path = $this->fconf['tmpl_img_path'].'edit.'.$this->fconf['image_ext_type'];
				$title = '<img src="'.$img_path.'"> '.tx_chcforum_shared::lang('btn_edit');

				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);

				return $out;
			} else {
				return false;
			}
		}
		
		// makes both the hide and unhide link, actually.
		function admin_unhide_link() {
			if ($this->uid) {
				$params = array ('view' => 'single_thread',
					'cat_uid' => $this->category_id,
					'conf_uid' => $this->conference_id,
					'thread_uid' => $this->thread_id,
					'post_uid' => $this->uid,
				);				
				if ($this->hidden == true) {
					$params['flag'] = 'unhide';
				} else {
					$params['flag'] = 'hide';				
				}
				$img_path = $this->fconf['tmpl_img_path'].$params['flag'].'.'.$this->fconf['image_ext_type'];
				$title = '<img src="'.$img_path.'"> '.tx_chcforum_shared::lang('btn_'.$params['flag']);
				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);
				return $out;
			}	else {
				return false;
			}
		}
		 
		/**
		* Returns HTML for the admin delete link
		*
		* @return string returns button that deletes this post.
		*/
		function admin_delete_link() {
			if ($this->uid) {
				$params = array ('view' => 'single_thread',
					'cat_uid' => $this->category_id,
					'conf_uid' => $this->conference_id,
					'thread_uid' => $this->thread_id,
					'post_uid' => $this->uid,
					'flag' => 'delete');
				$attr = 'onclick="return(confirm(\''.tx_chcforum_shared::lang('del_confirm').'\'))"';
				$img_path = $this->fconf['tmpl_img_path'].'delete.'.$this->fconf['image_ext_type'];
				$title = '<img src="'.$img_path.'"> '.tx_chcforum_shared::lang('btn_delete');

				$out.= tx_chcforum_shared::makeLink($params, $title, $attr);

				return $out;
			} else {
				return false;
			}
		}
		 
		/**
		* Returns HTML for the reply link
		*
		* @return string returns button linking to reply view for this post.
		*/
		function return_reply_link() {
			if ($this->uid && $this->user->can_write_conf($this->conference_id) == true) {
				$params = array ('view' => 'single_post',
					'cat_uid' => $this->category_id,
					'conf_uid' => $this->conference_id,
					'thread_uid' => $this->thread_id,
					'post_uid' => $this->uid);
				$img_path = $this->fconf['tmpl_img_path'].'reply.'.$this->fconf['image_ext_type'];
				$title = '<img src="'.$img_path.'"> '.tx_chcforum_shared::lang('btn_reply');

				$out.= tx_chcforum_shared::makeLink($params, $title);
				return $out;
			} else {
				return false;
			}
		}
		 
		function escape_preg_specialchars($string) {
			$pattern = str_replace('|','\|',$string);
			$pattern = str_replace('^','\^',$pattern);
			$pattern = str_replace('*','\*',$pattern);
			$pattern = str_replace('$','\$',$pattern);
			$pattern = str_replace('.','\.',$pattern);
			$pattern = str_replace('?','\?',$pattern);
			$pattern = str_replace('+','\+',$pattern);
			$pattern = str_replace('(','\(',$pattern);
			$pattern = str_replace(')','\)',$pattern);
			$pattern = str_replace(']','\]',$pattern);
			$pattern = str_replace('[','\[',$pattern);
			return $pattern;		
		}
		
		// Function taken from phpbb source -- bbcode.php
		function fcode_array_push(&$stack, $value) {
		   $stack[] = $value;
		   return(sizeof($stack));
		}

		// Function taken from phpbb source -- bbcode.php
		function fcode_array_pop(&$stack) {
		   $arrSize = count($stack);
		   $x = 1;
		
		   while(list($key, $val) = each($stack)) {
		      if($x < count($stack)) {
				 		$tmpArr[] = $val;
		      }
		      else {
				 		$return_val = $val;
		      }
		      $x++;
		   }
		   $stack = $tmpArr;
		   return($return_val);
		}

		// Function taken from phpbb source -- bbcode.php
		function fencode_first_pass_pda($text, $uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false) {
			$open_tag_count = 0;
			if (!$close_tag_new || ($close_tag_new == '')) {
				$close_tag_new = $close_tag;
			}
		
			$close_tag_length = strlen($close_tag);
			$close_tag_new_length = strlen($close_tag_new);
			$uid_length = strlen($uid);
		
			$use_function_pointer = ($func && ($func != ''));
		
			$stack = array();
			if (is_array($open_tag)) {
				if (0 == count($open_tag)) {
					// No opening tags to match, so return.
					return $text;
				}
				$open_tag_count = count($open_tag);
			} else {
				// only one opening tag. make it into a 1-element array.
				$open_tag_temp = $open_tag;
				$open_tag = array();
				$open_tag[0] = $open_tag_temp;
				$open_tag_count = 1;
			}
		
			$open_is_regexp = false;
		
			if ($open_regexp_replace) {
				$open_is_regexp = true;
				if (!is_array($open_regexp_replace)) {
					$open_regexp_temp = $open_regexp_replace;
					$open_regexp_replace = array();
					$open_regexp_replace[0] = $open_regexp_temp;
				}
			}
		
			if ($mark_lowest_level && $open_is_regexp) {
				message_die(GENERAL_ERROR, "Unsupported operation for fcode_first_pass_pda().");
			}
		
			// Start at the 2nd char of the string, looking for opening tags.
			$curr_pos = 1;
			while ($curr_pos && ($curr_pos < strlen($text))) {
				$curr_pos = strpos($text, "[", $curr_pos);
		
				// If not found, $curr_pos will be 0, and the loop will end.
				if ($curr_pos) {
					// We found a [. It starts at $curr_pos.
					// check if it's a starting or ending tag.
					$found_start = false;
					$which_start_tag = "";
					$start_tag_index = -1;
		
					for ($i = 0; $i < $open_tag_count; $i++) {
						// Grab everything until the first "]"...
						$possible_start = substr($text, $curr_pos, strpos($text, ']', $curr_pos + 1) - $curr_pos + 1);
		
						//
						// We're going to try and catch usernames with "[' characters.
						//
						if( preg_match('#\[quote=\\\"#si', $possible_start, $match) && !preg_match('#\[quote=\\\"(.*?)\\\"\]#si', $possible_start) ) {
							// OK we are in a quote tag that probably contains a ] bracket.
							// Grab a bit more of the string to hopefully get all of it..
							if ($close_pos = strpos($text, '"]', $curr_pos + 9)) {
								if (strpos(substr($text, $curr_pos + 9, $close_pos - ($curr_pos + 9)), '[quote') === false) {
									$possible_start = substr($text, $curr_pos, $close_pos - $curr_pos + 2);
								}
							}
						}

						// Now compare, either using regexp or not.
						if ($open_is_regexp) {
							$match_result = array();
							if (preg_match($open_tag[$i], $possible_start, $match_result)) {
								$found_start = true;
								$which_start_tag = $match_result[0];
								$start_tag_index = $i;
								break;
							}
						} else {
							// straightforward string comparison.
							if (0 == strcasecmp($open_tag[$i], $possible_start)) {
								$found_start = true;
								$which_start_tag = $open_tag[$i];
								$start_tag_index = $i;
								break;
							}
						}
					}
		
					if ($found_start) {
						// We have an opening tag.
						// Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
						$match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);
						$this->fcode_array_push($stack, $match);
						//
						// Rather than just increment $curr_pos
						// Set it to the ending of the tag we just found
						// Keeps error in nested tag from breaking out
						// of table structure..
						//
						$curr_pos += strlen($possible_start);
					} else {
						// check for a closing tag..
						$possible_end = substr($text, $curr_pos, $close_tag_length);
						if (0 == strcasecmp($close_tag, $possible_end)) {
							// We have an ending tag.
							// Check if we've already found a matching starting tag.
							if (sizeof($stack) > 0) {
								// There exists a starting tag.
								$curr_nesting_depth = sizeof($stack);
								// We need to do 2 replacements now.
								$match = $this->fcode_array_pop($stack);
								$start_index = $match['pos'];
								$start_tag = $match['tag'];
								$start_length = strlen($start_tag);
								$start_tag_index = $match['index'];
		
								if ($open_is_regexp) {
									$start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
								}
		
								// everything before the opening tag.
								$before_start_tag = substr($text, 0, $start_index);
		
								// everything after the opening tag, but before the closing tag.
								$between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);
		
								// Run the given function on the text between the tags..
								if ($use_function_pointer) {
									$between_tags = $func($between_tags, $uid);
								}
		
								// everything after the closing tag.
								$after_end_tag = substr($text, $curr_pos + $close_tag_length);
		
								// Mark the lowest nesting level if needed.
								if ($mark_lowest_level && ($curr_nesting_depth == 1)) {
									if ($open_tag[0] == '[code]') {
										$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
										$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
										$between_tags = preg_replace($code_entities_match, $code_entities_replace, $between_tags);
									}
									$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$uid]";
									$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$uid]";
								} else {
									if ($open_tag[0] == '[code]') {
										$text = $before_start_tag . '&#91;code&#93;';
										$text .= $between_tags . '&#91;/code&#93;';
									} else {
										if ($open_is_regexp) {
											$text = $before_start_tag . $start_tag;
										} else {
											$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$uid]";
										}
										$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$uid]";
									}
								}
		
								$text .= $after_end_tag;
		
								// Now.. we've screwed up the indices by changing the length of the string.
								// So, if there's anything in the stack, we want to resume searching just after it.
								// otherwise, we go back to the start.
								if (sizeof($stack) > 0) {
									$match = fcode_array_pop($stack);
									$curr_pos = $match['pos'];
								} else {
									$curr_pos = 1;
								}
							} else {
								// No matching start tag found. Increment pos, keep going.
								++$curr_pos;
							}
						} else {
							// No starting tag or ending tag.. Increment pos, keep looping.,
							++$curr_pos;
						}
					}
				}
			} // while
			return $text;
		}

		 
		/**
		* This method parses the text of a post for forum codes (eg. [b] = bold) and renders it accordingly
		*
		* @return string returns the post body as HTML with appropriate formatting included. 
		*/
		function return_parsed_text() {

			// check for a cached version of the thread text.
			// if the cache was generated _after_ the last time the post was changed
			// in the backend or _after_ the last time the post was edited in the 
			// frontend, then we can use the cached text.
#			if ($this->cache_parsed_text && $this->cache_tstamp >= $this->tstamp && $this->cache_tstamp >= $this->post_edit_tstamp) {
#				return $this->cache_parsed_text;
#			}

			if ($this->post_text) {
				// strip slashes, get raw text
				$raw_text = stripslashes($this->post_text);
     	
				// Much of the following code is taken from the source for phpBB, (C) 2001 The phpBB Group, Nathan Codding, Sept 26 2001.
				$fcode_tpl = $this->conf['fcode_tpl.'];
				$fcode_tpl['code_open'] = str_replace('{L_CODE}', tx_chcforum_shared::lang('post_form_code'), $fcode_tpl['code_open']);	
				$fcode_tpl['url1'] = str_replace('{URL}', '\\1', $fcode_tpl['url']);
				$fcode_tpl['url1'] = str_replace('{DESCRIPTION}', '\\1', $fcode_tpl['url1']);
				$fcode_tpl['url2'] = str_replace('{URL}', 'http://\\1', $fcode_tpl['url']);
				$fcode_tpl['url2'] = str_replace('{DESCRIPTION}', '\\1', $fcode_tpl['url2']);
				$fcode_tpl['url3'] = str_replace('{URL}', '\\1', $fcode_tpl['url']);
				$fcode_tpl['url3'] = str_replace('{DESCRIPTION}', '\\2', $fcode_tpl['url3']);
				$fcode_tpl['url4'] = str_replace('{URL}', 'http://\\1', $fcode_tpl['url']);
				$fcode_tpl['url4'] = str_replace('{DESCRIPTION}', '\\3', $fcode_tpl['url4']);
				$fcode_tpl['email'] = str_replace('{EMAIL}', '\\1', $fcode_tpl['email']);
				$fcode_tpl['color_open'] = str_replace('{COLOR}', '\\1', $fcode_tpl['color_open']);
				$fcode_tpl['size_open'] = str_replace('{SIZE}', '\\1', $fcode_tpl['size_open']);
				$fcode_tpl['img'] = str_replace('{URL}', '\\1', $fcode_tpl['img']);
     	
				// put all opening tags in $open_count
				preg_match_all('#\[[^/].*?]#', $raw_text, $open_count);
				// put all closing tags in $close_count
				preg_match_all('#\[/.*?]#', $raw_text, $close_count);
				$theText = $raw_text;
				 
				// Replace quote tags -- old method; not the phpbb inspired version
				preg_match_all('#\[quote.*?]#', $raw_text, $quote_open);
				if (count($quote_open[0]) == count(array_keys($close_count[0], '[/quote]'))) {
					$theText = preg_replace('#\[quote]#', $fcode_tpl['quote_open'], $theText);
					$theText = preg_replace('#\[/quote]#', $fcode_tpl['quote_close'], $theText);
					// Replace quote tags with authors
					preg_match_all('#\[quote=(.*?)\]#', $raw_text, $quote_authors);
					foreach ($quote_authors[1] as $the_author) {
						$author_open_quote_wrap = str_replace('{AUTHOR}',$the_author.' '.tx_chcforum_shared::lang('return_parsed_text_quote'),$fcode_tpl['quote_author']);
						$pattern = '/[quote='.$the_author.']/';
						$pattern = $this->escape_preg_specialchars($pattern);
						$theText = preg_replace($pattern, $author_open_quote_wrap, $theText);
					}
				}
     	
				// handle code tags
				$theText= $this->fencode_first_pass_pda($theText, $uid, '[code]', '[/code]', '', true, '');
     	
				$code_start_html = $fcode_tpl['code_open'];
				$code_end_html =  $fcode_tpl['code_close'];
     	
				$match_count = preg_match_all("#\[code:1:$uid\](.*?)\[/code:1:$uid\]#si", $theText, $matches);
     	
				for ($i = 0; $i < $match_count; $i++) {
					$before_replace = $matches[1][$i];
					$after_replace = $matches[1][$i];
     	
					// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
					$after_replace = str_replace("  ", "&nbsp; ", $after_replace);
					// now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
					$after_replace = str_replace("  ", " &nbsp;", $after_replace);
     	
					// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
					$after_replace = str_replace("\t", "&nbsp; &nbsp;", $after_replace);
     	
					// now Replace space occurring at the beginning of a line
					$after_replace = preg_replace("/^ {1}/m", '&nbsp;', $after_replace);
     	
					$str_to_match = "[code:1:$uid]".$before_replace."[/code:1:$uid]";
					$replacement = $code_start_html;
					$replacement .= $after_replace;
					$replacement .= $code_end_html;
					
					$theText = str_replace($str_to_match, $replacement, $theText);
				}
				$theText = str_replace("[code:$uid]", $code_start_html, $theText);
				$theText = str_replace("[/code:$uid]", $code_end_html, $theText);
     	
				// colors
				$theText = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+)\]/si", $fcode_tpl['color_open'], $theText);
				$theText = str_replace("[color]", $fcode_tpl['color_open'], $theText);
				$theText = str_replace("[/color]", $fcode_tpl['color_close'], $theText);
     	
				// size
				$theText = preg_replace("/\[size=([1-2]?[0-9])\]/si", $fcode_tpl['size_open'], $theText);
				$theText = str_replace("[/size]", $fcode_tpl['size_close'], $theText);
     	
				// [img]image_url_here[/img] code..
				// This one gets first-passed..
				$patterns[] = "#\[img\](.*?)\[/img\]#si";
				$replacements[] = $fcode_tpl['img'];

				// matches a [url]xxxx://www.typo3.org[/url] code..
				$patterns[] = "#\[url\]([\w]+?://[^ \"\n\r\t<]*?)\[/url\]#is";
				$replacements[] = $fcode_tpl['url1'];
				
				// [url]www.typo3.org[/url] code.. (no xxxx:// prefix).
				$patterns[] = "#\[url\]((www|ftp)\.[^ \"\n\r\t<]*?)\[/url\]#is";
				$replacements[] = $fcode_tpl['url2'];
     	
				// [url=xxxx://www.typo3.org]typo3[/url] code..
				$patterns[] = "#\[url=([\w]+?://[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is";
				$replacements[] = $fcode_tpl['url3'];
     	
				// [url=www.typo3.org]typo3[/url] code.. (no xxxx:// prefix).
				$patterns[] = "#\[url=((www|ftp)\.[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is";
				$replacements[] = $fcode_tpl['url4'];
     	
				// [email]user@domain.tld[/email] code..
				$patterns[] = "#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
				$replacements[] = $fcode_tpl['email'];

				$theText = preg_replace($patterns, $replacements, $theText);
				// End phpBB derived code...				

				// handle bold tags
				if (count(array_keys($open_count[0], '[b]')) == count(array_keys($close_count[0], '[/b]'))) {
					$theText = preg_replace('#\[b]#', '<span style="font-weight: bold;">', $theText);
					$theText = preg_replace('#\[/b]#', '</span>', $theText);
				}
				
				// handle italic tags
				if (count(array_keys($open_count[0], '[i]')) == count(array_keys($close_count[0], '[/i]'))) {
					$theText = preg_replace('#\[i]#', '<span style="font-style: italic;">', $theText);
					$theText = preg_replace('#\[/i]#', '</span>', $theText);
				}
				
				// handle underline tags
				if (count(array_keys($open_count[0], '[u]')) == count(array_keys($close_count[0], '[/u]'))) {
					$theText = preg_replace('#\[u]#', '<span style="text-decoration: underline;">', $theText);
					$theText = preg_replace('#\[/u]#', '</span>', $theText);
				}
				
					// Check if emoticons are disabled
					if ($this->fconf['emoticons_disable'] == false) {
						$emoticons = array();
						$searchEmoticons = array();
						$replaceEmoticons = array();
						// Load default emoticons if individual icons are empty or should be added to default icons
						if (empty($this->fconf['emoticons_type']) || $this->fconf['emoticons_add']) {
							// Define images and search codes
							$emoticons = array(':arrow:' => 'arrow.gif',
		  									     ':badgrin:' => 'badgrin.gif',
											 	 ':D' => 'biggrin.gif',
												 ':?' => 'confused.gif',
												 '8)' => 'cool.gif',
												 ':(' => 'cry.gif',
												 ':doubt:' => 'doubt.gif',
												 ':evil:' => 'evil.gif',
												 ':!:' => 'exclaim.gif',
												 ':idea:' => 'idea.gif',
												 ':lol:' => 'lol.gif',
												 ':mad:' => 'mad.gif',
												 ':neutral:' => 'neutral.gif',
												 ':question:' => 'question.gif',
												 ':razz:' => 'razz.gif',
												 ':oops:' => 'redface.gif',
												 ':roll:' => 'rolleyes.gif',
												 ':(' => 'sad.gif',
												 ':shock:' => 'shock.gif',
												 ':)' => 'smile.gif',
												 ':o' => 'surprised.gif',
												 ':wink:' => 'wink.gif');
						}
						// Parse individual emoticons
						if (!empty($this->fconf['emoticons_type'])) {
							// Split fconf value in lines
							$emicoValues = explode("\n", $this->fconf['emoticons_type']);
							// Split lines in emoticon code and image name
							reset($emicoValues);
							while (list(, $emicoLine) = each($emicoValues)) {
								$emicoTemp = explode(',', $emicoLine);
								$emoticons[trim($emicoTemp[0])] = trim($emicoTemp[1]);
							}
						}
						// Get emoticon path
						$emicoPath =$this->fconf['emoticons_path'];
     	
						// Prepare search and replace array
						reset($emoticons);
						while (list($emicoKey, $emicoValue) = each($emoticons)) {
							$searchEmoticons[] = $emicoKey;
							$replaceEmoticons[] = $this->cObj->IMAGE(array('file' => $emicoPath.$emicoValue,
														  				   'border' => '0'));
						}
						// Replace all emoticon codes with images
						$theText = str_replace($searchEmoticons, $replaceEmoticons, $theText);
					}

				// add breaks to the text
				$theText = nl2br($theText);

				// we've parsed the text, now store it in the cache.
				if ($this->uid) $this->update_post_cache($theText);	

				// return the text.
				$theText = kses($theText, $this->fconf['allowed'], array('http', 'https'));

				return $theText;
				} else {
				return false;
			}
		}
		
		function unhide() {
			if ($this->user->can_mod_conf($this->conference_id)) {
				$data_arr['hidden'] = 0;
				$table = 'tx_chcforum_post';
				$where = "uid = $this->uid";
				$qresults = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$data_arr);
			}
		}
		
		function hide() {
			if ($this->user->can_mod_conf($this->conference_id)) {
				$data_arr['hidden'] = 1;
				$table = 'tx_chcforum_post';
				$where = "uid = $this->uid";
				$qresults = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$data_arr);
			}			
		}

		function update_post_cache($theText) {
			if ($this->uid) {
				$tstamp = time();
				$data_arr = array();
				$data_arr['cache_parsed_text'] = $theText;
				$data_arr['cache_tstamp'] = $tstamp;
				$data_arr['tstamp'] = $tstamp;
				$table = 'tx_chcforum_post';
				$where = "uid = $this->uid";
				$qresults = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$data_arr);
			}
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_post.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_post.php']);
	}
	 
?>
