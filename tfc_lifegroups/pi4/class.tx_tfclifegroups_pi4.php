<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Tony McCallie <TonyMcCallie@tfchurch.org>
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
 * Plugin 'TFC Lifegroup Updater' for the 'tfc_lifegroups' extension.
 *
 * @author	Tony McCallie <TonyMcCallie@tfchurch.org>
 */

//TODO - update sendOneEmail() - recipient address, due date in message, sender name and email.

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('tfc_lifegroups','pi1/class.tx_tfclifegroups_pi1.php'));
require_once(PATH_t3lib.'class.t3lib_htmlmail.php');

class tx_tfclifegroups_pi4 extends tslib_pibase {
	var $prefixId = 'tx_tfclifegroups_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_tfclifegroups_pi4.php';	// Path to this script relative to the extension dir.
	var $extKey = 'tfc_lifegroups';	// The extension key.
	var $pi_checkCHash = FALSE;
	var $lgProperties = array(); // such as name, leader 1 firstname, leader 1 email, etc.
	
	var $templateFileContent = '';
	
	
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
		$this->pi_USER_INT_obj=1;
		
			// if nothing is submitted, don't display anything
			
			
			// if piVars['id'] is submitted, display the record if it has not been posted yet.
				// display form, containing current values of draft version.
		if ($this->piVars['id'] && (t3lib_div::testInt($this->piVars['id']) == true) ) {
				// if piVars['do_update'] == 'yes_do-update', validate and process the form.
					// email confirmation to someone at TFC
			if ($this->piVars['do_update'] == 'yes_do-update') {
				$content .= $this->processForm();
			}
			
			$queryAttempt = $this->getLgProperties($this->piVars['id']);
			if ($queryAttempt == 1) { // fills the array lgProperties {
				$content .= $this->displayForm($this->piVars['id']);
			} else {
				if ($this->piVars['do_update'] != 'yes_do-update') { // if we just did an update, don't show the following error
					$content .= $queryAttempt;
				}
			}
		}
			// if piVars['emailEveryone'] == true AND piVars['emailPassword'] == 'yes_really-do=it',
				// email invitation message to every email listed in tfc_lifegroups_lifegroups.
		if ($this->conf['showAdminLinks'] == 1) {
			$content .= $this->drawAdminLinks();
		}
		if ($this->piVars['emailEveryone'] == 'true' && $this->piVars['emailPassword'] == 'yes_really-do=it') {
			$content .= $this->sendEmailToLeaders(); // this will send out a bunch of emails
		}

	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function getLgProperties($id) {
		global $TYPO3_DB;
		$returnval = '';
		$addWhere = '';
		$currLabel = $this->conf['currLabel'];
		
			// git us some data! (one lifegroup record that needs to be updated)
//			$select_fields  	string  	List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
//			$from_table 	string 	Table(s) from which to select. This is what comes right after "FROM ...". Required value.
//			$where_clause 	string 	Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->quoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
//			$groupBy='' 	string 	Optional GROUP BY field(s), if none, supply blank string.
//			$orderBy='' 	string 	Optional ORDER BY field(s), if none, supply blank string.
//			$limit=''  	string  	Optional LIMIT value ([begin,]max), if none, supply blank string.
			
			// t3ver_oid is column that should contain the real uid of the lifegroup we want to update
			// t3ver_label is column that should contain a certain string for this round of updates.  Today, that is "Fall 2006"
		$addWhere = "t3ver_oid = '".$id."' AND t3ver_label = '".$currLabel."'";
		$res = $TYPO3_DB->exec_SELECTquery(
					'*',
					'tx_tfclifegroups_lifegroups',
					$addWhere.$this->cObj->enableFields('tx_tfclifegroups_lifegroups')
					);
		
			// only if numrows == 1
		$numrows = $TYPO3_DB->sql_num_rows($res);
		if ($numrows == 0) {
			$returnval = 'notice: this lifegroup can not be updated now, because either it has already been updated, or it is not ready to be updated.<br/>';
		} else if ($numrows > 1) {
			$returnval = 'error: too many records found.  Please notify the site administrator and report this error.<br/>';
		} else if ($numrows == 1) {
				// dump all lifegroup data into $this->lgProperties
			$this->lgProperties = $TYPO3_DB->sql_fetch_assoc($res);
			$returnval = 1;
		}
		
		// get name of parent section
//		$addWhere = "uid = '".$this->lgProperties['pid']."'";
//		$addWhere = "pages.uid = tx_tfclifegroups_lifegroups.pid AND tx_tfclifegroups_lifegroups.uid = ".$id;
//		$sectionres = $TYPO3_DB->exec_SELECTquery(
//					'title',
//					'pages',
//					$addWhere.$this->cObj->enableFields('pages')
//					);
		$sectionQuery = "SELECT pages.title 
						FROM pages, tx_tfclifegroups_lifegroups 
						WHERE pages.uid = tx_tfclifegroups_lifegroups.pid 
						AND tx_tfclifegroups_lifegroups.uid = ".$id;
		$sectionRes = $TYPO3_DB->sql_query($sectionQuery);
		$tempArr = $TYPO3_DB->sql_fetch_assoc($sectionRes);
		$this->lgProperties['sectionTitle'] = $tempArr['title'];
		
		return $returnval;
//			t3lib_div::debug((array)$this->lgProperties);
//			t3lib_div::debug($this->piVars);
	}
	
