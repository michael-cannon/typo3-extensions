<?php

//from PEAR. For db manipulation.
require_once 'DB/Query.php'; 
require_once 'dbinfo.php'; 

/**
A class to help with manipulations regarding many-to-many relationships.

@author Jaspreet Singh
$Id: AssociationHelper.class.php,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $
*/
class AssociationHelper {

	/** Name of the local table in a many to many relationship */
	var $localTable; 
	/** Name of the foreign table in a many to many relationship */
	var $foreignTable; 
	/** Name of the linking table in a many to many relationship */
	var $linkTable;
	/** Name of the field in the linking table that is the primary key in local table */
	var $linkTableLocalField;
	/** Name of the field in the foreign table that is the primary key in the 
		foreign table.
	*/
	var $linkTableForeignField;

	/** Database access info */
	var $dsn; //set in the constructor. 

	/** Whether or not to print debugging info */
	var $debug = false;

	/** A DB_QueryTool to wrap access to the linkTable. */
	var $linkTableWrapper;
	
	/**Constructor.
	
	@param string the local table
	@param string the foreign table
	@param string the many-to-many linking table
	@param string the field in the linking table that is the primary key in local
		table.  If the value is the Typo3 default uid_local, this can be omitted.
	@param string the field in the foreign table that is the primary key in the 
		foreign table.
		If the value is the Typo3 default uid_foreign, this can be omitted.
	*/
	function AssociationHelper (
		$localTable, 
		$foreignTable, 
		$linkTable,
		$linkTableLocalField = "uid_local",
		$linkTableForeignField = "uid_foreign"
		)
	{
		assert ( !empty($localTable) );
		assert ( !empty($foreignTable) );
		assert ( !empty($linkTable) );
		if ($this->debug) { echo "AssociationHelper\n"; }
		
		//cache parameters as class members
		$this->localTable = $localTable;
		$this->foreignTable = $foreignTable;
		$this->linkTable = $linkTable;
		$this->linkTableLocalField = $linkTableLocalField;
		$this->linkTableForeignField = $linkTableForeignField;
		
		//initialize other class members
		$this->dsn = $GLOBALS['bpmdsn'];
		if ($this->debug) { echo "dsn: $this->dsn \n"; };
		
		$this->linkTableWrapper = new DB_QueryTool_Query($this->dsn);
		assert( !is_null($this->linkTableWrapper) );
		$this->linkTableWrapper->table = $this->linkTable;
		//Don't set this, as just this single field isn't the PK.
		//$this->linkTableWrapper->primaryCol = $this->linkTableLocalField ;
	}
	
	/**
	Get the associations, either all of them, or for a specific local table ID.
	*/
	function getAssociations($id=null) {
		
		if (is_null($id)) {
			return $this->getAssociationsAll();
		} else {
			return $this->getAssociationsForID($id);
		}
		
	}

	/**
	Get the IDs of the of the foreign table rows associated with the passed local ID.
	@return An array of IDs. The IDs are numbers represented as strings.
	*/
	function getAssociationsForID($id) {
		
		if ($this->debug) { echo "\nfunction getAssociationsForID($id)"; }
		
		assert ( !empty($id) );
		//assert ( is_numeric($id) );  it could be a textid.
		
		$wrapper = $this->linkTableWrapper;
		
		$wrapper->reset();
		$wrapper->setSelect("$this->linkTableForeignField");
		$wrapper->setWhere("$this->linkTableLocalField = '$id'");
		
		if ($this->debug) { echo "\n" . $wrapper->getQueryString() .  "\n"; }
		$result = $wrapper->getCol();
		if ($this->debug)  { echo "\nresults: "; var_dump($result); }
		
		if (!is_array($result)) {
			$result = array();
		}
		return $result;
	}

