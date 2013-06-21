<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2002 Kasper Sk?rh?j (kasper@typo3.com)
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
 * Plugin 'Newsletter Subscription Management' for the 'da_newsletter_subscription' extension.
 *
 * @author    Kasper Sk?rh?j <kasper@typo3.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");

class tx_danewslettersubscription_pi1 extends tslib_pibase {
    var $prefixId = "tx_danewslettersubscription_pi1";        // Same as class name
    var $scriptRelPath = "pi1/class.tx_danewslettersubscription_pi1.php";    // Path to this script relative to the extension dir.
    var $extKey = "da_newsletter_subscription";    // The extension key.

    var $emailAuth = array();
    var $deleted = 'tx_danewslettersubscription_furels-deleted';
    /**
     * Main function
     */
    function main($content,$conf)    {
            // If no static template is included, show this error message:
        if (!$conf["_static_included"])    {
            return $this->pi_wrapInBaseClass('
            <div style="border: 1px solid black; background-color: red; padding: 5px 5px 5px 5px;">
                <p style="color:white;"><strong>Newsletter Subscription Plugin is not available for use</strong></p>
                <p style="color:white;">Before you can use the subscription form ask your administrator to add the Newsletter Subscription static template:</p>
                <img src="'.t3lib_extMgm::siteRelPath("da_newsletter_subscription").'pi1/template.gif" width="489" height="170" border="0" alt="">
                <p>More information about configuration of this plugin can be found at <a href="http://typo3.org/doc+M58d9bafda6e.0.html">http://typo3.org/doc+M58d9bafda6e.0.html</a></p>
            </div>
            ');
        }

            // Otherwise proceed:
        if (strstr($this->cObj->currentRecord,"tt_content"))    {
            $conf["pidList"] = $this->cObj->data["pages"];
            $conf["recursive"] = $this->cObj->data["recursive"];
        }
        return $this->pi_wrapInBaseClass($this->listView($content,$conf));
    }

    /**
     * Listing categories
     */
    function listView($content,$conf)    {
        $this->conf=$conf;        // Setting the TypoScript passed to this function in $this->conf
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();        // Loading the LOCAL_LANG values

        if ($this->piVars["e"] && $this->piVars["m"] && $this->md5int($this->piVars["e"]) == $this->piVars["m"])    {
            $this->emailAuth=array($this->piVars["e"],$this->piVars["m"]);
        }
        if (!$this->piVars["e"] && isset($this->piVars["DATA"]["info"]['Email'])) {
			$this->piVars["e"] = $this->piVars["DATA"]["info"]['Email'];
			$this->piVars["m"] = $this->md5int($this->piVars["e"]);
            $this->emailAuth=array($this->piVars["e"],$this->piVars["m"]);
        	$this->piVars["DATA"]["subscribe_email"] = $this->piVars["e"];
        }

        $authCode = $this->isAuthenticated();
            // SET data and GET subscriptions
        $this->subScriptions=array();
        if ($authCode && is_array($this->piVars["DATA"]) && is_array($this->piVars["DATA"]["signup"]))    {
            reset($this->piVars["DATA"]["signup"]);
            while(list($catUid,$mode)=each($this->piVars["DATA"]["signup"]))    {
                if ( ! $_SESSION[$this->deleted] ) {
					$query="DELETE FROM tx_danewslettersubscription_furels WHERE fe_user=".$this->getAuthId()." AND newsletter_cat=".intval($catUid);
                    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
				}
                if ($mode && !isset($this->piVars["DATA"]["unsubscribe"]))    {
                    $query="INSERT INTO tx_danewslettersubscription_furels (fe_user,newsletter_cat,datacontent,email) VALUES (".$this->getAuthId().", ".intval($catUid).",'".addslashes(serialize($this->piVars["DATA"]["info"]))."','".addslashes($this->piVars["e"])."');";
                    $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
                }
            }
            $_SESSION[$this->deleted] = true;
        }


            // Select for listing:
        $pidList = $this->pi_getPidList($this->conf["pidList"],$this->conf["recursive"]);
        $query="SELECT * FROM tx_danewslettersubscription_cat WHERE pid IN (".$pidList.")".
                $this->cObj->enableFields("tx_danewslettersubscription_cat").
                " ORDER BY ".$this->returnSortingField().($this->conf["sorting_desc"]?" DESC":"");

        $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
        if ($GLOBALS['TYPO3_DB']->sql_error())    debug(array($GLOBALS['TYPO3_DB']->sql_error(),$query));
        $catList="0";
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))    {
            $catList.=",".$row["uid"];
        }
        @$GLOBALS['TYPO3_DB']->sql_data_seek($res,0);