	/*
	 * @param id of lifegroup record to display.  Should be a "draft" version.
	 */
	function displayForm() {
		$content = '';
		$formtable = '';
		$substArray = array();
			
			// initialize template
		$this->templateFileContent = $this->cObj->cObjGetSingle($this->conf['altTemplateFile'],$this->conf['altTemplateFile.']);
		$formtable = $this->cObj->getSubpart($this->templateFileContent,'###UPDATE###');
//		$substArray['###ACTION###'] = t3lib_div::getIndpEnv('REQUEST_URI');
//		$substArray['###ACTION###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
		$substArray['###ACTION###'] = '';
		$substArray['###NAME###'] = $this->getFormField('NAME');
		$substArray['###LEADER1FIRSTNAME###'] = $this->getFormField('LEADER1FIRSTNAME');
		$substArray['###LEADER1LASTNAME###'] = $this->getFormField('LEADER1LASTNAME');
		$substArray['###LEADER1PHONE###'] = $this->getFormField('LEADER1PHONE');
		$substArray['###LEADER1EMAIL###'] = $this->getFormField('LEADER1EMAIL');
		$substArray['###LEADER2FIRSTNAME###'] = $this->getFormField('LEADER2FIRSTNAME');
		$substArray['###LEADER2LASTNAME###'] = $this->getFormField('LEADER2LASTNAME');
		$substArray['###LEADER2PHONE###'] = $this->getFormField('LEADER2PHONE');
		$substArray['###LEADER2EMAIL###'] = $this->getFormField('LEADER2EMAIL');
		$substArray['###DAY###'] = $this->getFormField('DAY');
		$substArray['###TIME###'] = $this->getFormField('TIME');
		$substArray['###LOCATION###'] = $this->getFormField('LOCATION');
		$substArray['###RECUR###'] = $this->getFormField('RECUR');
		$substArray['###CATEGORY###'] = $this->getFormField('CATEGORY');
		$substArray['###AGES###'] = $this->getFormField('AGES');
		$substArray['###INTERESTS###'] = $this->getFormField('INTERESTS');
		$substArray['###DESC###'] = $this->getFormField('DESC');
		$substArray['###HIDDENFIELDS###'] = $this->drawHiddenFields();
		
		$content .= $this->cObj->substituteMarkerArrayCached($formtable,$substArray);

		
		return $content;
	}
	
	function getFormField($name) {
		$fn = '';
		switch ($name) {
			case 'NAME':
				return $this->createInputField('title');
			break;
			case 'LEADER1FIRSTNAME':
				return $this->createInputField('leader1_firstname');
			break;
			case 'LEADER1LASTNAME':
				return $this->createInputField('leader1_lastname');
			break;
			case 'LEADER1PHONE':
				return $this->createInputField('leader1_phone');
			break;
			case 'LEADER1EMAIL':
				return $this->createInputField('leader1_email');
			break;
			case 'LEADER2FIRSTNAME':
				return $this->createInputField('leader2_firstname');
			break;
			case 'LEADER2LASTNAME':
				return $this->createInputField('leader2_lastname');
			break;
			case 'LEADER2PHONE':
				return $this->createInputField('leader2_phone');
			break;
			case 'LEADER2EMAIL':
				return $this->createInputField('leader2_email');
			break;
			
			case 'DAY':
				return $this->getDaysBox();
			break;
			case 'TIME':
				return $this->getTimeBox();
			break;
			
			case 'LOCATION':
				return $this->createInputField('location');
			break;
			
			
			case 'RECUR':
				return $this->getRecurrencesBox();
			break;
			case 'CATEGORY':
				return $this->getCategoriesBox();
			break;
			case 'AGES':
				return $this->getAgesBox();
			break;
			case 'INTERESTS':
				return $this->getInterestsBox();
			break;
			case 'DESC':
				return $this->createTextField('descr');
			break;
			
			default:
				return $name;
			break;
		}
	}
	
