<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002 Kasper Skårhøj (kasper@typo3.com)
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
 * Module 'NL Subscribers' for the 'da_newsletter_subscription' extension.
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

	

class tx_danewslettersubscription_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * 
	 */
	function init()	{
		global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();

		/*
		if (t3lib_div::GPvar("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"1" => $LANG->getLL("function1"),
				"2" => $LANG->getLL("function2"),
			)
		);
		parent::menuConfig();
	}

		// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $AB,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$HTTP_GET_VARS,$HTTP_POST_VARS,$CLIENT,$TYPO3_CONF_VARS;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{
	
				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript">
					script_ended = 1;
					if (top.theMenu) top.theMenu.recentuid = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->php3Lang["labels"]["path"].": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);

			
			// Render content:
			$this->moduleContent();

			
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}
		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
		
			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{
		global $SOBE;

		$this->content.=$this->doc->middle();
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		global $LANG;

#debug($this->modTSconfig);

			// Find any newletter categories:
		$query="SELECT * FROM tx_danewslettersubscription_cat WHERE pid=".intval($this->id).
			t3lib_BEfunc::deleteClause("tx_danewslettersubscription_cat").
			" ORDER BY sorting";
		$res = mysql(TYPO3_db,$query);
		if (!mysql_num_rows($res))	{
			$query="SELECT count(*),pid FROM tx_danewslettersubscription_cat WHERE 1=1".
				t3lib_BEfunc::deleteClause("tx_danewslettersubscription_cat").
				" GROUP BY pid";
			$res = mysql(TYPO3_db,$query);
			$links=array();
			while($row=mysql_fetch_assoc($res))	{
				if ($GLOBALS["BE_USER"]->isInWebMount($row["pid"]))	{
					$pRec = t3lib_BEfunc::getRecord("pages",$row["pid"]);
					$links[]='<a href="index.php?id='.$pRec["uid"].'"><img src="'.$GLOBALS["BACK_PATH"].'t3lib/gfx/i/pages.gif" width="18" height="16" border="0" alt="" align="absmiddle">'.htmlspecialchars($pRec["title"]).'</a><BR>';
				}
			}
			if (count($links))	{
				$this->content.=$this->doc->section($LANG->getLL("function1").":",implode("",$links),0,1);
			} else {
				$content='There were no pages with newsletter categories found!';
				$this->content.=$this->doc->section("Pages with newletter categories:",$content,0,1);
			}
		} else {
			switch((string)$this->MOD_SETTINGS["function"])	{
				case 1:
					if (t3lib_div::GPvar("NLcatUid"))	{
						$emails=array();

							// Selecting fe_users
						$query="SELECT fe_users.email FROM fe_users,tx_danewslettersubscription_furels 
							WHERE fe_users.uid=tx_danewslettersubscription_furels.fe_user AND 
								tx_danewslettersubscription_furels.newsletter_cat=".intval(t3lib_div::GPvar("NLcatUid")).
								t3lib_BEfunc::deleteClause("fe_users")." AND NOT fe_users.disable".
								" GROUP BY fe_users.email";
	#					debug($query);
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						while($row=mysql_fetch_assoc($res))	{
							$emails[]=$row["email"];
						}
						
							// Selecting NON-fe_users
						$query="SELECT email FROM tx_danewslettersubscription_furels 
							WHERE tx_danewslettersubscription_furels.fe_user<0 AND 
								tx_danewslettersubscription_furels.newsletter_cat=".intval(t3lib_div::GPvar("NLcatUid")).
								" GROUP BY email";
	#					debug($query);
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						while($row=mysql_fetch_assoc($res))	{
							if (trim($row["email"]))	{
								$emails[]=$row["email"];
							}
						}
						
						// UNIQUEify:
						$emails = array_unique($emails);
						
						
						$content.=$LANG->getLL("listEmail").' ('.count($emails).')<BR>';
						$content.='<textarea cols="100%" rows="10">'.implode(", ",$emails).'</textarea>';
						$content.='<BR><a href="index.php?id='.$this->id.'"><strong>'.$LANG->getLL("goBack").'</strong></a>';
					} else {
						$rows=array();
						$rows[]='<tr bgcolor="'.$this->doc->bgColor4.'">
							<td><strong>'.$LANG->getLL("ntitle").':</strong></td>
							<td><strong>'.$LANG->getLL("descr").':</strong></td>
							<td><strong>'.$LANG->getLL("subscr").':</strong></td>
						</tr>';
		
						$query="SELECT * FROM tx_danewslettersubscription_cat WHERE pid=".intval($this->id).
							t3lib_BEfunc::deleteClause("tx_danewslettersubscription_cat").
							" ORDER BY sorting";
						$res = mysql(TYPO3_db,$query);
						while($row=mysql_fetch_assoc($res))	{
							$query="SELECT count(*) FROM tx_danewslettersubscription_furels WHERE newsletter_cat=".intval($row["uid"]);
							$res_c = mysql(TYPO3_db,$query);
							list($count) = mysql_fetch_row($res_c);
		
							$rows[]='<tr bgcolor="'.$this->doc->bgColor5.'">
								<td valign="top">'.$row["title"].'</td>
								<td valign="top">'.$row["descr"].'</td>
								<td valign="top" align="center">'.$count.' <a href="index.php?id='.intval($this->id).'&NLcatUid='.$row["uid"].'">['.$LANG->getLL("list").']</a></td>
								</tr>';
						}
						
						$content='<table border=0 cellpadding=2 cellspacing=1>'.implode(chr(10),$rows).'</table>';
					}
					$this->content.=$this->doc->section($LANG->getLL("function1"),$content,0,1);
				break;
					// Make an extract of subscribers to the newletter based on settings from the tt_content record.
				case 2:
						// Help:
					$content='<a href="http://typo3.org/doc+M55dbd711fc8.0.html" target="_blank"><img src="'.$GLOBALS["BACK_PATH"].'gfx/helpbubble.gif" width="14" height="14" border="0" alt="Help" align="top" hspace=2>'.$LANG->getLL("needHelp").'</a>';
					$this->content.=$this->doc->section("",$content,0,1);
				
						// This is the incoming configuration values. This could be preset as a way of saving queries.
					$defVals=t3lib_div::GPvar("selcfg",1);
					$targetPreset = t3lib_div::GPvar("_target_record");

						// Save presets:
					$query="";
					if (t3lib_div::GPvar("_save"))	{
						if ($targetPreset) {
							$query = "UPDATE tx_danewslettersubscription_presets SET ".(trim(t3lib_div::GPvar("_name")) ? "title='".addslashes(trim(t3lib_div::GPvar("_name")))."', ":"")."presetcontent='".addslashes(serialize($defVals))."' WHERE uid=".intval($targetPreset);
							$res = mysql(TYPO3_db,$query);
						} elseif (trim(t3lib_div::GPvar("_name")))	{
							$query = "INSERT INTO tx_danewslettersubscription_presets (pid,title,presetcontent) VALUES ('".intval($this->id)."','".addslashes(trim(t3lib_div::GPvar("_name")))."','".addslashes(serialize($defVals))."')";
							$res = mysql(TYPO3_db,$query);
						}
					}
						// Save presets:
					if (t3lib_div::GPvar("_delete") && $targetPreset)	{
						$query = "DELETE FROM tx_danewslettersubscription_presets WHERE uid=".intval($targetPreset);
						$res = mysql(TYPO3_db,$query);
					}
#debug($query);
				
						// Get presets:
					$query = "SELECT * FROM tx_danewslettersubscription_presets WHERE pid=".intval($this->id)." ORDER BY title";
					$res = mysql(TYPO3_db,$query);
					$opt=array();
					$opt[]='<option value=""></option>';
					while($presetRow=mysql_fetch_assoc($res))	{
						$opt[]='<option value="'.$presetRow["uid"].'"'.($targetPreset==$presetRow["uid"]?' SELECTED':'').'>'.htmlspecialchars($presetRow["title"]).'</option>';
						
						if (t3lib_div::GPvar("_load") && $targetPreset==$presetRow["uid"])	{
							$defVals=unserialize($presetRow["presetcontent"]);
						}
					}
					$preset_content='<select name="_target_record">'.implode("",$opt).'</select><br>
					<input type="submit" name="_load" value="'.htmlspecialchars($LANG->getLL("loadpreset")).'"><br>
					<input type="submit" name="_delete" value="'.htmlspecialchars($LANG->getLL("deletepreset")).'"><br>
					<input type="submit" name="_save" value="'.htmlspecialchars($LANG->getLL("savepreset")).':"><input type="text" name="_name">';


						// First, select the current newsletters on the page and make them selectable.
					$rows=array();
					$rows[]='<tr bgcolor="'.$this->doc->bgColor5.'">
						<td><strong>'.htmlspecialchars($LANG->getLL("ntitle")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("number_subscr")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("action")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("Show")).':</strong></td>
						</tr>';
					
					$wherePartOfQuery=array();
					$query="SELECT * FROM tx_danewslettersubscription_cat WHERE pid=".intval($this->id).
						t3lib_BEfunc::deleteClause("tx_danewslettersubscription_cat").
						" ORDER BY sorting";
					$res = mysql(TYPO3_db,$query);
					$catIdList=array();
	
					while($row=mysql_fetch_assoc($res))	{
						$catIdList[]=$row["uid"];
						$opt=array();
						$query="SELECT count(*) FROM tx_danewslettersubscription_furels WHERE newsletter_cat=".intval($row["uid"]);
						$res_c = mysql(TYPO3_db,$query);
						list($count) = mysql_fetch_row($res_c);
						
							// Making selector box:
						$opt[]='<option></option>';
						$opt[]='<option value="and"'.($defVals["lists"][$row["uid"]]=="and"?' SELECTED':'').'>... '.htmlspecialchars($LANG->getLL("and_subscr")).'</option>';
						$opt[]='<option value="or"'.($defVals["lists"][$row["uid"]]=="or"?' SELECTED':'').'>... '.htmlspecialchars($LANG->getLL("or_subscr")).'</option>';
						$opt[]='<option value="not"'.($defVals["lists"][$row["uid"]]=="not"?' SELECTED':'').'>... '.htmlspecialchars($LANG->getLL("not_subscr")).'</option>';
	
							// Putting together the query for selecting the 
						switch((string)$defVals["lists"][$row["uid"]])	{
							case "and":
								$wherePartOfQuery["AND_GROUPS"][]=$row;
							break;
							case "or":
								$wherePartOfQuery["OR_LIST"][]=$row;
							break;
							case "not":
								$wherePartOfQuery["NOT_LIST"][]=$row;
							break;
						}
						
						$show='<input type="hidden" name="selcfg[showLists]['.$row["uid"].']" value="0"><input type="checkbox" name="selcfg[showLists]['.$row["uid"].']" value="1"'.($defVals["showLists"][$row["uid"]]?' CHECKED':'').'>';
						$rows[]='<tr bgcolor="'.$this->doc->bgColor4.'">
							<td valign="top">'.$row["title"].'</td>
							<td valign="top" align="center">'.$count.'</td>
							<td valign="top" align="center"><select name="selcfg[lists]['.$row["uid"].']">'.implode("",$opt).'</select></td>
							<td bgcolor="'.$this->doc->bgColor6.'">'.$show.'</td>
							</tr>';
					}
					
					$subscriber_result= $this->makeSelectionOfFeUserIds($wherePartOfQuery,$catIdList);
#debug($subscriber_result);
					$content='<table border=0 cellspacing=1 cellpadding=1>'.implode("",$rows).'</table>';
					$content.='<strong>Selection:</strong> '.$subscriber_result[1].'<br>';
					if (is_array($subscriber_result[0]["fe_users"]))	$content.='<strong>'.$LANG->getLL("sel_UsersSel").':</strong> '.count($subscriber_result[0]["fe_users"]).'<br>';
					if (is_array($subscriber_result[0]["email"]))	$content.='<strong>'.$LANG->getLL("sel_NonUsersSel").':</strong> '.count($subscriber_result[0]["email"]).'<br>';
					$this->content.=$this->doc->section(htmlspecialchars($LANG->getLL("lists")),$content,0,1);
					


				
						// fe_user attributes select:
					$rows=array();
					$rows[]='<tr bgcolor="'.$this->doc->bgColor5.'">
						<td><strong>'.htmlspecialchars($LANG->getLL("field")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("NOT")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("comparison")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("value")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("Show")).':</strong></td>
						</tr>';
					
						// User record select:
					$fe_users_fields=array_unique(t3lib_div::trimExplode(",",$this->modTSconfig["properties"]["fe_users_fieldList"].",email",1));
					t3lib_div::loadTCA("fe_users");

					$fe_users_queryParts=array();
					$email_queryParts=array();
					reset($fe_users_fields);
					while(list(,$fN)=each($fe_users_fields))	{
						$fConf = $GLOBALS["TCA"]["fe_users"]["columns"][$fN];
						if (is_array($fConf))	{
							$rows[]=$this->selectForFieldRow($defVals["fe_users"][$fN],$fN,$GLOBALS["LANG"]->sL($fConf["label"]));
							$queryP = $this->queryPartForRow($defVals["fe_users"][$fN],$fN,"fe_users.");
							if ($queryP)	$fe_users_queryParts[]=$queryP;

							if ((string)$fN=="email")	{	// If the field is "email" then it CAN be selected upon even if non-user-subscriber.
								$queryP = $this->queryPartForRow($defVals["fe_users"][$fN],$fN,"");
								if ($queryP)	$email_queryParts[]=$queryP;
							}
						}
					}
						// Making AND/OR mode box:
					$opt=array();
					$opt[]='<option value="and"'.($defVals["fe_users_mode"]=="and"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_labelAND")).'</option>';
					$opt[]='<option value="or"'.($defVals["fe_users_mode"]=="or"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_labelOR")).'</option>';
					$rows[]='<tr bgcolor="'.$this->doc->bgColor4.'">
						<td colspan=5><strong>'.htmlspecialchars($LANG->getLL("Mode")).':</strong> <select name="selcfg[fe_users_mode]">'.implode("",$opt).'</select></td>
						</tr>';

					$content='<table border=0 cellspacing=1 cellpadding=1>'.implode("",$rows).'</table>';
					
						// Now, select fe_users and non-users:
					$recips=array("email"=>array(),"fe_users"=>array());
					if (is_array($subscriber_result[0]["fe_users"]) && count($subscriber_result[0]["fe_users"]))	{
						$specSel = (count($fe_users_queryParts) ? " AND (".implode($defVals["fe_users_mode"]=="or"?" OR ":" AND ",$fe_users_queryParts).")" : "");
						$query="SELECT fe_users.*,tx_danewslettersubscription_furels.datacontent,tx_danewslettersubscription_furels.fe_user FROM fe_users,tx_danewslettersubscription_furels".
								" WHERE fe_users.uid=tx_danewslettersubscription_furels.fe_user".
								" AND tx_danewslettersubscription_furels.fe_user IN (".implode(",",$subscriber_result[0]["fe_users"]).")".
								t3lib_BEfunc::deleteClause("fe_users")." AND NOT fe_users.disable".
								" AND tx_danewslettersubscription_furels.newsletter_cat IN (".implode(",", $catIdList).")".
								$specSel.
								" GROUP BY tx_danewslettersubscription_furels.fe_user";
						$res=mysql(TYPO3_db,$query);
						echo mysql_error();
						while($row=mysql_fetch_assoc($res))		{
							$recips["fe_users"][$row["uid"]]=$row;
						}
						$content.="<strong>".$LANG->getLL("sel_UsersSel").":</strong> ".count($recips["fe_users"]).($specSel?" ".$LANG->getLL("sel_clause").": <em>".htmlspecialchars($specSel)."</em>":"")."<BR>";
					}
					if (is_array($subscriber_result[0]["email"]) && count($subscriber_result[0]["email"]))	{
						$specSel = (count($email_queryParts) ? " AND (".implode(" AND ",$email_queryParts).")" : "");
						$query="SELECT tx_danewslettersubscription_furels.* FROM tx_danewslettersubscription_furels".
								" WHERE tx_danewslettersubscription_furels.fe_user IN (".implode(",",$subscriber_result[0]["email"]).")".
								" AND tx_danewslettersubscription_furels.newsletter_cat IN (".implode(",", $catIdList).")".
								$specSel.
								" GROUP BY tx_danewslettersubscription_furels.fe_user";
						$res=mysql(TYPO3_db,$query);
						while($row=mysql_fetch_assoc($res))		{
							$recips["email"][$row["fe_user"]]=$row;
						}
						$content.="<strong>".$LANG->getLL("sel_NonUsersSel").":</strong> ".count($recips["email"]).($specSel?" ".$LANG->getLL("sel_clause").": <em>".htmlspecialchars($specSel)."</em>":"")."<BR>";
					}
#debug(array($recips));
#debug(array($email_queryParts,$fe_users_queryParts));

					$this->content.=$this->doc->section(htmlspecialchars($LANG->getLL("userAttrib")),$content,0,1);
					
					
					


						// Additional local attributes select:
					$rows=array();
					$confAccumArray=array();
					$rows[]='<tr bgcolor="'.$this->doc->bgColor5.'">
						<td><strong>'.htmlspecialchars($LANG->getLL("field")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("NOT")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("comparison")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("value")).':</strong></td>
						<td><strong>'.htmlspecialchars($LANG->getLL("Show")).':</strong></td>
						</tr>';
					$content="";
					$query = "SELECT uid,bodytext FROM tt_content WHERE pid='".intval($this->id)."' AND CType='list' AND list_type='da_newsletter_subscription_pi1' AND NOT hidden ".
								t3lib_BEfunc::deleteClause("tt_content").
								" ORDER BY sorting";
					$res = mysql(TYPO3_db,$query);
					if (mysql_num_rows($res)>1)	{
						 $content.=rfw(htmlspecialchars($LANG->getLL("warning_more_el"))).'<br>';
					} 
					if ($tt_content_row = mysql_fetch_assoc($res))	{
						$formData=t3lib_div::trimExplode(chr(10),$tt_content_row["bodytext"],1);
						reset($formData);
						while(list($kk,$vv)=each($formData))	{
							$formData[$kk]=t3lib_div::trimExplode("|",$vv);
							$cfgParts = $this->getFieldDataFromConfigLine($formData[$kk]);
							if ($cfgParts["fieldname"])	{
								$fN = $cfgParts["fieldname"];
								$rows[]=$this->selectForFieldRow($defVals["add"][$fN],$fN,$cfgParts["label"],"add",$cfgParts);
								$confAccumArray[$fN]=$defVals["add"][$fN];
							}
						}
					}				
					if (count($rows)>1)	{
							// Making AND/OR mode box:
						$opt=array();
						$opt[]='<option value="and"'.($defVals["local_mode"]=="and"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_labelAND")).'</option>';
						$opt[]='<option value="or"'.($defVals["local_mode"]=="or"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_labelOR")).'</option>';
						$rows[]='<tr bgcolor="'.$this->doc->bgColor4.'">
							<td colspan=5><strong>'.htmlspecialchars($LANG->getLL("Mode")).':</strong> <select name="selcfg[local_mode]">'.implode("",$opt).'</select></td>
							</tr>';
	
						$content.='<table border=0 cellspacing=1 cellpadding=1>'.implode("",$rows).'</table>';
						
						
							// FE_USER
						if (is_array($recips["fe_users"]))	{
							reset($recips["fe_users"]);
							while(list($uid,$internalRow)=each($recips["fe_users"]))	{
								$localData = unserialize($internalRow["datacontent"]);
								$cmpResult = $this->cmpLocalData($localData,$confAccumArray);
#debug($localData);
#debug($cmpResult);
								if ($defVals["local_mode"]=="or")	{
									if ($cmpResult[1]>0 && $cmpResult[0]<=0)	{	// OR - some must match if there ARE some criteria
										unset($recips["fe_users"][$uid]);
									}
								} else {
									if ($cmpResult[0]!=$cmpResult[1])	{	// AND - all must match
										unset($recips["fe_users"][$uid]);
									}
								}
							}
						}
						
							// EMAIL
						if (is_array($recips["email"]))	{
							reset($recips["email"]);
							while(list($uid,$internalRow)=each($recips["email"]))	{
								$localData = unserialize($internalRow["datacontent"]);
								$cmpResult = $this->cmpLocalData($localData,$confAccumArray);
#debug($localData);
#debug($cmpResult);
								if ($defVals["local_mode"]=="or")	{
									if ($cmpResult[1]>0 && $cmpResult[0]<=0)	{	// OR - some must match if there ARE some criteria
										unset($recips["email"][$uid]);
									}
								} else {
									if ($cmpResult[0]!=$cmpResult[1])	{	// AND - all must match
										unset($recips["email"][$uid]);
									}
								}
							}
						}
						
						if (count($recips["fe_users"]))	$content.="<strong>".$LANG->getLL("sel_UsersSel").":</strong> ".count($recips["fe_users"])."<BR>";
						if (count($recips["email"]))	$content.="<strong>".$LANG->getLL("sel_NonUsersSel").":</strong> ".count($recips["email"])."<BR>";

						$this->content.=$this->doc->section(htmlspecialchars($LANG->getLL("localAttrib")),$content,0,1);
					}	
	
	
	
						// Making selector box:
					$opt=array();
					$opt[]='<option></option>';
					$opt[]='<option value="email"'.($defVals["output"]=="email"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("output_simple")).'</option>';
					$opt[]='<option value="csv"'.($defVals["output"]=="csv"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("output_csv")).'</option>';
#					$opt[]='<option value="oodoc"'.($defVals["output"]=="oodoc"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("output_oodoc")).'</option>';
	
					$content='<select name="selcfg[output]">'.implode("",$opt).'</select><BR><BR><input type="submit" value="'.htmlspecialchars($LANG->getLL("output_make")).'">';
					$content.='<BR><input type="hidden" name="selcfg[output_unique]" value="0"><input type="checkbox" name="selcfg[output_unique]" value="1"'.($defVals["output_unique"]?' CHECKED':'').'> '.$LANG->getLL("output_unique");

					
					$content.='<BR><textarea '.$this->doc->formWidthText(48,"","off").' rows="10" wrap="off">'.t3lib_div::formatForTextarea($this->getOutput($recips,$defVals,t3lib_div::GPvar("_as_file"))).'</textarea>';
					$content.='<BR><input type="checkbox" name="_as_file" value="1">'.$LANG->getLL("download_as_file");
	
					$this->content.=$this->doc->section(htmlspecialchars($LANG->getLL("header_output")),$content,0,1);

						// Preset stuff:
					$this->content.=$this->doc->section(htmlspecialchars($LANG->getLL("header_preset")),$preset_content,0,1);
				break;
			} 
		}
	}
	function queryPartForRow($conf,$field,$prefix)	{
		if (!strcmp("",trim($conf["val1"])) && (string)$conf["type"]!="isset")	return;
		
		switch($conf["type"])	{
/*
			case "check":
				if ($conf["type"]=="set")	{
					$query=$prefix.$field."=".(!$conf["not"]?1:0);
				}
			break;
			case "select":		
			case "radio":
				$query_p=array();
				reset($conf["type"]);
				while(list(,$optVal)=each($conf["type"]))	{
					$query_p[]=$prefix.$field.'="'.addslashes($optVal).'"';
				}
				
				$query = implode(" AND ",$query_p);
			break;
			*/
			default:
				switch((string)$conf["type"])	{
					case "match":
						$query=$prefix.$field.($conf["not"]?'!':'')."='".addslashes($conf["val1"])."'";
					break;
					case "first":
						$query=$prefix.$field.($conf["not"]?' NOT':'')." LIKE '".addslashes($conf["val1"])."%'";
					break;
					case "last":
						$query=$prefix.$field.($conf["not"]?' NOT':'')." LIKE '%".addslashes($conf["val1"])."'";
					break;
					case "in":
						$query=$prefix.$field.($conf["not"]?' NOT':'')." LIKE '%".addslashes($conf["val1"])."%'";
					break;
					case "less":
						$query=$prefix.$field.($conf["not"]?'>=':'<')."'".addslashes($conf["val1"])."'";
					break;
					case "greater":
						$query=$prefix.$field.($conf["not"]?'<=':'>')."'".addslashes($conf["val1"])."'";
					break;
					case "between":
						if (!strcmp("",trim($conf["val2"])))	return;
						$query="(".$prefix.$field.($conf["not"]?'<':'>=')."'".addslashes($conf["val1"])."' ".($conf["not"]?"OR":"AND")." ".$prefix.$field.($conf["not"]?'>':'<=')."'".addslashes($conf["val2"])."')";
					break;
					case "isset":
						if (!$conf["not"])	{
							$query="(".$prefix.$field."!='' AND ".$prefix.$field."!='0')";
						} else {
							$query="(".$prefix.$field."='' OR ".$prefix.$field."='0')";
						}
					break;
				}
			break;
		}
		return $query;
	}
	function cmpLocalData($localData,$confAccumArray)	{
#debug(array($localData,$confAccumArray));
		
		$checks=0;
		$true=0;
		reset($confAccumArray);
		while(list($key,$conf)=each($confAccumArray))	{
			$trueFlag=-1;
			if (strcmp("",trim($conf["val1"])) || (string)$conf["type"]=="isset" || (string)$conf["type"]=="set" || is_array($conf["type"]))	{

				if (is_array($conf["type"]))	{
					reset($conf["type"]);
					while(list(,$v)=each($conf["type"]))	{
						if (strcmp(trim($v),""))	{
							$trueFlag=0;
							
							if (!is_array($localData[$key]))	{
								if (!strcmp($v,$localData[$key]))	{
									$trueFlag=1;
									break;
								}
							} else {
								reset($localData[$key]);
								while(list(,$v2)=each($localData[$key]))	{
									if (!strcmp($v,$v2))	{
										$trueFlag=1;
										break;
									}
								}
								if ($trueFlag)		break;
							}
						}
					}
				} else {
					switch((string)$conf["type"])	{
						case "match":
							$trueFlag= !strcmp(strtoupper($localData[$key]),strtoupper($conf["val1"]));
						break;
						case "first":
							$trueFlag= !strcmp(substr(strtoupper($localData[$key]),0,strlen($conf["val1"])),strtoupper($conf["val1"]));
						break;
						case "last":
							$trueFlag= !strcmp(substr(strtoupper($localData[$key]),-strlen($conf["val1"])),strtoupper($conf["val1"]));
						break;
						case "in":
							$trueFlag= strstr(strtoupper($localData[$key]),strtoupper($conf["val1"]));
						break;
						case "less":	// int+double
							$trueFlag= $localData[$key] < $conf["val1"];
						break;
						case "greater":	// int+double
							$trueFlag= $localData[$key] > $conf["val1"];
						break;
						case "between":	// int+double
							$trueFlag= $localData[$key]>=$conf["val1"] && $localData[$key]<=$conf["val2"];
						break;
						case "isset":
						case "set":
							$trueFlag= $localData[$key] ? 1 : 0;
						break;
					}
				}
				
					// Set value:
				if ($trueFlag>=0)	{
					$checks++;
					if (($trueFlag && !$conf["not"]) || (!$trueFlag && $conf["not"]))	$true++;
#		debug(array($conf,$localData[$key],$conf["val1"],$trueFlag?"TRUE":"FALSE"));
				}
			}
		}
		return array($true,$checks);
	}
	function selectForFieldRow($conf,$field,$name,$key="fe_users",$cfgParts=array())	{
		global $LANG;
		
			// Making selector box:
		$opt=array();
#		$opt[]='<option></option>';
		$opt[]='<option value="and"'.($conf["oper"]=="and"?' SELECTED':'').'>AND</option>';
		$opt[]='<option value="or"'.($conf["oper"]=="or"?' SELECTED':'').'>OR</option>';
#		$opt[]='<option value="not"'.($conf["oper"]=="not"?' SELECTED':'').'>NOT</option>';
		$operSel = '<select name="selcfg['.$key.']['.$field.'][oper]">'.implode("",$opt).'</select>';
		
			// Show in output
		$show='<input type="hidden" name="selcfg['.$key.']['.$field.'][show]" value="0"><input type="checkbox" name="selcfg['.$key.']['.$field.'][show]" value="1"'.($conf["show"]?' CHECKED':'').'>';

		$opt=array();
		switch($cfgParts["type"])	{
			case "check":		
				$opt[]='<option></option>';
				$opt[]='<option value="set"'.($conf["type"]=="set"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_checked")).'</option>';
				$type = '<select name="selcfg['.$key.']['.$field.'][type]">'.implode("",$opt).'</select>';
			
				$input='';

				$not='<input type="hidden" name="selcfg['.$key.']['.$field.'][not]" value="0"><input type="checkbox" name="selcfg['.$key.']['.$field.'][not]" value="1"'.($conf["not"]?' CHECKED':'').'>';
			break;
			case "select":		
			case "radio":		
				$opt[]='<option></option>';
				
				$options=t3lib_div::trimExplode(",",$cfgParts["options"]);
				while(list(,$optVal)=each($options))	{
					$opt[]='<option value="'.htmlspecialchars($optVal).'"'.(is_array($conf["type"]) && in_array($optVal,$conf["type"])?' SELECTED':'').'>'.htmlspecialchars($optVal).'</option>';
				}
				$type = '<select name="selcfg['.$key.']['.$field.'][type][]" size="'.count($opt).'" multiple>'.implode("",$opt).'</select>';
			
				$input='';

				$not='<input type="hidden" name="selcfg['.$key.']['.$field.'][not]" value="0"><input type="checkbox" name="selcfg['.$key.']['.$field.'][not]" value="1"'.($conf["not"]?' CHECKED':'').'>';
			break;
			default:		
				$opt[]='<option></option>';
				$opt[]='<option value="match"'.($conf["type"]=="match"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_equal")).'</option>';
				$opt[]='<option value="first"'.($conf["type"]=="first"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_first")).'</option>';
				$opt[]='<option value="last"'.($conf["type"]=="last"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_last")).'</option>';
				$opt[]='<option value="in"'.($conf["type"]=="in"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_contains")).'</option>';
				$opt[]='<option value="less"'.($conf["type"]=="less"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_less")).'</option>';
				$opt[]='<option value="greater"'.($conf["type"]=="greater"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_greater")).'</option>';
				$opt[]='<option value="between"'.($conf["type"]=="between"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_between")).'</option>';
				$opt[]='<option value="isset"'.($conf["type"]=="isset"?' SELECTED':'').'>'.htmlspecialchars($LANG->getLL("sel_true")).'</option>';
				$type = '<select name="selcfg['.$key.']['.$field.'][type]">'.implode("",$opt).'</select>';
			
				$input='';
				if ($conf["type"]=="between")	{
					$input='<input type="text" name="selcfg['.$key.']['.$field.'][val1]"'.$this->doc->formWidth(9).' value="'.htmlspecialchars($conf["val1"]).'">';
					$input.=' - <input type="text" name="selcfg['.$key.']['.$field.'][val2]"'.$this->doc->formWidth(9).' value="'.htmlspecialchars($conf["val2"]).'">';
				} elseif ($conf["type"]!="isset") {
					$input='<input type="text" name="selcfg['.$key.']['.$field.'][val1]"'.$this->doc->formWidth(20).' value="'.htmlspecialchars($conf["val1"]).'">';
				}
				
				$not='<input type="hidden" name="selcfg['.$key.']['.$field.'][not]" value="0"><input type="checkbox" name="selcfg['.$key.']['.$field.'][not]" value="1"'.($conf["not"]?' CHECKED':'').'>';
			break;
		}
					
		return '<tr bgcolor="'.$this->doc->bgColor4.'">
			<td>'.$name.'</td>
			<td>'.$not.'</td>
			<td>'.$type.'</td>
			<td>'.$input.'</td>
			<td bgcolor="'.$this->doc->bgColor6.'">'.$show.'</td>
			</tr>';

	}







	/**
	 * Parses a config line from the form configuration into an array.
	 * REDUNDANT FROM ..._pi class. This might go into a common class some day.
	 */
	function getFieldDataFromConfigLine($parts)	{
		$confData=array();
	
			// label:
		$confData["label"] = trim($parts[0]);
			// field:
		$fParts = explode(",",$parts[1]);
		$fParts[0]=trim($fParts[0]);
		if (substr($fParts[0],0,1)=="*")	{
			$confData["required"]=1;
			$fParts[0] = substr($fParts[0],1);
		}

		$typeParts = explode("=",$fParts[0]);
		$confData["type"] = trim(strtolower(end($typeParts)));
		if (count($typeParts)==1)	{
			$confData["fieldname"] = substr(ereg_replace("[^a-zA-Z0-9_]","",str_replace(" ","_",trim($parts[0]))),0,30);
			if (strtolower(ereg_replace("[^[:alnum:]]","",$confData["fieldname"]))=="email")	{$confData["fieldname"]="email";}
		} else {
			$confData["fieldname"] = str_replace(" ","_",trim($typeParts[0]));
		}


		switch($confData["type"])	{
			case "select":
			case "radio":
				$valueParts = explode(",",$parts[2]);

				for($a=0;$a<count($valueParts);$a++)	{
					$valueParts[$a]=trim($valueParts[$a]);
					if (substr($valueParts[$a],0,1)=="*")	{	// Finding default value
						$valueParts[$a] = trim(substr($valueParts[$a],1));
					}
				}

				$confData["options"] = implode(",",$valueParts);
			break;	
		}
		
		return $confData;
	}
	
	/**
	 * Returns an array with a unique array of fe_user-ids (both pos/neg) supposed to be selected + a text-message explaining the selection.
	 */
	function makeSelectionOfFeUserIds($wherePartOfQuery,$catIdList)	{
		global $LANG;
			// Select all "fe_user" ids which are in the OR_LIST:
		if (is_array($wherePartOfQuery["OR_LIST"]))	{
			$searchList=array();
			$searchWords=array();
			reset($wherePartOfQuery["OR_LIST"]);
			while(list($c,$row)=each($wherePartOfQuery["OR_LIST"]))	{
				$searchList[]=$row["uid"];
				$searchWords[]=($c>0?($c+1==count($wherePartOfQuery["OR_LIST"])?" ".$LANG->getLL("sel_labelOR")." ":", "):"").'"'.$row["title"].'"';
			}
			$query="SELECT fe_user FROM tx_danewslettersubscription_furels WHERE newsletter_cat IN (".implode(",",$searchList).") GROUP by fe_user";
#debug(array("OR",$query));
			$res_list = mysql(TYPO3_db,$query);
			$result_list=array();
			while($item=mysql_fetch_assoc($res_list))	{
				$result_list[]=$item["fe_user"];
			}

			$wherePartOfQuery["AND_GROUPS"][]=array("uid"=>"OR","_result_list"=>$result_list,"_text"=>implode("",$searchWords).' ('.count($result_list).' '.$LANG->getLL("sel_labelResults").')');
		}
		
			// Select all "fe_user" ids which are in the NOT_LIST:
		unset($not_array);
		if (is_array($wherePartOfQuery["NOT_LIST"]))	{
			$searchList=array();
			$searchWords=array();
			reset($wherePartOfQuery["NOT_LIST"]);
			while(list($c,$row)=each($wherePartOfQuery["NOT_LIST"]))	{
				$searchList[]=$row["uid"];
				$searchWords[]=($c>0?($c+1==count($wherePartOfQuery["NOT_LIST"])?" ".$LANG->getLL("sel_labelOR")." ":", "):"").'"'.$row["title"].'"';
			}
			$query="SELECT fe_user FROM tx_danewslettersubscription_furels WHERE newsletter_cat IN (".implode(",",$searchList).") GROUP by fe_user";
#debug(array("NOT",$query));
			$res_list = mysql(TYPO3_db,$query);
			$result_list=array();
			while($item=mysql_fetch_assoc($res_list))	{
				$result_list[]=$item["fe_user"];
			}

			$not_array=array("_result_list"=>$result_list,"_text"=>implode("",$searchWords).' ('.count($result_list).' '.$LANG->getLL("sel_labelResults").')');
		}
		
			// Select AND GROUPS (including OR group if any)
		if (is_array($wherePartOfQuery["AND_GROUPS"]))	{
			reset($wherePartOfQuery["AND_GROUPS"]);
			while(list($c,$row)=each($wherePartOfQuery["AND_GROUPS"]))	{
				if ((string)$row["uid"]!="OR")	{
					$query="SELECT fe_user FROM tx_danewslettersubscription_furels WHERE newsletter_cat=".intval($row["uid"])." GROUP by fe_user";
#debug(array("AND",$query));
					$res_list = mysql(TYPO3_db,$query);
					$result_list = array();
					while($item=mysql_fetch_assoc($res_list))	{
						$result_list[]=$item["fe_user"];
					}
					$wherePartOfQuery["AND_GROUPS"][$c]["_result_list"]=$result_list;
					$wherePartOfQuery["AND_GROUPS"][$c]["_text"]='"'.$row["title"].'" ('.count($result_list).' '.$LANG->getLL("sel_labelResults").')';
				}
			}
		}

			// Put it all together:
		unset($final_result_set);
		if (is_array($wherePartOfQuery["AND_GROUPS"]) && count($wherePartOfQuery["AND_GROUPS"]))	{
			$searchWords=array($LANG->getLL("sel_labelSel")." ");
			
			reset($wherePartOfQuery["AND_GROUPS"]);
			while(list($c,$row)=each($wherePartOfQuery["AND_GROUPS"]))	{
				$searchWords[]=($c>0?" ".$LANG->getLL("sel_labelAND")." ":"").$wherePartOfQuery["AND_GROUPS"][$c]["_text"];
				if (!$c)	{	// Initially set the final_result_set to the first one.
					$final_result_set=$wherePartOfQuery["AND_GROUPS"][$c]["_result_list"];
				} else {	// ... then make intersection for the rest (AND-ing)
					$final_result_set=array_intersect($final_result_set,$wherePartOfQuery["AND_GROUPS"][$c]["_result_list"]);
				}
			}
		} else {
				// Now, select all subscribers:
			$query="SELECT fe_user FROM tx_danewslettersubscription_furels WHERE newsletter_cat IN (".implode(",", $catIdList).") GROUP by fe_user";
			$res_list = mysql(TYPO3_db,$query);
			$final_result_set=array();
			while($item=mysql_fetch_assoc($res_list))	{
				$final_result_set[]=$item["fe_user"];
			}

			$searchWords=array($LANG->getLL("sel_labelAny")." (".count($final_result_set)." ".$LANG->getLL("sel_labelResults").")");
		}
		
		
		unset($output_result);
		if (is_array($final_result_set))	{
			if (is_array($not_array)) {
				$searchWords[]=" ".$LANG->getLL("sel_labelExcept")." ".$not_array["_text"];
				$final_result_set=array_diff($final_result_set,$not_array["_result_list"]);
			}
			// Split into email/fe_users subscriptions:
			
			$output_result=array("email"=>array(),"fe_users"=>array());
			reset($final_result_set);
			while(list(,$fe_user_id)=each($final_result_set))	{
				if ($fe_user_id<0)	{
					$output_result["email"][]=$fe_user_id;
				} else $output_result["fe_users"][]=$fe_user_id;
			}
		}
		

		return array($output_result,implode("",$searchWords));
	}
	
	
	/**
	 * Makes output from the input array of subscriber-rows.
	 */
	function getOutput($recips,$defVals,$as_file=0)	{

		$parts=explode(",","fe_users,email");
		$collect=array();
#		$defVals["fe_users"]["uid"]["show"]=1;
#		$defVals["fe_users"]["fe_user"]["show"]=1;
		$defVals["fe_users"]["email"]["show"]=1;
		
		while(list(,$key)=each($parts))	{
				// FE_USER
			if (is_array($recips[$key]))	{
				reset($recips[$key]);
				while(list($uid,$internalRow)=each($recips[$key]))	{
					$localData = unserialize($internalRow["datacontent"]);

					switch($defVals["output"])	{
						case "email":
							if ($defVals["output_unique"])	
								$collect[$internalRow["email"]]=$internalRow["email"];
							else
								$collect[]=$internalRow["email"];
						break;
						case "csv":
						case "oodoc":
							$datArray=array();
							if (is_array($defVals["fe_users"]))	{
								reset($defVals["fe_users"]);
								while(list($Id,$Dat)=each($defVals["fe_users"]))	{
									if ($Dat["show"])	{
										$setKey = "fe_users.".$Id;
										if ($Id=="email")	$setKey="email";
										if ($Id=="name")	$setKey="name";
										if ($Id=="uid")		$Id="fe_user";
										$datArray[$setKey]=$internalRow[$Id];
									}									
								}
							}
							if (is_array($defVals["add"]))	{
								reset($defVals["add"]);
								while(list($Id,$Dat)=each($defVals["add"]))	{
									if ($Dat["show"])	{
										$setKey = "local.".$Id;
										if (strtolower($Id)=="name")	$setKey="name";
										$datArray[$setKey]=is_array($localData[$Id])?implode(",",$localData[$Id]):$localData[$Id];
									}									
								}
							}
							if (is_array($defVals["showLists"]))	{
								reset($defVals["showLists"]);
								while(list($Id,$show)=each($defVals["showLists"]))	{
									if ($show)	{
										$query="SELECT uid FROM tx_danewslettersubscription_furels 
											WHERE tx_danewslettersubscription_furels.fe_user=".intval($uid)." AND 
												tx_danewslettersubscription_furels.newsletter_cat=".intval($Id);
										$res = mysql(TYPO3_db,$query);
										$datArray["lists_".$Id]=mysql_num_rows($res)?1:0;
									}
								}
							}

							if ($defVals["output"]=="csv")	{
								if (!count($collect))	{
									$collect["_"]=t3lib_div::csvValues(array_keys($datArray),",","");
								}
								$CSVline = t3lib_div::csvValues($datArray,",");
								if ($defVals["output_unique"])	
									$collect[$internalRow["email"]]=$CSVline;
								else
									$collect[]=$CSVline;
							}
						break;
					}
				}
			}
		}


		$filename="noname.txt";
		switch($defVals["output"])	{
			case "email":
				$OUTPUT = implode(", ",$collect);
				$filename="email_list_".date("dmy-Hi").".txt";
			break;
			case "csv":
				$OUTPUT = implode(chr(13).chr(10),$collect);
				$filename="email_list_".date("dmy-Hi").".csv";
			break;
		}

		if ($as_file)	{
			$mimeType = "application/octet-stream";
			Header("Content-Type: ".$mimeType);
			Header("Content-Disposition: attachment; filename=".$filename);
			echo $OUTPUT;
			exit;
		}
		
		
		return $OUTPUT;
	}
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/mod1/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/da_newsletter_subscription/mod1/index.php"]);
}




// Make instance:
$SOBE = t3lib_div::makeInstance("tx_danewslettersubscription_module1");
$SOBE->init();

// Include files?
reset($SOBE->include_once);	
while(list(,$INC_FILE)=each($SOBE->include_once))	{include_once($INC_FILE);}

$SOBE->main();
$SOBE->printContent();

?>