            // GET subscription data:
        if ($authCode)    {
            $defaultInfoSettings=is_array($this->piVars["DATA"]["info"]) ? $this->piVars["DATA"]["info"] : array();
            $query="SELECT * FROM tx_danewslettersubscription_furels WHERE fe_user=".$this->getAuthId()." AND newsletter_cat IN (".$catList.")";
            $res2 = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
            while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))    {
                $this->subScriptions[$row["newsletter_cat"]]=1;

                $defaultInfoSettings = unserialize($row["datacontent"]);        // The settings are REDUNDANTLY stored in each subscription record for the page. The last entry will determine the default settings.
            }
        }
#debug($defaultInfoSettings);


            // Making the form with the additional info fields:
        $formConf = $this->conf["formCObject."];

            // Unset data from formconf:
        unset($formConf["data"]);
        unset($formConf["data."]);

            // Set data from bodytext field + default values.
        $formData=t3lib_div::trimExplode(chr(10),$this->cObj->data["bodytext"],1);
        reset($formData);
        while(list($kk,$vv)=each($formData))    {
            $formData[$kk]=t3lib_div::trimExplode("|",$vv);
            $cfgParts = $this->getFieldDataFromConfigLine($formData[$kk]);

            if (isset($defaultInfoSettings[$cfgParts["fieldname"]]))    {
                switch($cfgParts["type"])    {
                    case "select":
                    case "radio":
                        $valueParts = explode(",",$formData[$kk][2]);

                        for($a=0;$a<count($valueParts);$a++)    {
                            $valueParts[$a]=trim($valueParts[$a]);
                            if (substr($valueParts[$a],0,1)=="*")    {    // Finding default value
                                $valueParts[$a] = trim(substr($valueParts[$a],1));
                            }
                            if (is_array($defaultInfoSettings[$cfgParts["fieldname"]]))    {
                                if (in_array($valueParts[$a],$defaultInfoSettings[$cfgParts["fieldname"]]))        $valueParts[$a]="*".$valueParts[$a];
                            } elseif (!strcmp($defaultInfoSettings[$cfgParts["fieldname"]],$valueParts[$a])) {
                                $valueParts[$a]="*".$valueParts[$a];
                            }
                        }

#                        debug($defaultInfoSettings[$cfgParts["fieldname"]],1);
#                        debug($formData[$kk][2]);
#                        debug($valueParts);
                        $formData[$kk][2] = implode(",",$valueParts);
                    break;
                    default:
                        $formData[$kk][2]=$defaultInfoSettings[$cfgParts["fieldname"]];
                    break;
                }
            }
        }

        $formCode = $this->cObj->FORM($formConf,$formData);