	function drawHiddenFields () {
		$content = '';
		$content .= '<input type="hidden" name="'.$this->prefixId.'[do_update]" value="yes_do-update">';
		$content .= '<input type="hidden" name="'.$this->prefixId.'[id]" value="'.$this->piVars['id'].'">';
		if (!t3lib_div::inList($this->lgProperties['semesters'], $this->conf['currSemesterID'])) {
			$this->lgProperties['semesters'] .= ','.$this->conf['currSemesterID'];
		}
		$content .= '<input type="hidden" name="'.$this->prefixId.'[semesters]" value="'.$this->lgProperties['semesters'].'">';
		$content .= '<input type="hidden" name="'.$this->prefixId.'[sectionTitle]" value="'.$this->lgProperties['sectionTitle'].'">';
		return $content;
	}
	
	function createInputField($name) {
		return '<input class="texty" type="text" name="'.$this->prefixId.'['.$name.']" value="'.$this->lgProperties[$name].'">';
	}
	function createTextField($name) {
		return '<textarea name="'.$this->prefixId.'['.$name.']">'.$this->lgProperties[$name].'</textarea>';
	}
	
	/*
	 * based on pi1::getDays()
	 */
	function getDaysBox() {
		$daysArray = tx_tfclifegroups_pi1::setDays();
		$sel = '';
		$html = '';
		for($i=0; $i<count($daysArray); $i++) {
			$sel = t3lib_div::inList($this->lgProperties['day'], key($daysArray))? ' checked="checked"': '';
			
			//$html .= '<input name="'.$this->prefixId.'[day]['.key($daysArray).']'.'" type="checkbox" value="1"'.$sel.'/> '.$daysArray[key($daysArray)].'<br/>
			$html .= '<input name="'.$this->prefixId.'[day][]'.'" type="checkbox" value="'.key($daysArray).'"'.$sel.'/> '.$daysArray[key($daysArray)].'<br/>
				';
			next($daysArray);
		}
		return $html;
	}
	function getAgesBox() {
		$agesArray = tx_tfclifegroups_pi1::setAges();
		$sel = '';
		$html = '';
		for($i=0; $i<count($agesArray); $i++) {
			$sel = t3lib_div::inList($this->lgProperties['ages'], key($agesArray))? ' checked="checked"': '';
			
			$html .= '<input name="'.$this->prefixId.'[ages][]'.'" type="checkbox" value="'.key($agesArray).'"'.$sel.'/> '.$agesArray[key($agesArray)].'<br/>
				';
			next($agesArray);
		}
		return $html;
	}
	function getInterestsBox() {
		$interestsArray = tx_tfclifegroups_pi1::setInterests();
		$sel = '';
		$html = '';
		for($i=0; $i<count($interestsArray); $i++) {
			$sel = t3lib_div::inList($this->lgProperties['interests'], key($interestsArray))? ' checked="checked"': '';
			
			$html .= '<input name="'.$this->prefixId.'[interests][]'.'" type="checkbox" value="'.key($interestsArray).'"'.$sel.'/> '.$interestsArray[key($interestsArray)].'<br/>
				';
			next($interestsArray);
		}
		return $html;
	}
	function getRecurrencesBox() {
		$recurrencesArray = tx_tfclifegroups_pi1::setRecurrences();
		$sel = '';
		$html = '';
		$html = '<select name="'.$this->prefixId.'[recurrence]" style="width: 200px">
					';
		for($i=0; $i<count($recurrencesArray); $i++) {
			$sel = $this->lgProperties['recurrence'] == key($recurrencesArray)? ' selected="selected"': '';
			
			$html .= '<option value="'.key($recurrencesArray).'"'.$sel.'>'.$recurrencesArray[key($recurrencesArray)].'</option>
				';
			next($recurrencesArray);
		}	
		$html.='</select>';
		return $html;
	}
	function getCategoriesBox() {
		$categoryArray = tx_tfclifegroups_pi1::setCategories();
		$sel = '';
		$html = '';
		$html = '<select name="'.$this->prefixId.'[category]" style="width: 200px">
					';
		for($i=0; $i<count($categoryArray); $i++) {
			$sel = $this->lgProperties['category'] == key($categoryArray)? ' selected="selected"': '';
			
			$html .= '<option value="'.key($categoryArray).'"'.$sel.'>'.$categoryArray[key($categoryArray)].'</option>
				';
			next($categoryArray);
		}	
		$html.='</select>';
		return $html;
	}
	
