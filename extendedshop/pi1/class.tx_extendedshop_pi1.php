<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 Mauro Lorenzutti for Webformat srl (mauro.lorenzutti@webformat.com)
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
 * Plugin 'Webformat Shop System' for the 'extendedshop' extension.
 *
 * @author	Mauro Lorenzutti for Webformat srl <mauro.lorenzutti@webformat.com>
 */


require_once(PATH_tslib."class.tslib_pibase.php");
require_once(PATH_t3lib."class.t3lib_parsehtml.php");
require_once(PATH_t3lib."class.t3lib_htmlmail.php");
//require_once("typo3conf/ext/extendedshop/res/simlib.php");

class tx_extendedshop_pi1 extends tslib_pibase {
	var $prefixId = "tx_extendedshop_pi1";		// Same as class name.
	var $scriptRelPath = "pi1/class.tx_extendedshop_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "extendedshop";	// The extension key.
	
	var $cObj="";									// The backReference to the mother cObj object set at call time
	var $conf = "";								// The extension configuration
	var $config = "";							// The personalized configuration
	
	// Internal
	var $pid_list="";
	var $uid_list="";							// List of existing uid's from the basket, set by initBasket()
	var $categories=array();			// Is initialized with the categories of the shopping system
	var $pageArray=array();				// Is initialized with an array of the pages in the pid-list
	var $orderRecord = array();		// Will hold the order record if fetched.
	
	var $globalMarkerArray = "";	// Marker Array to substitute
	var $langMarkerArray = "";		// Marker Array for localization
	
	var $calculatedSums_tax = array();	// Array for total cost in basket
	var $calculatedSums_no_tax = array();	// Array for total cost in basket without IVA
	
	// Internal: initBasket():
	var $basket=array();				// initBasket() sets this array based on the registered items
	var $basketExtra;					// initBasket() uses this for additional information like the current payment/shipping methods
	var $recs = Array(); 				// in initBasket this is set to the recs-array of fe_user.
	var $personInfo;					// Set by initBasket to the billing address
	var $deliveryInfo; 					// Set by initBasket to the delivery address
	var $finalize;						// Set by show_finalize() when clears the basket

	
	/**
	 * This is an Extended Shop System.
	 */
	function main($content,$conf)	{

		$GLOBALS["TSFE"]->set_no_cache();			// Cache not allowed!
	
//t3lib_div::debug($conf);

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		
		// Load the templateCode
		$this->config["templateCode"] = $this->cObj->fileResource($this->conf["templateFile"]);
		$this->config["cssCode"] = $this->cObj->fileResource($this->conf["cssFile"]);
		
		$GLOBALS["TSFE"]->setCSS($this->extKey, $this->config["cssCode"]);
		
		$this->config["limit"] = t3lib_div::intInRange($this->conf["limit"],0,1000);
		$this->config["limit"] = $this->config["limit"] ? $this->config["limit"] : 50;
		
		$this->config["pid_list"] = trim($this->cObj->stdWrap($this->conf["pid_list"],$this->conf["pid_list."]));
		$this->config["pid_list"] = $this->config["pid_list"] ? $this->config["pid_list"] : $GLOBALS["TSFE"]->id;
		
		if (t3lib_div::_GP("pid_product")!="")
			$this->config["pid_list"] = t3lib_div::_GP("pid_product");
		
		$this->config["recursive"] = $this->cObj->stdWrap($this->conf["recursive"],$this->conf["recursive."]);
		$this->config["storeRootPid"] = $this->conf["PIDstoreRoot"] ? $this->conf["PIDstoreRoot"] : $GLOBALS["TSFE"]->tmpl->rootLine[0][uid];
		
		// Evaluate the visualization code for actual page
		$this->config["code"] = strtolower(trim($this->cObj->stdWrap($this->conf["code"],$this->conf["code."])));
		$codes=t3lib_div::trimExplode(",", $this->config["code"]?$this->config["code"]:$this->conf["defaultCode"],1);
		
		$this->pi_initPIflexForm();
		$FXConf = array ();

		if (is_array($this->cObj->data['pi_flexform']['data'])) {
			foreach ($this->cObj->data['pi_flexform']['data'] as $sheet => $data)
				foreach ($data as $lang => $value)
					foreach ($value as $key => $val) {
						$FXConf[$key] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, $sheet);
					}
		}
		if ($FXConf['view_mode']!="")	{
			$codes = array();
			$codes[] = $FXConf['view_mode'];
		}
		
		// Check code from insert plugin (CMD= "singleView")
		if ($this->conf["CMD"]=="singleView")
			$codes[] = "SINGLE";
		
		// Pid of the basket
		$this->config["pid_basket"] = trim($this->conf["pid_basket"]);
		$this->config["pid_orders"] = trim($this->conf["pid_orders"]);
		
		//$this->setPidlist($this->config["storeRootPid"]);
		$this->setPidlist($this->config["pid_list"]);
		$this->initRecursive($this->config["recursive"]);
		$this->generatePageArray();
//echo($this->pid_list);
		
		$this->initBasket($GLOBALS["TSFE"]->fe_user->getKey("ses","recs"));	// Must do this to initialize the basket...

