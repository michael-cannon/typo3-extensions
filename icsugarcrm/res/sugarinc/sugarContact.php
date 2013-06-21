<?php
/* @version $Id: sugarContact.php,v 1.1.1.1 2010/04/15 10:03:40 peimic.comprock Exp $ */

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

// A struct that exists to pass flags about contact records back and forth
class sugarContactFlags {
    // Obviously, it's a contact
    var $isContact=false;
    // Not obviously, it's a contact that has a lead
    var $hasLead=false;
    // Obviously, it's a lead
    var $isLead=false;
    // Can he handle cases?
    var $isCaser=false;
}

class sugarContact extends sugarCommunication {
    var $module = "Contacts";

    function sugarContact(&$confObj, $portal_user) {
        $this->Initialize($confObj, $portal_user);
    }

    // Gets available Contact fields.
    function getAvailableFields() {
        $fields = $this->_getModuleFields();

        return $fields;
    }

    // This monster will do whatever it can to return a valid contact or lead for a given
    // ID.  It lives on the assumption that the person exists in Sugar.  If he doesn't,
    // for some reason, this class returns a list of false values (2).  If he does,
    // then the class returns list($contact, $flags), where flags tells you if the $contactid
    // passed in refers to the contact directly, a lead the contact is linked to, or
    // a lead directly (in which case $contact will be a lead)
    function getContact($pusername='',$search_fields = array()) {
        $this->createAutosession();

        $returnFlags = new sugarContactFlags;

        //$filter = array('portal_name'=>$pusername);
		$filter = array('name_value_operator'=>array('name'=>'portal_name','value'=>$pusername,'operator'=>'=','value_array'=>''));

        // Get contact record, if it exists
        $result = $this->_getEntryList($filter,'',$search_fields); // [IC] just get what we need
        //echo "<pre>"; echo $this->sugarClientProxy->debug_str; echo "</pre>";
        // if it returned a contact, set flags and return
        if ( is_array($result) ) {
            $returnFlags->isContact = true;

            // This flag is temporarily set and should be corrected
            //$returnFlags->isPortalUser = (bool)$result['portal_active'];

            $this->closeAutosession();

            return array($result, $returnFlags);
        }

        // If we made it all the way down here, something is seriously wrong
        // we'll fail quietly for right now while we figure out what to do about it
        $this->closeAutosession();

        return array(false, false);
    }

	/** [IC] 2006/03/24 - allow select account from drop-down */
    function getAccountByContact($contact_id='',$search_fields = array()) {
        $this->createAutosession();

        //$returnFlags = new sugarAccountFlags;

		$filter = array('name_value_operator'=>array('name'=>'id','value'=>$contact_id,'operator'=>'=','value_array'=>''));

        // Get account record, if it exists
        $result = $this->_getEntryList($filter,'',$search_fields);
        //echo "<pre>"; echo $this->sugarClientProxy->debug_str; echo "</pre>";
        // if it returned a account, set flags and return

        if ( is_array($result) ) {
            $returnFlags->isAccount = true;

            $this->closeAutosession();

            //return array($result, $returnFlags);
            return $result;
        }

        // If we made it all the way down here, something is seriously wrong
        // we'll fail quietly for right now while we figure out what to do about it
        $this->closeAutosession();

       // return array(false, false);
       return false;
    }
    
    // Create a new contact.  $contact should be an array of Contact fields and values.  Not
    // Not every field need be present, default values will be used for any not present.
    // Disabled for now, theoretically works
    /*
    function createNewContact(&$contact) {
        $this->modifyContact($contact);
    }
    */

    // Modify contact.  $contact should be an array of Contact fields and values.  Not
    // every field need be present, default values will be used for any not present.
    // The contactID *must* be present, however.
    // Returns the contactID on success, false on failure
    function modifyContact(&$contact) {
        $tmpContact = $this->bindContact($contact);

        return $this->_setEntry($tmpContact);
    }

    function bindContact($leadArr) {
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

        return $tmpLead;
    }

}


?>

