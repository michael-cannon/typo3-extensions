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



defined('_JEXEC') or
    die('Direct Access to this file is not allowed.');

// This is the config class, and it has this weird name on purpose
class MOScom_sugarcases extends JTable {
    var $id=null;
    var $name=null;
    var $value=null;
    var $module=null;
    var $meta=null;

    function __construct() {
        $database = & JFactory::getDBO();
        parent::__construct('#__sugar_portal_configuration', 'id', $database);
    }

    function setQuery($query) {
        $this->_db->setQuery($query);
    }

    function query() {
        return $this->_db->query();
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
        $database = & JFactory::getDBO();
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
	    $database = & JFactory::getDBO();
		parent::__construct( '#__sugar_portal_contacts', 'id', $database );
	}

	function check() {
		$this->default_con = intval( $this->default_con );
		return true;
	}
}

?>