		if (!count($codes))	$codes=array("");
		while(list(,$theCode)=each($codes))	{
			$theCode = (string)strtoupper(trim($theCode));
			
			$now = time();
			$timeCode = "AND (starttime=0 OR starttime>=".$now.") AND (endtime=0 OR endtime<=".$now.")";
			
			//	debug($theCode);
			switch($theCode)	{
				case "BASKET":
					$isEmpty = true;
					$clear = t3lib_div::_GP('clear');
					$proceed = t3lib_div::_GP('proceed');
					$datiPersonali = t3lib_div::_GP('datiPersonali');
					$clearPerson = t3lib_div::_GP('clearPerson');
					$new = t3lib_div::_GP('new');
					$login = t3lib_div::_GP('login');
					$forgetpsw = t3lib_div::_GP('forgetpsw');
					$askpsw = t3lib_div::_GP('askpsw');
					$shipping = t3lib_div::_GP('shipping');
					$finalize = t3lib_div::_GP('finalize');
					$returnPP = t3lib_div::_GP('item_name');
					$returnBS_a = t3lib_div::_GP('a');
					$returnBS_b = t3lib_div::_GP('b');
					
					$requiredOK = true;
					if (isset($new))	{
						$requiredOK = $this->checkRequired();
					}
					
					if (isset($new) && !$requiredOK)	{
						$isEmpty = false;
						
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###PERSONAL_INFO_TEMPLATE###")));
						$content.=$this->show_personal_info($template,false);
					}
					else if (isset($clear))	{
						// Clear the basket
						//reset($this->basket["products"]);
						$this->basket["products"] = "";
						$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
					}	
					else if (isset($proceed) || ($datiPersonali==1 && !isset($new) && !isset($clearPerson)))	{
						// Go to personal info page
						$isEmpty = false;
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###PERSONAL_INFO_TEMPLATE###")));
						$content.=$this->show_personal_info($template);
					}
					else if (isset($clearPerson))	{
						// Go to personal info page
						$isEmpty = false;
						$this->resetPersonalInfo();
						$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###PERSONAL_INFO_TEMPLATE###")));
						$content.=$this->show_personal_info($template);
					}
					else if (isset($new) || (isset($shipping) && !isset($finalize)))	{
						// Go to personal info page
						$isEmpty = false;
						$content.=$this->show_payment_shipping();
					}
					else if (isset($finalize) && $this->basket["payment"]["bankcode"]=="")	{
						$isEmpty = false;
						$content.=$this->show_finalize();
					}
					else if (isset($returnPP) || (isset($returnBS_a) && isset($returnBS_b)))	{
						$isEmpty = false;
						$content.=$this->show_finalize();
					}
					else if (isset($finalize) && $this->basket["payment"]["bankcode"]!="")	{
						$isEmpty = false;
						$content.=$this->show_bank();
					}
					else if (isset($login))	{
						// Go to personal info page
						$isEmpty = false;
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###PERSONAL_INFO_TEMPLATE###")));
						$content.=$this->show_personal_info($template);
					}
					else if (isset($forgetpsw) && !isset($login))	{
						$isEmpty = false;
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###FORGETPSW_TEMPLATE###")));
						//$template = $this->cObj->substituteSubpart($template,"###PERSONAL_USER###","",$recursive=0,$keepMarker=0);
						$mA["###PERSONAL_USER###"] = "";
						$template = $this->cObj->substituteMarkerArray($template, $mA);
						$content.= $this->manageLabels($template);
					}
					else if (isset($askpsw))	{
						$isEmpty = false;
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###FORGETPSW_RESPONSE_TEMPLATE###")));
						//$template = $this->cObj->substituteSubpart($template,"###PERSONAL_USER###","",$recursive=0,$keepMarker=0);
						//$mA["###PERSONAL_USER###"] = $this->basket["personinfo"]["USER"]?$this->basket["personinfo"]["USER"]:"";
						$personal = t3lib_div::_GP('personal');
						$email = $personal["USER"];
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', 'tx_extendedshop_login="'.$email.'" and deleted<>1');
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)==1)	{
							$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
							$mA["###LABEL_FORGETPSW_RESPONSE_TITLE###"] = $this->pi_getLL('###LABEL_FORGETPSW_RESPONSE_TITLE_OK###');
							$mA["###LABEL_FORGETPSW_RESPONSE_TEXT###"] = $this->pi_getLL('###LABEL_FORGETPSW_RESPONSE_TEXT_OK###');
							$mA["###PERSONAL_USER###"] = $email;
							$mA["###PERSONAL_PASS###"] = $row["tx_extendedshop_password"];
							$template = $this->cObj->substituteMarkerArray($template, $mA);
							$templateEmail = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###FORGETPSW_EMAIL_TEMPLATE###")));
							$templateEmail = $this->cObj->substituteMarkerArray($templateEmail, $mA);
							$this->send_email($this->manageLabels($templateEmail),"###EMAIL_FORGETPSW_SUBJECT###","",$row["email"]);
						}	else	{
							$mA["###LABEL_FORGETPSW_RESPONSE_TITLE###"] = $this->pi_getLL('###LABEL_FORGETPSW_RESPONSE_TITLE_KO###');
							$mA["###LABEL_FORGETPSW_RESPONSE_TEXT###"] = $this->pi_getLL('###LABEL_FORGETPSW_RESPONSE_TEXT_KO###');
							$template = $this->cObj->substituteMarkerArray($template, $mA);
						}
						$content.= $this->manageLabels($template);
					}
					else if (is_array($this->basket["products"]))	{
						$list = array_keys($this->basket["products"]);
						$where = "uid IN (";
						foreach($list as $id)
						{
							if ($this->basket["products"][$id]["num"]>0)	{
								$where.=$id.",";
								$isEmpty = false;
							}
						}
						if (!$isEmpty)	{
							$where = substr($where,0,-1).")";
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', $where,'',''.$orderBy,''.$this->config["limit"]);
							$content.=$this->show_basket($res);
						}
					}
					if ((!is_array($this->basket["products"]) || $isEmpty) && !$this->finalize)	{
						$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###EMPTY_BASKET_TEMPLATE###")));
						$content = $this->manageLabels($template);
					}
					$this->finalize = false;
				break;
				case "OFFER":
					if(t3lib_div::_GP('accessorieID')!='')	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_accessories', 'uid='.t3lib_div::_GP('accessorieID'),'','','');
						$content.=$this->show_accessorie($res);
					}	elseif(t3lib_div::_GP('productID')!='' && (t3lib_div::_GP('detail')==true || t3lib_div::_GP('numCombination')==''))	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 AND uid='.t3lib_div::_GP('productID').' AND (offertprice!=0 OR discount!=0)','','','');
						$content.=$this->show_product($res);
					}	else	{
						
						$lingua = t3lib_div::_GP("L");
						$testLingua = "";
						if ($lingua>0)	{
							$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'sys_language_uid='.t3lib_div::_GP("L").' AND deleted<>1 '.$timeCode.' AND hidden<>1 AND pid IN ('.$this->pid_list.')','',''.$orderBy,''.$this->config["limit"]);
							$uidExclude = "";
							while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))		{
								$uidExclude .= $row2['l18n_parent'].",";
							}
							$uidExclude = substr($uidExclude,0,-1);
							$testLingua = "AND uid NOT IN (".$uidExclude.")";
							if ($uidExclude=="")
								$testLingua="";
							$testLingua .= " AND (sys_language_uid<=0 OR sys_language_uid=".$lingua.")";
						}	else	{
							$testLingua = "AND sys_language_uid<=0";
						}
						
						if(t3lib_div::_GP('orderBy')!='')	{
  	  				$orderBy = str_replace("_", " ", t3lib_div::_GP('orderBy'));
    					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$timeCode.' '.$testLingua.' AND hidden<>1 AND pid IN ('.$this->pid_list.') AND (offertprice!=0 OR discount!=0)','',''.$orderBy,''.$this->config["limit"]);
    				}	else	{
    					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$timeCode.' '.$testLingua.' AND hidden<>1 AND pid IN ('.$this->pid_list.') AND (offertprice!=0 OR discount!=0)','','sorting',''.$this->config["limit"]);
    				}
    				//$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
    				if ($this->conf["list."]["modeImage"]==1)	{
							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_IMAGE_TEMPLATE###")));
							$content.=$this->show_image_list($res,$template);
						}	else	{
							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
							$content.=$this->show_list($res,$template,true);
						}
					}
				break;
				case "SEARCH":
					if(t3lib_div::_GP('productID')!='')	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'uid='.t3lib_div::_GP('productID'),'','','');
						$content.=$this->show_product($res);
					}	else	{
						if(t3lib_div::_GP('swords')!="")	{
							// If user search for some words then the result page is shown
							$where="";
							$searchFields = explode(",",$this->conf["searchFields"]);
							foreach($searchFields as $field)	{
								$where.= $field." LIKE '%".t3lib_div::_GP('swords')."%' OR ";
							}
							$where = substr($where, 0,-4);
							
							$lingua = t3lib_div::_GP("L");
  						$testLingua = "";
  						if ($lingua>0)	{
  							$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'sys_language_uid='.t3lib_div::_GP("L").' AND deleted<>1 '.$timeCode.' AND hidden<>1 AND pid IN ('.$this->pid_list.')','',''.$orderBy,''.$this->config["limit"]);
  							$uidExclude = "";
  							while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))		{
  								$uidExclude .= $row2['l18n_parent'].",";
  							}
  							$uidExclude = substr($uidExclude,0,-1);
  							$testLingua = "AND uid NOT IN (".$uidExclude.")";
  							if ($uidExclude=="")
  								$testLingua="";
  							$testLingua .= " AND (sys_language_uid<=0 OR sys_language_uid=".$lingua.")";
  						}	else	{
  							$testLingua = "AND sys_language_uid<=0";
  						}
	
							if(t3lib_div::_GP('orderBy')!='')	{
    						$orderBy = str_replace("_", " ", t3lib_div::_GP('orderBy'));
    						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$timeCode.' '.$testLingua.' AND hidden<>1 AND pid IN ('.$this->pid_list.') AND ('.$where.')','',''.$orderBy,''.$this->config["limit"]);
	    				}	else	{
  	  					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$timeCode.' '.$testLingua.' AND hidden<>1 AND pid IN ('.$this->pid_list.') AND ('.$where.')','','sorting',''.$this->config["limit"]);
    					}
    					if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0)	{
	    					//$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
								//$content.=$this->show_list($res,$template);
								//$content.=$this->show_image_list($res,$template);
								if ($this->conf["list."]["modeImage"]==1)	{
    							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_IMAGE_TEMPLATE###")));
    							$content.=$this->show_image_list($res,$template);
    						}	else	{
    							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
    							$content.=$this->show_list($res,$template,true);
    						}
							}	else	{
								$content.= $this->manageLabels(trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_SEARCH_TEMPLATE###"))));
							}
						}	else	{
							// Else the search form is shown
							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_SEARCH_TEMPLATE###")));
							$template = $this->cObj->substituteSubpart($template,"###NORESULTS###","",$recursive=0,$keepMarker=0);
							$content.= $this->manageLabels($template);
						}
					}
				break;
				
				case "ADVANCEDSEARCH":
					$mS["###S_KEYWORDS###"] = "";
					$mS["###S_SIZE###"] = "";
					$mS["###S_COLORS###"] = "";
					if(t3lib_div::_GP('productID')!='')	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'uid='.t3lib_div::_GP('productID'),'','','');
						$content.=$this->show_product($res);
					}	else	{
						if(t3lib_div::_GP('search')!="")	{
							$search = t3lib_div::_GP('search');
							$mS["###S_KEYWORDS###"] = $search['keywords'];
							$mS["###S_SIZE###"] = $search['size'];
							$mS["###S_COLORS###"] = $search['color'];
							$mS["###S_PRICE_".$search['price']."###"] = " selected";
							// If user search for some words then the result page is shown
							$where="(";
							$searchFields = explode(",",$this->conf["searchFields"]);
							foreach($searchFields as $field)	{
								$where.= $field." LIKE '%".$search["keywords"]."%' OR ";
							}
							$where = substr($where, 0,-4);
							$where .= ")";
							if ($search["size"]!="")
								$where .= " AND sizes LIKE '%".$search['size']."%'";
							if ($search["color"]!="")
								$where .= " AND colors LIKE '%".$search['color']."%'";
							if ($search["price"]!="_")	{
								$range = explode("_", $search["price"]);
								$where .= " AND price >=".$range[0];
								if ($range[1]!="")
									$where .= " AND price <=".$range[1];
							}
							
							$lingua = t3lib_div::_GP("L");
							$testLingua = "";
							if ($lingua>0)	{
								$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'sys_language_uid='.t3lib_div::_GP("L").' AND deleted<>1 '.$timeCode.' AND hidden<>1 AND pid IN ('.$this->pid_list.')','',''.$orderBy,''.$this->config["limit"]);
								$uidExclude = "";
								while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))		{
									$uidExclude .= $row2['l18n_parent'].",";
								}
								$uidExclude = substr($uidExclude,0,-1);
								$testLingua = "AND uid NOT IN (".$uidExclude.")";
								if ($uidExclude=="")
									$testLingua="";
								$testLingua .= " AND (sys_language_uid<=0 OR sys_language_uid=".$lingua.")";
							}	else	{
								$testLingua = "AND sys_language_uid<=0";
							}
	
							if(t3lib_div::_GP('orderBy')!='')	{
								$orderBy = str_replace("_", " ", t3lib_div::_GP('orderBy'));
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$timeCode.' '.$testLingua.' AND hidden<>1 AND pid IN ('.$this->pid_list.') AND '.$where,'',''.$orderBy,''.$this->config["limit"]);
							}	else	{
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$timeCode.' '.$testLingua.' AND hidden<>1 AND pid IN ('.$this->pid_list.') AND '.$where,'','sorting',''.$this->config["limit"]);
							}
							if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0)	{
								//$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
									//$content.=$this->show_list($res,$template);
									//$content.=$this->show_image_list($res,$template);
								if ($this->conf["list."]["modeImage"]==1)	{
									$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_IMAGE_TEMPLATE###")));
									$content.=$this->show_image_list($res,$template);
								}	else	{
									$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
									$content.=$this->show_list($res,$template,true);
								}
							}	else	{
								$content.= $this->manageLabels(trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_ADVANCEDSEARCH_TEMPLATE###"))));
							}
						}	else	{
							// Else the search form is shown
							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_ADVANCEDSEARCH_TEMPLATE###")));
							$template = $this->cObj->substituteSubpart($template,"###NORESULTS###","",$recursive=0,$keepMarker=0);
							$content.= $this->manageLabels($template);
						}
					}
					$content = $this->cObj->substituteMarkerArray($content, $mS);
					$content = ereg_replace("###S_PRICE_([0-9]*)_([0-9]*)###", "", $content);
				break;
				
				case "SINGLE":
					//$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_SINGLE_TEMPLATE###")));
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'uid="'.$this->cObj->data["uid"].'"','','');
					$content = $this->show_product($res);
				break;
				
				case "ORDERSINFO":
					$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ORDERSINFO_TEMPLATE###")));
					$content = $this->ordersInfo($template);
					$content = $this->manageLabels($content);
				break;
				
				default:
				case "LIST":
					if(t3lib_div::_GP('accessorieID')!='')	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_accessories', 'uid='.t3lib_div::_GP('accessorieID'),'','','');
						$content.=$this->show_accessorie($res);
					}	elseif(t3lib_div::_GP('productID')!='' && (t3lib_div::_GP('detail')==true || t3lib_div::_GP('numCombination')==''))	{
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 AND uid='.t3lib_div::_GP('productID'),'','','');
						$content.=$this->show_product($res);
					}	else	{
					
						$lingua = t3lib_div::_GP("L");
						$testLingua = "";
						if ($lingua>0)	{
							$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'sys_language_uid='.t3lib_div::_GP("L").' AND deleted<>1 '.$timeCode.' AND hidden<>1 AND pid IN ('.$this->pid_list.')','',''.$orderBy,''.$this->config["limit"]);
							$uidExclude = "";
							while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2))		{
								$uidExclude .= $row2['l18n_parent'].",";
							}
							$uidExclude = substr($uidExclude,0,-1);
							$testLingua = "AND uid NOT IN (".$uidExclude.")";
							if ($uidExclude=="")
								$testLingua="";
							$testLingua .= " AND (sys_language_uid<=0 OR sys_language_uid=".$lingua.")";
						}	else	{
							$testLingua = "AND sys_language_uid<=0";
						}
						if(t3lib_div::_GP('orderBy')!='')	{
  	  				$orderBy = str_replace("_", " ", t3lib_div::_GP('orderBy'));
    					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$testLingua.' '.$timeCode.' AND hidden<>1 AND pid IN ('.$this->pid_list.')','',''.$orderBy,''.$this->config["limit"]);
    				}	else	{
    					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 '.$testLingua.' '.$timeCode.' AND hidden<>1 AND pid IN ('.$this->pid_list.')','','sorting',''.$this->config["limit"]);
    				}
						//$content.=$this->show_list($res,$template);
						//$content.=$this->show_image_list($res,$template);
						if ($this->conf["list."]["modeImage"]==1)	{
							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_IMAGE_TEMPLATE###")));
							$content.=$this->show_image_list($res,$template);
						}	else	{
							$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_LIST_TEMPLATE###")));
							$content.=$this->show_list($res,$template,true);
						}
					}
				break;
			}
		}
		
		$content = $this->manageLabels($content);
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	
	
	function show_image_list($res, $template)	{
	
		$content = "";
		
		$numProducts = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		$productsForRow = $this->conf["list."]["productsForRow"];
		$productsRowNumbers = $this->conf["list."]["productsRowNumbers"];
		$productsForPages = $productsRowNumbers*$productsForRow;
		
		$numOfPages = ceil($numProducts/$productsForPages);
		if ($numOfPages==1)
			$markerArray["###LABEL_SHOW_ALL###"]="";
		else
			$markerArray["###LABEL_SHOW_ALL###"]= "<a href='".$this->getLinkUrl("","","","","","",false,"all")."'>".$this->pi_getLL('###LABEL_SHOW_ALL###')."</a>";
		
		$markerArray["###PRODUCT_PAGES###"]="";
		
		for ($i=1; $i<=$numOfPages; $i++)	{
			if (t3lib_div::_GP("productPage")==$i || (t3lib_div::_GP("productPage")=="" && $i==1))	{
				$markerArray["###PRODUCT_PAGES###"].="<span class='shop_selectedPage'><a href='".$this->getLinkUrl("","","","","","",false,$i)."'>".$i."</a></span>";
			}	else	{
				$markerArray["###PRODUCT_PAGES###"].="<span class='shop_notSelectedPage'><a href='".$this->getLinkUrl("","","","","","",false,$i)."'>".$i."</a></span>";
			}
		}
		
		
		$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###HEADER###")));
		$content .= $this->manageLabels($this->cObj->substituteMarkerArray($headerTemplate, $markerArray));
		
		$partial = 0;
		$rowStartTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###ROW_START###")));
		$rowEndTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###ROW_END###")));
		$colTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###COLUMN###")));
		
		if (t3lib_div::_GP("productPage")=='all')	{
			$productsRowNumbers = ceil($numProducts/$productsForRow);
		}	else if (t3lib_div::_GP("productPage")!="")	{
			$pag = t3lib_div::_GP("productPage");
			for ($i=0; $i<($pag-1)*$productsForPages; $i++)	{
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			}
		}

		//while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
		for ($q=0; $q<$productsRowNumbers; $q++)	{
			$content .= $this->manageLabels($rowStartTemplate);
			$n = 0;
			for ($i=0; $i<$productsForRow && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); $i++)	{
				$markerArray["###COLUMN_WIDTH###"] = (100/$productsForRow)."%";
				$markerArray["###COLUMN_COLSPAN###"] = 1;
				$markerArray["###COLUMN_CLASS###"] = "shop_columnFull";
				$partial += $this->basket["products"][$row['uid']]["price"]*$this->basket["products"][$row['uid']]["num"];
				$content .= $this->manageLabels($this->cObj->substituteMarkerArray($this->getProduct($row,"listImage",true,$colTemplate), $markerArray));
				$n++;
			}
			if ($n<$productsForRow)	{
				$markerArray["###COLUMN_WIDTH###"] = ((100/$productsForRow)*($productsForRow-$n))."%";
				$markerArray["###COLUMN_COLSPAN###"] = $productsForRow-$n;
				$markerArray["###COLUMN_CLASS###"] = "shop_columnEmpty";
				$content .= $this->manageLabels($this->cObj->substituteMarkerArray($this->getProduct($row,"listImage",true,$colTemplate), $markerArray));
				$content .= $this->manageLabels($rowEndTemplate);
				break;
			}
			$content .= $this->manageLabels($rowEndTemplate);
			if (($GLOBALS['TYPO3_DB']->sql_num_rows($res)-$n)<1)
				break;
		}
		
		$footerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###FOOTER###")));
		$content .= $this->manageLabels($this->cObj->substituteMarkerArray($footerTemplate, $markerArray));
		
		//$template = $this->cObj->substituteMarkerArray($template, $markerArray);
		//$content.= $this->manageLabels($template);
		return $content;
	}
	
	
	
	
	
	/**
	 * This function shows the list of products
	 */
	function show_list($res, $template, $limitedItems=false)	{
			$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###HEADER###")));
			
			if(t3lib_div::_GP("orderBy")=="title")	{
				$markerArrayLabel["###LABEL_TITLE###"] = "<a href='".$this->getLinkUrl("","","title_desc")."'>".htmlspecialchars($this->pi_getLL("###LABEL_TITLE###"))."</a>";
			}	else	{
				$markerArrayLabel["###LABEL_TITLE###"] = "<a href='".$this->getLinkUrl("","","title")."'>".htmlspecialchars($this->pi_getLL("###LABEL_TITLE###"))."</a>";
			}
			
			$markerArrayLabel["###LABEL_IMAGE###"] = htmlspecialchars($this->pi_getLL("###LABEL_IMAGE###"));
			$markerArrayLabel["###LABEL_SUMMARY###"] = htmlspecialchars($this->pi_getLL("###LABEL_SUMMARY###"));
			
			if(t3lib_div::_GP("orderBy")=="price")	{
				$markerArrayLabel["###LABEL_PRICE###"] = "<a href='".$this->getLinkUrl("","","price_desc")."'>".htmlspecialchars($this->pi_getLL("###LABEL_PRICE###"))."</a>";
			}	else	{
				$markerArrayLabel["###LABEL_PRICE###"] = "<a href='".$this->getLinkUrl("","","price")."'>".htmlspecialchars($this->pi_getLL("###LABEL_PRICE###"))."</a>";
			}
			
			if ($limitedItems)	{
  			$numProducts = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
    		$productsForPages = $this->conf["list."]["maxItems"];
    		
    		$numOfPages = ceil($numProducts/$productsForPages);
    		if ($numOfPages==1)
    			$markerArrayLabel["###LABEL_SHOW_ALL###"]="";
    		else
    			$markerArrayLabel["###LABEL_SHOW_ALL###"]= "<a href='".$this->getLinkUrl("","","","","","",false,"all")."'>".$this->pi_getLL('###LABEL_SHOW_ALL###')."</a>";
    		
    		$markerArrayLabel["###PRODUCT_PAGES###"]="";
    		
    		for ($i=1; $i<=$numOfPages; $i++)	{
    			if (t3lib_div::_GP("productPage")==$i || (t3lib_div::_GP("productPage")=="" && $i==1))	{
    				$markerArrayLabel["###PRODUCT_PAGES###"].="<span class='shop_selectedPage'><a href='".$this->getLinkUrl("","","","","","",false,$i)."'>".$i."</a></span>";
    			}	else	{
    				$markerArrayLabel["###PRODUCT_PAGES###"].="<span class='shop_notSelectedPage'><a href='".$this->getLinkUrl("","","","","","",false,$i)."'>".$i."</a></span>";
    			}
    		}
    		
    		if (t3lib_div::_GP("productPage")=='all')	{
    			$productsForPages = $numProducts;
    		}	else if (t3lib_div::_GP("productPage")!="")	{
    			$pag = t3lib_div::_GP("productPage");
    			for ($i=0; $i<($pag-1)*$productsForPages; $i++)	{
    				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    			}
    		}
			}
			
			$content = "";
			$content .= $this->cObj->substituteMarkerArray($headerTemplate, $markerArrayLabel);
			$content = $this->manageLabels($content);
			
			$subTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###LIST###")));

			if ($limitedItems)	{
				for ($i=0; $i<$productsForPages && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); $i++)	{
					$content .= $this->getProduct($row,"listImage",true,$subTemplate);
				}
			}	else	{
  			$partial = 0;
  			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
  				//$partial += $row["price"]*$this->basket["products"][$row['uid']]["num"];
  				$partial += $this->basket["products"][$row['uid']]["price"]*$this->basket["products"][$row['uid']]["num"];
  				//$content .= $this->cObj->substituteMarkerArray($subTemplate, $this->getProduct($row,"listImage",true));
  				$content .= $this->getProduct($row,"listImage",true,$subTemplate);
  			}
  			
  			$markerArray["###BASKET_TOTAL###"] = $this->priceFormat($partial+$this->calculatedSums_tax["payment"]+$this->basket["shipping"]["priceTax"]);
  			$this->calculatedSums_tax["total"] = $markerArray["###BASKET_TOTAL###"];
  		}
  		
			$footerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###FOOTER###")));
			$markerArray["###PERSONAL_NOTE###"] = "";
			if ($this->basket["personinfo"]["NOTE"]!="")
				$markerArray["###PERSONAL_NOTE###"] = $this->basket["personinfo"]["NOTE"];
			$footerTemplate = $this->cObj->substituteMarkerArray($footerTemplate, $markerArray);
			$content.= $this->manageLabels($footerTemplate);
			
			return $content;
	}
	
	
	/**
	 * This function shows the details of a single product
	 */
	function show_product($res)	{
			$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ITEM_SINGLE_TEMPLATE###")));
			
			$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###HEADER###")));
			
			$content.= $this->manageLabels($headerTemplate);
			
			$subTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###DETAIL###")));
			
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
				//$content .= $this->cObj->substituteMarkerArray($subTemplate, $this->getProduct($row));
				$content .= $this->getProduct($row,"image",false,$subTemplate,true);
			}
			
			return $content;
	}
	
	/**
	 * This function shows the details of a single accessorie
	 */
	function show_accessorie($res)	{
			$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ACCESSORIE_TEMPLATE###")));
			
			$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###HEADER###")));
			
			$content.= $this->manageLabels($headerTemplate);
			
			$subTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###DETAIL###")));
			
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
				//$content .= $this->cObj->substituteMarkerArray($subTemplate, $this->getProduct($row));
				$content .= $this->getProduct($row,"image",false,$subTemplate);
			}
			
			return $content;
	}
	
	
	
	
	
	
	/**
	 * This function shows the list of products in the basket
	 */
	function show_basket($res)	{
			$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###BASKET_TEMPLATE###")));
			
			$introTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###INTRO###")));
			$content = "";
			$content .= $this->manageLabels($introTemplate);
			
			$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###HEADER###")));
			
			$markerArrayLabel["###LABEL_TITLE###"] = htmlspecialchars($this->pi_getLL("###LABEL_TITLE###"));
			$markerArrayLabel["###LABEL_IMAGE###"] = htmlspecialchars($this->pi_getLL("###LABEL_IMAGE###"));
			$markerArrayLabel["###LABEL_SUMMARY###"] = htmlspecialchars($this->pi_getLL("###LABEL_SUMMARY###"));
			
			$markerArrayLabel["###LABEL_PRICE###"] = htmlspecialchars($this->pi_getLL("###LABEL_PRICE###"));
			
			$content .= $this->cObj->substituteMarkerArray($headerTemplate, $markerArrayLabel);
			
			$subTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###LIST###")));
			
			$freeShipping = true;
			$resCat = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_category', 'deleted<>1 AND code="noshippingcost"','','','');
			$rowCat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCat);
			
			$partial = 0;
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
				//$partial += $row["price"]*$this->basket["products"][$row['uid']]["num"];
				$partial += $this->basket["products"][$row['uid']]["price"]*$this->basket["products"][$row['uid']]["num"];
				//$content .= $this->cObj->substituteMarkerArray($subTemplate, $this->getProduct($row,"listImage",true));
				$content .= $this->getProduct($row,"listImage",true,$subTemplate);
				
				// Verifico se tutti i prodotti prevedono la spedizione gratuita
				if ($this->basket["products"][$row['uid']]["category"]!=$rowCat["uid"])
					$freeShipping = false;
			}
			
			$markerArray["###BASKET_TOTAL###"] = $this->priceFormat($partial);
			$this->calculatedSums_tax["total"] = $this->priceFormat($partial);
			//$this->calculatedSums_tax["total"] = $partial;
			
			$this->basket["totaleProdotti"] = $partial;
			$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
			
			$footerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###FOOTER###")));
			
			if ($this->conf["minOrder"]>0 && $partial<$this->conf["minOrder"] && !$freeShipping)	{
				$markerArray["###BASKET_PROCEED###"] = " disabled='disabled'";
			}	else	{
				$markerArray["###BASKET_PROCEED###"] = "";
			}
			
			$footerTemplate = $this->cObj->substituteMarkerArray($footerTemplate, $markerArray);
			$content.= $this->manageLabels($footerTemplate);
			
			return $content;
	}
	
	
	
	/**
	 * This function shows personal info template to ask to the client who is him.
	 */
	function show_personal_info($template,$complete=true)	{
			
			while(list($marker,$value)=each($this->basket["personinfo"]))	{
				if (substr($value,0,3)=="###")	{
					$markerArray["###PERSONAL_".$marker."###"] = "";
					$markerArray["###DELIVERY_".$marker."###"] = "";
				}	else	{
					$markerArray["###PERSONAL_".$marker."###"] = $value;
					$markerArray["###DELIVERY_".$marker."###"] = "";
				}
			}
			$markerArray["###DELIVERY_COUNTRY###"] = $this->basket["shipping"]["title"];
			if (is_array($this->basket["delivery"]))	{
  			while(list($marker,$value)=each($this->basket["delivery"]))	{
  				if (substr($value,0,3)=="###" || $value=="")	{
  					$markerArray["###DELIVERY_".$marker."###"] = "";
  				}	else	{
  					$markerArray["###DELIVERY_".$marker."###"] = $value;
  				}
  			}
  		}
			if ($complete)	{
				$markerArray["###LABEL_PERSONAL_INFO_NOTCOMPLETE###"] = "";
				$template = $this->cObj->substituteSubpart($template,"###INCOMPLETE###","",$recursive=0,$keepMarker=0);
			}	else	{
				$markerArray["###PERSONAL_USER###"] = "";
				$markerArray["###PERSONAL_PASSWORD###"] = "";
			}
			
			if ($markerArray["###PERSONAL_PRIVATE###"]==0){
				$markerArray["###PERSONAL_PRIVATE_P###"]=" checked ";
				$markerArray["###PERSONAL_PRIVATE_A###"]="";
				$markerArray["###PERSONAL_PRIVATE###"]=htmlspecialchars($this->pi_getLL("###LABEL_PERSONAL_INFO_PRIVATE_P###"));
			}	else	{
				$markerArray["###PERSONAL_PRIVATE_P###"]="";
				$markerArray["###PERSONAL_PRIVATE_A###"]=" checked ";
				$markerArray["###PERSONAL_PRIVATE###"]=htmlspecialchars($this->pi_getLL("###LABEL_PERSONAL_INFO_PRIVATE_A###"));
			}
			
			if ($markerArray["###PERSONAL_AUTHORIZATION###"]==1){
				$markerArray["###PERSONAL_AUTHORIZATION_V###"]=" checked ";
			}	else	{
				$markerArray["###PERSONAL_AUTHORIZATION_V###"]="";
			}
			
			if ($markerArray["###PERSONAL_CONDITIONS###"]==1){
				$markerArray["###PERSONAL_CONDITIONS_V###"]=" checked ";
			}	else	{
				$markerArray["###PERSONAL_CONDITIONS_V###"]="";
			}
			
			$listRequired = explode(",",trim($this->conf["requiredFields"]));
			foreach($listRequired as $field)	{
				$markerArray["###REQUIRED_".strtoupper($field)."###"] = trim($this->conf["requiredFieldsSymbol"]);
			}
			
			$markerArray["###PERSONAL_COUNTRY_SELECT###"] = $this->generateSelectForPerson("shipping");
			
				// Shipping
  		$this->calculatedSums_tax["shipping"]=doubleVal($this->basketExtra["shipping."]["priceTax"]);
  		$this->calculatedSums_no_tax["shipping"]=doubleVal($this->basketExtra["shipping."]["priceNoTax"]);
  		$perc = doubleVal($this->basketExtra["shipping."]["percentOfGoodstotal"]);
  		if ($perc)	{
  			$this->calculatedSums_tax["shipping"]+= $this->calculatedSums_tax["goodstotal"]/100*$perc;
  			$this->calculatedSums_no_tax["shipping"]+= $this->calculatedSums_no_tax["goodstotal"]/100*$perc;
  		}
  		if ($this->basketExtra["shipping."]["calculationScript"])	{
  			$calcScript = $GLOBALS["TSFE"]->tmpl->getFileName($this->basketExtra["shipping."]["calculationScript"]);
  			if ($calcScript)	{
  				$this->includeCalcScript($calcScript,$this->basketExtra["shipping."]["calculationScript."]);
  			}
  		}
  		$markerArray["###PRICE_SHIPPING_PERCENT###"] = $perc;
  		$markerArray["###PRICE_SHIPPING_TAX###"] = $this->priceFormat($this->calculatedSums_tax["shipping"]);
  		$markerArray["###PRICE_SHIPPING_NO_TAX###"] = $this->priceFormat($this->calculatedSums_no_tax["shipping"]);
  
  		$markerArray["###SHIPPING_SELECTOR###"] = $this->generateRadioSelect("shipping");
  		$markerArray["###SHIPPING_IMAGE###"] = $this->cObj->IMAGE($this->basketExtra["shipping."]["image."]);
  		$markerArray["###SHIPPING_TITLE###"] = $this->basketExtra["shipping."]["title"];
			
			$markerArray["###PERSONAL_NOTE###"] = "";
			if ($this->basket["personinfo"]["NOTE"]!="")
				$markerArray["###PERSONAL_NOTE###"] = $this->basket["personinfo"]["NOTE"];
			
			//$markerArray["###PERSONAL_USER###"] = "";
			//$markerArray["###PERSONAL_PASSWORD###"] = "";
			$template = $this->cObj->substituteMarkerArray($template, $markerArray);
			
			$template = ereg_replace("(###REQUIRED_)[A-Z]+(###)","",$template);
			
			$content.= $this->manageLabels($template);
			
			return $content;
	}
	
	/**
	 * This function shows shipping and payment template
	 */
	function show_payment_shipping()	{
		
		$freeShipping = true;
		$resCat = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_category', 'deleted<>1 AND code="noshippingcost"','','','');
		$rowCat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCat);
		
		$list = array_keys($this->basket["products"]);
		$where = "uid IN (";
		foreach($list as $id)
		{
			if ($this->basket["products"][$id]["num"]>0)	{
				$where.=$id.",";
				
				// Verifico se tutti i prodotti prevedono la spedizione gratuita
				if ($this->basket["products"][$id]["category"]!=$rowCat["uid"])
					$freeShipping = false;
			}
		}
		$where = substr($where,0,-1).")";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', $where,'','','');

		$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###SHIPPING_TEMPLATE###")));

		if ($freeShipping)	{
			$this->basket["shipping"]["priceTax"] = 0;
			$this->basket["shipping"]["priceNoTax"] = 0;
			$this->basket["noshippingcost"] = 1;
			//$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
		}	else	{
			$this->basket["noshippingcost"] = 0;
			//$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
		}
