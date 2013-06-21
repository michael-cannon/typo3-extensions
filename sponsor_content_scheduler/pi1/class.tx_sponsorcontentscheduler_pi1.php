<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Saurabh Nanda (saurabhnanda@gmail.com)
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
 * Plugin 'Sponsor Content Scheduler' for the 'sponsor_content_scheduler' extension.
 *
 * @author    Saurabh Nanda <saurabhnanda@gmail.com>
 */





require_once ( t3lib_extMgm::extPath('sponsor_content_scheduler')."pi1/class.tx_sponsorcontentscheduler_base.php");
require_once ( t3lib_extMgm::extPath('sponsor_content_scheduler')."classes/class.tx_sponsorcontentschedular_jobbank.php");

require_once( CB_COGS_DIR . 'cb_html.php' );

class tx_sponsorcontentscheduler_pi1 extends tx_sponsorcontentscheduler_base {

    var $itemType;
    var $confData;
    var $_debugIP;
    var $catMapper;

    var $salesGroupId;
    var $_loggedInUserGroupId;
    var $loginType;
    var $pidPackagesSysFolder = 122;

    /**
     * [Put your description here]
     */
    function main($content,$conf)
    {
    	session_start();
        $this->pi_USER_INT_obj = 1;
        $this->conf=$conf;
        $this->content=$content;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();

        $this->init();

        $this->confData=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $this->itemType=array(
        $this->confData['articlesPID'] => "Article",
        $this->confData['whitePapersPID'] => "White Paper",
        $this->confData['roundTablesPID'] => "Round Table",
        $this->confData['presentationsPID'] => "Presentation",
        );

        //Get the Sales Group Id Which is used to check that logged in user belongs to the sales group
        $this->salesGroupId = trim($this->confData['salesGroupID']).",".trim($this->confData['sponsorGroupID']);

        //Logged in user id
        $_loggedInUserId = $GLOBALS['TSFE']->fe_user->user['uid'];

        //Logged in user group
        $this->_loggedInUserGroupId = $GLOBALS['TSFE']->fe_user->user['usergroup'];

        $_catArticlesArr=array($this->conf['catarticlesPID'],$this->conf['catpresentationsPID'],$this->conf['catroundTablesPID'],$this->conf['whitePapersPID']);
        $this->catMapper=array();
        $index = 0;
        foreach($_catArticlesArr as $_catArticles) {
            $arraycatArticlesSite = explode("||", $_catArticles);
            foreach($arraycatArticlesSite as $chunkData) {
                $arraycarArticlesData=explode("|",$chunkData);
                $this->catMapper[$arraycarArticlesData[0].'_'.$arraycarArticlesData[1]] = $arraycarArticlesData[2];
            }
            $index++;
        }

        // Branch according to the appropriate function according to
        // the choice made by the user
        
        switch($this->piVars['action'])
        {
            case 'create_edit_sponsor_screen':
                $content.=$this->create_edit_sponsor_screen();
                break;
            case 'create_sponsor':
                if(strtoupper($this->cObj->data['select_key'])=='SALES')
                {
                    $content.=$this->createSponsor();
                }
                else
                {
                    $content.=$this->generateMenu();
                }
                break;
            case 'edit_sponsor_profile':
                $content.=$this->editSponsorProfile();
                break;
            case 'create_edit_sponsor_user_screen':
                $content.=$this->create_edit_sponsor_user_screen();
                break;
            case 'create_sponsor_user':
                $content.=$this->createSponsorUser();
                break;
            case 'edit_sponsor_user':
                $content.=$this->editSponsorUser();
                break;
            case 'create_edit_sponsor_package_screen':
                $content.=$this->create_edit_sponsor_package_screen();
                break;
            case 'edit_inventory':
                $content.=$this->editInventory();
                break;
            case 'package_manager':
                $content.=$this->createSponsorPackage();
                break;
            case 'create_package':
                $content.=$this->createPackageNew();
                break;
            case 'editPackage':
                $content.=$this->editPackage();
                break;
            case 'edit_package':
                $this->updatePackage();
                $content.=$this->createSponsorPackage();
                break;
            case 'upload_high_res':
                $content = $this->__uploadHighRes();
                break;
            case 'bulletin_conf':
                $this->__renderBulletinLogo();
                $content = $this->__renderBulletin();
                break;
            case 'job_conf':
                $jobObj = new tx_sponsorcontentschedular_jobbank($this->cObj);
                $content = $jobObj->showContent();
                break;
            case 'bulletin_conf_logo':
                $content = $this->__renderBulletinLogo();
                break;
            case 'create_package_new':
                $this->savePackage();
                $content.=$this->createSponsorPackage();
                break;
            case 'deletePackage':
                $this->deletePackage();
                $content.=$this->createSponsorPackage();
                break;
            case 'create_edit_delete_job_bank_screen':
                $this->isValidSalesUser();
                switch($this->loginType) {
                    case 'SALES':
                        $content.=$this->create_edit_delete_job_bank_screen();
                        break;
                    case 'OWNER':
                        $sponsorArr=$this->getSponserFromOwner();
                        $jobBankURL=t3lib_div::locationHeaderUrl($this->pi_getPageLink($this->confData['jobBankPID'],'',array('tx_jobbank_pi1[sponsor_id]' => $sponsorArr['uid'])));
                        header("Location: ".$jobBankURL);
                        break;
                }
                break;
            case 'edit_job_bank':
                $jobBankURL=t3lib_div::locationHeaderUrl($this->pi_getPageLink($this->confData['jobBankPID'],'',array('tx_jobbank_pi1[sponsor_id]' => $this->piVars['sponsor_id'])));
                header("Location: ".$jobBankURL);
                break;
            case 'downloadlead':
                $this->downloadLeads();
                break;
            case 'downloadevent':
                $this->downloadEvents();
                break;
            default:
                $content.=$this->generateMenu();
        }

        return $content;
    }

    function init(){
        parent::init();

    }
    /**
     * This functions displays a menu displaying a list of options available 
     * to the the Sales Personnel 
     */

    function generateMenu() {

        $content='';

        if(strtoupper($this->cObj->data['select_key'])=='SALES'){
            if ($this->isLoggedInUserBelongsToSalesGroup($this->_loggedInUserGroupId, $this->salesGroupId)) {
                $this->isValidSalesUser();

                switch($this->loginType){
                    case 'SALES':
                    $content=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/menu.tpl');
                    break;
                    case 'OWNER':
                    $content=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/owner_menu.tpl');
                    break;
                    // Replace all the markers in the template with the dynamic content
                    default:
                    $content=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/error.tpl');
                    break;
                }
                $markerArray=array(
                '###FORM_NAME###' => $this->formName('menu'),
                '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
                '###RADIO_CHOICE###' => $this->formName('action'),
                //'###SPONSOR_LIST_NAME###' => $this->formName('sponsor_id'),
                //'###SPONSOR_LIST###' => $sponsorList,
                '###SPONSOR_USER_LIST_NAME###' => $this->formName('sponsor_user_id'),
                //'###SPONSOR_USER_LIST###' => $sponsorUserList,
                '###SUBMIT_SPONSOR###' => $this->formName('submit_sponsor'),
                '###SUBMIT_SPONSOR_USER###' => $this->formName('submit_sponsor_user')
                );
                $markerArray['###HEADER###']='Sales Personnel Activities';
                $markerArray['###CREATE_EDIT_SPONSER_LINK###'] = $this->getPageLink('CREATE | EDIT SPONSOR','create_edit_sponsor_screen');
                $markerArray['###CREATE_EDIT_SPONSER_USER_LINK###'] = $this->getPageLink('CREATE | EDIT SPONSOR USER','create_edit_sponsor_user_screen');
                $markerArray['###CREATE_EDIT_SPONSER_PACKAGE_LINK###'] = $this->getPageLink('CREATE  | EDIT  SPONSOR PACKAGE','create_edit_sponsor_package_screen');
                $markerArray['###CREATE_EDIT_DELETE_JOB_BANK_LINK###'] = $this->getPageLink('CREATE  | EDIT | DELETE JOB BANK','create_edit_delete_job_bank_screen');
                $content=$this->cObj->substituteMarkerArrayCached($content, $markerArray);

            }else{
                $template_menu=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/error.tpl');
                $content=$this->cObj->substituteMarkerArrayCached($template_menu);

            }//End if logged in user group check with sales person

        }else{
            $this->isValidSalesUser();

            switch($this->loginType){
                case 'ERROR':
                $content=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/error.tpl');
                $content=$this->cObj->substituteMarkerArrayCached($content, $markerArray);
                break;
                case 'SALES':
                $content=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/error.tpl');
                $content=$this->cObj->substituteMarkerArrayCached($content, $markerArray);
                break;
                case 'OWNER':
                default:
                // Assume that a sponsor has logged in
                $user_id = $GLOBALS['TSFE']->fe_user->user['uid'];

                //Get Sponsor users info
                if($user_id!=''){

                    //Gets the first welcome note and sponsor and sales data block
                    $content .= $this->getSponsorPageWelcomeNoteAndDetails();

                    // Get the Sponsor package contents block
                    $content .= $this->getSponsorPageContentDetails();

                } //End if
                break;
            }


        }//End Else of sponsor login

        return $this->pi_wrapInBaseClass($content);

    }

    function getSponsorPageWelcomeNoteAndDetails(){

        $content = '';
        //Read the template file sponsor_welcome_note.tpl
        $this->templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/sponsor/sponsor_welcome_note.tpl');
        $template['headers'] = $this->cObj->getSubpart($this->templateCode, '###SPONSOR_WELCOME_NOTE_BLOCK###');
        $markerArray = array();
        $user_id = $GLOBALS['TSFE']->fe_user->user['uid'];

        //Get the data of the sponsor user and sales user
        $table='fe_users,tx_t3consultancies';
        $columns='fe_users.username,tx_t3consultancies.uid,tx_t3consultancies.title,
		tx_t3consultancies.logo,tx_t3consultancies.fe_owner_user,
		tx_t3consultancies.tx_sponsorcontentscheduler_sponsor_page as
		sponsor_page, fe_users.'.$this->tablePrefix.'package_id';
        //$where="fe_users.uid='$user_id' AND fe_users.tx_sponsorcontentscheduler_sponsor_id=tx_t3consultancies.uid";
        $where="fe_users.uid='$user_id' AND tx_t3consultancies.uid=fe_users.tx_sponsorcontentscheduler_sponsor_id";

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        if($GLOBALS['TYPO3_DB']->sql_num_rows($result)<1){

            return $this->pi_wrapInBaseClass($content);
        }
        //echo $GLOBALS['TYPO3_DB']->SELECTquery($columns, $table, $where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

		$sponsor_name=$row['title'];
		$sponsor_id=$row['uid'];
		$user_name=$row['username'];

		if ($row['sponsor_page']) {
            $typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=edit_sponsor_profile&".$this->prefixId."[sponsor_id]=".$sponsor_id;
            $company_profile=$this->cObj->typoLink('Company
				Profile',$typolink_conf_main);
        }
		
		 $_rightsInfo = $this->getRightsInfo();
		 if(is_array($_rightsInfo)){
			 foreach ($_rightsInfo  as $_packageIdentity => $packageInfo){
				 $_rightsData =
				 explode(':',$_rightsInfo[$_packageIdentity]['rights']);                                                                          
				 if(is_array($_rightsData)){
					 $_packageData[$user_id]['JOBBANK']
					 =
					 intval($_rightsData[0]);                                                                               
					 $_packageData[$user_id]['BULLETIN']
					 =
					 intval($_rightsData[1]);
					 $_packageData[$user_id]['EMAILBLAST']
					 =
					 intval($_rightsData[2]);
				}
			}
		}
																																					 

        $markerArray['###USERNAME###']=$user_name;
		$markerArray['###COMPANY_PROFILE###']= $company_profile;
		$typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=upload_high_res";
        $markerArray['###COMPANY_PROFILE###'] .="<br/>".$this->cObj->typoLink('Upload High-resolution Logos',$typolink_conf_main);
        if($_packageData[$user_id]['BULLETIN']){
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=bulletin_conf";
            $markerArray['###COMPANY_PROFILE###'] .=
				"<br/>".$this->cObj->typoLink('Bulletin Sponsorship',$typolink_conf_main); 
		}
		
		if($_packageData[$user_id]['JOBBANK']){
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=job_conf";
            $markerArray['###COMPANY_PROFILE###'] .=									"<br/>".$this->cObj->typoLink('Job Bank									Listings',$typolink_conf_main);
		}

        $markerArray['###SPONSOR###']='<TR><TD colspan="2"><B>Welcome '. $user_name . '</B><BR><BR><input type="hidden" name="'. $this->formName('sponsor_id'). "\" value=\"$sponsor_id\" / ></TD></TR>";

        $logoImage=$this->confData['logoDirectory'].$row['logo'];

        $markerArray['###SPONSOR_LOGO###'] ='';
        //Display Sponsor Logo
        if(file_exists($logoImage) && ($row['logo']!='')){
            $mysock = getimagesize($logoImage);
            $imageDisp="<img src='$logoImage' ".$this->imageResize($mysock[0],
			$mysock[1], 150)." border='0'>";
            $markerArray['###SPONSOR_LOGO###'] = $imageDisp;
        }

        if($row['fe_owner_user']!=''){
            $ownerAttributes=$this->getOwnerAttributes($row['fe_owner_user']);
            $markerArray['###SALES_REP###']=$ownerAttributes['name']."<br/>";
            $markerArray['###OFFICE_CONTACT_INFO###']='Office : '.$ownerAttributes['telephone']."<br/>";
            $typolink_conf = array(
            "parameter" => $ownerAttributes['email'],
            );
            $typolink_conf_main = array(
            "parameter" => $GLOBALS['TSFE']->id,
            );
            $markerArray['###SPONSOR_EMAIL###']="<a href='mailto:".$ownerAttributes['email']."'>".$ownerAttributes['email']."</a>";
            //    echo htmlspecialchars($markerArray['###SPONSOR_EMAIL###']);
        }else{
            $markerArray['###SALES_REP###']='';
            $markerArray['###OFFICE_CONTACT_INFO###']='';
            $markerArray['###SPONSOR_EMAIL###']='';
        }

        //This marker gets all the sponsor users tt_news items which has due date
        $markerArray['###DUE_NEWS_INFO###'] = '';
        $markerArray['###DUE_NEWS_LIST###'] = $this->getDueNewsList($user_id, $sponsor_id);
        if($markerArray['###DUE_NEWS_LIST###']!=''){
            $markerArray['###DUE_NEWS_INFO###'] = '<font color="#cc3300">You have <b>IMPORTANT DEADLINES</b> in the next two weeks: </font>';
        }

        $content .= $this->cObj->substituteMarkerArrayCached($template['headers'], $markerArray, array(), array());
        return $content;
    }

    function getSponsorPageContentDetails(){

        $content = '';
        $flag = 0;
        //this userid is the sponsor id
        $user_id = $GLOBALS['TSFE']->fe_user->user['uid'];

        //Read the template file sponsor_welcome_note.tpl
        $this->templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/sponsor/sponsor_package_contents.tpl');
        $template['headers'] = $this->cObj->getSubpart($this->templateCode, '###SPONSOR_PACKAGE_CONTENT_BLOCK###');

        //Get sponsor id
        $table='fe_users,tx_t3consultancies';
        $columns='fe_users.username,tx_t3consultancies.uid,tx_t3consultancies.title, tx_t3consultancies.logo,tx_t3consultancies.tx_sponsorcontentscheduler_sponsor_page as sponsor_page,tx_t3consultancies.fe_owner_user, fe_users.'.$this->tablePrefix.'package_id';
        //$where="fe_users.uid='$user_id' AND fe_users.tx_sponsorcontentscheduler_sponsor_id=tx_t3consultancies.uid";
        $where="fe_users.uid='$user_id' AND tx_t3consultancies.uid=fe_users.tx_sponsorcontentscheduler_sponsor_id";

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        if ($result) {
            if (mysql_num_rows($result)) {
                $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

                $sponsor_name=$row['title'];
                $sponsor_id=$row['uid'];
                $isToShowCompanyProfileLinkOnSponsorPage = $row['sponsor_page'];

                if ($isToShowCompanyProfileLinkOnSponsorPage) {
                    $flag=1;
                }
            }
        }//ENd if - result


        //Check if Company Profile edit link is to be shown on the sponsor page by checking the field tx_sponsorcontentscheduler_sponsor_page in table t3consultancy
        //Get the data of the sponsor user and sales user
        
        if ($flag==1) {
            $typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=edit_sponsor_profile&".$this->prefixId."[sponsor_id]=".$sponsor_id;
            $company_profile=$this->cObj->typoLink('Company Profile',$typolink_conf_main);
        }
        
        
        
        //Get the rights information for showing the bulletin sponsorship and job bank
        $_rightsInfo = $this->getRightsInfo();
        if(is_array($_rightsInfo)){
            
            foreach ($_rightsInfo  as $_packageIdentity => $packageInfo){
                $_rightsData = explode(':',$_rightsInfo[$_packageIdentity]['rights']);
                if(is_array($_rightsData)){
                    $_packageData[$user_id]['JOBBANK'] = intval($_rightsData[0]);
                    $_packageData[$user_id]['BULLETIN'] = intval($_rightsData[1]);
                    $_packageData[$user_id]['EMAILBLAST'] = intval($_rightsData[2]);
                }
            }
        }
        
        // Ends code
        $markerArray['###COMPANY_PROFILE###'] = $company_profil;
        $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=upload_high_res";
        $markerArray['###COMPANY_PROFILE###'] .= "<br/>".$this->cObj->typoLink('Upload High-resolution Logos',$typolink_conf_main);
        if($_packageData[$user_id]['BULLETIN']){
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=bulletin_conf";
            $markerArray['###COMPANY_PROFILE###'] .= "<br/>".$this->cObj->typoLink('Bulletin Sponsorship',$typolink_conf_main);
        }
        
        if($_packageData[$user_id]['JOBBANK']){
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=job_conf";
            $markerArray['###COMPANY_PROFILE###'] .= "<br/>".$this->cObj->typoLink('Job Bank Listings',$typolink_conf_main);
        }
        
        if($_packageData[$user_id]['EMAILBLAST']){
            $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=email_blast";
            $markerArray['###COMPANY_PROFILE###'] .= "<br/>".$this->cObj->typoLink('Email Blasts',$typolink_conf_main);
        }

        $markerArray['###SPONSOR_USER_PACKAGES###'] = $this->getSponsorUserPackages($user_id, $sponsor_id);

        $markerArray['###ATTENDANCE_REPORT###'] = $this->getRegMemberRoundTable($user_id, $sponsor_id);

        //Marker For Leads Section
        $markerArray['###LEADS###'] = $this->getLeadsSection($user_id, $sponsor_id);

        $content .= $this->cObj->substituteMarkerArrayCached($template['headers'], $markerArray, array(), array());
        return $content;
    }

    function getRightsInfo(){
        $data =  array();
        $fields='*';
        $tables = $this->tablePrefix.'package';
        $where = $tables.'.fe_uid='.$GLOBALS['TSFE']->fe_user->user['uid'].' '.$this->cObj->enableFields($tables);
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tables,$where);
        if($res){
            while($row =$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
                $data[$row['uid']] = $row;
            }
        }
        
        return $data;
    }
    /**
     * Function to display the No Data
     *
     * @param integer $colspan Columns to span if tables are being used in the data
     * @param string $section Marker name to be used for displaying the data
     * @return string
     */
    function noDataDisp($colspan,$section='NODATA'){
        $section = strtoupper($section);
        $this->templateCode = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/sponsor/sponsor_package_contents.tpl');
        $template['nodata'] = $this->cObj->getSubpart($this->templateCode, "###$section###");
        $markerArray['###NOCOLSPAN###']=$colspan;
        $content = $this->cObj->substituteMarkerArrayCached($template['nodata'], $markerArray);
        return $content;

    }

    /**
     */
    function getRegMemberRoundTable($user_id, $sponsor_id)
    {
        $content = '';

        //This query selects news items related tpo packages against users
        $columns1 = 'n.uid as news_uid, p.title';
        $table1 = 'tt_news_cat_mm ncm, tt_news n, tx_sponsorcontentscheduler_package p';
        $where1 = sprintf('ncm.uid_local = n.uid '
            . 'AND n.tx_sponsorcontentscheduler_package_id = p.uid '
            . 'AND n.pid = 61 '
            . 'AND p.hidden = 0 AND p.deleted = 0 AND p.fe_uid = %d '
            . 'AND p.sponsor_id = %d', $user_id, $sponsor_id);

        $result1=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns1, $table1, $where1);

        if ($result1) {
            if (mysql_num_rows($result1)) {
                while($row1 = mysql_fetch_assoc($result1)){
                    $package_name = $row1['title'];
                    $package_news_array[$package_name][] =$row1['news_uid'];
					t3lib_div::devLog("1", 'YES');
                }//END while - $result1

            }    //END if - mysql_num_rows($result1)

        }//End if $result1
        if(is_array($package_news_array)){
            $alt = false;
            $class = 'class="td-lightgrey"';
            foreach($package_news_array as $package_name=>$news_arr){
                if ($alt) {
                	$lclass = $class;
                } else {
                    $lclass = '';
                }
                $count = $this->getCountRegisteredUser($news_arr, $class, !$alt);
                if (!empty($count)) {
                    $content .= '<tr><td colspan="3">&nbsp;</td></tr><tr '.$lclass.'><td colspan="3"><big><b>['.$package_name.']</b></big></td></tr>';
                    $content .= $count;
                    $alt = !$alt;
                }
            }
        }

