<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Stanislas Rolland (stanislas.rolland@fructifor.com)
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
/**
 *
 * Class for handling static info tables: countries, and subdivisions, currencies, languages and taxes
 *
 * $Id: class.tx_srstaticinfo_pi1.php,v 1.1.1.1 2010/04/15 10:04:06 peimic.comprock Exp $
 *
 * @author	Stanislas Rolland <stanislas.rolland@fructifor.com>
 * @package TYPO3
 * @subpackage tx_srstaticinfo
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   64: class tx_srstaticinfo_pi1 extends tslib_pibase
 *   82:     function init()
 *  114:     function getStaticInfoName($type='COUNTRIES', $code, $country='', $countrySubdivision='')
 *  200:     function buildStaticInfoSelector($type='COUNTRIES', $name='', $class='', $selected='', $country='', $submit=0)
 *  261:     function initCountries()
 *  285:     function initCountrySubdivisions($country)
 *  308:     function initCurrencies()
 *  331:     function initLanguages()
 *  354:     function optionsConstructor($names, $selected='')
 *  375:     function loadCurrencyInfo($currencyCode)
 *  414:     function formatAmount($amount, $displayCurrencyCode='')
 *  438:     function formatAddress($delim, $streetAddress, $city, $zip, $subdivisionCode='', $countryCode='')
 *  481:     function applyConsumerTaxes($amount, $taxClass=0, $shopCountryCode, $shopCountrySubdivisionCode, $buyerCountryCode, $buyerCountrySubdivisionCode, $EUThreshold=0)
 *  592:     function enableFields($table,$show_hidden=0)
 *
 * TOTAL FUNCTIONS: 13
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_srstaticinfo_pi1 extends tslib_pibase {

	var $cObj;		// The backReference to the mother cObj object set at call time
	var $prefixId = 'tx_srstaticinfo_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_srstaticinfo_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'sr_static_info';		 // The extension key.
	var $conf = array();
	var $currency;		// default currency
	var $currencyInfo = array();
	var $defaultCountry;
	var $defaultCountryZone;
	var $defaultLanguage;
	var $typoVersion;	// Typo3 version
	var $staticInfoCharset = 'iso-8859-1';		// Charset used in the Static Info Tables

	/**
	 * Initializing the class: sets the language based on the TS configuration language property
	 *
	 * @return	boolean		Always returns true
	 */
	function init()	{

		$this->tslib_pibase();
		$this->typoVersion = t3lib_div::int_from_ver($GLOBALS['TYPO_VERSION']);

			//Make sure that labels in locallang.php may be overridden
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId.'.'];
		$this->pi_loadLL();

			//Get the default currency and make sure it does exist in table static_currencies
		$this->currency = (trim($this->conf['currencyCode'])) ? trim($this->conf['currencyCode']) : 'EUR';
		if( !$this->getStaticInfoName('CURRENCIES', $this->currency) ) { $this->currency = 'EUR'; }	//if not, we use the Euro!
		$this->currencyInfo = $this->loadCurrencyInfo($this->currency);

		$this->defaultCountry = trim($this->conf['countryCode']);
		if( !$this->getStaticInfoName('COUNTRIES', $this->defaultCountry) ) {  $this->defaultCountry = ''; }
		$this->defaultCountryZone = trim($this->conf['countryZoneCode']);
		if( !$this->getStaticInfoName('SUBDIVISIONS', $this->defaultCountryZone, $this->defaultCountry) ) {  $this->defaultCountryZone = ''; }
		$this->defaultLanguage = trim($this->conf['languageCode']);
		if( !$this->getStaticInfoName('LANGUAGES', $this->defaultLanguage) ) {  $this->defaultLanguage = ''; }

		return true;
	}

	/**
	 * Getting the name of a country, country subdivision, currency, language, tax
	 *
	 * @param	string		Defines the type of entry of the requested name: 'COUNTRIES', 'SUBDIVISIONS', 'CURRENCIES', 'LANGUAGES', 'TAXES', 'SUBTAXES'
	 * @param	string		The ISO alpha-3 code of a country or currency, or the ISO alpha-2 code of a language or the code of a country subdivision
	 * @param	string		The value of the country code (cn_iso_3) for which a name of type 'SUBDIVISIONS', 'TAXES' or 'SUBTAXES' is requested (meaningful only in these cases)
	 * @param	string		The value of the country subdivision code for which a name of type 'SUB_TAXES' is requested (meaningful only in this case)
	 * @return	string		The name of the object in the language set in init()
	 */
	function getStaticInfoName($type='COUNTRIES', $code, $country='', $countrySubdivision='', $self=0)	{

		switch($type)	{
			case 'COUNTRIES':
				$name = $this->pi_getLL('country_'.strtoupper(trim($code)));
				if( !$name ) {
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_short_en', 'static_countries', 'cn_iso_3="'.strtoupper(trim($code)).'"');
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$name = $GLOBALS['TSFE']->csConv($row['cn_short_en'], $this->staticInfoCharset);
					} else {
						$query = 'SELECT cn_short_en FROM static_countries WHERE cn_iso_3="'.strtoupper(trim($code)).'" ';
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						$row = mysql_fetch_assoc($res);
						$name = $row['cn_short_en'];
					}
				}
				break;
			case 'SUBDIVISIONS':
				$country = (trim($country)) ? trim($country) : $this->defaultCountry;
				$name = $this->pi_getLL('country_zone_'.$country.'_'.trim($code));
				if( !$name ) {
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_name_local', 'static_country_zones', 'zn_code="'.trim($code).'" AND zn_country_iso_3="'.$country.'"');
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$name = $GLOBALS['TSFE']->csConv($row['zn_name_local'], $this->staticInfoCharset);
					} else {
						$query = 'SELECT zn_name_local FROM static_country_zones WHERE zn_code="'.trim($code).'" '.
							'AND zn_country_iso_3="'.$country.'" ';
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						$row = mysql_fetch_assoc($res);
						$name = $row['zn_name_local'];
					}
				}
				break;
			case 'CURRENCIES':
				$name = $this->pi_getLL('currency_'.trim($code));
				if( !$name ) {
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cu_name_en', 'static_currencies', 'cu_iso_3="'.trim($code).'"');
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$name = $GLOBALS['TSFE']->csConv($row['cu_name_en'], $this->staticInfoCharset);
					} else {
						$query = 'SELECT cu_name_en FROM static_currencies WHERE cu_iso_3="'.trim($code).'" ';
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						$row = mysql_fetch_assoc($res);
						$name = $row['cu_name_en'];
					}
				}
				break;
			case 'LANGUAGES':
				if(!$self) {
					$name = $this->pi_getLL('language_'.trim($code));
					if( !$name ) {
						$codeParts = t3lib_div::trimExplode( '_', $code, 1);
						if($this->typoVersion >= 3006000) {
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_name_en', 'static_languages', 'lg_iso_2="'.$codeParts['0'].'" '. ($codeParts['1']?' AND lg_country_iso_2="'.$codeParts['1'].'" ':''));
							$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
							$name = $GLOBALS['TSFE']->csConv($row['lg_name_en'], $this->staticInfoCharset);
						} else {
							$query = 'SELECT lg_name_en FROM static_languages WHERE lg_iso_2="'.$codeParts['0'].'" '.
								($codeParts['1']?' AND lg_country_iso_2="'.$codeParts['1'].'" ':'');
							$res = mysql(TYPO3_db,$query);
							echo mysql_error();
							$row = mysql_fetch_assoc($res);
							$name = $row['lg_name_en'];
						}
					}
				} else {
					$codeParts = t3lib_div::trimExplode( '_', $code, 1);
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_name_en, lg_typo3', 'static_languages', 'lg_iso_2="'.$codeParts['0'].'" '.($codeParts['1']?' AND lg_country_iso_2="'.$codeParts['1'].'" ':''));
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					} else {
						$query = 'SELECT lg_name_en,lg_typo3 FROM static_languages WHERE lg_iso_2="'.$codeParts['0'].'" '.
							($codeParts['1']?' AND lg_country_iso_2="'.$codeParts['1'].'" ':'');
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						$row = mysql_fetch_assoc($res);
					}
					if (isset($this->LOCAL_LANG[$row['lg_typo3']]['language_'.trim($code)]) || $codeParts['0'] == 'EN')       {
						if ($this->typoVersion >= 3006000 ) {
                         				$fromCS = $GLOBALS['TSFE']->csConvObj->charSetArray[$row['lg_typo3']];
                         				$fromCS = $fromCS ? $fromCS: 'iso-8859-1';
                         				$name = $GLOBALS['TSFE']->csConv($this->LOCAL_LANG[$row['lg_typo3']]['language_'.trim($code)], $fromCS);
							$name = ($codeParts['0'] == 'EN' && !$codeParts['1']) ? $GLOBALS['TSFE']->csConv($row['lg_name_en'], $this->staticInfoCharset) : $name;
							$name = $name ? $name : $this->pi_getLL('language_'.trim($code));
							$name = $name ? $name : $GLOBALS['TSFE']->csConv($row['lg_name_en'], $this->staticInfoCharset);
						} else {
                         				$name = $this->LOCAL_LANG[$row['lg_typo3']]['language_'.trim($code)];
							$name = ($codeParts['0'] == 'EN' && !$codeParts['1']) ? $row['lg_name_en'] : $name;
							$name = $name ? $name : $this->pi_getLL('language_'.trim($code));
							$name = $name ? $name : $row['lg_name_en'];
						}
					}
				}
				break;
			case 'TAXES':
				$country = (trim($country)) ? trim($country) : $this->defaultCountry;
				$name = $this->pi_getLL('tax_'.$country.'_'.trim($code));
				if( !$name ) {
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_name_en', 'static_taxes', 'tx_code="'.trim($code).'" AND tx_country_iso_3="'.$country.'"');
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$name = $GLOBALS['TSFE']->csConv($row['tx_name_en'], $this->staticInfoCharset);
					} else {
						$query = 'SELECT tx_name_en FROM static_taxes WHERE tx_code="'.trim($code).'" '.
							'AND tx_country_iso_3="'.$country.'" ';
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						$row = mysql_fetch_assoc($res);
						$name = $row['tx_name_en'];
					}
				}
				break;
			case 'SUBTAXES':
				$country = (trim($country)) ? trim($country) : $this->defaultCountry;
				$countrySubdivision = (trim($countrySubdivision)) ? trim($countrySubdivision) : $this->defaultCountryZone;
				$name = $this->pi_getLL('tax_'.$country.'_'.$countrySubdivision.'_'.trim($code));
				if( !$name ) {
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_name_en', 'static_taxes', 'tx_code="'.trim($code).'" AND tx_country_iso_3="'.$country.'" AND tx_zn_code="'.$countrySubdivision.'"');
						$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
						$name = $GLOBALS['TSFE']->csConv($row['tx_name_en'], $this->staticInfoCharset);
					} else {
						$query = 'SELECT tx_name_en FROM static_taxes WHERE tx_code="'.trim($code).'" '.
							'AND tx_country_iso_3="'.$country.'" '.
							'AND tx_zn_code="'.$countrySubdivision.'" ';
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						$row = mysql_fetch_assoc($res);
						$name = $row['tx_name_en'];
					}
				}
				break;
		}
		return $name;
	}

	/**
	 * Buils a HTML drop-down selector of countries, country subdivisions, currencies or languages
	 *
	 * @param	string		Defines the type of entries to be presented in the drop-down selector: 'COUNTRIES', 'SUBDIVISIONS', 'CURRENCIES' or 'LANGUAGES'
	 * @param	string		A value for the name attribute of the <select> tag
	 * @param	string		A value for the class attribute of the <select> tag
	 * @param	string		The value of the code of the entry to be pre-selected in the drop-down selector: value of cn_iso_3, zn_code, cu_iso_3 or lg_iso_2
	 * @param	string		The value of the country code (cn_iso_3) for which a drop-down selector of type 'SUBDIVISIONS' is requested (meaningful only in this case)
	 * @param	boolean/string		If set to 1, an onchange attribute will be added to the <select> tag for immediate submit of the changed value; if set to other than 1, overrides the onchange script
	 * @return	string		A set of HTML <select> and <option> tags
	 */
	function buildStaticInfoSelector($type='COUNTRIES', $name='', $class='', $selected='', $country='', $submit=0)	{

		$nameAttribute = (trim($name)) ? 'name="'.trim($name).'" ' : '';
		$classAttribute = (trim($class)) ? 'class="'.trim($class).'" ' : '';
		$onchangeAttribute = '';
		if( $submit ) {
			if( $submit == 1 ) {
				$onchangeAttribute = 'onchange="'.$this->conf['onChangeAttribute'].'" ';
			} else {
				$onchangeAttribute = 'onchange="'.$submit.'" ';
			}
		}
		$selector = '<select size="1" '.$nameAttribute.$classAttribute.$onchangeAttribute.'>'.chr(10);

		switch($type)	{
			case 'COUNTRIES':
				$names = $this->initCountries();
				$selected = (trim($selected)) ? trim($selected) : $this->defaultCountry;
				reset($names);
				$selected = ($selected) ? $selected : key($names);
				break;
			case 'SUBDIVISIONS':
				$country = (trim($country)) ? trim($country) : $this->defaultCountry;
				$names = $this->initCountrySubdivisions($country);
				$selected = trim($selected);
				if( $country == $this->defaultCountry ) {
					$selected = ($selected) ? $selected : $this->defaultCountryZone;
				} else {
					reset($names);
					$selected = ($selected) ? $selected : key($names);
				}
				break;
			case 'CURRENCIES':
				$names = $this->initCurrencies();
				$selected = (trim($selected)) ? trim($selected) : $this->currency;
				reset($names);
				$selected = ($selected) ? $selected : key($names);
				break;
			case 'LANGUAGES':
				$names = $this->initLanguages();
				$selected = (trim($selected)) ? trim($selected) : $this->defaultLanguage;
				reset($names);
				$selected = ($selected) ? $selected : key($names);
				break;
		}
		if( count($names) > 0 )	{
			$selector .= $this->optionsConstructor($names, $selected);
			$selector .= '</select>'.chr(10);
		} else {
			$selector = '';
		}
		return $selector;
	}



	/**
	 * Getting all countries into an array
	 * 	where the key is the ISO alpha-3 code of the country
	 * 	and where the value is the name of the country in the language set in init()
	 *
	 * @return	array		An array of names of countries
	 */
	function initCountries()	{

		$names = array();
		if($this->typoVersion >= 3006000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_iso_3, cn_short_en', 'static_countries', '1=1');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$names[$row['cn_iso_3']] = $this->pi_getLL('country_'.$row['cn_iso_3']);
				if( !$names[$row['cn_iso_3']] ) { $names[$row['cn_iso_3']] = $GLOBALS['TSFE']->csConv($row['cn_short_en'], $this->staticInfoCharset); }
			}
		} else {
			$query = 'SELECT cn_iso_3, cn_short_en'.' '.
				'FROM static_countries'.' '.
				'WHERE 1=1'.' ';
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
			while($row = mysql_fetch_assoc($res))	{
				$names[$row['cn_iso_3']] = $this->pi_getLL('country_'.$row['cn_iso_3']);
				if( !$names[$row['cn_iso_3']] ) { $names[$row['cn_iso_3']] = $row['cn_short_en']; }
			}
		}
		uasort($names, 'strcoll');
		return $names;
	}

	/**
	 * Getting all country subdivisions of a given country into an array
	 * 	where the key is the code of the subdivision
	 * 	and where the value is the name of the country subdivision in the language set in init()
	 *
	 * @param	string		The ISO alpha-3 code of a country
	 * @return	array		An array of names of country subdivisions
	 */
	function initCountrySubdivisions($country)	{

		$names = array();
		if($this->typoVersion >= 3006000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('zn_code, zn_name_local', 'static_country_zones', 'zn_country_iso_3="'.$country.'"');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$names[$row['zn_code']] = $this->pi_getLL('country_zone_'.$country.'_'.$row['zn_code']);
				if( !$names[$row['zn_code']] ) { $names[$row['zn_code']] = $GLOBALS['TSFE']->csConv($row['zn_name_local'], $this->staticInfoCharset); }
			}
		} else {
			$query = 'SELECT zn_code, zn_name_local'.' '.
				'FROM static_country_zones'.' '.
				'WHERE zn_country_iso_3="'.$country.'" ';
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
			while($row = mysql_fetch_assoc($res))	{
				$names[$row['zn_code']] = $this->pi_getLL('country_zone_'.$country.'_'.$row['zn_code']);
				if( !$names[$row['zn_code']] ) { $names[$row['zn_code']] = $row['zn_name_local']; }
			}
		}
		uasort($names, 'strcoll');
		return $names;
	}

	/**
	 * Getting all currencies into an array
	 * 	where the key is the ISO alpha-3 code of the currency
	 * 	and where the value are the name of the currency in the language set in init()
	 *
	 * @return	array		An array of names of currencies
	 */
	function initCurrencies()	{

		$names = array();
		if($this->typoVersion >= 3006000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cu_iso_3, cu_name_en', 'static_currencies', '1=1');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$names[$row['cu_iso_3']] = $this->pi_getLL('currency_'.$row['cu_iso_3']);
				if( !$names[$row['cu_iso_3']] ) { $names[$row['cu_iso_3']] = $GLOBALS['TSFE']->csConv($row['cu_name_en'], $this->staticInfoCharset); }
			}
		} else {
			$query = 'SELECT cu_iso_3, cu_name_en'.' '.
				'FROM static_currencies'.' '.
				'WHERE 1=1'.' ';
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
			while($row = mysql_fetch_assoc($res))	{
				$names[$row['cu_iso_3']] = $this->pi_getLL('currency_'.$row['cu_iso_3']);
				if( !$names[$row['cu_iso_3']] ) { $names[$row['cu_iso_3']] = $row['cu_name_en']; }
			}
		}
		uasort($names, 'strcoll');
		return $names;
	}

	/**
	 * Getting all languages into an array
	 * 	where the key is the ISO alpha-2 code of the language
	 * 	and where the value are the name of the language in the language set in init()
	 *
	 * @return	array		An array of names of languages
	 */
	function initLanguages()	{

		$names = array();
		if($this->typoVersion >= 3006000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('lg_iso_2, lg_name_en, lg_country_iso_2', 'static_languages', '1=1');
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$code = $row['lg_iso_2'].($row['lg_country_iso_2']?'_'.$row['lg_country_iso_2']:'');
				$names[$code] = $this->pi_getLL('language_'.$code);
				if( !$names[$code] ) { $names[$code] = $GLOBALS['TSFE']->csConv($row['lg_name_en'], $this->staticInfoCharset); }
			}
		} else {
			$query = 'SELECT lg_iso_2, lg_name_en, lg_country_iso_2'.' '.
				'FROM static_languages'.' '.
				'WHERE 1=1'.' ';
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
			while($row = mysql_fetch_assoc($res))	{
				$code = $row['lg_iso_2'].($row['lg_country_iso_2']?'_'.$row['lg_country_iso_2']:'');
				$names[$code] = $this->pi_getLL('language_'.$code);
				if( !$names[$code] ) { $names[$code] = $row['lg_name_en']; }
			}
		}
		uasort($names, 'strcoll');
		return $names;
	}

	/**
	 * Builds a list of <option> tags
	 *
	 * @param	array		An array where the values will be the texts of an <option> tags and keys will be the values of the tags
	 * @param	string		A pre-selected value: if the value appears as a key, the <option> tag will bear a 'selected' attribute
	 * @return	string		A string of HTML <option> tags
	 */
	function optionsConstructor($names, $selected='') {

		$options = '';
		reset($names);
		while(list($value,$name)=each($names))	{
			$options  .= '<option value="'.$value.'"';
			if( $selected == $value) {
				$options  .= ' selected="selected"';
			}
			$options  .= '>'.$name.'</option>'.chr(10);
		}

		return $options;
	}

	/**
	 * Loading currency display parameters from Static Info Tables
	 *
	 * @param	string		An ISO alpha-3 currency code
	 * @return	array		An array of information regarding the currrency
	 */
	function loadCurrencyInfo($currencyCode)	{

			// Fetching the currency record
	 	$this->currencyInfo['cu_iso_3'] = trim($currencyCode);
	 	$this->currencyInfo['cu_iso_3'] = ($this->currencyInfo['cu_iso_3']) ? $this->currencyInfo['cu_iso_3'] : $this->currency;
		if($this->typoVersion >= 3006000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_currencies', 'cu_iso_3="'.$this->currencyInfo['cu_iso_3'].'"');
				// If not found we fetch the default currency!
			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
		 		$this->currencyInfo['cu_iso_3'] = $this->currency;
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_currencies', 'cu_iso_3="'.$this->currencyInfo['cu_iso_3'].'"');
			}
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		} else {
	 		$query = "SELECT * FROM static_currencies WHERE cu_iso_3='".$this->currencyInfo['cu_iso_3']."'";
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
				// If not found we fetch the default currency!
			if (!mysql_num_rows($res)) {
		 		$this->currencyInfo['cu_iso_3'] = $this->currency;
		 		$query = "SELECT * FROM static_currencies WHERE cu_iso_3='".$this->currencyInfo['cu_iso_3']."'";
		 		$res = mysql(TYPO3_db,$query);
		 		echo mysql_error();
			}
			$row = mysql_fetch_assoc($res);
		}

		$this->currencyInfo['cu_name'] = $this->getStaticInfoName('CURRENCIES', $this->currencyInfo['cu_iso_3']);
		$this->currencyInfo['cu_symbol_left'] = $row['cu_symbol_left'];
		$this->currencyInfo['cu_symbol_right'] = $row['cu_symbol_right'];
		$this->currencyInfo['cu_decimal_digits'] = $row['cu_decimal_digits'];
		$this->currencyInfo['cu_decimal_point'] = $row['cu_decimal_point'];
		$this->currencyInfo['cu_thousands_point'] = $row['cu_thousands_point'];

		return $this->currencyInfo;
	}

	/**
	 * Formatting an amount in the currency loaded by loadCurrencyInfo($currencyCode)
	 *
	 * 								 '' - the currency code is not displayed
	 * 								 'RIGHT' - the code is displayed at the right of the amount
	 * 								 'LEFT' - the code is displayed at the left of the amount
	 *
	 * @param	float		An amount to be displayed in the loaded currency
	 * @param	string		A flag specifying if the the currency code should be displayed:
	 * @return	string		The formated amounted
	 */
	function formatAmount($amount, $displayCurrencyCode='')	{

		$formatedAmount = '';

		if( $displayCurrencyCode == 'LEFT' ) { $formatedAmount .= $this->currencyInfo['cu_iso_3'].chr(32); }
		$formatedAmount .= $this->currencyInfo['cu_symbol_left'];
		$formatedAmount .= number_format($amount, intval($this->currencyInfo['cu_decimal_digits']), $this->currencyInfo['cu_decimal_point'], (($this->currencyInfo['cu_thousands_point'])?$this->currencyInfo['cu_thousands_point']:chr(32)));
		$formatedAmount .= (($this->currencyInfo['cu_symbol_right'])?chr(32):'').$this->currencyInfo['cu_symbol_right'];
		if( $displayCurrencyCode == 'RIGHT' ) { $formatedAmount .= chr(32).$this->currencyInfo['cu_iso_3']; }

		return $formatedAmount;
	}

	/**
	 * Formatting an address in the format specified
	 *
	 * @param	string		A street address
	 * @param	string		A city
	 * @param	string		A country subdivision code (zn_code)
	 * @param	string		A ISO alpha-3 country code (cn_iso_3)
	 * @param	string		A zip code
	 * @param	[type]		$countryCode: ...
	 * @return	string		The formated address using the country address format (cn_address_format)
	 */
	function formatAddress($delim, $streetAddress, $city, $zip, $subdivisionCode='', $countryCode='')	{

		$formatedAddress = '';

			// Get country name
		$countryName = $this->getStaticInfoName('COUNTRIES', (($countryCode)?$countryCode:$this->defaultCountry));
		if( !$countryName )	{ return $formatedAddress; }

			// Get address format
		if($this->typoVersion >= 3006000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_address_format', 'static_countries', 'cn_iso_3="'.trim((($countryCode)?$countryCode:$this->defaultCountry)).'"');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		} else {
			$query = 'SELECT cn_address_format FROM static_countries WHERE cn_iso_3="'.trim((($countryCode)?$countryCode:$this->defaultCountry)).'" ';
			$res = mysql(TYPO3_db,$query);
			echo mysql_error();
			$row = mysql_fetch_assoc($res);
		}
		$addressFormat = $row['cn_address_format'];

			// Get country subdivision name
		$countrySubdivisionName = $this->getStaticInfoName('SUBDIVISIONS', (($subdivisionCode)?$subdivisionCode:$this->defaultCountryZone), (($countryCode)?$countryCode:$this->defaultCountry));

			// Format the address
		$formatedAddress = $this->conf['addressFormat.'][$addressFormat];
		$formatedAddress = str_replace('%street', $streetAddress, $formatedAddress);
		$formatedAddress = str_replace('%city', $city, $formatedAddress);
		$formatedAddress = str_replace('%zip', $zip, $formatedAddress);
		$formatedAddress = str_replace('%countrySubdivisionCode', $subdivisionCode, $formatedAddress);
		$formatedAddress = str_replace('%countrySubdivisionName', $countrySubdivisionName, $formatedAddress);
		$formatedAddress = str_replace('%countryName', strtoupper($countryName), $formatedAddress);
		$formatedAddress = implode($delim, t3lib_div::trimExplode(';', $formatedAddress, 1));

		return $formatedAddress;
	}

	/**
	 * Applying taxes to a given amount
	 *
	 * @param	float		An amount to which taxes should be applied
	 * @param	integer		The class of taxation of the product
	 * @param	string		The ISO alpha-3 code of the country of the selling shop
	 * @param	string		The country subdivision code of the region of the selling shop
	 * @param	string		The ISO alpha-3 code of the country of the buying consumer
	 * @param	string		The country subdivision code of the region of the buying consumer
	 * @param	boolean		Should be set if the shop has sales of goods beyond the regulatory threshold in the buyer's country (when both shop and buyer in EU)
	 * @return	array		An array of 4-plets of applied taxes: ('tx_name','tx_rate','tx_amount','tx_priority')
	 */
	function applyConsumerTaxes($amount, $taxClass=0, $shopCountryCode, $shopCountrySubdivisionCode, $buyerCountryCode, $buyerCountrySubdivisionCode, $EUThreshold=0)	{

		$appliedTaxesIndex = 0;
		$appliedTaxes = array();
		$shopCountryCode = ($shopCountryCode) ? $shopCountryCode : $this->defaultCountry;
		$buyerCountryCode = ($buyerCountryCode) ? $buyerCountryCode : $this->defaultCountry;

	 		// Not taxable!
		if( !$taxClass || !trim($shopCountryCode) || !trim($buyerCountryCode) ) { return $appliedTaxes; }

	 		// Get national taxes
		if( trim($shopCountryCode) == trim($buyerCountryCode) ) {
			if($this->typoVersion >= 3006000) {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_taxes', 'tx_country_iso_3="'.trim($shopCountryCode).'" '.
						'AND tx_scope="1" '.
						'AND ( tx_class="'.$taxClass.'" OR tx_class="3" ) '.
						$this->enableFields('static_taxes'));
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					$appliedTaxes[$appliedTaxesIndex] = array();
					$appliedTaxes[$appliedTaxesIndex]['tx_name'] =  $this->getStaticInfoName('TAXES', $row['tx_code'], trim($shopCountryCode));
					$appliedTaxes[$appliedTaxesIndex]['tx_rate'] = doubleval($row['tx_rate']);
					$appliedTaxes[$appliedTaxesIndex]['tx_priority'] = $row['tx_priority'];
					$appliedTaxesIndex++;
				}
			} else {
	 			$query = 'SELECT * FROM static_taxes WHERE tx_country_iso_3="'.trim($shopCountryCode).'" '.
						'AND tx_scope="1" '.
						'AND ( tx_class="'.$taxClass.'" OR tx_class="3" ) '.
						$this->enableFields('static_taxes');
				$res = mysql(TYPO3_db,$query);
				echo mysql_error();
				while($row = mysql_fetch_assoc($res))	{

					$appliedTaxes[$appliedTaxesIndex] = array();
					$appliedTaxes[$appliedTaxesIndex]['tx_name'] =  $this->getStaticInfoName('TAXES', $row['tx_code'], trim($shopCountryCode));
					$appliedTaxes[$appliedTaxesIndex]['tx_rate'] = doubleval($row['tx_rate']);
					$appliedTaxes[$appliedTaxesIndex]['tx_priority'] = $row['tx_priority'];
					$appliedTaxesIndex++;
				}
			}

	 			// Get state or provincial taxes
			if( trim($shopCountrySubdivisionCode) && trim($buyerCountrySubdivisionCode) &&  trim($shopCountrySubdivisionCode) == trim($buyerCountrySubdivisionCode) ) {
				if($this->typoVersion >= 3006000) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_taxes', 'tx_country_iso_3="'.trim($shopCountryCode).'" '.
						'AND tx_zn_code="'.trim($shopCountrySubdivisionCode).'" '.
						'AND tx_scope="2" '.
						'AND ( tx_class="'.$taxClass.'" OR tx_class="3" ) '.
						$this->enableFields('static_taxes'));
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
						$appliedTaxes[$appliedTaxesIndex] = array();
						$appliedTaxes[$appliedTaxesIndex]['tx_name'] =  $this->getStaticInfoName('SUBTAXES', $row['tx_code'], trim($shopCountryCode), trim($shopCountrySubdivisionCode));
						$appliedTaxes[$appliedTaxesIndex]['tx_rate'] = doubleval($row['tx_rate']);
						$appliedTaxes[$appliedTaxesIndex]['tx_priority'] = $row['tx_priority'];
						$appliedTaxesIndex++;
					}
				} else {
	 				$query = 'SELECT * FROM static_taxes WHERE tx_country_iso_3="'.trim($shopCountryCode).'" '.
						'AND tx_zn_code="'.trim($shopCountrySubdivisionCode).'" '.
						'AND tx_scope="2" '.
						'AND ( tx_class="'.$taxClass.'" OR tx_class="3" ) '.
						$this->enableFields('static_taxes');
					$res = mysql(TYPO3_db,$query);
					echo mysql_error();
					while($row = mysql_fetch_assoc($res))	{
						$appliedTaxes[$appliedTaxesIndex] = array();
						$appliedTaxes[$appliedTaxesIndex]['tx_name'] =  $this->getStaticInfoName('SUBTAXES', $row['tx_code'], trim($shopCountryCode), trim($shopCountrySubdivisionCode));
						$appliedTaxes[$appliedTaxesIndex]['tx_rate'] = doubleval($row['tx_rate']);
						$appliedTaxes[$appliedTaxesIndex]['tx_priority'] = $row['tx_priority'];
						$appliedTaxesIndex++;
					}
				}
			}
		} else	{
	 			// Apply EU Internal Market rules for under threshold sales
			if($this->typoVersion >= 3006000) {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_eu_member', 'static_countries', 'cn_iso_3="'.trim($shopCountryCode).'"');
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			} else {
				$query = 'SELECT cn_eu_member FROM static_countries WHERE cn_iso_3="'.trim($shopCountryCode).'" ';
					$res = mysql(TYPO3_db,$query);
					echo mysql_error();
					$row = mysql_fetch_assoc($res);
			}
			$shop_cn_eu_member = $row['cn_eu_member'];
			if( $shop_cn_eu_member )	{
				if($this->typoVersion >= 3006000) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_eu_member', 'static_countries', 'cn_iso_3="'.trim($buyerCountryCode).'"');
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				} else {
					$query = 'SELECT cn_eu_member FROM static_countries WHERE cn_iso_3="'.trim($buyerCountryCode).'" ';
					$res = mysql(TYPO3_db,$query);
					echo mysql_error();
					$row = mysql_fetch_assoc($res);
				}
				$buyer_cn_eu_member = $row['cn_eu_member'];
				if( $buyer_cn_eu_member )	{
							// Here we apply the rules of the European Union Internal Market
					$taxCountryCode = trim($shopCountryCode);
					if( $taxClass == '1' && $EUThreshold )	{ $taxCountryCode = trim($buyerCountryCode); }
					if($this->typoVersion >= 3006000) {
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_taxes', 'tx_country_iso_3="'.$taxCountryCode.'" AND tx_scope="1" AND ( tx_class="'.$taxClass.'" OR tx_class="3" ) '.$this->enableFields('static_taxes'));
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
							$appliedTaxes[$appliedTaxesIndex] = array();
							$appliedTaxes[$appliedTaxesIndex]['tx_name'] =  $this->getStaticInfoName('TAXES', $row['tx_code'], trim($shopCountryCode));
							$appliedTaxes[$appliedTaxesIndex]['tx_rate'] = doubleval($row['tx_rate']);
							$appliedTaxes[$appliedTaxesIndex]['tx_priority'] = $row['tx_priority'];
							$appliedTaxesIndex++;
						}
					} else {
						$query = 'SELECT * FROM static_taxes WHERE tx_country_iso_3="'.$taxCountryCode.'" '.
							'AND tx_scope="1" '.
							'AND ( tx_class="'.$taxClass.'" OR tx_class="3" ) '.
							$this->enableFields('static_taxes');
						$res = mysql(TYPO3_db,$query);
						echo mysql_error();
						while($row = mysql_fetch_assoc($res))	{
							$appliedTaxes[$appliedTaxesIndex] = array();
							$appliedTaxes[$appliedTaxesIndex]['tx_name'] =  $this->getStaticInfoName('TAXES', $row['tx_code'], trim($shopCountryCode));
							$appliedTaxes[$appliedTaxesIndex]['tx_rate'] = doubleval($row['tx_rate']);
							$appliedTaxes[$appliedTaxesIndex]['tx_priority'] = $row['tx_priority'];
							$appliedTaxesIndex++;
						}
					}
				}
			}
		}

	 		// Apply rates
		if( count($appliedTaxes) )	{
			foreach ($appliedTaxes as $key => $row) {
				$priority[$key] = $row['tx_priority'];
			}
			array_multisort($priority, SORT_ASC, $appliedTaxes);
			$priority = $priority['0'];
			$appliedTaxesAmount = $amount;
			$baseAmount = $appliedTaxesAmount;
			foreach ($appliedTaxes as $key => $row) {
				if( $row['tx_priority'] > $priority ) {
					$baseAmount = $appliedTaxesAmount;
					$priority = $row['tx_priority'];
				}
				$taxedAmount = $row['tx_rate']*$baseAmount;
				$appliedTaxes[$key]['tx_amount'] = round($taxedAmount, ceil(0 - log10($taxedAmount)) + $this->currencyInfo['cu_decimal_digits']);
				$appliedTaxesAmount += $appliedTaxes[$key]['tx_amount'];
			}
		}
		return $appliedTaxes;
	}

	/**
	 * This function is imported from class.tslib_content.php
	 *
	 * Returns a part of a WHERE clause which will filter out records with start/end times or hidden/fe_groups fields set to values that should de-select them according to the current time, preview settings or user login. Definitely a frontend function.
	 * THIS IS A VERY IMPORTANT FUNCTION: Basically you must add the output from this function for EVERY select query you create for selecting records of tables in your own applications - thus they will always be filtered according to the "enablefields" configured in TCA
	 * Simply calls t3lib_pageSelect::enableFields() BUT will send the show_hidden flag along! This means this function will work in conjunction with the preview facilities of the frontend engine/Admin Panel.
	 *
	 * @param	string		The table for which to get the where clause
	 * @param	boolean		If set, then you want NOT to filter out hidden records. Otherwise hidden record are filtered based on the current preview settings.
	 * @return	string		The part of the where clause on the form " AND NOT [fieldname] AND ...". Eg. " AND NOT hidden AND starttime < 123345567"
	 * @see t3lib_pageSelect::enableFields()
	 */
         function enableFields($table,$show_hidden=0)    {
                 return $GLOBALS['TSFE']->sys_page->enableFields($table,$show_hidden?$show_hidden:$GLOBALS['TSFE']->showHiddenRecords);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_static_info/pi1/class.tx_srstaticinfo_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/sr_static_info/pi1/class.tx_srstaticinfo_pi1.php"]);
}

?>
