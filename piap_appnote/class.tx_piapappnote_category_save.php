<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Markus Bertheau (markus@bcs-it.com)
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
class tx_piapappnote_category_save
{
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$that)
	{
		if ($table != 'tx_piapappnote_notes')
			return;
        if (array_key_exists('categories', $fieldArray))
            $fieldArray['categories'] = implode(",", $this->addParents(
                $this->my_explode(",", $fieldArray['categories']), 'categories'));
        if (array_key_exists('devices', $fieldArray))
            $fieldArray['devices'] = implode(",", $this->addChildren(
                $this->my_explode(",", $fieldArray['devices']), 'devices'));
	}

	/*
	 * Just like explode, just always returns an array
	 */
	function my_explode($sep, $s)
	{
		if (strlen($s) == 0)
			return array();
		return explode($sep, $s);
	}


	function addParents($ids, $what)
	{
		$newids = array();
		global $TYPO3_DB;
		foreach ($ids as $id) {
			$parent = intval($id);
			do {
				$newids[] = $parent;
				$res = $TYPO3_DB->sql_query(sprintf('
					SELECT
						childof
					FROM
						tx_piapappnote_%s
					WHERE
						hidden = 0
						AND deleted = 0
						AND uid = %s
					', $what, $parent));
				list($parent) = $TYPO3_DB->sql_fetch_row($res);
						
			} while ($parent > 0);
		}
		$newids = array_unique($newids);
		return $newids;
	}

    function addChildren($ids, $table)
    {
        $newids = array();
        global $TYPO3_DB;
        foreach ($ids as $id) {
            $newids[] = $id;
            $newids = array_merge($newids, $this->getChildren($id, $table));
        }
        $newids = array_unique($newids);
        return $newids;
    }

    function getChildren($id, $table)
    {
        global $TYPO3_DB;
        $childids = array();
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                uid
            FROM
                tx_piapappnote_%s
            WHERE
                hidden = 0
                AND deleted = 0
                AND childof = %s
            ', $table, $id));
        while ($row = $TYPO3_DB->sql_fetch_row($res)) {
            $childids[] = $row[0];
            $newids = array_merge($childids, $this->getChildren($row[0], $table));
        }
        return $childids;
    }
}

?>
