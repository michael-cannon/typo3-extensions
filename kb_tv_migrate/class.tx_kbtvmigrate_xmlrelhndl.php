<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Kraft Bernhard (kraftb@kraftb.at)
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
 * XML Handler for kb_tv_migrate. Derived from TemplaVoila XML Handler
 * and slightly modified
 *
 * $Id: class.tx_kbtvmigrate_xmlrelhndl.php,v 1.1.1.1 2010/04/15 10:03:41 peimic.comprock Exp $
 *
 * @author	Kraft Bernhard <kraftb@kraftb.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */




class tx_kbtvmigrate_xmlrelhndl extends tx_templavoila_xmlrelhndl	{

	/**
	 * Performs the processing part of pasting a record. Modified to accept Traditional TABLE:UID style source references
	 *
	 * @param	string		$pasteCmd: Kind of pasting: 'cut', 'copy', 'ref' or 'localcopy' or 'unlink'
	 * @param	string		$source: String defining the original record. [table]:[uid]:[sheet]:[structure Language]:[FlexForm field name]:[value language]:[index of reference position in field value]/[ref. table]:[ref. uid]. Example: 'pages:78:sDEF:lDEF:field_contentarea:vDEF:0/tt_content:60'. The field name in the table is implicitly 'tx_templavoila_flex'. The definition of the reference element after the slash MUST match the element pointed to by the reference index in the first part. This is a security measure. Modified to take TRAD:TABLE:UID form parameter
	 * @param	string		$destination: Defines the destination where to paste the record (not used when unlinking of course). Syntax is the same as first part of 'source', defining a position in a FlexForm 'tx_templavoila_flex' field.
	 * @return	void
	 */
	function pasteRecord($pasteCmd, $source, $destination)	{

			// Split the source definition into parts:
		$trad = false;
		if (list($table, $uid) = explode('|', $source, 2))	{
			if (is_array($record = t3lib_BEfunc::getRecord($table, $uid)))	{
				$trad = true;
			}
		}
		if (!$trad)	{
			list($sourceStr,$check,$isLocal,$currentPageId) = explode('/',$source);
		}


		if ($sourceStr||$trad)	{
			if (!$trad)	{
				$sourceRefArr = $this->_splitAndValidateReference($sourceStr);
			}

				// The 'source' elements actually point to the source element by its current position in a relation field - the $check variable should match what we find...
			if ($trad||t3lib_div::inList($this->rootTable.',tt_content',$sourceRefArr[0]))	{

				if (!$trad)	{
					// Get source record (where the current item is)
					$sourceRec = t3lib_BEfunc::getRecord($sourceRefArr[0],$sourceRefArr[1],'uid,pid,'.$this->flexFieldIndex[$sourceRefArr[0]]);
				}
				if ($trad||is_array($sourceRec))	{
						// Get reference items of source field:
					if (!$trad)	{
						$sourceItemArray = $this->_getItemArrayFromXML($sourceRec[$this->flexFieldIndex[$sourceRefArr[0]]], $sourceRefArr);
						$itemOnPosition = $sourceItemArray[$sourceRefArr[6]-1];
					} else	{
						$itemOnPosition = Array();
						$itemOnPosition['id'] = $uid;
						$itemOnPosition['table'] = $table;
					}
					$refID = $itemOnPosition['id'];

						// Now, check if the current element actually matches what it should (otherwise some update must have taken place in between...)
					if ((($itemOnPosition['table'].':'.$itemOnPosition['id'] == $check)||$trad) && $itemOnPosition['table']=='tt_content')	{	// None other than tt_content elements are moved around...

						if (($pasteCmd=='unlink')&&!$trad)	{	// Removing the reference:
							$this->_removeReference($sourceItemArray, $sourceRefArr);
						} elseif (($pasteCmd=='delete')&&!$trad)	{	// Removing AND DELETING the reference:
							$this->_removeReference($sourceItemArray, $sourceRefArr);
							$this->_deleteContentElement($itemOnPosition['id']);
						} elseif ($pasteCmd=='localcopy')	{

								// Get the uid of a new tt_content element
							$refID = $this->_getCopyUid($refID,	$currentPageId);

							$this->_changeReference($sourceItemArray, $sourceRefArr, $refID);
						} else	{	// Copy or Cut a reference:

								// Now, find destination (record in which to insert the new reference)
							$destRefArr = $this->_splitAndValidateReference($destination);
							if (t3lib_div::inList($this->rootTable.',tt_content',$destRefArr[0]))	{
									// Destination record:
								$destinationRec = t3lib_BEfunc::getRecord($destRefArr[0],intval($destRefArr[1]),'uid,pid,'.$this->flexFieldIndex[$destRefArr[0]]);
								if (is_array($destinationRec))	{

										// Get reference items of destination field:
									$destItemArray = $this->_getItemArrayFromXML($destinationRec[$this->flexFieldIndex[$destRefArr[0]]], $destRefArr);


										// Depending on the paste command, we do...:
									switch ($pasteCmd)	{
										case 'copy':
												// Get the uid of a new tt_content element
											$refID = $this->_getCopyUid(
														$refID,
														$destRefArr[0]=='pages' ? $destinationRec['uid'] : $destinationRec['pid']
													);

											if ($refID)	{	// Only do copy IF a new element was created.
												$this->_insertReference($destItemArray, $destRefArr, 'tt_content_'.$refID);
											}
										break;
										case 'cut':

												// Find destination PID values (considering if table is 'pages' or not)
											$destPid = $destRefArr[0]=='pages' ? $destinationRec['uid'] : $destinationRec['pid'];	// Find true destination PID

												// Get record of the item we are moving:
											$itemRec = t3lib_BEfunc::getRecord($itemOnPosition['table'], $itemOnPosition['id'], 'uid,pid');

												// If the record we are cutting is LOCAL (on the current page) and if the destination PID is different from the record's pid (otherwise a move is non-sense) we set $destPid:
											if (($isLocal||$trad) && $itemRec['pid']!=$destPid)	{
												$movePid = $destPid;
											} else	{
												$movePid = 0;
											}

											if ($trad)	{
												if ($movePid)	{
													$cmdArray = array();
													$cmdArray['tt_content'][$refID]['move'] = $movePid;
													$tce = t3lib_div::makeInstance('t3lib_TCEmain');
													$tce->start(array(), $cmdArray);
													$tce->process_cmdmap();
												}
												$this->_insertReference($destItemArray, $destRefArr, 'tt_content_'.$refID);
											} else	{
												$this->_moveReference($destItemArray, $destRefArr, $sourceItemArray, $sourceRefArr, 'tt_content', $refID, $movePid);
											}
										break;
										case 'ref':		// Insert a reference (to Content Element from INSIDE the structure somewhere)
											$this->_insertReference($destItemArray, $destRefArr, 'tt_content_'.$refID);
										break;
									}
								}
							}
						}
					}
				}
			}
		} elseif($check && $pasteCmd=='ref')	{		// Insert a reference (to Content Element from outside the structure)

				// Splitting parameters
			$destRefArr = $this->_splitAndValidateReference($destination);
			list($table,$uid) = explode(':', $check);

				// Checking parameters:
			if ($table=='tt_content' && t3lib_div::inList($this->rootTable.',tt_content',$destRefArr[0]))	{
				$destinationRec = t3lib_BEfunc::getRecord($destRefArr[0],$destRefArr[1],'uid,pid,'.$this->flexFieldIndex[$destRefArr[0]]);
				if (is_array($destinationRec))	{

						// Insert the reference:
					$itemArray = $this->_getItemArrayFromXML($destinationRec[$this->flexFieldIndex[$destRefArr[0]]], $destRefArr);
					$this->_insertReference($itemArray, $destRefArr, 'tt_content_'.$uid);
				}
			}
		}
	}


