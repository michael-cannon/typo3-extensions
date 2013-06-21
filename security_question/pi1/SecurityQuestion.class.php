<?php


/**
Wraps some useful functionality regarding security question in one place.
$Id: SecurityQuestion.class.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
@author Jaspreet Singh
*/
class SecurityQuestion {
	
	/**
	Typo3 db pointer. Needs to be set before the class can be used.
	*/
	var $db;
	/** Typo3 content object pointer.
	*/
	var $cObj;
	
	/**Constructor.
	@param Typo3 db pointer
	@param Typo3 content object
	*/
	function SecurityQuestion($db, $cObj) {
		$this->db 		= $db;
		$this->cObj 	= $cObj;
	}

	/**
	Return HTML for the security question selectbox.
	Pass in the value the user has currently selected to have that have the "selected"
	attribute applied to it.
	@param The HTML name of select dropdown box. 
	@param The id (from the db table) of the value which the user has currently selected.
	@author Jaspreet Singh
	@return string. The HTML.
	*/
	function getSecurityQuestionDropdownListHTML($name="FE[fe_users][tx_securityquestion_question]", $userSelectedKey=0) {
		
		if ($this->debug) {
			//phpinfo();
			echo "SecurityQuestion->getSecurityQuestionDropdownListHTML() ";
			echo "\$userSelectedKey $userSelectedKey ";
		}
		
		$rows = $this->getSecurityQuestionRows();
		$html = "\n<select size='1' name='$name'>";
		foreach ( $rows as $row ) {
			$key = $row['uid'];
			$question = $row['question'];
			$selected = ( $key == $userSelectedKey ) 
				? 'selected'
				: ''
			;
			$html .= "\n<option value='$key' $selected>$question</option>"; 
		}
		$html .= "\n</select>";
		return $html;
	}

	/**
	* Retrieves and returns the data rows from the security questions table.
	* Doesn't returned disabled/delete/hidden/etc. rows.
	* @return the data rows.
	* @author Jaspreet Singh
	*/
	function getSecurityQuestionRows()
	{
		if ($this->debug) {
			echo 'getSecurityQuestionRows()<br>';
		}

        $table 		= 'tx_securityquestion_questions';
		$columns 	= 'uid, question';
        $where 		= ' 1=1 ' . $this->cObj->enableFields($table);
		$groupby 	= '';
		$orderby 	= 'sorting';
		$rows 		=  $this->db->exec_SELECTgetRows($columns, $table, $where, $groupby, $orderby  );
		
		if (!is_array($rows)) {
			if ($this->debug) {
				echo ' $rows not an array';
			}
			$rows=array();
		}
		if ($this->debug) {
			echo $columns; echo $where; 
			echo 'Questions rows:';
			foreach ( $rows as $row ) {
				print_r($row);
				//$output .= '';
			}
			reset( $rows );
		}
		return $rows;
	}

}

?>