        //Condition to check if the data is there otherwise show no data block
        if(strlen(trim($content))>0){
            return $content;
        }else{
            return $this->noDataDisp(3);
        }


    }



    function getCountRegisteredUser($news_arr, $class, $alt){
        
        $news_id_arr = array();
        $news_arr = array_unique($news_arr);
        foreach($news_arr as $k=>$v){
			if ($alt) {
				$lclass = $class;
			} else {
				$lclass = '';
			}

            //$news_id_arr[] ='news_id='.$v;
            $content .='<tr '.$lclass.'>';
            $content .= $this->getNewsDetail($v);
        	$content .= $this->getTotalRegisteredParticipants($v, 0);
        	$content .='</tr>';
			$alt = !$alt;
        }

        return $content;
    }

    //This function return the site related registered users count for news id of the sponsor user
    function getPackageRelatedSiteNameNewsDetail($pid_newsId_arr, $class){
        $content = '';

        //Now loop through the above array to print the site name and loop through the news id to get the data title, date fron tt_news and count of users from participants table
        if(!is_array($pid_newsId_arr)){
            return $content;
        }
        foreach ($pid_newsId_arr as $pid=>$news_arr){

            $content .= '<tr '.$class.'><td colspan="3"><b>'.$this->getSiteNameRelatedToPid($pid).'<b></td></tr>';
            $alt = true;
            foreach($news_arr as $k=>$news_id){
                $content .='<tr '.$class.'>';
                $content .=$this->getNewsDetail($news_id);
                $content .=$this->getTotalRegisteredParticipants($news_id,$pid);
                $content .='</tr>';
            }
        }

        return $content;

    }


    function getNewsDetail($news_id){

        $content ='';
        $select_news = 'uid, title,crdate';
        $table_news = 'tt_news';
        $where_news = 'uid='.$news_id.' AND hidden=0 AND deleted=0';
        $linkConf=array(
                    'parameter' => $GLOBALS['TSFE']->id,
                    'additionalParams'=>"&".$this->prefixId.'[action]=downloadevent&'.$this->prefixId.'[news_id]='.$news_id,
                );
        $link = $this->cObj->typoLink_URL($linkConf);
        //echo $GLOBALS['TYPO3_DB']->SELECTquery($select_news, $table_news, $where_news);
        $result_news_details=$GLOBALS['TYPO3_DB']->exec_SELECTquery($select_news, $table_news, $where_news);
        if ($result_news_details) {
            if (mysql_num_rows($result_news_details)) {
                $row = mysql_fetch_assoc($result_news_details);
                $content .= '<td><b>'.$row['title'].'</b></td>';
                $content .= '<td>'.date('F d, Y',$row['crdate']).'</td>';
            } else {
                $content .= '<td><b>No Title</b></td><td>&nbsp;</td>';
            }
        }
        return $content;

    }
    function getSiteNameRelatedToPid($pid){

        $_siteMapper=$this->conf['siteMapper'];
        $_arrSiteMapper=explode("|",$_siteMapper);
        $siteMapper=array();
        //($_arrSiteMapper);
        /*Array
        (
        [1] => BPM Institute
        [2] => SOA Institute
        )*/
        foreach($_arrSiteMapper as $value){
            // 1,2 and 2,39
            $tmp_arr = explode(',', $value);
            $siteMapper[$tmp_arr[0]]=$tmp_arr[1];

        }

        $siteRoundTablePid=explode('|',$this->conf['roundTableParticipantsPid']);

        foreach($siteRoundTablePid as $value){
            // 1,2 and 2,39
            $tmp_arr = explode(',', $value);
            $site_name_pid_arr[$tmp_arr[0]]=$tmp_arr[1];

        }

        //New array conteining site name as key and category id as value

        $pid_siteName_arr = array();

        /* $site_name_pid_arr is this type of array Array
        (
        [124] => BPM Institute
        [324] => SOA Institute
        )*/
        foreach($site_name_pid_arr as $k=>$v){

            $pid_siteName_arr[$v] = $siteMapper[$k];
        }

        foreach($pid_siteName_arr as $kPid=>$kSite){
            if ($pid==$kPid){
                return $kSite;
            }
        }


    }
    function getTotalRegisteredParticipants($news_id,$pid){

        $content ='';
        $linkConf=array(
                    'parameter' => $GLOBALS['TSFE']->id,
                    'additionalParams'=>"&".$this->prefixId.'[action]=downloadevent&'.$this->prefixId.'[news_id]='.$news_id,
                );
        $link = $this->cObj->typoLink_URL($linkConf);
        
        $table1='tx_newseventregister_participants p INNER JOIN fe_users u ON p.fe_user_id = u.uid INNER JOIN tt_news n ON p.news_id = n.uid';
        
        $columns1='count(distinct u.email) as user';

        $where1 = "1 = 1 AND p.hidden = 0 AND p.deleted = 0 AND u.disable = 0 AND u.deleted = 0 AND n.hidden = 0 AND n.deleted = 0 AND p.news_id=$news_id";

        //echo $GLOBALS['TYPO3_DB']->SELECTquery($columns1, $table1, $where1);
        $result1=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns1, $table1, $where1);
        if ($result1) {
            if (mysql_num_rows($result1)) {
                $row = mysql_fetch_assoc($result1);
                if (0 == $row['user']) {
                	$content .= '<td align="center">&nbsp;'.$row['user'].'&nbsp;</td>';
                } else {
                    $content .= '<td align="center"><a href="'.$link.'">&nbsp;'.$row['user'].'&nbsp;</a></td>';
                }
            }
        }

        return $content;
    }
    function getSiteNameAndRespectiveParticpantsPid(){

        //$_siteMapper - BEGIN

        //$site_roundTable_id = Array ( [0] => 1,2 [1] => 2,39 )
        $_siteMapper=$this->conf['siteMapper'];
        $_arrSiteMapper=explode("|",$_siteMapper);
        $siteMapper=array();
        //print_r($_arrSiteMapper);
        /*Array
        (
        [1] => BPM Institute
        [2] => SOA Institute
        )*/
        foreach($_arrSiteMapper as $value){
            // 1,2 and 2,39
            $tmp_arr = explode(',', $value);
            $siteMapper[$tmp_arr[0]]=$tmp_arr[1];

        }

        //$_siteMapper - END



        //roundTable Participants site related  PId - BEGIN

        $siteRoundTablePid=explode('|',$this->conf['roundTableParticipantsPid']);
        /*
        $site_name_pid_arr = array(
        [1] => 124
        [2] => 324

        */
        foreach($siteRoundTablePid as $value){
            // 1,2 and 2,39
            $tmp_arr = explode(',', $value);
            $site_name_pid_arr[$tmp_arr[0]]=$tmp_arr[1];

        }

        //New array conteining site name as key and category id as value

        $siteName_categoryId = array();

        /*Array
        (
        [BPM Institute] => 124
        [SOA Institute] => 324
        )*/
        foreach($site_name_pid_arr as $k=>$v){

            $siteName_PId[$v] = $siteMapper[$k];
        }

        return $siteName_PId;
    }

    function getLeadsSection($user_id, $sponsor_id)
    {
        $sponsorUserNewsList = $this->getSponsorUsersNewsList($user_id, $sponsor_id);
        if (count($sponsorUserNewsList) == 0)
            return $this->noDataDisp($colspan = 5);

        global $TSFE;
        $content = '';

		$table='fe_users,tx_t3consultancies';
        $columns='logo,tx_t3consultancies.fe_owner_user';
		$where="fe_users.uid='$user_id' AND
			tx_t3consultancies.uid=fe_users.tx_sponsorcontentscheduler_sponsor_id";

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns,
				$table, $where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		$ownerAttributes=$this->getOwnerAttributes($row['fe_owner_user']);
        $ownerName =$ownerAttributes['name'];
        $ownerPhone =$ownerAttributes['telephone'];
        $alt = false;
        $class = 'class="td-lightgrey"';
		
        foreach ($sponsorUserNewsList as $package_title => $news_arr)
        {
            // Get Package Name
			if ($alt) {
				$lclass = $class;
			} else {
				$lclass = '';
                $hclass = '';
            }
            $content .= '<tr><td colspan="5">&nbsp;</td></tr><tr '.$lclass.'><td colspan="5" valign="top" ><big><b>['.$package_title.']</b></big></td></tr>';
            // Get New
            foreach ($news_arr as $news_id => $news_details)
            {
				if (! $alt) {
					$lclass = $class;
				} else {
					$lclass = '';
					$hclass = '';
				}
                // Print News Title
                $linkConf = array(
                    'parameter'        => $TSFE->id,
                    'additionalParams' => "&".$this->prefixId.'[action]=downloadlead&'.$this->prefixId.'[lead_id]='.$news_id,
                );
                $link = $this->cObj->typoLink_URL($linkConf);
                $content .= '<tr '.$lclass.'><td valign="top"><b>';
                $content .= $news_details[0];
                $content .= '</b><br/>';
//				$content .= $this->getDownloadableLeadsPreviouslySent($news_id);
				$content .= '</td>';

                // Map the news pid with type name from array
                $type = '';
                if (array_key_exists($news_details[2], $this->itemType))
                    $type = $this->itemType[$news_details[2]];

                // Print News Type like roundtable etc
                $content .= '<td valign="top" align="center">'.$type.'</td>';

                // Print total no of news lead sent
				$lead_sents=  $this->getTotalNewsLeadSentNotSent($news_id,
				                $newsLeadSentOrNotSent=1);
                $content .= '<td valign="top" align="center">';
				if ($lead_sents > 0) {
					$content .= '<a href="'.$link.'">&nbsp;';
				}
				$content.= $lead_sents;
				if( $lead_sents > 0) {
					$content .= '&nbsp;</a><br/>';
				}
				$content.='</td>';

                // Print total no of news lead sent
                $content .= '<td valign="top" align="center" style="
				text-align: left; padding-left: 1.5em;"><div style="position: absolute">
				<a href="javascript:void(0)"
				onclick="getElementById(\'leadID'.$news_id.'\').style.display =
				\'block\'">&nbsp;';
				$content .= $this->getTotalNewsLeadSentNotSent($news_id,
				$newsLeadSentOrNotSent=0);
				$content .= '&nbsp;</a><div id="leadID'.$news_id.'" style="
				postion: relative; width: 150px; background: white; border: red
				solid 1px; padding:5px; display: none; overflow:
				inherit; margin-left: 30px;">
				<div style="text-align: right"> <a href="javascript:void(0)"
				onclick="getElementById(\'leadID'.$news_id.'\').style.display=
				\'none\'"
				style="color:red">&nbsp;X&nbsp;</a> </div>
				"Not Sent" leads are leads which continue to accumulate outside of your lead period. Contact your online coordinator to purchase these leads.
				</div></div>';
				$content .= '</td>';

                // Get the leads time frame values from tt_news_tx_newslead_timeframes_mm and  tx_newslead_leadperiod
                $content .= '<td valign="top">'.$this->getNewsIdRelatedLeadsFramesetDates($news_id).'</td>';
                $alt = !$alt;
            }
        }
        return $content;
    }
    
    /**
     * Return HTML link to a random file which is somehow connected to a given news_id
     * FIXME what does this function do and why
     */
    function getDownloadableLeadsPreviouslySent($news_id)
    {
        global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            'filename',
            // FROM
            'tx_newslead_leads',
            // WHERE
            "news_id = '$news_id' AND leadsent = 1 AND hidden = 0 AND deleted = 0");

        if (!$result)
            return '';
        if (mysql_num_rows($result) == 0)
            return '';

        $row = mysql_fetch_assoc($result);
        $leadFileSent = $row['filename'];
        // File Downloadable Path
        $file_path = $leadFileSent;
        // Don't prefix external leads
        if (!in_array(substr($leadFileSent, 0, 7), array('http://', 'https:/')))
            $file_path = $this->confData['relatedFilesDirectory'].$leadFileSent;
        // Show only the filename
        $leadFileSent = basename($leadFileSent);
        list($leadFileSent) = explode('&', $leadFileSent);

        $downloadable_leads_sent_file_link = '<a href = '.$file_path.' target="_blank">'.$leadFileSent.'</a><br/>';
        return $downloadable_leads_sent_file_link;
    }

    /**
     * Returns HTML with a list of lead period start and end dates for a given news_id
     */
    function getNewsIdRelatedLeadsFramesetDates($news_id)
    {
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT DISTINCT
                leadperiod.startdate,
                leadperiod.enddate
            FROM
                tt_news_tx_newslead_timeframes_mm timeframes_mm,
                tx_newslead_leadperiod leadperiod
            WHERE
                timeframes_mm.uid_local = %s
                AND timeframes_mm.uid_foreign = leadperiod.uid
                AND leadperiod.hidden = 0
                AND leadperiod.deleted = 0',
                $TYPO3_DB->fullQuoteStr($news_id)));
        if (!$res)
            return '';
        if (mysql_num_rows($res) == 0)
            return '';

        $ret = '';
        while ($row = mysql_fetch_assoc($res))
            $ret .= sprintf('<nobr>%s&nbsp;-&nbsp;%s</nobr><br/>',
                        date('n/j/y', $row['startdate']),
                        date('n/j/y', $row['enddate']));
        return $ret;
    }

    /**
     * Returns number of leads not sent for a given news_id
     */
    function getTotalNewsLeadSentNotSent($news_id,$newsLeadSentOrNotSent='')
    {
        global $TYPO3_DB;
        $query = sprintf('
            SELECT
                COUNT( DISTINCT u.email ) AS total_count
            FROM
                tx_newslead_leads                 l
                LEFT JOIN      tt_news            n ON                l.news_id = n.uid
                LEFT JOIN      fe_users           u ON             l.fe_user_id = u.uid
            WHERE
                l.hidden = 0
                AND l.deleted = 0
                AND l.leadsent = %s
                AND u.disable = 0
                AND u.deleted = 0
                AND n.hidden = 0
                AND n.deleted = 0
                AND n.uid IN (%s)
			',
			$TYPO3_DB->fullQuoteStr($newsLeadSentOrNotSent, ''),
			$TYPO3_DB->fullQuoteStr($news_id, '')
		);
        $result = $TYPO3_DB->sql_query( $query );
        if (!$result)
            return false;
        if (mysql_num_rows($result) == 0)
            return false;

        $row = mysql_fetch_assoc($result);
        return $row['total_count'];

    }

    /**
     * Return all white papers WITH files in a sponsor package for a given
     * sponsor user and sponsor in the form
     * array(
     *     "Package title" => array(
     *         news_id => array(news_title, news_files, news_pid ( = type))
     *     )
     * )
     *
     * (with files == can have leads)
     */
    function getSponsorUsersNewsList($user_id, $sponsor_id)
    {
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf("
            SELECT
                distinct(n.uid),
                n.title,
                n.news_files,
                n.pid,
                p.title as package_title,
                p.uid as package_uid,
                n.tx_newslead_timeframes
            FROM
                tt_news n,
                tx_sponsorcontentscheduler_package p
            WHERE
                p.fe_uid = %d
                AND p.sponsor_id = %d
                AND n.tx_sponsorcontentscheduler_package_id = p.uid
                AND n.news_files != ''
                AND n.hidden = 0
                AND n.deleted = 0",
                $user_id, $sponsor_id));

        $news_arr = array();
        while ($row = mysql_fetch_assoc($res))
            $news_arr[$row['package_title']][$row['uid']] = array($row['title'], $row['news_files'], $row['pid']);
        return $news_arr;
    }

    function getSponsorUserPackages($user_id, $sponsor_id){
        $typolink_conf_main['parameter'] = $GLOBALS['TSFE']->id;

        $table='tx_sponsorcontentscheduler_package';
        $columns='uid,title';
        //$where="fe_users.uid='$user_id' AND fe_users.tx_sponsorcontentscheduler_sponsor_id=tx_t3consultancies.uid";
        $where="fe_uid ='$user_id' AND sponsor_id ='$sponsor_id' AND hidden = 0 AND deleted = 0";

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        if ($result) {
            if (mysql_num_rows($result)) {
                while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
                    $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=edit_inventory&".$this->prefixId."[package_id]=".$row['uid']."&".$this->prefixId."[sponsor_id]=".$sponsor_id;

                    $package_list .= "<li>".$this->cObj->typoLink($row['title'],$typolink_conf_main)."</li>";
                }

            }
        }
        //Condition for checking if the Data is there otherwise show No data Block

        if(strlen($package_list)>0){
            $content =  $package_list;
        }else{
            $content =  $this->noDataDisp(0,'NOPACKAGE');
        }
        return $content;
        //        return $package_list;
    }

    function getDueNewsList($user_id, $sponsor_id){

        $content = '';
        //Get all packages related to userid and sponsor_id
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf("
            SELECT
                n.uid AS nuid,
                n.title AS ntitle,
                n.pid AS npid,
                n.tx_sponsorcontentscheduler_news_due_date,
                p.title AS ptitle
            FROM
                tx_sponsorcontentscheduler_package p,
                tt_news n
            WHERE
                p.uid = n.tx_sponsorcontentscheduler_package_id AND p.fe_uid = %s
                AND p.sponsor_id = %s AND p.hidden = 0 AND p.deleted = 0
                AND n.bodytext = ' ' AND n.hidden = 0 AND n.deleted = 0
                AND n.tx_sponsorcontentscheduler_news_due_date - UNIX_TIMESTAMP() BETWEEN 0 AND 60*60*24*14",
                $TYPO3_DB->fullQuoteStr($user_id),
                $TYPO3_DB->fullQuoteStr($sponsor_id)));
        $news_array = array();
        while ($row = $TYPO3_DB->sql_fetch_assoc($res))
            $news_array[$row['ptitle']] [$this->itemType[$row['npid']]] [$row['nuid']] [$row['ntitle']]
                = date('d/m/Y', $row['tx_sponsorcontentscheduler_news_due_date']);

        //Read array and show the data
        $i = 1;
        foreach($news_array as $package_name => $detail_arr) {

            $content .= sprintf('<br/><b>%d. %s</b>', $i, $package_name);;
            foreach($detail_arr as $type => $news_detail_arr){
                $content .= '<br/><b>' . $type . '</b><br/>';
                foreach($news_detail_arr as $news_id => $news){

                    foreach($news as $news_title => $date)
                    $content .= '<li>' . $news_title . ' due date: ' . $date . '<br/>';
                }
            }
            $i++;
        }

        return $content;
    }


    function getPageLink($linked_text,$additonal_parameter){

        $typolink_conf_main = array(
        "parameter" => $GLOBALS['TSFE']->id,
        );


        $typolink_conf_main['additionalParams']="&".$this->prefixId."[action]=".$additonal_parameter;

        return $this->cObj->typoLink($linked_text,$typolink_conf_main);
    }
    function getOwnerAttributes($id)
    {
        $table='fe_users';
        $columns='fe_users.*';
        $where="fe_users.uid='$id'";
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        //echo $GLOBALS['TYPO3_DB']->SELECTquery($columns, $table, $where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        return $row;

    }

    /**
     * This functions simply returns back the given string surrounded by square braces [] and
     * prepended by $this->prefixId... internal function for use in form generation
     */
    function formName($name, $offset='')
    {
        if($offset=='')
        {
            return $this->prefixId.'['.$name.']';
        }
        else
        {
            return $this->prefixId.'['.$name.']'.'['.$offset.']';
        }
    }


    /**
     * This function returns a SELECT form widget with the list of all available sponsors
     */
    function sponsorSelect()
    {
        $list='';

        /** TODO - Add a condition to select only those FE groups which are sponsor groups     */

        $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $_where='fe_owner_user = '.$GLOBALS['TSFE']->fe_user->user['uid'].' and pid="'.$data['sponsorsPID'].'" '. t3lib_BEfunc::deleteClause('tx_t3consultancies');
        //        echo $GLOBALS['TYPO3_DB']->SELECTquery('uid,title', 'tx_t3consultancies', $_where, '', 'title', '');
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tx_t3consultancies', $_where, '', 'title', '');
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            $selected=($row['uid']==$this->piVars['sponsor_id'])?'SELECTED':'';
            $list.="<OPTION value=\"$row[uid]\" $selected>$row[title]</OPTION>";
        }

        return $list;
    }

    /**
     * This function returns a SELECT form widget with the list of all available sponsors users
     */
    function sponsorUserSelect($sponsor_id='',$_selectedId='')
    {
        $list='';

        // NOTE: Only those users which have a non-zero sponsor_id figure around here
        // rest are lusers!
        if($sponsor_id!='')
        {
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,name', 'fe_users', $this->tablePrefix. 'sponsor_id="'. $this->piVars['sponsor_id'].'" '. t3lib_BEfunc::deleteClause('fe_users'), '', 'name', '');
        }
        else
        {
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,name', 'fe_users', $this->tablePrefix.'sponsor_id>0 '. t3lib_BEfunc::deleteClause('fe_users'), '', 'name', '');
        }

        if($GLOBALS['TYPO3_DB']->sql_num_rows($result)>0)
        {
            while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
            {
                $selected=($row[uid]==$_selectedId)?'selected':'';
                $list.="<OPTION value=\"$row[uid]\" $selected>$row[name]</OPTION>";
            }
        }

        return $list;
    }
    
    function validateMail($Email)
    {
        // all characters except @ and whitespace
        $name = '[^@\s]+';

        // letters, numbers, hyphens separated by a period
        $sub_domain = '[-a-z0-9]+\.';

        // country codes
        $cc = '[a-z]{2}';

        // top level domains
        $tlds = "$cc|com|net|edu|org|gov|mil|int|biz|pro|info|arpa|aero|coop|name|museum";

        $email_pattern = "/^$name@($sub_domain)+($tlds)$/ix";

        if ( preg_match($email_pattern, $Email, $check_pieces) )
        {
            // check mail exchange or DNS
            if ($check_mx)
            {
                $host = substr(strstr($check_pieces[0], '@'), 1);
                if ($debug) echo "<hr>".$host;
                if ($debug) echo "<hr>".$check_pieces[0];
                //Check DNS records
                if(checkdnsrr($host, "MX"))
                {
                    if(!getmxrr($host, $mxhost, $mxweight))
                    {
                        if ($debug) echo "Can't found records mail servers!";
                        return false;
                    }
                }
                else
                {
                    $mxhost[] = $host;
                    $mxweight[] = 1;
                }

                $weighted_host = array();
                for($i = 0; $i < count($mxhost); $i ++)
                {
                    $weighted_host[($mxweight[$i])] = $mxhost[$i];
                }
                ksort($weighted_host);

                foreach($weighted_host as $host)
                {
                    if ($debug) echo "<hr>".$host;
                    if(!($fp = fsockopen($host, 25, $errno, $errstr)))
                    {
                        if ($debug) echo "<hr>Can't connect to host: ".$host." $errstr ($errno)<br/>";
                        continue;
                    }

                    $stopTime = time() + 12;
                    $gotResponse = FALSE;
                    stream_set_blocking($fp, FALSE);

                    while(true)
                    {
                        $strresp = fgets($fp, 1024);
                        if(substr($strresp, 0, 3) == "220")
                        {
                            $stopTime = time() + 12;
                            $gotResponse = true;
                        }
                        elseif(($strresp == "") && ($gotResponse))
                        {
                            break;
                        }
                        elseif(time() > $stopTime)
                        {
                            break;
                        }
                    }
                    if(!$gotResponse)
                    {
                        continue;
                    }
                    stream_set_blocking($fp, true);

                    fputs($fp, "HELO {$_SERVER['SERVER_NAME']}\r\n");
                    fgets($fp, 1024);

                    fputs($fp, "MAIL FROM: <httpd@{$_SERVER['SERVER_NAME']}>\r\n");
                    fgets($fp, 1024);

                    fputs($fp, "RCPT TO: <$email>\r\n");
                    $line = fgets($fp, 1024);

                    fputs($fp, "QUIT\r\n");

                    fclose($fp);
                    if(substr($line, 0, 3) != "250")
                    {
                        $error = $line;
                        if ($debug) echo "<br/>Error".$error;
                        return false;
                    }
                    else return true;
                }
                if ($debug) echo "Error: Can't connect to mail server<br/><br/>";
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * This function returns the HTML text for the 'Create Sponsor' form
     */
    function createSponsor()
    {
        $content='';

        switch($this->piVars['formaction'])
        {

            case 'CreateSponsor':
            $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
            /** SANITY CHECKS **/
            // Basic validation left to Javscript
            $flag=0;         // Everyone starts sane... till education ruins them :-)
            $errorMsg='';
            // Simply checking whether the username doesn't already exist.
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title', 'tx_t3consultancies', 'title="' . $this->piVars['sponsor_name'] .'" AND pid="'. $data['sponsorsPID']. '" '. t3lib_BEfunc::deleteClause('tx_t3consultancies'));

            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if($row)
            {
                // User already exists!
                $flag=1;
                $errorMsg.='A sponsor with the same name already exists. Please choose a different sponsor name.<br>';
            }
            if($this->isUserRegistered($this->piVars['sponsor_user_username'])){
                $flag = 1;
                $errorMsg.='A user with the same name already exists. Please choose a different username.<br>';
            }
            
            // check if email address is valid
            $validate_result = $this->validateMail($this->piVars['sponsor_user_email']);
            if (!$validate_result) {
            	$flag = 1;
            	$errorMsg.='Invalid email address<br>';
            }

            // More sanity checks can come here

            if($flag)
            {
                $content.=$this->generateSponsorForm('create-error', $errorMsg);
            }
            else
            {

                //First insert the feuser details then get the inserted record id which in turn needs to
                //be inserted in the table tx_t3consultancies

                $insertFields=array(
                'name' => $this->piVars['sponsor_user_name'],
                'email' => $this->piVars['sponsor_user_email'],
                'username' => $this->piVars['sponsor_user_username'],
                'password' => $this->piVars['sponsor_user_password'],
                'telephone' => $this->piVars['sponsor_user_telephone'],
                'tstamp' => time(),
                'pid' => $data['sponsorUsersPID'],
                'usergroup'=>$data['sponsorGroupID']
                . ',' .$data['newSponsorGroupIDs']
                );

                $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertFields);
                $feuser_uid=$GLOBALS['TYPO3_DB']->sql_insert_id();

                // Create the user NOW!
                // Wait - first handle the logo upload so that we know the local filename of the logo
                $logoFile=$this->uploadFile($_FILES['logo'], $this->confData['logoDirectory']);
                $insertFields=array(
                'title' => $this->piVars['sponsor_name'],
                'description' => $this->piVars['sponsor_desc'],
                'contact_name' => $this->piVars['contact_name'],
                'contact_email' => $this->piVars['contact_email'],
                'url' => $this->piVars['website'],
                'cntry' => $this->piVars['country'],
                'tstamp' => time(),
                'crdate' => time(),
                $this->tablePrefix.'job_bank' => (isset($this->piVars['job_bank'])? 1:0),
                $this->tablePrefix.'sponsor_page' => (isset($this->piVars['sponsor_page'])? 1:0),
                'pid' => $data['sponsorsPID'],
                'tx_sponsorcontentscheduler_owner_id' => $feuser_uid,
                'fe_owner_user' => $GLOBALS['TSFE']->fe_user->user['uid'],
                'logo' => $logoFile
                );
                // echo $GLOBALS['TYPO3_DB']->INSERTquery('tx_t3consultancies', $insertFields);
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_t3consultancies', $insertFields);

                // Now that the spsonsor has been added, we need to add
                // its categories to the stupid MM table

                // get the UID of the inserted record!
                $uid_local=$GLOBALS['TYPO3_DB']->sql_insert_id();

                foreach($this->piVars['sponsor_categories'] as $uid_foreign)
                {
                    $insertFields=array(
                    'uid_local' => $uid_local,
                    'uid_foreign' => $uid_foreign,
                    );
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_t3consultancies_services_mm', $insertFields);
                }

                $this->sendMailToSponsorMasterUser($this->piVars['sponsor_user_email'],$this->piVars['sponsor_user_username']);
                
                $this->sendMailToSales($this->piVars['sponsor_user_username']);

                // Whew - that was a lot of hard work for creating a new sponsor!
                $content.=$this->generateMenu();
            }
            break;
            default:
            $content.=$this->generateSponsorForm('create');
            break;
        }
        return $content;
    }



    /**
     * This function returns the HTML form for editing the profile of a given sponsor
     * Some helper functions are used to render the form
     */
    function editSponsorProfile()
    {

        $content='';
        switch($this->piVars['formaction'])
        {
            case 'EditSponsorProfile':

            /** SANITY CHECKS **/
            // Basic validation left to Javscript
            $flag=0;         // Everyone starts sane... till education ruins them :-)
            $errorMsg='';

            // Update the sponsor information iff the sponsor uid corresponding to the username given in the form and the sponsor uid passed to us are the same!


            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title', 'tx_t3consultancies', 'uid!="' . $this->piVars['sponsor_id'] .'" ' . t3lib_BEfunc::deleteClause('tx_t3consultancies'));
            if($result){
                while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){

                    if(strcmp($row['title'], $this->piVars['sponsor_name'])==0){
                        // User already exists!
                        $flag=1;
                        $errorMsg.='A sponsor with the same name already exists. Please choose a different sponsor name.<br>';
                    }
                }
            }


            // More sanity checks can come here

            if($flag)
            {
                $content.=$this->generateSponsorForm('edit-error', $errorMsg);
            }
            else
            {
                // Update the user profile NOW!
                $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

                // Let's first handle the logo!
                $logoFile=$this->uploadFile($_FILES['logo'], $this->confData['logoDirectory']);

                if(strtoupper($this->cObj->data['select_key'])=='SALES'){
                    $insertFields=array(
                    'title' => $this->piVars['sponsor_name'],
                    'description' => $this->piVars['sponsor_desc'],
                    'contact_name' => $this->piVars['contact_name'],
                    'contact_email' => $this->piVars['contact_email'],
                    'url' => $this->piVars['website'],
                    'cntry' => $this->piVars['country'],
                    'tstamp' => time(),
                    $this->tablePrefix.'job_bank' => (isset($this->piVars['job_bank'])? 1:0),
                    $this->tablePrefix.'sponsor_page' => (isset($this->piVars['sponsor_page'])? 1:0),
                    'pid' => $data['sponsorsPID']
                    );

                }else{

                    $insertFields=array(
                    'title' => $this->piVars['sponsor_name'],
                    'description' => $this->piVars['sponsor_desc'],
                    'contact_name' => $this->piVars['contact_name'],
                    'contact_email' => $this->piVars['contact_email'],
                    'url' => $this->piVars['website'],
                    'cntry' => $this->piVars['country'],
                    'tstamp' => time(),
                    $this->tablePrefix.'job_bank' => (isset($this->piVars['job_bank'])? 1:0),
                    'pid' => $data['sponsorsPID']
                    );

                }


                if($logoFile!='')
                {
                    $insertFields['logo']=$logoFile;
                }

                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_t3consultancies', 'uid="'.$this->piVars['sponsor_id'].'"', $insertFields);


                if(strtoupper($this->cObj->data['select_key'])=='SALES'){

                    //Now get the tx_sponsorcontentscheduler_owner_id from tx_t3consultancies
                    $result_feuser_id=$GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_sponsorcontentscheduler_owner_id', 'tx_t3consultancies', 'uid="' . $this->piVars['sponsor_id'] .'" ' . t3lib_BEfunc::deleteClause('tx_t3consultancies'));
                    $row_feuser_id=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result_feuser_id);

                    $insertFields_feusers=array(
                    'name' => $this->piVars['sponsor_user_name'],
                    'email' => $this->piVars['sponsor_user_email'],
                    'username' => $this->piVars['sponsor_user_username'],
                    'password' => $this->piVars['sponsor_user_password'],
                    'telephone' => $this->piVars['sponsor_user_telephone'],
                    'tstamp' => time(),
                    );

                    //Now update the table fe_users  also

                    // MLC crate non-existent user
                    if ( $row_feuser_id['tx_sponsorcontentscheduler_owner_id'] )
                    {
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid="'.$row_feuser_id['tx_sponsorcontentscheduler_owner_id'].'"', $insertFields_feusers);
                    }

                    else
                    {
                        $insertFields=array(
                        'name' => $this->piVars['sponsor_user_name'],
                        'email' => $this->piVars['sponsor_user_email'],
                        'username' => $this->piVars['sponsor_user_username'],
                        'password' => $this->piVars['sponsor_user_password'],
                        'telephone' => $this->piVars['sponsor_user_telephone'],
                        'tx_sponsorcontentscheduler_sponsor_id' => $this->piVars['sponsor_id'],
                        'crdate' => time(),
                        'tstamp' => time(),
                        'pid' => $data['sponsorUsersPID'],
                        'usergroup'=>$data['sponsorGroupID']
                        . ',' .$data['newSponsorGroupIDs']
                        );

                        $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertFields);
                        $feuser_uid=$GLOBALS['TYPO3_DB']->sql_insert_id();

                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_t3consultancies', 'uid="'.$this->piVars['sponsor_id'].'"', array( 'tx_sponsorcontentscheduler_owner_id' => $feuser_uid));
                    }
                }

                // Now that the spsonsor has been updated, we need to update
                // its categories in the MM table

                // first of all delete all previous rows related to this sponsor
                $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_t3consultancies_services_mm', 'uid_local="'. $this->piVars['sponsor_id']. '"');

                // Now insert the categories into the table
                foreach($this->piVars['sponsor_categories'] as $uid_foreign)
                {
                    $insertFields=array(
                    'uid_local' => $this->piVars['sponsor_id'],
                    'uid_foreign' => $uid_foreign,
                    );
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_t3consultancies_services_mm', $insertFields);
                }


                // Whew - that was a lot of hard work for creating a new sponsor!
                $content.=$this->generateMenu();
            }

            break;

            default:

            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_t3consultancies', 'uid="' . $this->piVars['sponsor_id'] .'" '.t3lib_BEfunc::deleteClause('tx_t3consultancies'));
            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            //Now get the data from fe_users where uid = tx_sponsorcontentscheduler_owner_id

            $result_feusers=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid="' . $row['tx_sponsorcontentscheduler_owner_id'] .'" '.t3lib_BEfunc::deleteClause('fe_users'));
            $row_feusers=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result_feusers);

            $add=array(
            'sponsor_name' => $row['title'],
            'sponsor_desc' => $row['description'],
            'contact_name' => $row['contact_name'],
            'contact_email' => $row['contact_email'],
            'website' => $row['url'],
            'country' => $row['cntry'],
            'job_bank' => $row[$this->tablePrefix.'job_bank'],
            'sponsor_page' => $row[$this->tablePrefix.'sponsor_page'],
            'sponsor_user' => $row[$this->tablePrefix.'owner_id'],
            'logo' => $row['logo'],
            'sponsor_user_name' => $row_feusers['name'],
            'sponsor_user_email' => $row_feusers['email'],
            'sponsor_user_username' => $row_feusers['username'],
            'sponsor_user_password' => $row_feusers['password'],
            'sponsor_user_telephone' => $row_feusers['telephone'],
            'sponser_user_id' => $row['tx_sponsorcontentscheduler_owner_id'],
            'tstamp' => time(),
            );

            $this->piVars=array_merge($this->piVars, $add);

            $content.=$this->generateSponsorForm('edit');
            break;
        }

        $typolink_conf_main['parameter'] = $this->sponsorPageId;
        $typolink_conf_main['additionalParams']='';
        $content .=(($this->cObj->data['select_key'])=='SPONSOR')?$this->cObj->typoLink('<br/> Return to Main',$typolink_conf_main):'<br/><a href="'.t3lib_div::getIndpEnv('HTTP_REFERER').'">Return to Main</a>';

        return $content;
    }


    /**
     * This function generates a form for sponsor creation/editing
     */
    function generateSponsorForm($action, $errorMsg='')
    {
        $content.='';
        if (strtoupper($this->cObj->data['select_key'])=='SPONSOR') {
            $template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/sponsor/sponsor_profile_interface.tpl');
        }else{

            $template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/sales_interface.tpl');
        }

        $template=$this->cObj->getSubpart($template, '###TEMPLATE_CREATE_SPONSOR###');
        $markerArray=array(
        '###ERROR_MESSAGE###' => $errorMsg,
        '###JSVAL_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
        '###FORM_NAME###' => $this->formName('create_sponsor_form'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###HIDDEN_SPONSOR_ID###' => $this->formName('sponsor_id'),
        '###HIDDEN_SPONSOR_ID_VALUE###' => $this->piVars['sponsor_id'],
        '###INPUT_SPONSOR_NAME###' => $this->formName('sponsor_name'),
        '###INPUT_SPONSOR_NAME_VALUE###' => $this->piVars['sponsor_name'],
        '###INPUT_SPONSOR_DESC###' => $this->formName('sponsor_desc'),
        '###INPUT_SPONSOR_DESC_VALUE###' => $this->piVars['sponsor_desc'],

        '###INPUT_SPONSOR_CONTACT_NAME###' => $this->formName('contact_name'),
        '###INPUT_SPONSOR_CONTACT_NAME_VALUE###' => $this->piVars['contact_name'],

        '###INPUT_SPONSOR_CONTACT_EMAIL###' => $this->formName('contact_email'),
        '###INPUT_SPONSOR_CONTACT_EMAIL_VALUE###' => $this->piVars['contact_email'],
        '###INPUT_SPONSOR_EMAIL_VALUE###' => $this->piVars['contact_email'],

        '###INPUT_WEBSITE###' => $this->formName('website'),
        '###INPUT_WEBSITE_VALUE###' => $this->piVars['website'],
        '###MAX_LOGO_SIZE###' => '1073741824',  // Precise value of 1MB in Bytes
        '###INPUT_LOGO###' => 'logo',
        '###INPUT_LOGO_VALUE###' => $this->piVars['logo'],
        /** COOL NEW HACK I LEARNT - Add [] to the end of <select multiple> name so that
             * the PHP engine knows to accept multiple values :-)
             */ 
             '###SPONSOR_CATEGORIES###' => $this->formName('sponsor_categories').'[]',
             '###SPONSOR_CATEGORIES_VALUE###' => $this->sponsorCategoriesSelect(),
             '###COUNTRY###' => $this->formName('country'),
             '###COUNTRY_VALUE###' => $this->countrySelect(),

             '###INPUT_SPONSER_USER_NAME###' => $this->formName('sponsor_user_name'),
             '###INPUT_SPONSER_USER_NAME_VALUE###' => $this->piVars['sponsor_user_name'],

             '###INPUT_SPONSER_USER_EMAIL###' => $this->formName('sponsor_user_email'),
             '###INPUT_SPONSER_USER_EMAIL_VALUE###' => $this->piVars['sponsor_user_email'],

             '###INPUT_SPONSER_USER_USERNAME###' => $this->formName('sponsor_user_username'),
             '###INPUT_SPONSER_USER_USERNAME_VALUE###' => $this->piVars['sponsor_user_username'],

             '###INPUT_SPONSER_USER_PASSWORD###' => $this->formName('sponsor_user_password'),
             '###INPUT_SPONSER_USER_PASSWORD_VALUE###' => $this->piVars['sponsor_user_password'],

             '###INPUT_SPONSER_USER_TELEPHONE###' => $this->formName('sponsor_user_telephone'),
             '###INPUT_SPONSER_USER_TELEPHONE_VALUE###' => $this->piVars['sponsor_user_telephone'],

             '###CHECK_SPONSOR_PAGE###'     => $this->formName('sponsor_page'),
             '###CHECKED_SPONSOR_PAGE###'   => $this->piVars['sponsor_page'] == "1" ? 'checked="checked"' : '',
             '###CHECK_JOB_BANK###'         => $this->formName('job_bank'),
             '###CHECKED_JOB_BANK###'       => $this->piVars['job_bank'] == "1" ? 'checked="checked"' : ''
             );

             // DIRTY HACK ALERT

             switch($action)
             {

                 case 'create-error':
                 case 'create':
                 $markerArray['###HEADER###'] =(strtoupper($this->cObj->data['select_key'])!='SPONSOR')?'<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> '. $this->getPageLink('CREATE/EDIT SPONSOR','create_edit_sponsor_screen') .' >> CREATE':'<a href='.$this->pi_getPageLink($this->conf['sponsorLoginPageId']).'>MAIN PAGE</a> >> EDIT Sponsor Profle';
                 $markerArray['###SUBMIT_SPONSOR_FORM###']=$this->formName('create_sponsor');
                 $markerArray['###ACTION_NAME###']=$this->formName('action');
                 $markerArray['###ACTION_VALUE###']='create_sponsor';
                 $markerArray['###FORMACTION_NAME###']=$this->formName('formaction');
                 $markerArray['###FORMACTION_VALUE###']='CreateSponsor';
                 $markerArray['###LOGO_IMG###']='';
                 break;
                 case 'edit-error':
                 case 'edit':
                 $markerArray['###HEADER###'] = (strtoupper($this->cObj->data['select_key'])!='SPONSOR')?'<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> '. $this->getPageLink('CREATE/EDIT SPONSOR','create_edit_sponsor_screen') .' << EDIT Sponsor Profle':'<a href='.$this->pi_getPageLink($this->conf['sponsorLoginPageId']).'>MAIN PAGE</a> >> EDIT Sponsor Profle ';
                 $markerArray['###HEADER_SPONSOR###'] = '<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>Sponsor\'s Main Page </a> >> EDIT Sponsor Profle';
                 //$markerArray['###HEADER_SPONSOR###'] = '<a href="javascript:history.back(-1);"></a> >> '. $this->getPageLink('CREATE/EDIT SPONSOR','create_edit_sponsor_screen') .' >> EDIT';
                 $markerArray['###SPONSOR_USER_VALUE###'] = $this->sponsorUserSelect('',$this->piVars['sponsor_user']);
                 $markerArray['###SUBMIT_SPONSOR_FORM###']=$this->formName('edit_sponsor_profile');
                 $markerArray['###ACTION_NAME###']=$this->formName('action');
                 $markerArray['###ACTION_VALUE###']='edit_sponsor_profile';
                 $markerArray['###FORMACTION_NAME###']=$this->formName('formaction');
                 $markerArray['###FORMACTION_VALUE###']='EditSponsorProfile';
                 if($this->piVars['logo']=='')
                 {
                     $markerArray['###LOGO_IMG###']='Logo not uploaded. Upload the logo now:<br>';
                 }
                 else
                 {
                     $largeThumbsConf = array(
                        'width' => 150,
						'height' => 150,
						'newImgPath' => t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT').'/typo3temp/'.$this->piVars['logo']
						);
					 $imagePath = $this->confData['logoDirectory'].$this->piVars['logo'];
                     $imageFile = $this->transformImage( $imagePath, $largeThumbsConf);
                     $markerArray['###LOGO_IMG###']='<p><img src = "'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').'typo3temp/'.$this->piVars['logo'].'"></p>Upload new logo:';
                 }

                 break;
             }
             $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
             return $content;
    }



    /**
     * This function returns the HTML text for the 'Create Sponsor User' form
     */
    function createSponsorUser()
    {
        $content='';
        switch($this->piVars['formaction'])
        {
            case 'SuggestUsername':
            // If the Suggest Username & Password button was pressed then prepare an appropriate username
            // and password before the form is displayed
            /** TODO - fix this to handle a middle name as well! */
            $name=explode(' ', $this->piVars['first_name'], 3);
            $this->piVars['username']=$this->suggestUsername(strtolower($name[0]), strtolower($name[1]));
            $this->piVars['password']=$this->generateRandomPassword();
            $content=$this->generateSponsorUserCreationForm();
            break;


            case 'CreateSponsorUser':

            $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

            /** SANITY CHECKS **/
            // Basic validation left to Javscript
            $flag=0;         // Everyone starts sane... till education ruins them :-)
            $errorMsg='';

            // Simply checking whether the username doesn't already exist.
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username', 'fe_users', 'username="' . $this->piVars['username'] .'" AND pid="'. $data['sponsorUsersPID']. '" '. t3lib_BEfunc::deleteClause('fe_users'));
            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if($row)
            {
                // User already exists!
                $flag=1;
                $errorMsg.='A user with the same name already exists. Please choose a different username.<br>';
            }
            
            $validate_result = $this->validateMail($this->piVars['email']);
            if (!$validate_result) {
            	$flag = 1;
            	$errorMsg.= 'Invalid email address<br>';
            }

            // More sanity checks can come here

            if($flag)
            {
                $content.=$this->generateSponsorUserCreationForm($errorMsg);
            }
            else
            {
                $insertFields=array(
                'username' => trim($this->piVars['username']),
                'password' => trim($this->piVars['password']),
                'name' => trim($this->piVars['name']),
                'email' => trim($this->piVars['email']),
                'telephone' => trim($this->piVars['phone']),
                $this->tablePrefix.'sponsor_id' => $this->piVars['sponsor_id'],
                'tstamp' => time(),
                'crdate' => time(),
                'pid' => $data['sponsorUsersPID'],
                'usergroup'=>$data['sponsorGroupID']
                . ',' .$data['newSponsorGroupIDs']
                );

                $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertFields);
                $content=$this->generateMenu();
                
                $this->sendMailToSponsorMasterUser($this->piVars['email'],$this->piVars['username']);
                
                $this->sendMailToSales($this->piVars['username']);
            }
            break;


            default:
            $content=$this->generateSponsorUserCreationForm();
        }

        return $content;
    }


    /**
     * This function generates a form for sponsor user creation
     */
    function generateSponsorUserCreationForm($errorMsg='')
    {
        $template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey). 'templates/sales_interface.tpl');
        $template=$this->cObj->getSubpart($template, '###TEMPLATE_CREATE_SPONSOR_USER###');

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title', 'tx_t3consultancies', 'uid="' . $this->piVars['sponsor_id'] .'" ' . t3lib_BEfunc::deleteClause('tx_t3consultancies'));

        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

        $markerArray=array(
        '###JSVAL_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
        '###FORM_NAME###' => $this->formName('create_user_form'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ERROR_MESSAGE###' => $errorMsg,
        '###HEADER###' => '<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> '. $this->getPageLink('CREATE/EDIT SPONSOR USER','create_edit_sponsor_user_screen') .' >> CREATE',

        '###SPONSOR_SELECTOR_BOX###' => '<SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'">'. $this->sponsorSelect(). '</SELECT>',
        '###INPUT_NAME###' => $this->formName('name'),
        '###INPUT_NAME_VALUE###' => $this->piVars['name'],
        '###ID_NAME###' => $this->formName('name'),
        /** TODO - Currently, using first name as full name - change this later
             * if first name, last name distinction is necessary
             */
        //'###INPUT_LAST_NAME###' => $this->formName('last_name'),
        //'###INPUT_LAST_NAME_VALUE###' => $this->piVars['last_name'],
        //'###ID_LAST_NAME###' => $this->formName('last_name'),
        '###INPUT_EMAIL###' => $this->formName('email'),
        '###INPUT_EMAIL_VALUE###' => $this->piVars['email'],

        '###INPUT_USERNAME###' => $this->formName('username'),
        '###INPUT_USERNAME_VALUE###' => $this->piVars['username'],

        '###FORMACTION_NAME###' => $this->formName('formaction'),
        '###INPUT_PASSWORD###' => $this->formName('password'),
        '###INPUT_PASSWORD_VALUE###' => $this->piVars['password'],

        '###INPUT_PHONE###' => $this->formName('phone'),
        '###INPUT_PHONE_VALUE###' => $this->piVars['phone'],

        '###SPONSOR_ID_NAME###' => $this->formName('sponsor_id'),
        '###SPONSOR_ID###' => $this->piVars['sponsor_id'],

        '###SPONSOR_NAME###' => $row['title'],
        '###ACTION_NAME###' => $this->formName('action'),
        '###BACKLINK###' => $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[action]'=>'create_edit_sponsor_user_screen')),

        );
        $this->isValidSalesUser();

        switch($this->loginType){
            case 'SALES':
            $markerArray['###SPONSOR_SELECTOR_BOX###']='<SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'">'. $this->sponsorSelect(). '</SELECT>';
            break;
            case 'OWNER':
            $sponsorArr=$this->getSponserFromOwner();
            $markerArray['###SPONSOR_SELECTOR_BOX###']=$sponsorArr['title'].'<INPUT TYPE="HIDDEN" name="'. $this->formName('sponsor_id'). '" value="'. $sponsorArr['uid'] .'">';
            break;
        }
        $content=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
        return $content;
    }


    /**
     * This function returns the HTML text for the 'Edit Sponsor User' form
     */
    function editSponsorUser()
    {
        $content='';

        switch($this->piVars['formaction'])
        {
            case 'EditSponsorUser':
            $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
            /** SANITY CHECKS **/
            // Basic validation left to Javscript
            $flag=0;         // Everyone starts sane... till education ruins them :-)
            $errorMsg='';
            // Check whether the username that has been given exists or not
            // If it exists then the only case when it is acceptable is when the username has
            // not been changed :-)
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username', 'fe_users', 'username="' . $this->piVars['username'] .'" AND pid="'. $data['sponsorUsersPID']. '" '. t3lib_BEfunc::deleteClause('fe_users'));

            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if($row && $row['uid']!=$this->piVars['sponsor_user_id'])
            {
                // You can't take someone else's username STUPID!
                $flag=1;
                $errorMsg.='A user with the name "'. $this->piVars['username'] .'" already exists. Please choose a different username.<br>';
            }

            if($flag)
            {
                $content.=$this->generateSponsorUserEditingForm($errorMsg);
            }
            else
            {
                $insertFields=array(
                'name' => trim($this->piVars['name']),
                'email' => trim($this->piVars['email']),
                'username' => trim($this->piVars['username']),
                'password' => trim($this->piVars['password']),
                'telephone' => trim($this->piVars['phone']),
                'tstamp' => time(),
                );

                $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid="'.$this->piVars['sponsor_user_id'].'"', $insertFields);
                $content.=$this->generateMenu();
            }
            break;

            default:
            $content.=$this->generateSponsorUserEditingForm();
        }
        return $content;
    }

    /**
     * This function generates a form for sponsor user creation
     */
    function generateSponsorUserEditingForm($errorMsg='')
    {
        $content='';
        $template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/sales_interface.tpl');
        $template=$this->cObj->getSubpart($template, '###TEMPLATE_EDIT_SPONSOR_USER###');

        /** TODO - Fix this! */
        if($this->cObj->data['select_key']=='SALES'){
            $_whereCondition='fe_users.uid="' . $this->piVars['sponsor_user_id'].'"';
        }else{
            $_whereCondition='fe_users.uid="' . $GLOBALS['TSFE']->fe_user->user['uid'].'"';
        }
        $whereClause=$_whereCondition. ' AND fe_users.'. $this->tablePrefix. 'sponsor_id="' . $this->piVars['sponsor_id'].'" '. t3lib_BEfunc::deleteClause('fe_users');

        //echo $GLOBALS['TYPO3_DB']->SELECTquery('*','fe_users', $whereClause);

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users', $whereClause);
        //$result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_users.*,tx_t3consultancies.title', 'fe_users,tx_t3consultancies', $whereClause);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        $markerArray=array(
        '###FORM_NAME###' => $this->formName('edit_sponsor_user_form'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###FORMACTION_NAME###' => $this->formName('formaction'),
        '###HIDDEN_SPONSOR_USER_ID###' => $this->formName('sponsor_user_id'),
        '###HIDDEN_SPONSOR_USER_ID_VALUE###' => $this->piVars['sponsor_user_id'],

        '###HEADER###' => '<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >>'. $this->getPageLink('CREATE/EDIT SPONSOR USER','create_edit_sponsor_user_screen') .' >> EDIT',
        '###SPONSOR_NAME###' => $row['title'],
        '###ERROR_MESSAGE###' => $errorMsg,
        '###INPUT_NAME###' => $this->formName('name'),
        '###INPUT_NAME_VALUE###' => $row['name'],
        '###ID_NAME###' => $this->formName('name'),

        '###INPUT_EMAIL###' => $this->formName('email'),
        '###INPUT_EMAIL_VALUE###' => $row['email'],
        '###INPUT_USERNAME###' => $this->formName('username'),
        '###INPUT_USERNAME_VALUE###' => $row['username'],
        '###ID_USERNAME###' => $this->formName('username'),
        '###INPUT_PASSWORD###' => $this->formName('password'),
        '###INPUT_PASSWORD_VALUE###' => $row['password'],
        '###INPUT_PHONE###' => $this->formName('phone'),
        '###INPUT_PHONE_VALUE###' => $row['telephone'],
        '###SUBMIT_EDIT_SPONSOR_USER###' => $this->formName('edit_sponsor_user'),
        '###BACKLINK###' => $this->pi_getPageLink($GLOBALS['TSFE']->id,'',array($this->prefixId.'[action]'=>'create_edit_sponsor_user_screen')),
        '###ACTION_NAME###' => $this->formName('action')
        );
        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
        return $content;
    }

    /**
     * This function returns a SELECT form widget with the list of all available sponsor categories
     */
    function sponsorCategoriesSelect()
    {

        $list='';
        //echo $GLOBALS['TYPO3_DB']->SELECTquery('uid,title', 'tx_t3consultancies_cat', '1=1 '. t3lib_BEfunc::deleteClause('tx_t3consultancies_cat'), '', 'title', '');
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tx_t3consultancies_cat', '1=1 '. t3lib_BEfunc::deleteClause('tx_t3consultancies_cat'), '', 'title', '');
        //echo $GLOBALS['TYPO3_DB']->SELECTquery('uid_foreign', 'tx_t3consultancies_services_mm', 'uid_local="'. $this->piVars['sponsor_id']. '"');
        $result1=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign', 'tx_t3consultancies_services_mm', 'uid_local="'. $this->piVars['sponsor_id']. '"');
        $categories=array();
        while($row1=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result1))
        {
            $categories[]=$row1['uid_foreign'];
        }

        /** TODO - Figure out how this stupid function exec_SELECT_mm_query works! */
        /*$result2=$GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('uid_local,uid_foreign', 'tx_t3consultancies', 'tx_t3consultancies_services_mm', 'tx_t3consultancies_cat', 'uid_local="'. $this->piVars['sponsor_id']. '"');*/

        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            if(!empty($this->piVars['sponsor_categories'])){
                $selected=(in_array($row['uid'], $this->piVars['sponsor_categories']))?'SELECTED':'';
            }elseif (!empty($categories)){
                $selected=(in_array($row['uid'], $categories))?'SELECTED':'';
            }

            $list.="<OPTION value=\"$row[uid]\" $selected>$row[title]</OPTION>";
        }

        return $list;
    }

    /**
     * This function returns a SELECT form widget with the list of all available countries
     */
    function countrySelect()
    {
        $list='';

        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,cn_short_en', 'static_countries', '');

        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {

            if(!empty($this->piVars['country'])){

                $selected=($this->piVars['country']==$row['uid'])? 'SELECTED':'';
            }else{
                $selected=($row[cn_short_en]=='United States')? 'SELECTED':'';
            }

            $list.="<OPTION value=\"$row[uid]\" $selected>$row[cn_short_en]</OPTION>";
        }

        return $list;
    }

    /**
     * Given a firstname and lastname, this function returns an FE username which is does not currently exist
     */
    function suggestUsername($firstName, $lastName)
    {
        $possibleUsernames=array(
        $firstName,
        $lastName,
        "$firstName.$lastName",
        "$lastName.$firstName",
        "$firstName$lastName",
        "$lastName$firstName"
        );
        foreach($possibleUsernames as $username)
        {
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('username', 'fe_users', "username='$username' " . t3lib_BEfunc::deleteClause('fe_users'));
            $arr=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if(! $arr)
            return $username;
        }

        // If the function has not returned with a username by now
        // then start counting :-)
        $i=1;
        while(1)
        {
            $username="$firstName$i";
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('username', 'fe_users', "username='$username' " . t3lib_BEfunc::deleteClause('fe_users'));
            $arr=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            if(! $arr)
            return $username;
        }
    }


    /**
     * This function generates a random    passsword of 6 characters containing lower alphabets and numbers
     */
    function generateRandomPassword()
    {
        /** TODO - STUPID ALERT!! Pretty stupid way to generate a password! */
        $characters=array();
        for($i=0; $i<26; $i++)
        $characters[$i]=chr(97+$i);
        $numbers=array();
        for($i=0; $i<10; $i++)
        $numbers[$i]="$i";
        $rand_chars=array_rand($characters, 4);
        $rand_numbers=array_rand($numbers,2);
        $password=$characters[$rand_chars[0]].$characters[$rand_chars[1]].$numbers[$rand_numbers[0]].$characters[$rand_chars[2]].$characters[$rand_chars[3]].$numbers[$rand_numbers[1]];
        return $password;
    }


    /**
     * This function generates the form for adding/editing the sponsor inventory
     */
    function editInventory()
    {
        $content='';

        // Show page 3 only if the item is of the type 'Round Table'
        // Let's see what the item type is
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                pid, 
                tx_sponsorcontentscheduler_max_featured_weeks
            FROM
                tt_news
            WHERE
                uid = %s',
                $TYPO3_DB->fullQuoteStr($this->piVars['item_id'])));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        switch($this->piVars['formaction']){
            case 'AddItem':
                $content.=$this->createNewInventoryItem();
                break;
            case 'UpdateItems':
                $this->updateInventoryItems();
                $content = $this->generateInventoryList();
                break;
            case 'UpdateItemsFeatured':
                $this->updateInventoryItems();
                $content.=$this->generateInventoryList();
                break;            
            case 'CreateUser':
                $errMsg=$this->editInventoryItemUpdatePageOne();
                if($errMsg!='')
                    $content.=$this->editInventoryItemGeneratePageOne($errMsg);
                else
                    $content.=$this->editInventoryItemCreateUserForm();
                break;
            case 'SuggestUsername':
                // If the Suggest Username & Password button was pressed then prepare an appropriate username
                // and password before the form is displayed
                /** TODO - fix this to handle a middle name as well! */
                $name=explode(' ', $this->piVars['first_name'], 3);
                $this->piVars['username']=$this->suggestUsername(strtolower($name[0]), strtolower($name[1]));
                $this->piVars['password']=$this->generateRandomPassword();
                $content.=$this->editInventoryItemCreateUserForm();
                break;
            case 'CreateSponsorUser':
                $errMsg=$this->editInventoryItemCreateUser();
                if($errMsg!='')
                    $content.=$this->editInventoryItemCreateUserForm($errMsg);
                else
                    $content.=$this->editInventoryItemGeneratePageOne();
                break;
            case 'Page1':
                $content.=$this->editInventoryItemGeneratePageOne();
                break;
            case 'Page1Next':
                $errMsg=$this->editInventoryItemUpdatePageOne();
                if($errMsg!='')
                    $content.=$this->editInventoryItemGeneratePageOne($errMsg);
                else
                    $content.=$this->editInventoryItemGeneratePageTwo();
                break;
            case 'Page1NextDelFile':
                $this->deleteFileNews($this->piVars[item_id],$this->piVars[delFileName]);
                $content.=$this->editInventoryItemGeneratePageTwo();
                break;
            case 'Page2Next':
                $errMsg = $this->editInventoryItemUpdatePageTwo();
                if($errMsg != '')
                {
                    $content .= $this->editInventoryItemGeneratePageTwo($errMsg);
                    break;
                }

                // Show page 3 only if the item is of the type 'Round Table'
                if($row['pid'] == $this->confData['roundTablesPID'])
                {
                    $content .= $this->editInventoryItemGeneratePageThree();
                    break;
                }

                // show page four only if the person shall be able to select any weeks!
                if($row[$this->tablePrefix.'max_featured_weeks'] > 0)
                {
                    // editInventoryItemGeneratePageFour returns false if it
                    // didn't have anything to show despite of
                    // max_featured_weeks being > 0
                    // check this and show another page is this case
                    // this is a big hack but not bigger a hack than the rest
                    // of this file
                    $r = $this->editInventoryItemGeneratePageFour();
                    if ($r) {
                        $content .= $r;
                        break;
                    }
                }

                $content .= $this->editInventoryItemGeneratePageFive();
                break;
            case 'Page3Next':
                $errMsg=$this->editInventoryItemUpdatePageThree();
                if($errMsg!='')
                {
                    $content.=$this->editInventoryItemGeneratePageThree($errMsg);
                }
                else
                {
                    // show page four only if the person shall be able to select any weeks!
                    if($row[$this->tablePrefix.'max_featured_weeks']>0)
                    {
                        // editInventoryItemGeneratePageFour returns false if it
                        // didn't have anything to show despite of
                        // max_featured_weeks being > 0
                        // check this and show another page is this case
                        // this is a big hack but not bigger a hack than the rest
                        // of this file
                        $r = $this->editInventoryItemGeneratePageFour();
                        if ($r) {
                            $content .= $r;
                            break;
                        }
                    }
                    $content .= $this->editInventoryItemGeneratePageFive();
                }
                break;
            case 'Page4Next':
                $errMsg = $this->editInventoryItemUpdatePageFour();
                if($errMsg != '')
                {
                    $content .= $this->editInventoryItemGeneratePageFour($errMsg);
                }
                else
                {
                    $content .= $this->editInventoryItemGeneratePageFive();
                }
                break;
            case 'Page5Next':
                $content.=$this->generateInventoryList();
                break;
            case 'Page1Back':
                $content.=$this->generateInventoryList();
                break;
            case 'Page2Back':
                $content.=$this->editInventoryItemGeneratePageOne();
                break;
            case 'Page3Back':
                $content.=$this->editInventoryItemGeneratePageTwo();
                break;
            case 'Page4Back':
                // Show page 3 (round tables) only if the item is a round table
                if($row['pid']==$this->confData['roundTablesPID'])
                {
                    $content.=$this->editInventoryItemGeneratePageThree();
                }
                else
                {
                    $content.=$this->editInventoryItemGeneratePageTwo();
                }
                break;
            case 'Page5Back':
                // show page four only if the person shall be able to select any weeks!
                if($row[$this->tablePrefix.'max_featured_weeks'] > 0)
                {
                    // editInventoryItemGeneratePageFour returns false if it
                    // didn't have anything to show despite of
                    // max_featured_weeks being > 0
                    // check this and show another page is this case
                    // this is a big hack but not bigger a hack than the rest
                    // of this file
                    $r = $this->editInventoryItemGeneratePageFour();
                    if ($r) {
                        $content .= $r;
                        break;
                    }

                    if($row['pid'] == $this->confData['roundTablesPID'])
                    {
                        $content .= $this->editInventoryItemGeneratePageThree();
                        break;
                    }

                    $content .= $this->editInventoryItemGeneratePageTwo();
                    break;
                }

                // Show page three only if the item is a round table
                if($row['pid']==$this->confData['roundTablesPID'])
                {
                    $content.=$this->editInventoryItemGeneratePageThree();
                    break;
                }
                $content.=$this->editInventoryItemGeneratePageTwo();
                break;
            case 'SortBy':
            default:
            	$_SESSION['package_id']='';
                $content.=$this->generateInventoryList();
            break;
        }

        return $content;
    }


    /**
     *
     * This functions generates a form to create a user while editing an inventory item
     */
    function editInventoryItemCreateUserForm($errMsg=''){
        $template=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey). 'pi1/inventory_interface.tpl');
        $template=$this->cObj->getSubpart($template, '###TEMPLATE_CREATE_SPONSOR_USER###');
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title', 'tx_t3consultancies', 'uid="' . $this->piVars['sponsor_id'] .'" ' . t3lib_BEfunc::deleteClause('tx_t3consultancies'));
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

        $markerArray=array(
        '###JSVAL_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
        '###FORM_NAME###' => $this->formName('create_user_form'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ACTION_NAME###' => $this->formName('action'),
        '###FORMACTION_NAME###' => $this->formName('formaction'),
        '###ERROR_MESSAGE###' => $errMsg,
        '###INPUT_FIRST_NAME###' => $this->formName('first_name'),
        '###INPUT_FIRST_NAME_VALUE###' => $this->piVars['first_name'],
        '###ID_FIRST_NAME###' => $this->formName('first_name'),
        /** TODO - Currently, using first name as full name - change this later
                         * if first name, last name distinction is necessary
                         */
        //'###INPUT_LAST_NAME###' => $this->formName('last_name'),
        //'###INPUT_LAST_NAME_VALUE###' => $this->piVars['last_name'],
        //'###ID_LAST_NAME###' => $this->formName('last_name'),
        '###INPUT_EMAIL###' => $this->formName('email'),
        '###INPUT_EMAIL_VALUE###' => $this->piVars['email'],
        '###INPUT_USERNAME###' => $this->formName('username'),
        '###INPUT_USERNAME_VALUE###' => $this->piVars['username'],
        '###FORMACTION_NAME###' => $this->formName('formaction'),
        '###INPUT_PASSWORD###' => $this->formName('password'),
        '###INPUT_PASSWORD_VALUE###' => $this->piVars['password'],
        '###INPUT_PHONE###' => $this->formName('phone'),
        '###INPUT_PHONE_VALUE###' => $this->piVars['phone'],
        '###SUBMIT_SUGGEST_USERNAME###' => $this->formName('edit_inventory'),
        '###SUBMIT_CREATE_SPONSOR###' => $this->formName('edit_inventory'),
        '###SPONSOR_ID_NAME###' => $this->formName('sponsor_id'),
        '###SPONSOR_ID###' => $this->piVars['sponsor_id'],
        '###SPONSOR_NAME###' => $row['title'],
        '###ITEM_ID_NAME###' => $this->formName('item_id'),
        '###ITEM_ID###' => $this->piVars['item_id'],
        '###ACTION_NAME###' => $this->formName('action')
        );
        $content=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
        return $content;
    }

    /**
     * This function creates a new user while editing an inventory item
     */
    function editInventoryItemCreateUser()
    {
        /** SANITY CHECKS **/

        // Basic validation left to Javscript
        $flag=0;         // Everyone starts sane... till education ruins them :-)
        $errMsg='';

        // Simply checking whether the username doesn't already exist.
        $table='fe_users';
        $columns='uid,username';
        $where='username="' . $this->piVars['username'] .'" AND pid="'. $this->confData['sponsorUsersPID']. '" '. t3lib_BEfunc::deleteClause('fe_users');
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        if($row)
        {
            // User already exists!
            $flag=1;
            $errMsg.='A user with the same name already exists. Please choose a different username.<br>';
        }

        // More sanity checks can come here

        if($flag==0)
        {
            $insertFields=array(
            'username' => $this->piVars['username'],
            'password' => $this->piVars['password'],
            'name' => $this->piVars['first_name'],
            'email' => $this->piVars['email'],
            'telephone' => $this->piVars['phone'],
            $this->tablePrefix.'sponsor_id' => $this->piVars['sponsor_id'],
            'tstamp' => time(),
            'crdate' => time(),
            'usergroup'=>$this->getExtData['sponsorGroupID']
            . ',' .$this->getExtData['newSponsorGroupIDs']
            , 'pid' => $this->getExtData['sponsorUsersPID']
            );
            $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertFields);

        }

        return $errMsg;
    }


    /**
     * This function generates the inventory list for a sponsor
     */
    function generateInventoryList($package_name_new = null, $sponsor_user_id=null){
        // Send notification if we've just edited the item
        if ($this->piVars['formaction'] == 'Page5Next') {
            $this->sendMailItemEdit($this->piVars['item_id'], $this->piVars['sponsor_id']);
        }
        
        // The code below generates the entire Inventory editing table
        $content='';
        if (strtoupper($this->cObj->data['select_key'])=='SPONSOR') {
            $editTemplate.=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface_sponsor.tpl');
        }else{

            $editTemplate.=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface.tpl');
        }


        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tx_t3consultancies', 'uid="'.$this->piVars['sponsor_id'].'" '. t3lib_BEfunc::deleteClause('tx_t3consultancies'));
        
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

        $sponsorTitle=$row['title'];

        // Extract the header, substitute the markers, and output it as content
        $template=$this->cObj->getSubpart($editTemplate, '###INVENTORY_LISTING_HEADER###');
        //Following will be used if it in edit package mode

        //end code
        //echo "Package Id".$this->piVars['package_id'];
                if($this->piVars['package_id']!='')
                {
                	$_SESSION['package_id']=='';
                	$pack_id = $this->piVars['package_id'];
                }
                elseif($_SESSION['package_id']!='')
                {
                    $pack_id = $_SESSION['package_id'];
                }
        
        if($pack_id!='' ){
            $_packageInfo=$this->getPackageInfo($pack_id);
        }



        if ($package_name_new != null)
            $packagename = $package_name_new;
        else
            $packagename = $_packageInfo['title'];
        $markerArray=array(
        '###JSVAL_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
        '###JSCALENDAR_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/datetimepicker.js',

        '###FORM_NAME###' => 'edit_inventory',
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ACTION_NAME###' => $this->formName('action'),
        '###FORMACTION_NAME###' => $this->formName('formaction'),

        '###SPONSOR_NAME###' => $sponsorTitle,

        
        '###BREADCRUMB###' => '<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> '.  $this->getPageLink('CREATE/EDIT SPONSOR PACKAGE','create_edit_sponsor_package_screen') .' >> PACKAGE CREATE ["'.$packagename.'"]',
        '###BREADCRUMB_SPONSOR###' => '<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>Main Page</a> >>  Configure Package ["'.$packagename.'"]',

        '###HIDDEN_SPONSOR_ID###' => $this->formName('sponsor_id'),

        '###PACKAGE_NAME###' => $this->formName('package_name'),


        '###SPONSOR_USER_NAME###' => $this->formName('sponsor_user_id'),

        '###SUBMIT_ADD_ITEM###' => $this->formName('submit_add_item'),

        '###LEAD_DATES_LIST_POP###' => $this->featureDatesSelect('', 'sales'),
        '###SUBMIT_UPDATE###' => $this->formName('submit_update'),
        '###HIDDEN_SPONSOR_ID_VALUE###' => $this->piVars['sponsor_id'],
        '###ITEM_ID###' => $this->formName('item_id'),
        '###TITLE_HEADER_LINK###' => $this->pi_getPageLink($GLOBALS['TSFE']->id).'&'.$this->formName('action').'=edit_invetory&'. $this->formName('sort').'=title',
        '###TYPE_HEADER_LINK###' => '',
        '###ON_OFF_HEADER_LINK###' => '',
        '###FEATURE_WEEKS_HEADER_LINK###' => '',
        '###SORTBY###' => $this->formName('sortby')
        );
        if ($package_name_new!=null) {
            $markerArray['###PACKAGE_NAME_VALUE###'] = $package_name_new;
        }else{
            $markerArray['###PACKAGE_NAME_VALUE###'] = $_packageInfo['title'];
        }

        if ($sponsor_user_id!=null) {
            $markerArray['###SPONSOR_LIST###'] = $this->sponsorUserSelect($this->piVars['sponsor_id'],$sponsor_user_id);
        }else{
            $markerArray['###SPONSOR_LIST###'] = $this->sponsorUserSelect($this->piVars['sponsor_id'],$_packageInfo['fe_uid']);
        }

        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);


        // Extract the first row, substitute the markers, and output it as content
        $template=$this->cObj->getSubpart($editTemplate, '###INVENTORY_LISTING_FIRST_ROW###');
        $markerArray=array(
        '###FORMACTION_NAME###' => $this->formName('formaction'),
        '###SUBMIT_ADD_ITEM###' => $this->formName('submit_add_item'),
        '###INVENTORY_ITEM_TYPE###' => $this->formName('inventory_item_type_new'),
        '###INVENTORY_ITEM_TYPE_LIST###' => $this->inventoryItemTypeSelect(),
        '###CHECK_ON_OFF###' => $this->formName('check_on_off_new'),
        '###CHECK_ON_OFF_VALUE###' => 'checked',
        '###FEATURE_WEEKS###' => $this->formName('feature_weeks_new'). '[]',
        '###FEATURE_WEEKS_LIST###' => $this->featureDatesSelect('', 'sales'),
        '###INPUT_MAX_FEATURED_WEEKS###' => $this->formName('max_featured_weeks_new'),
        '###INPUT_MAX_FEATURED_WEEKS_VALUE###' => '0',
        '###CHECK_FEATUREWEEK_ON_OFF###' => $this->formName('feature_week_on_off_new'),
        '###CHECK_LEADS_ON_OFF###' => $this->formName('check_leads_on_off_new'),
        '###CHECK_LEADS_ON_OFF_VALUE###' => 'checked',
        '###LEAD_DATES###' => $this->formName('lead_periods_new'). '[]',
        '###LEAD_DATES_LIST###' => $this->leadPeriodsSelect()
        );
        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);

        // Extract all subsequent rows, substitute the markers, and output it as content
        $template=$this->cObj->getSubpart($editTemplate, '###INVENTORY_LISTING_ROW###');

        $_featuredWeekTemplate=$this->cObj->getSubpart($editTemplate, '###INVENTORY_PERMISSIBLE_LISTING_ROW###');

        $sortby = "title";
        if ($this->piVars['sortby'] != '')
            $sortby = $this->piVars['sortby'];
            
        if ((int)$this->piVars['package_id']>0 )
        {
			$_SESSION['package_id'] == "";
			$Pac_id = (int)$this->piVars['package_id'];
        }
		elseif ((int)$_SESSION['package_id']>0)
		{
			$Pac_id = (int)$_SESSION['package_id'];
		}
		
        global $TYPO3_DB;
        if ((int)$Pac_id>0){
        
        $result = $TYPO3_DB->sql_query(sprintf("
            SELECT
                *
            FROM
                tt_news
            WHERE
                tx_sponsorcontentscheduler_package_id = %s
                AND deleted = 0
            ORDER BY
                %s
            ",
            $TYPO3_DB->fullQuoteStr((int)$Pac_id),
            $sortby
        ));
//        	echo $Pac_id." <br>";
//			echo $GLOBALS['TYPO3_DB']->sql_affected_rows();
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            $_featuredWeek='';
            $_siteMapper=$this->conf['siteMapper'];
            $_arrSiteMapper=explode("|",$_siteMapper);
            $siteConfigureID=array();
            $_dueDate = '';
            foreach($_arrSiteMapper as $siteChunk)
            {
                $_arrInternalSiteMapper=explode(",",$siteChunk);
                $siteConfigureID[]=$_arrInternalSiteMapper[0];
                $_featuredWeek.=$this->generateFeaturedList($_featuredWeekTemplate,$_arrInternalSiteMapper[0],$row);
				//t3lib_div::devlog($_arrInternalSiteMapper[0], $_arrInternalSiteMapper[0]);
            }
                	//t3lib_div::devlog("FeaturedWeek", var_dump($_featuredWeek));
            if ($this->getTimeStampFromttNews($row['uid'])>0) {
                $_dueDate = $this->timeToString($this->getTimeStampFromttNews($row['uid']));
            }

            global $TYPO3_DB;
            $myres = $TYPO3_DB->sql_query(sprintf(
                'SELECT COUNT(1) AS unsent_leads FROM tx_newslead_leads WHERE news_id = %s AND leadsent = 0 AND hidden = 0 and deleted = 0',
                $TYPO3_DB->fullQuoteStr($row['uid'])));
            $myrow = $TYPO3_DB->sql_fetch_assoc($myres);
            $unsent_leads = $myrow['unsent_leads'];

            $markerArray = array(
                '###INVENTORY_ITEM_EDIT_LOCATION###'    => $this->pi_getPageLink($GLOBALS['TSFE']->id)
                                                           .'&'.$this->formName('action').'=edit_inventory'
                                                           .'&'.$this->formName('formaction').'=editItem'
                                                           .'&'.$this->formName('item_id').'='.$row['uid'],
                '###INVENTORY_ITEM_TITLE###'            => $row['title'] . ' ['.$row['uid']. ']' ,
                '###PACKAGE_ID###'                      => $this->formName('package_id'),
                '###PACKAGE_ID_VALUE###'                => $Pac_id,
                '###FORM_NAME###'                       => 'edit_inventory',
                '###ITEM_ID_VALUE###'                   => $row['uid'],
                '###INVENTORY_ITEM_TYPE###'             => $this->formName('inventory_item_type', $row['uid']),
                '###INVENTORY_ITEM_TYPE_LIST###'        => $this->inventoryItemTypeSelect($row['pid']),
                '###FETUREDWEEKS###'                    => $_featuredWeek,
                '###CHECK_ON_OFF###'                    => $this->formName('check_on_off', $row['uid']),
                '###CHECK_ON_OFF_VALUE###'              => ($row['hidden']==0)? 'CHECKED':'',
                '###FEATURE_WEEKS###'                   => $this->formName('feature_weeks', $row['uid']),
                '###PACKAGE_DUEDATE###'                 => $this->formName('due_date', $row['uid']). '[]',
                /** TODO - don't display the weeks during which an item of the same category
                  is being featured */
                '###OPTIONSITEMAPPER###'                => $this->generateSiteList($row['uid']),
                '###SITEMAPPER###'                      => $this->formName('publish_url', $row['uid']). '[]',
                '###FEATURE_WEEKS_LIST###'              => $this->featureDatesSelect($row['uid'], 'sales'),
                '###INPUT_MAX_FEATURED_WEEKS###'        => $this->formName('max_featured_weeks', $row['uid']),
                '###INPUT_MAX_FEATURED_WEEKS_VALUE###'  => $row[$this->tablePrefix. 'max_featured_weeks'],
                '###CHECK_LEADS_ON_OFF###'              => $this->formName('check_leads_on_off', $row['uid']),
                '###IMAGEFILEPATH###'                   => t3lib_extMgm::siteRelPath($this->extKey),
                '###CHECK_LEADS_ON_OFF_VALUE###'        => ($row['tx_newslead_leadon']==1)? 'CHECKED':'',
                '###LEAD_DATES###'                      => $this->formName('lead_periods', $row['uid']) . '[]',
                '###PACKAGE_DUEDATEVALUE###'            => $_dueDate==''?$this->timeToString(time()):$_dueDate,
                '###LEAD_DATES_LIST###'                 => $this->leadPeriodsSelect($row['uid']),
                '###LEADS_ADD_UNUSED###'                => $this->formName('unused_leads', $row['uid']),
                '###LEADS_UNUSED###'                    => $row['tx_sponsorcontentscheduler_unused_leads'],
                '###LEADS_UNSENT###'                    => $unsent_leads,
                '###LEADS_CURRENT_UNUSED###'            => $this->formName('current_unused_leads', $row['uid']),
            );
            $content .= $this->cObj->substituteMarkerArrayCached($template, $markerArray);
        }
        }

        // Extract the footer, substitute the markers, and output it as content
        $rightsInfo = explode(':',$_packageInfo['rights']);
        $checkedval = array('','checked');
        $template=$this->cObj->getSubpart($editTemplate, '###INVENTORY_LISTING_FOOTER###');
        $markerArray=array(
        '###SUBMIT_UPDATE###' => $this->formName('submit_update'),
        '###FORMACTION_NAME###' => $this->formName('formaction'),
        '###PREFIXID###' => $this->prefixId,
        '###PACKAGE_INFO_HIDDEN###' => $this->getPackageState($editTemplate),
        '###SELECTEDJOB###' => $checkedval[$rightsInfo[0]],
        '###SELECTEDBULLETIN###' => $checkedval[$rightsInfo[1]],
        '###SELECTEDEMAIL###' => $checkedval[$rightsInfo[2]],
        );
        
        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);

        return $content;
    }

    //Function to get Generate


    function getPackageState($editTemplate)
    {
        $packageInfo='';
        if($this->piVars['package_id']>0)
        {
            $templatePackageEdit=$this->cObj->getSubpart($editTemplate, '###INVENTORY_PACKAGE_ITEM###');

            $_markerArray_package=array(
            '###INVENTORY_PACKAGE_VALUE###'=>$this->piVars['package_id'],
            '###INVENTORY_PACKAGE_NAME###'=>$this->formName('package_id')
            );
            $packageInfo=$this->cObj->substituteMarkerArrayCached($templatePackageEdit, $_markerArray_package);
        }
        return $packageInfo;
    }
    function getPackageInfo($package_id)
    {
        $tablePackage=$this->tablePrefix. 'package';
        $fieldPackage="*";
        $where="uid=$package_id";
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($fieldPackage,$tablePackage,$where);
        if($res){
            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        }
        return $row;

    }

    function generateFeaturedList($templateName,$_featuredConf,$row)
    {
        $featuredDateArr=explode("|",$row[$this->tablePrefix. 'max_featured_weeks']);
        $featuredArrDateMax=array();
        foreach($featuredDateArr as $featuredVal)
        {
            $newArr=explode(",",$featuredVal);
            $featuredArrDateMax[$newArr[0]]=$newArr[1];
        }
        $_subPartArray['###FEATURE_WEEKS###']=$this->formName('feature_weeks', $row['uid']). "[$_featuredConf][]";
        $_subPartArray['###FEATURE_WEEKS_LIST###']=$this->featureDatesSelect($row['uid'], 'sales','',$_featuredConf);
        $_subPartArray['###INPUT_MAX_FEATURED_WEEKS###']= $this->formName('max_featured_weeks', $row['uid'])."[$_featuredConf]";
        $_subPartArray['###INPUT_MAX_FEATURED_WEEKS_VALUE###']=$featuredArrDateMax[$_featuredConf];
        $content=$this->cObj->substituteMarkerArrayCached($templateName, $_subPartArray);
        return $content;
    }

    function getTimeStampForNews($news_id){
        $table=$this->tablePrefix. 'featured_weeks_mm';
        $field="distinct(tstamp)";
        $where="$table.uid_local='$news_id'";
        //echo $GLOBALS['TYPO3_DB']->SELECTquery($field,$table,$where);
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res);
        return $row[0];
    }

    function getTimeStampFromttNews($news_id){
        $table='tt_news';
        $field="tx_sponsorcontentscheduler_news_due_date";
        $where="$table.uid='$news_id'";
        //echo $GLOBALS['TYPO3_DB']->SELECTquery($field,$table,$where);
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res);
        return $row[0];
    }


    /**
     * This function updates the types, on/off status, number of feature weeks,
     * feature weeks, leads on/off status, unused leads and lead dates of all
     * the items displayed in the inventory listing
     */
    function updateInventoryItems()
    {
        $content = '';
        $curTime = time();

        global $TYPO3_DB;
        
        // update / insert the package itself
        if ($this->piVars['package_name'] != '')
        {
            $rightsArr[] = ($this->piVars[rights][jobbank]         != '') ? '1' : '0';
            $rightsArr[] = ($this->piVars[rights][bulletinsponsor] != '') ? '1' : '0';
            $rightsArr[] = ($this->piVars[rights][emailblast]      != '') ? '1' : '0';
            $rights = implode(':', $rightsArr);
            
            if ($this->piVars['package_id'] != '')
            {
            	$_SESSION['package_id'] = '';
            	$package_id = $this->piVars['package_id'];
            }
            elseif ($_SESSION['package_id'] !='') 
            {
            	$package_id = $_SESSION['package_id'];
            }
            if ($package_id !='') {
               // $package_id = $this->piVars['package_id'];
                //update package name
                $updateFields = array(
                    'title'  => $this->piVars['package_name'],
                    'fe_uid' => $this->piVars['sponsor_user_id'],
                    'rights' => $rights,
                );
                $where = sprintf('uid = "%s"', $this->piVars['package_id']);
                $TYPO3_DB->exec_UPDATEquery("tx_sponsorcontentscheduler_package", $where, $updateFields);
            } else {
                $_fieldInsert = array(
                    'title'      => $this->piVars['package_name'],
                    'fe_uid'     => $this->piVars['sponsor_user_id'],
                    'sponsor_id' => $this->piVars['sponsor_id'],
                    'rights'     => $rights,
                    'pid'        => $this->pidPackagesSysFolder
                );
                $TYPO3_DB->exec_INSERTquery("tx_sponsorcontentscheduler_package", $_fieldInsert);
                $package_id = $TYPO3_DB->sql_insert_id();
            }
        }

        // cycle through all items in the package
        foreach ($this->piVars['inventory_item_type'] as $news_id => $inventory_item_type)
        {
            $updateFields = array(
                'pid'                => $inventory_item_type,
                'hidden'             => $this->piVars['check_on_off'][$news_id]       ? '0' : '1',
                'tx_newslead_leadon' => $this->piVars['check_leads_on_off'][$news_id] ? '1' : '0',
            );

            // Now we need to handle MM inserts for multiple feature weeks

            // First of all delete all the permitted feature weeks which are not in the past
            // DIRTY HACK - Can use a join/subquery here - but I don't know whether the TYPO3
            // API will be able to handle it!

            $feature_weeks     = $this->piVars['feature_weeks']     [$news_id];
            $feature_weeks_max = $this->piVars['max_featured_weeks'][$news_id];

            $result = $TYPO3_DB->exec_SELECTquery(
                // SELECT
                "tx_sponsorcontentscheduler_featured_weeks_mm.uid_local,
                 tx_sponsorcontentscheduler_featured_weeks_mm.uid_foreign",
                // FROM
                "tx_sponsorcontentscheduler_featured_weeks_mm,
                 tx_sponsorcontentscheduler_featured_weeks",
                // WHERE
                "tx_sponsorcontentscheduler_featured_weeks_mm.uid_local = '$news_id'
                 AND tx_sponsorcontentscheduler_featured_weeks_mm.uid_foreign = tx_sponsorcontentscheduler_featured_weeks.uid
                 AND tx_sponsorcontentscheduler_featured_weeks.starttime > $curTime");

            while ($row = $TYPO3_DB->sql_fetch_row($result))
            {
                list($uid_local, $uid_foreign) = $row;
                $TYPO3_DB->exec_DELETEquery(
                    // FROM
                    "tx_sponsorcontentscheduler_featured_weeks_mm",
                    // WHERE
                    "tx_sponsorcontentscheduler_featured_weeks_mm.uid_local = '$uid_local'
                     AND tx_sponsorcontentscheduler_featured_weeks_mm.uid_foreign = '$uid_foreign'");
            }


            // then insert all the permitted feature weeks which are in the future
            if ($feature_weeks)
            {
                foreach ($feature_weeks as $site_id => $siteArray)
                {
                    foreach ($siteArray as $week_id)
                    {
                        $insertFields = array(
                            'uid_local'   => $news_id,
                            'uid_foreign' => $week_id,
                            'site_id'     => $site_id,
                            'tstamp'      => $this->stringToTime($this->piVars['due_date'][$news_id][0])
                            /** TODO - sorting */
                        );
                        $TYPO3_DB->exec_INSERTquery("tx_sponsorcontentscheduler_featured_weeks_mm", $insertFields);
                    }
                }
            }

            $valFeaturedWeekMax = '';

            if($feature_weeks_max)
            {
                foreach($feature_weeks_max as $site_id_max => $week_id_max)
                {
                    $valFeaturedWeekMax .= "$site_id_max,$week_id_max|";
                }
            }
            $updateFields['tx_sponsorcontentscheduler_max_featured_weeks'] = $valFeaturedWeekMax;
            $updateFields['tx_sponsorcontentscheduler_package_id']         = $package_id;
            $updateFields['tx_sponsorcontentscheduler_news_due_date']      = $this->stringToTime($this->piVars['due_date'][$news_id][0]);
            $updateFields['tx_sponsorcontentscheduler_unused_leads']       = (int)$this->piVars['unused_leads'][$news_id] + (int)$this->piVars['current_unused_leads'][$news_id];

            $TYPO3_DB->exec_UPDATEquery("tt_news", "uid = $news_id", $updateFields);

            // Now we need to handle MM inserts for multiple lead periods

            // First of all delete all the assigned lead periods which are not in the past
            // DIRTY HACK - Can use a join/subquery here - but I don't know whether the TYPO3
            // API will be able to handle it!

            // Let's grab all those rows
            $result = $TYPO3_DB->exec_SELECTquery(
                // SELECT
                "tx_newslead_leadperiod.uid",
                // FROM
                "tx_newslead_leadperiod,
                 tt_news_tx_newslead_timeframes_mm",
                // WHERE
                "tt_news_tx_newslead_timeframes_mm.uid_local = '$news_id'
                 AND tt_news_tx_newslead_timeframes_mm.uid_foreign = tx_newslead_leadperiod.uid
                 AND tx_newslead_leadperiod.startdate > $curTime");
            $t = array();
            while($row = $TYPO3_DB->sql_fetch_assoc($result))
                $t[] = $row['uid'];

            if (count($t) > 0)
                $TYPO3_DB->exec_DELETEquery(
                    // FROM
                    "tt_news_tx_newslead_timeframes_mm",
                    // WHERE
                    sprintf('uid_local = "%s" AND uid_foreign IN (%s)', $news_id, implode(',', $t)));

            // then insert all the assigned feature weeks which are in the future
            $lead_periods = $this->piVars['lead_periods'][$news_id];
            if ($lead_periods)
            {
                foreach($lead_periods as $period_id)
                {
                    $insertFields = array(
                        'uid_local'   => $news_id,
                        'uid_foreign' => $period_id
                        /** TODO - sorting */
                    );
                    $TYPO3_DB->exec_INSERTquery("tt_news_tx_newslead_timeframes_mm", $insertFields);
                }
            }
            $this->sendLeads($news_id);
        }
        $this->sendMailPackageUpdated();
    }

    // THIS IS THE ORIGINAL FUNCTION
    // THERE IS A COPY IN news_lead/class.tx_newslead.php
    /**
     * Send outstanding leads.
     * Send means: set leadsent = 1 for it so that it shows up in the leads download
     * outstanding means: leads that have happened already but that the client
     *                    just now has paid for
     * This function should be called whenever the client buys leads.
     * It can probably also be called when a lead happens
     */
    function sendLeads($news_id)
    {
        if (!is_numeric($news_id))
            return;
        global $TYPO3_DB;

        // if unused_leads == 0: return
        $res = $TYPO3_DB->sql_query(sprintf(
            'SELECT tx_sponsorcontentscheduler_unused_leads FROM tt_news WHERE uid = %s',
            $TYPO3_DB->fullQuoteStr($news_id, '')));
        if ($TYPO3_DB->sql_num_rows($res) == 0)
            return;
        $row = $TYPO3_DB->sql_fetch_assoc($res);
        $unused_leads = $row['tx_sponsorcontentscheduler_unused_leads'];
        if ($unused_leads == 0)
            return;

        // if unsent_leads == 0: return
        $res = $TYPO3_DB->sql_query(sprintf(
            'SELECT COUNT(1) AS unsent_leads FROM tx_newslead_leads WHERE news_id = %s AND leadsent = 0 AND hidden = 0 and deleted = 0',
                $TYPO3_DB->fullQuoteStr($news_id)));
        $row = $TYPO3_DB->sql_fetch_assoc($res);
        $unsent_leads = $row['unsent_leads'];
        if ($unsent_leads == 0)
            return;

        $leads_to_send = min($unused_leads, $unsent_leads);

        // set leadsent = 1 for the leads_to_be_sent oldest leads
        $TYPO3_DB->sql_query(sprintf(
            'UPDATE tx_newslead_leads SET leadsent = 1 WHERE news_id = %s AND leadsent = 0 AND hidden = 0 AND deleted = 0 ORDER BY crdate LIMIT %d',
            $TYPO3_DB->fullQuoteStr($news_id), (int)$leads_to_send));

        // update unused_leads -= leads_to_be_sent
        $TYPO3_DB->sql_query(sprintf(
            'UPDATE tt_news SET tx_sponsorcontentscheduler_unused_leads = tx_sponsorcontentscheduler_unused_leads - %d WHERE uid = %s',
            (int)$leads_to_send, $TYPO3_DB->fullQuoteStr($news_id)));
    }

    /**
     * This functions generates a 'blank' inventory item with the feature dates, lead dates, etc. specified
     * by the sales personnel. The newly created 'blank' inventory item may then be edited to fill in content 
     * later.
     */
    function createNewInventoryItem()
    {
        $content='';
        //print_r($this->piVars['due_date']);
        /** SANITY CHECKS */
        // Yippee - no possible sanity checks here! Everything done by JavaScript, I guess
        $flag=0;

        if($flag)
        {
            // Re-generate the inventory listing indicating the error
        }
        else
        {
            if ((!empty($this->piVars['package_name'])) && (!empty($this->piVars['sponsor_user_id']))) {

            		
                $package_name = $this->piVars['package_name'];

                $sponsor_user_id = $this->piVars['sponsor_user_id'];

                $insertFields=array(
                // Inventory items of different types are stored in differnet pages
                // The PIDs are used to identify the inventory type
                'pid' => $this->piVars['inventory_item_type_new'],
                'tx_newssponsor_sponsor' => $this->piVars['sponsor_id'],
                'tx_newslead_leadon ' => isset($this->piVars['check_leads_on_off_new'])? '1':'0',
                $this->tablePrefix. 'max_featured_weeks' => $this->piVars['max_featured_weeks_new'],
                'title' => '[No Title]',
                'tstamp' => time(),
                'crdate' => time(),
                /** TODO - figure out what the column 'category' stores! */
                'hidden' => '1',// The newly created 'blank' item should be hidden right now!

                );
                
 // << YA; Create package; 
                
                if($this->piVars['package_id']!='')
                	$_SESSION['package_id']=='';
                //Check if inventory created during edit process of a packge
                if($this->piVars['package_id']!='')
                {
                    $insertFields[$this->tablePrefix.'package_id'] = $this->piVars['package_id'];
                    echo "PacID".$this->piVars['package_id']." ";
                }
                else 
                {
                	 if ($_SESSION['package_id']=='')
                	 {

                	        if ($this->piVars['package_name'] != '')
						        {
						            $rightsArr[] = ($this->piVars[rights][jobbank]         != '') ? '1' : '0';
						            $rightsArr[] = ($this->piVars[rights][bulletinsponsor] != '') ? '1' : '0';
						            $rightsArr[] = ($this->piVars[rights][emailblast]      != '') ? '1' : '0';
						            $rights = implode(':', $rightsArr);

					                	$_fieldInsert = array(
					                    'title'      => $this->piVars['package_name'],
					                    'fe_uid'     => $this->piVars['sponsor_user_id'],
					                    'sponsor_id' => $this->piVars['sponsor_id'],
					                    'rights'     => $rights,
					                    'pid'        => $this->pidPackagesSysFolder
					                	);
					                $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_sponsorcontentscheduler_package", $_fieldInsert);
					                $package_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
						        }
                $insertFields[$this->tablePrefix.'package_id'] = $package_id;
                $_SESSION['package_id'] = $package_id;
                	 }
                	 else 
                	 {
                	 	$insertFields[$this->tablePrefix.'package_id'] = $_SESSION['package_id'];
                	 }
                
// >> YA;
                }
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news', $insertFields);
                // Now handle the MM inserts for multiple feature weeks
                $news_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
                $table=$this->tablePrefix. 'featured_weeks_mm';
                if(is_array($this->piVars['feature_weeks_new'])){
                    foreach($this->piVars['feature_weeks_new'] as $week_id)
                    {
                        $insertFields=array(
                        'pid' => $this->featureWeeksPID,
                        'uid_local' => $news_id,
                        'uid_foreign' => $week_id,
                        'tstamp' => time(),
                        'crdate' => time()
                        /** TODO - might want to add the cruser_id as the ID of the fe_user */
                        );
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
                    }
                }
					
                $table='tt_news_tx_newslead_timeframes_mm';
                if(is_array($this->piVars['lead_periods_new'])){
                    foreach($this->piVars['lead_periods_new'] as $lead_id)
                    {
                        $insertFields=array(
                        'uid_local' => $news_id,
                        'uid_foreign' => $lead_id
                        );
                        $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFields);
                    }
                }

            }
            $content.=$this->generateInventoryList($package_name,$sponsor_user_id);

        }//End else
        return $content;
    }


    /**
     * This function returns the HTML code to generate the list for Inventory Item Types
     */
    function inventoryItemTypeSelect($itemType='')
    {
        $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $whitePapersPID=$data['whitePapersPID'];
        $roundTablesPID=$data['roundTablesPID'];
        $articlesPID=$data['articlesPID'];
        $presentationsPID=$data['presentationsPID'];
        $list='';
        if(strtoupper($this->cObj->data['select_key'])=='SALES'){
            $selected=($articlesPID==$itemType)? 'SELECTED' : '';
            $list.="<OPTION value=\"$articlesPID\" $selected>Article</OPTION>";

            $selected=($presentationsPID==$itemType)? 'SELECTED' : '';
            $list.="<OPTION value=\"$presentationsPID\" $selected>Presentation</OPTION>";

            $selected=($roundTablesPID==$itemType)? 'SELECTED' : '';
            $list.="<OPTION value=\"$roundTablesPID\" $selected>Round Table</OPTION>";

            $selected=($whitePapersPID==$itemType)? 'SELECTED' : '';
            $list.="<OPTION value=\"$whitePapersPID\" $selected>White Paper</OPTION>";
        }else{
            switch($this->piVars['inventory_type'])
            {
                case $whitePapersPID:
                $list.="<OPTION value=\"$whitePapersPID\" $selected>White Paper</OPTION>";
                break;
                case $roundTablesPID:
                $list.="<OPTION value=\"$roundTablesPID\" $selected>Round Table</OPTION>";
                break;
                case $articlesPID:
                $list.="<OPTION value=\"$articlesPID\" $selected>Aritcle</OPTION>";
                break;
                case $presentationsPID:
                $list.="<OPTION value=\"$presentationsPID\" $selected>Presentation</OPTION>";
                break;

            }
        }

        return $list;
    }

    function getInventoryItemType ($item = '')
    {
        $data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $whitePapersPID = $data['whitePapersPID'];
        $roundTablesPID = $data['roundTablesPID'];
        $articlesPID = $data['articlesPID'];
        $presentationsPID = $data['presentationsPID'];
        
        if (strtoupper($this->cObj->data['select_key']) == 'SALES') {
            switch ($item) {
            	case $whitePapersPID:
            		$itemType = 'White Paper';
            		break;
            	case $roundTablesPID:
            	    $itemType = 'Round Table';
            	    break;
            	case $articlesPID:
            	    $itemType = 'Article';
            	    break;
            	case $presentationsPID:
            	    $itemType = 'Presentation';
            }
        } else {
            switch($this->piVars['inventory_type']) {
                case $whitePapersPID:
            		$itemType = 'White Paper';
            		break;
            	case $roundTablesPID:
            	    $itemType = 'Round Table';
            	    break;
            	case $articlesPID:
            	    $itemType = 'Article';
            	    break;
            	case $presentationsPID:
            	    $itemType = 'Presentation';
            }
        }

        return $itemType;
    }

    /**
     * This function returns the HTML code to generate the list of feature dates.
     * The list of feature dates is picked off from tx_sponsor_content_scheduler_featured_weeks
     * @param UID the UID of the tt_news row
     * @param STRING should be 'sales' or '' 
     * @param TIME the starting date of the first week
     */
    function featureDatesSelect($news_id='', $mode='', $startDate='',$site_id='')
    {
        $curTime=time();
        $list='';
        $assignedWeeks=array();
        $startDate=$curTime;

        // First of all get all the feature weeks which have been assigned to the given tt_news item
        if($news_id!='')
        {
            $pageId=$this->getPageId($news_id);
            $relatedUid=$this->getRelatedUid($pageId);
            $table1=$this->tablePrefix. 'featured_weeks_mm';
            $table2=$this->tablePrefix. 'featured_weeks';
            $tables="$table1,$table2";
            $columns="$table1.uid_foreign,$table2.starttime,$table2.endtime";
            //$where='uid_local="'. $news_id .'" '. t3lib_BEfunc::deleteClause($table);
            if($site_id!='')
            $where_condition_enabled="$table1.site_id = $site_id and ";
            $where=$where_condition_enabled."$table1.uid_local='$news_id' AND $table1.uid_foreign=$table2.uid ". t3lib_BEfunc::deleteClause($table1) . t3lib_BEfunc::deleteClause($table2);
            $orderBy="$table2.starttime";
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $tables, $where, '', $orderBy);
            // Do the following voodoo only if the news item had been assigned feature weeks
            if($result){
                if($GLOBALS['TYPO3_DB']->sql_num_rows($result)>0)
                {
                    while($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($result))
                    {
                        $assignedWeeks[$row[0]]=$row[1];
                    }
                    reset($assignedWeeks);
                    $startDate=current($assignedWeeks);
                }
            }
        }

        if($startDate>$curTime)
        {
            $startDate=$curTime;
        }
        if($news_id != '' and $this->hasFeaturedWeek($news_id)>0)
        {
            $listExclude=$this->getRelatedUid($pageId,$news_id);
            $exludedList=$this->getExcludedFeaturedWeek($listExclude,$site_id);

        }
        else
        {
            $listExclude=$this->getRelatedUid($pageId);
            $exludedList=$this->getExcludedFeaturedWeek($listExclude);
        }
        if($exludedList!='')
        $where_condition="uid not in($exludedList) and ";


        $table=$this->tablePrefix. 'featured_weeks';
        $columns='uid,starttime,endtime,description';
        $where=$where_condition.'starttime>='.$startDate. t3lib_BEfunc::deleteClause($table);
        $orderBy='starttime';
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where, '', $orderBy);
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            // If we're generating this list for an existing item then
            // we need to think a bit more before discarding past weeks
            if($news_id!='')
            {
                $assigned=array_key_exists($row['uid'], $assignedWeeks);
                if($row['starttime']<$curTime && !$assigned)
                {
                    continue;
                }
                if($row['starttime']<$curTime && $assigned)
                {
                    $disabled='DISABLED';
                    $selected='SELECTED';
                }
                if($row['starttime']>$curTime && !$assigned)
                {
                    $disabled='';
                    $selected='';
                }
                if($row['starttime']>$curTime && $assigned)
                {
                    $disabled='';
                    $selected='SELECTED';
                }
            }
            //$selected='SELECTED';
            $list.='<OPTION value="'. $row['uid']. "\" $disabled $selected>". date('M d, y',$row['starttime']).'-'.date('M d, y',$row['endtime']). '</OPTION>';
            //$list.='<OPTION value="'. $row['uid']. "\" $disabled $selected>". $row['description']. '</OPTION>';
        }
        return $list;
    }

    function getPageId($pId)
    {
        $table='tt_news';
        $field="$table.pid";
        $where="$table.uid=$pId";
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        return $row['pid'];
    }

    function getRelatedUid($pageId,$uid='')
    {
        if ($pageId == '')
            return '';
        $table='tt_news';
        $field="$table.uid";
        $where="$table.pid=$pageId";
        if ($pageId == '')
            $this->print_backtrace();
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
        $listPID='';
        if($res){
            while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
            {
                if($uid!=$row['uid'])
                $listPID.=$row['uid'].",";
            }
            $listPID=substr($listPID,0,-1);
        }
        return $listPID;
    }

    function hasFeaturedWeek($news_id)
    {
        $tablenews=$this->tablePrefix."featured_weeks_mm";
        $field="count(*)";
        $where="$tablenews.uid_local=$news_id";
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$tablenews,$where);
        if($res){
            $row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res);
        }

        return $row[0];
    }

    function getExcludedFeaturedWeek($listExclude,$site_id='')
    {
        if ($listExclude == '')
            return '';
        $tablenews=$this->tablePrefix."featured_weeks_mm";
        $field="$tablenews.uid_foreign";
        if($site_id!='')
        $where_condition="site_id in($site_id) and ";
        $where=$where_condition."$tablenews.uid_local in($listExclude)";
        $listVal='';
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$tablenews,$where);
        if($res){
            while($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res))
            {
                $listVal.=$row[0].",";
            }
            $listVal=substr($listVal,0,-1);
        }
        return $listVal;
    }

    function generateSiteList($news_id='')
    {
        $arrListSite=explode("|",$this->conf["siteMapper"]);
        $listReturn='';

        foreach($arrListSite as $siteName)
        {

            $listSite=explode(",",$siteName);
            $table=$this->tablePrefix. 'featured_weeks_mm';
            $field="count(*)";
            $where="$table.uid_local=$news_id and site_id=".$listSite[0];
            $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
            if($res){
                $row=$GLOBALS['TYPO3_DB']->sql_fetch_row($res);
                if($row[0]>0)
                $listReturn.="<option value='".$listSite[0]."' selected>".$listSite[1]."</option>";
                else
                $listReturn.="<option value='".$listSite[0]."'>".$listSite[1]."</option>";
            }
        }
        return $listReturn;
    }
    /**
     * This function returns the HTML code to generate the list of *permissible* feature dates
     * @param UID the UID of the inventory item (tt_news)
     * @param STRING should be 'sales' or '' 
     * @param TIME the starting date of the first week
     */
    function permissibleFeatureDatesSelect($news_id, $mode = '', $startDate = '', $siteId = '', $siteName = '', $template = '', $maxFeaturedWeeks = '')
    {
        $list = '';
        $curTime = time();

        // Fetch all permissible weeks for the given inventory item
        if ($siteId != '' && $siteId > 0)
            $whereSiteId = " AND tx_sponsorcontentscheduler_featured_weeks_mm.site_id = '$siteId'";

        global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery(
            // SELECT
            "tx_sponsorcontentscheduler_featured_weeks.uid,
             tx_sponsorcontentscheduler_featured_weeks.description,
             tx_sponsorcontentscheduler_featured_weeks.starttime,
             tx_sponsorcontentscheduler_featured_weeks_mm.selected",
            // FROM
            "tx_sponsorcontentscheduler_featured_weeks_mm,
             tx_sponsorcontentscheduler_featured_weeks",
            // WHERE
            "    tx_sponsorcontentscheduler_featured_weeks_mm.uid_local = '$news_id'
             AND tx_sponsorcontentscheduler_featured_weeks_mm.uid_foreign = tx_sponsorcontentscheduler_featured_weeks.uid "
             . t3lib_BEfunc::deleteClause('tx_sponsorcontentscheduler_featured_weeks_mm')
             . t3lib_BEfunc::deleteClause('tx_sponsorcontentscheduler_featured_weeks')
             . $whereSiteId,
            // GROUP BY
            '',
            // ORDER BY
            "tx_sponsorcontentscheduler_featured_weeks.starttime");

        $permissibleWeeksUID         = array();
        $permissibleWeeksDescription = array();
        $permissibleWeeksStartTime   = array();
        $permissibleWeeksSelected    = array();
        while ($row = $TYPO3_DB->sql_fetch_row($result)) {
            $permissibleWeeksUID[]         = $row[0];
            $permissibleWeeksDescription[] = $row[1];
            $permissibleWeeksStartTime[]   = $row[2];
            $permissibleWeeksSelected[]    = $row[3];
        }
        // For all permissible weeks - fetch the number of items that have been already featured
        $item_type_id = $this->piVars['item_type_id'];
        $news_id = $this->piVars['item_id'];

        $permissibleWeeksFeaturedItemsNumber = array();

        if (count($permissibleWeeksUID) > 0) {
            $result = $TYPO3_DB->exec_SELECTquery(
                // SELECT
                "tx_sponsorcontentscheduler_featured_weeks.uid,
                 count(*)",
                // FROM
                "tx_sponsorcontentscheduler_featured_weeks,
                 tx_sponsorcontentscheduler_featured_weeks_mm,
                 tt_news",
                // WHERE
                "    tx_sponsorcontentscheduler_featured_weeks.uid IN (" . implode(',', $permissibleWeeksUID). ")
                 AND tx_sponsorcontentscheduler_featured_weeks.uid = tx_sponsorcontentscheduler_featured_weeks_mm.uid_foreign
                 AND tx_sponsorcontentscheduler_featured_weeks_mm.uid_local = tt_news.uid
                 AND tt_news.pid = '$item_type_id'
                 AND tt_news.uid != '$news_id' "
                 . t3lib_BEfunc::deleteClause("tx_sponsorcontentscheduler_featured_weeks")
                 . t3lib_BEfunc::deleteClause("tx_sponsorcontentscheduler_featured_weeks_mm")
                 . t3lib_BEfunc::deleteClause("tt_news"),
                // GROUP BY
                "tx_sponsorcontentscheduler_featured_weeks.uid");

            while($row = $TYPO3_DB->sql_fetch_row($result))
                $permissibleWeeksFeaturedItemsNumber[$row[0]] = $row[1];
        }

        // Figure out the maximum number of featured items for this category
        switch ($item_type_id) {
            case $this->confData['whitePapersPID']:
                $max_weeks = $this->confData['maxFeaturedWhitePapers'];
                break;
            case $this->confData['roundTablesPID']:
                $max_weeks = $this->confData['maxFeaturedRoundTables'];
                break;
            case $this->confData['articlesPID']:
                $max_weeks = $this->confData['maxFeaturedArticles'];
                break;
            case $this->confData['presentationsPID']:
                $max_weeks = $this->confData['maxFeaturedPresentations'];
                break;
            default:
                $max_weeks = 1;
        }

        for ($i = 0; $i < count($permissibleWeeksUID); $i++) {
            $disabled = '';
            $selected = '';

            if ($permissibleWeeksStartTime[$i] < $curTime)
                $disabled = 'DISABLED';
            else
                // If the week is not in the past - then we need to check whether we
                // already have the maximum number of featured items
                if ($permissibleWeeksFeaturedItemsNumber[$permissibleWeeksUID[$i]] >= $max_weeks)
                    $disabled = 'DISABLED';

            if ($permissibleWeeksSelected[$i] == '1')
                $selected = 'SELECTED';

            $list .= "<OPTION value='$permissibleWeeksUID[$i]' $disabled $selected>$permissibleWeeksDescription[$i]</OPTION>";
        }
        
        if ($siteId != '' && $siteId > 0) {
            if ($list != '') {
                $markerArray = array(
                    '###FEATURE_WEEKS###'         => $this->formName('featured_weeks').'['.$siteId.'][]',
                    '###SITENAME_FEATUREDWEEK###' => $siteName,
                    '###FEATURE_WEEKS_LIST###'    => $list,
                    '###MAXNOSELECTION###'        => $maxFeaturedWeeks[$siteId],
                );
                $content = $this->cObj->substituteMarkerArrayCached($template, $markerArray);
            }
            return $content;
        }

        return $list;
    }


    /**
     * This function returns the HTML code to generate the list of lead periods.
     * The list of lead periods is picked off from tx_newslead_leadperiod
     * @param UID the UID of the tt_news row
     * @param STRING should be 'sales' or '' 
     * @param TIME the starting date of the first week
     */
    function leadPeriodsSelect($news_id='', $mode='', $startDate='')
    {

        $curTime=time();
        $list='';
        $assignedWeeks=array();
        $startDate=$curTime;
        // First of all get all the lead periods which have been assigned the given tt_news item
        if($news_id!='')
        {
            $table1='tt_news_tx_newslead_timeframes_mm';
            $table2='tx_newslead_leadperiod';
            $tables="$table1,$table2";
            $columns="$table1.uid_foreign,$table2.startdate,$table2.enddate";
            $where='uid_local="'. $news_id .'" '. t3lib_BEfunc::deleteClause($table);
            $where="$table1.uid_local='$news_id' AND $table1.uid_foreign=$table2.uid ". t3lib_BEfunc::deleteClause($table1) . t3lib_BEfunc::deleteClause($table2);
            $orderBy=$table2.'.startdate';
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $tables, $where, '', $orderBy);

            // Do the following voodoo only if the news item had been assigned feature weeks
            if($GLOBALS['TYPO3_DB']->sql_num_rows($result)>0)
            {
                while($row=$GLOBALS['TYPO3_DB']->sql_fetch_row($result))
                {
                    $assignedWeeks[$row[0]]=$row[1];
                }
                // Even though we're expecting the array to be sorted - let's just sort it to be safe
                // Am I paranoid?
                asort($assignedWeeks);
                reset($assignedWeeks);
                $startDate=current($assignedWeeks);
            }
        }

        $table='tx_newslead_leadperiod';
        $columns='uid,startdate,enddate,description';
        $where='startdate>='.$startDate. t3lib_BEfunc::deleteClause($table);
        $orderBy='startdate';
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where, '', $orderBy);
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            // If we're generating this list for an existing item then
            // we need to think a bit more before discarding past weeks
            if($news_id!='')
            {
                $assigned=array_key_exists($row['uid'], $assignedWeeks);
                if($row['startdate']<$curTime && !$assigned)
                {
                    continue;
                }
                if($row['startdate']<$curTime && $assigned)
                {
                    $disabled='DISABLED';
                    $selected='SELECTED';
                }
                if($row['startdate']>$curTime && !$assigned)
                {
                    $disabled='';
                    $selected='';
                }
                if($row['startdate']>$curTime && $assigned)
                {
                    $disabled='';
                    $selected='SELECTED';
                }
            }
            $description_lead_period=date('M d, y',$row['startdate'])."-".date('M d, y',$row['enddate']);
            //$list.='<OPTION value="'. $row['uid']. "\" $disabled $selected>". $row['description']. '</OPTION>';
            $list.='<OPTION value="'. $row['uid']. "\" $disabled $selected>$description_lead_period</OPTION>";
        }
        return $list;
    }


    /**
     * Function for displaying the first page for updating an inventory item
     */
    function editInventoryItemGeneratePageOne($errMsg = '')
    {
        if (strtoupper($this->cObj->data['select_key']) == 'SPONSOR')
            $templateMain = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface_sponsor.tpl');
        else
            $templateMain = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface.tpl');
        $template = $this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_ONE###');

        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                n.*,
                p.title as package_title
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON (n.tx_sponsorcontentscheduler_package_id = p.uid)
            WHERE
                n.uid = %s
                AND n.deleted = 0',
                $TYPO3_DB->fullQuoteStr($this->piVars['item_id'])));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        //Built query string array to generate link to back button
        $backLinkQueryStringParameters = array();
        $backLinkQueryStringParameters[$this->formName('action')]     = 'edit_inventory';
        $backLinkQueryStringParameters[$this->formName('sponsor_id')] = $this->piVars['sponsor_id'];
        $backLinkQueryStringParameters[$this->formName('package_id')] = $this->piVars['package_id'];
        
        global $TSFE;
        $page_id = $TSFE->id;
        $markerArray = array(
            '###ACTION_NAME###'         => $this->formName('action'),
            '###AUTHOR_ID###'           => $this->formName('author_id'),
            '###AUTHOR_NAME###'         => $row['author'],
            '###CONTENT###'             => $this->formName('content'),
            '###CONTENT_VALUE###'       => $this->rteSafe($row['bodytext']),
            '###CREATE_USER###'         => $this->formName('create_user'),
            '###DATE###'                => $this->formName('date'),
// << YA; Added curent date/time; 04/2007
            '###DATE_VALUE###'          => $this->timeToString((trim($row['datetime'])==''||trim($row['datetime'])==0)?time():$row['datetime']),
// >> YA;
            '###FORMACTION_NAME###'     => $this->formName('formaction'),
            '###FORM_ACTION###'         => $this->pi_getPageLink($page_id),
            '###FORM_NAME###'           => $this->formName('edit_inventory_item_page_one'),
            '###IMG_CALENDAR###'        => "<img src='".t3lib_extMgm::siteRelPath($this->extKey)."images/cal.gif' border='0'>",
            '###ITEM_ID###'             => $this->formName('item_id'),
            '###ITEM_ID_VALUE###'       => $this->piVars['item_id'],
            '###ITEM_TYPE_ID###'        => $this->formName('item_type_id'),
            '###ITEM_TYPE_ID_VALUE###'  => $row['pid'],
            '###ITEM_TYPE###'           => $this->itemType[$row['pid']],
            '###JSCALENDAR_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/datetimepicker.js',
            '###JSVAL_LOCATION###'      => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
            '###PACKAGE_INFO_HIDDEN###' => $this->getPackageState($templateMain),
            '###PACKAGE_TITLE###'       => $row['package_title'],
            '###PAGE1_BACK_URL###'      => $this->pi_getPageLink($page_id, null, $backLinkQueryStringParameters),
            '###RTE_IMAGES_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/images/',
            '###RTE_LOCATION###'        => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/richtext_compressed.js',
            '###RTE_PATH###'            => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/',
            '###SPONSOR_ID###'          => $this->formName('sponsor_id'),
            '###SPONSOR_ID_VALUE###'    => $this->piVars['sponsor_id'],
            '###SUBMIT_BACK###'         => $this->formName('submit_back'),
            '###SUBMIT_NEXT###'         => $this->formName('submit_next'),
            '###TITLE###'               => $this->formName('title'),
            '###TITLE_VALUE###'         => $row['title']
        );
        return $this->cObj->substituteMarkerArrayCached($template, $markerArray);
    }


    /**
     * Function for updating the information recd from the first page for updating an inventory item
     */
    function editInventoryItemUpdatePageOne()
    {
        /** SANITY CHECKS */
        // No sanity checks as of now, everything done by javascript
        $flag=0;
        if($flag)
        {
            // re-generate Page1 with the error messages
        }
        else
        {
            //$author_details=$this->getOwnerAttributes($this->piVars['author_id']);
            $updateFields=array(
            'title' => $this->piVars['title'],
            'datetime' => $this->stringToTime($this->piVars['date']),
            //$this->tablePrefix.'author_id' => $this->piVars['author_id'],
            /** TODO - fix this! */
            'bodytext' => $this->piVars['content'],
            'author' => $this->piVars['author_id'],
            );
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_news', 'uid="'.$this->piVars['item_id'].'"', $updateFields);
        }
    }

    /**
     * Function for displaying the second page for updating an inventory item
     */
    function editInventoryItemGeneratePageTwo($errMsg='')
    {
        $content='';
        $templateMain=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface.tpl');
        $template=$this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_TWO###');
        $templateFILEUPLOAD=$this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_TWO_FILE_PART###');

        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                n.*,
                p.title as package_title
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON (n.tx_sponsorcontentscheduler_package_id = p.uid)
            WHERE
                n.uid = %s
                AND n.deleted = 0',
                $TYPO3_DB->fullQuoteStr($this->piVars['item_id'])));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        global $TSFE;
        $page_id = $TSFE->id;
        $backLinkQueryStringParameters = array();
        $backLinkQueryStringParameters[$this->formName('item_id')] = $this->piVars['item_id'];

        $markerArray = array(
            '###ACTION_NAME###'             => $this->formName('action'),
            '###FILE_UPLOAD_PLACEHOLDER###' => '',
            '###FORMACTION_NAME###'         => $this->formName('formaction'),
            '###FORM_ACTION###'             => $this->pi_getPageLink($page_id),
            '###FORM_NAME###'               => $this->formName('edit_inventory_item_page_two'),
            '###ITEM_CATEGORIES###'         => $this->formName('item_categories').'[]',
            '###ITEM_CATEGORIES_VALUE###'   => $this->itemCategoriesSelect($this->piVars['item_id']),
            '###ITEM_ID###'                 => $this->formName('item_id'),
            '###ITEM_ID_VALUE###'           => $this->piVars['item_id'],
            '###ITEM_TYPE_ID###'            => $this->formName('item_type_id'),
            '###ITEM_TYPE_ID_VALUE###'      => $row['pid'],
            '###ITEM_TYPE###'               => $this->itemType[$row['pid']],
            '###JSCALENDAR_LOCATION###'     => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/datetimepicker.js',
            '###JSVAL_LOCATION###'          => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
            '###MAX_FILE_SIZE###'           => '1073741824',
            '###PACKAGE_INFO_HIDDEN###'     => $this->getPackageState($templateMain),
            '###PACKAGE_TITLE###'           => $row['package_title'],
            '###PAGE2_BACK_URL###'          => $this->pi_getPageLink($page_id, null, $backLinkQueryStringParameters),
            '###RTE_IMAGES_LOCATION###'     => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/images/',
            '###RTE_LOCATION###'            => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/richtext_compressed.js',
            '###RTE_PATH###'                => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/',
            '###SPONSOR_ID###'              => $this->formName('sponsor_id'),
            '###SPONSOR_ID_VALUE###'        => $this->piVars['sponsor_id'],
            '###SUBMIT_BACK###'             => $this->formName('submit_back'),
            '###SUBMIT_NEXT###'             => $this->formName('submit_next'),
            '###TITLE_VALUE###'             => $row['title']
        );
        $table_lead_mm='tt_news_tx_newslead_timeframes_mm';
        $resultLead=$GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', $table_lead_mm, 'uid_local="'.$this->piVars['item_id'].'"');
        //Check : Whether to show file uploads or not
        $rowLead=$GLOBALS['TYPO3_DB']->sql_fetch_row($resultLead);
        $_FileCondition1=($rowLead[0]>0)?true:false;
        $_FileCondition2 = ($row['tx_newslead_leadon']>0)?true:false;
        $_additionalParams="&$this->prefixId[sponsor_id]=".$this->piVars[sponsor_id]."&$this->prefixId[formaction]=Page1NextDelFile&$this->prefixId[item_id]=".$this->piVars[item_id]."&$this->prefixId[action]=".$this->piVars[action];
        $typoLinkConfig=array(
        'parameter' =>$GLOBALS['TSFE']->id,
        );
        if($_FileCondition1 || $_FileCondition2){
            $relatedFiles=explode(',', $row['news_files']);

            if($relatedFiles[0]=='')
            {
                $markerArrayFile['###FILE1###']='<INPUT type="file" name="relatedFile1" required="0" /><br>';
            }
            else
            {
                $filename=$relatedFiles[0];
                $typoLinkConfig['additionalParams']=$_additionalParams."&$this->prefixId[delFileName]=$filename";
                $url=$this->confData['relatedFilesDirectory'] .  $filename;

                $markerArrayFile['###FILE1###']="<a href=\"$url\">$filename</a> ".$this->cObj->typoLink('Delete',$typoLinkConfig)."<BR>";
            }

            if($relatedFiles[1]=='')
            {
                $markerArrayFile['###FILE2###']='<INPUT type="file" name="relatedFile2" required="0" /> <br>';
            }
            else
            {
                $filename=$relatedFiles[1];
                $url=$this->confData['relatedFilesDirectory'] .  $filename;
                $typoLinkConfig['additionalParams']=$_additionalParams."&$this->extKey[delFileName]=$filename";
                $markerArrayFile['###FILE2###']="<a href=\"$url\">$filename</a> ".$this->cObj->typoLink('Delete',$typoLinkConfig)."<BR>";
            }

            if($relatedFiles[2]=='')
            {
                $markerArrayFile['###FILE3###']='<INPUT type="file" name="relatedFile3" required="0" /><br>';
            }
            else
            {
                $filename=$relatedFiles[2];
                $url=$this->confData['relatedFilesDirectory'] .  $filename;
                $typoLinkConfig['additionalParams']=$_additionalParams."&$this->extKey[delFileName]=$filename";
                $markerArrayFile['###FILE3###']="<a href=\"$url\">$filename</a> ".$this->cObj->typoLink('Delete',$typoLinkConfig)."<BR>";
            }
            $markerArrayFile['###MAX_FILE_SIZE###']='1073741824';
            $markerArray['###FILE_UPLOAD_PLACEHOLDER###']=$this->cObj->substituteMarkerArrayCached($templateFILEUPLOAD, $markerArrayFile);

        }
        //Condition Ends
        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
        return $content;
    }

    /**
     * Function for updating the information recd from the second page for updating an inventory item
     */
    function editInventoryItemUpdatePageTwo()
    {
    	//echo "editInventoryItemUpdatePageTwo()";
        /** SANITY CHECKS */
        // No sanity checks as of now, everything done by javascript
        $flag=0;
        if($flag)
        {
            // re-generate Page1 with the error messages
        }
        else
        {
            // First delete all old item categories
            $GLOBALS['TYPO3_DB']->exec_DELETEquery('tt_news_cat_mm', 'uid_local="'. $this->piVars['item_id']. '"');
            // Now insert all the new categories
            $insertFields=array(
            'uid_local' => $this->piVars['item_id'],
            'uid_foreign' => ''
            );
            foreach($this->piVars['item_categories'] as $uid_foreign)
            {
                $insertFields['uid_foreign']=$uid_foreign;
                $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_news_cat_mm', $insertFields);
            }

            
            
            
            // Now handling the file uploads
            $filename=array();
            foreach($_FILES as $file)
            {
                $t=$this->uploadFile($file, $this->confData['relatedFilesDirectory']);
                if($t!='')
                {
                    $filename[]=$t;
                }
            }

            // Get the files uploaded earlier
            $table='tt_news';
            $columns='news_files';
            $where='uid="'. $this->piVars['item_id']. '"';
            $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
            $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
            $filename[]=$row['news_files'];
            $relatedFiles=implode(',', $filename);
            if($relatedFiles!='')
            {
                $table='tt_news';
                $where='uid="'. $this->piVars['item_id']. '"';
                $updateFields=array(
                'news_files' => $relatedFiles
                );
                $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
            }
        }
    }

    /**
     * Function for displaying the third page for an inventory item
     * this page is displayed only when the item is of the type 'Round Table'
     */
    function editInventoryItemGeneratePageThree($errMsg='')
    {
        $templateMain=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface.tpl');
        $template=$this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_THREE###');

        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                n.*,
                p.title as package_title
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON (n.tx_sponsorcontentscheduler_package_id = p.uid)
            WHERE
                n.uid = %s
                AND n.deleted = 0',
                $TYPO3_DB->fullQuoteStr($this->piVars['item_id'])));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        $markerArray = array(
            '###ACTION_NAME###'             => $this->formName('action'),
            '###AFTER_CONTENT###'           => $this->formName('after_content'),
            '###AFTER_CONTENT_VALUE###'     => $this->rteSafe($row['tx_newseventregister_followupmessage']),
            '###BEFORE_CONTENT###'          => $this->formName('before_content'),
            '###BEFORE_CONTENT_VALUE###'    => $this->rteSafe($row['tx_newseventregister_eventinformation']),
            '###END_DATE###'                => $this->formName('end_date'),
            '###END_DATE_VALUE###'          => $this->timeToString($row['tx_newseventregister_enddateandtime']),
            '###FORMACTION_NAME###'         => $this->formName('formaction'),
            '###FORM_ACTION###'             => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            '###FORM_NAME###'               => $this->formName('edit_inventory_item_page_three'),
            '###ITEM_ID###'                 => $this->formName('item_id'),
            '###ITEM_ID_VALUE###'           => $this->piVars['item_id'],
            '###ITEM_TYPE_ID###'            => $this->formName('item_type_id'),
            '###ITEM_TYPE_ID_VALUE###'      => $row['pid'],
            '###ITEM_TYPE###'               => $this->itemType[$row['pid']],
            '###JSCALENDAR_LOCATION###'     => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/datetimepicker.js',
            '###JSVAL_LOCATION###'          => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
            '###PACKAGE_INFO_HIDDEN###'     => $this->getPackageState($templateMain),
            '###PACKAGE_TITLE###'           => $row['package_title'],
            '###RTE_IMAGES_LOCATION###'     => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/images/',
            '###RTE_LOCATION###'            => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/richtext_compressed.js',
            '###RTE_PATH###'                => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/',
            '###SPONSOR_ID###'              => $this->formName('sponsor_id'),
            '###SPONSOR_ID_VALUE###'        => $this->piVars['sponsor_id'],
            '###START_DATE###'              => $this->formName('start_date'),
            '###START_DATE_VALUE###'        => $this->timeToString($row['tx_newseventregister_startdateandtime']),
            '###SUBMIT_BACK###'             => $this->formName('submit_back'),
            '###SUBMIT_NEXT###'             => $this->formName('submit_next'),
            '###TITLE###'                   => $row['title']
        );
        return $this->cObj->substituteMarkerArrayCached($template, $markerArray);
    }

    /**
     * Function for updating the information recd from the third page for updating an inventory item
     */
    function editInventoryItemUpdatePageThree()
    {
        /** SANITY CHECKS */
        // No sanity checks as of now, everything done by javascript
        $flag=0;
        if($flag)
        {
            // re-generate Page1 with the error messages
        }
        else
        {
            $table='tt_news';
            $where='uid="'. $this->piVars['item_id'] .'"';
            $updateFields=array(
            'tx_newseventregister_startdateandtime' => $this->stringToTime($this->piVars['start_date']),
            'tx_newseventregister_enddateandtime' => $this->stringToTime($this->piVars['end_date']),
            'tx_newseventregister_eventinformation' => $this->piVars['before_content'],
            'tx_newseventregister_followupmessage' => $this->piVars['after_content']
            );
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $updateFields);
        }
    }




    /**
     * Function for displaying the fourth page for an inventory item
     */
    function editInventoryItemGeneratePageFour($errMsg='')
    {

        $content='';
        $_siteArray=$this->getSiteStats();

        $templateMain=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface.tpl');
        $template=$this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_FOUR###');
        $templateData = $this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGEFOUR_DATA###');
        $templateFeatured=$this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_FOUR_WEEK_SELECTOR###');

        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                n.*,
                p.title as package_title
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON (n.tx_sponsorcontentscheduler_package_id = p.uid)
            WHERE
                n.uid = %s
                AND n.deleted = 0',
                $TYPO3_DB->fullQuoteStr($this->piVars['item_id'])));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        $markerArray = array(
            '###ACTION_NAME###'         => $this->formName('action'),
            //'###FEATURED_WEEKS_MAX_NUMBER###' => $row[$this->tablePrefix. 'max_featured_weeks'],
            //'###FEATURED_WEEKS_MAX_NUMBER###' => $this->getProcessFeaturedWeek($row[$this->tablePrefix. 'max_featured_weeks']),
            //'###FEATURE_WEEKS_LIST###' => $this->permissibleFeatureDatesSelect($this->piVars['item_id']),
            //'###FEATURE_WEEKS###'     => $this->formName('featured_weeks').'[]',
            '###FORMACTION_NAME###'     => $this->formName('formaction'),
            '###FORM_ACTION###'         => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            '###FORM_NAME###'           => $this->formName('edit_inventory_item_page_four'),
            '###ITEM_ID###'             => $this->formName('item_id'),
            '###ITEM_ID_VALUE###'       => $this->piVars['item_id'],
            '###ITEM_TYPE_ID###'        => $this->formName('item_type_id'),
            '###ITEM_TYPE_ID_VALUE###'  => $row['pid'],
            //'###ITEM_TYPE###'           => $this->itemType[$row['pid']],
            '###ITEM_TYPE_REAL_VALUE###' => $this->itemType[$row['pid']],
            '###JSCALENDAR_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/datetimepicker.js',
            '###JSVAL_LOCATION###'      => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/jsval.js',
            '###PACKAGE_INFO_HIDDEN###' => $this->getPackageState($templateMain),
            '###PACKAGE_TITLE###'       => $row['package_title'],
            '###RTE_IMAGES_LOCATION###' => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/images/',
            '###RTE_LOCATION###'        => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/richtext_compressed.js',
            '###RTE_PATH###'            => t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/',
            '###SPONSOR_ID###'          => $this->formName('sponsor_id'),
            '###SPONSOR_ID_VALUE###'    => $this->piVars['sponsor_id'],
            '###SUBMIT_BACK###'         => $this->formName('submit_back'),
            '###SUBMIT_NEXT###'         => $this->formName('submit_next'),
            '###TITLE_VALUE###'         => $row['title'],
        );
        
        $FeaturedWeeks='';
        $_maxFeaturedWeeks=$this->getProcessFeaturedWeek($row[$this->tablePrefix. 'max_featured_weeks'],2);
        foreach($_siteArray as $siteId=>$siteName)
        {
            $FeaturedWeeks.=$this->permissibleFeatureDatesSelect($this->piVars['item_id'],'','',$siteId,$siteName,$templateFeatured,$_maxFeaturedWeeks);
        }
        $data='';
        if($FeaturedWeeks!=''){
            $markerArraySub['###TITLE_VALUE###']=$row['title'];
            $markerArraySub['###FEATURED_WEEKS_MAX_NUMBER###']=$this->getProcessFeaturedWeek($row[$this->tablePrefix. 'max_featured_weeks']);
            $markerArraySub['###FEATUREDLISTDISPLAY###']=$FeaturedWeeks;
            $data = $this->cObj->substituteMarkerArrayCached($templateData, $markerArraySub);
        }
        
