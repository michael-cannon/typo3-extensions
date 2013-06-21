<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Your Name (your@email.com)
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
 * Plugin 'Job Bank Resume' for the 'job_bank_resume_mgr' extension.
 *
 * @author    Your Name<your@email.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");
require_once(PATH_t3lib."class.t3lib_extmgm.php");
require_once(t3lib_extMgm::extPath('job_bank_resume_mgr')."pi1/class.phpmailer.php");

class tx_jobbankresumemgr_pi1 extends tslib_pibase {
    var $prefixId        = "tx_jobbankresumemgr_pi1";
    var $scriptRelPath   = "pi1/class.tx_jobbankresumemgr_pi1.php";
    var $extKey          = "job_bank_resume_mgr";
    var $theTable        = 'tx_jobbankresumemgr_info';
    var $theSponsorTable = 'tx_t3consultancies';
    var $theFeTable      = 'fe_users';
    var $theJobBankTable = 'tx_jobbank_list';
    var $TCA             = array();
    var $uploadPath      = 'uploads/tx_jobbankresumemgr';
    var $imagePath       = 'images/';
    var $fileFunc        = ''; // Set to a basic_filefunc object for file uploads
    var $userData        = '';
    var $site_name       = 'http://www.bpminstitute.org/';
    
    var $tableNameCountry = "static_countries";
    var $tableNameCountryZone = "static_country_zones";
    
    function main($content,$conf)
    {
        $this->conf = $conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        
        global $TSFE;
        $this->userData = $TSFE->fe_user->user;        
        $typolink_conf = array(
            "parameter" => $TSFE->id
        );
        
        $this->templateCode = $this->cObj->fileResource($this->conf["templateFile"]);

        $action = t3lib_div::_GP('action');
        $content = '';
        
        switch($action)
        {
            case 'preview_mail_form': 
                $this->deleteFileNews($this->piVars[job_id], $this->piVars[user_id], $this->piVars[delFileName]);
                $content .= $this->insertUserInfoForJob();
                break;
            case 'sendMail': 
                $content .= $this->sendMail();
                break;
            default:
                $content .= $this->getFormToApplyForJob();
        }
        
        return $this->pi_wrapInBaseClass($content);
    }
    
