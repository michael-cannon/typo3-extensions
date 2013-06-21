<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 Michael Scharkow (mscharkow@gmx.net)
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
 * Plugin 'Survey' for the 'ms_survey' extension.
 *
 * @author		Michael Scharkow <mscharkow@gmx.net>
 * @author		Steffen Müller <steffen@mail.kommwiss.fu-berlin.de> (co-author)
 * @package		TYPO3
 * @subpackage		tx_mssurvey
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_mssurvey_pi1 extends tslib_pibase {
	var $prefixId = 'tx_mssurvey_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_mssurvey_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'ms_survey';	// The extension key.

	// MLC class survey items
	var $multitems				= array();
	var $survey_items			= array();
	var $submitted				= false;
	var $required				= false;
	var $hasError				= false;
	var $conf					= array();
	var $count				= 0;
		

	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $content: ...
	 * @param   [type]      $conf: ...
	 * @return   [type]      ...
	 */
	function main($content,$conf)	{
		$this->init( $conf );
		$conf					= $this->conf;

		if (strstr($this->cObj->currentRecord,'tt_content'))	{
			// MLC denote news id and it's storage point
			$conf['pidList']	= ( $conf[ 'pidList' ] )
									? $conf[ 'pidList' ]
									: $this->cObj->data['pages'];
			$conf['recursive'] = $this->cObj->data['recursive'];
		}
		
		if ($GLOBALS['TSFE']->loginUser)	{
			$this->userdata = $GLOBALS['TSFE']->fe_user->getKey('user','surveyData');
			$this->userdata['username'] = $GLOBALS['TSFE']->fe_user->user['username'];
		} 
		else {
			$this->userdata = $GLOBALS['TSFE']->fe_user->getKey('ses','surveyData');
			if (trim($this->piVars['survey_user'])){
				$this->userdata['username'] = $this->piVars['survey_user'];
			} else if ($conf['allow_anonymous']){
				$this->userdata['username'] = 'anonymous';
			} else {$this->userdata['username'] = '';}						
		}

		// this strips the absolute path from the ff_template value, maybe set upload_folder in flexform like in tt_news?
		if (strstr($conf['templateFile'],PATH_site)) {
			$this->templateCode = $this->cObj->fileResource(substr(strstr($conf['templateFile'],PATH_site),strlen(PATH_site)));
		} else {
			$this->templateCode = $this->cObj->fileResource($conf['templateFile']);
		}
	
		if  ( $this->userdata[$GLOBALS['TSFE']->id] && !$conf['ignoreAlreadyFilled']){
			return $this->pi_wrapInBaseClass($this->pi_getLL('alreadyFilled'));
		} elseif ($this->userdata['username']) {
			return $this->pi_wrapInBaseClass($this->display_survey($content,$conf));
		} else {
                        $tmpl = $this->cObj->getSubpart($this->templateCode,'USERFORM');
                        $markerarray = array();
			$markerarray['url'] = $this->url;
			$markerarray['submit'] = $this->pi_getLL('submit');
			$markerarray['usermsg'] = $this->pi_getLL('username');
			$out = $this->cObj->substituteMarkerArray($tmpl,$markerarray,'###|###',1);
			return $this->pi_wrapInBaseClass($out);
	        }
	}
	
	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $content: ...
	 * @param   [type]      $conf: ...
	 * @return   [type]      ...
	 */
	function display_survey($content, $conf) {
		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values
		$this->pi_USER_INT_obj = 1;	

		// MLC load helper
		$this->loadSurveyItems();
		
		// If there are not items, we return nothing
		if (!$this->survey_items)
			return;
		       
	 	$this->stage			= ( $this->piVars['stage'] )
									? $this->piVars['stage']
									: 1;
	 	$counter				= 1;
		$pastitems				= 0;
		
		// MLC initialize
		$outitems				= '';

        foreach ($this->survey_items as $item) {
            if ($counter == $this->stage ) {
                $outitems .= $this->process_var($item);
                if ($item['break'])
                    $counter++;
            } elseif ($counter > $this->stage) {
                $this->continue = 1;
                break;
            } elseif ($counter < $this->stage) {
                $pastitems++;
                $this->process_var($item);
                if ($item['break']) {
                    $counter++;
                }
            }
        }

        $this->stage++;
		
		// Storing relevant piVars
		$this->storevars($this->piVars);

		if ($conf['allow_anonymous'])
			unset($this->userdata['username']);
		
		if ($GLOBALS['TSFE']->loginUser) {
			$GLOBALS['TSFE']->fe_user->setKey('user','surveyData', $this->userdata);
		} else {
			$GLOBALS['TSFE']->fe_user->setKey('ses','surveyData', $this->userdata);
		}
		
		//Rendering the survey form
		if ($outitems && !$this->submitted)
		{
			$tmpl = $this->cObj->getSubpart($this->templateCode, 'SURVEYFORM');
			$markerarray = array();
			$markerarray['url'] = $this->url;
			$markerarray['submit'] = ($this->continue) ? $this->pi_getLL('continue') : $this->pi_getLL('submit');
			$markerarray['items'] = $outitems;
			$markerarray['totalitems'] = count($this->survey_items);
			$markerarray['doneitems'] = $pastitems;
			$markerarray['percent'] = intval($pastitems * 100 / count($this->survey_items));
			$markerarray['stage'] = $this->stage;
            $out =  $this->cObj->substituteMarkerArray($tmpl, $markerarray, '###|###', 1);

            // Don't display progress bar if disabled or no page breaks	
			if (!$this->conf['progressBar'] || !$counter) {
				$out =  $this->cObj->substituteSubpart($out,'PROGRESSBAR', '');
			}
			return $out;
		}

		if ($this->hasError)
		{
			$tmpl = $this->cObj->getSubpart($this->templateCode, 'SURVEYFORM');
			$markerarray = array();
			$markerarray['url'] = $this->url;
			$markerarray['submit'] = ($this->continue) ? $this->pi_getLL('continue') : $this->pi_getLL('submit');
			$markerarray['items'] = $outitems;
			$markerarray['totalitems'] = count($this->survey_items);
			$markerarray['doneitems'] = $pastitems;
			$markerarray['percent'] = intval($pastitems * 100 / count($this->survey_items));
			$markerarray['stage'] = $this->stage;
            $out =  $this->cObj->substituteMarkerArray($tmpl, $markerarray, '###|###', 1);

            // Don't display progress bar if disabled or no page breaks	
			if (!$this->conf['progressBar'] || !$counter) {
				$out =  $this->cObj->substituteSubpart($out, 'PROGRESSBAR', '');
			}
			return $out;
		}

        $this->saveResults();

        //if there are errors, return error message
        if ($GLOBALS['TYPO3_DB']->sql_error()) {
            return $this->pi_getLL('failedSaveData');
        }

        // Clean variables, mark survey as done and output success message
        $emailrecip = $this->userdata['storedvars']['email'];
        $results = $this->userdata['storedvars'];

        // MLC email results as well
        $emailBody = '';
        if (isset($this->conf['emailBody']))
            $emailBody = $this->conf['emailBody'] . "\n\n";

        if (array_key_exists('emailTemplate', $this->conf))
            $emailTemplate = $this->conf['emailTemplate'];

        if (strlen(trim($emailTemplate)) > 0) {
            $marker = array();
            foreach ($results as $question => $answer) {
                if (!is_array($answer)) {
                    $marker[$question] = $answer;
                    continue;
                }
                if (count($answer) == 1) {
                    $marker[$question] = $answer[0];
                    continue;
                }
                $marker[$question] = '';
                foreach ($answer as $subanswer)
                    $marker[$question] .= sprintf("- %s\n", $subanswer);
            }
            $emailBody .= $this->cObj->substituteMarkerArray($emailTemplate, $marker, '###|###');
        }
        else {
            // no email template, use generic
            foreach ($results as $key => $value)
            {
                if (!is_array($value)) {
                    $emailBody .= sprintf("%s\n------\n%s\n\n", $key, $value);
                    continue;
                }
                $answers = "";
                foreach ($value as $v)
                    $answers .= sprintf("- %s\n", $v);
                $emailBody .= sprintf("%s:\n%s\n", $key, $answers);
            }
        }

        unset($this->userdata['storedvars']);
        unset($this->userdata['username']);
        $this->userdata[$GLOBALS['TSFE']->id] = 1;
        if ($GLOBALS['TSFE']->loginUser)	{
            $GLOBALS['TSFE']->fe_user->setKey('user','surveyData', $this->userdata);
        } else	{
            $GLOBALS['TSFE']->fe_user->setKey('ses','surveyData', $this->userdata);
        }
        
        $out = $this->pi_getLL('successSaveData');


        // Send confirmation e-mail if set in constants
        // MLC send to admin
        if ($this->conf['emailNotification'] && t3lib_div::validEmail($this->conf['emailFrom']))
        {
            if ($this->sendMail($this->conf['emailSubject'], $emailBody, $this->conf['emailFrom'],
                                $this->conf['emailFrom'], $this->conf['emailName'],
                                $this->conf['emailAddHeader']))
            {
                $out .= '<br>'.$this->pi_getLL('successConfirmMail') . $emailrecip;
            } else {
                $out .= '<br>'.$this->pi_getLL('failedConfirmMail');
                $mailfailed = 1;
            }
        }

        if ($this->conf['successRedirectPID'] && !$mailfailed) {
            header ('Location: /'.$this->pi_getPageLink($this->conf['successRedirectPID']));
        }
        return $out;
	}

	/**
	 * Sends a notification email using quoted-printable encoding scheme.
	 * This is a substitution for tslib_content::sendNotifyEmail() and t3lib_div::plainMailEncoded
	 * It's possible to add a customized "Sender:" field.
	 *
	 * @param   string      $subject: Subject of the E-mail
	 * @param   string      $message: Bodytext of the E-mail
	 * @param   string      $emailrecip: Email address to send to
	 * @param   string      $emailFrom: Senders E-mail address
	 * @param   string      $emailName: Senders realname
	 * @param   boolean     $addHeader: If set, additional header is used
	 * @return  boolean     Returns true, if sending mail was successful
	 */
	function sendMail($subject, $message, $emailrecip, $emailFrom, $emailName, $addHeader='')	{
		$charset = 'ISO-8859-1';

/*		$mailbody = $this->cObj->fileResource($this->conf['emailTemplateFile']);
		$parts = split(chr(10),$mailbody,2);                // First line is subject

		$subject = trim($parts[0]);
		$subject = '=?'.$charset.'?Q?'.trim(t3lib_div::quoted_printable(ereg_replace('[[:space:]]','_',$subject),1000)).'?=';

		$message = trim($parts[1]);
		$message = t3lib_div::quoted_printable($message);
*/

		if ($addHeader)	{
			$headers = 'From: '.$emailName.' <'.$emailFrom.">\r\nSender: ".$emailName.' <'.$emailFrom.">\r\n";
			$additionalParams = '-f'.$emailFrom;
		} else	{
			$headers = 'From: '.$emailName.' <'.$emailFrom.">\r\n";
		}

		$headers = trim($headers).chr(10).
			'Mime-Version: 1.0'.chr(10).
			'Content-Type: text/plain; charset="'.$charset.'"'.chr(10).
			'Content-Transfer-Encoding: quoted-printable';
		$headers = trim(implode(chr(10),t3lib_div::trimExplode(chr(10),$headers,1)));

		return (mail($emailrecip,$subject,$message,$headers,$additionalParams));
	}

	function merge_config($conf){
		
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'allow_anonymous', 'sDEF')){
		
			$conf['allow_anonymous'] = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'],'allow_anonymous', 'sDEF'))-1;
		}

		// MLC when adding fields to tt_content Flex Form, add them here as well
		$stdconf = array('registrantPid','surveyrequired','templateFile','successRedirectPID','progressBar');
		
		foreach ($stdconf as $value){
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],$value, 'sDEF')){
				 $conf[$value] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],$value, 'sDEF');
			}
		}
		
		$mailconf = array('emailName', 'emailFrom', 'emailSubject', 'emailBody', 'emailNotification',
                          'emailTemplate');
		
		foreach ($mailconf as $value){
			if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'],$value, 's_email')){
				 $conf[$value] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],$value, 's_email');
			}
		}

