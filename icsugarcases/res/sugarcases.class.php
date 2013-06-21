<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version
 * 1.1.3 ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied.  See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * The Original Code is: SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/


class JTable extends t3lib_DB {
	/**
	 * Name of the table in the db schema relating to child class
	 *
	 * @var 	string
	 * @access	protected
	 */
	var $_tbl		= '';

	/**
	 * Name of the primary key field in the table
	 *
	 * @var		string
	 * @access	protected
	 */
	var $_tbl_key	= '';

	/**
	 * Database connector
	 *
	 * @var		JDatabase
	 * @access	protected
	 */
	var $_db		= null;

	/**
	 * Object constructor to set table and key field
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @access protected
	 * @param string $table name of the table in the db schema relating to child class
	 * @param string $key name of the primary key field in the table
	 * @param object $db JDatabase object
	 */
	function __construct( $table, $key, &$db )
	{
		$this->_tbl		= $table;
		$this->_tbl_key	= $key;
		$this->_db		=& $db;
	}

	function sql_fetch_assoc( $resource ) {
		$result = parent::sql_fetch_assoc( $resource );

		return ( is_array( $result ) ) ? $result : array();
	}
}


// This is the config class, and it has this weird name on purpose
class MOScom_sugarcases extends JTable {
    var $id=null;
    var $name=null;
    var $value=null;
    var $module=null;
    var $meta=null;

	var $query = null;

    function __construct() {
        $database = $GLOBALS['TYPO3_DB'];
        parent::__construct('tx_icsugarcases_sugar_portal_configuration', 'uid', $database);
    }

    function setQuery($query) {
        $this->query = $query;
    }

    function query() {
        return $this->_db->sql_query($this->query);
    }
}


class mosSugar_Case_Fields extends JTable {
    var $id=null;
    var $field=null;
    var $type=null;
	var $name=null;
	var $show=null;
	var $size=null;
    var $canedit=null;
    var $inlist=null;
    var $default=null;
    var $searchable=null;
    var $parameters=null;
    var $advanced=null;
    var $ordering=null;

    function mosSugar_Case_Fields() {
        $database = $GLOBALS['TYPO3_DB'];
        parent::__construct('#__sugar_case_portal_fields', 'id', $database);
    }

}


class mosSugar_Portal_Contacts extends JTable {
    var $id=null;
    var $contactid=null;
    var $sugarid=null;

	/**
	* @param database A database connector object
	*/
	function mosSugar_Portal_Contacts() {
	    $database = $GLOBALS['TYPO3_DB'];
		parent::__construct( '#__sugar_portal_contacts', 'id', $database );
	}

	function check() {
		$this->default_con = intval( $this->default_con );
		return true;
	}
}

?>