    function getFormToApplyForJob()
    {
            $template = $this->cObj->getSubpart($this->templateCode, "###JOBBANKRESUME###");

            $_arrJobBank = $this->getJobBankDetail('uid = '.t3lib_div::_POST('job_id'));
            $sponsor_id  = $this->getSponsorByIdFromJob('sponsor_id', 'uid = '.t3lib_div::_POST('job_id'));
            $sponsorData = $this->getSponsorById('uid = '.$sponsor_id['sponsor_id']);
            
            global $TSFE;

            $subPartArray['###DYANAMIC_JS###']         = $this->getCountryZone(1);
            $subPartArray['###FIELD_address###']       = $this->userData['address'];
            $subPartArray['###FIELD_city###']          = $this->userData['city'];
            $subPartArray['###FIELD_company###']       = $this->userData['company'];
            $subPartArray['###FIELD_country###']       = $this->getCountryZone();
            $subPartArray['###FIELD_email###']         = $this->userData['email'];
            $subPartArray['###FIELD_fax###']           = $this->userData['fax'];
            $subPartArray['###FIELD_first_name###']    = $this->userData['first_name'];
            $subPartArray['###FIELD_last_name###']     = $this->userData['last_name'];
            $subPartArray['###FIELD_telephone###']     = $this->userData['telephone'];
            $subPartArray['###FIELD_title###']         = $this->userData['title'];
            $subPartArray['###FIELD_www###']           = $this->userData['www'];
            $subPartArray['###FIELD_zip###']           = $this->userData['zip'];
            $subPartArray['###FORMACTION###']          = $this->pi_getPageLink($TSFE->id);
            $subPartArray['###FORMNAMEEXTENSIONJS###'] = $this->prefixId;
            $subPartArray['###JOBID###']               = t3lib_div::_POST('job_id');
            $subPartArray['###JOBNAME###']             = $_arrJobBank['occupation'];
            $subPartArray['###SELECTED_COUNTRY###']    = $this->userData['static_info_country'];
            $subPartArray['###SELECTED_STATE###']      = $this->getZoneByCode($this->userData['zone'], $this->userData['static_info_country']);
            $subPartArray['###SPONSORID###']           = t3lib_div::_POST('sponsor_id');
            $subPartArray['###SPONSORNAME###']         = $sponsorData['title'];
            
            $content = $this->cObj->substituteMarkerArrayCached($template, $subPartArray, array(), array());
            
            return $content;
    }
        
    
    function insertUserInfoForJob()
    {
        global $TSFE;
        if(is_array(t3lib_div::_POST()))
        {
            $session_var = t3lib_div::_POST();
            foreach($session_var as $key => $value)
                $TSFE->fe_user->setKey("ses", $key, $value);
        }

        $_subPartArray = array();
        $_subPartArray['###FORMACTION###'] = $this->pi_getPageLink($GLOBALS["TSFE"]->id);
        $_subPartArray['###JOBID###']      = t3lib_div::_POST('job_id');
        $_subPartArray['###SPONSORID###']  = t3lib_div::_POST('sponsor_id');

        $filename = $_FILES['resume']['name'];
        if (!empty($filename))
        {
            $uploadedFileName = $_FILES['resume']['tmp_name'];

            $tmpFileName = t3lib_div::upload_to_tempfile($uploadedFileName);

            $fI = pathinfo($filename);
            $newFileName = '';
            if ($TSFE->loginUser)
                $newFileName = $TSFE->fe_user->user['username'] . '_';
            $newFileName .= basename($filename, '.' . $fI['extension']);
            $newFileName .= sprintf("_%s.%s", t3lib_div::shortmd5(uniqid($filename)), $fI['extension']);

            $theDestFile = PATH_site . $this->uploadPath.'/'.$newFileName;

            if (t3lib_div::upload_copy_move($tmpFileName, $theDestFile))
                $content = "Cannot Upload !<br/>Source File: $tmpFileName<br/>Destination File: $theDestFile";
        }

        $sponsor_id = $this->getSponsorByIdFromJob('sponsor_id', 'uid='.t3lib_div::_POST('job_id'));
        $sponsorData = $this->getSponsorById('uid='.$sponsor_id['sponsor_id']);

        $user_data_from_resumemanager_table = $this->getDataFromResumeTable(t3lib_div::_POST('job_id'), $resume_manager_user_job_id);

        //Get the Prievew page contents containing mail data
        $_arrJobBank = $this->getJobBankDetail('uid='.t3lib_div::_POST('job_id'));

        $job_title = $_arrJobBank['occupation'];
        $job_id = t3lib_div::_POST('job_id');

        //Set the session var for job title
        $TSFE->fe_user->setKey('ses','job_title', t3lib_div::_GP('job_title'));
        $TSFE->fe_user->setKey('ses','job_id', t3lib_div::_GP('job_id'));

        $_subPartArray['###ADDRESS###']                    = $TSFE->fe_user->getKey('ses', 'address');
        $_subPartArray['###ATTACHED_FILE_LINK###']         = " ";
        $_subPartArray['###CITY###']                       = $TSFE->fe_user->getKey('ses', 'city');
        $_subPartArray['###COMMENTS###']                   = $TSFE->fe_user->getKey('ses', 'job_bank_comments');
        $_subPartArray['###COUNTRY###']                    = $TSFE->fe_user->getKey('ses', 'zone_location');
        $_subPartArray['###DESTINATION_FILE###']           = $theDestFile;
        $_subPartArray['###EMAIL###']                      = $TSFE->fe_user->getKey('ses', 'email');
        $_subPartArray['###FAX###']                        = $TSFE->fe_user->getKey('ses', 'fax');
        $_subPartArray['###FILE_NAME###']                  = $filename;
        $_subPartArray['###FIRST_NAME###']                 = $TSFE->fe_user->getKey('ses', 'fname');
        $_subPartArray['###JOB_ID###']                     = $job_id;
        $_subPartArray['###JOB_TITLE###']                  = $job_title;
        $_subPartArray['###LAST_NAME###']                  = $TSFE->fe_user->getKey('ses', 'lastname');
        $_subPartArray['###ORGANISATION###']               = $TSFE->fe_user->getKey('ses', 'organisation');
        $_subPartArray['###PHONE###']                      = $TSFE->fe_user->getKey('ses', 'phone');
        $_subPartArray['###RESUME_MANAGER_USER_JOB_ID###'] = $resume_manager_user_job_id;
        $_subPartArray['###SITE_NAME###']                  = trim($this->conf['siteName']);
        $_subPartArray['###SPONSORNAME###']                = $TSFE->fe_user->getKey('ses', 'sponsor_name');
        $_subPartArray['###STATE###']                      = $this->mapZoneId($TSFE->fe_user->getKey('ses', 'location'));
        $_subPartArray['###TITLE###']                      = $TSFE->fe_user->getKey('ses', 'title');
        $_subPartArray['###WEBSITE###']                    = $TSFE->fe_user->getKey('ses', 'website');
        $_subPartArray['###ZIP###']                        = $TSFE->fe_user->getKey('ses', 'zip');

        if ($this->piVars["delFileName"])
            $_subPartArray['###GOBACK###'] = "javascript:history.go(-2);";
        else
            $_subPartArray['###GOBACK###'] = "javascript:history.back(-1);";

        $_additionalParams = "&$this->prefixId[job_id]=".t3lib_div::_POST('job_id')
            ."&action=preview_mail_form"
            ."&$this->prefixId[user_id]=" . $TSFE->fe_user->user['uid'];

        $typoLinkConfig = array(
            'parameter' => $TSFE->id,
        );

        //Attached file link if any
        if (!empty($filename))
        {
            $typoLinkConfig['additionalParams'] = $_additionalParams
                ."&$this->prefixId[delFileName]=$theDestFile"
                ."&job_title=$job_title"
                ."&job_id=$job_id";

            $theNewDestFilePath = sprintf("%s/%s/%s", $this->getCurrentSite(), $this->uploadPath, $newFileName);

            $_subPartArray['###ATTACHED_FILE_LINK###'] = sprintf('<a href="%s" target="_blank">%s</a> &nbsp; %s', 
                    $theNewDestFilePath, $filename, $this->cObj->typoLink('Delete', $typoLinkConfig));
            unlink($tmpFileName);
        }
        else {
        }

        $TSFE->fe_user->setKey("ses", 'resume_file', $newFileName);
        $TSFE->fe_user->setKey("ses", 'resume_file_name', $filename);

        $template = $this->cObj->getSubpart($this->templateCode, "###RESUMEPREVIEW###");
        $content .= $this->cObj->substituteMarkerArrayCached($template, $_subPartArray, array(), array());

        return $content;

    }
    