//		$conf['successRedirectPID'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'successRedirectPID', 'sDEF');
		return $conf;
	}


	/**
	 * Create string of survey item from template file and row data.
	 *
	 * @param   [type]      $row: ...
	 * @param   [type]      $name: ...
	 * @return   [type]      ...
	 */
	function process_var(& $row,$name='')
	{
		$row['title']			= ( $name )
									? trim( $name ) . '_'
										. trim( $row['title'] )
									: trim( $row['title'] );

		// MLC try to copy value from form
		$row[ 'value' ]			= ( isset( $this->piVars[ $row[ 'title' ] ] ) )
									? $this->piVars[ $row[ 'title' ] ]
									: '';

		$row[ 'error' ]			= '';

		// MLC submitted and required, show and recall error
		if ( $this->submitted && $this->required && ! $row[ 'optional' ]
			&& ( ! trim( $row[ 'value' ] )
				|| $row[ 'value' ] == $this->pi_getLL( 'pleaseChooseOne' )
			)
		)
		{
			$row[ 'error' ]		= $this->pi_getLL( 'requiredItem' );
			$row[ 'question' ]	= $row[ 'error' ] 
									. ' ' 
									. $row[ 'question' ];
			$this->hasError		= true;
		}

		switch( $row['type'] )
		{
			// string
			case 0:
				$this->variables[] = $row['title'];
				$out .= ($name)?$this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateCode,'SHORTSTRINGITEM'),$row,'###|###',1):$this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateCode,'STRINGITEM'),$row,'###|###',1);
				break;
			// text
			case 1:
				$this->variables[] = $row['title'];
				$out .= ($name)?$this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateCode,'SHORTTEXTITEM'),$row,'###|###',1):$this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateCode,'TEXTITEM'),$row,'###|###',1);
				break;
			// radiobutton
			case 2:
				$this->variables[] = $row['title'];
				$out = ($name)?$this->itemoutput($row,'RADIO','SHORT'):$this->itemoutput($row,'RADIO');
				break;
			// checkbox
			case 3:
				if ( $row['itemvalues'] )
				{
					$vars		= explode("\n",$row['itemvalues']);
					$vars		= array_map('trim', $vars);
					foreach ($vars as $item)
					{
						$this->variables[] = trim( trim( $row['title']
													. '_'
													. $item
												)
												, '@'
											);
					}
					$out		= ($name)
									? $this->itemoutput($row,'CHECKBOX','SHORT')
									: $this->itemoutput($row,'CHECKBOX');	
				}
					
				break;
			// simple select
			case 4:
				$this->variables[] = $row['title'];
				$row['multi'] = '';
				$row['arr'] = '';
				$row['height'] = '1';

				// MLC add Please Choose One text
				$row[ 'itemvalues' ]	= $this->pi_getLL( 'pleaseChooseOne' )
											. "\n"
											. $row[ 'itemvalues' ];
				
				$out = ($name)?$this->itemoutput($row,'SELECT','SHORT'):$this->itemoutput($row,'SELECT');
				break;
			// multiple select
			case 5:
				if ($row['itemvalues']){
					$row['multi'] ='multiple="multiple"'; 
					$row['arr'] = '[]';
					$row['height'] = '3';
					$vars = explode("\n",$row['itemvalues']);
	                                $vars = array_map('trim', $vars);
					foreach ($vars as $item){
		                                $this->variables[] = trim($row['title'].'_'.$item);
		                        }
					$out = ($name)?$this->itemoutput($row,'SELECT','SHORT'):$this->itemoutput($row,'SELECT');
		                }
				break; 
			// two-dimensional
			case 6:
				if ($row['itemrows']){
					$out = $row['description'].'<br>';
					$out .='<table border="1">';	
					$itemrows = explode("\n",$row['itemrows']);
					$itemrows = array_map('trim', $itemrows);
					$items = array_map('trim',explode(',',$row['items']));
					
					if ($row['exclude']){
						$userthere = array_search(strtolower($this->userdata['username']),array_map('strtolower', $itemrows));
						
						if ($userthere){
							unset($itemrows[$userthere]);
						} elseif ($userthere === 0){
							array_shift($itemrows);
						}  
					}

					foreach ( $items as $item){
							$marker['columns'] .= '<th>'.$this->multitems[$item]['question'].'</th>';

					}
					foreach ($itemrows as $itemrow){
						//$out .= $itemrow;
						$marker['rows'] .= '<tr><td>'.$itemrow.'</td>';
						foreach ( $items as $item){
							$marker['rows'] .= '<td>'.$this->process_var($this->multitems[$item],$itemrow).'</td>';
						}	
						$marker['rows'] .= '</tr>';
					}
					$out .= '</table>';
					$marker['description'] = $row['description'];
					$out = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateCode,'MULTITEM'),$marker,'###|###',1);
				}
				break; 
		}
		return $out;
		
	}

	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $row: ...
	 * @param   [type]      $subpart: ...
	 * @param   [type]      $short: ...
	 * @return   [type]      ...
	 */
	function itemoutput($row,$subpart,$short='')
	{
		// MLC check user input
		$userValue				= ( isset( $this->piVars[ $row[ 'title' ] ] ) )
									? $this->piVars[ $row[ 'title' ] ]
									: '';

		$values = explode("\n",$row['itemvalues']);
		$values = array_map('trim', $values);
		
		foreach ($values as $idx => $value)
		{
			$row['idx']			= $idx+1;
			$cleanval			= trim( $value );
			$cleanvalatless		= trim( $cleanval, '@' );
			$checked			= false;

			// MLC capture users input
			if ( $userValue )
			{
				// MLC check boxes are arrays
				if ( is_array( $userValue ) )
				{
					if ( in_array( $cleanvalatless, $userValue ) )
					{
						$checked	= true;
					}
				}

				elseif ( $userValue == $cleanvalatless )
				{
					$checked	= true;
				}
			}

			// MLC denote default
			elseif ( $cleanval != $cleanvalatless )
			{
				$checked		= true;
			}

			// MLC one point for checked
			if ( $checked )
			{
				$row['checked']		= 'checked';
				$row['selected']	= 'selected';
			}
				
			else
			{ 
				$row['checked']		= 0;
				$row['selected']	= 0 ;
			}
				
			$row['value']		= $cleanvalatless;

			$row['values']		.= $this->cObj->substituteMarkerArray(
									$this->cObj->getSubpart(
										$this->templateCode
										, $subpart . 'VALUE'
									)
									, $row
									, '###|###'
									, 1
								);
		 }
		
		return $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->templateCode,$short.$subpart.'ITEM'),$row,'###|###',1);
	}	

	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $array: ...
	 * @return   [type]      ...
	 */
	function makecsv($array){
		$array					= ( is_array( $array ) )
									? $array
									: array();

		$pco					= $this->pi_getLL( 'pleaseChooseOne' );

		foreach ($array as $variable=>$value){
			if (in_array($variable,$this->variables)){
				// MLC remove pco from value
				$value	= preg_replace( "#$pco#", '', $value );
				$out .= '"'.$variable.'":"'.addslashes($value).'"';
				$out .=',';
				}
			}
		$replace = array("\n","\r","\r\n");
		$out = str_replace($replace,' ',$out);
		$out = rtrim($out,',');
		return $out;
	}	
	
	/**
	 * [Put your description here]
	 *
	 * @param   [type]      $array: ...
	 * @return   [type]      ...
	 */
	function storevars($array){
		foreach ($array as $variable => $value)
		{
			 $this->userdata['storedvars'][trim($variable)] = $value;
		}

		return false;
	}

	/**
	 * Convert arrayed variables into indices.
	 *
	 * @param   [type]      $array: ...
	 * @return   [type]      ...
	 */
	function cleanvars($array){
		$array					= ( is_array( $array ) )
									? $array
									: array();

		foreach ($array as $varname => $var){
			if (is_array($var)){
				foreach ($var as $name){
					$array[$varname.'_'.$name] = 1;
				}
				unset($array[$varname]);
			}
		}
		return $array;
	}

	/**
	 * Generate string of items to be displayed as survey questions for a news
	 * item.
	 *
	 * @param array configuration array
	 * @return string
	 */
	function newsSurveyDisplay ( $conf )
	{
		$this->init( $conf );
		$this->loadSurveyItems();
		$string					= '';

		if ( ! $this->survey_items )
		{
			return $this->pi_getLL( 'noSurveyItems' );
		}

		foreach ( $this->survey_items as $item )
		{
			// build template and denote errors
			$string				.= $this->process_var( $item );
		}

		return $string;
	}
	
	/**
	 * Load survey items into class variables.
	 *
	 * @return void
	 */
	function loadSurveyItems ()
	{
		// Local settings for the listView function
		$lConf					= $this->conf['listView.'];

		// Initializing the query parameters:
		// Number of results to show in a listing.
		$this->internal['results_at_a_time']	= 1000;

		// The maximum number of "pages" 
		$this->internal['maxPages']				= t3lib_div::intInRange(
								$lConf['maxPages']
								, 0
								, 1000
								, 2
							);

		$this->internal['orderByList']			= 'sorting,uid';
		$this->internal['orderBy']				= 'sorting';

		// MLC add news uid requirement when questions are driven by news event
		$query					= false;
		$orderBy				= '';

		if ( $this->conf[ 'newsUid' ] )
		{
			$query				= "
				FROM tx_mssurvey_items
					, tt_news
					, tt_news_tx_mssurvey_items_mm
				WHERE
					tx_mssurvey_items.uid = tt_news_tx_mssurvey_items_mm.uid_foreign
					AND tt_news.uid = tt_news_tx_mssurvey_items_mm.uid_local
					AND tt_news.uid IN ( {$this->conf[ 'newsUid' ]} )
					AND tx_mssurvey_items.pid IN ( {$this->conf[ 'pidList' ]} )
			";

			$query				.= $this->cObj->enableFields(
									'tx_mssurvey_items'
								);

			$orderBy			= '
				ORDER BY tt_news_tx_mssurvey_items_mm.sorting
			';
		}

		// Doing the query
		$res					= $this->pi_exec_query( 'tx_mssurvey_items'
										, false		// count
										, ''		// addWhere
										, ''		// mm_cat
										, ''		// group
										, $orderBy	// order
										, $query	// own from
									);
		
		// iterate over all items
		while ( $res && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) )
		{
			if ($row['multitem'])
			{
				$this->multitems[$row['uid']] = $row;
			}
			
			else
			{
				$this->survey_items[] = $row;
			}
		}
	}

	/**
	 * Class initializer helper
	 *
	 * @param configuration array
	 */
	function init( $conf )
	{
		$GLOBALS['TSFE']->set_no_cache();
		$this->pi_initPIflexForm();
		$conf					= $this->merge_config( $conf );
		$this->conf				= $conf;
		$this->pi_USER_INT_obj	= 1;	
		$this->pi_loadLL();
		$this->url				= $this->pi_getPageLink( $GLOBALS['TSFE']->id
									, $GLOBALS["TSFE"]->sPre
								);

		$this->templateCode		= $this->cObj->fileResource(
									$this->conf['templateFile']
								);

		$this->submitted		= ( isset( $this->piVars[ 'submitted' ] )
									|| $this->conf[ 'submitted' ]
								)
									? true
									: false;

		$this->required			= $this->conf[ 'surveyrequired' ];
	}

	/**
	 * Save results to database.
	 *
	 * @return void
	 */
	function saveResults()
	{
		// Storing relevant piVars
		$this->storevars( $this->piVars );

		// MLC save pid with event registrants
		$pid					= ( $this->conf[ 'registrantPid' ] )
									? $this->conf[ 'registrantPid' ]
									: $GLOBALS['TSFE']->id;

		//save stuff
		array( $dbdata );
		$dbdata['pid']			= $pid;
		$dbdata['surveyid']		= ( isset( $this->conf[ 'newsUid' ] ) )
									? $this->conf[ 'newsUid' ]
									: $pid;
		$dbdata['results']		= $this->makecsv( $this->cleanvars(
									$this->userdata['storedvars']
								) );

		$fe_cruser_id			= ( isset(
									$GLOBALS['TSFE']->fe_user->user['uid'] )
								)
									? $GLOBALS['TSFE']->fe_user->user['uid']
									: 0;

		// MLC prevent double entry
		$query					= "
			SELECT uid
			FROM tx_mssurvey_results
			WHERE 1 = 1
				AND pid = {$dbdata['pid']}
				AND fe_cruser_id = {$fe_cruser_id}
				AND surveyid = {$dbdata['surveyid']}
		";

		$query					.= $this->cObj->enableFields(
									'tx_mssurvey_results'
								);

		$result					= $GLOBALS[ 'TYPO3_DB' ]->sql( TYPO3_db
									, $query
								);

		$data					= $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc(
									$result
								);

		$dbdata['remoteaddress']	= ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
									? $_SERVER[ 'REMOTE_ADDR' ]
									: 0;

		// MLC no results then insert
		if ( $this->conf[ 'ignoreAlreadyFilled'] || ( $result && ! $data ) )
		{
			$res				= $this->cObj->DBgetInsert(
									'tx_mssurvey_results'
									, $pid
									, $dbdata
									, 'surveyid,results,remoteaddress'
									, true
								);
		}

		// MLC has results, then update
		else
		{
			$res				= $this->cObj->DBgetUpdate(
									'tx_mssurvey_results'
									, $data[ 'uid' ]
									, $dbdata
									, 'results'
									, true
								);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ms_survey/pi1/class.tx_mssurvey_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ms_survey/pi1/class.tx_mssurvey_pi1.php']);
}

?>
