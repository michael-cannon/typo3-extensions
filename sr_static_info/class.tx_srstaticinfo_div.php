<?php
/**************************************************************
*
*  Copyright notice
*
*  (c) 2004 Ren� Fritz (r.fritz@colorcube.de)
*  (c) 2004 Stanislas Rolland (stanislas.rolland@fructifor.com)
*  All rights reserved**  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/*
* 
* Class for handling static info tables: countries, and subdivisions, currencies, languages and taxes 
* 
* $Id: class.tx_srstaticinfo_div.php,v 1.1.1.1 2010/04/15 10:04:06 peimic.comprock Exp $ 
* 
* @author	Ren� Fritz <r.fritz@colorcube.de> 
* @co-author	Stanislas Rolland <stanislas.rolland@fructifor.com> 
* @package TYPO3 * @subpackage tx_srstaticinfo 
*/

/*
* 
* [CLASS/FUNCTION INDEX of SCRIPT] 
* 
* 
*  114:     function getStaticInfoName($type='COUNTRIES', $code, $country='', $countrySubdivision='') 
*  331:     function initLanguages() 
* 
* TOTAL FUNCTIONS: 2 
* (This index is automatically created/updated by the extension "extdeveval") 
* 
*/

class tx_srstaticinfo_div {

	/**
	 * Returns a label field for the current language
	 *
	 * @param	string		table name
	 * @param	boolean		If set (default) the TCA definition of the table should be loaded with t3lib_div::loadTCA(). It will be needed to set it to false if you call this function from inside of tca.php
	 * @return	string		field name
	 */
	function getTCAlabelField($table, $loadTCA=true) {
		global $TCA;

		if($loadTCA) { t3lib_div::loadTCA($table); }
		return $TCA[$table]['ctrl']['label'];
	}

	/**
	 * Returns a sort field for the current language
	 *
	 * @param	string		table name
	 * @param	boolean		If set (default) the TCA definition of the table should be loaded
	 * @return	string		field name
	 */
	function getTCAsortField($table, $loadTCA=true) {
		return tx_srstaticinfo_div::getTCAlabelField($table, $loadTCA);
	}
	
	/**	 
	 * Getting all languages into an array	 
	 * 	where the key is the ISO alpha-2 code of the language	 
	 * 	and where the value are the name of the language in the language set in init()	 
	 *	 
	 * @return	array		An array of names of languages	 
	 */

	function initLanguages()	{
		global $LANG;

		$names = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, lg_iso_2, lg_name_en, lg_country_iso_2', 'static_languages', '1=1', '', 'lg_name_en');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$code = $row['lg_iso_2'].($row['lg_country_iso_2']?'_'.$row['lg_country_iso_2']:'');
			$names[$code] = array($LANG->getLL('language_'.$code), $row['uid'], '');
			if( !$names[$code][0] ) { $names[$code][0] = $LANG->csConvObj->conv($row['lg_name_en'], 'iso-8859-1', $LANG->charSet, 1); }
		}

		$compare = create_function('$a,$b', 'return strcoll($a[0],$b[0]);');
		$currentLocale = setlocale(LC_COLLATE, '');
		$collateLocale = tx_srstaticinfo_div::getCollateLocale();
		if( $collateLocale != 'C') { $collateLocale .= '.' . strtoupper($LANG->charSet); }
		setlocale(LC_COLLATE, $collateLocale);
		uasort($names, $compare);
		setlocale(LC_COLLATE, $currentLocale);

