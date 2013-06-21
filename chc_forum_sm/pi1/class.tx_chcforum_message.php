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
	* This is a rather modest class, used to create the little message boxes
	* that show up periodically throughout the extension. To make a message, 
	* make an instance of this class and pass the text and type to the constructor.
	* Call the display method to output the HTML.
	*
	* @author Zach Davis <zach@crito.org>
	*/
	class tx_chcforum_message {
		var $text;
		var $type;
		 
		/**
		* Message object constructor
		*
		* @param object $cObj:  the cObj that gets passed to every class constructor.
		* @param string $text:  the text that gets displayed in the message box.
		* @param string $type:  determines which template block to use.
		* @return void
		*/		 
		function tx_chcforum_message ($cObj, $text, $type = 'error') {
			$this->cObj = $cObj; $this->conf = $this->cObj->conf;
			$this->text = $text;
			$this->type = $type;			 

			// bring in the fconf.
			$this->fconf = $this->cObj->fconf;

			// bring in the user object.
			$this->user = $this->fconf['user'];
		}
		 
		/**
		* Outputs the HTML for the message object.
		*
		* @return string  message HTML.
		*/
		function display () {
			if (!$this->tmpl_path) {
				$this->tmpl_path = tx_chcforum_shared::setTemplatePath();
			}
			$tx_chcforum_tpower = t3lib_div::makeInstanceClassName("tx_chcforum_tpower");
			$tmpl = new $tx_chcforum_tpower($this->tmpl_path.'message_box.tpl');
			$tmpl->prepare();
			$tmpl->newBlock($this->type);
			$tmpl->assign('text', $this->text);
			$this->html_out = $tmpl->getOutputContent();			 
			return $this->html_out;
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_message.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/chc_forum/pi1/class.tx_chcforum_message.php']);
	}
	 
?>
