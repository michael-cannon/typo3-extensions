<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Peter Luser (netdog@typoheads.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Plugin 'mailformplus' for the 'th_mailformplus' extension.
 *
 * @author	Peter Luser <netdog@typoheads.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_thmailformplus_pi1 extends tslib_pibase {

	var $prefixId = "tx_thmailformplus_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_thmailformplus_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "th_mailformplus";	// The extension key.
#	var $cObj;    // reference to the calling object.
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{

	    global $TSFE; // new in release 2.2: automatic "no-cache" pagesetting
	    $TSFE->set_no_cache();
	    $this->conf = $conf;
		$this->get_post = array_merge(t3lib_div::_GET(), t3lib_div::_POST());
		$this->debug = $this->conf['saveDB.']['debug'];


		switch((string)$conf["CMD"])	{
			case "singleView":
				print "singleView<br>";
				list($t) = explode(":",$this->cObj->currentRecord);
				$this->internal["currentTable"]=$t;
				$this->internal["currentRow"]=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:

			    $mailformplus_id = $this->cObj->data['pages'];
				# default: display the form from the page where the plugin is inserted
			    if (!$mailformplus_id) $mailformplus_id = $this->cObj->data['pid'];
			    
                                // Initializing the query parameters:
			    $query = "SELECT tx_thmailformplus_main.* FROM tx_thmailformplus_main WHERE pid IN (".$mailformplus_id.") LIMIT 0,1";
			    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query); 
			    if (mysql_error())      debug(array(mysql_error(),$query));
			    $this->internal["currentTable"] = "tx_thmailformplus_main";
			    $this->internal["currentRow"] = mysql_fetch_array($res);
			    
			    #############
			    # template
			    #############
			    t3lib_div::loadTCA('tx_thmailformplus_main');
			    $config = $GLOBALS['TCA']['tx_thmailformplus_main'];
			    $template_folder = $config[columns][email_htmltemplate][config][uploadfolder];
	
			    $this->templateCode_orig = $this->cObj->fileResource($template_folder.'/'.$this->getFieldContent("email_htmltemplate"));
			    
			    # Form was submitted
			    if (t3lib_div::GPvar("SUBMITTED") || t3lib_div::GPvar("submitted")) {
				$this->loadMapping($conf);
    			$this->templateCode_error = $this->cObj->getSubpart($this->templateCode_orig,"###TEMPLATE_ERROR###");
				$error = $this->check_form($content,$conf);
				if ($error) {
					# self defined error message start
				    $temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, "ERROR_START");
				    if ($temp) {
					$error = $temp.$error;
				    } else {
					$error = 'Please fill out the following fields: <ul>'.$error.'</ul>'; # substr($error,0,strlen($error)-2);
				    }

					# self defined error message end
				    $temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, "ERROR_END");
				    if ($temp) {
					$error .= $temp;
				    }

				    $this->error = $error;
				    $this->templateCode = $this->cObj->getSubpart($this->templateCode_orig,"###TEMPLATE_FORM###");
				    return $this->show_form($content,$conf);
				} else {
				    $this->templateCode = $this->cObj->getSubpart($this->templateCode_orig,"###TEMPLATE_SUBMITTED_OK###");
    				$this->templateCode_useremail = $this->cObj->getSubpart($this->templateCode_orig,"###TEMPLATE_EMAIL_USER###");
    				$this->templateCode_receiver = $this->cObj->getSubpart($this->templateCode_orig,"###TEMPLATE_EMAIL_RECEIVER###");
					$this->send_form($content,$conf);
    				return $this->templateCode;
				}
			    } else {
				$this->templateCode = $this->cObj->getSubpart($this->templateCode_orig,"###TEMPLATE_FORM###");
				# compatibility to old version (no placeholder specified)
				if (!$this->templateCode) $this->templateCode = $this->templateCode_orig;
				return $this->show_form($content,$conf);
			    }
			    
#			    print_r(mysql_fetch_array($res));
			    
#				if (strstr($this->cObj->currentRecord,"tt_content"))	{
#					$conf["pidList"] = $this->cObj->data["pages"];
#					$conf["recursive"] = $this->cObj->data["recursive"];
#				}
#				print_r($this->cObj);
#				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}
	
	
	function loadMapping($conf) {


		##################### corrected by Robert start #############
                $this->conf['saveDB'] = array ();
		##################### corrected by Robert end #############              

		# data is not configured to be saved in DB or
		# no database table given or
		# no mapping rules specified
	    if (($conf['saveDB'] != 1) || (!$conf['saveDB.']['dbTable']) || (!$conf['saveDB.']['mapping'])) {
			if ($this->debug == 1) { print "no config for saving data in a user-table<br>";}
			return;
	    }
	    
	    $this->conf['saveDB']['dbTable'] = $conf['saveDB.']['dbTable'];
	    $this->conf['saveDB']['uploadfolder'] = $conf['saveDB.']['fileUpload'];

		# key for table specified?
		##################### corrected by Robert start############# 
	    if (isset($conf['saveDB.']['dbkey'])) {
			$this->conf['saveDB']['dbkey'] = $conf['saveDB.']['dbkey'];
	    } else {	   
			$this->conf['saveDB']['dbkey'] = 'uid'; 
	    }
		##################### corrected by Robert end #############   
		if ($this->debug == 1) {print "DB-key is set to: ".$this->conf['saveDB']['dbkey'].'<br>';}


		# only file of this type can be uploaded (fileTypes = jpg,gif,png,jpeg)
	    if ($conf['saveDB.']['fileTypes']) {
			$this->conf['saveDB']['allowedTypes'] = explode(",", $conf['saveDB.']['fileTypes']);
			if ($this->debug == 1) {print "only some filetypes are allowed: ".$this->conf['saveDB']['allowedTypes'].'<br>';}
	    }
	    
		# file size is limitted
	    if ($conf['saveDB.']['fileSize']) {
			$this->conf['saveDB']['allowedSize'] = $conf['saveDB.']['fileSize'];
			if ($this->debug == 1) {print "file size is limitted to: ".$this->conf['saveDB']['allowedSize'].'<br>';};
	    }
	    
		# fixed values should be inserted into DB
	    if ($conf['saveDB.']['dbinsert']) {
			$temp = explode(",", $conf['saveDB.']['dbinsert']);
			foreach ($temp as $temp2) {
					list($dbfield,$value) = explode(":", $temp2);
					$this->conf['saveDB']['dbinsert'][$dbfield] .= $value;
			}
	    }
		if ($this->debug == 1) {print "fixed values should be inserted into DB. <br>\n";print_r($this->conf['saveDB']['dbinsert']);}

		# prepare mapping form inputfields to DB fields
	    $temp = explode(",", $conf['saveDB.']['mapping']);
	    foreach ($temp as $temp2) {
			list($inputField,$dbField) = explode(":", $temp2);
			$this->conf['saveDB']['mapping'][$inputField] = $dbField;
			$this->conf['saveDB']['dbinsert'][$dbField] .= t3lib_div::GPvar($inputField);
	    }
	
		if ($this->debug == 1) {print "These fields are inserted into the DB:<br>\n"; print_r($this->conf['saveDB']['dbinsert']);}

#		# standard fields (if not overruled from user)
#	    if (!$this->conf['saveDB']['dbinsert']['pid']) {
#			$this->conf['saveDB']['dbinsert']['pid'] = $GLOBALS["TSFE"]->id;
#	    }
	
	}
	
	function send_form($content,$conf) {
	    $this->get_post = $this->get_post; # array_merge(t3lib_div::_GET(), t3lib_div::_POST());

	    $email_subject = $this->getFieldContent("email_subject");
	    $email_redirect = $this->getFieldContent("email_redirect");
	    $email_sender = $this->getFieldContent("email_sender");
	    $email_to = $this->getFieldContent("email_to");
	    $email_replyto = $this->getFieldContent("email_replyto");

		# since 18.10.2005: prevent mail injection (reported by Joerg Schoppet - thx!)
	    if (eregi("\r",$email_sender) || eregi("\n",$email_sender)) {
		$email_sender = '';
	    }
	    if (eregi("\r",$email_subject) || eregi("\n",$email_subject)) {
		$email_subject = '';
	    }

	    # use the submitted email address of the specified inputfield as reply-to in the sent email	
	    if ($email_replyto && $this->get_post[$email_replyto] && !eregi("\r",$this->get_post[$email_replyto]) && !eregi("\n",$this->get_post[$email_replyto])) {
		$email_replyto = $this->get_post[$email_replyto];
	    } else {
		$email_replyto = $email_sender;
	    }


	    # define MarkerArray for replacing emails
	    $globalMarkerArray=array();

	    if (is_array($this->get_post)) {
		foreach($this->get_post as $k=>$v) {
		    if (!ereg('EMAIL_', $k)) {
    			$globalMarkerArray['###value_'.$k.'###'] = $v;
			$globalMarkerArray['###'.$k.'###'] = $v;
			$globalMarkerArray['###checked_'.$k.'_'.$v.'###'] = 'checked';
			
			    # this is for log file in table tx_thmailformplus_log
			    # saving all submitted forms in CSV format
			$csv_firstline .= $k.';';
			$csv_line .= $v.';~';
			
		    }
		}
	    }

		############################
		# write into log-table in specified order
		# plugin.tx_thmailformplus_pi1.saveLog.order = [formular input name],[formular input name],...
		#
		# exclude the fields defined in
		# plugin.tx_thmailformplus_pi1.saveLog.exclude = [formular input name],[formular input name],...
		#
		# if no value is specified, take '' as default value, or (if specified):
		# plugin.tx_thmailformplus_pi1.saveLog.defaultValue = 0
		############################
	    if ($conf['saveLog'] == 1) {
	    
		    # an order is specified in which the fields should be saved
		    # else: take all fields
		if ($conf['saveLog.']['order']) {
		    $saveFields = array_map("trim", explode(',', $conf['saveLog.']['order']));
		} else {
		    $saveFields = array_keys($this->get_post);
		}
		
		    # fields should be excluded
		if ($conf['saveLog.']['exclude']) {
		    $excludeFields = array_map("trim", explode(',', $conf['saveLog.']['exclude']));
		}
		
		$csv_firstline = '';
		$csv_line = '';

		    # set default value
		$defaultValue = $conf['saveLog.']['defaultValue'];
		if (!$defaultValue && $defaultValue != '0') {
		    $defaultValue = '';
		}
		
		    # loop trough all specified fields that have to be saved
		foreach ($saveFields as $k) {
			# only save this field if it's not in the "exclude" list (TS)
		    if (!is_array($excludeFields) || !in_array($k, $excludeFields)) {
			$csv_firstline .= $k.';';
			if ($this->get_post[$k]) {
			    $csv_line .= $this->get_post[$k].';~';
			} else {
			    $csv_line .= $defaultValue.';~';
			}
		    } # end if in_array
		}

	    }


		############################
		# post-process data with user func
		# thx to Martin Kutschker
		#
		# example usage:
		#
		# class tx_myext {
		#	function doit(&$params, &$ref){
		#		...
		#	}
		# }
		#
		# plugin.tx_thmailformplus_pi1 {
		# 	saveUserFunc = EXT:myext/class.tx_myext.php:tx_myext->doit
		# 	saveUserFunc.dummy = hello
		# }
		#
		############################
		if ($this->conf['saveUserFunc']) {
		    $params['config'] = $this->conf['saveUserFunc.'];
		    $params['data'] = $this->get_post;
		    t3lib_div::callUserFunction($this->conf['saveUserFunc'],$params,$this);
		}


		############################
		# insert into standard DB (access via th_mailformplus backend module)
		############################
	    $csv_firstline = substr($csv_firstline, 0, strlen($csv_firstline)-1)."\n";
	    $csv_line = substr($csv_line, 0, strlen($csv_line)-2);
	    $insertArray = array(
		'pid' => $GLOBALS["TSFE"]->id,
		'submittedfields' => $csv_firstline.$csv_line,
		'date' => date("Y-m-d H:i:s", time())
	    );
	    $query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_thmailformplus_log', $insertArray);
	    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);


		############################
		# insert into user specified table
		############################
	    if ($this->conf['saveDB']['dbTable']) {
		    $insertArray = $this->conf['saveDB']['dbinsert'];

    		    $query = $GLOBALS['TYPO3_DB']->INSERTquery($this->conf['saveDB']['dbTable'], $insertArray);
		    if ($this->debug == 1) {
			print "executing this query for saving data in user specified table:<br>".$query.'<br><br>';
		    }
		    
		    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		    # insert OK
		    if ($res) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			    'max('.$this->conf['saveDB']['dbkey'].') as max',
			    $this->conf['saveDB']['dbTable'],
			    '');
			if ($res && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			    $this->conf['saveDB']['id'] = $row['max'];
			}
			if ($this->debug == 1) { print "insert sucessfully! generated UID: ".$row['max']."<br>\n"; }
		    } else {
			if ($this->debug == 1) { print "insert FAILED: ".mysql_error()."<br>\n"; }
		    }
	    }


		############################
		# file uploads!
		# if a file upload directory is specified in TypoScript
		# filename: [pageID]_[nameOfInputfield]_[newRecordID].[filetype]
		############################
	    $email_text_uploads = '';
	    if ($this->conf['saveDB']['uploadfolder'] && is_array($_FILES) && sizeof($_FILES) > 0) {
			$i = '1';
			foreach (array_keys($_FILES) as $file) {
					# find out file type
				if ($_FILES[$file]['name']) {
					list(,$type)= explode('.', $_FILES[$file]['name']);
					$upload = $this->conf['saveDB']['uploadfolder'].$GLOBALS["TSFE"]->id.'_'.$file.'_'.$this->conf['saveDB']['id'].'.'.$type;
					$temp = '';
					if ($updateArray[$this->conf['saveDB']['mapping'][$file]] > 0) {
					    $temp = ',';
					}
					if ($this->conf['saveDB']['mapping'][$file]) {
    					    $updateArray[$this->conf['saveDB']['mapping'][$file]] .= $temp.$GLOBALS["TSFE"]->id.'_'.$file.'_'.$this->conf['saveDB']['id'].'.'.$type;
					}
					
					move_uploaded_file($_FILES[$file]['tmp_name'], $upload);
					if ($this->debug == 1) { print "file sucessfully saved as: ".$upload."<br>\n";}
					$upload = "http://".$_SERVER["HTTP_HOST"]."/".$upload;
					$globalMarkerArray["###UPLOAD".$i."###"] = $upload; # for compatibility reasons
					$globalMarkerArray["###".$file."###"] = $upload;
					$email_text_uploads .= "$file: $upload\n";
				}
				$i++;
			}
	    }
		# make update of record for uploaded files
	    $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->conf['saveDB']['dbTable'], $this->conf['saveDB']['dbkey'].'='.$this->conf['saveDB']['id'], $updateArray);

	    $globalMarkerArray["###EMAIL_SUBJ###"] = $this->getFieldContent("email_subject");
	    $globalMarkerArray["###EMAIL_REDIRECT###"] = $this->getFieldContent("email_redirect");
	    $globalMarkerArray["###EMAIL_SENDER###"] = $this->getFieldContent("email_sender");
	    $globalMarkerArray["###EMAIL_TO###"] = $this->getFieldContent("email_to");
	    $globalMarkerArray["###EMAIL_REQUIREDFIELDS###"] = $this->getFieldContent("email_requiredfields");
	    // $globalMarkerArray['###PID###'] = $this->get_post['id'];
	    $globalMarkerArray['###PID###'] = $GLOBALS["TSFE"]->id;
	    $globalMarkerArray['###ERROR###'] = $this->error;


	    # send only if receiver is specified
	    if ($email_to) {

				# additional header
				# if no character set is specified, take default typo3 character set
			$conf['emailHeader'] = str_replace('\r', "\r", str_replace('\n', "\n", $conf['emailHeader']));
			if (!stristr($conf['emailHeader'], 'charset') && !stristr($conf['emailHeader'], 'content-type')) {
				$conf['emailHeader'] .= 'Content-Type: text/plain; charset="'.$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'].'"'."\r\n";
			}

			
			# if no special TEMPLATE for the email to the mailformplus receiver is specified
			# send mail with [fieldname]: [value]
			if (!$this->templateCode_receiver) {
				if (is_array($this->get_post)) {
					foreach($this->get_post as $k=>$v) {
						# don't show hidden config values in email
						if (!ereg('EMAIL_.',$k) && $k!='id' && $k!='x' && $k!='y') {
							$mail_text .= "$k: $v\n";
						}
					}
				}
				$mail_text .= $email_text_uploads;
			} else {
				$this->templateCode_receiver = $this->cObj->substituteMarkerArray($this->templateCode_receiver, $globalMarkerArray);
				$this->templateCode_receiver = ereg_replace('###[A-Za-z_1234567890]+###', '', $this->templateCode_receiver);
				$mail_text = $this->templateCode_receiver;
			}
							
		    
			$header = array();
			if ($email_sender) {
			    $header[] = "From: ".$email_sender;
			}
			if ($email_replyto) {
			    $header[] = "Reply-To: ".$email_replyto;
			}
			if ($conf['emailHeader']) {
			    $header[] = $conf['emailHeader'];
			}
			$email_header = implode("\r\n", $header);

			$mail_text			= stripslashes( $mail_text );

			if ($this->debug == 1) { 
			    print "email is sent: <br>\n---------------<br>\n";
			    print "EMAIL receiver: ".$email_to."<br>\n";
			    print "EMAIL subject: ".$email_subject."<br>\n";
			    print "EMAIL text: ".$mail_text."<br>\n";
			    print "EMAIL header: ".$email_header."<br>\n";
			    print "EMAIL parameters: ".$conf['emailParameter']."<br>\n------------------<br>\n";
			}

			# send mail 
			$emailto = split(',', $email_to);
			if (is_array($emailto)) {
				foreach ($emailto as $mailto) {
					# since 18.10.2005: prevent mail injection (reported by Joerg Schoppet - thx!)
					# $subject and $email_header are checked for mail injection as well before
				    if (strstr($mailto, '@') && !eregi("\r",$mailto) && !eregi("\n",$mailto)) {
					mail($mailto, $email_subject, $mail_text, $email_header, $conf['emailParameter']);
				    }
				}
			}

			// MLC CoolerEmail addition
			if ( $this->conf[ 'coolerEmail.' ][ 'enable' ] )
			{
				// look for passed parameter with cooler information
				// key is coolerEmail
				// list name may be passed with key as 'coolerEmail|SpurDigital'
				foreach ( $this->get_post as $key => $value )
				{
					if ( preg_match( '#coolerEmail#', $key )
						&& cbIsTrue( $value )
					)
					{
						// split to grab list
						$list	= explode( '|', $key );
						$list	= ( isset( $list[ 1 ] ) )
									? $list[ 1 ]
									: $this->conf[ 'coolerEmail.' ][ 'list' ];

						// build up URL
						$url	= $this->conf[ 'coolerEmail.' ][ 'action' ];
						$username		= $this->conf[ 'coolerEmail.' ][
											'username' ];
						$notification	= $this->conf[ 'coolerEmail.' ][
											'notification' ];

						// curl it
						$url	= $url
									. '?username=' . $username
									. '&notification=' . $notification
									. '&list=' . $list
									. '&email=' . $this->get_post[ 'email' ]
									;

						// cbDebug( 'url', $url );
						// cbDebug( 'curl', cbCurlUrl( $url ) );
						cbCurlUrl( $url );
					}
				}
			}
	    }
	    
	    # if an email should be sent to the user as well:
	    if ($this->get_post[$this->getFieldContent("email_sendtouser")] && strstr($this->get_post[$this->getFieldContent("email_sendtouser")], '@')) {
	    
		
			# special ###TEMPLATE_EMAIL_USER### specified: replace placeholders
			if ($this->templateCode_useremail) {
			
				$this->templateCode_useremail = $this->cObj->substituteMarkerArray($this->templateCode_useremail, $globalMarkerArray);
				$this->templateCode_useremail = ereg_replace('###[A-Za-z_1234567890]+###', '', $this->templateCode_useremail);
				$mail_text = $this->templateCode_useremail;

			}
			
			$email_header = '';
			if ($email_sender) {
			    $email_header = "From: ".$email_sender."\r\nReply-To: ".$email_sender;
			}
			if ($conf['emailHeader']) {
			    $email_header .= "\r\n".$conf['emailHeader'];
			}
			
			$subject = $this->getFieldContent("email_subject_user");
			if (eregi("\r",$subject) || eregi("\n",$subject)) {
			    $subject = '';
			}
						
			$mail_text			= stripslashes( $mail_text );

			if ($this->debug == 1) { 
			    print "email is sent: <br>\n---------------<br>\n";
			    print "EMAIL receiver: ".$this->get_post[$this->getFieldContent("email_sendtouser")]."<br>\n";
			    print "EMAIL subject: ".$subject."<br>\n";
			    print "EMAIL text: ".$mail_text."<br>\n";
			    print "EMAIL header: ".$email_header."<br>\n";
			    print "EMAIL parameters: ".$conf['emailParameter']."<br>\n------------------<br>\n";
			}

			# send mail 
			$emailto = split(',', $this->get_post[$this->getFieldContent("email_sendtouser")]);
			if (is_array($emailto)) {
				foreach ($emailto as $mailto) {
					    # since 18.10.2005: prevent mail injection (reported by Joerg Schoppet - thx!)
					    # $subject and $email_header are checked for mail injection as well before
					if (strstr($mailto, '@') && !eregi("\r",$mailto) && !eregi("\n",$mailto)) {
						mail($mailto, $subject, $mail_text, $email_header, $conf['emailParameter']);
					}
				}
			}

	    }
										
	    # redirect, if url is specified
	    if ($email_redirect) {
			if (is_numeric($email_redirect)) {
				// these parameters have to be added to the redirect url
				$addparams = array();
				if (t3lib_div::_GP("L")) {
				    $addparams["L"] = t3lib_div::_GP("L");
				}
				    // bugfixed - thx to Sebastian F.:: $url = 'index.php?id='.$email_redirect;
				$url = $this->pi_getPageLink($email_redirect, '',$addparams); # $GLOBALS["TSFE"]->sPre
				    // bugifxed - thx to Dominik Bohm :: correct redirect when using realurl
				$url = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$url;
			} else {
				$url = $email_redirect;
			}
			header("Location: ".$url."\n\n");
	    }
	    $this->templateCode = $this->cObj->substituteMarkerArray($this->templateCode, $globalMarkerArray);
	    $this->templateCode = ereg_replace('###[A-Za-z_1234567890]+###', '', $this->templateCode);
	
	}
	
	
	
	function check_form($content,$conf) {

	    $this->get_post = array_merge($_GET, $_POST);
	    t3lib_div::loadTCA('tx_thmailformplus_main');
	    $config = $GLOBALS['TCA']['tx_thmailformplus_main'];
	    
	    if (strlen(trim($this->getFieldContent("email_requiredfields"))) > 0) {
			$array_check = split(',', $this->getFieldContent("email_requiredfields"));
			$array_check = array_map('trim', $array_check);
			
			if (is_array($array_check)) {
				foreach ($array_check as $check) {
					if (!$this->get_post[$check]) {
						$temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, 'ERROR_'.$check);
						if ($temp) {
						$error .= $temp;
						$this->errors['###error_'.$check.'###'] = $temp;
						}
						else {
						$error .= "<li>".ucfirst($check);
						}
					}

					elseif ( 'email' == $check
						&& !preg_match(
						"#^[^@\s]+@([-a-z0-9]+\.)+(cc|com|net|edu|org|gov|mil|int|biz|pro|info|arpa|aero|coop|name|museum)$#ix"
							, $this->get_post[$check]
						)
					)
					{
						$temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, 'ERROR_'.$check);
						if ($temp) {
							$error .= $temp;
							$this->errors['###error_'.$check.'###'] = $temp;
						}
					}
				}
			}
		}


		############################
		# captcha check
		############################
		# is captcha configured to be used:
	    if ($this->conf['captchaFieldname']) {
		    # get captcha sting
		session_start();
		$captchaStr = $_SESSION['tx_captcha_string'];
		$_SESSION['tx_captcha_string'] = '';

		if ($captchaStr != $this->get_post[$this->conf['captchaFieldname']]) {
		    $temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, 'ERROR_'.$this->conf['captchaFieldname']);
		    if ($temp) {
			$error .= $temp;
			$this->errors['###error_'.$this->conf['captchaFieldname'].'###'] = $temp;
		    } else {
			$error .= "<li>".ucfirst($this->conf['captchaFieldname']);
		    }
		}
	    
	    }


		############################
		# file uploads!
		# if a file upload directory is specified in TypoScript
		# filename: [pageID]_[nameOfInputfield]_[newRecordID].[filetype]
		############################
		# uploadfolder does not exist...
	    if ($this->conf['saveDB']['uploadfolder'] && !is_dir($this->conf['saveDB']['uploadfolder'])) {
		t3lib_div::mkdir($this->conf['saveDB']['uploadfolder']);
		if (!is_dir($this->conf['saveDB']['uploadfolder']) && $this->debug == 1) {
		    print "could not create upload folder: ".$this->conf['saveDB']['uploadfolder']."<br>\n";
		} elseif ($this->debug == 1) {
		    print "upload folder created: ".$this->conf['saveDB']['uploadfolder']."<br>\n";
		}
	    } elseif ($this->conf['saveDB']['uploadfolder'] && $this->debug == 1) {
		print "upload folder already exists: ".$this->conf['saveDB']['uploadfolder']."<br>\n";
	    }
	    if ($this->conf['saveDB']['uploadfolder'] && is_array($_FILES) && sizeof($_FILES) > 0) {
			foreach (array_keys($_FILES) as $file) {
				# find out file type
			    list(,$type)= explode('.', $_FILES[$file]['name']);
				if (is_array($this->conf['saveDB']['allowedTypes']) && 
					sizeof($this->conf['saveDB']['allowedTypes']) > 0 && 
					$_FILES[$file]['name'] && 
					!in_array($type, $this->conf['saveDB']['allowedTypes'])) {
					$temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, "ERROR_FILETYPE");
					if ($temp) {
						$error .= $temp;
						$this->errors['###error_filetype###'] = $temp;
					}
					else {
						$error .= "<li>Filetype not allowed";
					}
					if ($this->debug == 1) {
					    print "File '".$type."' not allowed in ".implode(',',$this->conf['saveDB']['allowedTypes'])."<br>\n";
					}
				}
				
				if ($this->conf['saveDB']['allowedSize'] && ($_FILES[$file]['size'] > $this->conf['saveDB']['allowedSize'])) {
					$temp = $GLOBALS["TSFE"]->cObj->getSubpart($this->templateCode_error, "ERROR_FILESIZE");
					if ($temp) {
						$error .= $temp;
						$this->errors['###error_filesize###'] = $temp;
					}
					else {
						$error .= "<li>File is too large";
					}
				}

			}
	    }



	    return $error;
	}
	
	# shows html-template
	function show_form($content,$conf) {

	    $this->get_post = array_merge($_GET, $_POST);

	    $globalMarkerArray=array();
	    
		# merge error-array with globalMarkerArray
	    if (is_array($this->errors)) {
		$globalMarkerArray = $this->errors;
	    }

		#############################
		# define markers
		#############################
	    if (is_array($this->get_post)) {
		foreach($this->get_post as $k=>$v) {
		    if (!ereg('EMAIL_', $k)) {
    			$globalMarkerArray['###value_'.$k.'###'] = $v;
			$globalMarkerArray['###checked_'.$k.'_'.$v.'###'] = 'checked';
			$globalMarkerArray['###selected_'.$k.'_'.$v.'###'] = 'selected';
		    }
		}
	    }

		#############################
		# if user is logged in - make user data accessible via "###FEUSER_[field]###"
		#############################
	    if (is_array($GLOBALS["TSFE"]->fe_user->user)) {
		foreach($GLOBALS["TSFE"]->fe_user->user as $k=>$v) {
		    $globalMarkerArray['###FEUSER_'.strtoupper($k).'###'] = $v;
		}
	    }

		#############################
		# markers defined via TypoScript 
		# example: plugin.tx_thmailformplus_pi1.markers.abteilungen < temp.jsmenu
		#############################
	    if (is_array($conf['markers.'])) {

		$lastKey = '';
		foreach (array_keys($conf['markers.']) as $key) {
		    # key ["marker"] not ["marker."]
		    if (FALSE == strstr($lastKey, '.') && !is_array($conf['markers.'][$key])) {
			$lastKey = $key;
		    } else {
			$ts_marker = $this->cObj->cObjGetSingle($conf['markers.'][$lastKey], $conf['markers.'][$lastKey.'.']);
			$globalMarkerArray['###'.$lastKey.'###'] = $this->cObj->substituteMarkerArray($ts_marker, $globalMarkerArray);
#			print 'key gefunden und array dazu:'.$lastKey.'<br>';
		    }
		}
	    }


		################################
		# marker for captcha extension
		################################
	    if (t3lib_extMgm::isLoaded('captcha')){
		$globalMarkerArray["###CAPTCHA###"] = '<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="" />';
	    }

	    $globalMarkerArray["###EMAIL_SUBJ###"] = $this->getFieldContent("email_subject");
	    $globalMarkerArray["###EMAIL_REDIRECT###"] = $this->getFieldContent("email_redirect");
	    $globalMarkerArray["###EMAIL_SENDER###"] = $this->getFieldContent("email_sender");
	    $globalMarkerArray["###EMAIL_TO###"] = $this->getFieldContent("email_to");
	    $globalMarkerArray["###EMAIL_REQUIREDFIELDS###"] = $this->getFieldContent("email_requiredfields");
	    // // bugfixed - thx to Sebastian F.:: $globalMarkerArray['###PID###'] = $this->get_post['id'];
	    $globalMarkerArray['###PID###'] = $GLOBALS["TSFE"]->id;
	    $globalMarkerArray['###ERROR###'] = $this->error;
	    
	    $this->templateCode = $this->cObj->substituteMarkerArray($this->templateCode, $globalMarkerArray);
	    $this->templateCode = ereg_replace('###[A-Za-z_1234567890]+###', '', $this->templateCode);
	    return $this->templateCode;

    	    return $this->getFieldContent("email_to");
	
	}
	
	
	/**
	 * [Put your description here]
	 */
	function getFieldContent($fN)	{
	
		switch($fN) {
			case "uid":
				return $this->pi_list_linkSingle($this->internal["currentRow"][$fN],$this->internal["currentRow"]["uid"],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;
			
			default:
				# value is set
			    if ($this->internal["currentRow"][$fN]) {
				return $this->internal["currentRow"][$fN]; 
			    }   # no value set: take typoscript default setting 
			    else { 
				    // thx to Simon Glatz :: plugin.tx_thmailformplus_pi1.default.email_requiredfields.wrap = |,email
				return $this->cObj->stdWrap($this->conf['default.'][$fN], $this->conf['default.'][$fN."."]);
#				return $this->conf['default.'][$fN];
			    }
			break;
		}
	}
	
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/th_mailformplus/pi1/class.tx_thmailformplus_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/th_mailformplus/pi1/class.tx_thmailformplus_pi1.php"]);
}

?>