	function getTimeBox() {
		$html = '';
		$hours = array();
		$minutes = array(0=>'00',1=>'15',2=>'30',3=>'45');
		$selHour = 0;
		$selMinute = 0;
		$amSelected = ' checked="checked"';
		$pmSelected = '';
		$hours = $this->getHoursArray();
		
		// override $selHour and $selMinute from real stored time
		$selHour = t3lib_div::intval_positive($this->lgProperties['time'] / 60 / 60);
			// this would work too, but creates leading zeros.
			// $selHour = gmstrftime("%I", $this->lgProperties['time']);
		if ($selHour >12) {
			$selHour = $selHour - 12;
			$amSelected = '';
			$pmSelected = ' checked="checked"';
		}
		
		$selMinute = gmstrftime("%M", $this->lgProperties['time']);
//		echo('$selMinute: '.$selMinute.'<br/>'); // debugging
		switch (true) {
			case $selMinute >= 0 && $selMinute < 15 :
				$selMinute = 0;
			break;
			case $selMinute >= 15 && $selMinute < 30 :
				$selMinute = 1;
			break;
			case $selMinute >= 30 && $selMinute < 45 :
				$selMinute = 2;
			break;
			case $selMinute >= 45 :
				$selMinute = 3;
			break;
			default:
				$selMinute = 0;
			break;
		}
		
		$html .= '<select name="'.$this->prefixId.'[time]">
					';
		
		for($i=0; $i<count($hours); $i++) {
			for ($j=0; $j<count($minutes); $j++) {
				$sel = ($selHour == key($hours) && $selMinute == key($minutes))? ' selected="selected"': '';
				$dateVal = ($hours[key($hours)]*60*60) + ($minutes[key($minutes)]*60);
				$html .= '<option value="'.$dateVal.'"'.$sel.'>'.$hours[key($hours)].':'.$minutes[key($minutes)].'</option>
					';
				next($minutes);
			}
			reset($minutes);
			next($hours);
		}	
		$html.='</select>';
		$html.='<label><input name="'.$this->prefixId.'[ampm]" type="radio" value="am" '.$amSelected.' />
			AM</label>
			<label><input name="'.$this->prefixId.'[ampm]" type="radio" value="pm" '.$pmSelected.' />
			PM</label>';
		
		return $html;
	}
	function getHoursArray() {
		$hours = array();
//		for ($i = 0; $i < 24; $i++) { // for 24-hour system
		for ($i = 0; $i < 12; $i++) { // for 12-hour system
			$hours[$i] = $i;
			if ($i == 0) {
				$hours[$i] = 12;
			} else if ($i >= 13) {
				$hours[$i] = $i-12;
			}
		}
		return $hours;
	}
	
