<?php
defined('_JEXEC') or die();


/** ensure this file is being included by a parent file */
defined( '_VALID_SUGAR' ) or die( 'Direct Access to this location is not allowed.' );
/**  
class mosSugar_Configuration extends JTable {
    var $id=null;
    var $name=null;
    var $value=null;
    
    function mosSugar_Configuration() {
        $database = & JFactory::getDBO();
        parent::__construct('#__sugar_portal_configuration', 'id', $database);
    }

}

class mosSugar_Components extends JTable {
    var $id=null;
    var $name=null;
    var $tables=null;
    var $description=null;
    
    function mosSugar_Components() {
        $database = & JFactory::getDBO();
        parent::__construct('#__sugar_portal_components', 'id', $database);
    }
}
*/
class JTableSugar_case_portal_fields extends JTable {
    var $id			= null;
	var $ordering	= null;
    
    function JTableSugar_case_portal_fields(&$db) {
        parent::__construct('#__sugar_case_portal_fields', 'id', $db);
    }
}

?>




