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
class tx_piapappnote_related_notes_save
{
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$that)
	{
		if ($table != 'tx_piapappnote_notes')
			return;
		if (array_key_exists("related_appnotes", $fieldArray))
			$fieldArray["related_appnotes"] = implode(",", $this->orderByNoteId(
				$this->my_explode(",", $fieldArray["related_appnotes"])));
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


	function orderByNoteId($ids)
	{
        if (count($ids) == 0)
            return $ids;
		global $TYPO3_DB;
		$res = $TYPO3_DB->sql_query(sprintf('
			SELECT
				uid
			FROM
				tx_piapappnote_notes
			WHERE
				uid IN (%s)
			ORDER BY
				noteid
			', implode(",", $ids)));
		$newids = array();
		while ($row = $TYPO3_DB->sql_fetch_row($res))
			$newids[] = $row[0];
		return $newids;
	}
}

?>