#debug($formCode);
            // Adds the whole list table
        $listing=$this->pi_list_makelist($res,$this->conf["tableParams_list"]);

            // Send email based on subscription/unsubscription:
        if (isset($this->piVars["DATA"]["subscribe"]) || isset($this->piVars["DATA"]["unsubscribe"]))    {

            /*----------------Send email to admin about subscribe/Unsibscribe----------------------begin-----*/

            $pre = isset($this->piVars["DATA"]["subscribe"]) ? "" : "Un";
            $signup = $this->piVars["DATA"]["signup"];
            $info = $this->piVars["DATA"]["info"];
            foreach ($signup as $key => $value)
            {
                $val = $value ? "Yes" : "No";
                $new_msg .= $key.": ".$val." \n";//.($value ? 'Yes' : 'No');
                $new_msg .= "\n";
            }
            foreach ($info as $key => $value)
            {
                $new_msg .= $key.': '.$value."\n";
            }

            $this->cObj->sendNotifyEmail($pre."Subscribe\n".$new_msg, $this->conf['mailto'], "", $this->conf["email_from"], $this->conf["email_fromName"], $this->conf["replyTo"]);
            header("Location: ".t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->pi_getPageLink($this->conf['redirect'] , '', array()));
            exit;

            /*----------------Send email to admin about subscribe/Unsibscribe----------------------end-------*/



            if (count($this->emailAuth))    {
                $adminLink = t3lib_div::getIndpEnv("TYPO3_REQUEST_DIR").$this->pi_linkTP_keepPIvars_url(array("e"=>$this->emailAuth[0], "m"=>$this->emailAuth[1]));

                $pre = isset($this->piVars["DATA"]["subscribe"]) ? "" : "Un";
                $msg=
                    ($this->conf["email".$pre."SubscribedSubject"]?$this->conf["email".$pre."SubscribedSubject"]:$this->pi_getLL("email".$pre."SubscribedSubject")).chr(10).
                    (sprintf($this->conf["email".$pre."SubscribedMessage"]?$this->conf["email".$pre."SubscribedMessage"]:$this->pi_getLL("email".$pre."SubscribedMessage"),
                        $adminLink
                    ));

                if ($this->conf["mode."]["sendNotificationWhenEmailMode"])    $this->cObj->sendNotifyEmail($msg, $this->emailAuth[0], "", $this->conf["email_from"], $this->conf["email_fromName"], $this->conf["replyTo"]);
            }
        }





        if (isset($this->piVars["DATA"]["subscribe"]))    {
            $fullTable.='<p'.$this->pi_classParam("subscr").'>'.$this->pi_getLL("nowSubscribed").'</p>';
        } elseif (isset($this->piVars["DATA"]["unsubscribe"]))    {
            $fullTable.='<p'.$this->pi_classParam("subscr").'>'.$this->pi_getLL("nowUnSubscribed").'</p>';
        } elseif ($this->piVars["DATA"]["subscribe_email"])    {    //
            if (t3lib_div::validEmail($this->piVars["DATA"]["subscribe_email"]))    {
                $redirect="";
                if (t3lib_div::inList("1,2",$this->conf["mode."]["noAuthEmail"]))    {
                        // See if the submitted email has subscriptions:
                    $query="SELECT * FROM tx_danewslettersubscription_furels WHERE fe_user=".intval(-$this->md5int($this->piVars["DATA"]["subscribe_email"]))." AND newsletter_cat IN (".$catList.")";
                    $res2 = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
                    if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res2) || $this->conf["mode."]["noAuthEmail"]==2)    {
                        $redirect=1;
                    }
                }
                $authLink = t3lib_div::getIndpEnv("TYPO3_REQUEST_DIR").$this->pi_linkTP_keepPIvars_url(array("e"=>$this->piVars["DATA"]["subscribe_email"], "m"=>$this->md5int($this->piVars["DATA"]["subscribe_email"])));
                if ($redirect)    {
                    header("Location: ".t3lib_div::locationHeaderUrl($authLink));
                    exit;
                } else {
                    $msg=
                        ($this->conf["emailSubject"]?$this->conf["emailSubject"]:$this->pi_getLL("emailSubject")).chr(10).
                        (sprintf($this->conf["emailMessage"]?$this->conf["emailMessage"]:$this->pi_getLL("emailMessage"),
                            $authLink
                        ));

                    $this->cObj->sendNotifyEmail($msg, $this->piVars["DATA"]["subscribe_email"], "", $this->conf["email_from"], $this->conf["email_fromName"], $this->conf["replyTo"]);
                    $fullTable.='<p'.$this->pi_classParam("emailAdmin").'>'.sprintf($this->pi_getLL("emailSent_msg"), $this->piVars["DATA"]["subscribe_email"]).'</p>';
                }
            } else {
                $fullTable.='<p'.$this->pi_classParam("emailAdmin").'>'.$this->pi_getLL("emailInvalid_msg").'</p>';
            }
        } elseif ($authCode)    {
            $id = $this->getAuthId();
            /*if ($id>0)    {
                $fullTable.='<p'.$this->pi_classParam("premsg").'>'.sprintf($this->pi_getLL("authAsUser"), '<em>'.$GLOBALS["TSFE"]->fe_user->user["username"].'</em>').'</p>';
            } else {
                $fullTable.='<p'.$this->pi_classParam("premsg").'>'.sprintf($this->pi_getLL("authAsEmail"), '<em>'.$this->emailAuth[0].'</em>').'</p>';
            }*/

            $fullTable.='<form action="" method="post" style="margin: 0 0 0 0;" name="'.$formCode["formname"].'" '.$formCode["validateForm"].'>'.
                $listing.
                $formCode[1].'
            <input type="image" src="/fileadmin/images/buttons/subscribe.gif" name="'.$this->prefixId.'[DATA][subscribe]" value="'.$this->pi_getLL("signup_submit").'"> '.($this->conf["add_unsubscribe"]?'<input type="image" src="/fileadmin/images/buttons/unsubscribe.gif" name="'.$this->prefixId.'[DATA][unsubscribe]" value="'.$this->pi_getLL("unsubscribe_submit").'">':'').'
            </form>';
        } else {
             $fullTable.='<form action="" method="post" style="margin: 0 0 0 0;" name="'.$formCode["formname"].'"'.$formCode["validateForm"].'>'.
                $listing.
                $formCode[1].'
            <input type="image" src="/fileadmin/images/buttons/subscribe.gif" name="'.$this->prefixId.'[DATA][subscribe]" value="'.$this->pi_getLL("signup_submit").'"> '.($this->conf["add_unsubscribe"]?'<input type="image" src="/fileadmin/images/buttons/unsubscribe.gif" name="'.$this->prefixId.'[DATA][unsubscribe]" value="'.$this->pi_getLL("unsubscribe_submit").'">':'').'
            </form>';

        }

            // Returns the content from the plugin.
        return $fullTable;
    }

    /**
     * List row
     */
    function pi_list_row($c)    {
        $editPanel = $this->pi_getEditPanel();
        $checkBox='<input type="hidden"
		name="'.$this->prefixId.'[DATA][signup]['.$this->internal["currentRow"]["uid"].']"
		value="0"><input type="checkbox"
		name="'.$this->prefixId.'[DATA][signup]['.$this->internal["currentRow"]["uid"].']" value="1"'.($this->subScriptions[$this->internal["currentRow"]["uid"]]?' CHECKED':'').'>';

        return '<tr'.$this->pi_classParam("listrow-header").'>
                <td align="right" nowrap><p>'.$checkBox.'</p></td><td width="95%"><P>'.$this->getFieldContent("title").'</P></td>
            </tr>';
    }

    /**
     * List header
     */
    function pi_list_makelist($res,$tableParams="")    {
            // Make list table header:
        $tRows=array();
        $this->internal["currentRow"]="";
        $tRows[]=$this->pi_list_header();

            // Make list table rows
        $c=0;
        while($this->internal["currentRow"] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))    {
            $tRows[]=$this->pi_list_row($c);
            $c++;
        }

        $out = '<DIV'.$this->pi_classParam("listrow").'><'.trim('table '.$tableParams).'>'.implode("",$tRows).'</table></DIV>';
        return $out;
    }

    /**
     * List header
     */
    function pi_list_header()    {
        return '';
/*
        return '<tr'.$this->pi_classParam("listrow-header").'>
                <td><P>'.$this->getFieldHeader("title").'</P></td>
                <td><P>'.$this->getFieldHeader("descr").'</P></td>
            </tr>';
*/    }

    /**
     * Get content for a field
     */
    function getFieldContent($fN)    {
        switch($fN) {
            default:
                return $this->internal["currentRow"][$fN];
            break;
        }
    }

    /**
     * Get header label
     */
    function getFieldHeader($fN)    {
        switch($fN) {
            default:
                return $this->pi_getLL("listFieldHeader_".$fN,"[".$fN."]");
            break;
        }
    }

    /**
     * Parses a config line from the form configuration into an array.
     */
    function getFieldDataFromConfigLine($parts)    {
        $confData=array();

            // label:
        $confData["label"] = trim($parts[0]);
            // field:
        $fParts = explode(",",$parts[1]);
        $fParts[0]=trim($fParts[0]);
        if (substr($fParts[0],0,1)=="*")    {
            $confData["required"]=1;
            $fParts[0] = substr($fParts[0],1);
        }

        $typeParts = explode("=",$fParts[0]);
        $confData["type"] = trim(strtolower(end($typeParts)));
        if (count($typeParts)==1)    {
            $confData["fieldname"] = substr(ereg_replace("[^a-zA-Z0-9_]","",str_replace(" ","_",trim($parts[0]))),0,30);
            if (strtolower(ereg_replace("[^[:alnum:]]","",$confData["fieldname"]))=="email")    {$confData["fieldname"]="email";}
        } else {
            $confData["fieldname"] = str_replace(" ","_",trim($typeParts[0]));
        }


        switch($confData["type"])    {
            case "select":
            case "radio":
                $valueParts = explode(",",$parts[2]);

                for($a=0;$a<count($valueParts);$a++)    {
                    $valueParts[$a]=trim($valueParts[$a]);
                    if (substr($valueParts[$a],0,1)=="*")    {    // Finding default value
                        $valueParts[$a] = trim(substr($valueParts[$a],1));
                    }
                }

                $confData["options"] = implode(",",$valueParts);
            break;
        }

        return $confData;
    }

    /**
     *
     */
    function returnSortingField()    {
        if (t3lib_div::inList("sorting,starttime,endtime,uid,crdate,title,description",$this->conf["sorting_field"]))    {
            return $this->conf["sorting_field"];
        } else return "sorting";
    }

    /**
     *
     */
    function isAuthenticated()    {
        if (((string)$this->conf["mode"]=="login" || (string)$this->conf["mode"]=="dual") && $GLOBALS["TSFE"]->loginUser)    return "login";
        if ((!$this->conf["mode"] || (string)$this->conf["mode"]=="dual") && count($this->emailAuth))    return "email";
    }

    /**
     * If the authentication is of "login" type then returns the fe_users.uid, otherwise the md5int hash of the email address is returned in negative integer.
     */
    function getAuthId()    {
        return intval($this->isAuthenticated()=="login"?$GLOBALS["TSFE"]->fe_user->user["uid"]:-$this->emailAuth[1]);
    }

    /**
     *
     */
    function md5int($str)    {
        return hexdec(substr(md5($str),0,7));
    }
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi1/class.tx_danewslettersubscription_pi1.php"])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/pi1/class.tx_danewslettersubscription_pi1.php"]);
}

?>