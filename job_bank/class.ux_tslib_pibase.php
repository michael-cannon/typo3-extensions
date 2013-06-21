<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Peimic.com (http://peimic.com)
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
 * Class 'ux_tslib_fe extends' for the 'cisce_school_search' extension set.
 *
 * @author	Chetan Thapliyal <chetan@srijan.in>
 */

require_once(PATH_tslib."class.tslib_pibase.php");

class ux_tslib_pibase extends tslib_pibase {
	function pi_list_browseresults($showResultCount=1,$tableParams='',$wrapArr=array(), $pointerName = 'pointer', $hscText = TRUE)	{
		// example $wrapArr-array how it could be traversed from an extension
		/* $wrapArr = array(
			'browseBoxWrap' => '<div class="browseBoxWrap">|</div>',
			'showResultsWrap' => '<div class="showResultsWrap">|</div>',
			'browseLinksWrap' => '<div class="browseLinksWrap">|</div>',
			'showResultsNumbersWrap' => '<span class="showResultsNumbersWrap">|</span>',
			'disabledLinkWrap' => '<span class="disabledLinkWrap">|</span>',
			'inactiveLinkWrap' => '<span class="inactiveLinkWrap">|</span>',
			'activeLinkWrap' => '<span class="activeLinkWrap">|</span>'
		); */

			// Initializing variables:
		$pointer = intval($this->piVars[$pointerName]);
		$count = intval($this->internal['res_count']);
		$results_at_a_time = t3lib_div::intInRange($this->internal['results_at_a_time'],1,1000);
		$totalPages = ceil($count/$results_at_a_time);
		$maxPages = t3lib_div::intInRange($this->internal['maxPages'],1,100);
		$pi_isOnlyFields = $this->pi_isOnlyFields($this->pi_isOnlyFields);

			// $showResultCount determines how the results of the pagerowser will be shown.
			// If set to 0: only the result-browser will be shown
			//	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.
			//	 		 2: only the text "Displaying results..." will be shown
		$showResultCount = intval($showResultCount);

			// if this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
		$showFirstLast = $this->internal['showFirstLast'];

			// if this has a value the "previous" button is always visible (will be forced if "showFirstLast" is set)
		$alwaysPrev = $showFirstLast?1:$this->pi_alwaysPrev;

		if (isset($this->internal['pagefloat'])) {
			if (strtoupper($this->internal['pagefloat']) == 'CENTER') {
				$pagefloat = ceil(($maxPages - 1)/2);
			} else {
				// pagefloat set as integer. 0 = left, value >= $this->internal['maxPages'] = right
				$pagefloat = t3lib_div::intInRange($this->internal['pagefloat'],-1,$maxPages-1);
			}
		} else {
			$pagefloat = -1; // pagefloat disabled
		}

			// default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
		$wrapper['disabledLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
		$wrapper['inactiveLinkWrap'] = '<td nowrap="nowrap"><p>|</p></td>';
		$wrapper['activeLinkWrap'] = '<td'.$this->pi_classParam('browsebox-SCell').' nowrap="nowrap"><p>|</p></td>';
		$wrapper['browseLinksWrap'] = trim('<table '.$tableParams).'><tr>|</tr></table>';
		$wrapper['showResultsWrap'] = '<p>|</p>';
		$wrapper['browseBoxWrap'] = '
		<!--
			List browsing box:
		-->
		<div '.$this->pi_classParam('browsebox').'>
			|
		</div>';

			// now overwrite all entries in $wrapper which are also in $wrapArr
		$wrapper = array_merge($wrapper,$wrapArr);

		if ($showResultCount != 2) { //show pagebrowser
			if ($pagefloat > -1) {
				$lastPage = min($totalPages,max($pointer+1 + $pagefloat,$maxPages));
				$firstPage = max(0,$lastPage-$maxPages);
			} else {
				$firstPage = 0;
				$lastPage = t3lib_div::intInRange($totalPages,1,$maxPages);
			}
			$links=array();

				// Make browse-table/links:
			if ($showFirstLast) { // Link to first page
				if ($pointer>0)	{
					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_first','<< First',$hscText),array($pointerName => null),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				} else {
					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_first','<< First',$hscText),$wrapper['disabledLinkWrap']);
				}
			}
			if ($alwaysPrev>=0)	{ // Link to previous page
				if ($pointer>0)	{
					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText),array($pointerName => ($pointer-1?$pointer-1:'')),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				} elseif ($alwaysPrev)	{
					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_prev','< Previous',$hscText),$wrapper['disabledLinkWrap']);
				}
			}
			for($a=$firstPage;$a<$lastPage;$a++)	{ // Links to pages
				if ($this->internal['showRange']) {
					$pageText = (($a*$results_at_a_time)+1).'-'.min($count,(($a+1)*$results_at_a_time));
				} else {
					$pageText = trim($this->pi_getLL('pi_list_browseresults_page','Page',$hscText).' '.($a+1));
				}
				if ($pointer == $a) { // current page
					if ($this->internal['dontLinkActivePage']) {
						$links[] = $this->cObj->wrap($pageText,$wrapper['activeLinkWrap']);
					} else {
						$links[] = $this->cObj->wrap($this->pi_linkTP_keepPIvars($pageText,array($pointerName  => ($a?$a:'')),$pi_isOnlyFields),$wrapper['activeLinkWrap']);
					}
				} else {
					$links[] = $this->cObj->wrap($this->pi_linkTP_keepPIvars($pageText,array($pointerName => ($a?$a:'')),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				}
			}
			if ($pointer<$totalPages-1 || $showFirstLast)	{
				if ($pointer==$totalPages-1) { // Link to next page
					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),$wrapper['disabledLinkWrap']);
				} else {
					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_next','Next >',$hscText),array($pointerName => $pointer+1),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				}
			}
			if ($showFirstLast) { // Link to last page
				if ($pointer<$totalPages-1) {
					$links[]=$this->cObj->wrap($this->pi_linkTP_keepPIvars($this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),array($pointerName => $totalPages-1),$pi_isOnlyFields),$wrapper['inactiveLinkWrap']);
				} else {
					$links[]=$this->cObj->wrap($this->pi_getLL('pi_list_browseresults_last','Last >>',$hscText),$wrapper['disabledLinkWrap']);
				}
			}
			$theLinks = $this->cObj->wrap(implode(chr(10),$links),$wrapper['browseLinksWrap']);
		} else {
			$theLinks = '';
		}

		$pR1 = $pointer*$results_at_a_time+1;
		$pR2 = $pointer*$results_at_a_time+$results_at_a_time;

		if ($showResultCount) {
			if ($wrapper['showResultsNumbersWrap']) {
				// this will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
				// the formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'

				$markerArray['###FROM###'] = $this->cObj->wrap($this->internal['res_count'] > 0 ? $pR1 : 0,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TO###'] = $this->cObj->wrap(min($this->internal['res_count'],$pR2),$wrapper['showResultsNumbersWrap']);
				$markerArray['###OUT_OF###'] = $this->cObj->wrap($this->internal['res_count'],$wrapper['showResultsNumbersWrap']);
				$markerArray['###FROM_TO###'] = $this->cObj->wrap(($this->internal['res_count'] > 0 ? $pR1 : 0).' '.$this->pi_getLL('pi_list_browseresults_to','to').' '.min($this->internal['res_count'],$pR2),$wrapper['showResultsNumbersWrap']);
				$markerArray['###CURRENT_PAGE###'] = $this->cObj->wrap($pointer+1,$wrapper['showResultsNumbersWrap']);
				$markerArray['###TOTAL_PAGES###'] = $this->cObj->wrap($totalPages,$wrapper['showResultsNumbersWrap']);
				// substitute markers
				$resultCountMsg = $this->cObj->substituteMarkerArray($this->pi_getLL('pi_list_browseresults_displays','Displaying results ###FROM### to ###TO### out of ###OUT_OF###'),$markerArray);
			} else {
				// render the resultcount in the "traditional" way using sprintf
				$resultCountMsg = sprintf(
					str_replace('###SPAN_BEGIN###','<span'.$this->pi_classParam('browsebox-strong').'>',$this->pi_getLL('pi_list_browseresults_displays','Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>')),
					$count > 0 ? $pR1 : 0,
					min($count,$pR2),
					$count);
			}
			$resultCountMsg = $this->cObj->wrap($resultCountMsg,$wrapper['showResultsWrap']);
		} else {
			$resultCountMsg = '';
		}

		$sTables = $this->cObj->wrap($resultCountMsg.$theLinks,$wrapper['browseBoxWrap']);

		return $sTables;
	}
}
?>
