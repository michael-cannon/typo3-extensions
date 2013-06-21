<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Bernhard Kraft (kraftb@kraftb.at)
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
 * Module 'TV Migrate' for the 'kb_tv_migrate' extension.
 *
 * @author	Bernhard Kraft <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  104: class tx_kbtvmigrate_module1 extends t3lib_SCbase
 *  112:     function init()
 *  129:     function menuConfig()
 *  146:     function main()
 *  212:     function printContent()
 *  223:     function moduleContent()
 *  307:     function error($type)
 *
 *              SECTION: OUTPUT
 *  358:     function printColmapForm($similar)
 *  435:     function printDSTOForm($similar)
 *
 *              SECTION: SAVE
 *  532:     function saveColmapToDB($similar, $data)
 *  618:     function saveDSTOConfToPageheaders($similar, $data)
 *
 *              SECTION: ARRAY MODIFICATION
 *  695:     function rec_setDataArByDSTO($treePart, &$dataAr)
 *  715:     function rec_simplifyDSTOInTree(&$treePart)
 *  768:     function rec_setDSTOTree(&$treePart, $setPages, $ds, $to)
 *  789:     function setUsedT3Columns(&$similar)
 *  819:     function pagesWithSameDSTO_possible($combos)
 *  907:     function dsto_compare($row, $dsto)
 *  927:     function array_compare($a, $b)
 *
 *              SECTION: TO/DS METHODS
 *  958:     function getDS($row)
 *  973:     function getTO($row)
 *  990:     function getDSName($ds_id)
 * 1004:     function getTOName($to_id)
 * 1019:     function getDSTOCombinations($id, &$combos)
 *
 *              SECTION: TEMPLATE METHODS
 * 1064:     function getTemplateCombinations($id)
 * 1080:     function getTemplateCombination(&$combos, $row)
 * 1098:     function rec_getTemplateCombination(&$combos, $rows)
 *
 *              SECTION: Record methods
 * 1119:     function getPageTree($id)
 *
 *              SECTION: STORAGE FOLDERS AND TEMPLATES
 * 1149:     function findingStorageFolderIds()
 * 1183:     function getStorageFolders()
 *
 *              SECTION: EXTRA METHODS
 * 1212:     function getItemLabel($items, $val)
 *
 * TOTAL FUNCTIONS: 29
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:kb_tv_migrate/mod1/locallang.php');
#include ('locallang.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once (PATH_t3lib.'class.t3lib_treeview.php');
require_once (PATH_t3lib.'class.t3lib_tceforms.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');
require_once (t3lib_extMgm::extPath('templavoila').'class.tx_templavoila_xmlrelhndl.php');
require_once (t3lib_extMgm::extPath('kb_tv_migrate').'class.tx_kbtvmigrate_xmlrelhndl.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_kbtvmigrate_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the object
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/

		set_time_limit(600);
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('menu_about'),
				'2' => $LANG->getLL('menu_set_page_type'),
				'3' => $LANG->getLL('menu_insert_content_elements'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		$this->foldOut = isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_tv_migrate']['foldOut'])?$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_tv_migrate']['foldOut']:1;
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="'.t3lib_div::linkThisScript(array('kb_tv_migrate' => '')).'" method="POST" target="_self" enctype="multipart/form-data">';

				// JavaScript
			$this->doc->JScode = '
					###JS_REPLACE###
				<script language="javascript" type="text/javascript" src="../res/jsfunc.js"></script>'.chr(10);
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					var please_select = "'.$LANG->getLL('please_select').'";
					var select_ds_first = "'.$LANG->getLL('select_ds_first').'" 
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);

			$this->content = str_replace('###JS_REPLACE###', $this->JSreplacement, $this->content);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		global $LANG;
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = $LANG->getLL('about_text');
				$this->content.=$this->doc->section('About this extension',$content,0,1);
			break;
			case 2:
				$content= '';
				if (!intval($this->id))	{
					$content .= '<h1>Please select a page</h1><p>To start converting a site to Templa Voila please select the Root-Page of the site</p>';
				} else	{
					$combos = $this->getTemplateCombinations($this->id);
					$this->getDSTOCombinations($this->id, $combos);
					$similarPages = $this->pagesWithSameDSTO_possible($combos);
					$data = t3lib_div::_GP('kb_tv_migrate');
					if (is_array($data['dsto'])&&count($data['dsto']))	{
						$ok = $this->saveDSTOConfToPageheaders($similarPages, $data['dsto']);
						if ($ok)	{
							$content = '<h3>Save complete !!!</h3>
							<p>
								The data was saved sucessfully to the page headers. Have a look at your page headers
								and see if everything is set as expected.
							</p>'.chr(10);
						} else	{
							$content = '<h3>Error occured while saving</h3>
							<p>
								While saving the DS/TO configuration to the page headers there occured an error.<br />
								The specific error message is : <br />
								<br />
								<i>ERROR: '.$this->errorMsg.'</i>
								<br />
								<br />
								Please consult the Erros section of the manual on this specific error. If you don\'t find
								anything in there you can search the web or write a mail to the extension author.
							</p>'.chr(10);
						}
					} else	{
						$content .= $this->printDSTOForm($similarPages);
					}
				}
				$this->content.=$this->doc->section('Used Template Combinations',$content,0,1);
			break;
			case 3:
				$content='';
				if (!intval($this->id))	{
					$content .= '<h1>Please select a page</h1><p>To start converting a site to Templa Voila please select the Root-Page of the site</p>';
				} else	{
					$combos = $this->getTemplateCombinations($this->id);
					$this->getDSTOCombinations($this->id, $combos);
					$sameDSTO = $this->pagesWithSameDSTO_set($combos);
					$this->setUsedT3Columns($sameDSTO);
					if (!$sameDSTO&&strlen($this->errorMsg))	{
						$content = $this->error(2);
					} else {
						$data = t3lib_div::_GP('kb_tv_migrate');
						if (is_array($data['colmap'])&&count($data['colmap']))	{
							$ok = $this->saveColmapToDB($sameDSTO, $data['colmap']);
							if ($ok)	{
								$content = '<h3>Save complete !!!</h3>
								<p>
									The data was saved sucessfully to the tt_content table. Have a look at your Templa Voila
									pages and see if everything is inserted as expected.
								</p>'.chr(10);
							} else	{
								$content = $this->error(1);
							}
						} else	{
							$content .= $this->printColmapForm($sameDSTO);
						}
					}
				}
				$this->content.=$this->doc->section('Column Mapping',$content,0,1);
			break;
		}
	}


	/**
	 * Returns an error message
	 *
	 * @param	integer		The type of error
	 * @return	string		The error message
	 */
	function error($type)	{
		switch ($type)	{
			case 1:
				$content = '<h3>Error occurred while saving</h3>
								<p>
									While inserting the content elements into the Templa Voila columns there occurred an error.<br />
									The specific error message is : <br />
									<br />
									<i>ERROR: '.$this->errorMsg.'</i>
									<br />
									<br />
									Please consult the Erros section of the manual on this specific error. If you don\'t find
									anything in there you can search the web or write a mail to the extension author.
								</p>'.chr(10);
			break;
			case 2:
				$content = '<h3>Error occurred while checking DS/TO</h3>
								<p>
									While checking the validity of the DS/TO configuration some error occurred : <br />
									<br />
									<i>ERROR: '.$this->errorMsg.'</i>
									<br />
									<br />
									Please consult the Erros section of the manual on this specific error. If you don\'t find
									anything in there you can search the web or write a mail to the extension author.
								</p>'.chr(10);
			break;
			default:
				$content = '<h3>Unspecified Error occurred</h3>
								<p>
									An unspecified error occurred while performing some operation. This should not happen.
								</p>'.chr(10);
			break;
		}
		return $content;
	}

	/*******************************************
	 *
	 * OUTPUT
	 *
	 * This methods perform operations on the data arrays
	 *
	 *******************************************/

	/**
	 * Returns an the form for defining the column mapping
	 *
	 * @param	array		Data array
	 * @return	string		HTML
	 */
	function printColmapForm($similar)	{
		global $LANG;
		$content = '';
		$menuItems = array();
		$selectId = 0;
		$JS .= 'var selectVals = Array();'.chr(10);
		$JS .= 'var validTO = Array();'.chr(10);
		$JS .= 'var validTAB = Array();'.chr(10);
		$JS .= 'var validALL = 0;'.chr(10);
		$tab = 0;
		foreach ($similar as $main => $main_ar)	{
			foreach ($main_ar as $sub => $similar_ar)	{
				$JS .= 'validTO['.$tab.'] = Array();'.chr(10);
				$JS .= 'validTAB['.$tab.'] = 0;'.chr(10);
				$HTML = '';
				$HTML .= '<div class="bgColor4">'.chr(10);
				foreach ($similar_ar as $cnt => $data)	{
					$JS .= 'selectVals['.$selectId.'] = Array();'.chr(10);
					$JS .= 'validTO['.$tab.']['.$selectId.'] = 0;'.chr(10);
					$HTML .= '<h2>Pages with same TS/DO configuration:</h2>'.chr(10);
					$HTML .= '<ul>'.chr(10);
					foreach ($data['PAGES'] as $uid => $row)	{
						$HTML .= '<li>'.t3lib_BEfunc::getRecordTitle('pages', $row).' ('.$uid.')</li>'.chr(10);
					}
					$HTML .= '</ul>'.chr(10);
					$HTML .= '<p>Please select now which colum should get mapped to which TV column. You can just choose TV columns which are mapped in the TO selected for those pages. Content Elements get just inserted if they aren\'t in the TV column already. If more than one T3 column gets mapped to the same TV column then Content Elements from the lower (in this form) mapping will be above the elements of the higher (in this form) mapping.</p>'.chr(10);
					$HTML .= '<table cellspacing="8" cellpadding="0" border="0">'.chr(10);
					foreach ($data['T3_COLUMNS'] as $t3Col)	{
						$getLabel = $this->getItemLabel($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'], $t3Col);
						$HTML .= '<tr>
		<td align="right" style="font-weight: bold;">
			'.$LANG->getLL('t3col_label').' "'.$LANG->sL($getLabel).'" ==> '.$LANG->getLL('tvcol_label').' :
		</td>
		<td>
			<select id="select_'.$selectId.'_col" name="kb_tv_migrate[colmap]['.rawurlencode($main).']['.rawurlencode($sub).']['.$cnt.']['.$t3Col.']">
				<option value="">'.$LANG->getLL('no_target_column').'</option>'.chr(10);
						foreach ($data['TV_COLUMNS'] as $field => $fieldAr) {
							$HTML .= '<option value="'.$field.'">'.$fieldAr['tx_templavoila']['title'].'</option>'.chr(10);
						}
						$HTML .='</select>
		</td>
	</tr>';
					}
					$HTML .= '</table>'.chr(10);
					$selectId++;
				} // EOF: foreach ($similar_ar as $cnt => $data)	{

				$HTML .= '</div>'.chr(10);

				$mainTitle = ($main=='_DEFAULT')?'DEFAULT':$main;
				$subTitle = ($sub=='_DEFAULT')?'DEFAULT':$sub;
				$menuItem = array(
					'label' => $mainTitle.' / '.$subTitle,
					'description' => 'Pages with this MAIN / SUB Template',
					'linkTitle' => 'Pages with this MAIN / SUB Template',
					'content' => $HTML,
					'stateIcon' => -1,
				);
				$menuItems[] = $menuItem;
				$tab++;
			} // EOF: foreach ($main_ar as $sub => $similar_ar)	{
		} // EOF: foreach ($similar as $main => $main_ar)	{
		$content .= $this->doc->getDynTabMenu($menuItems, 'kb_tv_migrate', $this->foldOut?1:-1, $this->foldOut?true:false);
		$content .= '<input type="submit" name="submit" value="Save this Column-Mapping" id="submit" style="border: 1px solid #333333; padding: 4px 10px 4px 10px; margin: 20px 0px 0px 20px; font-weight: bold; font-size: 15px; width: 300px;" class="bgColor6" onClick="return confirm(\''.$LANG->getLL('confirm_mapping').'\')">';
		$this->JSreplacement = $this->doc->wrapScriptTags($JS);
		return $content;
	} // EOF: function printForm($similar)	{


	/**
	 * Returns an the form for defining DS/TO configuration
	 *
	 * @param	array		Data array
	 * @return	string		HTML
	 */
	function printDSTOForm($similar)	{
		global $LANG;
		$content = '';
		$menuItems = array();
		$selectId = 0;
		$JS .= 'var selectVals = Array();'.chr(10);
		$JS .= 'var validTO = Array();'.chr(10);
		$JS .= 'var validTAB = Array();'.chr(10);
		$JS .= 'var validALL = 0;'.chr(10);
		$tab = 0;
		foreach ($similar as $main => $main_ar)	{
			foreach ($main_ar as $sub => $similar_ar)	{
				$JS .= 'validTO['.$tab.'] = Array();'.chr(10);
				$JS .= 'validTAB['.$tab.'] = 0;'.chr(10);
				$HTML = '';
				$HTML .= '<div class="bgColor4">'.chr(10);
				foreach ($similar_ar as $cnt => $data)	{
					$JS .= 'selectVals['.$selectId.'] = Array();'.chr(10);
					$JS .= 'validTO['.$tab.']['.$selectId.'] = 0;'.chr(10);
					$HTML .= '<h2>Pages with same possible TS/DO configurations:</h2>'.chr(10);
					$HTML .= '<ul>'.chr(10);
					foreach ($data['PAGES'] as $uid => $row)	{
						$HTML .= '<li>'.t3lib_BEfunc::getRecordTitle('pages', $row).' ('.$uid.')</li>'.chr(10);
					}
					$HTML .= '</ul>'.chr(10);
					$HTML .= '<p>Please select now which DS/TO those pages should get assinged. If you wish to make finer grained decisions on which DS/TO to use please select it manually afterwards</p>'.chr(10);
					$HTML .= '<table cellspacing="8" cellpadding="0" border="0">
	<tr>
		<td align="right" style="font-weight: bold;">
			'.$LANG->getLL('ds_label').' :
		</td>
		<td>
			<select id="select_'.$selectId.'_ds" name="kb_tv_migrate[dsto]['.rawurlencode($main).']['.rawurlencode($sub).']['.$cnt.'][ds]" onChange="updateTOselect('.$selectId.'); updateValid('.$selectId.', '.$tab.'); return false;">
				<option value="0">'.$LANG->getLL('please_select').'</option>'.chr(10);
					foreach ($data['DSTO_OPTIONS'] as $ds => $to_ar) {
						$HTML .= '<option value="'.$ds.'">'.$this->getDSName($ds).'</option>'.chr(10);
						$JS .= 'selectVals['.$selectId.']['.$ds.'] = Array();'.chr(10);
						foreach ($to_ar as $to => $val)	{
							if (!intval($val)) continue;
							$JS .= 'selectVals['.$selectId.']['.$ds.']['.$to.'] = "'.$this->getTOName($to).'";'.chr(10);
						}
					} // EOF: foreach ($data['DSTO_OPTIONS'] as $ds => $to_ar) {
					$HTML .='</select>
		</td>
	</tr>
	<tr>
		<td align="right" style="font-weight: bold;">
			'.$LANG->getLL('to_label').' :
		</td>
		<td>
			<select id="select_'.$selectId.'_to" name="kb_tv_migrate[dsto]['.rawurlencode($main).']['.rawurlencode($sub).']['.$cnt.'][to]" onChange="updateValid('.$selectId.', '.$tab.')">
				<option value="0">'.$LANG->getLL('select_ds_first').'</option>
			</select>
		</td>
	</tr>
</table>'.chr(10);
					$selectId++;
				} // EOF: foreach ($similar_ar as $cnt => $data)	{
				$HTML .= '</div>'.chr(10);
				$mainTitle = ($main=='_DEFAULT')?'DEFAULT':$main;
				$subTitle = ($sub=='_DEFAULT')?'DEFAULT':$sub;
				$menuItem = array(
					'label' => $mainTitle.' / '.$subTitle,
					'description' => 'Pages with this MAIN / SUB Template',
					'linkTitle' => 'Pages with this MAIN / SUB Template',
					'content' => $HTML,
					'stateIcon' => 2,
				);
				$menuItems[] = $menuItem;
				$tab++;
			} // EOF: foreach ($main_ar as $sub => $similar_ar)	{
		} // EOF: foreach ($similar as $main => $main_ar)	{
		$content .= '<form action="'.t3lib_div::linkThisScript(array('kb_tv_migrate' => '')).'" method="POST" enctype="multipart/form-data" target="_self">'.chr(10);
		$content .= $this->doc->getDynTabMenu($menuItems, 'kb_tv_migrate', $this->foldOut?1:-1, $this->foldOut?true:false);
		$content .= '<input type="submit" name="submit" value="Save TO/DS Configuration to Pageheaders" id="submit" style="border: 1px solid #333333; padding: 4px 10px 4px 10px; margin: 20px 0px 0px 20px; font-weight: bold; font-size: 15px; width: 400px; display: none;" class="bgColor6">';
		$content .= '</form>'.chr(10);
		$this->JSreplacement = $this->doc->wrapScriptTags($JS);
		return $content;
	} // EOF: function printForm($similar)	{


	/*******************************************
	 *
	 * SAVE
	 *
	 * This methods store the submitted data in the database
	 *
	 *******************************************/


	/**
	 * Applies the column mapping to the page headers. Inserts references to the content elements of the T3 columns
	 *
	 * @param	array		Data array
	 * @param	array		Submitted data
	 * @return	bool		true on success, false on error
	 */
	function saveColmapToDB($similar, $data)	{
		if (!is_array($data))	{
			$this->errorMsg = 'Variable expected to be array but was from different type (11)';
			return false;
		}
		foreach ($data as $main => $main_ar)	{
			if (!is_array($main_ar))	{
				$this->errorMsg = 'Variable expected to be array but was from different type (12)';
				return false;
			}
			foreach ($main_ar as $sub => $data_ar)	{
				if (!is_array($data_ar))	{
					$this->errorMsg = 'Variable expected to be array but was from different type (13)';
					return false;
				}
				if (!is_array($similar[$main][$sub]))	{
					$this->errorMsg = 'Posted path not found in pagestructure array (14)';
					return false;
				}
				foreach ($data_ar as $cnt => $conf_ar)	{
					if (!is_array($conf_ar))	{
						$this->errorMsg = 'Variable expected to be array but was from different type (15)';
						return false;
					}
					if (!is_array($similar[$main][$sub][$cnt]))	{
						$this->errorMsg = 'Posted path not found in pagestructure array (16)';
						return false;
					}
					$t3Cols = implode(',', $similar[$main][$sub][$cnt]['T3_COLUMNS']);
					$tvCols = implode(',', array_keys($similar[$main][$sub][$cnt]['TV_COLUMNS']));
					foreach($conf_ar as $t3Col => $tvCol)	{
						$tvCol = trim($tvCol);
						if (!strlen($tvCol)) continue;
						$t3Col = intval($t3Col);
						if (!t3lib_div::inList($t3Cols, $t3Col))	{
							$this->errorMsg = 'Posted T3 column not valid for this section (17)';
							return false;
						}
						if (!t3lib_div::inList($tvCols, $tvCol))	{
							$this->errorMsg = 'Posted TV column not valid for this section (18)';
							return false;
						}
						if (strlen($tvCol))	{
							$similar[$main][$sub][$cnt]['MAPPING'][$t3Col] = $tvCol;
						}
					}
				} // EOF: foreach ($data_ar as $cnt => $conf_ar)	{
			} // EOF: foreach ($main_ar as $sub => $data_ar)	{
		} // EOF: foreach ($data as $main => $main_ar)	{
			// Insert references to the content elements into the TV XML records
		$xmlhandler = t3lib_div::makeInstance('tx_kbtvmigrate_xmlrelhndl');
		foreach ($similar as $main => $mainAr)	{
			foreach ($mainAr as $sub => $subAr)	{
				foreach ($subAr as $cnt => $dataAr)	{
					foreach ($dataAr['PAGES'] as $uid => $pageRow)	{
						if (is_array($dataAr['MAPPING'])&&count($dataAr['MAPPING']))	{
							$xml = t3lib_div::xml2array($pageRow['tx_templavoila_flex']);
							$xml = is_array($xml) ? $xml : array();

							foreach ($dataAr['MAPPING'] as $t3Col => $tvCol)	{
								$records = t3lib_BEfunc::getRecordsByField('tt_content', 'pid', $pageRow['uid'], ' AND colPos='.$t3Col.' '.t3lib_BEfunc::BEenableFields('tt_content'), '', ' sorting DESC');
								if (is_array($records)&&count($records))	{
									$elements = $xml['data']['sDEF']['lDEF'][$tvCol]['vDEF'];
									$xmlTarget = 'pages:'.$pageRow['uid'].':sDEF:lDEF:'.$tvCol.':vDEF:0';
									foreach ($records as $contentRow)	{
											// Add if not already in list
										if (!t3lib_div::inList($elements, $contentRow['uid']))	{
											$xmlhandler->pasteRecord('ref', 'tt_content|'.$contentRow['uid'], $xmlTarget);
										}
									} // EOF: foreach ($records as $contentRow)	{
								} // EOF: if (is_array($records)&&count($records))	{
							} // EOF: foreach ($dataAr['MAPPING'] as $t3Col => $tvCol)	{
						} // EOF: if (is_array($dataAr['MAPPING'])&&count())	{
					} // EOF: foreach ($dataAr['PAGES'] as $uid => $pageRow)	{
				} // EOF: foreach ($subAr as $cnt => $dataAr)	{
			} // EOF: foreach ($mainAr as $sub => $subAr)	{
		} // EOF: foreach ($similar as $main => $mainAr)	{
		return true;
	} // EOF: function saveColmapToDB($similar, $data)	{


	/**
	 * Saves the new DS/TO configuration to the page headers
	 *
	 * @param	array		Data array
	 * @param	array		Submitted data
	 * @return	bool		true on success, false on error
	 */
	function saveDSTOConfToPageheaders($similar, $data)	{
		if (!is_array($data))	{
			$this->errorMsg = 'Variable expected to be array but was from different type (1)';
			return false;
		}
		$tree = $this->getPageTree($this->id);
		foreach ($data as $main => $main_ar)	{
			if (!is_array($main_ar))	{
				$this->errorMsg = 'Variable expected to be array but was from different type (2)';
				return false;
			}
			foreach ($main_ar as $sub => $data_ar)	{
				if (!is_array($data_ar))	{
					$this->errorMsg = 'Variable expected to be array but was from different type (3)';
					return false;
				}
				if (!is_array($similar[$main][$sub]))	{
					$this->errorMsg = 'Posted path not found in pagestructure array (4)';
					return false;
				}
				foreach ($data_ar as $cnt => $conf_ar)	{
					if (!is_array($conf_ar))	{
						$this->errorMsg = 'Variable expected to be array but was from different type (5)';
						return false;
					}
					if (!(isset($conf_ar['ds'])&&isset($conf_ar['to'])))	{
						$this->errorMsg = 'Expected Key in array not set (6)';
						return false;
					}
					if (!isset($similar[$main][$sub][$cnt]))	{
						$this->errorMsg = 'Combination index not set in pagestructure array (7)';
						return false;
					}
					if (!intval($similar[$main][$sub][$cnt]['DSTO_OPTIONS'][$conf_ar['ds']][$conf_ar['to']]))	{
						$this->errorMsg = 'Posted DS/TO combination not valid for this Main/Sub Template combination (8)';
						return false;
					}
					$pages = array();
					foreach ($similar[$main][$sub][$cnt]['PAGES'] as $uid => $row)	{
						$pages[] = $uid;
					}
					$pages = implode(',', $pages);
					$this->rec_setDSTOTree($tree, $pages, $conf_ar['ds'], $conf_ar['to']);
				} // EOF: foreach ($data_ar as $cnt => $conf_ar)	{
			} // EOF: foreach ($main_ar as $sub => $data_ar)	{
		} // EOF: foreach ($data as $main => $main_ar)	{
		$err = $this->rec_simplifyDSTOInTree($tree);
		if (strlen($err)) {
			$this->errorMsg = $err;
			return false;
		}
		$dataAr = array();
		$this->rec_setDataArByDSTO($tree, $dataAr);
		$tcemain = t3lib_div::makeInstance('t3lib_TCEmain');
		$tcemain->start($dataAr, array());
		$tcemain->process_datamap();
		return true;
	} // EOF: function saveDSTOConfToPageheaders($similar, $data)	{


	/*******************************************
	 *
	 * ARRAY MODIFICATION
	 *
	 * This methods perform operations on the data arrays
	 *
	 *******************************************/


	/**
	 * Sets the data array which get's passed to TCAmain by the values of the templavoila DS/TO configuration fields
	 * Gets called recursive
	 *
	 * @param	array		Tree part
	 * @param	array		TCAmain datamap array
	 * @return	void
	 */
	function rec_setDataArByDSTO($treePart, &$dataAr)	{
		$dataAr['pages'][$treePart['row']['uid']]['tx_templavoila_ds'] = $treePart['row']['tx_templavoila_ds'];
		$dataAr['pages'][$treePart['row']['uid']]['tx_templavoila_to'] = $treePart['row']['tx_templavoila_to'];
		$dataAr['pages'][$treePart['row']['uid']]['tx_templavoila_next_ds'] = $treePart['row']['tx_templavoila_next_ds'];
		$dataAr['pages'][$treePart['row']['uid']]['tx_templavoila_next_to'] = $treePart['row']['tx_templavoila_next_to'];
		if (is_array($treePart['childs'])&&count($treePart['childs']))	{
			foreach ($treePart['childs'] as $cnt => $data)	{
				$this->rec_setDataArByDSTO($treePart['childs'][$cnt], $dataAr);
			}
		}
	}


	/**
	 * Simplifies the DS/TO structure. In overall it checks how many of the sub-pages of a page are set to specific DS/TO combinations and finds the max set one. Then it sets the Sub-Page DS and TO on this page to the determined value and set all DS/TO settings of the subpages which match this combination to 0
	 * Gets called recursive
	 *
	 * @param	array		Tree part
	 * @return	void
	 */
	function rec_simplifyDSTOInTree(&$treePart)	{
		$childDSTO = array();
		if (is_array($treePart['childs'])&&count($treePart['childs']))	{
			foreach ($treePart['childs'] as $cnt => $data)	{
				$childDSTO[$data['row']['tx_templavoila_ds']][$data['row']['tx_templavoila_to']][] = $data['row']['uid'];
			}
		}
		$max = 0;
		$maxDS = 0;
		$maxTO = 0;
		if (is_array($childDSTO)&&count($childDSTO))	{
			foreach ($childDSTO as $ds => $ds_ar)	{
				foreach ($ds_ar as $to => $to_ar)	{
					if (($cnt = count($to_ar))>$max)	{
						$max = $cnt;
						$maxDS = $ds;
						$maxTO = $to;
					}
				}
			}
		}
		if ($max)	{
			if (!$maxDS||!$maxTO)	{
				return 'For some reason there is a max TS/DO number of childs in this row but has invalid maxDS or maxTO (9)';
			}
			$treePart['row']['tx_templavoila_next_ds'] = $maxDS;
			$treePart['row']['tx_templavoila_next_to'] = $maxTO;
			foreach ($treePart['childs'] as $cnt => $data)	{
				if (($data['row']['tx_templavoila_ds']==$maxDS)&&($data['row']['tx_templavoila_to']==$maxTO))	{
					$treePart['childs'][$cnt]['row']['tx_templavoila_ds'] = 0;
					$treePart['childs'][$cnt]['row']['tx_templavoila_to'] = 0;
				}
			}
		}
			// Call recursive
		if (is_array($treePart['childs'])&&count($treePart['childs']))	{
			foreach ($treePart['childs'] as $cnt => $data)	{
				$this->rec_simplifyDSTOInTree($treePart['childs'][$cnt]);
			}
		}
		return '';
	}

	/**
	 * Sets DS/TO recursive in the tree on the given pages.
	 * Gets called recursive
	 *
	 * @param	array		Tree data
	 * @param	string		Set DS/TO to the given values on this pages
	 * @param	integer		UID of DS record
	 * @param	integer		UID of TO record
	 * @return	void
	 */
	function rec_setDSTOTree(&$treePart, $setPages, $ds, $to)	{
		if (t3lib_div::inList($setPages, $treePart['row']['uid']))	{
			$treePart['row']['tx_templavoila_ds'] = $ds;
			$treePart['row']['tx_templavoila_to'] = $to;
			$treePart['row']['tx_templavoila_next_ds'] = 0;
			$treePart['row']['tx_templavoila_next_to'] = 0;
		}
		if (is_array($treePart['childs'])&&count($treePart['childs']))	{
			foreach ($treePart['childs'] as $key => $child)	{
				$this->rec_setDSTOTree($treePart['childs'][$key], $setPages, $ds, $to);
			}
		}
	}

	/**
	 * Sets the T3 Columns used in the by reference passed data array
	 * Extracts data from TSConfig
	 *
	 * @param	array		Template/DS/TO Combinations (passed by reference)
	 * @return	bool		true on success
	 */
	function setUsedT3Columns(&$similar)	{
		if (!is_array($similar)) return false;
		foreach ($similar as $main => $main_ar)	{
			if (!is_array($main_ar)) return false;
			foreach ($main_ar as $sub => $sub_ar)	{
				if (!is_array($sub_ar)) return false;
				foreach ($sub_ar as $cnt => $data_ar)	{
					if (!is_array($data_ar)) return false;
					$usedCols = array();
					foreach ($data_ar['PAGES'] as $uid => $row)	{
						$TSconfig = t3lib_BEfunc::getPagesTSconfig($uid);
						$colPos_list = $TSconfig['mod.']['SHARED.']['colPos_list'];
						if (!strlen($colPos_list)) $colPos_list = '1,0,2,3';
						$usedCols = array_merge($usedCols, t3lib_div::intExplode(',', $colPos_list));
						$usedCols = array_unique($usedCols);
					}
					sort($usedCols);
					$similar[$main][$sub][$cnt]['T3_COLUMNS'] = $usedCols;
				} // EOF: foreach ($sub_ar as $cnt => $data_ar)	{
			} // EOF: foreach ($main_ar as $sub => $sub_ar)	{
		} // EOF: foreach ($similar as $main => $main_ar)	{
		return true;
	}

	/**
	 * Returns pages on which the same DS/TO combination is possible
	 *
	 * @param	array		Template Combinations
	 * @return	array		Template + DS/TO Combinations
	 */
	function pagesWithSameDSTO_possible($combos)	{
		$similar = array();
		foreach ($combos as $main => $main_ar)	{
			foreach ($main_ar as $sub => $pages_ar)	{
				foreach ($pages_ar as $uid => $row)	{
					$found = 0;
					if (is_array($similar[$main][$sub]))	{
						foreach ($similar[$main][$sub] as $cnt => $data)	{
							if (!$this->array_compare($row['_DSTO_OPTIONS'], $data['DSTO_OPTIONS']))	{
								$similar[$main][$sub][$cnt]['PAGES'][$row['uid']] = $row;
								$found = 1;
								break;
							}
						}
					}
					if (!$found)	{
						$similar[$main][$sub][] = array(
							'DSTO_OPTIONS' => $row['_DSTO_OPTIONS'],
							'PAGES' => array($row['uid'] => $row),
						);
					}
				}
			}
		}
		return $similar;
	}

	/**
	 * Returns pages on which the same DS/TO combination is set
	 *
	 * @param	array		Template Combinations
	 * @return	array		Template + DS/TO Combinations
	 */
	function pagesWithSameDSTO_set(&$combos)	{
		$similar = array();
		foreach ($combos as $main => $main_ar)	{
			foreach ($main_ar as $sub => $pages_ar)	{
				foreach ($pages_ar as $uid => $row)	{
					$found = 0;
					if (is_array($similar[$main][$sub]))	{
						foreach ($similar[$main][$sub] as $cnt => $data)	{
							if (!$this->dsto_compare($row, $data['DSTO_DATA']))	{
								$similar[$main][$sub][$cnt]['PAGES'][$row['uid']] = $row;
								$found = 1;
								break;
							}
							if (strlen($this->errorMsg))	{
								return false;
							}
						}
					}
					if (!$found)	{
						$ds = $this->getDS($row);
						$to = $this->getTO($row);
						$toRec = t3lib_BEfunc::getRecord('tx_templavoila_tmplobj', $to);
						$dsRec = t3lib_BEfunc::getRecord('tx_templavoila_datastructure', $ds);
						$dsXML = t3lib_div::xml2array($dsRec['dataprot']);
						$mapping = unserialize($toRec['templatemapping']);
						$TVcols = array();
						foreach ($mapping['MappingInfo']['ROOT']['el'] as $field => $fieldAr)	{
							if ($dsXML['ROOT']['el'][$field]['tx_templavoila']['eType']=='ce')	 {
								$TVcols[$field] = $dsXML['ROOT']['el'][$field];
							}
						}
						$similar[$main][$sub][] = array(
							'DSTO_DATA' => array(
								'ds' => $ds,
								'to' => $to,
							),
							'TV_COLUMNS' => $TVcols,
							'PAGES' => array($row['uid'] => $row),
						);
					}
				}
			}
		}
		return $similar;
	}


	/**
	 * Compares if two DS/TO settings are the same.
	 * Gets called recursive.
	 *
	 * @param	array		Page row
	 * @param	array		Stored DS/TO settings
	 * @return	integer		0 if the settings are equal NOT 0 if different
	 */
	function dsto_compare($row, $dsto)	{
		$this_ds = $this->getDS($row);
		$this_to = $this->getTO($row);
		if (!($this_ds&&$this_to))	{
			$this->errorMsg = 'DS or TO not set for page "'.t3lib_BEfunc::getRecordTitle('pages', $row).'" ('.$row['uid'].') ! (10)';
			return -1;
		}
		if ($this_ds!=$dsto['ds']) return -1;
		if ($this_to!=$dsto['to']) return -1;
		return 0;
	}

	/**
	 * Compares if two arrays are the same.
	 * Gets called recursive.
	 *
	 * @param	array		First array
	 * @param	array		Second array
	 * @return	integer		0 if the array are equal NOT 0 if different
	 */
	function array_compare($a, $b)	{
		if (is_array($a)&&is_array($b))	{
			foreach ($a as $key => $val)	{
				if (!isset($b[$key])) return -1;
				if (is_array($a[$key])&&is_array($b[$key]))	{
					if ($this->array_compare($a[$key], $b[$key]))	{
						return -1;
					}
				} else {
					if ($a[$key]!==$b[$key]) return -1;
				}
			}
		} else {
			return -1;
		}
		return 0;
	}

	/*******************************************
	 *
	 * TO/DS METHODS
	 *
	 *******************************************/


	/**
	 * Returns the DS name of a given page row
	 *
	 * @param	array		Page row
	 * @return	string		Datastructure name
	 */
	function getDS($row)	{
		$this_ds = intval($row['tx_templavoila_ds']);
		if (!$this_ds)	{
			$parent = t3lib_BEfunc::getRecord('pages', $row['pid']);
			$this_ds = intval($parent['tx_templavoila_next_ds']);
		}
		return $this_ds;
	}

	/**
	 * Returns the TO name of a given page row
	 *
	 * @param	array		Page row
	 * @return	string		Template Object name
	 */
	function getTO($row)	{
		$this_to= intval($row['tx_templavoila_to']);
		if (!$this_to)	{
			$parent = t3lib_BEfunc::getRecord('pages', $row['pid']);
			$this_to = intval($parent['tx_templavoila_next_to']);
		}
		return $this_to;
	}


	/**
	 * Returns the Title of a Datastructure
	 *
	 * @param	integer		Datastructure Id
	 * @return	string		Datastructure Name
	 */
	var $DScache = array();
	function getDSName($ds_id)	{
		if (!isset($DScache[$ds_id]))	{
			$DScache[$ds_id] = t3lib_BEfunc::getRecord('tx_templavoila_datastructure', $ds_id);
		}
		return t3lib_BEfunc::getRecordTitle('tx_templavoila_datastructure', $DScache[$ds_id]);
	}

	/**
	 * Returns the Title of a TO
	 *
	 * @param	integer		Template Object Id
	 * @return	string		Template Object Name
	 */
	var $TOcache = array();
	function getTOName($to_id)	{
		if (!isset($TOcache[$to_id]))	{
			$TOcache[$to_id] = t3lib_BEfunc::getRecord('tx_templavoila_tmplobj', $to_id);
		}
		return t3lib_BEfunc::getRecordTitle('tx_templavoila_tmplobj', $TOcache[$to_id]);
	}


	/**
	 * Returns all DS/TO combinations
	 *
	 * @param	integer		Root Id
	 * @param	array		Template combinations
	 * @return	array		Template + DS/TO combinations
	 */
	function getDSTOCombinations($id, &$combos)	{
		$tceforms = t3lib_div::makeInstance('t3lib_TCEforms');
		t3lib_div::loadTCA('pages');
		$initfields_ds = $tceforms->initItemArray($GLOBALS['TCA']['pages']['columns']['tx_templavoila_ds']);
		$initfields_to = $tceforms->initItemArray($GLOBALS['TCA']['pages']['columns']['tx_templavoila_to']);
		$this->findingStorageFolderIds();
		$storage_folders = $this->getStorageFolders();
		foreach ($combos as $main => $sub_ar)	{
			foreach ($sub_ar as $sub => $pages_ar)	{
				foreach ($pages_ar as $uid => $row)	{
					$TSconfig = $tceforms->setTSconfig('pages',$row);
					$selItems_ds = $tceforms->addSelectOptionsToItemArray($initfields_ds, $GLOBALS['TCA']['pages']['columns']['tx_templavoila_ds'], $TSconfig, 'tx_templavoila_ds');
					foreach ($selItems_ds as $dsAr)	{
						if (!($ds = intval($dsAr[1]))) continue;
						$selItems_to = array();
						foreach ($storage_folders as $storage_folder)	{
							$TSconfigTmp = $TSconfig;
							$TSconfigTmp['_THIS_ROW']['tx_templavoila_ds'] = $ds;
							$TSconfigTmp['_STORAGE_PID'] = $storage_folder['uid'];
							$selItems_to = array_merge($selItems_to, $tceforms->addSelectOptionsToItemArray($initfields_to, $GLOBALS['TCA']['pages']['columns']['tx_templavoila_to'], $TSconfigTmp, 'tx_templavoila_to'));
						}
						foreach ($selItems_to as $toAr)	{
							if (!($to = intval($toAr[1]))) continue;
							$combos[$main][$sub][$uid]['_DSTO_OPTIONS'][$ds][$to] = 1;
						}
					} // EOF: foreach ($selItems_ds as $dsAr)	{
				} // EOF: foreach ($pages_ar as $uid => $row)	{
			} // EOF: foreach ($sub_ar as $sub => $pages_ar)	{
		} // EOF: foreach ($combos as $main => $sub_ar)	{
	} // EOF: function getDSTOCombinations($id, &$combos)	{


	/*******************************************
	 *
	 * TEMPLATE METHODS
	 *
	 *******************************************/


	/**
	 * Returns all template combinations
	 *
	 * @param	integer		Root Id
	 * @return	array		Template combinations
	 */
	function getTemplateCombinations($id)	{
		$tree = $this->getPageTree($id);
		$templateCombinations = array();
		$this->getTemplateCombination($templateCombinations, $tree['row']);
		$this->rec_getTemplateCombination($templateCombinations, $tree['childs']);
		return $templateCombinations;
	}


	/**
	 * Returns a single template combination
	 *
	 * @param	array		All combination (passed by reference)
	 * @param	array		Page row
	 * @return	void
	 */
	function getTemplateCombination(&$combos, $row)	{
		$main_tmpl = $row['tx_rlmptmplselector_main_tmpl'];
		if (!strlen($main_tmpl)) $main_tmpl = '_DEFAULT';
		if (!strcmp($main_tmpl, '0')) $main_tmpl = '_DEFAULT';
		$ca_tmpl = $row['tx_rlmptmplselector_ca_tmpl'];
		if (!strlen($ca_tmpl)) $ca_tmpl = '_DEFAULT';
		if (!strcmp($ca_tmpl, '0')) $ca_tmpl = '_DEFAULT';
		$combos[$main_tmpl][$ca_tmpl][$row['uid']] = $row;
	}

	/**
	 * Sets first parameter to all possible template combinations.
	 * Gets called recursive.
	 *
	 * @param	array		All combination (passed by reference)
	 * @param	array		Actual part of tree
	 * @return	void
	 */
	function rec_getTemplateCombination(&$combos, $rows)	{
		foreach ($rows as $row)	{
			$this->getTemplateCombination($combos, $row['row']);
			$this->rec_getTemplateCombination($combos, $row['childs']);
		}
	}



	/*******************************************
	 *
	 * Record methods
	 *
	 *******************************************/

	/**
	 * Returns an page tree
	 *
	 * @param	integer		Root Id
	 * @return	array		Page tree
	 */
	function getPageTree($id)	{
		$row = t3lib_BEfunc::getRecord('pages', $id);
		if (is_array($row))	{
			$tmpChilds = t3lib_BEfunc::getRecordsByField('pages', 'pid', $row['uid'], ' AND doktype<200', '', 'sorting');
			$childs = array();
			if (is_array($tmpChilds) && count($tmpChilds))	{
				foreach ($tmpChilds as $key => $child) {
					if (!intval($child['uid'])) continue;
					$childs[] = $this->getPageTree($child['uid']);
				}
			}
			return array('row' => $row, 'childs' => $childs);
		} else {
			return false;
		}
	}

	/****************************************
	 *
	 * STORAGE FOLDERS AND TEMPLATES
	 *
	 * This methods return the Storage folders and existing Templates
	 *
	 ****************************************/

	/**
	 * Generates $this->storageFolders with available sysFolders linked to as storageFolders for the user
	 *
	 * @return	void		Modification in $this->storageFolders array
	 */
	function findingStorageFolderIds()	{
		global $TYPO3_DB;

			// Init:
		$readPerms = $GLOBALS['BE_USER']->getPagePermsClause(1);
		$this->storageFolders=array();

			// Looking up all references to a storage folder:
		$res = $TYPO3_DB->exec_SELECTquery (
			'uid,storage_pid',
			'pages',
			'storage_pid>0'.t3lib_BEfunc::deleteClause('pages')
		);
		while($row = $TYPO3_DB->sql_fetch_assoc($res))	{
			if ($GLOBALS['BE_USER']->isInWebMount($row['storage_pid'],$readPerms))	{
				$storageFolder = t3lib_BEfunc::getRecord('pages',$row['storage_pid'],'uid,title');
				if ($storageFolder['uid'])	{
					$this->storageFolders[$storageFolder['uid']] = $storageFolder['title'];
				}
			}
		}

			// Compopsing select list:
		$sysFolderPIDs = array_keys($this->storageFolders);
		$sysFolderPIDs[]=0;
		$this->storageFolders_pidList = implode(',',$sysFolderPIDs);
	}


	/**
	 * Returns an array containing all storage folders
	 *
	 * @return	array		Storage folders
	 */
	function getStorageFolders()	{
		$folders = array();
		$ids = t3lib_div::trimExplode(',', $this->storageFolders_pidList, 1);
		foreach ($ids as $id)	{
			if (intval($id))	{
				$row = t3lib_BEfunc::getRecord('pages', $id);
				if (is_array($row))	{
					$folders[] = $row;
				}
			}
		}
		return $folders;
	}

	/****************************************
	 *
	 * EXTRA METHODS
	 *
	 * Some methods we need
	 *
	 ****************************************/

	/**
	 * Returns an label of a TCA 'group' items entry of a specific value
	 *
	 * @param	array		Items from TCA configuration
	 * @param	mixed		Value
	 * @return	string		Label
	 */
	function getItemLabel($items, $val)	{
		if (is_array($items)&&count($items))	{
			foreach ($items as $item)	{
				if ($item[1]==$val) return $item[0];
			}
		}
		return '[INVALID VALUE]';
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_tv_migrate/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_tv_migrate/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_kbtvmigrate_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
