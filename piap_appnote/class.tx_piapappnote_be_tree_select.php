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
class tx_piapappnote_be_tree_select
{
	var $table;
	var $pid;
	var $allowSelfReference = false;
	function main(&$params, &$pObj)
	{
		if ($params["table"] == "tx_piapappnote_notes") {
			$this->table = "tx_piapappnote_" . $params["field"];
			$this->allowSelfReference = true;
		} else {
			$this->table = $params["table"];
		}
		$this->pid = $params["row"]["pid"];
		$this->uid = $params["row"]["uid"];
		$params["items"] = array(array('', 0));
		$this->fillArray($params["items"], 0, 0);
	}

	function fillArray(&$items, $parentid, $depth)
	{
		global $TYPO3_DB;
		$noSelfRef = "";
		if (!$this->allowSelfReference && is_numeric($this->uid))
			$noSelfRef = sprintf("AND uid <> %s", $this->uid);
		$res = $TYPO3_DB->sql_query(sprintf('
			SELECT
				uid,
				title
			FROM
				%s
			WHERE
				CASE WHEN %s > 0 THEN pid = %s ELSE 1 = 1 END
				AND childof = %s
				%s
				AND hidden = 0
				AND deleted = 0
			ORDER BY
				title
			', $this->table, $this->pid, $this->pid, $parentid, $noSelfRef));
		while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
			$items[] = array(sprintf("%s- %s", $this->my_str_repeat("&nbsp;", $depth * 2), $row['title']), $row['uid']);
			$this->fillArray($items, $row['uid'], $depth + 1);
		}
	}

	function my_str_repeat($s, $c)
	{
		if ($c <= 0)
			return "";
		return str_repeat($s, $c);
	}
}

?>