//        $markerArray['###FEATUREDLISTDISPLAY###']=$FeaturedWeeks;
        $markerArraySub['###EDIT_INVENTORY_ITEM_PAGEFOUR_DATA###']=$data;
        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray,$markerArraySub);

        // Ich liebe Hacks!
        if ($data == '')
            return false;
        return $content;
    }

    /**
     * Function for updating the information recd from the fourth page for updating an inventory item
     */
    function editInventoryItemUpdatePageFour()
    {
        /** SANITY CHECKS */
        // No sanity checks as of now, everything done by javascript
        $flag=0;
        if($flag)
        {
            // re-generate Page1 with the error messages
        }
        else
        {
            $curTime=time();
            $table1=$this->tablePrefix.'featured_weeks_mm';
            $table2=$this->tablePrefix.'featured_weeks';
            $tables="$table1,$table2";
            $item_id=$this->piVars['item_id']; // UID if the tt_news item

            // First if all unselect all those feature weeks which are in the future
            $updateFields=array(
            'selected' => '0'
            );
            $where="$table2.starttime>$curTime AND $table2.uid=$table1.uid_foreign AND $table1.uid_local='$item_id'";
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($tables, $where, $updateFields);

            // Now select all those feature weeks which the user has chosen right now
            $updateFields=array(
            "$table1.selected" => '1'
            );
            $news_id=$this->piVars['item_id'];
            if(is_array($this->piVars['featured_weeks']))
            {
                foreach($this->piVars['featured_weeks'] as $siteId=>$weekArr)
                {
                    foreach ($weekArr as $weekKey=>$week_id)
                    {
                        // Making sure that past is not changed at any cost!
                        $where="$table1.uid_local='$news_id' AND $table1.uid_foreign='$week_id' AND $table1.uid_foreign=$table2.uid AND $table2.starttime>$curTime and $table1.site_id=$siteId";
                        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($tables, $where, $updateFields);
                    }
                }
            }
        }
    }



    /**
     * Function for displaying the fifth page for an inventory item (review inventory item)
     */
    function editInventoryItemGeneratePageFive()
    {
        $content='';
        $templateMain=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/inventory_interface.tpl');
        $template=$this->cObj->getSubpart($templateMain, '###EDIT_INVENTORY_ITEM_PAGE_FIVE###');
        $item_id=$this->piVars['item_id'];
        $sponsor_id=$this->piVars['sponsor_id'];

        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                n.*,
                p.title as package_title
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON (n.tx_sponsorcontentscheduler_package_id = p.uid)
            WHERE
                n.uid = %s',
                $TYPO3_DB->fullQuoteStr($this->piVars['item_id'])));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        $table='tx_t3consultancies';
        $columns='title';
        $where="uid='$sponsor_id' ". t3lib_BEfunc::deleteClause($table);
        $result2=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        $row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2);  
        
        
        $markerArray = array(
            '###ACTION_NAME###'         => $this->formName('action'),
            '###AUTHOR###'              => $row['author'],
            '###CONTENT###'             => $row['bodytext'],
            '###DATE###'                => $this->timeToString($row['datetime']),
            '###FORMACTION_NAME###'     => $this->formName('formaction'),
            '###FORM_ACTION###'         => $this->pi_getPageLink($GLOBALS['TSFE']->id),
            '###FORM_NAME###'           => $this->formName('edit_inventory_item_page_five'),
            '###ITEM_ID###'             => $this->formName('item_id'),
            '###ITEM_ID_VALUE###'       => $this->piVars['item_id'],
            '###ITEM_TYPE_ID###'        => $this->formName('item_type_id'),
            '###ITEM_TYPE_ID_VALUE###'  => $row['pid'],
            '###ITEM_TYPE###'           => $this->itemType[$row['pid']],
            '###LEADS###'               => ($row['tx_newslead_leadon']=='1')?'Yes':'No',
            '###PACKAGE_INFO_HIDDEN###' => $this->getPackageState($templateMain),
            '###PACKAGE_TITLE###'       => $row['package_title'],
            '###SPONSOR_ID###'          => $this->formName('sponsor_id'),
            '###SPONSOR_ID_VALUE###'    => $this->piVars['sponsor_id'],
            '###SPONSOR###'             => $row2['title'],
            '###START_DATE###'          => $this->timeToString($row['tx_newseventregister_startdateandtime']),
            '###SUBMIT_BACK###'         => $this->formName('submit_back'),
            '###SUBMIT_NEXT###'         => $this->formName('submit_next'),
            '###TITLE###'               => $row['title']
        );


        if($row['news_files']!='')
        {
            $relatedFiles=explode(',', $row['news_files']);
            $rf='';
            if($relatedFiles[0]!='')
            {
                $filename=$relatedFiles[0];
                $url=$this->confData['relatedFilesDirectory'] .  $filename;
                $rf.="<a href=\"$url\">$filename</a><BR>";
            }
            if($relatedFiles[1]!='')
            {
                $filename=$relatedFiles[1];
                $url=$this->confData['relatedFilesDirectory'] .  $filename;
                $rf.="<a href=\"$url\">$filename</a><BR>";
            }
            if($relatedFiles[2]!='')
            {
                $filename=$relatedFiles[2];
                $url=$this->confData['relatedFilesDirectory'] .  $filename;
                $rf.="<a href=\"$url\">$filename</a><BR>";
            }

            $markerArray['###RELATED_FILES###']=$rf;
        }
        else
        {
            $markerArray['###RELATED_FILES###']='No related files';
        }
        $table='tt_news_cat,tt_news_cat_mm';
        $columns='tt_news_cat.title';
        $where='tt_news_cat_mm.uid_local="'.$item_id.'" AND tt_news_cat_mm.uid_foreign=tt_news_cat.uid '. t3lib_BEfunc::deleteClause('tt_news_cat');
        $result3=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        $categories=array();
        if($GLOBALS['TYPO3_DB']->sql_num_rows($result3)>0)
        {
            while($row3=$GLOBALS['TYPO3_DB']->sql_fetch_row($result3))
            {
                $categories[]=$row3[0];
            }
            $markerArray['###CATEGORIES###']=implode('<BR>', $categories);
        }
        else
        {
            $markerArray['###CATEGORIES###']='Item not yet categorized';
        }

        $table1=$this->tablePrefix.'featured_weeks';
        $table2=$this->tablePrefix.'featured_weeks_mm';
        $tables="$table1,$table2";
        $columns="$table1.description";
        $where="$table2.uid_local='$item_id' AND $table2.uid_foreign=$table1.uid AND $table2.selected".  t3lib_BEfunc::deleteClause($table1);
        $result4=$GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $tables, $where);
        $featuredWeeks=array();
        if($GLOBALS['TYPO3_DB']->sql_num_rows($result4)>0)
        {
            while($row4=$GLOBALS['TYPO3_DB']->sql_fetch_row($result4))
            {
                $featuredWeeks[]=$row4[0];
            }
            $markerArray['###FEATURED_WEEKS###']=implode('<BR>', $featuredWeeks);
        }
        else
        {
            $markerArray['###FEATURED_WEEKS###']='Item not featured<BR>';
        }

        if($row['pid']==$this->confData['roundTablesPID'])
        {
            $markerArray['###DISPLAY###']='block';
            $markerArray['###START_DATE###']=$this->timeToString($row['tx_newseventregister_startdateandtime']);
            $markerArray['###END_DATE###']=$this->timeToString($row['tx_newseventregister_enddateandtime']);
            $markerArray['###BEFORE_CONTENT###']=$row['tx_newseventregister_eventinformation'];
            $markerArray['###AFTER_CONTENT###']=$row['tx_newseventregister_followupmessage'];
        }
        else
        {
            $markerArray['###DISPLAY###']='none';
        }

        $content.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
        return $content;
    }




    /**
      * This function returns a SELECT widget containing the list of all         possible authors for th
     * current sponsor
     *
     * @param string $authorID
     * @return String
     */
    function authorSelect($authorID='')
    {
        $list='';
        $whereClause=$this->tablePrefix.'sponsor_id="'.$this->piVars['sponsor_id'].'" '.t3lib_BEfunc::deleteClause('fe_users');
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,name', 'fe_users', $whereClause, '', 'name');
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            $selected=($row['uid']==$authorID)? 'SELECTED' : '';
            $list.="<OPTION value=\"$row[uid]\" $selected>$row[name]</OPTION>\n";
        }
        return $list;
    }

    /**
     * This function returns a SELECT widget containing the list of all possible authors for th
     * current sponsor
     */
    function itemCategoriesSelect($tt_new_id)
    {
        $table_tt_news='tt_news';
        $field = "pid";
        $where = "uid=$tt_new_id";
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field, $table_tt_news, $where);
        $siteArray=$this->getSiteStats();
        $listVar='';

        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        /*foreach($siteArray as $siteId=>$siteName)
        {
            $listVar.=$this->catMapper[$siteId.'_'.$row['pid']];
        }*/
        $listVar = '4,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,2941,4,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29';
        $list='';
        $whereClause='uid in('.$listVar.') and pid="'. $this->confData['itemCategoriesPID']. '" '. t3lib_BEfunc::deleteClause('tt_news_cat');
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tt_news_cat', $whereClause, '', 'title');

        $result1=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign', 'tt_news_cat_mm', 'uid_local="'.$this->piVars['item_id'].'"');
        $selectedCategories=array();
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result1))
        {
            $selectedCategories[]=$row['uid_foreign'];
        }

        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            $selected=in_array($row['uid'], $selectedCategories) ? 'SELECTED' : '';
            $list.="<OPTION value=\"$row[uid]\" $selected>$row[title]</OPTION>\n";
        }
        return $list;
    }

    /**
     * Given a $_FILES[] variable this function uploads the logo in it
     */
    function uploadFile($file, $directory)
    {
        $uploadFile='';
        if($file['name']!='')
        {
            /** TODO - Write an error notifying code for file upload */
            $fileparts=explode('.', basename($file['name']));
            $fileext=$fileparts[count($fileparts)-1];
            array_pop($fileparts);
            $filename=implode('.', $fileparts);
            $i=0;
            $uploadFile="$filename-$i.$fileext";
            while(file_exists($directory . $uploadFile))
            {
                $i++;
                $uploadFile="$filename-$i.$fileext";
            }

            move_uploaded_file($file['tmp_name'], $directory . $uploadFile);
        }
        return $uploadFile;
    }

    /**
     * This functions returns a string of the format dd-mm-yyyy hh:mm:ss given a UNIX timestamp
     */
    function stringToTime($time='')
    {
        if($time=='')
        {
            return '';
        }
        else
        {
            return strtotime($time);
        }
    }


    /**
     * This functions returns a string of the format dd-mm-yyyy hh:mm:ss given a UNIX timestamp
     */
    function timeToString($time='0')
    {
        if($time=='0')
        {
            return '';
        }
        else
        {
            return date('d-M-Y H:i:s', $time);
        }
    }

    function rteSafe($strText)
    {
        //returns safe code for preloading in the RTE
        $tmpString = $strText;

        //convert all types of single quotes
        $tmpString = str_replace(chr(145), chr(39), $tmpString);
        $tmpString = str_replace(chr(146), chr(39), $tmpString);
        $tmpString = str_replace("'", "&#39;", $tmpString);

        //convert all types of double quotes
        $tmpString = str_replace(chr(147), chr(34), $tmpString);
        $tmpString = str_replace(chr(148), chr(34), $tmpString);
        //    $tmpString = str_replace("\"", "\"", $tmpString);

        //replace carriage returns & line feeds
        $tmpString = str_replace(chr(10), " ", $tmpString);
        $tmpString = str_replace(chr(13), " ", $tmpString);

        return $tmpString;
    }


    function imageResize($width, $height, $target) {

        //takes the larger size of the width and height and applies the  formula accordingly...this is so this script will work  dynamically with any size image

        if ($width > $height) {
            $percentage = ($target / $width);
        } else {
            $percentage = ($target / $height);
        }

        //gets the new value and applies the percentage, then rounds the value
        $width = round($width * $percentage);
        $height = round($height * $percentage);

        //returns the new sizes in html image tag format...this is so you can plug this function inside an image tag and just get the
        return "width=\"$width\" height=\"$height\"";
    }

    /**************************************************************************************************************************
    *                                       Package Management                                           *
    **************************************************************************************************************************/
    // Main Package Management Handling Block
    function createSponsorPackage()
    {
        $content='';
        switch($this->piVars['formaction'])
        {
            default:
            $content=$this->generatePackageListing();
        }
        return $content;
    }

    //Generate Package Listings
    function generatePackageListing()
    {
        $table='tx_sponsorcontentscheduler_package';
        $fields='*';
        $where="1 ".$this->cObj->enableFields($table);
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where);

        $typolink_conf = array(
        "parameter" => $GLOBALS['TSFE']->id,
        "additionalParams" => "&".$this->prefixId."[action]=create_package"
        );
        $this->templateCode=$this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'pi1/sale_package.tpl');
        $template['header']=$this->cObj->getSubpart($this->templateCode, '###TEMPLATE_SPONSOR_PACKAGE_LIST###');
        $template['dataPart']=$this->cObj->getSubpart($this->templateCode, '###TEMPLATE_SPONSOR_PACKAGE_DATA###');
        $data_row='';
        while($row=mysql_fetch_assoc($res))    {
            $data_row.=$this->generateDataListings($template['dataPart'],$row);
        }
        $markerArray['###TEMPLATE_PACKAGE_DATA###']=$data_row;
        $markerArray['###PREFIXID###']=$this->prefixId;
        $markerArray['###PACKAGE_CREATE_LINK###']=$this->cObj->typoLink_URL($typolink_conf);
        $content=$this->cObj->substituteMarkerArrayCached($template['header'], $markerArray);
        return $content;
    }

    function generateDataListings($template,$dataRow)
    {
        $typolink_conf = array(
        "parameter" => $GLOBALS['TSFE']->id,
        "additionalParams" => "&".$this->prefixId."[action]=editPackage&".$this->prefixId."[uid]=".$dataRow[uid]
        );
        $editLink=$this->cObj->typoLink_URL($typolink_conf);
        $typolink_conf['additionalParams']="&".$this->prefixId."[action]=deletePackage&".$this->prefixId."[uid]=".$dataRow[uid];
        $deleteLink=$this->cObj->typoLink_URL($typolink_conf);
        $typolink_conf_back = array(
        "parameter" => $GLOBALS['TSFE']->id
        );
        $markerArray['###PACKAGE_NAME###']=$dataRow['title'];
        $status=($dataRow['company_profile']==1)?'Enabled':'Disabled';
        $markerArray['###PACKAGE_DESCRIPTION###']="Company Profile Editing : ".$status;
        $markerArray['###PACKAGE_DESCRIPTION###'].="<br/>Whitepaper : ".$dataRow['whitepaper'];
        $markerArray['###PACKAGE_DESCRIPTION###'].="<br/>Roundtable : ".$dataRow['roundtable'];
        $markerArray['###PACKAGE_DESCRIPTION###'].="<br/>Bulletin : ".$dataRow['bulletin'];
        $markerArray['###PACKAGE_DESCRIPTION###'].="<br/>Due Date : ".date('j-M-Y G:i:s',$dataRow['endtime']);
        $markerArray['###PACKAGE_OPTION_EDIT###']=$editLink;
        $markerArray['###PACKAGE_OPTION_DELETE###']=$deleteLink;
        $markerArray['###PACKAGE_LINK_BACK###']=$this->cObj->typoLink_URL($typolink_conf_back);
        $content = $this->cObj->substituteMarkerArrayCached($template, $markerArray,array());
        return $content;
    }

    function getPackagesList($selected='')
    {
        $table='tx_sponsorcontentscheduler_package';
        $fields='uid,title';
        $where="1 ".$this->cObj->enableFields($table);
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where);
        return $this->getOptionListfromDB($res,'uid','title',$selected);
    }
    function getOptionListfromDB($res,$optionValMap,$optionNameMap,$selected=''){
        $optionVal='';
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
        {
            if($row[$optionValMap]==$selected){
                $optionVal.="<option value='".$row[$optionValMap]."' selected>".$row[$optionNameMap]."</option>";
            }else{
                $optionVal.="<option value='".$row[$optionValMap]."'>".$row[$optionNameMap]."</option>";
            }
        }
        return $optionVal;
    }

    function getPackagesListSelected($_condition)
    {
        $table='tx_sponsorcontentscheduler_package';
        $fields="$table.*";
        $where="$_condition ".$this->cObj->enableFields($table);
        $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        return $row;
    }
    /**************************************************************************************************************************
    *                                       Package Management      End                                *
    **************************************************************************************************************************/

    function _debug($varArr)
    {
        if($_SERVER['REMOTE_ADDR']=='210.211.168.169'){
            echo "<PRE>";
            var_dump($varArr);
            echo "</PRE>";
        }

    }

    function getExtData($key)
    {
        $data=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        return $data[$key];
    }

    /**
     * tx_sponsorcontentscheduler_pi1::create_edit_sponser_screen()
     * 
     * 
     * @return  
     **/
    function create_edit_sponsor_screen(){

        $content = '';
        $template_file = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/create_edit_sponsor_screen.tpl');

        $markerArray=array(
        '###FORM_NAME###' => $this->formName('edit_sponser'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ACTION###' => $this->formName('action'),
        '###SUBMIT_SPONSOR###' => $this->formName('submit_sponsor'),
        );

        //$markerArray['###HEADER###']='SALES PERSONAL AVTIVITIES >> CREATE/EDIT SPONSOR';
        $markerArray['###HEADER###']='<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> CREATE/EDIT SPONSOR';
        $markerArray['###SPONSOR###']='<TD width=30%><SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'">'. $this->sponsorSelect(). '</SELECT></TD>';
        /*$markerArray['###SPONSOR_USER_LIST###']=$this->sponsorUserSelect();
        $markerArray['###DISPLAY###']='block';
        $markerArray['###CREATE_NEW_SPONSOR###']='<TR><TD><A href="javascript: createSponsor()" class="lnav">Create new sponsor</A></TD><TD></TD></TR>';
        $markerArray['###CREATE_NEW_PACKAGE###']='<TR><TD><A href="javascript: createPackage()" class="lnav">Sponsor Package Management</A></TD><TD></TD></TR>';*/
        $markerArray['###CREATE_NEW_SPONSOR_LINK###'] = $this->getPageLink('CREATE NEW SPONSOR','create_sponsor');

        $content .= $this->cObj->substituteMarkerArrayCached($template_file, $markerArray);

        return $this->pi_wrapInBaseClass($content);

    }

    /**
     * tx_sponsorcontentscheduler_pi1::create_edit_sponsor_user_screen()
     * 
     * @return 
     **/
    function create_edit_sponsor_user_screen(){

        $content = '';
        $template_file = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/create_edit_sponsor_user_screen.tpl');

        $markerArray=array(
        '###FORM_NAME###' => $this->formName('edit_sponser_user'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ACTION###' => $this->formName('action'),
        '###SUBMIT_SPONSOR###' => $this->formName('submit_sponsor_user'),
        );

        $markerArray['###FORM_JS###']=$this->getMenuJS();

        $markerArray['###HEADER###']='<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> CREATE/EDIT SPONSOR USER';
        $markerArray['###SPONSOR###']= $this->getSponserSelectorBox();
        $markerArray['###SPONSOR_USER###']=$this->getSponserUserSelectorBox();
        /*$markerArray['###SPONSOR_USER_LIST###']=$this->sponsorUserSelect();
        $markerArray['###DISPLAY###']='block';
        $markerArray['###CREATE_NEW_SPONSOR###']='<TR><TD><A href="javascript: createSponsor()" class="lnav">Create new sponsor</A></TD><TD></TD></TR>';
        $markerArray['###CREATE_NEW_PACKAGE###']='<TR><TD><A href="javascript: createPackage()" class="lnav">Sponsor Package Management</A></TD><TD></TD></TR>';*/
        $markerArray['###CREATE_NEW_SPONSOR_USER_LINK###'] = $this->getPageLink('CREATE NEW SPONSOR USER','create_sponsor_user');

        $content .= $this->cObj->substituteMarkerArrayCached($template_file, $markerArray);

        return $this->pi_wrapInBaseClass($content);

    }

    function create_edit_delete_job_bank_screen(){

        $content = '';
        $template_file = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/create_edit_delete_job_bank_screen.tpl');

        $markerArray=array(
        '###FORM_NAME###' => $this->formName('create_edit_job_bank'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ACTION###' => $this->formName('action'),
        '###SUBMIT_SPONSOR###' => $this->formName('submit_sponsor_for_job_bank'),
        );

        //$markerArray['###FORM_JS###']=$this->getMenuJS();

        $markerArray['###HEADER###']='<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> CREATE/EDIT/DELETE JOB BANK';
        $markerArray['###SPONSOR###']= '<TD width=30%><SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'">'. $this->sponsorSelect(). '</SELECT></TD>';

        $content .= $this->cObj->substituteMarkerArrayCached($template_file, $markerArray);

        return $this->pi_wrapInBaseClass($content);

    }

    function create_edit_sponsor_package_screen(){

        $content = '';
        $markerArray=array(
        '###FORM_NAME###' => $this->formName('create_edit_sponsor_package'),
        '###FORM_ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
        '###ACTION###' => $this->formName('action'),
        '###SUBMIT_SPONSOR###' => $this->formName('submit_sponsor_to_create_package'),
        '###SUBMIT_SPONSOR_PACKAGE###' => $this->formName('submit_package_to_edit'),
        );
        $this->isValidSalesUser();
        switch($this->loginType){
            case 'SALES':
            $template_file = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/create_edit_sponsor_package_screen.tpl');
            $markerArray['###SPONSOR###']= '<TD width=30%><SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'" onChange="packageChanged()"><option value="0">Select a Sponsor</option>'. $this->sponsorSelect(). '</SELECT></TD>';
            $markerArray['###SPONSOR_PACKAGE###']= '<TD width=30%><SELECT name="'. $this->formName('package_id'). '" id="'. $this->formName('package_id'). '" value="'. $package_id .'"><option value="">----SELECT----</option></SELECT></TD>';
            break;
            case 'OWNER':
            $sponsorArr=$this->getSponserFromOwner();
            $template_file = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . 'templates/create_edit_owner_package_screen.tpl');
            $markerArray['###SPONSOR###']= '<TD width=30%><INPUT TYPE="hidden" name="'. $this->formName('sponsor_id'). '" value="'. $sponsorArr['uid'] .'">';
            $markerArray['###SPONSOR_PACKAGE###']= '<TD width=30%>&nbsp;&nbsp;<SELECT name="'. $this->formName('package_id'). '" id="'. $this->formName('package_id'). '" value=""><option value="">----SELECT----</option>'.$this->getPackageJS($sponsorArr['uid']).'</SELECT></TD>';
            break;
        }


        $markerArray['###FORM_JS###']= $this->getPackageJS();


        $markerArray['###REDIRECT_URL_SPONSOR###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_sponsorcontentscheduler_pi1[action]' => 'edit_inventory'));

        $markerArray['###REDIRECT_URL_PACKAGE###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', array('tx_sponsorcontentscheduler_pi1[action]' => 'edit_inventory'));

        $markerArray['###HEADER###']='<a href='.$this->pi_getPageLink($GLOBALS['TSFE']->id).'>SALES PERSONNEL ACTIVITIES</a> >> CREATE/EDIT SPONSOR PACKAGE';

        /*$markerArray['###SPONSOR###']= '<TD width=30%><SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'" onChange="packageChanged()">'. $this->sponsorSelect(). '</SELECT></TD>';*/



        $content .= $this->cObj->substituteMarkerArrayCached($template_file, $markerArray);

        return $this->pi_wrapInBaseClass($content);


    }


    function getSponserSelectorBox(){

        $list = '';
        $list.= '<TD>';

        /** TODO - Add a condition to select only those FE groups which are sponsor groups     */

        $data = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $_where='fe_owner_user = '.$GLOBALS['TSFE']->fe_user->user['uid'].' and pid="'.$data['sponsorsPID'].'" '. t3lib_BEfunc::deleteClause('tx_t3consultancies');
        //echo $GLOBALS['TYPO3_DB']->SELECTquery('uid,title', 'tx_t3consultancies', $_where, '', 'title', '');
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tx_t3consultancies', $_where, '', 'title', '');
        $this->isValidSalesUser();
        switch($this->loginType){
            case 'SALES':
            $list .= '<SELECT name="'. $this->formName('sponsor_id'). '" value="'. $sponsor_id .'" size="1" onChange="sponsorChanged()">';

            //$list .= '<SELECT name= "sponsor" value="'. $sponsor_id .'"size="1" onChange="sponsorChanged(this.form)">';
            $list .= '<option value="">----SELECT----</option>';
            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
            {
                $list .= "<OPTION value=\"$row[uid]\" >$row[title]</OPTION>";
            }
            $list .= '</SELECT>';
            break;
            case 'OWNER':

            $sponsorArr=$this->getSponserFromOwner();
            $list.='<INPUT TYPE="hidden" name="'. $this->formName('sponsor_id'). '" value="'. $sponsorArr['uid'] .'">';
            break;
        }
        $list .= '</TD>';
        return $list;

    }


    function getSponserUserSelectorBox(){
        $this->isValidSalesUser();
        switch($this->loginType){
            case 'OWNER':
            $optionVal='';
            $select_fields = 'uid, username, tx_sponsorcontentscheduler_sponsor_id';
            $orderBy = 'name';
            $table_feUser='fe_users';
            $sponsorArr=$this->getSponserFromOwner();
            $where = "$table_feUser.tx_sponsorcontentscheduler_sponsor_id = ".$sponsorArr['uid'];

            $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $table_feUser, $where, null, $orderBy);
            if ($result)
            {
                if (mysql_num_rows($result))
                {
                    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
                    {
                        if (!empty($row['uid']) && !empty($row['username'])) {
                            $sponsor_user_uid = $row['uid'];
                            $sponsor_user_username = $row['username'];

                            $sponsor_id = (0 < intval($row['tx_sponsorcontentscheduler_sponsor_id'])) ? intval($row['tx_sponsorcontentscheduler_sponsor_id']) : '0';

                            $optionVal .= "<option value='$sponsor_user_uid'>$sponsor_user_username</option>\n";
                        }
                    }
                }
            }
            break;

            case 'SALES':
            break;
        }


        $list = '';
        $list.= '<TD>';
        $list .= '<select name="'. $this->formName('sponsor_user_id'). '" value="'. $sponsor_user_id .'" size="1" >
                    <option value="">----SELECT----</option>';
        $list.=$optionVal;
        $list .= '</select>';

        $list.= '</TD>';

        return $list;



    }

    function getMenuJS()
    {
        $js = '<script language="JavaScript">
        <!--
        
        /////////////////////////////////////////////////////////////////////////////
        var sponsorUserTable;
        sponsorUserTable = new Array(';

        $select_fields = 'uid, username, tx_sponsorcontentscheduler_sponsor_id';
        $orderBy = 'name';
        $where = "fe_users.tx_sponsorcontentscheduler_sponsor_id!=''";
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, 'fe_users', $where, null, $orderBy);
        if ($result)
        {
            if (mysql_num_rows($result))
            {
                while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
                {
                    if (!empty($row['uid']) && !empty($row['username'])) {
                        $sponsor_user_uid = $row['uid'];
                        $sponsor_user_username = $row['username'];

                        $sponsor_id = (0 < intval($row['tx_sponsorcontentscheduler_sponsor_id'])) ? intval($row['tx_sponsorcontentscheduler_sponsor_id']) : '0';

                        $js .= 'new Array('.$sponsor_user_uid.', \''.addslashes($sponsor_user_username).'\', '.$sponsor_id.'), '."\n";
                    }
                }
            }
        }

        $js = rtrim($js, ", \n");

        $js .= ');
        
        
        ';

        $replace = array();
        $replace['FORM_NAME'] = $this->formName('edit_sponser_user');

        $temp_js = $this->tmpl_script;

        foreach ($replace as $key => $value)
        {
            $temp_js = str_replace('###'.$key.'###', $value, $temp_js);
        }

        //echo $temp_js;
        $js .= $temp_js;
        unset($temp_js);

        $js .= '
        -->
        </script>';

        return $js;
    }


    function getPackageJS($returnType='')
    {
        $returnVal='';
        $js = '<script language="JavaScript">
        <!--
        
        /////////////////////////////////////////////////////////////////////////////
        var packageTable;
        packageTable = new Array(';

        $select_fields = 'uid,title,sponsor_id';
        $orderBy = 'title';
        $where='';
        if($returnType!='')
        $where="sponsor_id=$returnType";

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, 'tx_sponsorcontentscheduler_package', $where, null, $orderBy);
        if ($result)
        {
            if (mysql_num_rows($result))
            {
                while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
                {
                    $sponsor_id = (0 < intval($row['sponsor_id'])) ? intval($row['sponsor_id']) : '0';

                    $js .= 'new Array('.$row['uid'].', \''.addslashes($row['title']).'\', '.$sponsor_id.'), '."\n";
                    $returnVal .= '<option value="'.$row['uid'].'">'.addslashes($row['title'])."</option>\n";

                }
            }
        }

        $js = rtrim($js, ", \n");

        $js .= ');
        
        
        ';

        $temp_js = $this->tmpl_script;

        //echo $temp_js;
        $js .= $temp_js;
        unset($temp_js);

        $js .= '
        -->
        </script>';
        if($returnType!=''){
            return $returnVal;
        }else{
            return $js;
        }


    }


    function sendMailToSponsorMasterUser($recipient, $user_name)
    {
        $subject = 'Testing';
        $headers = 'From: sponsor@bpminstitute.org' . "\r\n" .
                   'Reply-To: sponsor@bpminstitute.org' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        $mail_template_path = t3lib_extMgm::siteRelPath($this->extKey).'/templates/mail_template.tpl';
        
        if (file_exists($mail_template_path)) {
            $mail_text = file_get_contents($mail_template_path);
        }

        $marker['###USER_NAME###'] = $user_name;
        $mail_text = $this->cObj->substituteMarkerArray($mail_text, $marker);

        return mail($recipient, $subject, $mail_text, $headers);
    }
    
    function sendMailToSales($user_name)
    {
        $fe_owner_user = $GLOBALS['TSFE']->fe_user->user['uid'];
        $ownerAttributes = $this->getOwnerAttributes($fe_owner_user);
        $recipient = $ownerAttributes['email'];
        $subject = 'New sponsor user was created';
        $headers = 'From: sponsor@bpminstitute.org' . "\r\n" .
                   'Reply-To: sponsor@bpminstitute.org' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        $mail_template_path = t3lib_extMgm::siteRelPath($this->extKey).'/templates/mail_sales_template.tpl';
        
        if (file_exists($mail_template_path)) {
            $mail_text = file_get_contents($mail_template_path);
        }

        $marker['###USER_NAME###'] = $user_name;
        $mail_text = $this->cObj->substituteMarkerArray($mail_text, $marker);
        
        return mail($recipient, $subject, $mail_text, $headers);
    }
    
    function sendMailItemEdit($item_id, $sponsor_id)
    {
        // get sales rep. email
        $fe_owner_user = $GLOBALS['TSFE']->fe_user->user['uid'];
        $ownerAttributes = $this->getOwnerAttributes($fe_owner_user);
        $recipient = $ownerAttributes['email'];
        // get sponsor user email
        if ($this->piVars['package_id'] != "")
        $package_info = $this->getPackageInfo($this->piVars['package_id']);
        elseif ($_SESSION['package_id'] != "")
        $package_info = $this->getPackageInfo($_SESSION['package_id']);  
        
        $sponsorUserAttrs = $this->getOwnerAttributes($package_info['fe_uid']);
        
		// MLC 200704041350 commented out per GR request
        // $recipient.= ','.$sponsorUserAttrs['email'];
        $subject = 'Sponsor package news item changed';
        $headers = 'From: sponsor@bpminstitute.org' . "\r\n" .
                   'Reply-To: sponsor@bpminstitute.org' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        $mail_text = $this->cObj->fileResource('EXT:sponsor_content_scheduler/templates/mail_package_edit.tpl');
        
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                n.*,
                p.title as package_title
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON (n.tx_sponsorcontentscheduler_package_id = p.uid)
            WHERE
                n.uid = %s
                AND n.hidden = 0
                AND n.deleted = 0',
                $TYPO3_DB->fullQuoteStr($item_id)));
        $row = $TYPO3_DB->sql_fetch_assoc($res);

        $table = 'tx_t3consultancies';
        $columns = 'title';
        $where = "uid='$sponsor_id' ".t3lib_BEfunc::deleteClause($table);
        $result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        $row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2);

        $marker = array (
            '###AUTHOR###'     => $row['author'],
            '###CONTENT###'    => strip_tags($row['bodytext']),
            '###DATE###'       => $this->timeToString($row['datetime']),
            '###ITEM_TYPE###'  => $this->itemType[$row['pid']],
            '###LEADS###'      => ($row['tx_newslead_leadon'] == '1') ? 'Yes' : 'No',
            '###SPONSOR###'    => $row2['title'],
            '###START_DATE###' => $this->timeToString($row['tx_newseventregister_startdateandtime']),
            '###TITLE###'      => $row['title']
        );

        if ($row['news_files'] != '') {
            $relatedFiles=explode(',', $row['news_files']);
            $rf = '';
            foreach ($relatedFiles as $filename) {
                if (!empty($filename)) {
                	$url = $this->confData['relatedFilesDirectory'].$filename;
                	$rf.= "\t* $url\n";
                }
            }
            $marker['###RELATED_FILES###'] = $rf;
        } else {
            $marker['###RELATED_FILES###'] = 'No related files';
        }
        
        $table = 'tt_news_cat,tt_news_cat_mm';
        $columns = 'tt_news_cat.title';
        $where = 'tt_news_cat_mm.uid_local="'.$item_id.'" AND tt_news_cat_mm.uid_foreign=tt_news_cat.uid '.t3lib_BEfunc::deleteClause('tt_news_cat');
        $result3 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $table, $where);
        $categories = array();
        if ($GLOBALS['TYPO3_DB']->sql_num_rows($result3) > 0) {
            while($row3=$GLOBALS['TYPO3_DB']->sql_fetch_row($result3)) {
                $categories[] = $row3[0];
            }
            $marker['###CATEGORIES###'] = "\t* ".implode("\n\t* ", $categories);
        } else {
            $marker['###CATEGORIES###'] = 'Item not yet categorized';
        }

        $table1 = $this->tablePrefix.'featured_weeks';
        $table2 = $this->tablePrefix.'featured_weeks_mm';
        $tables = "$table1,$table2";
        $columns = "$table1.description";
        $where = "$table2.uid_local='$item_id' AND $table2.uid_foreign=$table1.uid AND $table2.selected".t3lib_BEfunc::deleteClause($table1);
        $result4 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($columns, $tables, $where);
        $featuredWeeks = array();
        if($GLOBALS['TYPO3_DB']->sql_num_rows($result4) > 0) {
            while($row4=$GLOBALS['TYPO3_DB']->sql_fetch_row($result4)) {
                $featuredWeeks[] = $row4[0];
            }
            $marker['###FEATURED_WEEKS###'] = "\t* ".implode("\n\t* ", $featuredWeeks);
        } else {
            $marker['###FEATURED_WEEKS###']='Item not featured';
        }

        if ($row['pid'] == $this->confData['roundTablesPID']) {
            $marker['###START_DATE###']     = $this->timeToString($row['tx_newseventregister_startdateandtime']);
            $marker['###END_DATE###']       = $this->timeToString($row['tx_newseventregister_enddateandtime']);
            $marker['###BEFORE_CONTENT###'] = $row['tx_newseventregister_eventinformation'];
            $marker['###AFTER_CONTENT###']  = $row['tx_newseventregister_followupmessage'];
        } else {
            $marker['###START_DATE###']     = '';
            $marker['###END_DATE###']       = '';
            $marker['###BEFORE_CONTENT###'] = '';
            $marker['###AFTER_CONTENT###']  = '';
        }
        
        $mail_text = $this->cObj->substituteMarkerArray($mail_text, $marker);
        
        return mail($recipient, $subject, $mail_text, $headers);
    }
    
    function sendMailPackageUpdated()
    {
        global $TYPO3_DB;

        // get sales rep. email
        $fe_owner_user = $GLOBALS['TSFE']->fe_user->user['uid'];
        $ownerAttributes = $this->getOwnerAttributes($fe_owner_user);
        $recipient = $ownerAttributes['email'];
        
        // get sponsor user attributes
        $attributes = $this->getOwnerAttributes($this->piVars['sponsor_user_id']);
        
        // get sponsor user email
		// MLC 200704041350 commented out per GR request
        // $recipient.= ','.$attributes['email'];
        
        $subject = 'Sponsor package news item changed';
        $headers = 'From: sponsor@bpminstitute.org' . "\r\n" .
                   'Reply-To: sponsor@bpminstitute.org' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        // Get sponsor title
        $column = 'title';
        $table = 'tx_t3consultancies';
        $where = 'uid = '.$TYPO3_DB->fullQuoteStr($this->piVars['sponsor_id']);
        $result = $TYPO3_DB->exec_SELECTquery($column, $table, $where);
        $row = $TYPO3_DB->sql_fetch_row($result);
        $sponsor = $row[0];
        
        // Get package items
        $result = $TYPO3_DB->sql_query(sprintf("
            SELECT
                *
            FROM
                tt_news
            WHERE
                tx_sponsorcontentscheduler_package_id = %s
                AND deleted = 0
            ",
            $TYPO3_DB->fullQuoteStr((int)$this->piVars['package_id'])
        ));
        
        $items = '';

        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
        {
            $_featuredWeek='';
            $_siteMapper=$this->conf['siteMapper'];
            $_arrSiteMapper=explode("|",$_siteMapper);
            $siteConfigureID=array();
            foreach($_arrSiteMapper as $siteChunk)
            {
                $_arrInternalSiteMapper=explode(",",$siteChunk);
                $siteConfigureID[]=$_arrInternalSiteMapper[0];
                $_featuredWeek.=$this->generateFeaturedList($_featuredWeekTemplate,$_arrInternalSiteMapper[0],$row);
            }

            if ($this->getTimeStampFromttNews($row['uid'])>0) {
                $_dueDate = $this->timeToString($this->getTimeStampFromttNews($row['uid']));
            }

            global $TYPO3_DB;
            $myres = $TYPO3_DB->sql_query(sprintf(
                'SELECT COUNT(1) AS unsent_leads FROM tx_newslead_leads WHERE news_id = %s AND leadsent = 0 AND hidden = 0 and deleted = 0',
                $TYPO3_DB->fullQuoteStr($row['uid'])));
            $myrow = $TYPO3_DB->sql_fetch_assoc($myres);
            $unsent_leads = $myrow['unsent_leads'];
            $live = ($row['hidden']==0) ? 'Live: On' : 'Live: Off';
            $leads = ($row['tx_newslead_leadon'] == 1) ? 'Leads: On' : 'Leads: Off';
            
            $items.= $row['title'].' ['.$row['uid']."]\n"
                    ."\t".$this->getInventoryItemType($row['pid'])."\n"
                    ."\tFeatured weeks: ".$_featuredWeek."\n"
                    ."\t".$live."\n"
                    ."\t".$leads."\n"
                    ."\tUnused leads: ".$row['tx_sponsorcontentscheduler_unused_leads']."\n"
                    ."\tUnsent leads: ".$unsent_leads."\n\n";
        }

        $marker = array(
            '###SPONSOR###' => $sponsor,
            '###PACKAGE_NAME###' => $this->piVars['package_name'],
            '###SPONSOR_USER_NAME###' => $attributes['name'],
            '###ITEMS###' => $items
        );
        
        $mail_text = $this->cObj->fileResource('EXT:sponsor_content_scheduler/templates/mail_package_update.tpl');
        $mail_text = $this->cObj->substituteMarkerArray($mail_text, $marker);
        
        return mail($recipient, $subject, $mail_text, $headers);
    }
    
    /**
     * tx_sponsorcontentscheduler_base::isLoggedInUserBelongsToSalesGroup()
     * 
     * checks if logged in user belongs to Sales Group
     * @param $loggedInUserGroup
     * @param $salesGroupId
     * @return boolean
     **/
    function isLoggedInUserBelongsToSalesGroup($loggedInUserGroup, $salesGroupId){

        //Get the logged in user groupid in an array
        $_loggedInUserGroup_array = explode(',', $loggedInUserGroup);
        $_salesGroupId_array = explode(',', $salesGroupId);
        $_returnVal=false;
        foreach ($_loggedInUserGroup_array as $valid_id){
            if (in_array($valid_id,$_salesGroupId_array)) {
                $_returnVal=TRUE;
            }

        }
        //        if (in_array($salesGroupId, $_loggedInUserGroup_array)) {
        //            return TRUE;
        //        }else{
        //            return FALSE;
        //        }
        return $_returnVal;
    }

    function isValidSalesUser()
    {
        $salesGroupId=$this->getExtData('salesGroupID');
        $loggedInGroup_array=explode(",",$this->_loggedInUserGroupId);

        if(in_array($salesGroupId,$loggedInGroup_array)){
            $this->loginType='SALES';
        }elseif($this->getSponserOwner() || $this->isSponsorUser() ){
            $this->loginType='OWNER';
        }else{
            $this->loginType='ERROR';
        }

    }

    function getSponserOwner()
    {
        $tableSponsor='tx_t3consultancies';
        $field='count(*) as total';
        $where="$tableSponsor.tx_sponsorcontentscheduler_owner_id in (".$GLOBALS['TSFE']->fe_user->user['uid'].")";
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$tableSponsor,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        if($row['total']>0)
        {
            return true;
        }else{
            return false;
        }
    }

    function isSponsorUser()
    {
        $tableFeuser='fe_users';
        $field='count(tx_sponsorcontentscheduler_sponsor_id) as total';
        $where="$tableFeuser.uid =".$GLOBALS['TSFE']->fe_user->user['uid'];
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$tableFeuser,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        if($row['total']>0)
        {
            return true;
        }else{
            return false;
        }
    }

    //Function get Sponsor Id from current logged in User [Owner]
    function getSponserFromOwner()
    {
        $tableSponsor='tx_t3consultancies';
        $field='*';
        $where="$tableSponsor.tx_sponsorcontentscheduler_owner_id in (".$GLOBALS['TSFE']->fe_user->user['uid'].")";
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$tableSponsor,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        return $row;
    }

    function deleteFileNews($news_id,$fileName){
        $table_ttNews="tt_news";
        $field = "news_files";
        $where = "uid=$news_id";
        //@unlink($this->confData['relatedFilesDirectory'] . $fileName);
        $result=$GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table_ttNews,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        $relatedFiles=explode(',', $row['news_files']);
        $updatedFiles='';
        foreach ($relatedFiles as $sourceFile) {

            if($sourceFile===$fileName){
                @unlink($this->confData['relatedFilesDirectory'] . $fileName);
            }else{
                $updatedFiles.="$sourceFile,";
            }
        }
        $updateFields=array(
        $field=>$updatedFiles
        );
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table_ttNews, $where, $updateFields);
    }

    function getSiteStats()
    {
        $_siteArr=explode("|",$this->conf['siteMapper']);
        $_returnArr=array();
        foreach ($_siteArr as $siteVal) {
            list($_siteId,$_siteName)=explode(",",$siteVal);
            $_returnArr[$_siteId]=$_siteName;
        }
        return $_returnArr;
    }


    function getProcessFeaturedWeek($featuredWeek,$returnType=''){
        $arrSite=$this->getSiteStats();
        $returnVal="<ul>";
        $returnValArr=array();
        $_featuredArr=explode("|",$featuredWeek);
        foreach ($_featuredArr as $_featuredVal) {
            list($_featuredId,$_featuredName)=explode(",",$_featuredVal);
            if(($_featuredName>0) && ($_featuredName!='')){
                $returnValArr[$_featuredId]=$_featuredName;
                $returnVal.="<li><b>".$_featuredName." for ".$arrSite[$_featuredId]."</b></li>";
            }
        }
        $returnVal.="</ul>";
        if($returnType!='')
        {
            return $returnValArr;
        }else{
            return $returnVal;
        }
    }

    function isUserRegistered($username){

        $table = "fe_users";
        $fields = "uid,username";
        $where = "username = '$username'";
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where);
        $row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        if($row){
            return true;
        }else{
            return false;
        }
    }

    function debug_argument($v)
    {
        $MAXSTRLEN = 64;
        if (is_null($v))
            return 'null';
        else if (is_int($v))
            return $v;
        else if (is_array($v)) {
            $elems = array();
            foreach ($v as $key => $value) {
                $elems[] = $this->debug_argument($key)." => ".$this->debug_argument($value);
            }
            return "array(".join(", ", $elems).")";
        }
        else if (is_object($v))
            return 'Object:'.get_class($v);
        else if (is_bool($v))
            return $v ? 'true' : 'false';
        else {
            $v = (string) @$v;
            $str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
            if (strlen($v) > $MAXSTRLEN) $str .= '...';
            $str = str_replace("\n", "\\n", $str);
            $str = str_replace("\t", "\\t", $str);
            $str = str_replace("\r", "\\r", $str);
            return "\"".$str."\"";
        }
    }

    function print_backtrace()
    {
        print("<pre>Debug backtrace:\n");
        $i = -1;
        foreach(debug_backtrace() as $t)
        {
            $i++;
            echo "#$i in ";

            if(isset($t['class']))
                echo $t['class'] . $t['type'];

            echo $t['function'];

            if(isset($t['args']) && count($t['args']) > 0) {
                $args = array();
                foreach($t['args'] as $v)
                    $args[] = $this->debug_argument($v);
                echo '('.implode(', ',$args).')';
            } else
                echo '()';
            if(isset($t['file']))
                echo " at ". basename($t['file']) . ":" . $t['line'];
            else
            {
                // if file was not set, I assumed the functioncall
                // was from PHP compiled source (ie XML-callbacks).
                echo '<PHP inner-code>';
            }

            echo "\n";
        }
        echo '</pre>';
    }
        
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sponsor_content_scheduler/pi1/class.tx_sponsorcontentscheduler_pi1.php"])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sponsor_content_scheduler/pi1/class.tx_sponsorcontentscheduler_pi1.php"]);
}

?>
