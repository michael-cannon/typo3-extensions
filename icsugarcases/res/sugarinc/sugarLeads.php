<?php
/* @version $Id: sugarLeads.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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


/** ensure this file is being included by a parent file */
defined( '_VALID_SUGAR' ) or die( 'Direct Access to this location is not allowed.' );

class sugarLead extends sugarCommunication {
    var $module="Leads";
    function sugarLead(&$confObj, $portal_user='lead') {
        $this->Initialize($confObj, 'lead');
    }
    
    // Gets available Lead fields
    function getAvailableFields() {
        $fields = $this->_getModuleFields();
        
        $this->_showDebug($fields);
        
        return $fields;
    }
    
    // Create a new lead.  $lead should be an array of Contact fields and values.  Not
    // every field need be present, default values will be used for any not present.
    // Returns the id of the new lead
    function createNewLead(&$lead) {
        $tmpLead = $this->bindLead($lead);
        
        return $this->_setEntry($tmpLead);
    }
    
    function bindLead($leadArr) {
        if(empty($this->availableFields))
            $this->_getModuleFields();

        $leadFields = $this->availableFields;
        $tmpLead = array();
        
        foreach($leadFields as $field) {
            if ( array_key_exists($field, $leadArr) ) {
                $tmpLead[$field] = $leadArr[$field];
            } else {
                $tmpLead[$field] = '';
            }
        }
        
        $tmpLead['portal_name'] = $this->portal_user;
        $tmpLead['portal_app'] = $this->appName;
        
        //$this->_showDebug($tmpLead);
        
        return $tmpLead;
    }
        
}


?>