    function sendMail()
    {
        global $TSFE;
        $fieldArray = array(
            'user_id'           => $TSFE->fe_user->user['uid'],
            'pid'               => $this->getData('storagePID'),
            'crdate'            => time(),
            'tstamp'            => time(),
            'job_id'            => $TSFE->fe_user->getKey('ses', 'job_id'),
            'resume_file'       => $TSFE->fe_user->getKey('ses', 'resume_file'),
            'resume_file_name'  => $TSFE->fe_user->getKey('ses', 'resume_file_name'),
            'job_bank_comments' => $TSFE->fe_user->getKey('ses', 'job_bank_comments')
        );

        global $TYPO3_DB;
        $TYPO3_DB->exec_INSERTquery($this->theTable, $fieldArray);

        $resFiles = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'resume_file',
            // FROM
            $this->theTable,
            // WHERE
            'user_id='.$TSFE->fe_user->user['uid']
            . ' ' . $this->cObj->enableFields($this->theTable));

        if ($resFiles)
        {
            $_fileName = '';
            while ($row = $TYPO3_DB->sql_fetch_assoc($resFiles))
                $_fileName .= $row['resume_file'].',';

            $_fileName = substr($_fileName, 0, strlen($_fileName) - 1);

            $_arrFilename = array(
                "tx_jobbankresumemgr_resume_file" => $_fileName,
                'first_name' => $TSFE->fe_user->getKey('ses', 'fname'),
                'last_name'  => $TSFE->fe_user->getKey('ses', 'lastname'),
                'company'    => $TSFE->fe_user->getKey('ses', 'organisation'),
                'title'      => $TSFE->fe_user->getKey('ses', 'title'),
                'address'    => $TSFE->fe_user->getKey('ses', 'address'),
                'city'       => $TSFE->fe_user->getKey('ses', 'city'),
                'zone'       => $TSFE->fe_user->getKey('ses', 'location'),
                'country'    => $TSFE->fe_user->getKey('ses', 'zone_location'),
                'zip'        => $TSFE->fe_user->getKey('ses', 'zip'),
                'telephone'  => $TSFE->fe_user->getKey('ses', 'phone'),
                'fax'        => $TSFE->fe_user->getKey('ses', 'fax'),
                'email'      => $TSFE->fe_user->getKey('ses', 'email'),
                'www'        => $TSFE->fe_user->getKey('ses', 'website')
            );
        }

