<?php
	/***************************************************************
	*  Copyright notice
	*
	*  (c) 2004 Zach Davis (zach@crito.org)
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
	* @author Zach Davis <zach@crito.org>
	*/

class ext_update  {

    /**
     * Main function, returning the HTML content of the module
     * 
     * @return    string        HTML
     */
    function main()    {
			
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_chcforum_conference','');
			
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				unset($data_arr);
				if ($row['auth_forumgroup_rw']) {
					$data_arr['auth_forumgroup_r'] = $row['auth_forumgroup_rw'];
					$data_arr['auth_forumgroup_w'] = $row['auth_forumgroup_rw'];
					$data_arr['auth_forumgroup_rw'] = '';
					$content.= 'updating permissions for conference: <strong>'.$row['conference_name'].'</strong><br />';
					$where = 'uid = '.$row['uid'];
					$update_exec = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_chcforum_conference',$where,$data_arr);
				}
			}
			$content.= '<br />Update complete!';
			return $content;	
    }
    
    /**
     * Checks how many rows are found and returns true if there are any
     * 
     * @return    boolean        
     */
    function access()    {

			// make sure the conference table exists (it won't exist if the extension
			// hasn't been installed yet, for example)
			$tables = $GLOBALS['TYPO3_DB']->admin_get_tables();
			if (!in_array('tx_chcforum_conference',$tables)) return false;
			
			// see if any rows need updating
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_chcforum_conference','');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)) {
				if ($row['auth_forumgroup_rw']) {
					return true;
				}
			}
			return false;
		}
}

?>