		return $names;
	}

	/**	 
	 * Function to use in own TCA definitions	 
	 * Adds additional select items	 
	 *	 
	 * @param	array		itemsProcFunc data array	 
	 * @return	void	 
	 */	

	function selectItemsTCA($params) {
		global $TCA, $LANG;
/*
		$params['items'] = &$items;
		$params['config'] = $config;
		$params['TSconfig'] = $iArray;
		$params['table'] = $table;
		$params['row'] = $row;
		$params['field'] = $field;
*/
		
		// get the local lang file for this extension of the static info tables:
		$savedGlobal = array();
		$savedGlobal = $GLOBALS['LOCAL_LANG'];
		include (t3lib_extMgm::extPath('sr_static_info').'pi1/locallang.php');
		$GLOBALS['LOCAL_LANG'] = t3lib_div::array_merge_recursive_overrule($savedGlobal,$LOCAL_LANG);

		$table = $params['config']['itemsProcFunc_config']['table'];
		if ($table) {
			$indexField = $params['config']['itemsProcFunc_config']['indexField'];
			$indexField = $indexField ? $indexField : 'uid';
			//$lang = strtolower(tx_srstaticinfo_div::getCurrentLanguage());
			$titleField = tx_srstaticinfo_div::getTCAlabelField($table);
			$fields = $table . '.' . $indexField . ',' . $table . '.' . $titleField;
			if($table == 'static_languages') { 
				$fields .= ',' . $table . '.' . 'lg_iso_2' . ',' . $table . '.' . 'lg_country_iso_2';
				$languageLabels = tx_srstaticinfo_div::initLanguages();
			}

			if ($params['config']['itemsProcFunc_config']['prependHotlist']) {
				$limit = $params['config']['itemsProcFunc_config']['hotlistLimit'];
				$limit = $limit ? $limit : '8';
				$app = $params['config']['itemsProcFunc_config']['hotlistApp'];
				$app = $app ? $app : TYPO3_MODE;

				$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						$fields,
						$table,
						'tx_staticinfotables_hotlist',
						'',	// $foreign_table
						'AND tx_staticinfotables_hotlist.application="'.$GLOBALS['TYPO3_DB']->quoteStr($app,'tx_staticinfotables_hotlist').'"',
						'',
						'tx_staticinfotables_hotlist.sorting DESC',	// $orderBy
						$limit
					);
				$cnt = 0;
				$rows = array();
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					if( $table == 'static_languages') {
						$code = $row['lg_iso_2'].($row['lg_country_iso_2']?'_'.$row['lg_country_iso_2']:'');
						$rows[$row[$indexField]] = $languageLabels[$code][0];
					} else {
						$rows[$row[$indexField]] = $row[$titleField];
					}
					$cnt++;
				}
				if (!isset($params['config']['itemsProcFunc_config']['hotlistSort']) OR $params['config']['itemsProcFunc_config']['hotlistSort']) {
					if( $table != 'static_languages') {
						asort ($rows);
					}
				}
				foreach ($rows as $index => $title)	{
					$params['items'][] = array($title, $index, '');
				}
				if (!isset($params['config']['itemsProcFunc_config']['hotlistSort']) OR $params['config']['itemsProcFunc_config']['hotlistSort']) {
					if( $table == 'static_languages') {
						$compare = create_function('$a,$b', 'return strcoll($a[0],$b[0]);');
						$currentLocale = setlocale(LC_COLLATE, '');
						$collateLocale = tx_srstaticinfo_div::getCollateLocale();
						if( $collateLocale != 'C') { $collateLocale .= '.' . strtoupper($LANG->charSet); }
						setlocale(LC_COLLATE, $collateLocale);
						uasort($params['items'], $compare);
						setlocale(LC_COLLATE, $currentLocale);
					}
				}
				if($cnt AND !$params['config']['itemsProcFunc_config']['hotlistOnly']) {
					$params['items'][] = array('--------------', '', '');
				}
			}

			if(!$params['config']['itemsProcFunc_config']['hotlistOnly']) {
				if( $table == 'static_languages') {
					$params['items'] +=  $languageLabels;
				} else {
					$orderBy = ($TCA[$table]['ctrl']['sortby']) ? $TCA[$table]['ctrl']['sortby'] : $TCA[$table]['ctrl']['default_sortby'];
					$orderBy = $GLOBALS['TYPO3_DB']->stripOrderBy($orderBy);
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, '1'.t3lib_BEfunc::deleteClause($table), '', $orderBy);
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
						$params['items'][] = array($row[$titleField], $row[$indexField], '');
					}
				}
			}
		}
	}

	/*
	 *
	 * Returns the current language as iso-2-alpha language code _ iso-2-alpha country code (when applicable)
	 *
	 * @return	string		'DE', 'EN', 'DK', 'PT_BR'
	 */
	function getCurrentLanguage() {
		global $LANG, $TSFE, $BE_USER;
		// what about that? different than $LANG? I think not.  $langCode = strtolower($GLOBALS['BE_USER']->user['lang']);
		if (is_object($LANG)) {
			$langCodeT3 = $LANG->lang;
		} elseif (is_object($TSFE)) {
			$langCodeT3 = $TSFE->lang;
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_iso_2, lg_country_iso_2', 'static_languages', 'lg_typo3="' . $langCodeT3 . '"');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$lang = strtolower($row['lg_iso_2']).($row['lg_country_iso_2']?'_'.strtoupper($row['lg_country_iso_2']):'');
		}
		return $lang ? $lang : strtolower($langCodeT3);
	}

	/*
	 *
	 * Returns the current language as iso-2-alpha language code _ iso-2-alpha country code (when applicable)
	 *
	 * @return	string		'DE', 'EN', 'DK', 'PT_BR'
	 */
	function getCollateLocale() {
		global $LANG, $TSFE;

		if (is_object($LANG)) {
			$langCodeT3 = $LANG->lang;
		} elseif (is_object($TSFE)) {
			$langCodeT3 = $TSFE->lang;
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_collate_locale', 'static_languages', 'lg_typo3="' . $langCodeT3 . '"');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$locale = $row['lg_collate_locale'];
		}
		return $locale ? $locale : 'C';
	}
}
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_static_info/class.tx_srstaticinfo_div.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_static_info/class.tx_srstaticinfo_div.php"]);}
?>