        $jobSearchPageLinkId = $this->conf['jobBankPageId'];

        $destination_file = t3lib_div::_POST('destination_file');
        $fileNamePath = t3lib_div::_POST('file_name');
        $site_name = $this->getCurrentSite()."/";

        /***************************************************************************
         *       Mail Template
         ***************************************************************************/


        $_subPartArray = array();
        $_subPartArray['###JOB_TITLE###']    = t3lib_div::_POST('job_title');
        $_subPartArray['###FILE_PATH###']    = $site_name.t3lib_extMgm::siteRelPath('job_bank_resume_mgr');
        $_subPartArray['###FIRST_NAME###']   = $TSFE->fe_user->getKey('ses','fname');
        $_subPartArray['###LAST_NAME###']    = $TSFE->fe_user->getKey('ses','lastname');
        $_subPartArray['###ORGANISATION###'] = $TSFE->fe_user->getKey('ses','organisation');
        $_subPartArray['###TITLE###']        = $TSFE->fe_user->getKey('ses','title');
        $_subPartArray['###ADDRESS###']      = $TSFE->fe_user->getKey('ses','address');
        $_subPartArray['###COUNTRY###']      = $TSFE->fe_user->getKey('ses','zone_location');
        $_subPartArray['###STATE###']        = $this->mapZoneId($GLOBALS["TSFE"]->fe_user->getKey('ses','location'));
        $_subPartArray['###CITY###']         = $TSFE->fe_user->getKey('ses','city');
        $_subPartArray['###ZIP###']          = $TSFE->fe_user->getKey('ses','zip');
        $_subPartArray['###PHONE###']        = $TSFE->fe_user->getKey('ses','phone');
        $_subPartArray['###FAX###']          = $TSFE->fe_user->getKey('ses','fax');
        $_subPartArray['###WEBSITE###']      = $TSFE->fe_user->getKey('ses','website');
        $_subPartArray['###EMAIL###']        = $TSFE->fe_user->getKey('ses','email');
        $_subPartArray['###SITE_NAME###']    = trim($this->conf['siteName']);
        $_subPartArray['###SPONSORNAME###']  = t3lib_div::_POST('sponsor_name');

        $urlParameters = array();
        $urlParameters['action'] = 'showcompanyDetails';

        if (t3lib_div::_POST('job_id')!='')
            $urlParameters['job_id'] = t3lib_div::_POST('job_id');
        else
            $urlParameters['job_id'] = $TSFE->fe_user->getKey('ses','job_id');

        // Link to job page
        if (t3lib_div::_POST('job_title') != '')
            $jobtitle = t3lib_div::_POST('job_title');
        else
            $jobtitle = $TSFE->fe_user->getKey('ses','job_title');
        
        $_subPartArray['###JOB_TITLE_PAGE_LINK###'] = '<a href = '.$this->pi_getPageLink($jobSearchPageLinkId, null, $urlParameters).">Back to $jobtitle</a>";
        $_subPartArray['###JOB_LISTING_PAGE_LINK###'] = '<a href="'.$this->pi_getPageLink($jobSearchPageLinkId).'">Back to Job Listings</a>';
        $user_data_from_resumemanager_table  = $this->getDataFromResumeTable(t3lib_div::_POST('job_id'),t3lib_div::_POST('resume_manager_user_job_id'));
        $_subPartArray['###COMMENTS###']= $TSFE->fe_user->getKey('ses', 'job_bank_comments');

        if (!$_subPartArray['###COMMENTS###'])
            $_subPartArray['###COMMENTS###'] = 'None';

        // Mail Body Text
        $template = $this->cObj->getSubpart($this->templateCode, "###MAILFORMAT###");
        $msg = $this->cObj->substituteMarkerArrayCached($template, $_subPartArray, array(), array());

        // Mail Subject
        $subject = trim($this->conf["email_subject"]);

        $sponsor_id=$this->getSponsorByIdFromJob('sponsor_id','uid='.t3lib_div::_POST('job_id'));
        $sponsorData = $this->getSponsorById('uid='.$sponsor_id['sponsor_id']);