	/**
	 * Returns the record referneced by an XML Path
	 *
	 * @param	string		XML Path
	 * @return	array		Record
	 */
	function getRecord($ref)	{
		list($sourceStr,$check,$isLocal,$currentPageId) = explode('/',$ref);
		$refArr = $this->_splitAndValidateReference($sourceStr);
		$xmlRec = t3lib_BEfunc::getRecord($refArr[0],intval($refArr[1]),'uid,pid,'.$this->flexFieldIndex[$refArr[0]]);
		if (is_array($xmlRec))	{
			$itemArray = $this->_getItemArrayFromXML($xmlRec[$this->flexFieldIndex[$refArr[0]]], $refArr);
			if (!$itemArray) return false;
				// If check is set perform a check that the XML Path is pointing to the correct record
			if ($check)	{
				list($checkTable, $checkUid) = explode(':', $check);
				if (($checkTable != $itemArray[$refArr[6]-1]['table'])||($checkUid != $itemArray[$refArr[6]-1]['id']))	{
					return false;
				}
			}
			$rec = t3lib_BEfunc::getRecord($itemArray[$refArr[6]-1]['table'], $itemArray[$refArr[6]-1]['id']);
			if (!$rec) return false;
			return array($itemArray[$refArr[6]-1]['table'], $itemArray[$refArr[6]-1]['id'], $rec);
		}
		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_tv_migrate/class.tx_kbtvmigrate_xmlrelhndl.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kb_tv_migrate/class.tx_kbtvmigrate_xmlrelhndl.php']);
}

?>
