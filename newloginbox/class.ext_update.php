<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2002-2004 Kasper Sk�rh�j (kasper@typo3.com)
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   52: class ext_update  
 *   59:     function main()	
 *  174:     function access()	
 *  188:     function query($fields)	
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */







/**
 * Class for updating newloginbox from the previous version under TYPO3 3.5.0
 * 
 * @author	 Kasper Sk�rh�j <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_newloginbox
 */
class ext_update  {

	/**
	 * Main function, returning the HTML content of the module
	 * 
	 * @return	string		HTML
	 */
	function main()	{
		$query = $this->query('*');
		
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$query);
		echo $GLOBALS['TYPO3_DB']->sql_error();
		
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		
		if (!t3lib_div::GPvar('do_update'))	{
			$onClick = "document.location='".t3lib_div::linkThisScript(array('do_update'=>1))."'; return false;";
			
			return 'There are '.$count.' rows in "tt_content" to update. Do you want to perform the action now?
			
				<form action=""><input type="submit" value="DO IT" onclick="'.htmlspecialchars($onClick).'"></form>
			';
		} else {
			while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				
				$xml = trim('
<T3FlexForms>
	<meta>
		<dataStructureUid></dataStructureUid>
		<currentSheetId>s_logout</currentSheetId>
	</meta>
	<data>
		<sDEF>
			<lDEF>
				<show_forgot_password>
					<vDEF>'.$row['tx_newloginbox_show_forgot_password'].'</vDEF>
				</show_forgot_password>
			</lDEF>
		</sDEF>
		<s_welcome>
			<lDEF>
				<header>
					<vDEF>'.$row['tx_newloginbox_header_welcome'].'</vDEF>
				</header>
				<message>
					<vDEF>'.$row['tx_newloginbox_msg_welcome'].'</vDEF>
				</message>
			</lDEF>
		</s_welcome>
		<s_success>
			<lDEF>
				<header>
					<vDEF>'.$row['tx_newloginbox_header_success'].'</vDEF>
				</header>
				<message>
					<vDEF>'.$row['tx_newloginbox_msg_success'].'</vDEF>
				</message>
			</lDEF>
		</s_success>
		<s_error>
			<lDEF>
				<header>
					<vDEF>'.$row['tx_newloginbox_header_error'].'</vDEF>
				</header>
				<message>
					<vDEF>'.$row['tx_newloginbox_msg_error'].'</vDEF>
				</message>
			</lDEF>
		</s_error>
		<s_status>
			<lDEF>
				<header>
					<vDEF>'.$row['tx_newloginbox_header_status'].'</vDEF>
				</header>
				<message>
					<vDEF>'.$row['tx_newloginbox_msg_status'].'</vDEF>
				</message>
			</lDEF>
		</s_status>
		<s_logout>
			<lDEF>
				<header>
					<vDEF>'.$row['tx_newloginbox_header_logout'].'</vDEF>
				</header>
				<message>
					<vDEF>'.$row['tx_newloginbox_msg_logout'].'</vDEF>
				</message>
			</lDEF>
		</s_logout>
	</data>
</T3FlexForms>
				');
	
				
				
				$updateRecord=array();
				$updateRecord['tx_newloginbox_show_forgot_password']=0;
				$updateRecord['tx_newloginbox_header_welcome']='';
				$updateRecord['tx_newloginbox_msg_welcome']='';
				$updateRecord['tx_newloginbox_header_success']='';
				$updateRecord['tx_newloginbox_msg_success']='';
				$updateRecord['tx_newloginbox_header_error']='';
				$updateRecord['tx_newloginbox_msg_error']='';
				$updateRecord['tx_newloginbox_header_status']='';
				$updateRecord['tx_newloginbox_msg_status']='';
				$updateRecord['tx_newloginbox_header_logout']='';
				$updateRecord['tx_newloginbox_msg_logout']='';
				$updateRecord['pi_flexform']=$xml;
	
				$updateQ = t3lib_BEfunc::DBcompileUpdate('tt_content','uid='.intval($row['uid']),$updateRecord);
				$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db,$updateQ);
				
				return $count.' ROW updated.';
			}
		}
	}
	
	/**
	 * Checks how many rows are found and returns true if there are any
	 * 
	 * @return	boolean		
	 */
	function access()	{
		$res = @$GLOBALS['TYPO3_DB']->sql(TYPO3_db, $this->query('count(*)'));
		if ($GLOBALS['TYPO3_DB']->sql_error())		return false;	// In this case the old database fields has probably been removed and hence we don't need the update module for anything.
			
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		return $row[0] ? 1 : 0;
	}
	
	/**
	 * Creates 	query finding all tt_content elements of plugin/newloginbox type which has any of the message/header fields set.
	 * 
	 * @param	string		Select fields, eg. "*" or "tx_newloginbox_show_forgot_password,tx_newloginbox_header_welcome" or "count(*)"
	 * @return	string		Full query
	 */
	function query($fields)	{
		$query = 'SELECT '.$fields.' FROM tt_content WHERE 
				CType="list" AND
				list_type="newloginbox_pi1" AND
				
				(
					tx_newloginbox_show_forgot_password OR 
					tx_newloginbox_header_welcome OR 
					tx_newloginbox_msg_welcome OR 
					tx_newloginbox_header_success OR 
					tx_newloginbox_msg_success OR 
					tx_newloginbox_header_error OR 
					tx_newloginbox_msg_error OR 
					tx_newloginbox_header_status OR 
					tx_newloginbox_msg_status OR 
					tx_newloginbox_header_logout OR 
					tx_newloginbox_msg_logout
				)
				';	
		return $query;
	}
}
?>