//echo($this->basket["noshippingcost"]);
//t3lib_div::debug($this->basket);
			// Payment
		$this->calculatedSums_tax["payment"]=doubleVal($this->basketExtra["payment."]["priceTax"]);
		$this->calculatedSums_no_tax["payment"]=doubleVal($this->basketExtra["payment."]["priceNoTax"]);
		$perc = doubleVal($this->basketExtra["payment."]["percentOfGoodstotal"]);
		if ($perc)	{
			$this->calculatedSums_tax["payment"]+= $this->calculatedSums_tax["goodstotal"]/100*$perc;
			$this->calculatedSums_no_tax["payment"]+= $this->calculatedSums_no_tax["goodstotal"]/100*$perc;
		}
		if ($this->basketExtra["payment."]["calculationScript"])	{
			$calcScript = $GLOBALS["TSFE"]->tmpl->getFileName($this->basketExtra["payment."]["calculationScript"]);
			if ($calcScript)	{
				$this->includeCalcScript($calcScript,$this->basketExtra["payment."]["calculationScript."]);
			}
		}

		$markerArray["###PRICE_PAYMENT_PERCENT###"] = $perc;
		$markerArray["###PRICE_PAYMENT_TAX###"] = $this->priceFormat($this->calculatedSums_tax["payment"]);
		$markerArray["###PRICE_PAYMENT_NO_TAX###"] = $this->priceFormat($this->calculatedSums_no_tax["payment"]);

		$markerArray["###PAYMENT_SELECTOR###"] = $this->generateRadioSelect("payment");
		$markerArray["###PAYMENT_IMAGE###"] = $this->cObj->IMAGE($this->basketExtra["payment."]["image."]);
		$markerArray["###PAYMENT_TITLE###"] = $this->basketExtra["payment."]["title"];
		
		$markerArray["###SHIPPING_TITLE###"] = $this->basket["shipping"]["title"];
		$markerArray["###PRICE_SHIPPING_TAX###"] = $this->priceFormat($this->basket["shipping"]["priceTax"]);
		$this->calculatedSums_tax["shipping"] = $this->basket["shipping"]["priceTax"];
		$this->calculatedSums_no_tax["shipping"] = $this->basket["shipping"]["priceNoTax"];

			// This is the total for everything
		$this->calculatedSums_tax["total"]+= $this->calculatedSums_tax["payment"];
		//$this->calculatedSums_tax["total"]+= $this->calculatedSums_tax["shipping"];
		$this->calculatedSums_tax["total"]+= $this->basket["shipping"]["priceTax"];

		$this->calculatedSums_no_tax["total"] = $this->calculatedSums_no_tax["goodstotal"];
		$this->calculatedSums_no_tax["total"]+= $this->calculatedSums_no_tax["payment"];
		$this->calculatedSums_no_tax["total"]+= $this->basket["shipping"]["priceNoTax"];

		$markerArray["###PRICE_TOTAL_TAX###"] = $this->priceFormat($this->calculatedSums_tax["total"]);
		$markerArray["###PRICE_TOTAL_NO_TAX###"] = $this->priceFormat($this->calculatedSums_no_tax["total"]);
		
		$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###SHIPPING###")));
		$content .= $this->cObj->substituteMarkerArray($headerTemplate, $markerArray);
		
		$listTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###PRODUCTS###")));
		$content .= $this->show_list($res, $listTemplate);
		
		$infoTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###PERSONAL_INFO###")));
		$content .= $this->show_personal_info($infoTemplate);
		
		return $content;
	}
	
	
	/**
	 * This function finalizes the order.
	 */
		function show_finalize()	{
		
		if ($this->basket["payment"]["bankcode"]=="paypal")
			$transactionBS = $this->decryptDataFromPayPal("");
		if ($this->basket["payment"]["bankcode"]=="gestpay")
			$transactionBS = $this->decryptDataFromGestPay("");
		if ($this->basket["payment"]["bankcode"]=="sella")
			$transactionBS = $this->decryptDataFromBancaSella("");
		if ($this->basket["payment"]["bankcode"]=="authorize")	{
			$transactionBS = $this->decryptDataFromAuthorize("");
			if ($transactionBS["response"]==3)	{
				// Transaction failed (error)
				$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###AUTHORIZE_ERROR_TEMPLATE###")));
				$rA["###REASON_CODE###"] = $transactionBS["reason"];
				$rA["###REASON_TEXT###"] = $transactionBS["reason_text"];
				$content = $this->manageLabels($template);
				$content = $this->cObj->substituteMarkerArray($content, $rA);
				return $content;
			}	elseif ($transactionBS["response"]==2)	{
				// Transaction declined
				$list = array_keys($this->basket["products"]);
				$where = "uid IN (";
				foreach($list as $id)
				{
					if ($this->basket["products"][$id]["num"]>0)	{
						$where.=$id.",";
						$isEmpty = false;
					}
				}
				$where = substr($where,0,-1).")";
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', $where,'',''.$orderBy,''.$this->config["limit"]);
				return $this->show_basket($res);
			}
		}
		
		/*$freeShipping = true;
		$resCat = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_category', 'deleted<>1 AND code="noshippingcost"','','','');
		$rowCat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCat);
		
		$list = array_keys($this->basket["products"]);
		$where = "uid IN (";
		foreach($list as $id)
		{
			if ($this->basket["products"][$id]["num"]>0)	{
				$where.=$id.",";
				
				// Verifico se tutti i prodotti prevedono la spedizione gratuita
				if ($this->basket["products"][$id]["category"]!=$rowCat["uid"])
					$freeShipping = false;
			}
		}*/
		
		$list = array_keys($this->basket["products"]);
		$where = "uid IN (";
		foreach($list as $id)
		{
			if ($this->basket["products"][$id]["num"]>0)	{
				$where.=$id.",";
			}
		}
		$where = substr($where,0,-1).")";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', $where,'','','');

		$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###FINALIZE_TEMPLATE###")));
		
		/*if ($freeShipping)	{
			$this->basket["shipping"]["priceTax"] = 0;
			$this->basket["shipping"]["priceNoTax"] = 0;
			$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
		}*/
		
		$markerArray["###PAYMENT_TITLE###"] = $this->basket["payment"]["title"];
		$markerArray["###PRICE_PAYMENT_TAX###"] = $this->priceFormat($this->basket["payment"]["priceTax"]);
		$this->calculatedSums_tax["payment"] = $this->basket["payment"]["priceTax"];
		
		$markerArray["###SHIPPING_TITLE###"] = $this->basket["shipping"]["title"];
		$markerArray["###PRICE_SHIPPING_TAX###"] = $this->priceFormat($this->basket["shipping"]["priceTax"]);
		$this->calculatedSums_tax["shipping"] = $this->basket["shipping"]["priceTax"];

		
		$infoTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###PERSONAL_INFO###")));
		if ($this->basket["payment"]["message"]=="")	{
			$infoTemplate = $this->cObj->substituteSubpart($infoTemplate,"###PAYMENT_INFO###","",$recursive=0,$keepMarker=0);
		}	else	{
			$paymentTemplate = trim($this->cObj->getSubpart($infoTemplate,$this->spMarker("###PAYMENT_INFO###")));
			$mA["###INFO_PAGAMENTO###"] = $this->basket["payment"]["message"];
			$paymentTemplate = $this->cObj->substituteMarkerArray($paymentTemplate, $mA);
			$infoTemplate = $this->cObj->substituteSubpart($infoTemplate,"###PAYMENT_INFO###",$paymentTemplate,$recursive=0,$keepMarker=0);
		}
		$content .= $this->show_personal_info($infoTemplate);
		
		$listTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###PRODUCTS###")));
		$content .= $this->show_list($res, $listTemplate);
		
		$headerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###SHIPPING###")));
		$content .= $this->cObj->substituteMarkerArray($headerTemplate, $markerArray);
		
		$footerTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###FOOTER###")));
		$markerArray["###BASKET_TOTAL###"] = $this->calculatedSums_tax["total"];
		$markerArray["###PERSONAL_NOTE###"] = "";
		if ($this->basket["personinfo"]["NOTE"]!="")
			$markerArray["###PERSONAL_NOTE###"] = $this->basket["personinfo"]["NOTE"];
		$content .= $this->cObj->substituteMarkerArray($footerTemplate, $markerArray);
					
		$markerArray["###PERSONAL_EMAIL###"] = $this->basket["personinfo"]["EMAIL"];
		
		$user = $this->manageUsers();
		$orderID = $this->insertOrder($user["idCustomer"], $user["idDelivery"],$this->manageLabels($content),$transactionBS["###PAY1_SHOPTRANSACTIONID###"],$markerArray);
		
		$markerArray["###ORDERID###"] = $orderID;
		
		$content = $this->manageLabels($content);
		$content = $this->cObj->substituteMarkerArray($content, $markerArray);
		
		$this->send_email($this->manageLabels($content),"###EMAIL_ORDER_SUBJECT###",$orderID);
				
		
		// Clear the basket
		$this->basket["products"] = "";
		$this->finalize = true;
		$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
		
		return $content;
	}
	
	
	
	/**
	 * This function finalizes the order.
	 */
		function show_bank()	{
		$list = array_keys($this->basket["products"]);
		$where = "uid IN (";
		foreach($list as $id)
		{
			if ($this->basket["products"][$id]["num"]>0)	{
				$where.=$id.",";
			}
		}
		$where = substr($where,0,-1).")";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', $where,'','','');

		$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###ONLINEBANK_TEMPLATE###")));
		
		$this->calculatedSums_tax["payment"] = $this->basket["payment"]["priceTax"];
		$this->calculatedSums_tax["shipping"] = $this->basket["shipping"]["priceTax"];
		
		$partial = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
			//$partial += $row["price"]*$this->basket["products"][$row['uid']]["num"];
			$partial += $this->basket["products"][$row['uid']]["price"]*$this->basket["products"][$row['uid']]["num"];
		}
		
		$markerArray["###BASKET_TOTAL###"] = $this->priceFormat($partial+$this->calculatedSums_tax["payment"]+$this->calculatedSums_tax["shipping"]);
		$markerArray["###BASKET_TOTAL_NOFORMAT###"] = $partial+$this->calculatedSums_tax["payment"]+$this->calculatedSums_tax["shipping"];
		$this->calculatedSums_tax["total"] = $markerArray["###BASKET_TOTAL###"];
		
		
		//$this->basket["payment."]["key"]
		if ($this->basket["payment"]["bankcode"]=="paypal")
			$markerArray["###FORM_URL_ONLINEBANK###"] = $this->calculatePayPalParameters($markerArray);
		if ($this->basket["payment"]["bankcode"]=="gestpay")
			$markerArray["###FORM_URL_ONLINEBANK###"] = $this->calculateGestPayParameters($markerArray);
		if ($this->basket["payment"]["bankcode"]=="sella")
			$markerArray["###FORM_URL_ONLINEBANK###"] = $this->calculateBancaSellaParameters($markerArray);
		if ($this->basket["payment"]["bankcode"]=="authorize")	{
			$markerArray = $this->calculateAuthorizeParameters($markerArray);
			$template = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###AUTHORIZE_TEMPLATE###")));
		}
		
		$markerArray["###BANK_NAME###"] = $this->basket["payment"]["bankname"];
		$markerArray["###BANK_LINK###"] = $this->basket["payment"]["banklink"];
		
		return $this->cObj->substituteMarkerArray($this->manageLabels($template), $markerArray);
	}
	
	
	/**
	 * This function returns the product title. Useful for replace the page title in the product detail page
	 */
	function product_title($content="", $conf="")
    {
		 $GLOBALS["TSFE"]->set_no_cache();
		 global $TSFE;
		 $prefix = ereg_replace(":.*", ':', $content);
		 $TSFE->set_no_cache();
		 //$title = $TSFE->page['tx_ecisearchengine_eci_searchengine_title'];
		 $title="";
		 if(t3lib_div::_GP('productID')!='')	{
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 AND uid='.t3lib_div::_GP('productID'),'','','');
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					if ($row["pagetitle"]!="")
						$title = $row["pagetitle"];
					else
						$title = $row["title"];
				}
		 if($title=="")
		 {
			$title = $TSFE->page['title'];
		 }
	//echo("TITLE".$title);
		 return $title;
    }
	
	/**
	 * This function initializes the data of a single product
	 */
	function getProduct($row,$imgRender="image",$linkTitle=false,$template,$detail=false)	{
	
			// Setta il titolo della pagina con il nome del prodotto
			//t3lib_TStemplate::printTitle($row["title"],0,1);
		
		if ($this->basket["products"][$row['uid']]["page"]=="")
			$markerArray["###PRODUCT_PAGE###"] = $GLOBALS["TSFE"]->id;
		else
			$markerArray["###PRODUCT_PAGE###"] = $this->basket["products"][$row['uid']]["page"];
		
  		// Get image
  		$imageNum = 0;
  		$theImgCode="";
    $imgs = explode(",",$row["image"]);
  		$val = $imgs[0];
  		while(list($c,$val)=each($imgs))	{
  			//if ($c==$imageNum)	break;
  			if ($val)	{
  				$this->conf[$imgRender."."]["file"] = "uploads/tx_extendedshop/".$val;
  			} else {
  				$this->conf[$imgRender."."]["file"] = $this->conf["noImageAvailable"];
  			}
  			$this->conf[$imgRender."."]["altText"] = '"'.$row["title"].'"';
if (t3lib_div::_GP('debug')==1)
	t3lib_div::debug($this->conf[$imgRender."."]);
  			if($imgRender=="listImage")	{
  				//$link = $this->getLinkUrl($row['pid'],"","",$row['uid']);
				$link = $this->getLinkUrl($markerArray["###PRODUCT_PAGE###"],"","",$row['uid'],"","","","",$row['pid']);
    			$theImgCode.='<a href="'.$link.'">'.$this->cObj->IMAGE($this->conf[$imgRender."."]).'</a>';
  			}	else	{
  				$theImgCode.=$this->cObj->IMAGE($this->conf[$imgRender."."]);
  				$imgZoom = $this->conf['zoomimage.'];
  				
  				$confZoom['bodyTag'] = '<body bgColor=white leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">';
			  $confZoom['wrap'] = '<a href="javascript: close();"> | </a>';
			  $confZoom['width'] = '400';
			  $confZoom['JSwindow'] = '1';
			  $confZoom['JSwindow.newWindow'] = '1';
			  $confZoom['JSwindow.expand'] = '17,20';
			  $confZoom['enable'] = '1';
  				
  				$markerArray["###PRODUCT_ZOOM###"] = $this->cObj->imageLinkWrap($this->cObj->IMAGE($imgZoom), "uploads/tx_extendedshop/".$val, $confZoom);
  			}
  		}
  		
  		
  		// Gestione "avanti" e "indietro"
  		if ($detail)	{
    		$resAI = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 AND pid='.$row["pid"],'','sorting','');
    		$trovatoAI = false;
    		$iAI = 0;
    		$iTrovato = -1;
    		$numAI = $GLOBALS['TYPO3_DB']->sql_num_rows($resAI);
    		if ($numAI>1)	{
      		while ($rowAI = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resAI))	{
      			if ($rowAI["uid"]==$row["uid"])	{
      				// Sono arrivato al centro
      				$trovatoAI = true;
      				$iTrovato = $iAI;
      			}
      			else	{
    					if (!$trovatoAI || ($iAI==$numAI-1 && $iTrovato==0))	{
        				$markerArray["###PRODUCT_LINK_PREVIOUS###"] = "<a href='".$this->getLinkUrl($row['pid'],'','',$rowAI['uid'])."'>".htmlspecialchars($this->pi_getLL("###LABEL_PRODUCTPREVIOUS###"))."</a>";
        				
        				// Anteprima per previous
        				$imgs = explode(",",$rowAI["image"]);
                  		$val = $imgs[0];
        				$this->conf["previous."]["file"] = "uploads/tx_extendedshop/".$val;
        				$this->conf["previous."]["altText"] = '"'.$rowAI["title"].'"';
        				$markerArray["###PRODUCT_IMG_PREVIOUS###"] = '<a href="'.$this->getLinkUrl($row['pid'],'','',$rowAI['uid']).'">'.$this->cObj->IMAGE($this->conf["previous."]).'</a>';
        				
        			}
        			if (($trovatoAI && $iAI==$iTrovato+1) || $iAI==0)	{
        				$markerArray["###PRODUCT_LINK_NEXT###"] = "<a href='".$this->getLinkUrl($row['pid'],'','',$rowAI['uid'])."'>".htmlspecialchars($this->pi_getLL("###LABEL_PRODUCTNEXT###"))."</a>";
        				
        				// Anteprima per next
        				$imgs = explode(",",$rowAI["image"]);
                  		$val = $imgs[0];
        				$this->conf["next."]["file"] = "uploads/tx_extendedshop/".$val;
        				$this->conf["next."]["altText"] = '"'.$rowAI["title"].'"';
        				$markerArray["###PRODUCT_IMG_NEXT###"] = '<a href="'.$this->getLinkUrl($row['pid'],'','',$rowAI['uid']).'">'.$this->cObj->IMAGE($this->conf["next."]).'</a>';
        			}
        		}
      			$iAI++;
      		}
    		}	else	{
    			$template = $this->cObj->substituteSubpart($template,"###LINK_PRODUCTS###","",$recursive=0,$keepMarker=0);
    		}
  		}
  		
		
  		if($linkTitle)	{
  			$markerArray["###PRODUCT_TITLE###"] = "<a href='".$this->getLinkUrl($markerArray["###PRODUCT_PAGE###"],"","",$row['uid'],"","","","",$row['pid'])."'>".$row["title"]."</a>";
  		}	else	{
  			$markerArray["###PRODUCT_TITLE###"] = $row["title"];
  		}
  		$markerArray["###PRODUCT_UID###"] = $row["uid"];
  		$markerArray["###PRODUCT_CODE###"] = $row["code"];
  		$markerArray["###PRODUCT_SUMMARY###"] = $row["summary"];
		// MLC temp fix while RTE isn't saving HTML
  		$markerArray["###PRODUCT_DESCRIPTION###"] = preg_replace( "#\n#", "$0<br /><br />", $row["description"] );
  		$markerArray["###PRODUCT_IMAGE###"] = $theImgCode;
  		$markerArray["###PRODUCT_CATEGORY###"] = $row["category"];
  		
  		//if ($row["offertprice"]=="" && $row["discount"]=="" && $row["price"]=="")	{
  		if ($row=="")	{
  			$template = $this->cObj->substituteSubpart($template,"###PRICE###","",$recursive=0,$keepMarker=0);
  			$template = $this->cObj->substituteSubpart($template,"###PRICEDISCOUNT###","",$recursive=0,$keepMarker=0);
  		}
  		if ($row["offertprice"]!=0 || $row["discount"]!=0)	{
  			$markerArray["###PRODUCT_PRICE###"] = $this->priceFormat($row["price"]);
  			$markerArray["###PRODUCT_DISCOUNT###"] = $this->calculateDiscount($row);
  			$markerArray["###PRODUCT_OFFERTPRICE###"] = $this->calculateOffertPrice($row);
  			$markerArray["###PRODUCT_OFFERTPRICENOTAX###"] = $this->calculateOffertPrice($row,'notax');
  			$markerArray["###PRODUCT_SELLPRICE###"] = $this->calculateOffertPrice($row,"",false);
				$sellPrice = $this->calculateOffertPrice($row,"",false);
 				$template = $this->cObj->substituteSubpart($template,"###PRICE###","",$recursive=0,$keepMarker=0);
  				
  		}	else	{
  			$markerArray["###PRODUCT_PRICE###"] = $this->priceFormat($row["price"]);
  			$markerArray["###PRODUCT_PRICENOTAX###"] = $this->priceFormat($row["pricenotax"]);
  			$markerArray["###PRODUCT_SELLPRICE###"] = $row["price"];
				$sellPrice = $row["price"];
  			$template = $this->cObj->substituteSubpart($template,"###PRICEDISCOUNT###","",$recursive=0,$keepMarker=0);
  		}
  		
  		$markerArray["###PRODUCT_TOTAL_PRICE###"] = $this->priceFormat($this->basket["products"][$row['uid']]["num"] * $sellPrice);
  		
  		$markerArray["###PRODUCT_INSTOCK###"] = $row["instock"];
  		
  		/*$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_categories', 'uid='.$row["category"]);
  		while($cat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
				$markerArray["###PRODUCT_CATEGORY###"] = $cat["title"];
			}*/
  		
  		$markerArray["###PRODUCT_WWW###"] = $row["www"];
  		$markerArray["###PRODUCT_ORDERED###"] = $row["ordered"];
  		$markerArray["###PRODUCT_WEIGHT###"] = $row["weight"];
  		$markerArray["###PRODUCT_VOLUME###"] = $row["volume"];
  		
  		$disabledSelect="";
  		if ($GLOBALS["TSFE"]->id == $this->config["pid_basket"])
  			$disabledSelect=" disabled";
  			
  		if ($this->basket["products"][$row["uid"]]["combinations"]>0)	{
				$max = $this->basket["products"][$row["uid"]]["combinations"];
			}	else	{
				if ($row["sizes"]=="" && $row["colors"]=="")
					$max = 0;
				else
					$max = 1;
			}
			$markerArray["###PRODUCT_COMBINATIONS###"] = $max;
			if ($max > 0)	{
  			$markerArray["###ADD_COMBINATION###"] = $this->getLinkUrl($id="","","",$row["uid"],"",$max+1,$detail);
  			$markerArray["###CLEAR_COMBINATION###"] = $this->getLinkUrl($id="","","",$row["uid"],"",1,$detail);
  			$markerArray["###PRODUCT_ADDCOMBINATIONS###"] = $max;
  		}	else	{
  			$markerArray["###ADD_COMBINATION###"] = "";
  			$markerArray["###CLEAR_COMBINATION###"] = "";
  			$markerArray["###PRODUCT_ADDCOMBINATIONS###"] = 1;
  		}
			$subTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###COMBINATIONS###")));  	
			$subContent = "";	
  		for ($i=1; $i<=$max; $i++)	{
    		if ($row["sizes"]!="")	{
  	  		$markerArray["###FIELD_SIZE_NAME###"]="product[".$row["uid"]."][sizes][".$i."]";
  				$markerArray["###FIELD_SIZE_VALUE###"]=$this->basket["products"][$row["uid"]]["sizes"][$i] ? $this->basket["products"][$row["uid"]]["sizes"][$i] : "";
    			$prodSizeText = '';
    			$sizesList = $row["sizes"];
					if (strrpos($sizesList,"!")==(strlen($sizesList)-1))
						$colorsList = substr($sizesList,0,-1);
    			$prodSizeTmp = explode('!', $sizesList);
  	  		foreach ($prodSizeTmp as $prodSize)	{
    				if ($prodSize==$markerArray["###FIELD_SIZE_VALUE###"])	{
    					$prodSizeText = $prodSizeText . '<OPTION value="'.$prodSize.'" selected>'.$prodSize.'</OPTION>';
    					$markerArray["###PRODUCT_SIZES_LABEL###"] = $prodSize;
    				}	else	{
    					if ($prodSize!="")
    						$prodSizeText = $prodSizeText . '<OPTION value="'.$prodSize.'">'.$prodSize.'</OPTION>';
    				}
  	  		}
  				$markerArray["###PRODUCT_SIZES###"] = '<SELECT'.$disabledSelect.' name="'.$markerArray["###FIELD_SIZE_NAME###"].'" rows="1">'.$prodSizeText.'</SELECT>';
  			}	else	{
  				$markerArray["###PRODUCT_SIZES###"] = "";
  			}
  			
  			if ($row["colors"]!="")	{
  	  		$markerArray["###FIELD_COLOR_NAME###"]="product[".$row["uid"]."][colors][".$i."]";
  				$markerArray["###FIELD_COLOR_VALUE###"]=$this->basket["products"][$row["uid"]]["colors"][$i] ? $this->basket["products"][$row["uid"]]["colors"][$i] : "";
					$prodSizeText = '';
					$colorsList = $row["colors"];
					if (strrpos($colorsList,"!")==(strlen($colorsList)-1))
						$colorsList = substr($colorsList,0,-1);
    			$prodSizeTmp = explode('!', $colorsList);
  	  		foreach ($prodSizeTmp as $prodSize)	{
    				if ($prodSize==$markerArray["###FIELD_COLOR_VALUE###"])	{
    					$prodSizeText = $prodSizeText . '<OPTION value="'.$prodSize.'" selected>'.$prodSize.'</OPTION>';
    					$markerArray["###PRODUCT_COLORS_LABEL###"] = $prodSize;
    				}	else	{
    					if ($prodSize!="")
    						$prodSizeText = $prodSizeText . '<OPTION value="'.$prodSize.'">'.$prodSize.'</OPTION>';
    				}
  	  		}
  				$markerArray["###PRODUCT_COLORS###"] = '<SELECT'.$disabledSelect.' name="'.$markerArray["###FIELD_COLOR_NAME###"].'" rows="1">'.$prodSizeText.'</SELECT>';
  			}	else	{
  				$markerArray["###PRODUCT_COLORS###"] = "";
  			}
  			
  			$subContent .= $this->cObj->substituteMarkerArray($subTemplate, $markerArray);
  			if ($i<$max)
  				$subContent .= "<br />";
  		}
  		
  		$template = $this->cObj->substituteSubpart($template,"###COMBINATIONS###",$subContent,$recursive=0,$keepMarker=0);
  		$subTemplate2 = trim($this->cObj->getSubpart($template,$this->spMarker("###LINKCOMBINATIONS###")));  
			if ($max>0)	{
				$subContent2 = $this->cObj->substituteMarkerArray($subTemplate2, $markerArray);
				$template = $this->cObj->substituteSubpart($template,"###LINKCOMBINATIONS###",$subContent2,$recursive=0,$keepMarker=0);
			}	else	{
				$template = $this->cObj->substituteSubpart($template,"###LINKCOMBINATIONS###","",$recursive=0,$keepMarker=0);
			}
			
			
			// Managing correlated products
			if($row["correlatedproducts"]!="")	{
  			$resCP = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'uid IN ('.$row["correlatedproducts"].')');
  			$theImgCodeCP="<TABLE><TR>";
  			while($rowCP = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCP))		{
	  			$imageNumCP = 0;
    			$imgsCP = explode(",",$rowCP["image"]);
    			$valCP = $imgsCP[0];
    			while(list($cCP,$valCP)=each($imgsCP))	{
    				//if ($c==$imageNum)	break;
	    			if ($valCP)	{
  	  				$this->conf["correlatedImage."]["file"] = "uploads/tx_extendedshop/".$valCP;
    				} else {
    					$this->conf["correlatedImage."]["file"] = $this->conf["noImageAvailable"];
    				}
    				$this->conf["correlatedImage."]["altText"] = $rowCP['title'];
    				$link = $this->getLinkUrl($rowCP['pid'],"","",$rowCP['uid']);
    				$theImgCodeCP.='<TD><a href="'.$link.'">'.$rowCP["title"].'</a><br /><a href="'.$link.'">'.$this->cObj->IMAGE($this->conf["correlatedImage."]).'</a></TD>';
    			}
    		}
    		$theImgCodeCP.="</TR></TABLE>";
    		$markerArray["###PRODUCT_CORRELATEDPRODUCTS###"] = $theImgCodeCP;
    		$markerArray["###LABEL_CORRELATEDPRODUCTS###"] = htmlspecialchars($this->pi_getLL("###LABEL_CORRELATEDPRODUCTS###"));

    		$subTemplate2 = trim($this->cObj->getSubpart($template,$this->spMarker("###CORRPRODUCTS###")));  
				$subContent2 = $this->cObj->substituteMarkerArray($subTemplate2, $markerArray);
				$template = $this->cObj->substituteSubpart($template,"###CORRPRODUCTS###",$subContent2,$recursive=0,$keepMarker=0);
    	}	else	{
    		$markerArray["###PRODUCT_CORRELATEDPRODUCTS###"] = "";
    		$markerArray["###LABEL_CORRELATEDPRODUCTS###"] = "";
    		$template = $this->cObj->substituteSubpart($template,"###CORRPRODUCTS###","",$recursive=0,$keepMarker=0);
    	}
    	
    	// Managing correlated accessories
			if($row["correlatedaccessories"]!="")	{
  			$resCP = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_accessories', 'uid IN ('.$row["correlatedaccessories"].')');
  			$theImgCodeCP="";
  			while($rowCP = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCP))		{
	  			$imageNumCP = 0;
    			$imgsCP = explode(",",$rowCP["image"]);
    			$valCP = $imgsCP[0];
    			while(list($cCP,$valCP)=each($imgsCP))	{
    				//if ($c==$imageNum)	break;
	    			if ($valCP)	{
  	  				$this->conf["listImage."]["file"] = "uploads/tx_extendedshop/".$valCP;
    				} else {
    					$this->conf["listImage."]["file"] = $this->conf["noImageAvailable"];
    				}
    				$this->conf["listImage."]["altText"] = $rowCP['title'];
    				$link = $this->getLinkUrl("","","","",$rowCP['uid']);
    				$theImgCodeCP.='<a href="'.$link.'">'.$this->cObj->IMAGE($this->conf["listImage."]).'</a> - ###LABEL_ACCESSORIES_PRICE###: '.$this->priceFormat($rowCP["price"]).'<br />';
    			}
    		}
    		$markerArray["###PRODUCT_CORRELATEDACCESSORIES###"] = $theImgCodeCP;
    		$markerArray["###LABEL_CORRELATEDACCESSORIES###"] = htmlspecialchars($this->pi_getLL("###LABEL_CORRELATEDACCESSORIES###"));
    		
    		$subTemplate2 = trim($this->cObj->getSubpart($template,$this->spMarker("###CORRACC###")));  
				$subContent2 = $this->cObj->substituteMarkerArray($subTemplate2, $markerArray);
				$template = $this->cObj->substituteSubpart($template,"###CORRACC###",$subContent2,$recursive=0,$keepMarker=0);
    	}	else	{
    		$markerArray["###PRODUCT_CORRELATEDACCESSORIES###"] = "";
    		$markerArray["###LABEL_CORRELATEDACCESSORIES###"] = "";
    		$template = $this->cObj->substituteSubpart($template,"###CORRACC###","",$recursive=0,$keepMarker=0);
    	}
    	
    	if ($this->basket["products"][$row['uid']]["num"]>0)	{
    		if ($markerArray["###PRODUCT_COMBINATIONS###"]<$this->basket["products"][$row['uid']]["num"])
    			$markerArray["###PRODUCT_NUMINBASKET###"] = $this->basket["products"][$row['uid']]["num"];
    		else
    			$markerArray["###PRODUCT_NUMINBASKET###"] = $markerArray["###PRODUCT_COMBINATIONS###"];
    	}	else	{
    		if ($markerArray["###PRODUCT_COMBINATIONS###"]>1)
    			$markerArray["###PRODUCT_NUMINBASKET###"] = $markerArray["###PRODUCT_COMBINATIONS###"];
    		else
    			$markerArray["###PRODUCT_NUMINBASKET###"] = 0;
    	}
    	$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] = "<select name='product[".$row['uid']."][num]'>";
    	/*if ($this->basket["products"][$row['uid']]["num"]<1)	{
    		$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] .= "<option value='0' selected>".$this->pi_getLL('###LABEL_QUANTITY###')."</option>";
    	}	else	{
    		$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] .= "<option value='0'>0"</option>;
    	}*/
    	$iCombinations = $markerArray["###PRODUCT_COMBINATIONS###"];
    	$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] .= "<option value='0'>0</option>";
    	if ($iCombinations==0)
    		$iCombinations = 1;
    	for ($i=$iCombinations; $i<11*$iCombinations; $i=$i+$iCombinations)	{
    		if ($i==$this->basket["products"][$row['uid']]["num"] || ($i==$iCombinations && $this->basket["products"][$row['uid']]["num"]<=0))
    			$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] .= "<option value='".$i."' selected>".$i."</option>";
    		else
    			$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] .= "<option value='".$i."'>".$i."</option>";
    	}
    	$markerArray["###PRODUCT_QUANTITY_SELECTOR###"] .= "</select>";
  		
  		//return $markerArray;
  		return $this->cObj->substituteMarkerArray($template, $markerArray);
	}
	
	/**
	 * This function inserts the order, the rows and some pages to organize the BE work
	 */
	function insertOrder($idCustomer, $idDelivery,$content,$trackingcode=false,$markerArray)	{
		
		if ($trackingcode==false)
			$trackingcode = time();
		$pid_orders = $this->config["pid_orders"];
		
		$year = date("Y");
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid="'.$pid_orders.'" AND title="'.$year.'" AND deleted <>1');
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)<1)	{			
			$insertFields["pid"] = $pid_orders;
			$insertFields["title"] = $year;
			$time = time();
			$insertFields["tstamp"] = $time;
			$insertFields["crdate"] = $time;
			$insertFields["doktype"] = 254;
			$resP = $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $insertFields);
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid="'.$pid_orders.'" AND title="'.$year.'" AND deleted <>1');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$id_year = $row["uid"];
		$month = date("m");
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid="'.$id_year.'" AND title="'.$month.'" AND deleted <>1');
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)<1)	{  		
			$insertFields["pid"] = $id_year;
			$insertFields["title"] = $month;
			$time = time();
			$insertFields["tstamp"] = $time;
			$insertFields["crdate"] = $time;
			$insertFields["doktype"] = 254;
			$resP = $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $insertFields);
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid="'.$id_year.'" AND title="'.$month.'" AND deleted <>1');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$id_month = $row["uid"];
		
		// Insert Order
		$orderCode = "order_";
		$orderFields = array (
			"code" => $orderCode,
			"customer" => $idCustomer,
			"shippingcustomer" => $idDelivery,
			"date" => time(),
			"shipping" => $this->basket["shipping"]["title"]." - ".$this->basket["shipping"]["priceTax"],
			"payment" => $this->basket["payment"]["title"]." - ".$this->basket["payment"]["priceTax"],
			"total" => $this->basket["totaleProdotti"] + $this->basket["shipping"]["priceTax"] + $this->basket["payment"]["priceTax"],
			"pid" => $id_order,
			"ip" => t3lib_div::getIndpEnv("REMOTE_ADDR"),
			"note" => $this->basket["personinfo"]["NOTE"]
		);
		$resO = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_extendedshop_orders', $orderFields);
		
		$newId = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		$orderCode = "order_".$newId;
		$markerArray["###ORDERID###"] = $orderCode;
		
		$insertFields["pid"] = $id_month;
		$insertFields["title"] = $orderCode;
		$time = time();
		$insertFields["tstamp"] = $time;
		$insertFields["crdate"] = $time;
		$insertFields["doktype"] = 1;
		$resP = $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $insertFields);
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid="'.$id_month.'" AND title="'.$orderCode.'"');		
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		$id_order = $row["uid"];
		
		$insertContent["pid"] = $id_order;
		$insertContent["tstamp"] = time();
		$insertContent["header"] = $orderCode;
		$insertContent["bodytext"] = $this->manageLabels($this->cObj->substituteMarkerArray($content, $markerArray));
		$insertContent["CType"] = "html";
		
		$resC = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_content', $insertContent);
		
		$updateFields = array (
			"code" => $orderCode,
			"pid" => $id_order,
			"trackingcode" => $orderCode."_".$trackingcode,
		);
		$resO = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_extendedshop_orders', 'uid='.$newId, $updateFields);
		
		// Insert Rows
		$res = "";
		$row = "";
		$list = array_keys($this->basket["products"]);
		$where = "uid IN (";
		foreach($list as $id)
		{
			if ($this->basket["products"][$id]["num"]>0)	{
				$where.=$id.",";
			}
		}
		$where = substr($where,0,-1).")";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', $where,'','','');
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
				$insertRow["pid"] = $id_order;
				$insertRow["tstamp"] = time();
				$insertRow["crdate"] = time();
				$insertRow["ordercode"] = $newId;
				$insertRow["productcode"] = $row["uid"];
				$insertRow["itemcode"] = $row["code"];
				$insertRow["quantity"] = $this->basket["products"][$row['uid']]["num"];
				$insertRow["price"] = $this->basket["products"][$row['uid']]["price"];
				$insertRow["weight"] = $row["weight"];
				$insertRow["volume"] = $row["volume"];
				//$insertRow["accessoriescodes"] = $this->basket["products"][$row['uid']]["accessoriescodes"];
				$insertRow["options"] = "";
				
				for ($i=1; $i<=$this->basket["products"][$row["uid"]]["combinations"]; $i++)	{
					$insertRow["options"] .= '('.$this->basket["products"][$row["uid"]]["sizes"][$i].'-'.$this->basket["products"][$row["uid"]]["colors"][$i].')';
				}
				
				$resR = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_extendedshop_rows', $insertRow);
		}
		return $orderCode;
		
	}
	
	
	/**
	 * This function search for the customer and delivery users. If they aren't present, the function creates two new users
	 */
	function manageUsers()	{
		// Test if customer is a new user
		$email = $this->basket["personinfo"]["EMAIL"];
		if ($GLOBALS["TSFE"]->loginUser!="")	{
			// Logged user
			$user = $GLOBALS["TSFE"]->fe_user->user;
			$idUser = $user["uid"];
			$insertFields["pid"] = $this->conf["pid_users"];
			$insertFields["name"] = $this->basket["personinfo"]["NAME"];
			$insertFields["email"] = $email;
			$insertFields["telephone"] = $this->basket["personinfo"]["PHONE"];
			$insertFields["address"] = $this->basket["personinfo"]["ADDRESS"];
			$insertFields["company"] = $this->basket["personinfo"]["COMPANY"];
			$insertFields["fax"] = $this->basket["personinfo"]["FAX"];
			$insertFields["city"] = $this->basket["personinfo"]["CITY"];
			$insertFields["country"] = $this->basket["personinfo"]["COUNTRY"];
			$insertFields["tx_extendedshop_state"] = $this->basket["personinfo"]["STATE"];
			$insertFields["zip"] = $this->basket["personinfo"]["ZIP"];
			$insertFields["tx_extendedshop_mobile"] = $this->basket["personinfo"]["MOBILE"];
			$insertFields["www"] = $this->basket["personinfo"]["WWW"];
			$insertFields["tx_extendedshop_vatcode"] = $this->basket["personinfo"]["VATCODE"];
			$insertFields["tx_extendedshop_private"] = $this->basket["personinfo"]["PRIVATE"];
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid='.$idUser, $insertFields);
		}	else	{
			// User not logged
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'username="'.$email.'" and deleted<>1');
			$newUserTemplate = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###NEWUSER_TEMPLATE###")));
			$pass = $this->newPassword();
			$markerArray["###NEWUSER_USER###"] = $email;
			$markerArray["###NEWUSER_PASS###"] = $pass;
			$newUserTemplate = $this->cObj->substituteMarkerArray($newUserTemplate, $markerArray);
			$this->send_email($this->manageLabels($newUserTemplate),"###EMAIL_NEWUSER_SUBJECT###");
			$insertFields["pid"] = $this->conf["pid_users"];
			$insertFields["usergroup"] = $this->conf["group_customer"];
			$insertFields["username"] = $email;
			$insertFields["password"] = $pass;
			$insertFields["name"] = $this->basket["personinfo"]["NAME"];
			$insertFields["email"] = $email;
			$insertFields["telephone"] = $this->basket["personinfo"]["PHONE"];
			$insertFields["address"] = $this->basket["personinfo"]["ADDRESS"];
			$insertFields["company"] = $this->basket["personinfo"]["COMPANY"];
			$insertFields["fax"] = $this->basket["personinfo"]["FAX"];
			$insertFields["city"] = $this->basket["personinfo"]["CITY"];
			$insertFields["country"] = $this->basket["personinfo"]["COUNTRY"];
			$insertFields["tx_extendedshop_state"] = $this->basket["personinfo"]["STATE"];
			$insertFields["zip"] = $this->basket["personinfo"]["ZIP"];
			$insertFields["tx_extendedshop_mobile"] = $this->basket["personinfo"]["MOBILE"];
			$insertFields["www"] = $this->basket["personinfo"]["WWW"];
			$insertFields["tx_extendedshop_vatcode"] = $this->basket["personinfo"]["VATCODE"];
			$insertFields["tx_extendedshop_private"] = $this->basket["personinfo"]["PRIVATE"];
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)!=1)	{
				// New user
				$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $insertFields);
			}	else	{
				// Existing user
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid='.$row["uid"], $insertFields);
			}
		}
		$insertFields="";
		// Test if delivery person is a new user
		$email = $this->basket["delivery"]["EMAIL"];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', 'email="'.$email.'" and deleted<>1');
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)==0 && $email!="")	{
			$newUserTemplate2 = trim($this->cObj->getSubpart($this->config["templateCode"],$this->spMarker("###NEWUSER_TEMPLATE###")));
/*			$pass = $this->newPassword();
			$markerArray["###NEWUSER_USER###"] = $email;
			$markerArray["###NEWUSER_PASS###"] = $pass;
			$newUserTemplate2 = $this->cObj->substituteMarkerArray($newUserTemplate2, $markerArray);
			//$this->send_email($this->manageLabels($newUserTemplate2),"###EMAIL_NEWUSER_SUBJECT###"); */
			$insertFields["pid"] = $this->conf["pid_delivery"];
			$insertFields["name"] = $this->basket["delivery"]["NAME"];
			$insertFields["email"] = $email;
			$insertFields["phone"] = $this->basket["delivery"]["PHONE"];
			$insertFields["mobile"] = $this->basket["delivery"]["MOBILE"];
			$insertFields["address"] = $this->basket["delivery"]["ADDRESS"];
			$insertFields["company"] = $this->basket["delivery"]["COMPANY"];
			$insertFields["city"] = $this->basket["delivery"]["CITY"];
			$insertFields["country"] = $this->basket["delivery"]["COUNTRY"];
			$insertFields["tx_extendedshop_state"] = $this->basket["delivery"]["STATE"];
			$insertFields["zip"] = $this->basket["delivery"]["ZIP"];
			$insertFields["hidden"] = 1;
			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_address', $insertFields);
		}
		
		if ($GLOBALS["TSFE"]->loginUser!="")	{
			$user["idCustomer"] = $user["uid"];
		}	else	{
			$email = $this->basket["personinfo"]["EMAIL"];
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'username="'.$email.'" and deleted<>1');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$user["idCustomer"] = $row["uid"];
		}
		$email = $this->basket["delivery"]["EMAIL"];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', 'email="'.$email.'" and pid="'.$this->conf["pid_delivery"].'" and deleted<>1');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$user["idDelivery"] = $row["uid"];
		return $user;
	}
	
	
	/**
	 * This function displays the list of orders for the logged user
	 */
	function ordersInfo($template)	{
		$content = "";
		if ($GLOBALS["TSFE"]->loginUser!="")	{
			$user = $GLOBALS["TSFE"]->fe_user->user;
			$rowTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###ORDER_ROW###")));
			$contentRow = "";
			
			$pageNumber = t3lib_div::_GP("productPage");
			if ($pageNumber=="")
				$pageNumber = 1;
			
			if (t3lib_div::_GP("productID")=="")	{
				$template = $this->cObj->substituteSubpart($template,"###ROW_TEMPLATE###","",$recursive=0,$keepMarker=0);
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_orders', 'customer='.$user["uid"].' AND deleted<>1','date DESC','','');
			}	else	{
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_orders', 'uid='.t3lib_div::_GP("productID"),'','','');
				$order_details = true;
			}
			
			$num_orders = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			// NUMERO DI ORDINI PER PAGINA
			$num_orders_for_page = $this->conf["ordersInfo."]["ordersForPage"];
			
			// Numero di pagine
			$num_pages = ceil($num_orders/$num_orders_for_page);
			
			for ($i=1; $i<=$num_pages; $i++)	{
				if ($i==$pageNumber)	{
					$pageLink .= " <b>".$i."</b>";
				}	else	{
					$pageLink .= " <a href='".$this->getLinkUrl("","","","","","","",$i)."'>".$i."</a>";
				}
			}
			$mA["###ORDER_VIEWALL###"] = $this->getLinkUrl("","","","","","","","all");
			$mA["###ORDER_PAGES###"] = $pageLink;
			
			// Scarta i primi ordini se sono in pagine successive
	  		if ($pageNumber!="all" && $pageNumber>1)	{
				for ($i=0; $i<($pageNumber-1)*$num_orders_for_page; $i++)	{
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				}
			}	else if ($pageNumber=="all")	{
				$num_orders_for_page = $num_orders;
			}
			
			for ($i=0; $i<$num_orders_for_page && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); $i++)	{
				$resUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid='.$row['customer'],'','','');
				$rowUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resUser);
				$resShippingCustomer = $GLOBALS['TYPO3_DB']->exec_SELECTquery('name', 'tt_address', 'uid='.$row['shippingcustomer'],'','','');
				$rowShippingCustomer = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resShippingCustomer);
				
				$totalProducts = 0;
				$resProd =$GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_rows', 'ordercode='.$row["uid"],'','','');
				while($rowProd = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resProd))	{
					$totalProducts += $rowProd["price"] * $rowProd["quantity"];
				}
				$priceShipping = trim(substr($row["shipping"],strrpos($row["shipping"],"-")+1));
				if ($priceShipping == "")
					$priceShipping = "0,00";
				$pricePayment = trim(substr($row["payment"],strrpos($row["payment"],"-")+1));
				if ($pricePayment == "")
					$pricePayment = "0,00";
				$totale = $this->priceFormat($totalProducts+$pricePayment+$priceShipping);
				$tot = $totalProducts+$pricePayment+$priceShipping;
				// Aggiorna il totale...
				if ($tot!=$row['total'])	{
					$update["total"] = $tot;
					$resUpdate = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_extendedshop_orders','uid='.$row["uid"],$update);
				}
				
				$rA["###ORDER_NUMBER###"] = $row["code"];
				$rA["###ORDER_DATE###"] = date("d M Y - H:i:s",$row['date']);
				$rA["###ORDER_DELIVERYTO###"] = $rowShippingCustomer["name"];
				$rA["###ORDER_SHIPPING###"] = $row["shipping"];
				$rA["###ORDER_PAYMENT###"] = $row["payment"];
				$rA["###ORDER_TOTAL###"] = $totale;
				$rA["###ORDER_SELLERNOTE###"] = $row["ordernote"];
				if ($row['deliverydate']=="0" || $row['deliverydate']=="")
					$rA["###ORDER_DELIVERYDATE###"] = "";
				else
					$rA["###ORDER_DELIVERYDATE###"] = date("d M Y",$row['deliverydate']);
				$rA["###ORDER_DETAILS###"] = $this->getLinkUrl("","","",$row["uid"]);
				
				if ($row['complete']!=1)
					$workingTemplate = $this->cObj->substituteSubpart($rowTemplate,"###ORDER_COMPLETED###","",$recursive=0,$keepMarker=0);
				else
					$workingTemplate = $rowTemplate;
				
				// Status management
				$lingua = t3lib_div::_GP("L");
				if ($lingua>0)	{
					$resStatus = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_status', 'l18n_parent='.$row["status"].' AND sys_language_uid='.t3lib_div::_GP("L").' AND deleted<>1 AND hidden<>1','','','');
				}
				if ($resStatus=="" || $GLOBALS['TYPO3_DB']->sql_num_rows($resStatus)==0 || $lingua<=0)	{
					$resStatus = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_status', 'uid='.$row["status"].' AND deleted<>1 AND hidden<>1','','','');
				}
				$rowStatus = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resStatus);
				$rA["###ORDER_STATUS###"] = $rowStatus["status"];
				
				// Background-color management
				if ($i%2==0)
					$rA["###BACKGROUND###"] = "even";
				else
					$rA["###BACKGROUND###"] = "odd";
				
				$contentRow .= $this->cObj->substituteMarkerArray($workingTemplate, $rA);
				unset($rA);
			}
			$template = $this->cObj->substituteSubpart($template,"###ORDER_ROW###",$contentRow,$recursive=0,$keepMarker=0);
			
			// Order details
			if ($order_details)	{
				$singleRowTemplate = trim($this->cObj->getSubpart($template,$this->spMarker("###SINGLE_ROW###")));
				$contentProducts = "";
				
				$resRows = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_rows', 'ordercode='.t3lib_div::_GP("productID").' AND deleted<>1 AND hidden<>1','','','');
				$i=0;
				while($rowRows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resRows))	{
					$rA["###PRODUCT_PRICE###"] = $this->priceFormat($rowRows["price"]);
					$rA["###PRODUCT_QUANTITY###"] = $rowRows["quantity"];
					$rA["###PRODUCT_COMBINATIONS###"] = $rowRows["options"];
					$rA["###PRODUCT_CODE###"] = $rowRows["itemcode"];
					$rA["###PRODUCT_TOTAL_PRICE###"] = $this->priceFormat($rowRows["price"]*$rowRows["quantity"]);
					$resProd = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'uid='.$rowRows["productcode"].' AND deleted<>1 AND hidden<>1','','','');
					if ($GLOBALS['TYPO3_DB']->sql_num_rows($resProd)==1)	{
						$rowProd = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resProd);
						$rA["###PRODUCT_TITLE###"] = $rowProd["title"];
						// Get image
						$imageNum = 0;
						$theImgCode="";
						$imgs = explode(",",$rowProd["image"]);
						$val = $imgs[0];
						while(list($c,$val)=each($imgs))	{
							if ($val)	{
								$this->conf["ordersImage."]["file"] = "uploads/tx_extendedshop/".$val;
							} else {
								$this->conf["ordersImage."]["file"] = $this->conf["noImageAvailable"];
							}
							$this->conf["ordersImage."]["altText"] = '"'.$rowProd["title"].'"';
							$theImgCode.=$this->cObj->IMAGE($this->conf["ordersImage."]);
						}
						$rA["###PRODUCT_IMAGE###"] = $theImgCode;
					}	else	{
						$rA["###PRODUCT_TITLE###"] = "";
						$rA["###PRODUCT_IMAGE###"] = "";
					}
					// Background-color management
					if ($i%2==0)
						$rA["###BACKGROUND###"] = "even";
					else
						$rA["###BACKGROUND###"] = "odd";
					$i++;
					$contentProducts .= $this->cObj->substituteMarkerArray($singleRowTemplate, $rA);
					unset($rA);
				}
				
				$template = $this->cObj->substituteSubpart($template,"###SINGLE_ROW###",$contentProducts,$recursive=0,$keepMarker=0);
			}
			
			$content = $this->cObj->substituteMarkerArray($template, $mA);
		}	else	{
			$content = $this->pi_getLL('###LABEL_NO_USER_LOGGED_IN###');
		}
		return $content;
	}
	
	
	
	/**
	 * Calculates the discount starting from original price and offert price
	 */
	function calculateDiscount($row)	{
		if ($row["discount"]!=0 && $row["offertprice"]==0)
			return $row["discount"];
		return 100-round(($row["offertprice"]*100)/$row["price"],0);
	}
	
	/**
	 * Calculates the offert price starting from original price and the discount
	 */
	function calculateOffertPrice($row,$type="",$format=true)	{
		switch ($type)	{
  		case "notax" :
				if ($row["offertpricenotax"]!=0)	{
					if ($format)
						return $this->priceFormat($row["offertpricenotax"]);
					else
						return $row["offertpricenotax"];
				}	else	{
					if ($format)
						return $this->priceFormat(round((1-$this->calculateDiscount($row)/100)*$row["pricenotax"],1));
					else
						return round((1-$this->calculateDiscount($row)/100)*$row["pricenotax"],1);
				}
				break;
			default:
				if ($row["offertprice"]!=0)	{
					if ($format)
						return $this->priceFormat($row["offertprice"]);
					else
						return $row["offertprice"];
				}	else	{
					if ($format)
						return $this->priceFormat(round((1-$this->calculateDiscount($row)/100)*$row["price"],1));
					else
						return round((1-$this->calculateDiscount($row)/100)*$row["price"],1);
				}
				break;
		}
		return;
	}
	
	/**
	 * Sets the pid_list internal var
	 */
	function setPidlist($pid_list)	{
		$this->pid_list = $pid_list;
	}

	/**
	 * Extends the internal pid_list by the levels given by $recursive
	 */
	function initRecursive($recursive)	{
		if ($recursive)	{		// get pid-list if recursivity is enabled
			$pid_list_arr = explode(",",$this->pid_list);
			$this->pid_list="";
			while(list(,$val)=each($pid_list_arr))	{
				$this->pid_list.=$val.",".$this->cObj->getTreeList($val,intval($recursive));
			}
			$this->pid_list = ereg_replace(",$","",$this->pid_list);
		}
	}

	/**
	 * Getting all categories into internal array
	 */
	function initCategories()	{
			// Fetching catagories:
	 	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_category', '1=1'.$this->cObj->enableFields('tx_extendedshop_category'));
		$this->categories = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$this->categories[$row["code"]] = $row["title"];
		}	
	}

	/**
	 * Generates an array, ->pageArray of the pagerecords from ->pid_list
	 */
	function generatePageArray()	{
			// Get pages (for category titles)		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title,uid', 'pages', 'uid IN ('.$this->pid_list.')');
		$this->pageArray = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
			$this->pageArray[$row["uid"]] = $row;
		}
	}
	
	/**
	 * Returning template subpart marker
	 */
	function spMarker($subpartMarker)	{
		$sPBody = substr($subpartMarker,3,-3);
		$altSPM = "";
		if (isset($this->conf["altMainMarkers."]))	{
			$altSPM = trim($this->cObj->stdWrap($this->conf["altMainMarkers."][$sPBody],$this->conf["altMainMarkers."][$sPBody."."]));
			$GLOBALS["TT"]->setTSlogMessage("Using alternative subpart marker for '".$subpartMarker."': ".$altSPM,1);
		}
		return $altSPM ? $altSPM : $subpartMarker;
	}
	
	
	/**
	 * Formatting a price
	 */
	function priceFormat($double,$priceDecPoint="",$priceThousandPoint="")	{
		if ($priceDecPoint=="")
			$priceDecPoint=$this->conf["priceDecPoint"];
		if ($priceThousandPoint=="")
			$priceThousandPoint=$this->conf["priceThousandPoint"];
//echo($double."-".intval($this->conf["priceDec"])."-".$priceDecPoint."-".$this->conf["priceThousandPoint"]);
		return number_format($double,intval($this->conf["priceDec"]),$priceDecPoint,$this->conf["priceThousandPoint"]);
	}
	
	/**
	 * Generating a new random password
	 */
	function newPassword()	{
		$length = rand(5,8);
		$pass = "";
		for ($i=0; $i<$length; $i++)	{
			$a["1"] = rand(48,57);
			$a["2"] = rand(65,90);
			$a["3"] = rand(97,122);
			$j = rand(1,3);
			$pass .= chr($a[$j]);
		}
		return $pass;
	}
	
	
	/**
	 * Substitute all labes with the correct language label
	 */
	function manageLabels($content)	{
		$lang = $this->pi_getLL("LABELS");
		$markers = explode(",",$lang);
		foreach ($markers as $marker)	{
			$markerArray["###LABEL_".$marker."###"] = $this->pi_getLL("###LABEL_".$marker."###");
		}

		$markerArray["###SWORDS###"] = t3lib_div::_GP("swords") ? t3lib_div::_GP("swords") : "";
		$content = $this->cObj->substituteMarkerArray($content, $markerArray);
		
		$content = $this->manageFormLinks($content);
		return $content;
	}
	
	
	
	/**
	 * Substitute all labes with the correct language label
	 */
	function manageFormLinks($content)	{
		$markerArray["###FORM_SEARCH###"] = $this->getLinkUrl($GLOBALS["TSFE"]->id);
		$markerArray["###FORM_ADD###"] = $this->getLinkUrl($this->config["pid_basket"]);
		$markerArray["###FORM_BASKET###"] = $this->getLinkUrl($this->config["pid_basket"]);
		
		$content = $this->cObj->substituteMarkerArray($content, $markerArray);
		return $content;
	}
	
	/**
	 * Returns a url for use in forms and links
	 */
	function getLinkUrl($id="",$excludeList="",$orderBy="",$productID="",$accessorieID="",$numCombination="",$detail=false,$productPage="",$pid_product="")	{
		$queryString=array();
		$queryString["id"] = ($id ? $id : $GLOBALS["TSFE"]->id);
		$queryString["type"]= $GLOBALS["TSFE"]->type ? 'type='.$GLOBALS["TSFE"]->type : "";
		$queryString["backPID"]= 'backPID='.$GLOBALS["TSFE"]->id;
		$queryString["swords"]= t3lib_div::_GP("swords") ? "swords=".rawurlencode(t3lib_div::_GP("swords")) : "";
		$queryString["L"]= t3lib_div::_GP("L") ? "L=".t3lib_div::_GP("L") : "";
		if ($orderBy!="")
			$queryString["orderBy"]='orderBy='.$orderBy;
		if ($productID!="")
			$queryString["productID"]='productID='.$productID;
		if ($accessorieID!="")
			$queryString["accessorieID"]='accessorieID='.$accessorieID;
		if ($numCombination!="")
			$queryString["numCombination"]='numCombination='.$numCombination;
		if ($productPage!="")
			$queryString["productPage"]='productPage='.$productPage;
		if ($pid_product!="")
			$queryString["pid_product"]='pid_product='.$pid_product;
		$queryString["detail"]='detail='.$detail;
		
		reset($queryString);
		while(list($key,$val)=each($queryString))	{
			if (!$val || ($excludeList && t3lib_div::inList($excludeList,$key)))	{
				unset($queryString[$key]);
			} 
		}
		if ($GLOBALS['TSFE']->config['config']['simulateStaticDocuments'])   {
			$pageId = $id ? $id : $GLOBALS["TSFE"]->id ;
			$pageType = $GLOBALS["TSFE"]->type ;
			unset($queryString['id']);
			unset($queryString['type']);

			$allQueryString = implode($queryString,"&");
			if( $addQueryString )	{
				$allQueryString .= "&".implode($addQueryString,"&");
			}	
			$URLtitle = "";
			if ($productID!="")	{
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_products', 'deleted<>1 AND uid='.$productID,'','','');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$URLtitle = $row["title"];
			}
//			debug($allQueryString);
      return $GLOBALS["TSFE"]->makeSimulFileName($URLtitle, $pageId, $pageType, $allQueryString ).".html";								
		
		}	else	{
			return $GLOBALS["TSFE"]->absRefPrefix.'index.php?'.implode($queryString,"&");
		}
	}
	
	
	/**
	*	Checks if all required personal info fileds are filled
	*
	*/
	function checkRequired()	{
		$listRequired = explode(",",trim($this->conf["requiredFields"]));
		$requiredOK = true;
		while (list($key, $field) = each ($listRequired))	{
			$field = strtoupper($field);
			if ($this->basket["personinfo"][$field]=="")	{
				$requiredOK = false;
				break;
			}
		}
		
		return $requiredOK;
	}
	
	
	
	
	
	
	/**
	 * Initialized the basket
	 *
	 */
	function initBasket($basket)	{
	
		//print_r(t3lib_div::_GP('product'));
		
//print_r($basket);
		
		$products = t3lib_div::_GP('product');
		$numCombination = t3lib_div::_GP('numCombination');
		
//print_r($products);	
	
		$this->recs = $basket;	// Sets it internally
		$this->basket=array();
		$this->resetPersonalInfo();
		$uidArr=array();
		
		if ($basket["totaleProdotti"]!="")
			$this->basket["totaleProdotti"] = $basket["totaleProdotti"];
		if ($basket["noshippingcost"]!="")
			$this->basket["noshippingcost"] = $basket["noshippingcost"];
		
		if (is_array($basket["products"]))	{
			reset($basket["products"]);
			foreach($basket["products"] as $prod)	{
				if (t3lib_div::testInt($prod["uid"]))	{
					$count=t3lib_div::intInRange($prod["num"],0,100000);
					if ($count)	{
						$uid = $prod["uid"];
						$this->basket["products"][$uid]["num"]=$count;
						$this->basket["products"][$uid]["uid"]=$uid;
						$this->basket["products"][$uid]["price"]=$prod["price"];
						$this->basket["products"][$uid]["page"]=$prod["page"];
						$this->basket["products"][$uid]["category"]=$prod["category"];
						$this->basket["products"][$uid]["combinations"]=$prod["combinations"];
						for ($i=1; $i<=$prod["combinations"]; $i++)	{
							$this->basket["products"][$uid]["sizes"][$i]=$prod["sizes"][$i];
							$this->basket["products"][$uid]["colors"][$i]=$prod["colors"][$i];
						}
						$uidArr[]=$uid;
					}
				}
			}
		}
		
		if (is_array($products))	{
			foreach($products as $prod)	{
				if (t3lib_div::testInt($prod["uid"]))	{
					$count=t3lib_div::intInRange($prod["num"],0,100000);
					$uid = $prod["uid"];
					if ($count)	{
						$this->basket["products"][$uid]["num"]=$count;
						$this->basket["products"][$uid]["uid"]=$uid;
						$this->basket["products"][$uid]["price"]=$prod["price"];
						$this->basket["products"][$uid]["page"]=$prod["page"];
						$this->basket["products"][$uid]["category"]=$prod["category"];
						$this->basket["products"][$uid]["combinations"]=$prod["combinations"];
						for ($i=1; $i<=$prod["combinations"]; $i++)	{
							$this->basket["products"][$uid]["sizes"][$i]=$prod["sizes"][$i];
							$this->basket["products"][$uid]["colors"][$i]=$prod["colors"][$i];
						}
						$uidArr[]=$uid;
					}	else	{
						$this->basket["products"][$uid]["num"]=0;
						$this->basket["products"][$uid]["uid"]=$uid;
						$this->basket["products"][$uid]["price"]=0;
					}
				}
			}
		}
		
		if (isset($numCombination)){
			$productID = t3lib_div::_GP('productID');
			$this->basket["products"][$productID]["combinations"]=$numCombination;
		}

		$this->uid_list=implode($uidArr,",");
		
		// Saving shipping and payment settings
		$recsForm = t3lib_div::_GP('recs');
		if (isset($recsForm))	{
			$kShipping = $recsForm["final"]["shipping"];
			$kPayment = $recsForm["final"]["payment"];
			if ($kShipping != "")	{
				$this->basket["shipping"] = $this->conf["shipping."][$kShipping."."];
				$this->basket["shipping"]["key"] = $kShipping;
			}	else	{
				$this->basket["shipping"] = $basket["shipping"];
			}
			if ($kPayment != "")	{
				$this->basket["payment"] = $this->conf["payment."][$kPayment."."];
				$this->basket["payment"]["key"] = $kPayment;
			}	else	{
				$this->basket["payment"] = $basket["payment"];
			}
		}	else	{
			$this->basket["payment"] = $basket["payment"];
			$this->basket["shipping"] = $basket["shipping"];
		}

		//$this->setBasketExtras($basket);
		
		
		// Management of personal data
		$this->personInfo = $basket["personinfo"];
		$this->deliveryInfo = $basket["delivery"];
		
		
		$login = t3lib_div::_GP('login');
		$new = t3lib_div::_GP('new');
		$datiPersonali = t3lib_div::_GP('datiPersonali');
		
		$personal = t3lib_div::_GP('personal');
		$delivery = t3lib_div::_GP('delivery');
		
		if ($personal["NOTE"]!="")
			$this->basket["personinfo"]["NOTE"] = $personal["NOTE"];
		else
			$this->basket["personinfo"]["NOTE"] = $basket["personinfo"]["NOTE"];
		
		if ($GLOBALS["TSFE"]->loginUser!="" && !isset($new) && $datiPersonali!=1 && $basket["personinfo"]["NEW"]!=1)	{
			$user = $GLOBALS["TSFE"]->fe_user->user;
			$this->basket["personinfo"]["USER"] = $user["username"];
			$this->basket["personinfo"]["PASSWORD"] = $user["password"];
			$this->basket["personinfo"]["NAME"] = $user["name"];
			$this->basket["personinfo"]["ADDRESS"] = $user["address"];
			$this->basket["personinfo"]["CITY"] = $user["city"];
			$this->basket["personinfo"]["ZIP"] = $user["zip"];
			$this->basket["personinfo"]["STATE"] = $user["tx_extendedshop_state"];
			$this->basket["personinfo"]["COUNTRY"] = $user["country"];
			$this->basket["personinfo"]["COMPANY"] = $user["company"];
			$this->basket["personinfo"]["VATCODE"] = $user["tx_extendedshop_vatcode"];
			$this->basket["personinfo"]["PRIVATE"] = $user["tx_extendedshop_private"];
			$this->basket["personinfo"]["WWW"] = $user["www"];
			$this->basket["personinfo"]["PHONE"] = $user["telephone"];
			$this->basket["personinfo"]["MOBILE"] = $user["tx_extendedshop_mobile"];
			$this->basket["personinfo"]["FAX"] = $user["fax"];
			$this->basket["personinfo"]["EMAIL"] = $user["email"];
		//	t3lib_div::debug($GLOBALS["TSFE"]->fe_user->user);
		} elseif (isset($new)  || $datiPersonali==1)	{
			$this->basket["personinfo"] = $personal;
			//$this->basket["personinfo"]["COUNTRY"] = $this->basket["shipping"]["title"];
			$this->basket["personinfo"]["USER"] = "";
   			$this->basket["personinfo"]["PASSWORD"] = "";
			$this->basket["personinfo"]["NEW"] = 1;
		}	elseif (is_array($basket["personinfo"]))	{
			$this->basket["personinfo"]["USER"] = $basket["personinfo"]["USER"];
			$this->basket["personinfo"]["PASSWORD"] = $basket["personinfo"]["PASSWORD"];
			$this->basket["personinfo"]["NAME"] = $basket["personinfo"]["NAME"];
			$this->basket["personinfo"]["ADDRESS"] = $basket["personinfo"]["ADDRESS"];
			$this->basket["personinfo"]["CITY"] = $basket["personinfo"]["CITY"];
			$this->basket["personinfo"]["ZIP"] = $basket["personinfo"]["ZIP"];
			$this->basket["personinfo"]["STATE"] = $basket["personinfo"]["STATE"];
			$this->basket["personinfo"]["COUNTRY"] = $basket["personinfo"]["COUNTRY"];
			$this->basket["personinfo"]["COMPANY"] = $basket["personinfo"]["COMPANY"];
			$this->basket["personinfo"]["VATCODE"] = $basket["personinfo"]["VATCODE"];
			$this->basket["personinfo"]["PRIVATE"] = $basket["personinfo"]["PRIVATE"];
			$this->basket["personinfo"]["WWW"] = $basket["personinfo"]["WWW"];
			$this->basket["personinfo"]["PHONE"] = $basket["personinfo"]["PHONE"];
			$this->basket["personinfo"]["MOBILE"] = $basket["personinfo"]["MOBILE"];
			$this->basket["personinfo"]["FAX"] = $basket["personinfo"]["FAX"];
			$this->basket["personinfo"]["EMAIL"] = $basket["personinfo"]["EMAIL"];
			$this->basket["personinfo"]["AUTHORIZATION"] = $basket["personinfo"]["AUTHORIZATION"];
			$this->basket["personinfo"]["CONDITIONS"] = $basket["personinfo"]["CONDITIONS"];
			$this->basket["personinfo"]["NEW"] = $basket["personinfo"]["NEW"];
		}	else	{
			$this->resetPersonalInfo();
		}
		
		if (isset($new))	{
  		if (is_array($delivery) && $delivery["NAME"]!="")	{
    		$this->basket["delivery"] = $delivery;
  		}	elseif (is_array($basket["delivery"]) && $basket["delivery"]["NAME"]!="")	{
  			/*$this->basket["delivery"]["NAME"] = $basket["delivery"]["NAME"];
  			$this->basket["delivery"]["ADDRESS"] = $basket["delivery"]["ADDRESS"];
  			$this->basket["delivery"]["COMPANY"] = $basket["delivery"]["COMPANY"];
  			$this->basket["delivery"]["PHONE"] = $basket["delivery"]["PHONE"];
  			$this->basket["delivery"]["FAX"] = $basket["delivery"]["FAX"];
  			$this->basket["delivery"]["EMAIL"] = $basket["delivery"]["EMAIL"];*/
  			$this->basket["delivery"] = $basket["delivery"];
  		}	else	{
  			$this->basket["delivery"] = $this->basket["personinfo"];
  		}
  	}	elseif (isset($login))	{
  		$this->basket["delivery"] = $this->basket["personinfo"];
  	}	else	{
  		$this->basket["delivery"] = $basket["delivery"];
  		$this->basket["delivery"]["COUNTRY"] = $this->basket["shipping"]["title"];
  	}
  	
  	if ($this->conf["freeDelivery"]!="" && $this->conf["freeDelivery"]<$this->basket["totaleProdotti"])	{
			$this->calculatedSums_tax["shipping"]= doubleVal(0);
			$this->basket["shipping"]["priceTax"] = doubleVal(0);
			$this->calculatedSums_no_tax["shipping"]= doubleVal(0);
			$this->basket["shipping"]["priceNoTax"] = doubleVal(0);
		}

		$this->setBasketExtras($basket);
		
		
//print_r($this->basket);
//t3lib_div::debug($this->basket);
		$GLOBALS["TSFE"]->fe_user->setKey("ses","recs",$this->basket);
	}
	
	
	/**
	 * This function clears the personal info
	 */
	function resetPersonalInfo()	{
			$this->basket["personinfo"] = "";
			$this->basket["personinfo"]["USER"] = "";
			$this->basket["personinfo"]["PASSWORD"] = "";
			$this->basket["personinfo"]["NAME"] = "";
			$this->basket["personinfo"]["ADDRESS"] = "";
			$this->basket["personinfo"]["CITY"] = "";
			$this->basket["personinfo"]["ZIP"] = "";
			$this->basket["personinfo"]["STATE"] = "";
			$this->basket["personinfo"]["COUNTRY"] = "";
			$this->basket["personinfo"]["COMPANY"] = "";
			$this->basket["personinfo"]["VATCODE"] = "";
			$this->basket["personinfo"]["PRIVATE"] = "";
			$this->basket["personinfo"]["WWW"] = "";
			$this->basket["personinfo"]["PHONE"] = "";
			$this->basket["personinfo"]["MOBILE"] = "";
			$this->basket["personinfo"]["FAX"] = "";
			$this->basket["personinfo"]["EMAIL"] = "";
			$this->basket["personinfo"]["AUTHORIZATION"] = "";
			$this->basket["personinfo"]["CONDITIONS"] = "";
			
			$this->basket["delivery"] = $this->basket["personinfo"];
	}
	
	
	/**
	 * Setting shipping and payment methods
	 */
	function setBasketExtras($basket)	{
			// shipping
		ksort($this->conf["shipping."]);
		reset($this->conf["shipping."]);
		$k=intval($this->basket["shipping"]["key"]);
		if (!$this->checkExtraAvailable("shipping",$k))	{
			$k=intval(key($this->cleanConfArr($this->conf["shipping."],1)));
		}
		$this->basketExtra["shipping"] = $k;
		$this->basketExtra["shipping."] = $this->conf["shipping."][$k."."];
		$excludePayment = trim($this->basketExtra["shipping."]["excludePayment"]);

			// payment
		if ($excludePayment)	{
			$exclArr = t3lib_div::intExplode(",",$excludePayment);
			while(list(,$theVal)=each($exclArr))	{
				unset($this->conf["payment."][$theVal]);
				unset($this->conf["payment."][$theVal."."]);
			}
		}
/*echo($basket["noshippingcost"]);
		if ($basket["noshippingcost"]==1)	{
			// Disabilito il contrassegno
			unset($this->conf["payment."][20]);
			unset($this->conf["payment."]["20."]);
		}
*/
		ksort($this->conf["payment."]);
		reset($this->conf["payment."]);
		$k=intval($this->basket["payment"]["key"]);
		if (!$this->checkExtraAvailable("payment",$k))	{
			$k=intval(key($this->cleanConfArr($this->conf["payment."],1)));
		}
		$this->basketExtra["payment"] = $k;
		$this->basketExtra["payment."] = $this->conf["payment."][$k."."];

//		debug($this->basketExtra);
//		debug($this->conf);
	}
	
	
	
	
	/**
	 * Check if payment/shipping option is available
	 */
	function checkExtraAvailable($name,$key)	{
		if (is_array($this->conf[$name."."][$key."."]) && (!isset($this->conf[$name."."][$key."."]["show"]) || $this->conf[$name."."][$key."."]["show"]))	{
			return true;
		}
	}
	
	/**
	 * Include handle script
	 */
	function includeHandleScript($handleScript,$conf)	{
		include($handleScript);
		return $content;
	}
	
	
	
	
	
	/**
	 * Generates a radio or selector box for payment shipping
	 */
	function generateRadioSelect($key)	{
			/*
			 The conf-array for the payment/shipping configuration has numeric keys for the elements
			 But there are also these properties:

			 	.radio 		[boolean]	Enables radiobuttons instead of the default, selector-boxes
			 	.wrap 		[string]	<select>|</select> - wrap for the selectorboxes.  Only if .radio is false. See default value below
			 	.template	[string]	Template string for the display of radiobuttons.  Only if .radio is true. See default below

			 */
		$type=$this->conf[$key."."]["radio"];
//echo($this->basket["noshippingcost"]);
		if ($this->basket["noshippingcost"]==1)	{
			// Disabilito il contrassegno
			unset($this->conf["payment."][20]);
			unset($this->conf["payment."]["20."]);
		}
		
		$lmKey = $key;
		
		if ($this->basket["personinfo"]["COUNTRY"]!="" && sizeof($this->basket["delivery"])==1 && $key=="shipping")
			$active = "";
		elseif (sizeof($this->basket["delivery"])>1 && $key=="shipping")
			$active = "";
		elseif ($key=="shipping")
			$active = "";
		else
			$active = $this->basketExtra[$key];

		$confArr = $this->cleanConfArr($this->conf[$key."."]);
		$out="";

		$template = $this->conf[$key."."]["template"] ? $this->conf[$key."."]["template"] : '<nobr>###IMAGE### <input type="radio" name="recs[final]['.$key.']" onClick="submit()" value="###VALUE###"###CHECKED###> ###TITLE###</nobr><BR>';
		$wrap = $this->conf[$key."."]["wrap"] ? $this->conf[$key."."]["wrap"] :'<select name="recs[final]['.$key.']" onChange="submit()">|</select>';

		while(list($key,$val)=each($confArr))	{
			if (trim($this->basket["personinfo"]["COUNTRY"])==trim($val["title"]) && sizeof($this->basket["delivery"])==1 && $lmKey=="shipping")	{
				$active = $key;
			}	elseif (sizeof($this->basket["delivery"])>1 && $lmKey=="shipping" && trim($this->basket["delivery"]["COUNTRY"])==trim($val["title"]))	{
				$active = $key;
			}
			if ($val["show"] || !isset($val["show"]))	{
				if ($type)	{	// radio
					$markerArray=array();
					$markerArray["###VALUE###"]=intval($key);
					$markerArray["###CHECKED###"]=(intval($key)==$active?" checked":"");
					$markerArray["###TITLE###"]=$val["title"];
					$markerArray["###IMAGE###"]=$this->cObj->IMAGE($val["image."]);
					$out.=$this->cObj->substituteMarkerArrayCached($template, $markerArray);
				} else {
					$out.='<option value="'.intval($key).'"'.(intval($key)==$active?" selected":"").'>'.htmlspecialchars($val["title"]).'</option>';
				}
			}
		}
		if (!$type)	{
			$out=$this->cObj->wrap($out,$wrap);
		}
		return $out;
	}
	
	
	
	
	
	function generateSelectForPerson($key)	{
		$lmKey = $key;
		
		if ($this->basket["personinfo"]["COUNTRY"]!="")
			$active = "";
		else
			$active = $this->basketExtra[$key];

		$confArr = $this->cleanConfArr($this->conf[$key."."]);
		$out="";

		$wrap = '<select name="personal[COUNTRY]" onChange="submit()">|</select>';

		while(list($key,$val)=each($confArr))	{
			if (trim($this->basket["personinfo"]["COUNTRY"])==trim($val["title"]))	{
				$active = $key;
			}
				$out.='<option value="'.htmlspecialchars($val["title"]).'"'.(intval($key)==$active?" selected":"").'>'.htmlspecialchars($val["title"]).'</option>';
		}
		$out=$this->cObj->wrap($out,$wrap);
		return $out;
	}
	
	
	
	function cleanConfArr($confArr,$checkShow=0)	{
		$outArr=array();
		if (is_array($confArr))	{
			reset($confArr);
			while(list($key,$val)=each($confArr))	{
				if (!t3lib_div::testInt($key) && intval($key) && is_array($val) && (!$checkShow || $val["show"] || !isset($val["show"])))	{
					$outArr[intval($key)]=$val;
				}
			}
		}
		ksort($outArr);
		reset($outArr);
		return $outArr;
	}
	
	
	
	
	
	
	/**
	 * This function sends an email with content and subject passed as parameters
	 */
	function send_email($content,$label, $orderID="", $email="")	{
		// Sends order emails:
		$headers=array();
		if ($this->conf["orderEmail_from"])	{$headers[]="FROM: ".$this->conf["orderEmail_fromName"]." <".$this->conf["orderEmail_from"].">";}

		$recipients = $this->conf["orderEmail_to"];
		if ($email!="")	{
			$recipients.=",".$email;
		}	else	{
  		$recipients.=",".$this->basket["personinfo"]["EMAIL"];
  		if ($this->basket["personinfo"]["EMAIL"]!=$this->basket["delivery"]["EMAIL"])	{
  			$recipients.=",".$this->basket["delivery"]["EMAIL"];
  		}
		}
		$recipients=t3lib_div::trimExplode(",",$recipients,1);

		if (count($recipients))	{	// If any recipients, then compile and send the mail.
			$emailContent=$content;
			if ($emailContent)	{		// If there is plain text content - which is required!!
				$parts = split(chr(10),$emailContent,2);		// First line is subject
				$mA["###ORDERID###"] = $orderID;
				$subject= $this->cObj->substituteMarkerArray($this->pi_getLL($label), $mA);
				$plain_message=trim($parts[1]);


				$cls  = t3lib_div::makeInstanceClassName("htmlmail");
				if (class_exists($cls) && $this->conf["orderEmail_htmlmail"])	{	// If htmlmail lib is included, then generate a nice HTML-email
					//$HTMLmailContent="<style type='text/css'>".$this->config["cssCode"]."</style>".$content;
					$HTMLmailContent="<html><body><style type='text/css'>".$this->config["cssCode"]."</style>".$content."</body></html>";
					}

					$V = array (
						"from_email" => $this->conf["orderEmail_from"],
						"from_name" => $this->conf["orderEmail_fromName"]
					);
//echo("TO: ".implode($recipients,","));
					$Typo3_htmlmail = t3lib_div::makeInstance("htmlmail");
					//$Typo3_htmlmail->useBase64();
					//$Typo3_htmlmail->useQuotedPrintable();
					$Typo3_htmlmail->use8Bit();
					$Typo3_htmlmail->start(implode($recipients,","), $subject, $plain_message, $HTMLmailContent, $V);
					
//echo $HTMLmailContent."<br><br><br>";
//t3lib_div::debug($Typo3_htmlmail->message);
//t3lib_div::debug($Typo3_htmlmail->recipient);
//t3lib_div::debug($Typo3_htmlmail->theParts);

						$Typo3_htmlmail->sendtheMail();

						//debug($HTMLmailContent);
				} else {		// ... else just plain text...
//echo("Recipients: ".implode($recipients,",")."<br>");
//echo("Subject: ".$subject."<br>");
//echo("Plain_message: ".$plain_message."<br>");
//echo("Headers: ".implode($headers,chr(10))."<br>");
					$GLOBALS["TSFE"]->plainMailEncoded(implode($recipients,","), $subject, $plain_message, implode($headers,chr(10)));
				}
			}
		}
	
	
	
	
	
	
	// **************************

	// PayPal interaction

	// **************************
	/**
	 * Returns the complete link to PayPal
	 * https://www.paypal.com/cgi-bin/webscr?cmd=_cart&upload=1&business=EMAIL_REGISTRAZIONE&
	 * item_name = nome del prodotto (=== codice ordine)
	 * currency_code = codice della divisa (USD=dollari, EUR=euro)
	 * amount = ammontare totale
	 * item_number = codice del prodotto (=== codice trackingno ordine)
	 * return = url della pagina di ritorno
	 * on0 = campo opzionale (=== data ordine)
	 */
	 function calculatePayPalParameters($markerArray)	{
	 	//$linkErrore = "index.php?id=266";
	 	$link = $this->basket["payment"]["paylink"];
		$currency_code = $this->basket["payment"]["UICCODE"];
		$return = $this->basket["payment"]["return"];
		$business = $this->basket["payment"]["ShopLogin"];

$markerArray["###ORDER_UID###"] = time();
$markerArray["###ORDER_TRACKING_NO###"] = $markerArray["###ORDER_UID###"];
$markerArray["###ORDER_DATE_NOTFORMATTED###"] = $markerArray["###ORDER_UID###"];
		
		
		$link .= "&amount=".$this->priceFormat($this->calculatedSums_tax['total'],".")."&item_name=".$markerArray["###ORDER_UID###"]."&item_number=".$markerArray["###ORDER_TRACKING_NO###"]."&on0=".$markerArray["###ORDER_DATE_NOTFORMATTED###"];
		$link .= "&currency_code=".$currency_code;
		
		return $link."&business=".$business."&return=".$return;
	 }

	 /**
	 * Returns an array with all the information of the response of PayPal
	 */
	 function decryptDataFromPayPal($transactionBS)	{
	 	$business = $this->basketExtra["payment"]["ShopLogin"];
	 	$transactionBS["###PAY1_SHOPTRANSACTIONID###"] = t3lib_div::_GET('item_name');
		$transactionBS["###ORDERTRACKINGNO###"] = t3lib_div::_GET('item_number');
		$transactionBS["###ORDERDATE###"] = t3lib_div::_GET('on0');
		$transactionBS["ShopLogin"] = $business;
//t3lib_div::debug($transactionBS);
	 	return $transactionBS;
	 }
	
	
	
	
	// **************************

	// GestPay (Banca Sella) interaction

	// **************************
	/**
	 * Returns the complete link to Banca Sella
	 * https://ecomm.sella.it/gestpay/pagam.asp
	 */
	 function calculateGestPayParameters($markerArray)	{
	 	$link = $this->basket["payment"]["paylink"];
	 	
	 	$objCrypt = new Java("GestPayCrypt");
	 	
	 	if (!$objCrypt)
	 		echo("Exception: ".java_last_exception_get());
	 	else	{
  	 	
  	 	$myshoplogin = $this->basket["payment"]["ShopLogin"];
  		$mycurrency = $this->basket["payment"]["UICCODE"];
  		$myamount = $this->priceFormat($this->calculatedSums_tax['total'],".");
  		$mytransactionID = time();
			$myerrpage = $this->basket["payment"]["errpage"];
  
  		$mybuyername = $this->basket["personinfo"]["NAME"];
  		$mybuyeremail = $this->basket["personinfo"]["EMAIL"];
  		$mylanguage = $this->basket["payment"]["language"];
  		$mycustominfo = "";
  		
  		$return = $this->basket["payment"]["return"];
  		
			$objCrypt->SetShopLogin($myshoplogin);
			$objCrypt->SetCurrency($mycurrency);
			$objCrypt->SetAmount($myamount);
			$objCrypt->SetShopTransactionID($mytransactionID);
			$objCrypt->SetBuyerName($mybuyername);
			$objCrypt->SetBuyerEmail($mybuyeremail);
			$objCrypt->SetLanguage($mylanguage);
			$objCrypt->SetCustomInfo($mycustominfo);
			
			$objCrypt->Encrypt();
			
			if (!java_last_exception_get())	{
				$ed = $objCript->GetErrorDescription();
				if ($ed!="")	{
					echo("Errore di encoding: ".$objCrypt->GetErrorCode()." ".$ed." <br />");
				}	else	{
					$b = $objCrypt->GetEncryptedString();
					$a = $objCrypt->GetShopLogin();
				}
			}
			return $link."?a=".$a."&b=".$b;
		}
	 }

	 /**
	 * Returns an array with all the information of the response of PayPal
	 */
	 function decryptDataFromGestPay($transactionBS)	{
	 	/*$business = $this->basketExtra["payment"]["ShopLogin"];
	 	$transactionBS["###PAY1_SHOPTRANSACTIONID###"] = t3lib_div::_GET('item_name');
		$transactionBS["###ORDERTRACKINGNO###"] = t3lib_div::_GET('item_number');
		$transactionBS["###ORDERDATE###"] = t3lib_div::_GET('on0');
		$transactionBS["ShopLogin"] = $business;*/
		
		$parametro_a = trim(t3lib_div::_GET('a'));
		$parametro_b = trim(t3lib_div::_GET('b'));
		
		$objdeCrypt = new Java("GestPayCrypt");
		
		if ($objdeCrypt)	{
			echo("Exception: ".java_last_exception_get());
		}	else	{
			$objdeCrypt->SetShopLogin($parametro_a);
			$objdeCrypt->SetEncryptedString($parametro_b);
			$objdeCrypt->Decrypt();
			
			$transactionBS["###MYSHOPLOGIN###"] = trim($objdeCrypt->GetShopLogin());
			$transactionBS["###MYCURRENCY###"] = trim($objdeCrypt->GetCurrency());
			$transactionBS["###MYAMOUNT###"] = trim($objdeCrypt->GetAmount());
			$transactionBS["###MYSHOPTRANSACTIONID###"] = trim($objdeCrypt->GetShopTransactionID());
			$transactionBS["###MYBUYERNAME###"] = trim($objdeCrypt->GetBuyerName());
			$transactionBS["###MYBUYEREMAIL###"] = trim($objdeCrypt->GetBuyerEmail());
			$transactionBS["###MYTRANSACTIONRESULT###"] = trim($objdeCrypt->GetTransactionResult());
			$transactionBS["###MYAUTHORIZATIONCODE###"] = trim($objdeCrypt->GetAuthorizationCode());
			$transactionBS["###MYERRORCODE###"] = trim($objdeCrypt->GetErrorCode());
			$transactionBS["###MYERRORDESCRIPTION###"] = trim($objdeCrypt->GetErrorDescription());
			$transactionBS["###MYBANKTRANSACTIONID###"] = trim($objdeCrypt->GetBankTransactionID());
			$transactionBS["###MYALERTCODE###"] = trim($objdeCrypt->GetAlertCode());
			$transactionBS["###MYALERTDESCRIPTION###"] = trim($objdeCrypt->GetAlertDescription());
			$transactionBS["###MYCUSTOMINFO###"] = trim($objdeCrypt->GetCustomInfo());
			$transactionBS["###PAY1_SHOPTRANSACTIONID###"] = $transactionBS["###MYTRANSACTIONRESULT###"];
		}
		
//t3lib_div::debug($transactionBS);
	 	return $transactionBS;
	 }
	 
	 
 	// **************************
	// BancaSella interaction
	// **************************

	/**
	 * Returns the complete link to Banca Sella
	 * https://ecomm.sella.it/gestpay/pagam.asp?a=CODICEVENDITORE&b=TUTTI_I_PARAMETRI_OBBLIGATORI_SEPARATI_DA_"*P1*"
	 * PAY1_UICCODE = codice che identifica la divisa in cui e' specificato l'ammontare (242 per €)
	 * PAY1_AMOUNT = ammontare dell'acquisto (separatore dei centesimi . )
	 * PAY1_SHOPTRANSACTIONID = codice univoco attribuito dal venditore all'ordine corrente
	 * PAY1_OTP = chiave monouso da inviare al server e da cancellare dal file delle chiavi
	 * PAY1_IDLANGUAGE = codice della lingua (1 per l'italiano)
	 */
	 function calculateBancaSellaParameters($markerArray)	{
	 	$linkErrore = $this->basket["payment"]["linkError"];
	 	$link = $this->basket["payment"]["paylink"];
		
		$ShopLogin = $this->basket["payment"]["ShopLogin"];
		$UICCODE = $this->basket["payment"]["UICCODE"];
		$IDLANGUAGE = $this->basket["payment"]["IDLANGUAGE"];
		
		$ID_OK = $this->basket["payment"]["id_ok"];
		$ID_KO = $this->basket["payment"]["id_ko"];
		
		$otpSend = $this->basket["payment"]["otp_send"];
		
		$markerArray["###ORDER_UID###"] = time();
		$markerArray["###ORDER_TRACKING_NO###"] = $markerArray["###ORDER_UID###"];
		$markerArray["###ORDER_DATE_NOTFORMATTED###"] = $markerArray["###ORDER_UID###"];

		//$a = "GESPAY23083";
		$a = $ShopLogin;
		//$b = 'PAY1_UICCODE='.$UICCODE.'*P1*PAY1_AMOUNT='.$markerArray["###PRICE_TOTAL_TAX###"].'*P1*PAY1_SHOPTRANSACTIONID='.$markerArray["###ORDER_UID###"].'*P1*ORDERDATE='.$markerArray["###ORDER_DATE_NOTFORMATTED###"].'*P1*ORDERTRACKINGNO='.$markerArray["###ORDER_TRACKING_NO###"];
		$b = 'PAY1_UICCODE='.$UICCODE.'*P1*PAY1_AMOUNT='.$this->priceFormat($markerArray["###BASKET_TOTAL_NOFORMAT###"],".").'*P1*PAY1_SHOPTRANSACTIONID='.$markerArray["###ORDER_UID###"];

		$linee = file($optSend);
		if($linee!==false)	{
			$riga = trim(array_pop($linee));
			while (strlen($riga)=="\n\r" && sizeof($linee)>0)	{
				$riga = trim(array_pop($linee));
			}
			if(strlen($riga)!=32)	{
				return $linkErrore."&err=1";
			}	else	{
				//$toFile = implode("", $linee);
				$toFile = "";
				for ($i=0; $i<sizeof($linee); $i++)	{
					$toFile.=trim($linee[$i])."\n";
				}
//t3lib_div::debug($toFile);
				$puntatore = fopen($optSend, "w");
				if(!fwrite($puntatore, $toFile))	{
					return $linkErrore."&err=2";
				}
			}
		}	else	{
			return $linkErrore."&err=3";
		}
		$OTP = $riga;

		//$b .= "*P1*PAY1_OTP=".$OTP."*P1*PAY1_IDLANGUAGE=".$IDLANGUAGE;
		$b .= "*P1*PAY1_OTP=".$OTP;
		//$b .= "*P1*ID_OK=".$ID_OK."*P1*ID_KO=".$ID_KO;
		return $link."?a=".$a."&b=".$b;
	 }

	 /**
	 * Returns an array with all the information of the response of Banca Sella
	 * https://ecomm.sella.it/gestpay/pagam.asp?a=CODICEVENDITORE&b=TUTTI_I_PARAMETRI_OBBLIGATORI_SEPARATI_DA_"*P1*"
	 * PAY1_UICCODE = codice che identifica la divisa in cui è specificato l'ammontare (242 per €)
	 * PAY1_AMOUNT = ammontare dell'acquisto (separatore dei centesimi . )
	 * PAY1_SHOPTRANSACTIONID = codice univoco attribuito dal venditore all'ordine corrente
	 * PAY1_OTP = chiave monouso inviata dal server e da controllare con il file delle chiavi di risposta
	 * PAY1_IDLANGUAGE = codice della lingua (1 per l'italiano)
	 * PAY1_TRANSACTIONRESULT = risultato della transazione (OK, KO, XX [=indefinito])
	 * PAY1_AUTHORIZATIONCODE = Codice di autorizzazione della transazione
	 * PAY1_BANKTRANSACTIONID = Identificativo attribuito alla transazione da GestPay
	 * PAY1_ERRORCODE = Codice d'errore
	 * PAY1_ERRORDESCRIPTION = Descrizione dell'errore
	 * ERRORE_BANCA_SELLA = impostato a 1 se la chiave OTP inviata da Banca Sella non esiste nel db
	 */
	 function decryptDataFromBancaSella($transactionBS)	{
	 	$a = t3lib_div::_GET('a');
		$b = t3lib_div::_GET('b');
		
		$otpReceive = $this->basket["payment"]["otp_receive"];
		
		$bExploded = explode("*P1*", $b);

		for($i=0; $i<sizeof($bExploded); $i++)	{
			$coppia = $bExploded[$i];
			$coppiaExploded = explode("=", $coppia);
			$transactionBS[$coppiaExploded[0]] = $coppiaExploded[1];
		}
		$transactionBS["ShopLogin"] = $a;
		
		$transactionBS["ERRORE_BANCA_SELLA"] = 0;
		
		$otp = $transactionBS["PAY1_OTP"];
		if (strlen($otp)!=32)	{
			$transactionBS["ERRORE_BANCA_SELLA"] = 1;
		}	else	{
			$linee = file($optReceive);
//t3lib_div::debug($otp);
			$index = -1;
  		$toFile = "";
			for ($i=0; $i<sizeof($linee); $i++)	{
				if ($otp==trim($linee[$i]))	{
					$index = $i;
				}	else	{
					$toFile.=trim($linee[$i])."\n";
				}
			}
//t3lib_div::debug($toFile);
			if ($index>=0)	{
				//array_splice($linee, $index, 1);
				$puntatore = fopen($optReceive, "w");
				if(!fwrite($puntatore, $toFile))	{
					$transactionBS["ERRORE_BANCA_SELLA"] = 2;
				}
			}	else	{
				$transactionBS["ERRORE_BANCA_SELLA"] = 3;
			}
		}
//print_r($transactionBS);
	 	return $transactionBS;
	 }
	 
	 
	 
	 
	 // **************************
	// BancaSella interaction
	// **************************

	/**
	 * Returns the complete form to Authorize.net
	 */
	 function calculateAuthorizeParameters($markerArray)	{
	 	$linkErrore = $this->basket["payment"]["linkError"];
	 	$link = $this->basket["payment"]["paylink"];
		$ShopLogin = $this->basket["payment"]["ShopLogin"];
		$TransactionKey = $this->basket["payment"]["TransactionKey"];
		
		$markerArray["###LOGIN_ID###"] = $ShopLogin;
		$markerArray["###FORM_URL_ONLINEBANK###"] = $link;
		$markerArray["###RETURN_LINK###"] = $this->basket["payment"]["returnUrl"];
		
		$markerArray["###ORDER_TRACKING_NO###"] = time();
		srand(time());
		$sequence = rand(1, 1000);
		
		$markerArray["###FINGERPRINT_FIELDS###"] = InsertFP ($ShopLogin, $TransactionKey, $marketArray["###BASKET_TOTAL_NOFORMAT###"], $sequence);
		
		return $markerArray;
	 }

	 /**
	 * Returns an array with all the information of the response of Authorize.net gateway
	 */
	 function decryptDataFromAuthorize($transaction)	{
	 	$transaction["response"] = t3lib_div::_GP('x_response_code');
		$transaction["reason"] = t3lib_div::_GP('x_response_reason_code');
		$transaction["reason_text"] = t3lib_div::_GP('x_response_reason_text');
		$transaction["###PAY1_SHOPTRANSACTIONID###"] = t3lib_div::_GP('x_trans_id');
		return $transaction;
	 }
	 
	 
}