	function processForm() {
		// basic validation
		// required: title, leader1_firstname, leader1_lastname, leader1_phone, leader1_email
			// day, location, ages, interests
		$error = false;
		$content = '';
		if (
			!$this->piVars['title']
			|| !$this->piVars['leader1_firstname']
			|| !$this->piVars['leader1_lastname']
			|| !$this->piVars['leader1_phone']
			|| !$this->piVars['leader1_email']
			|| !$this->piVars['location']
			|| !$this->piVars['day']
			|| !$this->piVars['ages']
			|| !$this->piVars['interests']
			) {
			$error = true;
		}
		
		if ($error == true) {
			$content .= 'Required fields were submitted empty.  Please go back and be sure to fill them in.';
		} else {
			$content .= $this->updateLifegroup($this->piVars['id']);
		}
		
		return $content;
	}
	
	
	function updateLifegroup($id) {
		global $TYPO3_DB;
		$content = '';
		$updateArray = array();
		$addWhere = '';
		$currLabel = $this->conf['currLabel'];
		
		$updateArray['title'] = $this->piVars['title'];
		$updateArray['leader1_firstname'] = $this->piVars['leader1_firstname'];
		$updateArray['leader1_lastname'] = $this->piVars['leader1_lastname'];
		$updateArray['leader1_phone'] = $this->piVars['leader1_phone'];
		$updateArray['leader1_email'] = $this->piVars['leader1_email'];
		$updateArray['leader2_firstname'] = $this->piVars['leader2_firstname'];
		$updateArray['leader2_lastname'] = $this->piVars['leader2_lastname'];
		$updateArray['leader2_phone'] = $this->piVars['leader2_phone'];
		$updateArray['leader2_email'] = $this->piVars['leader2_email'];
		$updateArray['descr'] = $this->piVars['descr'];
		$updateArray['day'] = implode(',', $this->piVars['day']);
		$updateArray['time'] = $this->piVars['time'];
			if($this->piVars['ampm'] == 'pm') {
				$updateArray['time'] += 12 * 60 * 60; // add 12 hours of seconds: 12 hours x 60 minutes per hours x 60 seconds per minute
			}
		$updateArray['location'] = $this->piVars['location'];
		$updateArray['category'] = $this->piVars['category'];
		$updateArray['recurrence'] = $this->piVars['recurrence'];
		$updateArray['ages'] = implode(',', $this->piVars['ages']);
		$updateArray['interests'] = implode(',', $this->piVars['interests']);
		$updateArray['semesters'] = $this->piVars['semesters'];
		$updateArray['t3ver_label'] = $this->conf['currLabelUpdated'];
		
			// t3ver_oid is column that should contain the real uid of the lifegroup we want to update
			// t3ver_label is column that should contain a certain string for this round of updates.  Today, that is "Fall 2006"
		$addWhere = "t3ver_oid = '".$id."' AND t3ver_label = '".$currLabel."'";
		
		$res = $TYPO3_DB->exec_UPDATEquery(
				'tx_tfclifegroups_lifegroups',
				$addWhere.$this->cObj->enableFields('tx_tfclifegroups_lifegroups'),
				$updateArray
				);
		
		$affectedrows = $TYPO3_DB->sql_affected_rows($res);
		if ($affectedrows == 1) {
			$content .= 'Thank you for using this system to update your lifegroup information.<br/>';
			$content .= 'Your lifegroup information has been updated.';
			$this->emailConfirmation($this->piVars['leader1_email'], 
									$this->piVars['leader1_firstname'].' '.$this->piVars['leader1_lastname'], 
									$this->piVars['title'], 
									$id,
									$this->piVars['sectionTitle']);
		} else {
			$content .= 'There was a problem updating your lifegroup information.<br/>';
			$content .= 'Please give this number to the site administrator: '.$id.'<br/>';
		}
		
		return $content;
		
	}
	
	
	function emailConfirmation($email, $name, $lifegroupTitle, $groupUid, $sectionTitle='') {
		$content = '';
			//TODO get parent page name of lifegroup record
		$message = $name.' ('.$email.') has updated the lifegroup '.$lifegroupTitle.' in the section '.$sectionTitle.'.';
		$messageHTML = '<p>'.$name.' ('.$email.') has updated the lifegroup <strong>'.$lifegroupTitle.'</strong> in the section <strong>'.$sectionTitle.'</strong>.</p>';
		
		$oneMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$oneMail->useQuotedPrintable();
		$oneMail->setRecipient($this->conf['notificationEmailMain']);
		$oneMail->addPlain($message);
		$oneMail->theParts["html"]["content"] = $messageHTML;
		
		$oneMail->subject = 'lifegroup updated: '.$lifegroupTitle.'';
		$oneMail->from_email = $this->conf['emailFromEmail'];
		$oneMail->from_name =  $this->conf['emailFromName'];
		$oneMail->replyto_email = $this->conf['emailFromEmail'];
		$oneMail->replyto_name = $this->conf['emailFromName'];
		$oneMail->recipient_copy = $this->conf['notificationEmailCC'];
		$oneMail->organisation = 'Trinity Fellowship';
		$oneMail->messageid = md5(microtime()).'@tfchurch.org';
		
		$oneMail->setHeaders();
		$oneMail->setContent();
		$content .= $oneMail->send($this->conf['notificationEmailMain']);
		return $content;
	}
	
	function drawAdminLinks() {
		$content = '';
		$content = '<form method="post">';
		$content .= '<input type="hidden" name="'.$this->prefixId.'[emailEveryone]" value="true" />';
		$content .= '<input type="hidden" name="'.$this->prefixId.'[emailPassword]" value="yes_really-do=it" />';
		$content .= '<input type="submit" value="click here to email all lifegroup leaders">';
		$content .= '</form>';
		return $content;
	}
	
