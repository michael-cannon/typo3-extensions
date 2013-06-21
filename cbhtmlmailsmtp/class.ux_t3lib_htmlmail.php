<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Peimic.com (michael@peimic.com)
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

require_once 'Mail.php';

/** 
 * SMTP extension of t3lbib_htmlmail's class.
 *
 * @author	Michael Cannon <michael@peimic.com>
 */
class ux_t3lib_htmlmail extends t3lib_htmlmail {
	/**
	 * PEAR::Mail object
	 */
	var $mailObj = NULL;

	/**
	 * Sends a mail to one recipient, using the configured SMTP-Host
	 *
	 * @return	bool	Result is only false if recipient or message is empty.
	 */
	function sendTheMail () {
		$config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cbhtmlmailsmtp'];
		$headerlines = explode("\n",trim($this->headers));
		for($i = 0; $i < count($headerlines); $i++)
		{
			//--- Header Content-Type fixed 19.05.2006 Alexander Bohndorf
			if(substr($headerlines[$i],0,9)==" boundary")
				$headers['Content-Type'] .= "\n " . $headerlines[$i];
			else
			{
				$current = explode(':',$headerlines[$i]);
				$headers[$current[0]] = $current[1];
			}
		}
		$headers['To']      = $this->recipient;
		$headers['Subject'] = $this->subject;
		$body = $this->message;
		$recipients = $this->recipient;
		
		if (!is_a($this->mailObj, 'Mail_smtp') || $config['smtpPersist'] == 1) {
			$this->mailObj = NULL;
			$params = array('host' => $config['smtpHost'],
							'username' => $config['smtpUser'],
							'password' => $config['smtpPass'],
							'auth' => ($config['smtpAuth']==1),
							'persist' => ($config['smtpPersist']==1));
			$this->mailObj =& Mail::factory('smtp', $params);
		}
		
			// Sends the mail.
			// Requires the recipient, message and headers to be set.
		if (trim($this->recipient) && trim($this->message))	{	//  && trim($this->headers)
			$res = $this->mailObj->send($this->recipient, $headers, $this->message);

				// Sending copy:
			if ($this->recipient_copy)	{
				$res = $this->mailObj->send($this->recipient_copy, $headers, $this->message);
			}
				// Auto response
			if ($this->auto_respond_msg)	{
				$theParts = explode('/',$this->auto_respond_msg,2);
				$theParts[1] = str_replace("/",chr(10),$theParts[1]);
				// Create the mail object using the Mail::factory method
				$headers['Subject'] = $theParts[0];
				$headers['From'] = $this->recipient;
				$res = $this->mailObj->send($this->from_email, $headers, $theParts[1]);
			}
			return true;
		} else {
			return false;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbhtmlmailsmtp/class.ux_t3lib_htlmail.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbhtmlmailsmtp/class.ux_t3lib_htlmail.php']);
}
?>