	/**
	Returns all of the associations between local table IDs and foreign table
	IDs.
	@return An array of arrays, similar to 
	<code>        
		$expected = array( 
			array( 1, array(7, 3, 4)),
			array( 2, array(2, 4, 6)),
			array( 3, array(6, 2, 4)),
			array( 4, array(8, 5, 1)),
			);
	</code>
	*/
	function getAssociationsAll() {
		
		if ($this->debug) { echo "\nfunction getAssociationsAll()"; }
		
		$wrapper = $this->linkTableWrapper;
		
		$wrapper->reset();
		$wrapper->setSelect("$this->linkTableLocalField, $this->linkTableForeignField");
		
		if ($this->debug) { echo "\n" . $wrapper->getQueryString() .  "\n"; }
		$queryResult = $wrapper->getAll();
		if ($this->debug) { var_dump($queryResult); var_export($queryResult); }
		
		//fold items with a similar ID into arrays
		$processedResult = $this->tableToMultiDimensionalArray($queryResult);
		
		return $processedResult;
	}
	
	/**
	Transforms the passed array (which is in the form of a normal database table)
	into a multidimensional array of the following sort.
	
	@param array of arrays. For example:
		<code>
		Array
		(
			[0] => Array
				(
					[uid_local] => 1
					[uid_foreign] => 3
				)
		
			[1] => Array
				(
					[uid_local] => 1
					[uid_foreign] => 4
				)
		
			[2] => Array
				(
					[uid_local] => 1
					[uid_foreign] => 7
				)
		
			[3] => Array
				(
					[uid_local] => 2
					[uid_foreign] => 2
				)
		
			[4] => Array
				(
					[uid_local] => 2
					[uid_foreign] => 4
				)
		
			[5] => Array
				(
					[uid_local] => 2
					[uid_foreign] => 7
				)
		
		)
		</code>
	@return the passed array, transformed as follows:
		<code>
		Array
		(
			[1] => Array
				(
					[0] => 3
					[1] => 4
					[2] => 7
				)
		
			[2] => Array
				(
					[0] => 2
					[1] => 4
					[2] => 7
				)
		
		)
		</code>
		It can be accessed like this:
		$item = $result[1][1]; // == "a"
	*/
	function tableToMultiDimensionalArray($table) {
		
		if ($this->debug) { echo "\nfunction tableToMultiDimensionalArray($table)"; }
		
		$result = array();
		foreach($table as $row) {
			//$result[$row[0]][] = $row[1];
			//echo ' ' . $row[0] . ', ';
			//$key = 
			
			list($key, $value) = each($row);
			$resultKey = $value;
			
			list($key, $value) = each($row);
			$resultValue = $value;
			
			$result[$resultKey][] = $resultValue;
			
		}
		if ($this->debug) { var_dump($result); var_export($result); }
		
		return $result;
		
	}

	
	/**
	Add an association (many-to-many relationship) between the specified local
	and foreign table IDs.
	@access public
	*/
	function addAssociation($localID, $foreignID) {
		if (!$this->existsAssociation($localID, $foreignID)) {
			$this->_addAssociation($localID, $foreignID);
		}
	}

	/**
	@access private
	*/
	function _addAssociation($localID, $foreignID) {
		
		if ($this->debug) { echo "function _addAssociation($localID, $foreignID) {"; }
		
		assert( !empty($localID) );
		assert( !empty($foreignID) );
		
		$wrapper = $this->linkTableWrapper;
		
		$wrapper->reset();
		$newRowData = array( 
			$this->linkTableLocalField 		=> "$localID", 
			$this->linkTableForeignField 	=> "$foreignID",
		);
		if ($this->debug) { print_r($newRowData); }
		$wrapper->add( $newRowData );
		
	}
	
	/**
	Whether or not there is a relationship between the specified local and foreign
	table IDs.
	@return boolean
	*/
	function existsAssociation($localID, $foreignID) {

		assert( !empty($localID) );
		assert( !empty($foreignID) );

		$associations = $this->getAssociationsForID($localID);
		if (is_array($associations)) {
			return in_array($foreignID, $associations);
		} else {
			return false;
		}
	}
}
?>