	function sendEmailToLeaders() {
		$content = '';
		$leaders = array();
		$counter = 0;
		$addWhere = 'pid IN ('. $this->pi_getPidList($this->conf['pids'], 4).')';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tx_tfclifegroups_lifegroups',
					$addWhere.$this->cObj->enableFields('tx_tfclifegroups_lifegroups')
					);
		$numrows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		for ($i=0; $i<$numrows; $i++) {
			$thisrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if (strlen($thisrow['leader1_email'])>3) {
				$leaders[$counter]['uid'] = $thisrow['uid'];
				$leaders[$counter]['title'] = $thisrow['title'];
				$leaders[$counter]['firstname'] = $thisrow['leader1_firstname'];
				$leaders[$counter]['lastname'] = $thisrow['leader1_lastname'];
				$leaders[$counter]['email'] = $thisrow['leader1_email'];
					
					// looks like I really didn't need to build that $leaders array
				$content .= $this->sendOneEmail($thisrow['leader1_email'],
												$thisrow['leader1_firstname'].' '.$thisrow['leader1_lastname'],
												$thisrow['title'],
												$thisrow['uid']);
				$counter ++;
			}
		}
		$counter = 0;
		
		//$content .= t3lib_div::view_array($leaders); // testing
		
		return $content; // finished version will say 'emails sent'
	}
	
	function sendOneEmail($email, $name, $lifegroupTitle, $groupUid) {
		$content = '';
		$message = 'Dear '.$name.' ('.$email.'):
Since you have led a lifegroup ('.$lifegroupTitle.') at Trinity in the past, we would like to give you the opportunity to either continue your current group, or begin a new group in the fall semester.  To make this process easier, we have implemented a web system that allows you to renew your leadership in a few simple steps.  Just follow the link at the bottom of this email, fill out the form, click Submit, and we will send you a letter to confirm that you will be leading a lifegroup with Trinity.  The deadline to register a lifegroup for the Fall 2006 semester is 00_00_0000, so please register soon.

Thanks and Blessings,
Lifegroup Team

link:
http://www.tfchurch.org/lifegroups/update/?tx_tfclifegroups_pi4[id]='.$groupUid.'
';
		$messageHTML = '<p>Dear '.$name.' ('.$email.'):</p>
<p>Since you have led a lifegroup ('.$lifegroupTitle.') at Trinity in the past, we would like to give you the opportunity to either continue your current group, or begin a new group in the spring semester. To make this process easier, we have implemented a web system that allows you to renew your leadership in a few simple steps. Just follow the link at the bottom of this email, fill out the form, click Submit, and we will send you an email to confirm that you will be leading a lifegroup with Trinity. The deadline to register a lifegroup for the Spring 2007 semester is '.$this->conf['lifegroupEmailDuedate'].', so please register soon. All entries received after the '.$this->conf['lifegroupEmailDuedate'].' deadline will not be included in the Sping Semester catalog. If you have any questions or concerns please contact Krystal Burns at the church offices.</p>

<p>Thanks and Blessings,<br/>
Lifegroup Team</p>

<p><strong><a href="http://www.tfchurch.org/lifegroups/update/?tx_tfclifegroups_pi4[id]='.$groupUid.'">
Click here to update your lifegroup information</a></strong></p>
<p>&nbsp;</p>
<p>If the link above does not work, copy and paste the following address into your browser:
http://www.tfchurch.org/lifegroups/update/?tx_tfclifegroups_pi4[id]='.$groupUid.'
</p>

';
		$oneMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$oneMail->useQuotedPrintable();
//		$oneMail->setRecipient('nadavoid@gmail.com'); // testing
		$oneMail->setRecipient($email); // live
		$oneMail->addPlain($message);
		$oneMail->theParts["html"]["content"] = $messageHTML;
		
		$oneMail->subject = $this->conf['emailSubject'];
		$oneMail->from_email = $this->conf['emailFromEmail'];
		$oneMail->from_name =  $this->conf['emailFromName'];
		$oneMail->replyto_email = $this->conf['emailFromEmail'];
		$oneMail->replyto_name = $this->conf['emailFromName'];
		$oneMail->recipient_copy = $this->conf['emailCC'];
		$oneMail->organisation = 'Trinity Fellowship';
		$oneMail->messageid = md5(microtime()).'@tfchurch.org';
		
		$oneMail->setHeaders();
		$oneMail->setContent();
//		$content .= $oneMail->send('nadavoid@gmail.com'); // testing
		$content .= $oneMail->send($email); // live
		return $content;
	}




} // end class



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tfc_lifegroups/pi4/class.tx_tfclifegroups_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/tfc_lifegroups/pi4/class.tx_tfclifegroups_pi4.php']);
}

?>