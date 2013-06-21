<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Kasper (kasper2005@typo3.com)
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
 * Hook for page tag usage stats
 *
 * @author	Kasper <kasper2005@typo3.com>
 * Some copy-paste from Dimitri Ebert <dimitri.ebert@dkd.de>'s work on the "Indexed Search Statistics" module
 */



/**
 * Plugin 'Page Tag Redirection'
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_xdspagetag
 */
class tx_xdspagetag {

	/**
	 * Hook function for "Indexed Search Statistics" module
	 *
	 * @return	string		Content to include:
	 */
	function additionalSearchStat()	{
		global $LANG;

		$addwhere1 = '';										//for all
		$addwhere2 = ' AND tstamp > '.(time()-30*24*60*60);		//for last 30 days
		$addwhere3 = ' AND tstamp > '.(time()-24*60*60);		//for last 24 hours

			// Create statistics for page tags:
		$content.= '
			<h3>Statistics for Page Tags:</h3>
			<table cellpading="5" cellspacing="5" valign=top>
				<tr>
					<td valign=top>'.$this->_listSeveralStats($LANG->getLL("all"),$addwhere1).'</td>
					<td valign=top>'.$this->_listSeveralStats($LANG->getLL("last30days"),$addwhere2).'</td>
					<td valign=top>'.$this->_listSeveralStats($LANG->getLL("last24hours"),$addwhere3).'</td>
				</tr>
			</table>';

			// Create check display for page tags in general including detection of duplicates:
		$content.= $this->_checkForDuplicates();

		return $content;
	}

	/**
	 * Listing stats for page tag hits
	 *
	 * @param	string		Stat label
	 * @param	string		Additional WHERE clause.
	 * @return	string		HTML output
	 */
	function _listSeveralStats($title, $addWhere)	{
		global $LANG;

			// Search for stat result:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'searchstring, count(*) c',
				'tx_pagetag_stat',
				'1'.$addWhere,
				'searchstring',
				'c DESC'
		);

			// Traverse it and put in table:
		$table1='';
		$i=0;
		if( $res ){
			while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res ) ) {
				$i++;
				$table1.='<tr class="bgColor4"><td>'.$i.'.</td><td>'.$row['searchstring'].'</td><td>&nbsp;&nbsp;'.$row['c'].'</td></tr>';
			}
		}

		if( $i==0 ){
			$table1='<tr class="bgColor4"><td callspan="3">'.$LANG->getLL("noresults").'</td></tr>';
		}

		$table1 = '<table class="bgColor5" cellpadding="2" cellspacing="1"><tr class="tableheader"><td colspan="3">'.$title.'</td></tr>'.$table1.'</table>';

			// Return result:
		return $table1;
	}

	/**
	 * List page tags across the page tree and list
	 *
	 * @return	string		HTML content
	 */
	function _checkForDuplicates()	{

			// Look up pages where a page tag is set:
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid,tx_xdspagetag_pagetag',
				'pages',
				'tx_xdspagetag_pagetag!=""'.
					t3lib_BEfunc::deleteClause('pages')
		);

			// Traverse results, split page tags and group the individual words:
		$words = array();
		foreach($rows as $row)	{
			$wordSplit = explode(',', $row['tx_xdspagetag_pagetag']);
			foreach($wordSplit as $word)	{
				if ($word)	$words[$word][] = $row['uid'];
			}
		}

			// Traverse words and create map with word-2-page(s). If more than one page is found for a word it will be marked red.
		$table = '';
		foreach($words as $word => $uids)	{
			foreach($uids as $k => $pageId)	{
				$uids[$k] = t3lib_BEfunc::getRecordPath($pageId, '1=1', 30).' ['.$pageId.']';
			}
			$table.='
			<tr class="bgColor4">
				<td>'.$word.'</td>
				<td'.(count($uids)>=2 ? ' bgcolor="red"' : '').'>'.implode('<br/>', $uids).'</td>
			</tr>';
		}

			// Compile header:
		$table = '<table cellpadding="2" cellspacing="1">
			<tr class="bgColor5 tableheader">
				<td>Page Tag:</td>
				<td>Page(s) where applied:</td>
			</tr>
			'.$table.'</table>';

			// Output:
		return $table;
	}
}

?>