        $contact_email = ('' != $sponsorData['tx_jobbankresumemgr_resumecontactemail'])
            ? $sponsorData['tx_jobbankresumemgr_resumecontactemail']
            : $sponsorData['contact_email'];

        $contact_name = ('' != $sponsorData['tx_jobbankresumemgr_resumecontactname'])
            ? $sponsorData['tx_jobbankresumemgr_resumecontactname']
            : $sponsorData['contact_name'];

        // MLC send sponsor copy
        $this->SendEmailNotification($contact_email
                , $contact_name
                , $applicant_email
                , $applicant_name
                , $subject
                , $msg
                , $destination_file
                , $fileNamePath
                );

        /*
         * Retrieve the data admin name, admin mail, emaol subject from the TS File
         * 
         */

        $to = trim($this->conf["email_admin"]);
        $to_name = trim($this->conf["name_admin"]);
        $applicant_email = $this->userData['email'];
        $applicant_name = $this->userData['username'];

        if ($this->SendEmailNotification($to,$to_name,$applicant_email,$applicant_name,$subject,$msg,$destination_file,$fileNamePath))
        {
            $template = $this->cObj->getSubpart($this->templateCode, "###MAIL_SENT_STATUS###");
            $content = $this->cObj->substituteMarkerArrayCached($template, array(), $_subPartArray, array());
        }
        else {
            $template = $this->cObj->getSubpart($this->templateCode, "###MAIL_NOTSENT_STATUS###");
            $content = $this->cObj->substituteMarkerArrayCached($template, array(), array(), array());
        }