class htmlmail extends t3lib_htmlmail {
	
	function start($recipient,$subject,$plain,$html,$V,$bcc="")	{
		if ($recipient)	{
				// Sets the message id
			$this->messageid = '<'.md5(microtime()).'@domain.tld>';
		
			$this->subject = $subject;
			
			$this->from_email = ($V["from_email"]) ? $V["from_email"] : (($V["email"])?$V["email"]:'');
			$this->from_name = ($V["from_name"]) ? $V["from_name"] : (($V["name"])?$V["name"]:'');
			$this->replyto_email = ($V["replyto_email"]) ? $V["replyto_email"] : $this->from_email;
			$this->replyto_name = ($V["replyto_name"]) ? $V["replyto_name"] : $this->from_name;
			$this->organisation = ($V["organisation"]) ? $V["organisation"] : '';
			$this->priority = ($V["priority"]) ? intInRange($V["priority"],1,5) : 3;

			$this->addPlain($plain);

			if ($html!="")	{
				$this->theParts["html"]["content"] = $html;	// Fetches the content of the page
				$this->theParts["html"]["path"] = "";
				
				$this->extractMediaLinks();
				$this->extractHyperLinks();
				$this->fetchHTMLMedia();
				$this->substMediaNamesInHTML(0);	// 0 = relative
				$this->substHREFsInHTML();	
				$this->setHTML($this->encodeMsg($this->theParts["html"]["content"]));
			}
				
			$this->setHeaders();
			
			if($bcc!="")	{
				$this->add_header("Bcc: $bcc");
			}
			
			$this->setContent();
			$this->setRecipient($recipient);
		}
	}	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/extendedshop/pi1/class.tx_extendedshop_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/extendedshop/pi1/class.tx_extendedshop_pi1.php"]);
}

?>
