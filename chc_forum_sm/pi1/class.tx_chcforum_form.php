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
	
	
require_once(t3lib_extMgm::extPath('comment_notify').'pi1/class.tx_commentnotify_pi1.php');
	
	
	/**
	* The form class is responsible for displaying the form and handling
	* the form submission. All insertions to the post, conference, category,
	* and thread tables take place in this class, in the submit function. The
	* only method that gets called publicly is the handle_form method -- more or
	* less everything else takes place internally (at least, that was the original
	* plan -- I need to look back over this class and make sure that's still the
	* case).
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_form extends tx_chcforum_pi1 {
		 
		var $name; // post author name
		var $username;
		var $email; // post author email
		var $fe_user_uid;
		 
		// following get passed via post and are inherited from display object.
		var $view;
		var $cat_uid;
		var $conf_uid;
		var $thread_uid;
		var $where;
		var $stage;
		var $preview;
		var $text;
		var $subject;
		 
		var $button; // text for the button
		var $label_array; // a constant, set in the constructor for the class.
		var $action; // a constant, refers to form action url.
		var $cObj;
		
		var $html_out;

 		// Emoticon code container
 		var $emoticonCode = null;
		 
		// For dev purposes ONLY
#		var $lorem_forum = 10000;
#		var $lorem_forum_count = 1;
		
		/** An instance of tx_chcforum_display.
		For the purpose of calling get_page_count()
		@author Jaspreet Singh */
		var $display_object;
		
	 /**
	  * Constructor for form object. The entire display object gets passed here.
	  * This is probably inefficient, and it should probably be changed.
		*
		* @param object $display_object: the display object gets passed to this form because we need to many of it's properties to process the form correctly. However, this can probably be trimmed down somewhat.
		* @return void
	  */
		function tx_chcforum_form($display_object) {
			 
			//Cache the display_object so we can call get_page_count() on it later
			//Jaspreet Singh
			$this->display_object = $display_object;
			
			
			// Setting the form constants
			$this->cObj = $display_object->cObj;
			$this->conf = $this->cObj->conf;

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;
			#if (intval(phpversion()) < 5) unset($this->cObj->fconf);

			// bring in the user object.
			$this->user = $this->fconf['user'];

			$this->internal['results_at_a_time'] = 1000;

			// get any params from typoscript -- used for forum / tt_news integration.
			if ($this->conf['chcAddParams']) {
    			$paramArray = tx_chcforum_shared::getAddParams($this->conf['chcAddParams']);
			}	
			$this->action = htmlspecialchars($this->pi_getPageLink($GLOBALS['TSFE']->id,'',$paramArray));
			$this->label_array = array ('label_name' => tx_chcforum_shared::lang('form_label_name'),
				'label_author_and_subject' => tx_chcforum_shared::lang('form_label_author_and_subject'),
				'label_email' => tx_chcforum_shared::lang('form_label_email'),
				'label_subject' => tx_chcforum_shared::lang('form_label_subject'),
				'label_post' => tx_chcforum_shared::lang('form_label_post'),
				'form_help' => tx_chcforum_shared::lang('form_help'),
				'label_instruct' => tx_chcforum_shared::lang('form_label_instruct'),
				'b_help' => tx_chcforum_shared::lang('form_b_help'),
				'i_help' => tx_chcforum_shared::lang('form_i_help'),
				'u_help' => tx_chcforum_shared::lang('form_u_help'),
				'q_help' => tx_chcforum_shared::lang('form_q_help'),
				'c_help' => tx_chcforum_shared::lang('form_c_help'),
				'p_help' => tx_chcforum_shared::lang('form_p_help'),
				'w_help' => tx_chcforum_shared::lang('form_w_help'),
				'a_help' => tx_chcforum_shared::lang('form_a_help'),
				's_help' => tx_chcforum_shared::lang('form_s_help'),
				'f_help' => tx_chcforum_shared::lang('form_f_help'),
				'b_value' => tx_chcforum_shared::lang('form_b_value'),
				'i_value' => tx_chcforum_shared::lang('form_i_value'),
				'u_value' => tx_chcforum_shared::lang('form_u_value'),
				'q_value' => tx_chcforum_shared::lang('form_q_value'),
				'p_value' => tx_chcforum_shared::lang('form_p_value'),
				'w_value' => tx_chcforum_shared::lang('form_w_value'),
				'close_tags' => tx_chcforum_shared::lang('form_close_tags'),
				'missing_subject' => tx_chcforum_shared::lang('form_missing_subject'),
				'missing_message' => tx_chcforum_shared::lang('form_missing_message')
			);
			
			// Getting the current user information.
			$this->name = $display_object->name;
			$this->rawName = $display_object->rawName;
			$this->email = $display_object->email;	 

			// we prefer name to be the full name, but we'll settle for username if name isn't available.
			// if anonymous write access is allowed, the name can be anything, and we're not going to 
			// check it.
			if ($this->user->uid or $display_object->conference->conference_public_w == false) {
				if ($this->fconf['use_username'] == true) {
					$this->name = $this->user->username;
				} else {
					if ($this->user->name) {
						$this->name = $this->user->name;
					} else {
						if ($this->user->username) {
							$this->name = $this->user->username;
						}
					}
				}
			} else {
				
			}
			
			// set the user email -- why?
			if ($this->user->email) $this->email = $this->user->email;
			
			// set username and fe_user_uid -- why? 
			$this->username = $this->user->username;
			$this->fe_user_uid = $this->user->uid;
						 
			// Setting any vars that might have been sent via post.
			$this->view = $display_object->view;
			$this->cat_uid = $display_object->cat_uid;
			$this->conf_uid = $display_object->conf_uid;
			$this->thread_uid = $display_object->thread_uid;
			$this->post_uid = $display_object->post_uid;
			$this->where = $display_object->where;
			$this->preview = $display_object->preview;
			$this->subject = $display_object->subject;
			$this->rawSubject = $display_object->rawSubject;
			$this->cancel = $display_object->cancel;
			$this->text = $display_object->text;
			$this->rawText = $display_object->rawText;
			$this->submit = $display_object->submit;
			$this->flag = $display_object->flag;
			$this->page = $display_object->page;
			$this->attachment = $display_object->files['attachment'];
			$this->attach_file_name = $display_object->attach_file_name;
			$this->thread_endtime = $display_object->thread_endtime;
			
			// Check that the file was in fact uploaded.
			if ($this->attach_file_name) {
				$file_path = 'uploads/tx_chcforum/'.$this->attach_file_name;
				$uploadfile = t3lib_div::getFileAbsFileName($file_path);
				if (file_exists($uploadfile)) $this->file_was_uploaded = true;
			}
			
			// Set the stage for the form (not sure if I'm still using this...)
			if ($display_object->stage) {
				$this->stage = $display_object->stage;
			} else {
				$this->stage = 'new';
			}
			
			// If it we're quoting, add the quoted text to $this->text
			if ($this->flag == "quote" && $this->post_uid) {
				$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
				$quoted_post = new $tx_chcforum_post($this->post_uid, $this->cObj);
				$this->text = '[quote='.$quoted_post->post_author_name.']'.$quoted_post->post_text.'[/quote]';
			}
			 
			// If we're editing a post, make sure that the user is authorized to edit -- if so, fill in the form.
			if ($this->view == "edit_post" && $this->post_uid && $this->user->can_edit_post($this->post_uid) == true) {
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$this->message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('edit_msg'), 'message');
				$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
				$edit_post = new $tx_chcforum_post($this->post_uid, $this->cObj);
				if (empty($this->subject)) $this->subject = $edit_post->post_subject;
				if (empty($this->text)) $this->text = $edit_post->post_text;
				$tx_chcforum_author= t3lib_div::makeInstanceClassName("tx_chcforum_author");
				$this->author = new $tx_chcforum_author($edit_post->post_author, $this->cObj);				
				if ($this->author->uid) {
					$this->name = $edit_post->post_author_name;
					$this->username = $this->author->username;
					$this->email = $edit_post->post_author_email;
				} else {
					$this->name = $edit_post->post_author_name; 
					$this->email = $edit_post->post_author_email;
					$this->username = $this->author->username;
					if (!$this->username) $this->username = tx_chcforum_shared::lang('guest');
				}
			}
			 
			// If the user can attach files, add the label to the label array
			if ($this->conf_uid && $this->user->can_attach_conf($this->conf_uid) == true && $this->view != 'edit_post') {
				$this->label_array['label_attachment'] = tx_chcforum_shared::lang('form_label_attachment');
			}

			// is the current conference set to hide new posts?
			if ($display_object->conference->hide_new == true) {
				$this->hide_new = true;
			}


			// Set up the emoticons
 			// Check if emoticons are disabled
 			if ($this->fconf['emoticons_disable'] == false) {
 				$emoticons = array();
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

 				// Generate emoticon html image tags
 				reset($emoticons);
 				while (list($emicoKey, $emicoValue) = each($emoticons)) {
 					$this->emoticonCode .= $this->cObj->IMAGE(array('file' => $emicoPath.$emicoValue,
												  				'border' => '0',
 																	'alttext' => $this->pi_getLL('emoticons.'.$emicoValue, 'Emoticon'),
 																	'params' => 'class="tx-chcforum-pi1-formEmicoStyle" onclick="emoticon(\''.$emicoKey.'\')"'));
 				}
 			}
		}
		 
	 /**
		* Used to debug the form -- needs updating
		*
		* @return string  html table with debug information.
		*/
		function debug_form () {
			print '<table>';
			print '<tr><td>Name:</td><td>'.$this->name.'</td></tr>';
			print '<tr><td>Username:</td><td>'.$this->username.'</td></tr>';
			print '<tr><td>email:</td><td>'.$this->email.'</td></tr>';
			print '<tr><td>fe_user_uid:</td><td>'.$this->fe_user_uid.'</td></tr>';
			print '<tr><td>view:</td><td>'.$this->view.'</td></tr>';
			print '<tr><td>cat_uid:</td><td>'.$this->cat_uid.'</td></tr>';
			print '<tr><td>conf_uid:</td><td>'.$this->conf_uid.'</td></tr>';
			print '<tr><td>thread_uid:</td><td>'.$this->thread_uid.'</td></tr>';
			print '<tr><td>post_uid:</td><td>'.$this->post_uid.'</td></tr>';
			print '<tr><td>stage:</td><td>'.$this->stage.'</td></tr>';
			print '<tr><td>preview:</td><td>'.$this->preview.'</td></tr>';
			print '<tr><td>subject:</td><td>'.$this->subject.'</td></tr>';
			print '<tr><td>text:</td><td>'.$this->text.'</td></tr>';
			print '<tr><td>button:</td><td>'.$this->button.'</td></tr>';
			print '<tr><td>crdate:</td><td>'.$this->crdate.'</td></tr>';
			print '<tr><td>submit:</td><td>'.$this->submit.'</td></tr>';
			print '<tr><td>cancel:</td><td>'.$this->cancel.'</td></tr>';
			print '<tr><td>validated:</td><td>'.$this->validated.'</td></tr>';
			print '<tr><td>page:</td><td>'.$this->page.'</td></tr>';
			print '<tr><td>flag:</td><td>'.$this->flag.'</td></tr>';
			print '<tr><td>where:</td><td>'.$this->where.'</td></tr>';
			print '</table>';
		}
		 
	 /**
	  * Public interface for the form object. This is the only method that gets called
	  * outside of the class.
		*
		* @param boolean $debug set to true to debug the form submission / handling / validation / etc.
		* @return void
    */
		function handle_form($debug = false) {
			// If the incoming submit variable equals cancel, unset the form object completely.
			if ($this->cancel) {
				$this->reset_form();
				// if a file was uploaded, get rid of it
				$file_path = 'uploads/tx_chcforum/'.$this->attach_file_name;
				$uploadfile = t3lib_div::getFileAbsFileName($file_path);
				if (file_exists($uploadfile)) @unlink($uploadfile);
					$this->stage = 'new';
			}
			 
			// set crdate for the form object
			$this->crdate = time();
			if ($debug == true) $this->debug_form();
				 
			if ($this->stage) {
				if ($this->flag == 'delete') $this->process_delete_flag();
					switch ($this->stage) {
					// The form was submitted
					case 'previewed':
					// If it's valid, we can submit the post to the DB
					$this->validate();
					if ($this->validated == true) {
						// Submit post to DB
						$this->submit();

						// used solely for dev purposes
						if ($this->lorem_forum == true) {
							while ($this->lorem_forum_count <= $this->lorem_forum) {
								$this->submit();
								$this->lorem_forum_count++;
							}					
						}
						// end dev routine

					} else {
						// tell the user why it failed
						$this->errors();
					}
					break;
					case 'posted':

					//was a preview requested? If it was...
					if ($this->preview == 1) {
						// We need to validate it
						$this->validate();
						// It passed validation
						if ($this->validated == true) {
							// Display the requested preview
							$this->preview();
							// It failed validation
						} else {
							// tell the user why it failed
							$this->html_out .= $this->errors();
						}
						// no preview requested
					} else {
						// Looks like a preview was not requested. In that case, we need to validate it
						$this->validate();
						// If it's valid, we can submit the post to the DB
						if ($this->validated == true) {
							// Submit post to DB
							$this->submit();

							// used solely for dev purposes
							if ($this->lorem_forum == true) {
								while ($this->lorem_forum_count <= $this->lorem_forum) {
									$this->submit();
									$this->lorem_forum_count++;
								}					
							}
							// end dev routine
							
						} else {
							// tell the user why it failed
							$this->html_out .= $this->errors();
						}
					}
					break;
					case 'new':
					// First time pulling up the form -- display it. Set stage to posted first -- gets written into form HTML
					#$this->stage = 'posted';
					break;
					default:
					// First time pulling up the form -- display it.
					break;
				}
			}
			return $this->html_out;
		}
		 
	 /**
		* Display a preview of a post.
		*
		* @return void
		*/
		function preview() {
			$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
			$preview_post = new $tx_chcforum_post('', $this->cObj, true);
			$preview_post->preview = true;
			$preview_post->post_author_name = $this->name;
			$preview_post->post_subject = $this->subject;
			$preview_post->post_text = $this->text;
			$preview_post->crdate = $this->crdate;
			$preview_post->conf_auth = true;
			$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
			$preview_message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('preview_message'), 'message');
			$this->html_out .= $preview_message->display();
			$this->html_out .= $preview_post->display_post();
			$this->stage = 'previewed';
			$this->is_preview = true;
		}
		 
	 /**
		* Resets the form vars. Used on cancel.
		*
		* @return void
		*/
		function reset_form() {
			unset($this->subject);
			unset($this->text);
			unset($this->thread_endtime);
			$this->stage = 'new';
			// if the view was single_post, and the reply has been submitted, we need to
			// go back to the single_thread view -- and we need to jump to the last page!
			if ($this->flag == 'delete') $this->view = 'single_conf';
			if ($this->view == 'single_post') $this->view = 'single_thread';
			if ($this->view == 'single_thread') $this->flag = 'last';
			if ($this->view == 'single_conf') $this->flag = 'first';
		}
		 
	 /**
		* Handles and outputs any errors from form submission / validation.
		*
		* @return void
		*/
		function errors() {
			if ($this->error) {
				$msg_txt .= tx_chcforum_shared::lang('validate_error_hdr');
				foreach ($this->error as $error) {
					$msg_txt .= $error.'<br />';
				}
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$message = new $tx_chcforum_message($this->cObj, $msg_txt, 'message');
				$out .= $message->display();
			}
			return $out;
		}
		 
	 /**
		* Form validation method.
		*
		* @return void
		*/
		function validate() {
			$this->validated = false;

			// make sure that we have a name, text, subject, and email address for the message.
			if (empty($this->name)) {
				$this->error['name'] = tx_chcforum_shared::lang('validate_name');
			} else {
				$valid_name = true;
			}
			if (empty($this->text)) {
				$this->error['text'] = tx_chcforum_shared::lang('validate_text');
			} else {
				$valid_text = true;
			}
			if (empty($this->subject)) {
				$this->error['subject'] = tx_chcforum_shared::lang('validate_subject');
			} else {
				$valid_subject = true;
			}
			if (empty($this->email)) {
				 // unless fconf is set to optional email, return error
				 if ($this->fconf['req_email'] != true) {
					 $this->error['email'] = tx_chcforum_shared::lang('validate_email');
				 } else {
				 	 $valid_email = true;
				 }
			} else {
				$valid_email = true;
			}
		
			// validate thread_endtime
			if ($this->fconf['allow_thread_endtime'] == 1 && $this->where == 'single_conf') {
				$thread_endtime = intval($this->thread_endtime);
				$max = $this->fconf['max_thread_lifetime'];
				if ($max > 0) {
					if ($thread_endtime > $max) $thread_endtime = $max;
				}
				if ($thread_endtime < 0) $thread_endtime = 0;
				$this->thread_endtime = $thread_endtime;
			} else {
				unset ($this->thread_endtime);
			}
			
			// if there's a default endtime, and now other value is set, set it.
			if ($this->fconf['thread_lifetime'] && !$this->thread_endtime) {
				$this->thread_endtime = intval($this->fconf['thread_lifetime']);
			}
			 
			// i'm not running further validation on these vars, since they should get validated
			// in display.
			if (!empty($this->cat_uid) && is_numeric($this->cat_uid)) $valid_catuid = true;
			if (!empty($this->conf_uid) && is_numeric($this->conf_uid)) $valid_confuid = true;

			// Attachment validation...
			if ($this->attachment && $this->attachment['name'] && $this->attachment['type'] && $this->attachment['tmp_name'] && $this->attachment['size'] && $this->attachment['error'] == 0) {

				$valid_attachment = false;
				// check the $tmp_name and $name path strings
				if (t3lib_div::validPathStr(t3lib_div::fixWindowsFilePath($this->attachment['tmp_name'])) == true ) {
					$valid_1 = true;
				} else {
					$this->error['attach_upload'] = tx_chcforum_shared::lang('validate_upload');
				}
				if (t3lib_div::validPathStr(t3lib_div::fixWindowsFilePath($this->attachment['name'])) == true ) {
					$valid_2 = true;
				} else {
					$this->error['attach_upload'] = tx_chcforum_shared::lang('validate_upload');
				}
				 
				// check the size
				if (!$this->fconf['max_attach']) $this->fconf['max_attach'] = (100 * 1024); // if we don't have a max size, set it to 100k
				if ($this->attachment['size'] <= ($this->fconf['max_attach'] * 1024)) {
					$valid_3 = true;
				} else {
					$this->error['attach_size'] = tx_chcforum_shared::lang('validate_size');
				}
				 
				// make sure it's uploaded
				if (is_uploaded_file($this->attachment['tmp_name']) == true) {
					$valid_4 = true;
				} else {
					$this->error['attach_upload'] = tx_chcforum_shared::lang('validate_upload');
				}
				 
				// check errors
				if ($this->attachment['error'] == 0) {
					$valid_5 = true;
				} else {
					$this->error['attach_upload'] = tx_chcforum_shared::lang('validate_upload');
				}
	 		 
				// check file extension -- get from fconf. Add any errors to $this->error['attachment']
				$extension = strtolower(substr(strrchr($this->attachment['name'], '.'), 1));
				$allowed = explode(',',strtolower($this->fconf['allowed_file_types']));
				if ($allowed) foreach ($allowed as $key=>$value) $allowed[$key]=trim($value);
				if (is_array($allowed) && in_array($extension, $allowed)) {
					$valid_6 = true;
				} else {
					$this->error['attach_type'] = tx_chcforum_shared::lang('validate_type');
				}

				// check mime type -- get from fconf.
				$mime = $this->attachment['type'];
				$allowed = explode(',',strtolower($this->fconf['allowed_mime_types']));
				if ($allowed) foreach ($allowed as $key=>$value) $allowed[$key]=trim($value);
				if (is_array($allowed) && in_array($mime, $allowed)) {
					$valid_7 = true;
				} else {
					$this->error['attach_type'] = tx_chcforum_shared::lang('validate_type');
				}
				 
				if ($valid_1 && $valid_2 && $valid_3 && $valid_4 && $valid_5 && $valid_6 && $valid_7) $valid_attachment = true;
			} else {
				// either no file was set, or the file that was sent was not valid, so we need to unset the attachment post.
				unset($this->attachment);
				$valid_attachment = true;
				$no_attachment = true;
			}
			 
			// we have to move the file here or else it will be deleted from the temporary location.
			if ($valid_attachment == true && !$this->attach_file_name && !$no_attachment) {
				$attach_file_name = $this->attachment['name'];
				$file_path = 'uploads/tx_chcforum/'.$attach_file_name;
				$uploadfile = t3lib_div::getFileAbsFileName($file_path);
				$tmp_file = t3lib_div::upload_to_tempfile($this->attachment['tmp_name']);
				 
				while (file_exists($uploadfile)) {
					$i++;
					$file_path = 'uploads/tx_chcforum/'.$i.$attach_file_name;
					$uploadfile = t3lib_div::getFileAbsFileName($file_path);
				}
				$this->attach_file_name = $i.$attach_file_name;
				 
				t3lib_div::upload_copy_move($tmp_file, $uploadfile);
				if (file_exists($uploadfile)) $this->file_was_uploaded = true;
				if ($tmp_file) t3lib_div::unlink_tempfile($tmp_file);
			}
			 
			if ($valid_name == true && $valid_text == true && $valid_subject == true && $valid_email == true && $valid_catuid == true && $valid_confuid == true && $valid_attachment == true) $this->validated = true;
			 
			// at this point, the post is generally valid...
			 
			// if we're in edit view and the user can't mod this conf, then we won't validate this post.
			if ($this->where == "edit_view" && $this->user->can_mod_conf($this->conf_uid) != true) $this->validated = false;
		}
		 
	 /** 
		* Method that creates HTML for the form. View is where the form is placed.
		*
		* @return void
		*/
		function display_form() {
			
			// decide what label to put at the top of the form, in the legend
			switch ($this->view) {
				case "single_conf":
					$this->label_array['label_where'] = tx_chcforum_shared::lang('form_label_where_conf');
				break;
				
				case "single_post":
					$this->label_array['label_where'] = tx_chcforum_shared::lang('form_label_where_post');
				break;

				case "edit_post":
					$this->label_array['label_where'] = tx_chcforum_shared::lang('form_label_where_edit');
				break;
				
				case "single_thread":
					$this->label_array['label_where'] = tx_chcforum_shared::lang('form_label_where_thread');
				break;				
				
				case "default":
					$this->label_array['label_where'] = tx_chcforum_shared::lang('form_label_where_default');					
				break;
			}

			// We're not doing anything if the user isn't authorized to view the conf in which
			// The form is being displayed.
			if ($this->conf_uid && $this->user->can_write_conf($this->conf_uid) == true) {
				// Set up Javascript
				if (!$this->tmpl_path) $this->tmpl_path = tx_chcforum_shared::setTemplatePath();
					$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
					$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'post_form.js');
				$tmpl->prepare();
				$tmpl->assign($this->label_array);
				// Output Javascript
				$this->html_out .= $tmpl->getOutputContent();
				 
				// Set up HTML template
				if (!$this->tmpl_path) $this->tmpl_path = tx_chcforum_shared::setTemplatePath();
					$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
					$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'post_form.tpl');
				$tmpl->prepare();
				$tmpl->assign($this->label_array);

				// Set form action.
				$tmpl->assign('action', $this->action);
				 
				// Decide what to put in the name and author field -- if there's a user logged in,
				// fill it in automatically. Otherwise render the input fields for username and email.
				if ($this->user->uid) {
					if ($this->fconf['use_username'] == true) {
						$tmpl->assign('username',$this->username);		
					} else {
						$tmpl->assign('name', $this->name);
						$tmpl->assign('username', '['.$this->username.']');
					}
					$tmpl->assign('email', $this->cObj->getTypoLink($this->email, $this->email));
				} else {
					if ($this->rawName) {
						$name = $this->rawName;
					} else {
						$name = $this->name;
					}
					$tmpl->assign('name', '<input type="text" maxlength="50" size="40" name="name" value="'.t3lib_div::htmlspecialchars_decode($name).'" />');
					$tmpl->assign('email', '<input type="text" maxlength="100" size="40" name="email" value="'.t3lib_div::htmlspecialchars_decode($this->email).'" />');
				}
				 
				// Stick all this info in here -- all the ids get validated anyways (and it isn't safe
				// to trust them anyhow, so they might be approaching unnecessary.
				$tmpl->assign('hash_fe_user_uid', tx_chcforum_shared::makeHash($this->fe_user_uid));
				$tmpl->assign('hash_cat_uid', tx_chcforum_shared::makeHash($this->cat_uid));
				$tmpl->assign('hash_conf_uid', tx_chcforum_shared::makeHash($this->conf_uid));
				$tmpl->assign('hash_thread_uid', tx_chcforum_shared::makeHash($this->thread_uid));
				$tmpl->assign('hash_post_uid', tx_chcforum_shared::makeHash($this->post_uid));
				$tmpl->assign('hash_view', tx_chcforum_shared::makeHash($this->view));
				$tmpl->assign('page', $this->page);
				$tmpl->assign('button', $this->button);
				$tmpl->assign('where', $this->where);

				// if posts in this conference are hidden until approved,
				// display a message to that effect.
				if ($this->hide_new == true) {
					$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
					$message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('form_hide_new'), 'error');
					$tmpl->assign('message',$message->display());
				}		

 				// Assign emoticon code to template
				$tmpl->newBlock('emoticons');
 				$tmpl->assign('emoticons', $this->emoticonCode);
				$tmpl->gotoBlock('_ROOT');

				// Assign thread enddate code to template
				if ($this->fconf['allow_thread_endtime'] == 1 && $this->where == 'single_conf') {
					$tmpl->newBlock('thread_endtime');
					$tmpl->assign('label_endtime',tx_chcforum_shared::lang('form_label_endtime'));
					$tmpl->assign('endtime_inpt', '<input type="text" name="thread_endtime" value="'.$this->thread_endtime.'" size="5" />');
					if ($this->fconf['max_thread_lifetime']) {
						$max_str = tx_chcforum_shared::lang('form_label_max_endtime');
						$max_str = str_replace('###num###',$this->fconf['max_thread_lifetime'],$max_str);
						$tmpl->assign('label_max_endtime',$max_str);
					}
					$tmpl->gotoBlock('_ROOT');					
				}
				
				// set up paths for buttons
				$tmpl->assign('img_path', $this->fconf['tmpl_img_path']);
				
				// Set up the default subject for new posts.
				if (empty($this->subject) && $this->thread_uid) {
					$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
					$thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
					$this->subject = tx_chcforum_shared::lang('reply_label').' '.$thread->thread_subject;
				}
				
				// If we already have data for subject or post, stick it in the fields (probably
				// means we're editing or previewing.
				// note that we have to decode everything that goes back in a form field (actually,
				// do we need this for subject field?)

				if ($this->rawText) { 
					$text = $this->rawText; 
				} else {
					$text = $this->text;
				}
				if ($this->rawSubject) { 
					$subject = $this->rawSubject; 
				} else {
					$subject = $this->subject;
				}
				

				$tmpl->assign('subject', $subject);
				$tmpl->assign('text', $text);
				
				// Stage tracking -- set the form stage correctly.
				if ($this->stage == "new" or $this->stage == 'previewed') {
					$tmpl_stage = 'posted';
					$tmpl->assign('stage', 'posted');
				} else {
					$tmpl_stage = $this->stage;
					$tmpl->assign('stage', $this->stage);
				}

				// setup post and preview buttons
				if ($this->view == 'edit_post') {
					$submit_btn_text = tx_chcforum_shared::lang('display_form_btn_edit');
				} else {
					$submit_btn_text = tx_chcforum_shared::lang('display_form_btn_post');
				}
				$tmpl->assign('submit', $submit_btn_text);
				$tmpl->assign('preview_btn', tx_chcforum_shared::lang('display_form_btn_preview'));

				// set up attachments and cancel button. behaviour depends on whether or not
				// we're previewing.
				if ($this->stage == 'previewed') {
					// previewing
					if ($this->user->can_attach_conf($this->conf_uid) == true) {
						if (!$this->attach_file_name) {
							// user can attach and no attachment has been added yet
							$tmpl->assign('attach_inpt', '<input type="file" name="attachment" size="45" />');
						} else {
							// user can attach and attachment was added -- carry it over
							if ($this->attachment['name']) $tmpl->assign('attach_inpt', $this->attachment['name']. '<input type="hidden" name="attach_file_name" value="'.$this->attach_file_name.'" />');
							if ($this->attach_file_name) $tmpl->assign('attach_inpt', $this->attach_file_name. '<input type="hidden" name="attach_file_name" value="'.$this->attach_file_name.'" />');
						}	
					}
					$tmpl->newBlock('cancel');
					$tmpl->assign('cancel_confirm', tx_chcforum_shared::lang('display_form_cancel_confirm'));
					$tmpl->assign('cancel', tx_chcforum_shared::lang('display_form_btn_cancel'));
				} else {
					// not previewing
					if ($this->user->can_attach_conf($this->conf_uid) == true && $this->view != 'edit_post') {
						// not previewing -- include attachment input
						$tmpl->assign('attach_inpt', '<input type="file" name="attachment" size="45" />');
					}
				}



				// Output HTML countent
				$this->html_out .= $tmpl->getOutputContent();
			} else {
				// didn't pass authorization -- output an error message.	 
				$message_text = tx_chcforum_shared::lang('error_no_user');
				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
				$message = new $tx_chcforum_message($this->cObj, $message_text, 'error');
				$this->html_out = $message->display();
				
			}

			return $this->html_out;
		}
		 
		/**
		* Submits form data to the database -- called ONLY after validation is successful.
		*
		* @param boolean $update: set to true if we're updating rather than inserting.
		* @return void
		*/
		function submit($update = false) {
			//jsdebug
			if (false) {
				$uri =  $this->getThreadURI();
				echo $uri;
				exit();
				echo "submit() \n";
				echo "cat_uid $this->cat_uid \n";
				echo "conf_uid $this->conf_uid \n";
				echo "thread_uid $this->thread_uid \n";
				$FIRST_PAGE = 1;
				//Get the last page, unless display_object isn't valid,
				//in which case we just get the first page
				$pages2 = empty( $this->display_object )
					? $FIRST_PAGE
					: $this->display_object ->get_page_count()
					;
				echo "pages $pages2 \n";
				$pid2 = $GLOBALS['TSFE']->id;
				echo "pid: $pid2 \n";
				$params = array(
					"view" => "single_thread"
					,"cat_uid" => $this->cat_uid
					,"conf_uid" => $this->conf_uid
					,"thread_uid" => $this->thread_uid
					,"page" => $pages2
					);
	
				
				$url2 = htmlspecialchars($this->cObj->getTypoLink_URL($pid2,$params)); // run it through special chars for XHTML compliancy
				
				echo "URL: $url2 \n";
				exit();
			}
			
			
			
			// Is the user allowed to write to this conference?
			if ($this->user->can_write_conf($this->conf_uid) != true) return false;
			// Is this thread closed?			
			if (in_array($this->thread_uid, $this->user->closed_threads)) return false;

			// Constants for all queries
			// $pid = $GLOBALS['TSFE']->id; OLD METHOD
			$pid = $this->conf['pidList']; // new method
			switch ($this->where) {
				// if where equals single_conf, that means were posting a new $tx_chcforum_post while viewing a single conf -- so we need a new $tx_chcforum_post, and a new $tx_chcforum_thread.
				case 'single_conf':
				// Add the post to the post table
				$table = 'tx_chcforum_post';
				$field_list = 'category_id,
					conference_id,
					post_author,
					post_author_name,
					post_author_email,
					post_subject,
					post_author_ip,
					post_edit_tstamp,
					post_edit_count,
					post_attached,
					post_text,
					hidden';
				$dataArr = array();
				$dataArr['category_id'] = $this->cat_uid;
				$dataArr['conference_id'] = $this->conf_uid;
				$dataArr['post_author'] = $this->fe_user_uid;
				$dataArr['post_author_name'] = $this->name;
				$dataArr['post_author_email'] = $this->email;
				$dataArr['post_subject'] = $this->subject;
				$dataArr['post_author_ip'] = t3lib_div::getIndpEnv('REMOTE_ADDR');
				$dataArr['post_edit_tstamp'] = 0;
				$dataArr['post_edit_count'] = 0;
				if ($this->hide_new == true) $dataArr['hidden'] = 1;
				if ($this->file_was_uploaded == true) $dataArr['post_attached'] = $this->attach_file_name;
				$dataArr['post_text'] = $this->text;

				// Search for same author, same subject, same conf, same cat, less than five minutes ago
				// probably a mistaken double-submit
				if ($this->fe_user_uid && $this->cat_uid && $this->conf_uid) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $table, 
										      "category_id=".$this->cat_uid.
										      " AND conference_id=".$this->conf_uid.
										      " AND post_author=".$this->fe_user_uid.
										      " AND post_subject='".mysql_escape_string($this->subject)."'".
										      " AND tstamp>".(time()-60*5));
				} elseif ($this->cat_uid && $this->conf_uid) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $table, 
										      "category_id=".$this->cat_uid.
										      " AND conference_id=".$this->conf_uid.
										      " AND post_subject='".mysql_escape_string($this->subject)."'".
										      " AND tstamp>".(time()-60*5));
				}
				
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				  break;
				} 

				$query = $this->cObj->DBgetInsert($table, $pid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					if ($GLOBALS['TYPO3_DB']->sql_affected_rows() > 0) $postid = $GLOBALS['TYPO3_DB']->sql_insert_id();
					 
				// Add a new $tx_chcforum_thread to the thread table
				$table = 'tx_chcforum_thread';
				$field_list = 'category_id,
					conference_id,
					thread_subject,
					thread_author,
					thread_datetime,
					thread_views,
					thread_replies,
					thread_firstpostid,
					thread_lastpostid,
					endtime,
					hidden';
				$dataArr = array();
				$dataArr['category_id'] = $this->cat_uid;
				$dataArr['conference_id'] = $this->conf_uid;
				$dataArr['thread_subject'] = $this->subject;
				$dataArr['thread_author'] = $this->fe_user_uid;
				$dataArr['thread_datetime'] = $this->crdate;
				$dataArr['thread_views'] = 0;
				$dataArr['thread_replies'] = 0;
				$dataArr['thread_firstpostid'] = $postid;
				$dataArr['thread_lastpostid'] = $postid;
				$dataArr['thread_lastposttstamp'] = time();
				
				// deal with endtime
				if ($this->thread_endtime) {
					$now = time();
					$add = $this->thread_endtime * 24 * 60 * 60; // 24 hours per day, 60 minutes per hour, 60 seconds per minute.
					$endtime = $now + $add;
				}
				$dataArr['endtime'] = $endtime;

				// deal with default endtime

				if ($this->hide_new == true) $dataArr['hidden'] = 1;
				$query = $this->cObj->DBgetInsert($table, $pid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);

				if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					if ($GLOBALS['TYPO3_DB']->sql_affected_rows() > 0) $threadid = $GLOBALS['TYPO3_DB']->sql_insert_id();
				// Update the post with the thread_id number
				$table = 'tx_chcforum_post';
				$uid = $postid;
				$field_list = 'thread_id';
				$dataArr['thread_id'] = $threadid;
				$query = $this->cObj->DBgetUpdate($table, $uid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
				
				// consider the new post read by the current user.
				if ($this->user->uid && $postid) {
					$this->user->add_post_read($postid);
				}
				break;
				 
				// If we're posting in a thread that has already been created, then we're posting
				// a reply, and we don't need to add a thread to the thread table (although we do
				// need to update it.
				case 'single_thread':
				case 'single_post':
				// Add the post to the post table
				$table = 'tx_chcforum_post';
				$field_list = 'category_id,
					conference_id,
					thread_id,
					post_author,
					post_subject,
					post_author_ip,
					post_edit_tstamp,
					post_edit_count,
					post_attached,
					post_text,
					post_author_name,
					post_author_email,
					hidden';
				$dataArr = array();
				$dataArr['category_id'] = $this->cat_uid;
				$dataArr['conference_id'] = $this->conf_uid;
				$dataArr['thread_id'] = $this->thread_uid;
				$dataArr['post_author'] = $this->fe_user_uid;
				$dataArr['post_subject'] = $this->subject;
				$dataArr['post_author_ip'] = t3lib_div::getIndpEnv('REMOTE_ADDR');
				$dataArr['post_edit_tstamp'] = 0;
				$dataArr['post_edit_count'] = 0;
				if ($this->file_was_uploaded == true) $dataArr['post_attached'] = $this->attach_file_name;
				$dataArr['post_text'] = $this->text;
				$dataArr['post_author_name'] = $this->name;
				$dataArr['post_author_email'] = $this->email;
				if ($this->hide_new == true) $dataArr['hidden'] = 1;

				// Search for same author, same text, same conf, same cat, less than five minutes ago
				// probably a mistaken double-submit		
				// why the lorem_forum check? Lorem_forum allows you to save a single post to the database
				// hundreds or thousands of times (for testing purposes), so it's important to not check
				// for dupes if we're adding a bunch of test messages.
				if (!$this->lorem_forum) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $table, 
									      	"category_id='".$this->cat_uid.
									      	"' AND conference_id='".$this->conf_uid.
									      	"' AND thread_id='".$this->thread_uid.
									      	"' AND post_author='".$this->fe_user_uid.
									      	"' AND post_text='".mysql_escape_string($this->text)."'".
 									      	" AND tstamp>".(time()-60*5));
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				  	break;
					}
				}

				$query = $this->cObj->DBgetInsert($table, $pid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					if ($GLOBALS['TYPO3_DB']->sql_affected_rows() > 0) $postid = $GLOBALS['TYPO3_DB']->sql_insert_id();
					 
				// Update the thread with the post id number and a tstamp
				$table = 'tx_chcforum_thread';
				$uid = $this->thread_uid;
				$field_list = 'thread_lastpostid';
				$dataArr['thread_lastpostid'] = $postid;
				$query = $this->cObj->DBgetUpdate($table, $uid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					break;
				 
				// Used to update an edited post in the DB.
				case 'edit_post':
				// Update the post in the post table
				$table = 'tx_chcforum_post';
				$uid = $this->post_uid;
				$field_list = 'post_subject,post_edit_tstamp,post_text,post_edit_count';
				$dataArr = array();
				$dataArr['post_subject'] = $this->subject;
				$dataArr['post_edit_tstamp'] = time();
				$dataArr['post_text'] = $this->text;
				$dataArr['post_edit_count'] = 1; // fix this!
				$query = $this->cObj->DBgetUpdate($table, $uid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
								
				if ($GLOBALS['TYPO3_DB']->sql_error()) debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					break;
			}
			//phpinfo();
			//Jaspreet Singh: Do stuff for new post notifications
			//echo 'Starting Queuing notifications. '; //jsdebug
			if (true && ($GLOBALS['TSFE']->loginUser) ) {
				//echo 'Queuing notifications. '; //jsdebug 
				$threadID = $this->thread_uid;
				$lastPosterFeID = $this->fe_user_uid;
				//$internalUrl = $_SERVER["REQUEST_URI"];
				$internalUrl = $this->getThreadURI();
				$cn = new tx_commentnotify_pi1(); 
				//$memberaccess->initialize();
				$cn->db = $GLOBALS['TYPO3_DB'];
				$cn->debug = false;
				

				$cn->processForMessageboardPost( $threadID, $lastPosterFeID, $internalUrl);
				
			}
			
			if ($this->lorem_forum) {
				if ($this->lorem_forum_count == $this->lorem_forum) $this->reset_form();
			} else {
				$this->reset_form();				
			}
			
			$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
			$this->message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('form_submitted'), 'message');

		}

	 /**
		* Prune the forum. This can be called without instantiating the form object, if need be.
	  * This is currently not used -- someday, it might get enabled. But it needs to be completed.
	  * Currently, this function only deletes the posts -- doesn't get rid of empty threads.
	  * It's also not tested.
		* @return void
		*/
		function prune() {
			$age = $this->fconf['pruning_age'];
			$fconf_age = $age['pruning_age']; // age in days
			if ($fconf_age > 0) {
				#$fconf_age_s = $fconf_age * 86400; // age in secs
				$fconf_age_s = $fconf_age * 16400; // age in secs
				$now = time(); // um...now
				$min_tstamp = $now - $fconf_age_s; // min tstamp value; anything less gets deleted
				$p_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_chcforum_post', "tstamp < $min_tstamp", '', '');
				if ($p_res) {
					while ($post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($p_res)) {
						$posts_to_prune[] = array( 'uid' => $post['uid']);
					}
					$where = 'uid='.$posts_to_prune[0]['uid'];
					for ($key = 1, $size = count($posts_to_prune); $key < $size; $key++) {
						$where .= ' OR uid='.$posts_to_prune[$key]['uid'];
					}
					if ($where) $p_res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_chcforum_post', $where);
				}
			}
		}
		 
		/**
		* Processes any command to delete a message (sent via delete flag). Note -- security check performed
		* again here.
		*
		* @return void
		*/
		function process_delete_flag() {
			
			if ($this->post_uid && $this->user->can_mod_conf($this->conf_uid) == true) {
				// Update "deleted" to 1 in the post table
				$table = 'tx_chcforum_post';
				$uid = $this->post_uid;
				$field_list = 'deleted';
				$dataArr['deleted'] = 1;
				$query = $this->cObj->DBgetUpdate($table, $uid, $dataArr, $field_list);
				$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
				if ($GLOBALS['TYPO3_DB']->sql_error()) debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					unset($field_list);
				 
				// After deleting the message, check to see if there are any messages left in the
				// thread. If there aren't any remaining messages, you need to delete the thread, too.
				$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
				$thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
				$table = 'tx_chcforum_thread';
				$uid = $thread->uid;
				 
				if ($thread->thread_lastpostid == $this->post_uid) {
					$lastpostid = $thread->return_most_recent_post();
					$dataArr['thread_lastpostid'] = $lastpostid;
					$field_list = 'thread_lastpostid';
					$query = $this->cObj->DBgetUpdate($table, $uid, $dataArr, $field_list);
					$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
					if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
					}
				 
				if ($thread->return_post_count() <= 0) {
					$field_list = 'deleted';
					$dataArr['deleted'] = 1;
					$query = $this->cObj->DBgetUpdate($table, $uid, $dataArr, $field_list);
					$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
					
					// we just deleted a thread, and we want to make sure
					// we don't return to it.
					$this->thread_deleted = true;
					unset($this->thread_uid);
					$this->view = 'single_conf';
					
					if ($GLOBALS['TYPO3_DB']->sql_error()) t3lib_div::debug(array($GLOBALS['TYPO3_DB']->sql_error(), $query));
						$this->reset_form();
				}
			}
		}
		
		/**
		Get the URI for this thread 
		@return URI.  Example: '/index.php?id=4&view=single_thread&cat_uid=1&conf_uid=1&thread_uid=42&page=2'
		Example: (if RealURL is enabled) '/your-stories/view/single_thread/chc-forum/1/1/42.html?page=2'
		@author Jaspreet Singh
		*/
		function getThreadURI() {
			
			$FIRST_PAGE = 1;
			$SLASH = '/';
			//Get the last page, unless display_object isn't valid,
			//in which case we just get the first page
			$pages = empty( $this->display_object )
				? $FIRST_PAGE
				: $this->display_object ->get_page_count()
				;
			$pid = $GLOBALS['TSFE']->id;
			
			$params = array(
				"view" => "single_thread"
				,"cat_uid" => $this->cat_uid
				,"conf_uid" => $this->conf_uid
				,"thread_uid" => $this->thread_uid
				,"page" => $pages
				);
			
			$uri = $SLASH . htmlspecialchars($this->cObj->getTypoLink_URL($pid,$params)); // run it through special chars for XHTML compliancy
			
			if (false) {
				echo "submit() \n";
				echo "cat_uid $this->cat_uid \n";
				echo "conf_uid $this->conf_uid \n";
				echo "thread_uid $this->thread_uid \n";
				echo "pages $pages \n";
				echo "pid: $pid \n";
				echo "URL: $uri \n";
				//exit();
			}
			return $uri;
		}
		
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_form.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_form.php']);
	}
	
	
	 
?>