        return $content;
    }
    
    function getJobTitle($job_uid)
    {
        if (!is_numeric($job_uid))
            return 0;

        global $TYPO3_DB;
        $res = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            "*",
            // FROM
            'tx_jobbank_list',
            // WHERE
            "uid = $job_id");

        $row = $TYPO3_DB->sql_fetch_assoc($res);
        return $row['occupation'];
    }
    
    
    function resumeUpload($fileObj)
    {
        $filename = $fileObj['resume']['name'];
        $uploadedFileName = $fileObj['resume']['tmp_name'];
        $tmpFileName = t3lib_div::upload_to_tempfile($uploadedFileName);
        $fI = pathinfo($filename);
        global $TSFE;
        $newFileName = (($TSFE->loginUser)?($TSFE->fe_user->user['username'].'_'):'').basename($filename, '.'.$fI['extension']).'_'.t3lib_div::shortmd5(uniqid($filename)).'.'.$fI['extension'];
        $theDestFile = PATH_site.$this->uploadPath.'/'.$newFileName;

        $fieldArray = array(
            'user_id'          => $TSFE->fe_user->user['uid'],
            'pid'              => $this->getData('storagePID'),
            'job_id'           => t3lib_div::_POST('job_id'),
            'resume_file'      => $newFileName,
            'crdate'           => time(),
            'tstamp'           => time(),
            'resume_file_name' => $filename
        );
        global $TYPO3_DB;
        $this->_debug($TYPO3_DB->INSERTquery($this->theTable, $fieldArray));
    }

    function updateFeData()
    {
        global $TYPO3_DB;
        global $TSFE;
        $resFiles = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'resume_file',
            // FROM
            $this->theTable,
            // WHERE
            'user_id = '.$TSFE->fe_user->user['uid']
            .' '.$this->cObj->enableFields($this->theTable));
        if ($resFiles) {
            $_fileName = '';
            while ($row = $TYPO3_DB->sql_fetch_assoc($resFiles))
                $_fileName .= $row['resume_file'] . ',';

            $_fileName = substr($_fileName, 0, strlen($_fileName) - 1);
            $_arrFilename = array("tx_jobbankresumemgr_resume_file" => $_fileName);
            //$TYPO3_DB->exec_UPDATEquery($this->theFeTable,'uid='.$GLOBALS['TSFE']->fe_user->user['uid'],$_arrFilename);
        }
    }
    
    function getDataFromResumeTable($user_job_id, $resume_manager_user_job_id)
    {
        if (!is_numeric($resume_manager_user_job_id))
            return false;

        global $TYPO3_DB;
        global $TSFE;
        $res = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            '*',
            // FROM
            'tx_jobbankresumemgr_info',
            // WHERE
            "    uid     = $resume_manager_user_job_id
             AND job_id  = $user_job_id
             AND user_id = " . $TSFE->fe_user->user['uid']
             . ' ' . $this->cObj->enableFields('tx_jobbankresumemgr_info'));

        if ($TYPO3_DB->sql_num_rows($res) > 0)
            return $TYPO3_DB->sql_fetch_assoc($res);
    }

    function SendEmailNotification($to, $to_name, $applicant_email, $applicant_name, $subject, $message, $attachmentFile = '', $fileNameDisplay)
    {
        $mail = new PHPMailer();
        $mail->Mailer   = "sendmail";
        $mail->From     = $this->userData['email'];
        $mail->FromName = $this->userData['username'];
        $mail->Subject  = $subject;
        $mail->Body     = $message;

        $mail->AddAddress($to, $to_name);
        $mail->AddReplyTo($applicant_email, $applicant_name);
        $mail->IsHTML(true);

        if ($attachmentFile != '')
            $mail->AddAttachment($attachmentFile, $fileNameDisplay);

        return $mail->Send();
    }

    function getSponsorById($condition)
    {
        // HACK 
        // catch invalid condition strings and return false to prevent invalid SQL from being sent to the database
        if ($condition[strlen($condition) - 1] == '=')
            return false;

        global $TYPO3_DB;
        $resSponsor = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            '*',
            // FROM
            $this->theSponsorTable,
            // WHERE
            $condition
            . ' '. $this->cObj->enableFields($this->theSponsorTable));
        if (!$resSponsor)
            return 0;

        return $TYPO3_DB->sql_fetch_assoc($resSponsor);
    }
    
    function getSponsorByIdFromJob($fields, $condition)
    {
        // HACK 
        // catch invalid condition strings and return false to prevent invalid SQL from being sent to the database
        if ($condition[strlen($condition) - 1] == '=')
            return false;

        global $TYPO3_DB;
        $resSponsor = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            $fields,
            // FROM
            $this->theJobBankTable,
            // WHERE
            $condition
            . ' ' . $this->cObj->enableFields($this->theJobBankTable));
        if (!$resSponsor)
            return 0;

        return $TYPO3_DB->sql_fetch_assoc($resSponsor);
    }
    
    function getJobBankDetail($condition)
    {
        // HACK 
        // catch invalid condition strings and return false to prevent invalid SQL from being sent to the database
        if ($condition[strlen($condition) - 1] == '=')
            return false;

        global $TYPO3_DB;
        $resJobBank = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            '*',
            // FROM
            $this->theJobBankTable,
            // WHERE
            $condition
            . ' ' . $this->cObj->enableFields($this->theJobBankTable));
        if(!$resJobBank)
            return 0;

        return $TYPO3_DB->sql_fetch_assoc($resJobBank);
    }

    function showDebug($var)
    {
        echo "<PRE>";
        var_dump($var);
        echo "<PRE>";
    }

    function getData($keyName) 
    {
        $data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        return $data[$keyName];
    }
    
    function getCurrentSite()
    {
        $currentURL = 'http';
        $script_name = '';
    
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $currentURL .=  's';
        }
    
        $currentURL .=  '://';
        if($_SERVER['SERVER_PORT'] != '80') {
            $currentURL .= $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'];
        } else {
            $currentURL .= $_SERVER['HTTP_HOST'];
        }
    
        return $currentURL;
    }

    function getCountryZone($zoneFlag = 0, $selected = '')
    {
        global $TYPO3_DB;

        if ($zoneFlag)
        {
            $resZone = $TYPO3_DB->exec_SELECTquery(
                // SELECT
                'zn_country_iso_3,
                 uid,
                 zn_name_local',
                // FROM
                $this->tableNameCountryZone,
                // WHERE
                "zn_name_local != ''",
                // GROUP BY
                '',
                // ORDER BY
                'zn_country_iso_3,
                 zn_name_local');

            $oldVal = '';
            $ValCountryZone = '';
            $ctrZone = 0;
            while ($row = $TYPO3_DB->sql_fetch_assoc($resZone)) {
                if ($oldVal!=$row['zn_country_iso_3']) {
                    if ($ctrZone == 0)
                        $ctrZone++;
                    else 
                        $varReturnVal = substr($varReturnVal, 0, strlen($varReturnVal) - 1) . ")";

                    $varReturnVal .= "\n\nvar Menu$row[zn_country_iso_3]Menu = new Array(new Array(\"Select a State\",\"\"),";
                    $oldVal = $row['zn_country_iso_3'];
                    $ValCountryZone .= "'$oldVal',";
                }
                $varReturnVal .= "new Array(\"$row[zn_name_local]\", \"$row[uid]\"),";
            }
            $varReturnVal = substr($varReturnVal, 0, strlen($varReturnVal) - 1) . ")";
            $ValCountryZone = substr($ValCountryZone, 0, strlen($ValCountryZone) - 1);

            $resCountry = $TYPO3_DB->exec_SELECTquery(
                // SELECT
                "cn_iso_3",
                // FROM
                $this->tableNameCountry,
                // WHERE
                "cn_iso_3 not in (".$ValCountryZone.")");
            while ($row = $TYPO3_DB->sql_fetch_assoc($resCountry))
                $varReturnVal .= "\n\nvar Menu$row[cn_iso_3]Menu=new Array(new Array(\"Select a State\",\"\"))";
            return $varReturnVal;
        }
        else {
            $resCountry = $TYPO3_DB->exec_SELECTquery(
                // SELECT
                "cn_iso_3,
                 cn_short_en",
                // FROM
                $this->tableNameCountry,
                // WHERE
                "cn_short_en != ''",
                // GROUP BY
                '',
                // ORDER BY
                'cn_short_en');
            while ($row = $TYPO3_DB->sql_fetch_assoc($resCountry)) {
                if ($row['cn_iso_3'] == 'USA')
                    $varReturnVal .= "<option value=\"$row[cn_iso_3]\" selected>$row[cn_short_en]</option>\n";
                else 
                    $varReturnVal.="<option value=\"$row[cn_iso_3]\">$row[cn_short_en]</option>\n";
            }
            return $varReturnVal;
        }
    }

    function mapZoneId($zone_uid)
    {
        if (!is_numeric($zone_uid))
            return '';

        global $TYPO3_DB;
        $zone_name = '';
        $select = 'zn_name_local';
        $where = 'uid = '.$zone_uid;
        $resZone = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'zn_name_local',
            // FROM
            $this->tableNameCountryZone,
            // WHERE
            $where);

        if (mysql_num_rows($resZone) == 0)
            return '';

        $row = mysql_fetch_assoc($resZone);
        return $row['zn_name_local'];
    }
    
    function deleteFileNews($job_id, $user_id, $fileName)
    {
        @unlink($this->uploadPath . '/' . $fileName);
        global $TSFE;
        $TSFE->fe_user->setKey("ses", 'resume_file', '');
        $TSFE->fe_user->setKey("ses", 'resume_file_name', '');

        /*$table="fe_users";
          $field = "tx_jobbankresumemgr_resume_file";
          $where = "uid=$user_id";
        //@unlink($this->confData['relatedFilesDirectory'] . $fileName);
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        $relatedFiles=explode(',', $row['tx_jobbankresumemgr_resume_file']);

        $this->_debug($row);
        $this->_debug($relatedFiles);
        $updatedFiles='';
        echo $this->uploadPath.'/'.$fileName;
        foreach ($relatedFiles as $sourceFile) {

        if($sourceFile===$fileName){
        @unlink($this->uploadPath.'/'.$fileName);
        }else{
        $updatedFiles.="$sourceFile,";
        }
        }
        $updateFields=array(
        $field=>$updatedFiles
        );
        echo $GLOBALS['TYPO3_DB']->UPDATEquery($table_ttNews, $where, $updateFields);
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table_ttNews, $where, $updateFields);*/
    }

    function _debug( $var)
    {
       echo '<PRE>'.print_r($var, true).'</PRE>';
    }
    
    function getZoneByCode($zone_code, $countryCode)
    {
        global $TYPO3_DB;
        $res = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'uid',
            // FROM
            $this->tableNameCountryZone,
            // WHERE
            "zn_country_iso_3 = '$countryCode' and zn_code = '$zone_code'");
        if (!$res)
            return false;

        $row = $TYPO3_DB->sql_fetch_assoc($res);
        return $row['uid'];
    }
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/job_bank_resume_mgr/pi1/class.tx_jobbankresumemgr_pi1.php"])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/job_bank_resume_mgr/pi1/class.tx_jobbankresumemgr_pi1.php"]);
}

?>
