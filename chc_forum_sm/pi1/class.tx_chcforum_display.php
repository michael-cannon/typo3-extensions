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
        *  (at your optionf) any later version.
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
        * Display class handles all output. It decides what to do based on the information
        * It receives from the get / post variables. This is the master class that controls
        * all the other classes. It gets instantiated by the pi1 class, and all of the methods
        * in this class that produce HTML for the forum output it to $this->html_out, which
        * eventually gets sent to the browser in the pi1 class.
        *
        * @author Zach Davis <zach@crito.org>
        */
        class tx_chcforum_display extends tx_chcforum_pi1 {

                var $view;
                var $cat_uid;
                var $conf_uid;
                var $thread_uid;
                var $post_uid;
                var $where;
                var $stage;
                var $preview;
                var $text;
                var $subject;
                var $cObj;
                var $html_out;

                var $conf;
                var $thread;
                var $post;


               /**
                * Display object constructor
                *
                * @param array  $gpvars: This contains all the relevant get/post vars. These get set in class.tx_chcforum_pi1 and passed to display object. The logic behind this is that at some point we might want to run validation on these variables in pi1 -- and we can do so before they get to the display object.
                * @param object  $cObj: cObj that gets passed to every constructor in the forum.
                *
                * @return void
                */
                function tx_chcforum_display ($gpvars, $cObj) { 

					// extract values of $gpvars and assign to variables with same name as
					// key name -- setting up the display object.
					foreach ($gpvars as $attr => $value) {
						$this->$attr = $value;
					}
                
          // Any way to get rid of this -- and/or set it above 1000?
          $this->internal['results_at_a_time'] = 1000;
           
          $this->cObj = $cObj;

					// new method for getting pid from GRSP
					$d = $GLOBALS['TSFE']->getStorageSiterootPids();
					if ($d['_STORAGE_PID'] && !$this->cObj->conf['pages']) $this->cObj->conf['pidList'] = $d['_STORAGE_PID'];

					// get PID from starting point
					if ($this->cObj->data['pages']) $this->cObj->conf['pidList'] = $this->cObj->data['pages'];
					
					// use this page if nothing else is available.
					if (!$this->cObj->conf['pidList']) $this->cObj->conf['pidList'] = $this->cObj->data['pid'];
					
					// Now that we've changed the pid, go ahead and bring the conf in from the cObj.
					$this->conf = $this->cObj->conf;

					// Bring in configuration info from fconf -- if the configuration isn't set properly, display
					// instructions on how to set it and abort the script.
					$this->fconf = $this->cObj->fconf;

					if ($this->fconf['is_valid'] == false) {
						$this->html_out = $this->fconf['message'];
						return;
					} else {
						$this->threads_per_page = $this->fconf['threads_per_page'];
						$this->posts_per_page = $this->fconf['posts_per_page'];
						$this->max_user_img = $this->fconf['max_user_img'];
					}
					$this->tmpl_path = tx_chcforum_shared::setTemplatePath();

					// include application wide JS code
					$js_tmpl = new tx_chcforum_tpower($this->tmpl_path.'global.js');
					$js_tmpl->prepare();
					$this->img_path = $this->fconf['tmpl_img_path'];
														
					// Output Javascript
					$this->html_out .= $js_tmpl->getOutputContent();
            			
					// Create the user object
					$tx_chcforum_user = t3lib_div::makeInstanceClassName("tx_chcforum_user");
					$this->user = new $tx_chcforum_user($this->cObj);
					$this->user->set_access_array(); // init access
					$this->user->set_closed_threads(); // init closed threads

					// are we showing hidden records?
					if ($this->user->can_mod_conf($this->conf_uid)) $this->user->show_hidden = 1;
					
					// Add the user object to the fconf so it can be accessed from other objects
					$this->cObj->fconf['user'] = $this->user;

					// Create conf, cat, and thread objects, if necessary -- in any of the single views, we should
           // try to use these objects rather than creating new ones.
					$tx_chcforum_category = t3lib_div::makeInstanceClassName("tx_chcforum_category");
					if ($this->cat_uid) $this->cat = new $tx_chcforum_category($this->cat_uid, $this->cObj);
					$tx_chcforum_conference = t3lib_div::makeInstanceClassName("tx_chcforum_conference");
					if ($this->conf_uid) $this->conference = new $tx_chcforum_conference($this->conf_uid, $this->cObj);
					$tx_chcforum_thread = t3lib_div::makeInstanceClassName("tx_chcforum_thread");
					if ($this->thread_uid) $this->thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
					$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
					if ($this->post_uid) $this->post = new $tx_chcforum_post($this->post_uid, $this->cObj);

					// set_path_vars will set the value of cat_uid, conf_uid, and thread_uid if needed -- and if they haven't
					// already been set. It will also validate the values of cat,conf, and thread uid passed via the URL, and
					// correct them if they're wrong.      									
					$this->validate_path_vars();
                  
                  // if there isn't a value in $this->page passed via the gpvars, we need to set the default page to 1
                  if (empty($this->page)) {
                  		$this->page = 1;
                  }

                  // Anytime we create a new $tx_chcforum_display object, we should also process any incoming form
                  // variables.
									$tx_chcforum_form = t3lib_div::makeInstanceClassName("tx_chcforum_form");
                  $this->form = new $tx_chcforum_form($this);

                  // Run handle_form function for the first time to process posted variables.
                  $this->form->handle_form();
									if ($this->form->thread_deleted == true) {
										$this->view = 'single_conf';
										unset($this->thread_uid);
										unset($this->thread);
										$this->validate_path_vars();
									}

									
									// if the user was editing, and the edit was valid and successful, bump the user
									// to the thread view.
									if ($this->form->where == 'edit_post' 
											&& $this->form->validated == 1 
											&& $this->form->stage == 'new') $this->form->view = 'single_thread';

                  // The form might change the view, depending on how it processed the post -- in case it does, let's sync
                  // the form view setting with this view setting
                  $this->view = $this->form->view;                       
                  
                  // If form sets flag to last, give us the last page
                  if ($this->form->flag == 'last' or $this->flag == 'last') $this->page = $this->get_page_count();
                          if ($this->form->flag == 'first' or $this->flag == 'first') $this->page = 1;

									// Create the header and toolbar, which should always be visible.
									// all HTML that precedes the toolbar should be placed in this 
									// header_html attribute -- the tool bar comes second in the html,
									// but it has to be built last in order to reflect any messages that
									// might be read in the the view (otherwise the new post count will
									// be inaccurate).
									$this->header_html .= $this->return_title_hdr();
									
                  if ($this->form->message) $this->html_out .= $this->form->message->display();

									// Use this section to handle any flags that might need to be processed
									// in the future, flags will be used for events that are not necessarily
									// display related
									switch($this->flag) {
													case 'mark_read':
														$this->user->mark_read();
													break;

													case 'watch_conf':
														$this->user->watch_conf($this->conf_uid);
													break;

													case 'ignore_conf':
														$this->user->ignore_conf($this->conf_uid);
													break;

													case 'watch_thread':
														$this->user->watch_thread($this->thread_uid,$this->conf_uid);
													break;

													case 'ignore_thread':
														$this->user->ignore_thread($this->thread_uid,$this->conf_uid);
													break;

													case 'close_thread':
														$this->thread->close_thread();
														$this->user->set_closed_threads();
														$this->cObj->fconf['user'] = $this->user;
													break;

													case 'open_thread':
														$this->thread->open_thread();
														$this->user->set_closed_threads();
														$this->cObj->fconf['user'] = $this->user;
													break;
													
													case 'rate':
														if ($this->fconf['allow_rating'] == true) $this->user->rate_post($this->rateSelect,$this->ratePostUID);
													break;

													case 'unhide':
														$this->post->unhide();
													break;

													case 'hide':
														$this->post->hide();
													break;

													case 'unhide_thread':
														$this->thread->unhide();
													break;

													case 'hide_thread':
														$this->thread->hide();
													break;
												}

												// CWT community integration control
												if ($this->conf['cwtCommunityIntegrated.'] = true) {
													// cwt_community control structure

													switch($this->action) {
														case 'getviewuserlist':
															$this->form->view = 'ulist';
															$this->view = 'ulist';
														break;													
														case 'getviewprofile':
															$this->form->view = 'profile';
															$this->view = 'profile';
															$this->author = t3lib_div::_GP('uid');
														break;
														case 'getviewmessagesdelete':
														case 'getviewmessages':
														case 'getviewmessagesnew':
														case 'getviewmessagessingle':

															$this->form->view = 'cwt_user_pm';
															$this->view = 'cwt_user_pm';
															$submitPressed = t3lib_div::_GP('tx_cwtcommunity_pi1');
															$submitPressed = $submitPressed['submit_button'];
															if ($this->action == 'getviewmessagesnew' && $this->post->uid && $submitPressed == null) {
																$subject = tx_chcforum_shared::lang('reply_label').' '.$this->post->post_subject;
																$body = $this->post->post_text;
                                $body = "\n-------------------------".$body;
																t3lib_div::_GETset(array ('subject' => $subject, 'body' => $body),'tx_cwtcommunity_pi1');
															}
														break;
														case 'getviewbuddylistadd':
														case 'getviewbuddylistdelete':
															$this->form->view = 'cwt_buddylist';
															$this->view = 'cwt_buddylist';
														break;

													}
												}


                        // switch statement that determines the view. Add new views here.
                        // any html produced out of this switch statement should be placed in
                        // $this->html_out 
                       switch($this->form->view) {
                        	case 'cwt_user_pm':
                        	$this->cwt_message();
                        	break;
                        	case 'cwt_buddylist':
                        	$this->cwt_buddylist();
                        	break;
													case 'all_cats':
													$this->all_cats();
													break;
													case 'single_cat':
													$this->single_cat();
													break;
													case 'single_conf':
													$this->single_conf();
													break;
													case 'single_thread':
													$this->single_thread();
													break;
													case 'edit_post':
													$this->edit_post();
													break;
													case 'single_post':
													$this->single_post();
													break;
													case 'profile':
													$this->profile();
													break;
													case 'search':
													$this->search();
													break;
													case 'ulist':
													$this->user_list();
													break;
													case 'new':
													$this->new_posts_view();
													break;
													default:
													$this->all_cats();
													break;                                
                        }
                        
												$this->toolbar_html = $this->return_tool_bar();
												$this->html_out = $this->header_html.$this->toolbar_html.$this->html_out;

             }

               /**
                * Validates the path vars that are sent via get_post -- makes sure they're set and correct.
                * This is to defend against any tampering via the URL.
                *
                * @return void
                */
                function validate_path_vars() {
                       	
                       	// if we don't have the final ID needed for this view, we should bump back to cat view.
                       	switch($this->view) {
                      					case 'single_cat':
                      								if (!$this->cat_uid) {
                      										$this->view = 'all_cats';
                      										return;
                      								}
                      					break;

                      					case 'single_conf':
                      								if (!$this->conf_uid) {
                      										$this->view = 'all_cats';
                      										return;
                      								}
                      					break;
                      					
                      					case 'single_thread':
                      					
                      								if (!$this->thread_uid) {
                      										$this->view = 'all_cats';
                       										return;
                       								}
	                     					break;
                      					
                      					case 'single_post':
                      								if (!$this->post_uid) {
                      										$this->view = 'all_cats';
                      										return;
                      								}
                      					break;                      					
                      	}
                        // make sure everything is set
                        $this->set_path_vars();

                        if ($this->post_uid && $this->post->is_valid == true) {
																$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                                if (empty($this->post)) $this->post = new $tx_chcforum_post($this->post_uid, $this->cObj);
                                        if ($this->thread_uid != $this->post->thread_id) $this->thread_uid = $this->post->thread_id;
                                if ($this->conf_uid != $this->post->conference_id) $this->conf_uid = $this->post->conference_id;
                                if ($this->cat_uid != $this->post->category_id) $this->cat_uid = $this->post->category_id;
                        } else {
                                if ($this->thread_uid) {
                                        $tx_chcforum_thread = t3lib_div::makeInstanceClassName("tx_chcforum_thread");
                                        if (empty($this->thread)) $this->thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
                                                if ($this->conf_uid != $this->thread->conference_id) $this->conf_uid = $this->thread->conference_id;
                                        if ($this->cat_uid != $this->thread->category_id) $this->cat_uid = $this->thread->category_id;
                                } else {
                                        if ($this->conf_uid) {
                                                $tx_chcforum_conference = t3lib_div::makeInstanceClassName("tx_chcforum_conference");
                                                if (empty($this->conference)) $this->conference = new $tx_chcforum_conference($this->conf_uid, $this->cObj);
                                                        if ($this->cat_uid != $this->conference->cat_id) $this->cat_uid = $this->conference->cat_id;
                                        }
                                }
                        }
                }



               /**
                * set_path_vars will set the value of $this->cat_uid, $this->conf_uid, and $this->thread_uid if
                * needed -- and if they haven't already been set.
                *
                * @return void
                */
                function set_path_vars() {
                        // if, for whatever reason, cat_uid, conf_uid, or thread_uid are empty, let's try to get
                        // them from conf or thread id.
                        if (empty($this->cat_uid)) {
                                if ($this->conf_uid) {
                                        $tx_chcforum_conference = t3lib_div::makeInstanceClassName("tx_chcforum_conference");
                                        if (empty($this->conference)) $this->conference = new $tx_chcforum_conference($this->conf_uid, $this->cObj);
                                                $this->cat_uid = $this->conference->cat_id;
                                }
                                if ($this->thread_uid && empty($this->cat_uid)) {
                                        $tx_chcforum_thread = t3lib_div::makeInstanceClassName("tx_chcforum_thread");
                                        if (empty($this->thread)) $this->thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
                                                $this->cat_uid = $this->thread->category_id;
                                }
                                if ($this->post_uid && empty($this->cat_uid)) {
                                        $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                                        if (empty($this->post)) $this->post = new $tx_chcforum_post($this->post_uid, $this->cObj);
                                                $this->cat_uid = $this->post->category_id;
                                }
                        }
                        if (empty($this->conf_uid)) {
                                if ($this->thread_uid && empty($this->conf_uid)) {
                                        $tx_chcforum_thread = t3lib_div::makeInstanceClassName("tx_chcforum_thread");                                        
                                        if (empty($this->thread)) $this->thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
                                                $this->conf_uid = $this->thread->conference_id;
                                }
                                if ($this->post_uid && empty($this->conf_uid)) {
                                        $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                                        if (empty($this->post)) $this->post = new $tx_chcforum_post($this->post_uid, $this->cObj);
                                                $this->conf_uid = $this->post->conference_id;
                                }
                        }
                        if (empty($this->thread_uid)) {
                                $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                                if (empty($this->post)) $this->post = new $tx_chcforum_post($this->post_uid, $this->cObj);
                                        $this->thread_uid = $this->post->thread_id;
                        }
                }

								function editProfile() {

									// show link back to all cats.
									$link_params['view'] = 'all_cats';
									$link_text = tx_chcforum_shared::lang('single_conf_all_cats_link');
									$text = tx_chcforum_shared::makeLink($link_params, $link_text);
									$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
									$link_to_cats = new $tx_chcforum_message($this->cObj, $text, 'link');
									$this->html_out .= $link_to_cats->display();

									// process edit form, show edit form, show profile.
									if ($this->submit && $this->profile['submit'] == 'submit') {

										$this->html_out .= $this->user->process_profile_form($this->profile, $this->files, $this->max_user_img);
										// refresh the author object because the record was just changed
										$tx_chcforum_author = t3lib_div::makeInstanceClassName("tx_chcforum_author");
                    $this->author = new $tx_chcforum_author($this->author_uid, $this->cObj);
										// refresh the user object because the record was just changed
										$tx_chcforum_user = t3lib_div::makeInstanceClassName("tx_chcforum_user");
                    $this->user = new $tx_chcforum_user($this->cObj);
									}

									if ($this->conf['cwtCommunityIntegrated'] == true) {
										$cwt_edit .= tx_chcforum_shared::getCWTcommunity('PROFILE_EDIT');
										
										$this->html_out .= '<table width="100%" border="0" cellspacing="1" cellpadding="0" summary="This table contains the simple search fields" class="tx-chcforum-pi1-Table">';
										$this->html_out .= '<tr><td>';
										$this->html_out .= '<div class="tx_chcforum-pi1-profileHdrBig" >'.tx_chcforum_shared::lang('profile_contact_acct_hdr').'</div>';
										$this->html_out .= '<div class="tx-chcforum-pi1-profileBorder">';
										$this->html_out .= $cwt_edit;
										$this->html_out .= '</div>';
										$this->html_out .= $this->user->profile_form(true);
										$this->html_out .= '</td></tr></table>';

									} else {
										$this->html_out .= $this->user->profile_form();
										$tx_chcforum_author = t3lib_div::makeInstanceClassName("tx_chcforum_author");
                    $this->author = new $tx_chcforum_author($this->author_uid, $this->cObj);
										$this->html_out .= $this->author->display();
										$this->html_out .= $link_to_cats->display();
									}
								}

               /**
                * Handles the display of an author's profile. Begins by creating the author object and displays
                * it using author display method. If the author uid is equal to the logged in user uid, then the
                * forum will also display the user profile, which can be edited.
                *
                * @return void
                 */
                function profile() {
                        if ($this->author) {
                                if ($this->author == 'self') {
																	return ($this->editProfile());
  																$this->author_uid = $this->user->uid;	                              
	                              } else {
	                                $this->author_uid = $this->author;
	                              }

                                // If the profile belongs to the current user, switch to edit profile view.
                                if ($this->user->uid == $this->author or $this->author == 'self') {
                                        return $this->editProfile();
                                }

																// Go ahead and display the author profile...
                                $tx_chcforum_author = t3lib_div::makeInstanceClassName("tx_chcforum_author");
                                $this->author_obj = new $tx_chcforum_author($this->author, $this->cObj);
                                if ($this->user->uid == $this->author_obj->uid && $this->submit && $this->profile['submit'] == 'submit') {
                                        $this->html_out .= $this->user->process_profile_form($this->profile, $this->files, $this->max_user_img);
                                        $this->author_obj = new $tx_chcforum_author($this->author, $this->cObj); // reset the author
                                }

																$link_params['view'] = 'all_cats';
																$link_text = tx_chcforum_shared::lang('single_conf_all_cats_link');
																$text = tx_chcforum_shared::makeLink($link_params, $link_text);
																$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
																$link_to_cats = new $tx_chcforum_message($this->cObj, $text, 'link');
																$this->html_out .= $link_to_cats->display();

                                $this->html_out .= $this->author_obj->display();
                        } else { // no author
                                $link_params['view'] = 'all_cats';
                                $link_text = tx_chcforum_shared::lang('single_conf_all_cats_link');
                                $text = tx_chcforum_shared::makeLink($link_params, $link_text);
                                $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                $link_to_cats = new $tx_chcforum_message($this->cObj, $text, 'link');
                                $this->html_out .= $link_to_cats->display();
                                $text = tx_chcforum_shared::lang('profile_no_acct');
                                $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                $message = new $tx_chcforum_message ($this->cObj, $text, 'nav_path');
                                $this->html_out .= $message->display();
                        }
                }

								
								function cwt_message() {
									
									// Output the nav path
									$this->html_out .= $this->set_nav_path();										
                  $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
									
									switch ($this->action) {
										case 'getviewmessagesnew':
											$text = tx_chcforum_shared::lang('cwt_message_new_with_recipient');								
											$tx_chcforum_author = t3lib_div::makeInstanceClassName("tx_chcforum_author");
											$recipient = new $tx_chcforum_author($this->recipient_uid,$this->cObj);
											$author_name = $recipient->return_name_link();
											$text = str_replace('###RECIPIENT###',$author_name,$text);												
										break;
										default:
											$text = tx_chcforum_shared::lang('cwt_message_pm');								
										break;										
									}

									$message = new $tx_chcforum_message ($this->cObj, $text, 'error_no_border');
                  $this->html_out .= $message->display();


									if ($this->conf['cwtCommunityIntegrated'] == true) {
										$this->html_out .= tx_chcforum_shared::getCWTcommunity('MESSAGES');
									}								
								}

								function cwt_buddylist() {

									// Output the nav path
									$this->html_out .= $this->set_nav_path();										
									
									if ($this->conf['cwtCommunityIntegrated'] == true) {
										$cwt_string .= tx_chcforum_shared::getCWTcommunity('BUDDYLIST');
										$header_row = $this->conf['cwtHeaderRow'];
										$out = str_replace('<{cwt_buddylist_header_status}>',tx_chcforum_shared::lang('cwt_buddylist_header_status'),$cwt_string);
										$out = str_replace('<{cwt_buddylist_header_name}>',tx_chcforum_shared::lang('cwt_buddylist_header_name'),$out);
										$out = str_replace('<{cwt_buddylist_header_remove}>',tx_chcforum_shared::lang('cwt_buddylist_header_remove'),$out);
										$out = str_replace('<{cwt_buddylist_header_message}>',tx_chcforum_shared::lang('cwt_buddylist_header_message'),$out);
										$this->html_out .= $out;
									}
								}

								function user_list() {

									// check if the ulist is restricted to logged in users...
									// if it is, and no user is logged in, return an error message.
									if (($this->fconf['restrict_ulist'] != false && !$this->user->uid) or ($this->fconf['ulist_disable'] == true)) {
										$msg = tx_chcforum_shared::lang('err_ulist_disabled');
										$type = 'error';
					                    $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                    					$message = new $tx_chcforum_message($this->cObj, $msg, $type);
										// Output the nav path
										$this->html_out .= $this->set_nav_path();							
										// Output the error.
										$this->html_out .= $message->display();
										return false;
									}

									// setup the query
									if (is_numeric($this->fconf['feusers_pid'])) $pid = $this->fconf['feusers_pid'];
									if ($this->fconf['users_per_page']) {
										$this->users_per_page = $this->fconf['users_per_page'];
									} else {
										$this->users_per_page = 10;
									}
									$fields = 'uid';
									$table = 'fe_users';
									$where = 'deleted != 1 AND uid IS NOT NULL AND pid='.$pid;
									$c_fields = 'COUNT(*)';
									$sort_by = 'username ASC';
									if ($this->search['uname']) {
										$where = 'username LIKE "%'.$this->search['uname'].'%" AND '.$where;
									}
									$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$sort_by);
									$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
									
									while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
										$uids[] = $row[uid];
									}
									$GLOBALS['TYPO3_DB']->sql_free_result($res);

									$this->user_cnt = $count;
									$this->set_page_links();
									
									// Output the nav path
									$this->html_out .= $this->set_nav_path();							

									if ($this->conf['cwtCommunityIntegrated'] == true) {

										$this->html_out .= tx_chcforum_shared::getCWTcommunity('USERLIST');

									} else {
										// Output the page links
										$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
										$page_links_msg = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_top');
										$this->html_out .= $page_links_msg->display();

										// no cwt_community integration requested.
										// prepare the template
										$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
										$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'user_list.tpl');
										$tmpl->prepare();
  	
										// add headers to template
										$headers = array('submit','lbl_search','hdr_search','hdr_user_list','hdr_name','hdr_joined','hdr_posts','hdr_email','hdr_aim');
										foreach ($headers as $v) {
											$headers_arr[$v] = tx_chcforum_shared::lang('ulist_'.$v);
										}
										$tmpl->assign($headers_arr);
  	
										// cycle through the X amount of users to display on this page.
										if ($count > 0) {
											while ($this->page_start <= $this->page_end) {
												$tx_chcforum_author = t3lib_div::makeInstanceClassName("tx_chcforum_author");
												$author = new $tx_chcforum_author($uids[$this->page_start],$this->cObj);
												
												if ($this->fconf['disable_profile'] == false) {
													$user['name'] = $author->return_name_link();
												} else {
													$user['name'] = $author->username;
												}
		
												if ($this->fconf['date_format']) {
													$date_format = $this->fconf['date_format'];
												} else {
													$date_format = '%m/%d/%Y';
												}
												if ($author->crdate) {
													$user['joined'] = $this->strftime($date_format,$author->crdate);
												} else {
													$user['joined'] = tx_chcforum_shared::lang('ulist_missing_crdate');
												}	
												$user['posts'] = $author->return_total_posts();
												if ($this->fconf['disable_email'] == false) $user['email'] = $author->return_email_link('<img border="0" src="'.$this->img_path.'email.'.$this->fconf['image_ext_type'].'">');
		
												$tmpl->newBlock('user');
												$tmpl->assign($user);										
												$this->page_start++;
											}
										} else {
											$tmpl->newBlock('user');
											$na = tx_chcforum_shared::lang('ulist_na');
											$user = array('name' => tx_chcforum_shared::lang('ulist_no_results'), 'joined' => $na, 'posts' => $na, 'email' => $na);
											$tmpl->assign($user);		
										}

										// add user list html to output ($this->html_out).
										$this->html_out .= $tmpl->getOutputContent();																	

										// Output the page links
										$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
										$page_links_msg = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_btm');
										$this->html_out .= $page_links_msg->display();									
									}
								}


								function new_posts_view() {
									// we're going to piggy back on the search results function here...
									$threads = $this->user->get_threads_with_new();
									$this->thread_cnt = count($threads);
									$this->list_threads($threads);
								}
								
								// used to display a list of threads
								function list_threads($threads,$message = false) {
									// Prepare the template and set up the headers.
									$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
									$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'conf_view.tpl');
									$tmpl->prepare();

									if ($message) {
										$tmpl->assign('message',$message);	
									}

									$this->thread_cnt = count($threads);

									// Setup a link to the category view (we'll display it at the end of this method)
									$link_params['view'] = 'all_cats';
									$link_text = tx_chcforum_shared::lang('single_conf_all_cats_link');
									$text = tx_chcforum_shared::makeLink($link_params, $link_text);
									$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
									$link_to_cats = new $tx_chcforum_message($this->cObj, $text, 'link');
                	
									// Set up the page links -- handle start and stop for page display.
									$this->set_page_links();
									
									// Make the thread table if there are threads
									if ($this->thread_cnt > 0) {

										// make the thread table
										$tmpl->newBlock('thread_table');

										$single_conf_headers = array ('header_title' => tx_chcforum_shared::lang('single_conf_header_title'),
										        'header_replies' => tx_chcforum_shared::lang('single_conf_header_replies'),
										        'header_author' => tx_chcforum_shared::lang('single_conf_header_author'),
										        'header_last' => tx_chcforum_shared::lang('single_conf_header_last'),
										        'cat_title' => $this->conference->conference_name);
										$tmpl->assign($single_conf_headers);

										while ($this->page_start <= $this->page_end) {
											$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
											$thread = new $tx_chcforum_thread($threads[$this->page_start], $this->cObj);
											$thread->new_cnt = $this->user->check_new($thread->uid, 'thread');
                			
											$thread_row = $thread->return_thread_row_data();
											
											// if user is a moderator for this conference,
											// show unhide / hide thread button
											if ($this->user->can_mod_conf($this->conference->uid)) {
												$thread_row['hide'] = $thread->return_thread_hide_btn();
											}	
 									
 												$tmpl->newBlock('thread');
												$tmpl->assign($thread_row);
												$this->page_start++;
										}
              		} else {
										// no threads -- output message
										$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
										$no_new_msg = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('no_new_posts'), 'message');
										$tmpl->assignGlobal('message',$no_new_msg->display());	
									}

									// Create the top and bottom page links
									$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
									$page_links_msg_t = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_top');
									$page_links_msg_b = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_btm');
									$page_links_top = $page_links_msg_t->display();
									$page_links_bottom = $page_links_msg_b->display();
                	
									$tmpl->gotoBlock('root');
									$tmpl->assignGlobal('nav_path',$this->set_nav_path());
									$tmpl->assignGlobal('sub_tool_bar',$this->return_sub_tool_bar());
									$tmpl->assignGlobal('page_links_top',$page_links_top);
									$tmpl->assignGlobal('page_links_bottom',$page_links_bottom);
									$tmpl->assignGlobal('preview_post',$preview_post);
									$tmpl->assignGlobal('post_form',$post_form);
									$tmpl->assignGlobal('message',$message_out);

									// Output the link to cat view again
									$tmpl->assignGlobal('link_to_cats',$link_to_cats->display());
									$this->html_out .= $tmpl->getOutputContent();
								}
																							
								function search() {
									$tx_chcforum_search = t3lib_div::makeInstanceClassName("tx_chcforum_search");
									$search_obj = new $tx_chcforum_search($this->search,$this->cObj);
									$results = $search_obj->display();
									// if it's an array, we have results
									if (is_array($results)) {
										// set search query
										$this->s_query = $results['query'];
										$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
										$no_new_msg = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::lang('search_results_msg'), 'message');
										$message = $no_new_msg->display();
										
										switch ($results['display_results']) {
											case "posts":
												$post_ids = $results['uids'];
												$this->list_posts($post_ids,$message);
											break;

											case "threads":
												$threads = $results['uids'];
												$this->list_threads($threads,$message);
											break;
										}										
									// if it's a string, we have the form.
									} elseif (is_string($results)) {
										$this->html_out .= $results;
									}
									return $this->html_out;
								}

               /**
                *Displays a singly conference (in other words, displays a list of threads for a single conf)
                *
                * @return void
                */
                function single_conf () {
                        // check for authorization.
                        if ($this->cat_uid && $this->conf_uid && $this->user->can_read_conf($this->conf_uid) == true) {
                                // Tell the form object where we are
                                $this->form->where = 'single_conf';

                                // Setup a link to the category view (we'll display it at the end of this method)
                                $link_params['view'] = 'all_cats';
                                $link_text = tx_chcforum_shared::lang('single_conf_all_cats_link');
                                $text = tx_chcforum_shared::makeLink($link_params, $link_text);
                                $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                $link_to_cats = new $tx_chcforum_message($this->cObj, $text, 'link');

                                // Create the conference object
                                $tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
                                if (empty($this->conference)) $this->conference = new $tx_chcforum_conference($this->conf_uid, $this->cObj);
                                
                                // Prepare the template
                                $tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                                $tmpl = new $tx_chcforum_tpower($this->tmpl_path.'conf_view.tpl');
                                $tmpl->prepare();

                                $threads = $this->conference->return_all_thread_ids();

                                if ($threads) {
                                        $this->thread_cnt = count($threads);

                                        // Set up the page links -- handle start and stop for page display.
                                        $this->set_page_links();

																				// make the thread table
																				$tmpl->newBlock('thread_table');

																				// set up the headers
																				$single_conf_headers = array ('header_title' => tx_chcforum_shared::lang('single_conf_header_title'),
																				        'header_replies' => tx_chcforum_shared::lang('single_conf_header_replies'),
																				        'header_author' => tx_chcforum_shared::lang('single_conf_header_author'),
																				        'header_last' => tx_chcforum_shared::lang('single_conf_header_last'),
																				        'cat_title' => $this->conference->conference_name);
																				$tmpl->assign($single_conf_headers);

                                        // Make the thread rows
                                        while ($this->page_start <= $this->page_end) {
                                                $tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
                                                $thread = new $tx_chcforum_thread($threads[$this->page_start], $this->cObj);
                                                $thread->new_cnt = $this->user->check_new($thread->uid, 'thread');

                                                $thread_row = $thread->return_thread_row_data();

																								// if user is a moderator for this conference,
																								// show unhide / hide thread button
																								if ($this->user->can_mod_conf($this->conference->uid)) {
																									$thread_row['hide'] = $thread->return_thread_hide_btn();
																								}

                                                $tmpl->newBlock('thread');
                                                $tmpl->assign($thread_row);
                                                $this->page_start++;
                                        }

                                        // Create the top and bottom page links
                                        $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                        $page_links_msg_t = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_top');
                                        $page_links_msg_b = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_btm');
																				$page_links_top = $page_links_msg_t->display();
																				$page_links_bottom = $page_links_msg_b->display();

                                        // Create the form or preview -- if a preview was produced, display form before the conf.
                                        // If not, display it after the conf.
                                        if ($this->form->is_preview == true) {
																						$preview_post = $this->cObj->stdWrap($this->form->display_form(),$this->conf['single_conf.']['preview.']['form.']);
                                        } else {
																						$post_form = $this->form->display_form();
                                        }
                                } else {
                                				$message_text = tx_chcforum_shared::lang('error_no_threads');
                                				$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                				$message = new $tx_chcforum_message($this->cObj, $message_text, 'error');
                                				$message_out = $message->display();
																				$post_form = $this->form->display_form();
                                }

                                $tmpl->gotoBlock('root');
                                $tmpl->assignGlobal('nav_path',$this->set_nav_path());
                                $tmpl->assignGlobal('sub_tool_bar',$this->return_sub_tool_bar());
                                $tmpl->assignGlobal('page_links_top',$page_links_top);
                                $tmpl->assignGlobal('page_links_bottom',$page_links_bottom);
                                $tmpl->assignGlobal('preview_post',$preview_post);
                                $tmpl->assignGlobal('post_form',$post_form);
                                $tmpl->assignGlobal('message',$message_out);

                                // Output the link to cat view again
                                $tmpl->assignGlobal('link_to_cats',$link_to_cats->display());
                                $this->html_out .= $tmpl->getOutputContent();
                        } else {
                                $this->html_out .= $this->no_auth_msg('dflt');
                        }
                }

				function return_sub_tool_bar() {
#					return false;
					
					$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
					$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'sub_tool_bar.tpl');
					$tmpl->prepare();

					// if we're looking at a conference or a thread, turn on the watch button
					if ($this->fconf['mailer_disable'] == false && ($this->view == 'single_thread' or $this->view == 'single_conf')) {

						$params = $this->generate_link_to_self_params();

						switch ($this->view) {
							case 'single_thread':
								$thread_prefs = $this->user->get_thread_prefs();
								if (is_array($thread_prefs) && in_array($this->thread_uid,$thread_prefs)) {
									$watch_link_text = tx_chcforum_shared::lang('toolbar_watch_thread_on');
									$params['flag'] = 'ignore_thread';
								} else {
									$watch_link_text = tx_chcforum_shared::lang('toolbar_watch_thread_off');
									$params['flag'] = 'watch_thread';
								}
							break;
												
							case 'single_conf':
								$conf_prefs = $this->user->get_conf_prefs();
								if (is_array($conf_prefs) && in_array($this->conf_uid,$conf_prefs)) {
									$watch_link_text = tx_chcforum_shared::lang('toolbar_watch_conf_on');
									$params['flag'] = 'ignore_conf';
								} else {
									$watch_link_text = tx_chcforum_shared::lang('toolbar_watch_conf_off');
									$params['flag'] = 'watch_conf';
								}
							break;											
						}
						
						$tmpl->newBlock('watch');
						$watch_link = tx_chcforum_shared::makeLink($params,$watch_link_text);
						$watch_alt = 'alt text here...';
						$watch_arr['watch_img'] = $this->img_path.'watch.'.$this->fconf['image_ext_type'];
						$watch_arr['watch_link'] = $watch_link;
						$watch_arr['watch_alt'] = $wath_alt;
						$tmpl->assign($watch_arr);
						$flag = true; // if something gets added to the button, set this flag.
					}				

					// if we have a moderator, turn on the thread locking button.
					if ($this->user->can_mod_conf($this->conf_uid) && $this->view == 'single_thread') {
						$params = $this->generate_link_to_self_params();

						if ($this->thread->thread_closed == false) {
							$link_text = tx_chcforum_shared::lang('toolbar_close_thread');
							$img_name = 'close_thread';
							$params['flag'] = 'close_thread';
						} else {
							$link_text = tx_chcforum_shared::lang('toolbar_open_thread');							
							$img_name = 'open_thread';
							$params['flag'] = 'open_thread';
						}
						$tmpl->newBlock('close_thread');
						$link = tx_chcforum_shared::makeLink($params,$link_text);
						$img_alt = 'alt text here...';
						$img_arr['img'] = $this->img_path.$img_name.'.'.$this->fconf['image_ext_type'];
						$img_arr['link'] = $link;
						$img_arr['alt'] = $img_alt;
						$tmpl->assign($img_arr);
						$flag = true; // if something gets added to the button, set this flag.
					}



					if ($flag == true) $out .= $tmpl->getOutputContent();
					return $out;
				}

               /**
                * Determines the number of pages necessary for the current conference or thread being viewed based
                * on fconf settings and number of child records (in conf, number of threads / in thread, number of
                * posts).
                *
                * @return integer  total number of pages that exist for this conference or thread
                */
                function get_page_count() {
                        switch ($this->view) {
                                case 'single_conf':
                                $threads = $this->conference->return_all_thread_ids($this->cObj);
                                if ($threads) $this->thread_cnt = count($threads);
                                        $page_cnt = ceil($this->thread_cnt / $this->threads_per_page);
                                break;

                                case 'single_thread':
                                $post_ids = $this->thread->return_all_post_ids();
                                if ($post_ids) $this->post_cnt = count($post_ids);
                                        $page_cnt = ceil($this->post_cnt / $this->posts_per_page);
                                break;
                        }
                        return $page_cnt;
                }

							function generate_link_to_self_params() {
								$params = array();
								$params['view'] = $this->view;
								switch ($this->view) {
									case 'single_conf':
										$params['cat_uid'] = $this->cat_uid;
										$params['conf_uid'] = $this->conf_uid;
										$params['page'] = $this->page;
									break;
	
									case 'single_thread':
										$params['cat_uid'] = $this->cat_uid;
										$params['conf_uid'] = $this->conf_uid;
										$params['thread_uid'] = $this->thread_uid;										
										$params['page'] = $this->page;
									break;

									case 'single_post':
										$params['cat_uid'] = $this->cat_uid;
										$params['conf_uid'] = $this->conf_uid;
										$params['thread_uid'] = $this->thread_uid;										
										$params['post_uid'] = $this->post_uid;
									break;
								}								
								return $params;
							}


               /**
                * Sets the page links that appear at the top of the page in conf and thread views.
                * Sets $this->page_links, $this->page_start, and $this->page_end vars. Switch based
                * on where we're viewing.
                *
                * @return void
                */
                function set_page_links () {
									switch ($this->view) {
										case 'ulist':
										if (isset($this->user_cnt)) {
											$page_cnt = ceil($this->user_cnt / $this->users_per_page);
											if (!isset($this->page)) $this->page = 1;
											if ($this->page > $page_cnt) $this->page = $page_cnt;
											$this->page_start = ($this->page - 1) * $this->users_per_page;
											$this->page_end = ($this->page * $this->users_per_page) - 1;
											if ($this->page_end >= $this->user_cnt) $this->page_end = $this->user_cnt - 1;

											$this->page_links = tx_chcforum_shared::lang('single_conf_header_threads').': '.$this->thread_cnt.' - ';
											$this->page_links .= tx_chcforum_shared::lang('single_conf_header_pages').' ('.$page_cnt.'): ';
											$link_params = array ('view' => $this->view);
				
											$num = 3;
											if ($this->page >= 5) {
												$link_params['page'] = '1';
												$link_title = '1';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												$this->page_links .= ' ... ';												
												$num = 2;
											}
											if ($this->page >= $page_cnt - 5) {
												$num = 2;
												if ($this->page == $page_cnt - 3) {
													$num = 3;
												}
											}
											$i = $this->page - $num;
											if ($i < 1) $i = 1;
											while ($i <= $this->page + $num && $i < $page_cnt + 1) {
												$link_params['page'] = $i;
												if ($i == $this->page) {
													$link_title = '['.$i.']';
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												} else {
													$link_title = $i;
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												}
												$i++;
											}											
											if ($this->page <= $page_cnt - 4) {
												$link_params['page'] = $page_cnt;
												$link_title = $page_cnt;
												$this->page_links .= ' ... ';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
											}
							
/*
											$i = $this->page - 4;
											if ($i < 1) $i = 1;
											while ($i <= ($this->page + 4) and $i <= $page_cnt) {
												$link_params['page'] = $i;
												$link_title = '['.$i.']';
												if ($i == $this->page) {
													$this->page_links .= '<strong>'.tx_chcforum_shared::makeLink($link_params, $link_title).'</strong>';
												} else {
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												}
												$i++;
											}
*/
										}
											
										break;
										
										case 'single_conf':

										if (isset($this->thread_cnt)) {
											$page_cnt = ceil($this->thread_cnt / $this->threads_per_page);
											if (!isset($this->page)) $this->page = 1;
											if ($this->page > $page_cnt) $this->page = $page_cnt;
											$this->page_start = ($this->page - 1) * $this->threads_per_page;
											$this->page_end = ($this->page * $this->threads_per_page) - 1;
											if ($this->page_end >= $this->thread_cnt) $this->page_end = $this->thread_cnt - 1;


											$this->page_links = tx_chcforum_shared::lang('single_conf_header_threads').': '.$this->thread_cnt.' - ';
											$this->page_links .= tx_chcforum_shared::lang('single_conf_header_pages').' ('.$page_cnt.'): ';
											$link_params = array ('view' => $this->view,
												'cat_uid' => $this->cat_uid,
												'conf_uid' => $this->conf_uid);

											$num = 3;
											if ($this->page >= 5) {
												$link_params['page'] = '1';
												$link_title = '1';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												$this->page_links .= ' ... ';												
												$num = 2;
											}
											if ($this->page >= $page_cnt - 5) {
												$num = 2;
												if ($this->page == $page_cnt - 3) {
													$num = 3;
												}
											}
											$i = $this->page - $num;
											if ($i < 1) $i = 1;
											while ($i <= $this->page + $num && $i < $page_cnt + 1) {
												$link_params['page'] = $i;
												if ($i == $this->page) {
													$link_title = '['.$i.']';
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												} else {
													$link_title = $i;
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												}
												$i++;
											}											
											if ($this->page <= $page_cnt - 4) {
												$link_params['page'] = $page_cnt;
												$link_title = $page_cnt;
												$this->page_links .= ' ... ';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
											}
										}
										break;
										
										case 'search':
										case 'new':
										if (isset($this->thread_cnt)) {
											$max = $this->threads_per_page;
											$count = $this->thread_cnt;
										} else {
											$max = $this->posts_per_page;
											$count = $this->post_cnt;
										}
										
										if (isset($count)) {
											$page_cnt = ceil($count / $max);
											if (!isset($this->page)) $this->page = 1;
											if ($this->page > $page_cnt) $this->page = $page_cnt;
											$this->page_start = ($this->page - 1) * $max;
											$this->page_end = ($this->page * $max) - 1;
											if ($this->page_end >= $count) $this->page_end = $count - 1;
											if ($this->search['display_results'] == '1') {
												$this->page_links = tx_chcforum_shared::lang('single_conf_header_posts').': '.$count.' - ';
											} else {											
												$this->page_links = tx_chcforum_shared::lang('single_conf_header_threads').': '.$count.' - ';
												$this->page_links .= tx_chcforum_shared::lang('single_conf_header_pages').' ('.$page_cnt.'): ';
											}
											$link_params = array ('view' => $this->view,
												's_query' => urlencode($this->s_query)
											);

											$num = 3;
											if ($this->page >= 5) {
												$link_params['page'] = '1';
												$link_title = '1';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												$this->page_links .= ' ... ';												
												$num = 2;
											}
											if ($this->page >= $page_cnt - 5) {
												$num = 2;
												if ($this->page == $page_cnt - 3) {
													$num = 3;
												}
											}
											$i = $this->page - $num;
											if ($i < 1) $i = 1;
											while ($i <= $this->page + $num && $i < $page_cnt + 1) {
												$link_params['page'] = $i;
												if ($i == $this->page) {
													$link_title = '['.$i.']';
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												} else {
													$link_title = $i;
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												}
												$i++;
											}											
											if ($this->page <= $page_cnt - 4) {
												$link_params['page'] = $page_cnt;
												$link_title = $page_cnt;
												$this->page_links .= ' ... ';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
											}
										}				
										break;
										
										case 'single_thread':
										if (isset($this->post_cnt)) {
											$page_cnt = ceil($this->post_cnt / $this->posts_per_page);
											if (!isset($this->page)) $this->page = 1;
											if ($this->page > $page_cnt) $this->page = $page_cnt;
											$this->page_start = ($this->page - 1) * $this->posts_per_page;
											$this->page_end = ($this->page * $this->posts_per_page) - 1;
											if ($this->page_end >= $this->post_cnt) $this->page_end = $this->post_cnt - 1;
											$this->page_links = tx_chcforum_shared::lang('single_conf_header_posts').': '.$this->post_cnt.' - ';
											$this->page_links .= tx_chcforum_shared::lang('single_conf_header_pages').' ('.$page_cnt.'): ';
											$link_params = array ('view' => 'single_thread',
												'cat_uid' => $this->cat_uid,
												'conf_uid' => $this->conf_uid,
												'thread_uid' => $this->thread_uid);

											$num = 3;
											if ($this->page >= 5) {
												$link_params['page'] = '1';
												$link_title = '1';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												$this->page_links .= ' ... ';												
												$num = 2;
											}
											if ($this->page >= $page_cnt - 5) {
												$num = 2;
												if ($this->page == $page_cnt - 3) {
													$num = 3;
												}
											}
											$i = $this->page - $num;
											if ($i < 1) $i = 1;
											while ($i <= $this->page + $num && $i < $page_cnt + 1) {
												$link_params['page'] = $i;
												if ($i == $this->page) {
													$link_title = '['.$i.']';
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												} else {
													$link_title = $i;
													$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
												}
												$i++;
											}											
											if ($this->page <= $page_cnt - 4) {
												$link_params['page'] = $page_cnt;
												$link_title = $page_cnt;
												$this->page_links .= ' ... ';
												$this->page_links .= tx_chcforum_shared::makeLink($link_params, $link_title).' ';
											}
										}
										break;
									}
                }



                // used to display a list of posts
                function list_posts($post_ids) {
                	$this->post_cnt = count($post_ids);
                	$this->set_page_links();

                	// Setup a link to the category view (we'll display it at the end of this method)
                	$link_params['view'] = 'all_cats';
                	$link_text = tx_chcforum_shared::lang('single_conf_all_cats_link');
                	$text = tx_chcforum_shared::makeLink($link_params, $link_text);
                	$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                	$link_to_cats = new $tx_chcforum_message($this->cObj, $text, 'link');

									// Create the top and bottom page links
									// Create the top and bottom page links
									$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
									$page_links_msg = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_top');
									$markers['page_links_top'] = $page_links_msg->display();
									$page_links_msg = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_btm');
									$markers['page_links_bottom'] = $page_links_msg->display();

									while ($this->page_start <= $this->page_end) {
									        $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
									        $post = new $tx_chcforum_post ($post_ids[$this->page_start], $this->cObj);
									        $this->user->check_new($post->uid);
									        $markers['the_thread'] .= $post->display_post();
									        $this->page_start++;
									}
									
                	$markers['link_to_conf'] = $link_to_cats->display();
                	$markers['nav_path'] = $this->set_nav_path();
                	$markers['sub_tool_bar'] = $this->return_sub_tool_bar();

                	$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                	$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'single_thread.tpl');
                	$tmpl->prepare();
                	$tmpl->assign($markers);
                	$this->html_out = $tmpl->getOutputContent();
                }
                
								/**
                * Displays a singly thread (in other words, displays a sequence of posts)
                *
                * @return void
                */
                function single_thread () {
									if ($this->cat_uid && $this->conf_uid && $this->user->can_read_conf($this->conf_uid) == true) {
									
										// Setup a link to the category view (we'll display it at the end of this method)
                  	$link_params = array ('view' => 'single_conf',
                  	        'cat_uid' => $this->cat_uid,
                  	        'conf_uid' => $this->conf_uid);
                  	$link_text = tx_chcforum_shared::lang('single_thread_conf_link');
                  	$text = tx_chcforum_shared::makeLink($link_params, $link_text);
                  	$tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                  	$link_to_conf = new $tx_chcforum_message($this->cObj, $text, 'link');
 
                  	// Create the thread object
                  	$tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
                  	if (empty($this->thread)) $this->thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);

                  	// Tell the form object where we are
                  	$this->form->where = 'single_thread';
                  	// If we're quoting or previewing, display the form first
                  	if ($this->form->flag == "quote" or $this->form->preview == '1') {
                  		if(!$this->thread->thread_closed) $markers['preview_post'] = $this->cObj->stdWrap($this->form->display_form(),$this->conf['single_thread.']['preview.']['form.']);
                  	} else {           
                  	  // Output the form -- but only if the thread hasn't been closed.
                  	  if(!$this->thread->thread_closed) $markers['post_form'] = $this->form->display_form();
                  	}

										// build 'the_thread';
                  	$post_ids = $this->thread->return_all_post_ids();
                  	if ($post_ids) {
                  	        $this->post_cnt = count($post_ids);
                  	        $this->set_page_links();

                  	        // Create the top and bottom page links
                  	        $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                  	        $page_links_msg = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_top');
                  	        $markers['page_links_top'] = $page_links_msg->display();
                  	        $page_links_msg = new $tx_chcforum_message($this->cObj, $this->page_links, 'page_links_btm');
                  	        $markers['page_links_bottom'] = $page_links_msg->display();

                  	        while ($this->page_start <= $this->page_end) {
                  	                $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                  	                $post = new $tx_chcforum_post ($post_ids[$this->page_start], $this->cObj);
                  	                $this->user->check_new($post->uid);
                  	                $markers['the_thread'] .= $post->display_post();
                  	                $this->page_start++;
                  	        }
                  	}

                    $markers['link_to_conf'] = $link_to_conf->display();
                   	$markers['nav_path'] = $this->set_nav_path();
                   	$markers['sub_tool_bar'] = $this->return_sub_tool_bar();

                   	$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                   	$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'single_thread.tpl');
                   	$tmpl->prepare();
										$tmpl->assign($markers);
										$this->html_out = $tmpl->getOutputContent();
                  } else {
                 		// no auth
 										$this->html_out .= $this->no_auth_msg('dflt');
                	}
                }

               /**
                * Edit post view -- displays the the post and the filled in form.
                *
                * @return void
                */
                function edit_post () {
                        if ($this->conf_uid && $this->user->can_edit_post($this->post_uid) == true) {
                                $link_params = array ('view' => 'single_thread',
                                        'cat_uid' => $this->cat_uid,
                                        'conf_uid' => $this->conf_uid,
                                        'thread_uid' => $this->thread_uid);
                                $link_text = tx_chcforum_shared::lang('single_post_thread_link');
                                $text = tx_chcforum_shared::makeLink($link_params, $link_text);
                                $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                $link_to_thread = new $tx_chcforum_message($this->cObj, $text, 'link');
                                // Set up the form -- process any incoming variables
                                $this->form->where = 'edit_post';
                                $this->html_out .= $this->set_nav_path();
                                $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                                $post = new $tx_chcforum_post($this->post_uid, $this->cObj);
                                $this->html_out .= $this->form->display_form();
                                // Create the thread object and display it HUH?
                                $this->html_out .= $post->display_post();
                                $this->html_out .= $link_to_thread->display();
                        } else {
                                $this->html_out .= $this->no_auth_msg('dflt');
                        }
                }

               /**
                * Single post view -- displays post and form.
                *
                * @return void
                */
                function single_post () {
                        if ($this->cat_uid && $this->conf_uid && $this->user->can_read_conf($this->conf_uid) == true) {
                                // Setup a link to the category view (we'll display it at the end of this method)
                                $link_params = array ('view' => 'single_thread',
                                        'cat_uid' => $this->cat_uid,
                                        'conf_uid' => $this->conf_uid,
                                        'thread_uid' => $this->thread_uid);
                                $link_text = tx_chcforum_shared::lang('single_post_thread_link');
                                $text = tx_chcforum_shared::makeLink($link_params, $link_text);
                                $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                                $link_to_thread = new $tx_chcforum_message($this->cObj, $text, 'link');
                                // Set up the form -- process any incoming variables
                                $this->form->where = 'single_post';
                                $markers['nav_path'] = $this->set_nav_path();
  															$tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
  															$post = new $tx_chcforum_post($this->post_uid, $this->cObj);
         												// add re: in form subject...
                                if ($this->form->stage != previewed and $this->form->flag != 'edit') $this->form->subject = 're: '.$post->post_subject;
                                if ($this->form->flag == "quote" or $this->flag == "edit" or $this->form->stage == 'previewed') {
                                        $markers['preview_post'] = $this->form->display_form();
                                        // Create the post object and display it
                                        $markers['the_post'] = $post->display_post();
                                } else {
                                        // Create the post object and display it
                                        $markers['the_post'] = $post->display_post();
                                        $markers['post_form'] = $this->form->display_form();
                                }
                                $markers['link_to_thread'] = $link_to_thread->display();
																
																// output this bad boy
																$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                                $tmpl = new $tx_chcforum_tpower($this->tmpl_path.'single_post_view.tpl');
                                $tmpl->prepare();
                                $tmpl->assign($markers);
                                $this->html_out = $tmpl->getOutputContent();																
																
                        } else {
                                $this->html_out .= $this->no_auth_msg('dflt');
                        }
                }


               /**
                * Displays the listing of all viewable categories. This is the default view for the forum.
                *
                * @return void
                */
                function all_cats () {
                        $cat_ids = tx_chcforum_category::get_all_cat_ids($this->cObj);
                        if (!empty($cat_ids)) {
                                $tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                                $tmpl = new $tx_chcforum_tpower($this->tmpl_path.'cat_view.tpl');
                                $tmpl->prepare();
                                $cat_list_headers = array ('header_title' => tx_chcforum_shared::lang('all_cats_header_title'),
                                        'header_thread' => tx_chcforum_shared::lang('all_cats_header_thread'),
                                        'header_post' => tx_chcforum_shared::lang('all_cats_header_post'),
                                        'header_last' => tx_chcforum_shared::lang('all_cats_header_last'));
                                $tmpl->assign($cat_list_headers);
                                foreach ($cat_ids as $a_cat_id) {           
                                        $tx_chcforum_category = t3lib_div::makeInstanceClassName("tx_chcforum_category");
                                        $current_cat = new $tx_chcforum_category($a_cat_id, $this->cObj);
                                        $cat_header_row = $current_cat->cat_header_row();
					$cat_header_printed = false;
                                        $conf_ids = $current_cat->get_confs();
                                        if ($conf_ids) {
                                                $at_least_one = false;
						if (!$cat_header_printed) {
						        $tmpl->newBlock('cat_list');
							$tmpl->newBlock('cat_row');
							$tmpl->assign($cat_header_row);
							$cat_header_printed = true;
						}

                                                foreach ($conf_ids as $a_conf_id) {
                                                        if ($this->user->can_read_conf($a_conf_id) == true) {
                                                                $tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
                                                                $current_conf = new $tx_chcforum_conference($a_conf_id, $this->cObj);
                                                                $current_conf->new_cnt = $this->user->check_new($current_conf->uid, 'conf');
                                                                $conf_row = $current_conf->return_conf_row_data();
                                                                $tmpl->newBlock('conf_row');
                                                                $tmpl->assign($conf_row);
                                                                $at_least_one = true;
                                                        }
                                                        
                                                }
                                                // no conferences were returned for this cat -- say so.
                                                if ($at_least_one != true && $this->fconf['hide_empty_cats'] != 1) {
                                                	$tmpl->newBlock('conf_row');
                                                	$empty_arr['conf_thread_count'] = '&nbsp;';
                                                	$empty_arr['conf_post_count'] = '&nbsp;';
                                                	$empty_arr['conf_last_post_data'] = '&nbsp;';
                                                	$empty_arr['conf_name'] = 'No conferences';
                                                	$empty_arr['conf_desc'] = tx_chcforum_shared::lang('all_cats_noconfs');
                                                	$tmpl->assign($empty_arr);
                                                }
                                        } else {
					        if ($this->fconf['hide_empty_cats'] != 1) {

						        if (!$cat_header_printed) {
							        $tmpl->newBlock('cat_list');
								$tmpl->newBlock('cat_row');
								$tmpl->assign($cat_header_row);
								$cat_header_printed = true;
							}

                                                $conf_row = tx_chcforum_conference::return_conf_row_data(true);
                                                $tmpl->newBlock('conf_row');
                                                $tmpl->assign($conf_row);
                                        }
                                }
                                }
																$tmpl->assignGlobal('nav_path',$this->set_nav_path());
                                $tmpl->assignGlobal('footer_box',$this->return_footer_box());
																$this->html_out .= $tmpl->getOutputContent();
                        } else {
                                $this->html_out .= $this->no_auth_msg('dflt');
                        }
                }




               /**
                * Returns the HTML / Javascript link for showing users online.
                *
                * @return string  users online link
                */
								// until sessions are fixed in the typo3 core, I'm not able to find a way to get this
								// method to work properly. In the meantime, I'm removing the count of users online, since
								// it does't return a valid count. I'll leave this framework here, in case we need it 
								// sometime down the road.
                function return_users_online_html() {
                	if ($this->fconf['disable_users_online'] != 1) {
	                
	                // base query kh_useronline table, if it's present.
  	              	if (t3lib_extMgm::isLoaded('kh_usersonline')) {
    	            		// Delete all users which are timed out
											$query = "DELETE FROM tx_khusersonline_users WHERE time<'$clear_time'";
											$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
											// Count anonymous users
											#$query = "SELECT ip FROM tx_khusersonline_users WHERE user=0";
											#$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
											#$guests = mysql_num_rows($res);
											// Count logged in users
											$query = "SELECT fe_users.username, fe_users.name, tx_khusersonline_users.user AS uid FROM tx_khusersonline_users LEFT JOIN fe_users ON tx_khusersonline_users.user = fe_users.uid WHERE user != 0";
										// kh_useronline is not loaded -- user flawed forum method.
	                	} else {
		                  if (is_numeric($this->fconf['feusers_pid'])) $pid = $this->fconf['feusers_pid'];                  
											$query = 'SELECT DISTINCT fe_sessions.ses_userid, fe_users.name, fe_users.username, fe_users.uid FROM fe_users, fe_sessions WHERE fe_users.pid = '.$pid.' AND fe_users.uid = fe_sessions.ses_userid AND fe_users.is_online';
										}	        	            

										// get results
										$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
	        	        $count = mysql_num_rows($res);
	          	      // Get the names of users online
	            	    while ($row = mysql_fetch_assoc($res)) {
	            	    	if ($this->fconf['show_online_names'] == 1) {
		              	  	$names[] = tx_chcforum_shared::makeLink(array('view' => 'profile', 'author' => $row['uid']), $row['username']);	            	      		
	            	     	} else {
		              	   	$names[] = tx_chcforum_shared::makeLink(array('view' => 'profile', 'author' => $row['uid']), $row['name'].' ['.$row['username'].']');
											}
	                	}                  
  			            // hard coded text here...fix it...
  			            $names_text=((sizeof($names)>1) ? implode(array_slice($names,0,-1),", ").' and '.$names[sizeof($names)-1] : ((sizeof($names)==1) ? $names[0] : "no users online")); // thanks to el thrusto for the hard work.
  	                
    	              // put the output together -- both of the methods above need
      	            // to be compatible with this how this string is built.
        	          if ($count == 1) {
          	        	$text[0] = tx_chcforum_shared::lang('footer_online_5');
            	      	$text[1] = tx_chcforum_shared::lang('footer_online_6');
              	    } else {
                	  	$text[0] = tx_chcforum_shared::lang('footer_online_7');
                  		$text[1] = tx_chcforum_shared::lang('footer_online_8');
	                  }
	
  	                $alert_text = tx_chcforum_shared::lang('footer_online_4').' '.$names_text;
    	              $out .= tx_chcforum_shared::lang('footer_online_1').' '.$text[0].' '.tx_chcforum_shared::lang('footer_online_2').' ';
	
  	                $out .= '<a href="#" title="users online" onclick="showhide(\'0\'); return false;">'.$count.' '.$text[1].'</a>';
    	              $out .= ' '.tx_chcforum_shared::lang('footer_online_3');
      	            $out .= '<div style="display: none;" class="tx-chcforum-pi1-footerUsersDiv" id="div0">';
										$out .= ''.$alert_text;
          	        $out .= '</div><br />';                        
            	      return $out;
              	  }
								}
							
               /**
                * Returns the count of frontend users.
                *
                * @return integer  number of frontend user accounts.
                */
                function return_user_count() {
                	if (is_numeric($this->fconf['feusers_pid'])) $pid = $this->fconf['feusers_pid'];
                  $query = 'SELECT COUNT(*) FROM fe_users WHERE deleted=0 and pid='.$pid;
                  $results = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
                  $count = mysql_fetch_array($results);
                  // Get the count of users online
									$GLOBALS['TYPO3_DB']->sql_free_result($results);
                  $real_count = $count[0];
                  return $real_count;
                }

               /**
                * Returns total number of posts in the post table.
                *
                * @return integer  total posts (not deleted) in post table.
                */
                function return_post_count() {
                  if (!$this->cObj->conf['pidList']) return false;
                  $query = 'SELECT COUNT(*) FROM tx_chcforum_post WHERE deleted=0 AND pid='.$this->cObj->conf['pidList'];
                  $results = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
                  $count = mysql_fetch_array($results);
                  // Get the count of users online
                  $real_count = $count[0];
									$GLOBALS['TYPO3_DB']->sql_free_result($results);
                  return $real_count;
                }

                /**
                * Used to get the header box that appears on the cat view.
                *
                * @return string  html for header box.
                */                
                function return_title_hdr() {
                	if ($this->fconf['forum_title']) {
										$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
										$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'header.tpl');
										$tmpl->prepare();
										$tmpl->assign('content', $this->fconf['forum_title']);
										$tmpl->assign('img_path', $this->fconf['tmpl_img_path']);
										$out .= $tmpl->getOutputContent();
										return $out;
									} else {
										return false;
									}
                }

								function return_tool_bar() {
									$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
									$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'tool_bar.tpl');
									$tmpl->prepare();
									
									$sparams = array ('view' => 'search');
									$search_link = tx_chcforum_shared::makeLink($sparams,tx_chcforum_shared::lang('toolbar_search'));
									$tmpl->assign('search_img',$this->img_path.'search.'.$this->fconf['image_ext_type']);
									$tmpl->assign('search_link',$search_link);
									$tmpl->assign('search_alt',tx_chcforum_shared::lang('toolbar_search'));

									if ($this->fconf['restrict_ulist'] == true && !$this->user->uid) {
										// don't do anything...	
									} elseif ($this->fconf['ulist_disable'] == true) {
										// don't do anything...
									} else {
										$uparams = array ('view' => 'ulist');
										$users_link = tx_chcforum_shared::makeLink($uparams,tx_chcforum_shared::lang('toolbar_users'));
										$ulist_arr['users_img'] = $this->img_path.'users.'.$this->fconf['image_ext_type'];
										$ulist_arr['users_link'] = $users_link;
										$ulist_arr['users_alt'] = tx_chcforum_shared::lang('toolbar_users');
										$tmpl->newBlock('ulist');
										$tmpl->assign($ulist_arr);
									}
						
									// put all toolbar options that require a user in this statement
                 					if ($this->user->uid) {
										
										$mr_params = array ('flag' => 'mark_read');
										$mark_read_link = tx_chcforum_shared::makeLink($mr_params,tx_chcforum_shared::lang('toolbar_mark_read'));
										$is_user_arr['mark_read'] = $mark_read_link;
										$is_user_arr['mark_read_img'] = $this->img_path.'mark_read.'.$this->fconf['image_ext_type'];
										$is_user_arr['mark_read_alt'] = tx_chcforum_shared::lang('toolbar_mark_read');
										
										$new_post_cnt = $this->user->count_all_new();

										if ($new_post_cnt == 0) {
											$new_post_str = tx_chcforum_shared::lang('tool_bar_new_none');
										} elseif ($new_post_cnt == 1) {
											$new_post_str = tx_chcforum_shared::lang('tool_bar_new_singular');
										} elseif ($new_post_cnt > 1) {
											$new_post_str = tx_chcforum_shared::lang('tool_bar_new_plural');
										}
										$new_post_str = str_replace('###NUM###',$new_post_cnt,$new_post_str);

										$nparams = array ('view' => 'new');
										if ($new_post_cnt < 1) {
											$new_link = $new_post_str;
										} else {
											$new_link = tx_chcforum_shared::makeLink($nparams,$new_post_str);
										}									
										$is_user_arr['new_posts'] = $new_link;
										$is_user_arr['new_img'] = $this->img_path.'new.'.$this->fconf['image_ext_type'];
										$is_user_arr['new_alt'] = tx_chcforum_shared::lang('toolbar_new');
										$tmpl->newBlock('is_user');
										$tmpl->assign($is_user_arr);

										// if profiles are enabled, show the profile link button						
										if ($this->fconf['disable_profile'] == false) {
											$tmpl->newBlock('is_profile');
											$pparams = array ('view' => 'profile', 'author' => tx_chcforum_shared::encode($this->user->uid));
											$profile_link = tx_chcforum_shared::makeLink($pparams,tx_chcforum_shared::lang('toolbar_profile'));
											$profile_arr['profile_img'] = $this->img_path.'profile.'.$this->fconf['image_ext_type'];
											$profile_arr['profile_link'] = $profile_link;
											$profile_arr['profile_alt'] = tx_chcforum_shared::lang('toolbar_profile');
											$tmpl->assign($profile_arr);
										}
										
										// if cwt_community integration is on, show private messages button
										if ($this->conf['cwtCommunityIntegrated'] == true) {

											$tmpl->newBlock('buddylist');
											$pparams = array ('view' => 'cwt_buddylist');

											$label = tx_chcforum_shared::lang("toolbar_buddylist");

											// populate the template
											$buddylist_link = tx_chcforum_shared::makeLink($pparams,$label);
											$buddylist_arr['buddylist_img'] = $this->img_path.'user_pm.'.$this->fconf['image_ext_type'];
											$buddylist_arr['buddylist_link'] = $buddylist_link;
											$buddylist_arr['buddylist_alt'] = tx_chcforum_shared::lang('toolbar_buddylist');
											$tmpl->assign($buddylist_arr);										


											// make new PM button / link
											$tmpl->newBlock('user_pm');
											$pparams = array ('view' => 'cwt_user_pm');
											
											// count new PMs
											$where = 'tx_cwtcommunity_message.cruser_id = fe_users.uid AND tx_cwtcommunity_message.fe_users_uid = '.$this->user->uid.' AND tx_cwtcommunity_message.status = 0';
											$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cwtcommunity_message.cruser_id, tx_cwtcommunity_message.crdate, tx_cwtcommunity_message.subject, tx_cwtcommunity_message.uid, tx_cwtcommunity_message.status, fe_users.username','tx_cwtcommunity_message, fe_users',$where,'','crdate DESC');
											while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
												$messages[] = $row;
											}
											$c = count($messages);
											if ($c == 0) $label = tx_chcforum_shared::lang("toolbar_user_pm_count_0");
											if ($c == 1) $label = tx_chcforum_shared::lang("toolbar_user_pm_count_sing");
											if ($c > 1) $label = tx_chcforum_shared::lang("toolbar_user_pm_count_plural");
											$label = str_replace('###COUNT###',$c,$label);

											// populate the template
											$user_pm_link = tx_chcforum_shared::makeLink($pparams,$label);
											$user_pm_arr['user_pm_img'] = $this->img_path.'user_pm_message.'.$this->fconf['image_ext_type'];
											$user_pm_arr['user_pm_link'] = $user_pm_link;
											$user_pm_arr['user_pm_alt'] = tx_chcforum_shared::lang('toolbar_user_pm');
											$tmpl->assign($user_pm_arr);
										}
								
									}
									
									$out .= $tmpl->getOutputContent();
									return $out;
								}

                /**
                * Used to get the footer box that appears on the cat view.
                *
                * @return string  html for footer box.
                */
                function return_footer_box() {
									// Set up Javascript
									if (!$this->tmpl_path) $this->tmpl_path = tx_chcforum_shared::setTemplatePath();
									$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
									$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'footer.js');
									$tmpl->prepare();
									$tmpl->assign($this->label_array);
									// Output Javascript
									$out .= $tmpl->getOutputContent();

                  $tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                  $tmpl = new $tx_chcforum_tpower($this->tmpl_path.'footer.tpl');
                  $tmpl->prepare();
                  // we're throwing this online count into the closet until we can figure it out... or until t3 core handles sessions more reliably...
                  #$populate_tmpl['users_online'] = $this->return_users_online_html();
                  $populate_tmpl['total_posts'] = tx_chcforum_shared::lang('footer_posts_1').' '.$this->return_post_count().' '.tx_chcforum_shared::lang('footer_posts_2');
				  // MLC client request not to display
                  // $populate_tmpl['total_users'] = tx_chcforum_shared::lang('footer_users_1').' '.$this->return_user_count().' '.tx_chcforum_shared::lang('footer_users_2');
                  $tmpl->assign($populate_tmpl);
                  $out .= $tmpl->getOutputContent();
									return $out;
                }

                /**
                * Displays a single category
                *
                * @return void
                */
                function single_cat () {
                        $params['view'] = 'all_cats';
                        $message_text = tx_chcforum_shared::makeLink($params, tx_chcforum_shared::lang('single_cat_all_cats'));
                        $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                        $message = new $tx_chcforum_message($this->cObj, $message_text, 'link');
                        $message_out = '<div class="tx-chcforum-pi1-postTableWrap">'.$message->display().'</div>';

                        $tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
                        $tmpl = new $tx_chcforum_tpower($this->tmpl_path.'cat_view.tpl');
                        $tmpl->prepare();
                        $cat_list_headers = array ('header_title' => tx_chcforum_shared::lang('all_cats_header_title'),
                                'header_thread' => tx_chcforum_shared::lang('all_cats_header_thread'),
                                'header_post' => tx_chcforum_shared::lang('all_cats_header_post'),
                                'header_last' => tx_chcforum_shared::lang('all_cats_header_last'));
                        $tmpl->assign($cat_list_headers);

                        $tx_chcforum_category = t3lib_div::makeInstanceClassName("tx_chcforum_category");
                        if (empty($this->cat)) $this->cat = new $tx_chcforum_category($this->cat_uid, $this->cObj);
                                if (!empty($this->cat->uid) && $this->user->can_read_cat($this->cat->uid)) {
                               		$cat_header_row = $this->cat->cat_header_row();
                                	$tmpl->newBlock('cat_list');
                                	$tmpl->newBlock('cat_row');
                                	$tmpl->assign($cat_header_row);
                                	$conf_ids = $this->cat->get_confs();
                                	if ($conf_ids) {
                                		$at_least_one = false;
                                	       foreach ($conf_ids as $a_conf_id) {
                                                if ($this->user->can_read_conf($a_conf_id) == true) {
                                                        $tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
                                                        $current_conf = new $tx_chcforum_conference($a_conf_id, $this->cObj);
                                                        $current_conf->new_cnt = $this->user->check_new($current_conf->uid, 'conf');
                                                        $conf_row = $current_conf->return_conf_row_data();
                                                        $tmpl->newBlock('conf_row');
                                                        $tmpl->assign($conf_row);
                                                        $at_least_one = true;
                                                }
                                        }
                                        // no conferences were returned for this cat -- say so.
                                        if ($at_least_one != true) {
                                        	$tmpl->newBlock('conf_row');
                                        	$empty_arr['conf_thread_count'] = '&nbsp;';
                                         	$empty_arr['conf_post_count'] = '&nbsp;';
                                          $empty_arr['conf_last_post_data'] = '&nbsp;';
                                          $empty_arr['conf_name'] = 'No conferences';
                                          $empty_arr['conf_desc'] = tx_chcforum_shared::lang('all_cats_noconfs');
                                          $tmpl->assign($empty_arr);
                                        }
                                	} else {
                                        $conf_row = tx_chcforum_conference::return_conf_row_data(true);
                                        $tmpl->newBlock('conf_row');
                                        $tmpl->assign($conf_row);
                                	}
																	$tmpl->assignGlobal('link_to_cats',$message_out);
                                	$tmpl->assignGlobal('nav_path',$this->set_nav_path());
                                	$tmpl->assignGlobal('footer_box',$this->return_footer_box());
                                	$this->html_out .= $tmpl->getOutputContent();
                        			} else {
                          	      $this->html_out .= $this->no_auth_msg('cat');
                        			}

                }

                /**
                * Create the navigation path that appears on most views in the extension
                *
                * @return string  html for the navigation path box.
                */
                function set_nav_path () {
                   
                        $lparams = array('view' => 'all_cats');
                        $nav_path .= tx_chcforum_shared::makeLink($lparams, tx_chcforum_shared::lang('all_cats'));
                        $nav_div = tx_chcforum_shared::lang('navpath_divider');

                        // special nav path settigns
                        if ($this->view == 'search') {
                        	$lparams = array('view' => 'search');
	                        $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, tx_chcforum_shared::lang('toolbar_search'));
                        }
                        if ($this->view == 'ulist') {
                        	$lparams = array('view' => 'ulist');
	                        $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, tx_chcforum_shared::lang('toolbar_users'));
                        }
                        if ($this->view == 'cwt_buddylist') {
                        	$lparams = array('view' => 'cwt_buddylist');
	                        $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, tx_chcforum_shared::lang('toolbar_buddylist'));
                        }
                        if ($this->view == 'cwt_user_pm') {
                        	$lparams = array('view' => 'cwt_message');
	                        $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, tx_chcforum_shared::lang('cwt_message_pm'));
                        }
                        if ($this->view == 'new') {
                        	$lparams = array('view' => 'new');
	                        $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, tx_chcforum_shared::lang('toolbar_new'));
                        }

                        
                        if (isset($this->cat_uid) && $this->cat_uid) {
																$tx_chcforum_category = t3lib_div::makeInstanceClassName("tx_chcforum_category");
                                if (empty($this->cat)) $this->cat = new $tx_chcforum_category($this->cat_uid, $this->cObj);
                                        if ($this->cat->uid) {
                                        $lparams = array('view' => 'single_cat',
                                                'cat_uid' => $this->cat_uid);
                                        if ($this->view == 'single_cat') {
                                                $nav_path .= '&nbsp;'.$nav_div.$this->cat->cat_title;
                                        } else {
                                                $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, $this->cat->cat_title);
                                        }
                                }
                        }
                        if (isset($this->conf_uid) && $this->conf_uid) {
																$tx_chcforum_conference= t3lib_div::makeInstanceClassName("tx_chcforum_conference");
                                if (empty($this->conference)) $this->conference = new $tx_chcforum_conference($this->conf_uid, $this->cObj);
                                        if ($this->conference->uid) {
                                        $lparams = array('view' => 'single_conf',
                                                'cat_uid' => $this->cat_uid,
                                                'conf_uid' => $this->conf_uid);
                                        if ($this->view == 'single_conf') {
                                                $nav_path .= '&nbsp;'.$nav_div.$this->conference->conference_name;
                                        } else {
                                                $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, $this->conference->conference_name);
                                        }
                                }
                        }
                        if (isset($this->thread_uid) && $this->thread_uid) {
                                $tx_chcforum_thread= t3lib_div::makeInstanceClassName("tx_chcforum_thread");
                                if (empty($this->thread)) $this->thread = new $tx_chcforum_thread($this->thread_uid, $this->cObj);
                                        if ($this->thread->uid) {
                                        $lparams = array('view' => 'single_thread',
                                                'cat_uid' => $this->cat_uid,
                                                'conf_uid' => $this->conf_uid,
                                                'thread_uid' => $this->thread_uid);
                                        if ($this->view == 'single_thread') {
                                                $nav_path .= '&nbsp;'.$nav_div.$this->thread->thread_subject;
                                        } else {
                                                $nav_path .= '&nbsp;'.$nav_div.tx_chcforum_shared::makeLink($lparams, $this->thread->thread_subject);
                                        }
                                }
                        }

                        if (isset($this->post_uid) && $this->post_uid) {
                                $tx_chcforum_post = t3lib_div::makeInstanceClassName("tx_chcforum_post");
                                $post = new $tx_chcforum_post($this->post_uid, $this->cObj);
                                if ($post->uid) {
                                        $lparams = array('view' => 'single_post',
                                                'cat_uid' => $this->cat_uid,
                                                'conf_uid' => $this->conf_uid,
                                                'thread_uid' => $this->thread_uid,
                                                'post_uid' => $this->post_uid);
                                        if ($this->view == 'single_post') {
                                                $nav_path .= '&nbsp;'.$nav_div.$post->post_subject;
                                        } 
                                }
                        }
                        $type = 'nav_path';
                        if (empty($nav_path)) {
                                $nav_path = tx_chcforum_shared::lang('err_no_auth_msg_dflt');
                                $type = 'error';
                        }
                        $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                        $message = new $tx_chcforum_message($this->cObj, $nav_path, $type);
                        return $message->display();
                }

                /**
                * Returns an appropriate message if a user is not authenticated properly.
                *
                * @param string  $type: this can be either 'conf' or 'cat' (or empty for default).
                * @return string  returns the message box scolding user for trying to access something without authentication.
                */
                function no_auth_msg ($type) {
                        switch ($type) {
                                case 'conf':
                                $text = tx_chcforum_shared::lang('err_no_auth_msg_conf');
                                break;
                                case 'cat':
                                $text = tx_chcforum_shared::lang('err_no_auth_msg_cat');
                                break;
                                default:
                                $text = tx_chcforum_shared::lang('err_no_auth_msg_dflt');
                                break;
                        }
                        $tx_chcforum_message = t3lib_div::makeInstanceClassName("tx_chcforum_message");
                        $message = new $tx_chcforum_message($this->cObj, $text, 'error');
                        $out = $message->display();
                        $message = new $tx_chcforum_message($this->cObj, tx_chcforum_shared::makeLink(false, tx_chcforum_shared::lang('err_no_auth_msg_return')), 'link');
                        $out .= $message->display();
                        return $out;
                }
        }

        if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_display.php']) {
                include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_display.php']);
        }


?>