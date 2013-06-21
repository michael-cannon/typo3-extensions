<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Georg Ringer <typo3 et ringerge dot org>
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


require_once(PATH_t3lib.'class.t3lib_extobjbase.php');



/**
 * Module extension (addition to function menu) 'Statistic sent news' for the 'rgsendnews' extension.
 *
 * @author    Georg Ringer <typo3 et ringerge dot org>
 * @package    TYPO3
 * @subpackage    tx_rgsendnews
 */
class tx_rgsendnews_modfunc1 extends t3lib_extobjbase {

	/**
	 * Returns the module menu
	 *
	 * @return    Array with menuitems
	 */
	function modMenu()    {
	    global $LANG;
	
	    return Array (
	        "tx_rgsendnews_modfunc1_check" => "1",
	        "tx_rgsendnews_modfunc1_check2" => "21",
	    );
	}
	
	/**
	 * Main method of the module
	 *
	 * @return    HTML
	 */
	function main()    {
	        // Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
	    global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
			$LANG->includeLLFile("EXT:rgsendnews/locallang_db.xml");
			
			$content.= $this->pObj->doc->spacer(5);
			$content.= $this->getStatistics();
			

	    return $content;
	}

	/**
	 * Get the statistic of sent news records
	 *
	 * @return    the statistic
	 */	
	function getStatistics() {
		global $LANG;
		
		$singleNewsUid = intval(t3lib_div::_GET('details'));
		
			// statistic of a single sent news record 
		if ($singleNewsUid!='') {
			$pid = $this->pObj->pageinfo['uid'] ? ' AND pid = '.$this->pObj->pageinfo['uid'] : '';
			$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			    '*',
			    'tx_rgsendnews_stat',
			    'deleted=0 AND hidden=0 '.$pid.' AND newsid='.$singleNewsUid,
			    '',
			    'tstamp DESC',
			    $sqlLimit
			    );
			    
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if ($res) {
				$table.='<table cellpadding="1" cellspacing="1" class="bgColor4" width="100%">
										<tr class="tableheader bgColor5">

											<td>'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.sender").'</td>
											<td>'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.receiver").'</td>
											<td>'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.comment").'</td>
											<td>'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.tstamp").'</td>
										</tr>';
				$i=0;
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$details = '<a href="index.php?&id='.$this->pObj->pageinfo['uid'].'&SET[function]=tx_rgsendnews_modfunc1&details='.$row['newsid'].'">'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.details").'</a>';
					$table.='<tr class="'.($i++ % 2==0 ? 'bgColor3' : 'bgColor4').'">
											<td valign="top">'.$row['sender'].' <i>'.$row['sendmail'].'</i></td>
											<td valign="top">'.$row['receiver'].' <i>'.$row['recmail'].'</i></td>
											<td valign="top">'.$row['comment'].'</td>
											<td valign="top">'.strftime('%d.%m.%y %H:%M',$row['tstamp']).'</td>
										</tr>';
				}
				$table.='</table>';
			}	

				$content.= $table;

	    	$content= $this->pObj->doc->section($LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.title").': '.$this->getNews($singleNewsUid),$content,0,1);
				$content.= '<br /><a href="index.php?&id='.$this->pObj->pageinfo['uid'].'&SET[function]=tx_rgsendnews_modfunc1"> <=== </a>';				


		} else {
			// if there is a valid page, use it as pid 
			$pid = $this->pObj->pageinfo['uid'] ? ' AND pid = '.$this->pObj->pageinfo['uid'] : '';
			$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			    'count(newsid) AS count, newsid',
			    'tx_rgsendnews_stat',
			    'deleted=0 AND hidden=0 '.$pid,
			    'newsid',
			    'count DESC',
			    $sqlLimit
			    );
	        
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if ($res) {
				$table.='<table cellpadding="1" cellspacing="1" class="bgColor4" width="100%">
										<tr class="tableheader bgColor5">
											<td>'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.count").'</td>
											<td>'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.newstitle").'</td>
											<td> </td>
										</tr>';
				$i=0;
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$details = '<a href="index.php?&id='.$this->pObj->pageinfo['uid'].'&SET[function]=tx_rgsendnews_modfunc1&details='.$row['newsid'].'">'.$LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.details").'</a>';
					$table.='<tr class="'.($i++ % 2==0 ? 'bgColor3' : 'bgColor4').'">
											<td valign="top">'.$row['count'].'</td>
											<td valign="top">'.$this->getNews($row['newsid']).' <i>('.$row['newsid'].')</i></td>
											<td valign="top">'.$details.'</td>
										</tr>';
				}
				$table.='</table>';

	    	$content.=$this->pObj->doc->section($LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.title"),$content,0,1);
								
						// if any record has been found
				$content.= ($i>0) ? $table : $LANG->getLL("moduleFunction.tx_rgsendnews_modfunc1.nonewsfound");

			}
		
		}
		
				
		return $content;
	}

	/**
	 * Get the title of a news record by its uid
	 *
	 * @return    news title
	 */	
	
	function getNews($id) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('title','tt_news','uid='.$id);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		return $row['title'];
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsendnews/modfunc1/class.tx_rgsendnews_modfunc1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsendnews/modfunc1/class.tx_rgsendnews_modfunc1.php']);
}

?>