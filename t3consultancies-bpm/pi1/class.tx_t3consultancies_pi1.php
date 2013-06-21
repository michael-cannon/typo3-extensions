<?php
/***************************************************************
 *  Copyright notice
 *  
 *  (c) 2002 Kasper Skårhøj (kasper@typo3.com)
 *  (c) 2006 Markus Bertheau (markus@bcs-it.com)
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
 * Plugin 'Sponsors' for the 't3consultancies' extension.
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 * @author  Markus Bertheau <markus@bcs-it.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_t3consultancies_pi1 extends tslib_pibase {
    var $prefixId = "tx_t3consultancies_pi1";		// Same as class name
    var $scriptRelPath = "pi1/class.tx_t3consultancies_pi1.php";	// Path to this script relative to the extension dir.
    var $extKey = "t3consultancies";	// The extension key.

    // Internal
    var $categories=array();
    var $showRef=0;	// If set, then references for sponsors are shown.
    var $singleViewOn=0;	// If set, a single item is shown


    /**
     * Main function
     */
    function main($content,$conf)
    {
        // show contact form
        if ($this->piVars['contact'])
        {
            $template = $this->cObj->fileResource($conf['contactTemplateFile']);
            $template = $this->cObj->getSubpart($template, '###DOCUMENT_BODY###');
            global $TSFE;
            $marker = array(
                '###ACTION###' => htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')),
                '###FIRSTNAME###' => $TSFE->fe_user->user['first_name'],
                '###LASTNAME###' => $TSFE->fe_user->user['last_name'],
                '###COMPANY###' => $TSFE->fe_user->user['company'],
                '###PHONE###' => $TSFE->fe_user->user['telephone'],
                '###EMAIL###' => $TSFE->fe_user->user['email'],
                '###COMPANIES###' => implode("-", $this->piVars['contact']),
            );
            return $this->cObj->substituteMarkerArrayCached($template, $marker, array());
        }

        // send contact requests
        if ($this->piVars['comparray'])
        {
            $companies = implode(",", explode("-", $this->piVars['comparray']));

            // record contact request in database
            
            global $TYPO3_DB;
            $TYPO3_DB->sql_query(sprintf("
                INSERT INTO tx_t3consultancies_contact_requests
                    (pid, first_name, last_name, company, phone, email, comment, companies)
                VALUES
                    (%s, %s, %s, %s, %s, %s, %s, %s)",
                $this->cObj->data['pages'],
                $TYPO3_DB->fullQuoteStr($this->piVars['cdata']['cfirstname'], ''),
                $TYPO3_DB->fullQuoteStr($this->piVars['cdata']['clastname'], ''),
                $TYPO3_DB->fullQuoteStr($this->piVars['cdata']['ccompany'], ''),
                $TYPO3_DB->fullQuoteStr($this->piVars['cdata']['cphone'], ''),
                $TYPO3_DB->fullQuoteStr($this->piVars['cdata']['cemail'], ''),
                $TYPO3_DB->fullQuoteStr($this->piVars['cdata']['ccomments'], ''),
                $TYPO3_DB->fullQuoteStr($companies, '')
            ));

            // send email to vendor

            $template = $this->cObj->fileResource($conf['emailTemplateFile']);
            // convert win / mac newlines to unix newlines
            $template = str_replace("\r\n", "\n", $template);
            $template = str_replace("\r", "\n", $template);
            $marker = array(
                '###CFIRSTNAME###' => $this->piVars['cdata']['cfirstname'],
                '###CLASTNAME###'  => $this->piVars['cdata']['clastname'],
                '###CCOMPANY###'   => $this->piVars['cdata']['ccompany'],
                '###CPHONE###'     => $this->piVars['cdata']['cphone'],
                '###CEMAIL###'     => $this->piVars['cdata']['cemail'],
                '###CCOMMENTS###'  => $this->piVars['cdata']['ccomments'],
            );
            foreach (explode("-", $this->piVars['comparray']) as $company) {
                // fetch vendor info
                $res = $TYPO3_DB->sql_query(sprintf("
                    SELECT
                        *
                    FROM
                        tx_t3consultancies
                    WHERE
                        uid = %s
                        AND hidden = 0
                        AND deleted = 0",
                    $TYPO3_DB->fullQuoteStr($company, '')));
                while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
                    $mymarker = $marker;
                    $mymarker['###VENDORCONTACTNAME###'] = $row['contact_name'];
                    $mymarker['###VENDORCONTACTEMAIL###'] = $row['contact_email'];
                    $mymarker['###SPONSORNAME###'] = $row['title'];
                    $email = $this->cObj->substituteMarkerArrayCached($template, $mymarker, array());
                    $body = array();
                    $headers = array();
                    foreach (explode("\n", $email) as $line) {
                        if ($this->startswith($line, "To: ")) {
                            $to = substr($line, 4);
                            continue;
                        }
                        if ($this->startswith($line, "Subject: ")) {
                            $subject = substr($line, 9);
                            continue;
                        }
                        if ($this->startswith($line, "From: ")) {
                            $headers[] = $line;
                            continue;
                        }
                        $body[] = $line;
                    }
                    if (strlen($conf['overrideVendorAddress']) > 0)
                        $to = $conf['overrideVendorAddress'];
                    if (strlen($conf['Bcc']) > 0) {
                        $bcc = str_replace(array("\r", "\n"), "", $conf['BCC']);
                        $headers[] = "Bcc: " . $bcc;
                    }
                    mail($to, $subject, implode("\r\n", $body), implode("\r\n", $headers));
                }
            }
            
            // display thank you page
            
            $template = $this->cObj->fileResource($conf['thankYouTemplateFile']);
            $template = $this->cObj->getSubpart($template, '###DOCUMENT_BODY###');
            unset($this->piVars['comparray']);
            unset($this->piVars['cdata']);
            $url = $this->pi_linkTP_keepPIvars_url();
            $marker = array(
                '###URL###' => $url,
            );
            header(sprintf("Refresh: 15; URL=%s", $url));
            return $this->cObj->substituteMarkerArrayCached($template, $marker, array());
        }

        // show solution locator
        $this->template = $this->cObj->fileResource($conf['templateFile']);
        $this->template = $this->cObj->getSubpart($this->template, '###DOCUMENT_BODY###');
        $this->showRef = $conf["t3references_disabled"] ? 0 : t3lib_extMgm::isLoaded("t3references");
        $this->sitesMade = !$conf["sites_made_disabled"];

        switch((string)$conf["CMD"]) {
            case "singleView":
                list($t) = explode(":",$this->cObj->currentRecord);
            $this->internal["currentTable"]=$t;
            $this->internal["currentRow"]=$this->cObj->data;
            return $this->pi_wrapInBaseClass($this->singleView($content,$conf,1));
            break;
            default:
            if (strstr($this->cObj->currentRecord,"tt_content"))	{
                $conf["pidList"] = $this->cObj->data["pages"];
                $conf["recursive"] = $this->cObj->data["recursive"];
                $conf["selectedOnly"] = $this->cObj->data["tx_t3consultancies_selected_only"];
            }
            return $this->pi_wrapInBaseClass($this->listView($content,$conf));
            break;
        }
    }

    function startswith($s, $start)
    {
        return substr($s, 0, strlen($start)) == $start;
    }

    function strcap($s, $width)
    {
        if (strlen($s) <= $width)
            return $s;
        return substr($s, 0, $width - 3) . '...';
    }

    /**
     * Makes the list view of sponsors
     */
    function listView($content,$conf)
    {
        $this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();		// Loading the LOCAL_LANG values

        $lConf = $this->conf["listView."];	// Local settings for the listView function



        $this->pi_autoCacheEn=1;
        $this->pi_autoCacheFields = Array();
        $this->pi_autoCacheFields["pointer"] = array("range"=>array(0,10));

        if ($this->piVars["showUid"])	{	// If a single element should be displayed:
            $this->internal["currentTable"] = "tx_t3consultancies";
            $this->internal["currentRow"] = $this->pi_getRecord("tx_t3consultancies",$this->piVars["showUid"]);

            $content = '<div class="csc-header csc-header-n2"><h2 class="red">Solution Locator</h2></div>';
            $content .= $this->singleView($content,$conf);
            return $content;
        } else {
# So the preview button will always be shown...
            $this->pi_alwaysPrev=1;

            $this->editAdd=0;
            if ((string)$this->piVars["editAdd"]) {
                $this->editAdd=1;
                $GLOBALS["TSFE"]->set_no_cache();
            }

            if ($this->editAdd && !$GLOBALS["TSFE"]->loginUser) {
                return '<p>'.sprintf($this->pi_getLL("noUserLoggedInWarning"),'<a href="'.$this->pi_getPageLink($this->conf["loginPageId"]).'">','</a>').'</p>';
            } elseif ($this->editAdd)	{
#				$GLOBALS["TSFE"]->set_no_cache();

                $fullTable="";

                $feAConf=$this->getFe_adminLibConf();
                $fullTable.= $this->cObj->cObjGetSingle($this->conf["fe_adminLib"],$feAConf);

                $fullTable.= '<p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("Return_to_listing"),array("editAdd"=>"")).'</p>';
                // Returns the content from the plugin.
                return $fullTable;
            } else {

                if (!isset($this->piVars["pointer"]))
                    $this->piVars["pointer"]=0;

                // Initializing the query parameters:
                // Number of results to show in a listing.
                $this->internal["results_at_a_time"]=t3lib_div::intInRange($lConf["results_at_a_time"],0,2000000,2000000);
                // The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
                $this->internal["maxPages"]=t3lib_div::intInRange($lConf["maxPages"],0,1000,5);
                $this->internal["searchFieldList"]="title,description,url,contact_email,contact_name";


                $this->addWhere="";

                global $TSFE;
                $bpm_or_soa = "bpm";
                if (strstr($TSFE->config['config']['baseURL'], "soainstitute"))
                    $bpm_or_soa = "soa";

                $this->addWhere .= sprintf(' AND weight_%s > 0 ', $bpm_or_soa);

                // Only selected:
                $this->addWhere.= $this->conf["selectedOnly"]	? ' AND tx_t3consultancies.selected' : '';


                // Getting the categories used - piVars["service"]
                $this->loadCategories();
#debug($this->categories);


#debug($this->addWhere);

                $mm_cat="";
                if ($this->piVars["service"]) {
                    $mm_cat=array();
                    $mm_cat["mmtable"]="tx_t3consultancies_services_mm";
                    $mm_cat["table"]="tx_t3consultancies_cat";
                    $mm_cat["catUidList"]=intval($this->piVars["service"]);
                }


                // Get number of records:
                $query = $this->pi_list_query('tx_t3consultancies',1,$this->addWhere,$mm_cat);
                $res = mysql(TYPO3_db,$query);
                if (mysql_error())
                    debug(array(mysql_error(),$query));
                list($this->internal["res_count"]) = mysql_fetch_row($res);

                // Make listing query, pass query to MySQL:
                $query = $this->pi_list_query("tx_t3consultancies",0,$this->addWhere,$mm_cat,"", sprintf(" ORDER BY tx_t3consultancies.weight_%s DESC, tx_t3consultancies.title", $bpm_or_soa));
                $res = mysql(TYPO3_db,$query);
                if (mysql_error())
                    debug(array(mysql_error(),$query));
                $this->internal["currentTable"] = "tx_t3consultancies";

                global $TYPO3_DB;
                
                $templateCollageRow = $this->cObj->getSubpart($this->template, '###COLLAGE_ROW###');
                $templateCollageItem = $this->cObj->getSubpart($templateCollageRow, '###COLLAGE_ITEM###');
                $templateListItem = $this->cObj->getSubpart($this->template, '###LIST_ITEM###');
                $templateEliteCollage = $this->cObj->getSubpart($this->template, '###ELITE_COLLAGE###');
                $templateEliteList = $this->cObj->getSubpart($this->template, '###ELITE_LIST###');
                $templatePreferredCollage = $this->cObj->getSubpart($this->template, '###PREFERRED_COLLAGE###');
                $templatePreferredList = $this->cObj->getSubpart($this->template, '###PREFERRED_LIST###');

                $htmlEliteCollage = "";
                $htmlPreferredCollage = "";
                $htmlEliteList = "";
                $htmlPreferredCollage = "";
                $htmlCollageRow = "";
                $i = 1;
                $prevweight = 100;
                $htmlCollage = "htmlEliteCollage";
                $htmlList = "htmlEliteList";
                while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
                    $row['weight'] = $row[sprintf('weight_%s', $bpm_or_soa)];
                    if ($row['weight'] != $prevweight) {
                        $$htmlCollage .= $this->cObj->substituteMarkerArrayCached($templateCollageRow, array(), 
                            array('###COLLAGE_ITEM###' => $htmlCollageRow));
                        $htmlCollageRow = "";
                        $htmlCollage = "htmlPreferredCollage";
                        $htmlList = "htmlPreferredList";
                        $i = 1;
                    }
                    if (!$this->startswith($row['url'], 'http'))
                        $row['url'] = 'http://' . $row['url'];
                    $marker = array(
                        '###ID###' => $row['uid'],
                        '###LOGO###' => "uploads/tx_t3consultancies/" . $row['logo'],
                        '###DESCRIPTION###' => htmlspecialchars($row['description'], ENT_NOQUOTES),
                        '###URL###' => htmlspecialchars($row['url'], ENT_COMPAT),
                        '###URLTEXT###' => htmlspecialchars($this->strcap($row['url'], 40), ENT_NOQUOTES),
                        '###TITLE###' => htmlspecialchars($row['title'],ENT_NOQUOTES),
                    );
                    $htmlCollageRow .= $this->cObj->substituteMarkerArrayCached($templateCollageItem, $marker, array());
                    $$htmlList .= $this->cObj->substituteMarkerArrayCached($templateListItem, $marker, array());
                    
                    if ($i % 3 == 0) {
                        $$htmlCollage .= $this->cObj->substituteMarkerArrayCached($templateCollageRow, array(), 
                            array('###COLLAGE_ITEM###' => $htmlCollageRow));
                        $htmlCollageRow = "";
                    }
                    $prevweight = $row['weight'];
                    $i++;
                }
                $$htmlCollage .= $this->cObj->substituteMarkerArrayCached($templateCollageRow, array(), 
                    array('###COLLAGE_ITEM###' => $htmlCollageRow));
                    
                if (strlen($htmlEliteCollage) > 0)
                    $htmlEliteCollage = $this->cObj->substituteMarkerArrayCached($templateEliteCollage, array(),
                        array('###COLLAGE_ROW###' => $htmlEliteCollage));
                if (strlen($htmlEliteList) > 0)
                    $htmlEliteList = $this->cObj->substituteMarkerArrayCached($templateEliteList, array(),
                        array('###LIST_ITEM###' => $htmlEliteList));
                if (strlen($htmlPreferredCollage) > 0)
                    $htmlPreferredCollage = $this->cObj->substituteMarkerArrayCached($templatePreferredCollage, array(),
                        array('###ITEMS###' => $htmlPreferredCollage));
                if (strlen($htmlPreferredList) > 0)
                    $htmlPreferredList = $this->cObj->substituteMarkerArrayCached($templatePreferredList, array(),
                        array('###ITEMS###' => $htmlPreferredList));
                
                $marker = array(
                    '###SEARCH_FORM###' => $this->makeSelectors(),
                    '###ACTION###'      => htmlspecialchars(t3lib_div::getIndpEnv('REQUEST_URI')),
                );
                $subpartmarker = array(
                    '###ELITE_COLLAGE###'     => $htmlEliteCollage,
                    '###ELITE_LIST###'        => $htmlEliteList,
                    '###PREFERRED_COLLAGE###' => $htmlPreferredCollage,
                    '###PREFERRED_LIST###'    => $htmlPreferredList,
                );
                return $this->cObj->substituteMarkerArrayCached($this->template, $marker, $subpartmarker);
            }
        }
    }

    /**
     * Make selector boxes in the top of page
     */
    function makeSelectors($noServiceBox=0)
    {
        if (!$noServiceBox)
            $fullTable.=$this->makeServiceSelect();
        return '<DIV'.$this->pi_classParam("modeSelector").'>'.
            $fullTable.
            '</DIV>';
    }

    /**
     * Make the selectorbox with categories/services.
     */
    function makeServiceSelect()
    {
        $opt=array();
        reset($this->categories);

        global $TSFE;
        $this->pi_linkTP_keepPIvars($v,array("service"=>"","pointer"=>""));
        $url = $this->cObj->lastTypoLinkUrl;
        if (substr($url, 0, 4) != "http")
            $url = $TSFE->config['config']['baseURL'] . $url;
        $opt[]='<option value="'.htmlentities($url).'">'.$this->pi_getLL('All_services').'</option>';

        while(list($k,$v)=each($this->categories))	{
            $this->pi_linkTP_keepPIvars($v,array("service"=>$k,"pointer"=>""));
            $url = $this->cObj->lastTypoLinkUrl;
            if (substr($url, 0, 4) != "http")
                $url = $TSFE->config['config']['baseURL'] . $url;
            $opt[]='<option value="'.htmlentities($url).'"'.($this->piVars["service"]==$k?" SELECTED":"").'>'.htmlentities($v).'</option>';
        }

#		return '<select onChange="document.location=unescape(\''.rawurlencode($this->cObj->lastTypoLinkUrl).'\')+\'&tx_t3consultancies_pi1[service]=\'+this.options[this.selectedIndex].value;">'.implode("",$opt).'</select>';
        return '<select onChange="document.location=this.options[this.selectedIndex].value;">'.implode("",$opt).'</select>';
    }

    /**
     * Makes a link to/from edit mode
     */
    function makeEditAddButton()
    {
        if ($this->conf["editAdd_enabled"])	{
            if (!$this->editAdd) {
                $this->pi_moreParams="&cmd=edit";
                return '<p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("Edit_or_add_entries"),array("editAdd"=>1),1,1).'</p>';
            } else {
                return '<p>'.$this->pi_linkTP_keepPIvars($this->pi_getLL("Cancel_edit_mode"),array("editAdd"=>""),1,1).'</p>';
            }
        }
    }

    /**
     * Loading the categories into an internal array, $this->categories
     */
    function loadCategories()
    {
        $query = $this->pi_categoriesUsed("tx_t3consultancies_cat","tx_t3consultancies_services_mm","tx_t3consultancies",$this->addWhere);
        $res = mysql(TYPO3_db,$query);

        $this->categories=array();

        while($row=mysql_fetch_assoc($res)) {
            $this->categories[$row["uid"]]=$row["title"];
        }
    }

    function pi_categoriesUsed($cat_table,$mm_table,$table,$addWhere="")
    {
        // Fetches the list of PIDs to select from. 
        // TypoScript property .pidList is a comma list of pids. If blank, current page id is used.
        // TypoScript property .recursive is a int+ which determines how many levels down from the pids in the pid-list subpages should be included in the select.
        $pidList = $this->pi_getPidList($this->conf["pidList"],$this->conf["recursive"]);

        // Begin Query:
        $query="FROM ".$table.",".$cat_table.",".$mm_table.chr(10).
            " WHERE ".$table.".uid=".$mm_table.".uid_local AND ".$cat_table.".uid=".$mm_table.".uid_foreign ".chr(10).
            " AND ".$table.".pid IN (".$pidList.")".chr(10).
            $this->cObj->enableFields($cat_table).
            $this->cObj->enableFields($table).chr(10);
        if ($addWhere)
            $query.=" ".$addWhere.chr(10);
        $query.=" GROUP BY ".$cat_table.".uid";

        $query = "SELECT ".$cat_table.".title,".$cat_table.".uid ".chr(10).$query;
        return $query;
    }

    /**
     * Renders a single entry
     */
    function singleView($content,$conf,$noBack=0)
    {
        if (!$this->internal["currentRow"]["uid"])
            return "No record found!";

        $this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->singleViewOn=1;

        // This sets the title of the page for use in indexed search results:
        if ($this->internal["currentRow"]["title"])	$GLOBALS["TSFE"]->indexedDocTitle=$this->internal["currentRow"]["title"];

        $descr = $this->getFieldContent("description");
        $descr = $descr ? '<P'.$this->pi_classParam("singleViewField-description").'>'.$this->getFieldContent("description").'</P>' : '';

        // Gathering the info table content:
        $contactInfo = array();

        $services = $this->getFieldContent("services");
        if ($services)
            $contactInfo[$this->pi_getLL("listFieldHeader_services")]=$services;

        $tmp=array();
        $tmp[] = $this->getFieldContent("contact_name");
        $tmp[] = $this->getFieldContent("contact_email");
        $contact = implode(", ",t3lib_div::trimExplode(",",implode(",",$tmp),1));
        if ($contact)
            $contactInfo[$this->pi_getLL("listFieldHeader_contact")]=$contact;

        $url = $this->getFieldContent("url");
        if ($url)
            $contactInfo[$this->pi_getLL("listFieldHeader_url")]=$url;

        $contactTbl="";
        reset($contactInfo);
        while(list($k,$v)=each($contactInfo)) {
            $contactTbl.='<tr>
                <td nowrap'.$this->pi_classParam("singleViewField-infoH").'><P>'.$k.'</p></td>
                <td width="95%"><P>'.$v.'</p></td>
                </tr>';
        }
        $contactTbl='<table '.$this->conf["singleView."]["infoTableParams"].$this->pi_classParam("singleViewField-infoT").'>'.$contactTbl.'</table>';

        $feuser = '<P'.$this->pi_classParam("singleViewField-fe-owner-user").'>'.$this->pi_getLL("maintainedBy").' '.$this->getFieldContent("fe_owner_user").'</P>';

        $ref = $this->conf["singleView."]["showRefList"] ? $this->getRefListForRecord() : "";

        $content='<DIV'.$this->pi_classParam("singleView").'>
            <h2>'.$this->getFieldContent("title").'</h2>
            <table '.$this->conf["singleView."]["mainTableParams"].'>
            <tr>
            <td valign=top width="95%">
            '.$contactTbl.'
            '.$descr.'
            '.$ref.'
            '.$feuser.'
            </td>
            <td>
            <img src="clear.gif" width="10" height="1">
            </td>
            <td valign=top>'.
            $this->getImage($this->internal["currentRow"]["logo"],$this->conf["logoImage."]).
            '<BR><img src="clear.gif" width="100" height="1"></td>
            </tr>
            </table>'.
            (!$noBack?'<P>'.$this->pi_list_linkSingle($this->pi_getLL("Back"),0,1,
                                                      array("pointer"=>$this->piVars["pointer"])
                                                     ).'</P>':'').
            '</DIV>'.
            $this->pi_getEditPanel();

        return $content;
    }	

    /**
     * Selects and renders the referencelist for the sponsor.
     */
    function getRefListForRecord()
    {
        if ($this->showRef) {
            $value="";
            $pLR = $this->pidListForReferences();
            if ($pLR) {
                $pLR="pid IN (".$pLR.") AND";
            } else $pLR="";
            $query = "SELECT * FROM tx_t3references WHERE ".$pLR." dev_rel=".intval($this->internal["currentRow"]["uid"]).
                ($this->conf["selectedOnly"]	? ' AND tx_t3references.selected' : '').
                $this->cObj->enableFields("tx_t3references").
                " ORDER BY tx_t3references.weight DESC, tx_t3references.launchdate DESC, tx_t3references.title";

            $res = mysql(TYPO3_db,$query);
#echo mysql_error();

            $marginBetweenRefs = t3lib_div::intInRange($this->conf["singleView."]["showRefList."]["marginBetweenRefs"],1,100,10);
            $marginToImg = t3lib_div::intInRange($this->conf["singleView."]["showRefList."]["marginToImg"],1,100,10);
            $refPage = intval($this->conf["singleView."]["showRefList."]["refPage"]);

            $items=array();
            while ($row=mysql_fetch_assoc($res)) {
                list($srcDump) = t3lib_div::trimExplode(",",$row["screendump"],1);
                $img = $this->getReferencesImage($srcDump,$this->conf["singleView."]["showRefList."]["screenDump."]);
                $descr = t3lib_div::fixed_lgd(strip_tags($row["description"]),200).
                    " ".$this->linkSingleRef($this->pi_getLL("more"),$row["uid"],$refPage);

                $title = $row["title"];

                $uP = parse_url($row["url"]);
                $value = $uP["host"].($uP["path"]&&$uP["path"]!="/"?$uP["path"]:"");
                $value = 'http://'.$value;
                $url = 'URL: <a href="'.$value.'" target="_blank">'.$value.'</a>';

                $items[]='<tr>
                    <td width="95%" valign="top">
                    <h3>'.$title.'</h3>
                    <p>'.$descr.'</p>
                    <p'.$this->pi_classParam("reflist-url").'>'.$url.'</p>
                    </td>
                    <td><img src="clear.gif" width='.$marginToImg.' height=1></td>
                    <td valign="top">'.$img.'</td>
                    </tr>
                    <tr>
                    <td colspan=3><img src="clear.gif" width=1 height='.$marginBetweenRefs.'></td>
                    </tr>';
            }
            if (count($items)) {
                $retVal = '<table '.$this->conf["singleView."]["showRefList."]["tableParams"].'>'.implode("",$items).'</table>';
            } else {
                $retVal = '<p>Currently no references is available for this sponsor.</p>';
            }
            $retVal = '<h2>References:</h2>'.$retVal;
            $retVal = '<div'.$this->pi_classParam("reflist").'>'.$retVal.'</div>';
            return $retVal;
        } else {
            return '<font color="red">Sorry, the references could not be listed, because the reference plugin is not enabled</font>';
        }
    }

    /**
     * Wraps the $str in a link to a single display of the record.
     */	
    function linkSingleRef($str,$uid,$refPage)
    {
        if ($refPage) {
            $this->pi_tmpPageId=$refPage;

            $str = $this->pi_linkTP($str,array("tx_t3references_pi1[showUid]" => $uid),1);
        }
        $this->pi_tmpPageId=0;
        return $str;
    }

    /**
     * Returns the list of items.
     */	
    function pi_list_makelist($res)
    {
        // Make list table header:
        $tRows=array();
        $this->internal["currentRow"]="";
        $tRows[]=$this->pi_list_header();

        // Make list table rows
        $c=0;
        while($this->internal["currentRow"] = mysql_fetch_assoc($res)) {
            $tRows[]=$this->pi_list_row_2($c);
            $c++;
        }

        $out = '<DIV'.$this->pi_classParam("listrow").'><table>'.implode("",$tRows).'</table></DIV>';
        return $out;
    }

    /**
     * Displays the sponsor list:
     */
    function pi_list_row_2($c)
    {
        $editPanel = $this->pi_getEditPanel();
        if ($editPanel)	$editPanel="<TD>".$editPanel."</TD>";
        return '<tr'.($c%2 ? $this->pi_classParam("listrow-odd") : "").'>
            <td valign="top"><P>'.$this->getFieldContent("title").'</P></td>
                <td valign="top"><P>'.$this->getFieldContent("services").'</P></td>
                '.($this->showRef && $this->sitesMade?'<td valign="top" align="center"><P>'.$this->getFieldContent("_ref").'</P></td>':'').'
                <td valign="top"><P>'.$this->getFieldContent("details").'</P></td>
                '.$editPanel.'
                </tr>';
    }

    /**
     * Displays sponsor list header
     */
    function pi_list_header()
    {
        return '<tr'.$this->pi_classParam("listrow-header").'>
            <td nowrap><P>'.$this->getFieldHeader("title").'</P></td>
            <td nowrap><P>'.$this->getFieldHeader("services").'</P></td>
            '.($this->showRef && $this->sitesMade?'<td><P>'.$this->getFieldHeader("references").'</P></td>':'').'
            <td><P>'.$this->getFieldHeader("details").'</P></td>
            </tr>';
    }

    /**
     * Returns processed content for a given fieldname ($fN) from the current row
     */
    function getFieldContent($fN)
    {
        switch($fN) {
            case "details":
                return $this->pi_list_linkSingle($this->pi_getLL("Details"),$this->internal["currentRow"]["uid"],1,array("pointer"=>$this->piVars["pointer"]));
            break;
            case "description":
                $content = implode("<br/>",t3lib_div::trimExplode(chr(10),strip_tags(t3lib_div::fixed_lgd($this->internal["currentRow"]["description"],t3lib_div::intInRange($this->conf["truncate_limit"],1,100000,1000),$this->pi_getLL("trunc")),"<b><i><u><strong><em><a>"),1));
            return $content;
            break;
            case "title":
                $title = $this->internal["currentRow"]["title"];
            if ($this->internal["currentRow"]["url"] && !$this->singleViewOn) {
                $uP = parse_url($this->internal["currentRow"]["url"]);
                $value = $uP["host"].($uP["path"]&&$uP["path"]!="/"?$uP["path"]:"");
                $title = '<a href="http://'.$value.'" target="_blank">'.$title.'</a>';
            }
            return $title;
            break;
            case "services":
                $query = "SELECT uid_foreign FROM tx_t3consultancies_services_mm WHERE uid_local=".intval($this->internal["currentRow"]["uid"])." ORDER BY sorting";
            $res = mysql(TYPO3_db,$query);
            $services=array();
            while($row=mysql_fetch_assoc($res))	{
                if (isset($this->categories[$row["uid_foreign"]])) {
                    $services[]=$this->categories[$row["uid_foreign"]];
                } else {
                    $catRec = $this->pi_getRecord("tx_t3consultancies_cat",$row["uid_foreign"]);
                    $services[]=$catRec["title"];
                }
            }
            return implode($this->singleViewOn?', ':'</BR>',$services);
            break;
            case "_ref":
                if ($this->showRef)	{
                    $value="";
                    $pLR = $this->pidListForReferences();
                    if ($pLR)	{
                        $pLR="pid IN (".$pLR.") AND";
                    } else $pLR="";
                    $query = "SELECT count(*) FROM tx_t3references WHERE ".$pLR." dev_rel=".intval($this->internal["currentRow"]["uid"]).
                        ($this->conf["selectedOnly"]	? ' AND tx_t3references.selected' : '').
                        $this->cObj->enableFields("tx_t3references");
                    $res = mysql(TYPO3_db,$query);
                    if ($row=mysql_fetch_assoc($res)) {
                        $value=$row["count(*)"]?$row["count(*)"]:"-";
                    }
                    return $value;
                }
            break;
            case "contact_email":
                if ($this->internal["currentRow"][$fN]) {
                    return $this->pi_linkToPage($this->internal["currentRow"][$fN],$this->internal["currentRow"][$fN]);
                } else return false;
            break;
            case "fe_owner_user":
                $fe_user = $this->pi_getRecord("fe_users",$this->internal["currentRow"][$fN]);
            if (is_array($fe_user)) {
                return '<strong>'.$fe_user["username"].'</strong>';
            } else {
                return '<em>'.$this->pi_getLL("NA").'</em>';
            }
            break;
            case "url":
                if ($this->internal["currentRow"]["url"]) {
                    $uP = parse_url($this->internal["currentRow"]["url"]);
                    $value = $uP["host"].($uP["path"]&&$uP["path"]!="/"?$uP["path"]:"");
                    $value = '<a href="http://'.$value.'" target="_blank">'.$value.'</a>';
                    return $value;
                }
            break;
            default:
                return $this->internal["currentRow"][$fN];
            break;
        }
    }

    /**
     * Returns a list of integer-pids for selecting the references belonging to sponsors
     */
    function pidListForReferences()
    {
        $v= implode(",",t3lib_div::intExplode(",",$this->conf["pidList_references"]));
        return $v;
    }

    /**
     * Returns the header text of a field (from locallang)
     */
    function getFieldHeader($fN)
    {
        switch($fN) {
            default:
                return $this->pi_getLL("listFieldHeader_".$fN,"[".$fN."]");
                break;
        }
    }

    /**
     * Returns an image given by $TSconf
     */
    function getImage($filename,$TSconf) 
    {
        list($theImage)=explode(",",$filename);
        $TSconf["file"] = "uploads/tx_t3consultancies/".$theImage;

        $img = $this->cObj->IMAGE($TSconf);
        return $img;
    }

    /**
     * Returns an image given by $TSconf for references
     */
    function getReferencesImage($filename,$TSconf)  
    {
        list($theImage)=explode(",",$filename);
        $TSconf["file"] = "uploads/tx_t3references/".$theImage;

        $img = $this->cObj->IMAGE($TSconf);
        return $img;
    }

    /**
     * Makes the editing form for submitting information by frontend users.
     */
    function getFe_adminLibConf()
    {
        $feAConf = $this->conf["fe_adminLib."];
        $feAConf["templateContent"]='

            <!-- ###TEMPLATE_EDIT### -->
            <h3>Edit "###FIELD_title###"</h3>
            <table border=0 cellpadding=1 cellspacing=2>
            <FORM name="tx_t3consultancies_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
            '.$this->makeFormFromConfig($feAConf["edit."],$feAConf["table"]).'
            <tr>
            <td></td>
            <td></td>
            <td>
###HIDDENFIELDS###
            <input type="Submit" name="submit" value="'.$this->pi_getLL("feAL_save").'">
            </td>
            </tr>
            </FORM>
            </table>
            <p>&nbsp;</p>
            <p style="color: red;"><a href="###FORM_URL###&cmd=delete&backURL=###FORM_URL_ENC###&rU=###REC_UID###" onClick="return confirm(\'Are you sure?\');">'.$this->pi_getLL("feAL_delete").'</a></p>
            <p>&nbsp;</p>
            <!-- ###TEMPLATE_EDIT### end-->


            <!-- ###TEMPLATE_EDIT_SAVED### begin-->
            <h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
            <p>'.$this->pi_getLL("feAL_contentSaved").'</p>
            <p>&nbsp;</p>
            <!-- ###TEMPLATE_EDIT_SAVED### end-->


            <!-- ###TEMPLATE_CREATE_LOGIN### -->
            <h3>Create new sponsor entry</h3>
            <table border=0 cellpadding=1 cellspacing=2>
            <FORM name="tx_t3consultancies_form" method="post" action="###FORM_URL###" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">
            '.$this->makeFormFromConfig($feAConf["create."],$feAConf["table"]).'
            <tr>
            <td></td>
            <td></td>
            <td>
###HIDDENFIELDS###
            <input type="Submit" name="submit" value="'.$this->pi_getLL("feAL_save").'">
            </td>
            </tr>
            </FORM>
            </table>
            <!-- ###TEMPLATE_CREATE_LOGIN### end-->


            <!-- ###TEMPLATE_CREATE_SAVED### begin-->
            <h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
            <p>'.$this->pi_getLL("feAL_contentSaved").'</p>
            <p>&nbsp;</p>
            <!-- ###TEMPLATE_CREATE_SAVED### end-->







            <!-- ###TEMPLATE_DELETE_SAVED### begin-->
            <h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
            <p>'.$this->pi_getLL("feAL_deleteSaved").'</p>
            <p>&nbsp;</p>
            <!-- ###TEMPLATE_DELETE_SAVED### end-->





            <!-- ###TEMPLATE_EDITMENU### begin -->
            <h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
            <p>'.$this->pi_getLL("feAL_listOfItems").'</p>
            <p>--</p>
            <!-- ###ALLITEMS### begin -->
            <!-- ###ITEM### begin -->
            <p><a href="###FORM_URL###&rU=###FIELD_uid###&cmd=edit">###FIELD_title###</a></p>
            <!-- ###ITEM### end -->
            <!-- ###ALLITEMS### end -->
            <p>--</p>
            <p><a href="###FORM_URL###&cmd=">'.$this->pi_getLL("feAL_createNew").'</a></p>
            <p>&nbsp;</p>
            <!-- ###TEMPLATE_EDITMENU### -->

            <!-- ###TEMPLATE_EDITMENU_NOITEMS### begin -->
            <h3>'.$this->pi_getLL("Managing_consultancies").'</h3>
            <p>'.$this->pi_getLL("feAL_noItems").'</p>
            <p><a href="###FORM_URL###&cmd=">'.$this->pi_getLL("feAL_createNew").'</a></p>
            <p>&nbsp;</p>
            <!-- ###TEMPLATE_EDITMENU_NOITEMS### -->









            <!-- ###EMAIL_TEMPLATE_CREATE_SAVED-ADMIN### begin -->
            New sponsor created.

            <!--###SUB_RECORD###-->
            Title: ###FIELD_title###
            Description: ###FIELD_description###
            Contact email: ###FIELD_contact_email###
            Contact name: ###FIELD_contact_name###


            Approve:
###THIS_URL######FORM_URL######SYS_SETFIXED_approve###

Delete:
###THIS_URL######FORM_URL######SYS_SETFIXED_DELETE###
            <!--###SUB_RECORD###-->
            <!-- ###EMAIL_TEMPLATE_CREATE_SAVED-ADMIN### end-->

            <!-- ###EMAIL_TEMPLATE_EDIT_SAVED-ADMIN### begin -->
            Sponsor record edited.

            <!--###SUB_RECORD###-->
            Title: ###FIELD_title###
            Description: ###FIELD_description###
            Contact email: ###FIELD_contact_email###
            Contact name: ###FIELD_contact_name###


            Approve:
###THIS_URL######FORM_URL######SYS_SETFIXED_approve###

Delete:
###THIS_URL######FORM_URL######SYS_SETFIXED_DELETE###
            <!--###SUB_RECORD###-->
            <!-- ###EMAIL_TEMPLATE_EDIT_SAVED-ADMIN### end-->



            <!-- ###EMAIL_TEMPLATE_SETFIXED_DELETE### begin -->
            Sponsor DELETED!

            <!--###SUB_RECORD###-->
            Title: ###FIELD_title###
            Description: ###FIELD_description###

            Your entry has been deleted by the admin for some reason.

            - kind regards.
            <!--###SUB_RECORD###-->
            <!-- ###EMAIL_TEMPLATE_SETFIXED_DELETE### begin -->



            <!-- ###EMAIL_TEMPLATE_SETFIXED_approve### begin -->
            Sponsor approved

            <!--###SUB_RECORD###-->
            Title: ###FIELD_title###
            Description: ###FIELD_description###

            Your sponsor entry has been approved! 

            - kind regards.
            <!--###SUB_RECORD###-->
            <!-- ###EMAIL_TEMPLATE_SETFIXED_approve### begin -->













            <!-- ###TEMPLATE_SETFIXED_OK### -->
            <h3>Setfixed succeeded</h3>
            Record uid; ###FIELD_uid###
            <!-- ###TEMPLATE_SETFIXED_OK### end-->

            <!-- ###TEMPLATE_SETFIXED_OK_DELETE### -->
            <h3>Setfixed delete record "###FIELD_uid###"</h3>
            <!-- ###TEMPLATE_SETFIXED_OK_DELETE### end-->

            <!-- ###TEMPLATE_SETFIXED_FAILED### -->
            <h3>Setfixed failed!</h3>
            <p>May happen if you click the setfixed link a second time (if the record has changed since the setfixed link was generated this error will happen!)</p>
            <!-- ###TEMPLATE_SETFIXED_FAILED### end-->



            <!-- ###TEMPLATE_AUTH### -->
            <h3>Authentication failed</h3>
            <p>Of some reason the authentication failed. </p>
            <!-- ###TEMPLATE_AUTH### end-->

            <!-- ###TEMPLATE_NO_PERMISSIONS### -->
            <h3>No permissions to edit record</h3>
            <p>Sorry, you did not have permissions to edit the record.</p>
            <!-- ###TEMPLATE_NO_PERMISSIONS### end-->

            ';

        $feAConf["addParams"]=$this->conf["parent."]["addParams"].t3lib_div::implodeArrayForUrl($this->prefixId,$this->piVars,"",1);
        return $feAConf;
    }

    /**
     * 
     */
    function makeFormFromConfig($conf,$table)
    {
#	debug($table);
        $fields = array_unique(t3lib_div::trimExplode(",",$conf["fields"],1));
        $reqFields = array_unique(t3lib_div::trimExplode(",",$conf["required"],1));
#debug($conf);

        $tableRows = array();
        while(list(,$fN)=each($fields))	{
            $fieldCode = $this->getFormFieldCode($fN,'FE['.$table.']['.$fN.']');
            if ($fieldCode)	{
                if (in_array($fN,$reqFields))	{
                    $reqMsg='<!--###SUB_REQUIRED_FIELD_'.$fN.'###--><p style="color: red; font-weight: bold;">'.$this->pi_getLL("feAL_required").'</p><!--###SUB_REQUIRED_FIELD_'.$fN.'###-->';
                    $reqMarker=$this->pi_getLL("feAL_requiredMark");
                } else {
                    $reqMsg='';
                    $reqMarker='';
                }

                $tableRows[]='<tr>
                    <td><p>'.$this->pi_getLL("feAL_fN_".$fN,"feAL_fN_".$fN).' '.$reqMarker.'</p></td>
                    <td><img src="clear.gif" width=10 height=1></td>
                    <td>'.$reqMsg.$fieldCode.'</td>
                    </tr>';
            }
        }

        return implode(chr(10),$tableRows);
#	debug($tableRows);
    }

    function getFormFieldCode($fN,$fieldName)
    {
        switch($fN)	{
            case "description":
                return '<textarea name="'.$fieldName.'" rows="5" wrap="virtual" style="width: 300px;"></textarea>';
            break;
            case "logo":
                return '<input type="file" name="'.$fieldName.'[]" style="width: 300px;">';
            break;
            case "hidden":
                return "";
            break;
            case "services":
                $opt=array();
            $query = "SELECT uid,title FROM tx_t3consultancies_cat WHERE 1=1 ".
                $this->cObj->enableFields("tx_t3consultancies_cat").
                " ORDER BY title";
            $res = mysql(TYPO3_db,$query);
            while($row=mysql_fetch_assoc($res)) {
                $opt[]='<option value="'.$row["uid"].'">'.$row["title"].'</option>';
            }
            return '<select name="'.$fieldName.'[]" multiple size='.count($opt).'>'.implode("",$opt).'</select>';
            break;
            default:
            return '<input type="text" name="'.$fieldName.'" style="width: 300px;">';
            break;
        }
    }


    function afterSave($content,$conf)
    {
        $inVar = t3lib_div::GPvar("FE",1);
        $services = $inVar["tx_t3consultancies"]["services"];
        $uid = $content["rec"]["uid"];

        if (intval($uid)>0 && is_array($services)) {
            $query = "DELETE FROM tx_t3consultancies_services_mm WHERE uid_local=".intval($uid);
            $res = mysql(TYPO3_db,$query);

            if (is_array($services)) {
                reset($services);
                while(list($k,$sId)=each($services)) {
                    $query = "INSERT INTO tx_t3consultancies_services_mm (uid_local,uid_foreign,tablenames,sorting) VALUES (".intval($uid).",".intval($sId).",'',".intval($k).")";
                    $res = mysql(TYPO3_db,$query);
                }
            }
        }
    }
    function updateArray($content,$conf)
    {

        $content["services"]=array();
        $query = "SELECT uid_foreign FROM tx_t3consultancies_services_mm WHERE uid_local=".intval($content["uid"]);
        $res = mysql(TYPO3_db,$query);
        while($row=mysql_fetch_assoc($res))	{
            $content["services"][]=$row["uid_foreign"];
        }

        unset($content["weight"]);
        unset($content["selected"]);

        return $content;
    }
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3consultancies/pi1/class.tx_t3consultancies_pi1.php"])	{
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3consultancies/pi1/class.tx_t3consultancies_pi1.php"]);
}

?>
