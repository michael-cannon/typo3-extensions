<?php
/* @version $Id: sugarAccount.php,v 1.1.1.1 2010/04/15 10:03:40 peimic.comprock Exp $ */

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
class sugarAccountFlags {
    // Obviously, it's an account
    var $isAccount=false;
}

class sugarAccount extends sugarCommunication {
    var $module = "Accounts";

    function sugarAccount(&$confObj, $portal_user) {
        $this->Initialize($confObj, $portal_user);
    }

    // Gets available Account fields.
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
    function getAccount($account_id=false) {
        $this->createAutosession();

        $returnFlags = new sugarAccountFlags;

        //$filter = array('id'=>$account_id);
		$filter = array('name_value_operator'=>array('name'=>'id','value'=>$account_id,'operator'=>'=','value_array'=>''));

		$result = array('portal_active'=>false);

        // Get account record, if it exists
        $result = $this->_getEntryList($filter);
        //echo "<pre>"; echo $this->sugarClientProxy->debug_str; echo "</pre>";
        // if it returned a account, set flags and return
        if ( is_array($result) ) {
            $returnFlags->isAccount = true;

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

        $returnFlags = new sugarAccountFlags;

		//$filter = array('name_value_operator'=>array('name'=>'contacts.id','value'=>$contact_id,'operator'=>'=','value_array'=>''));
		$filter = array();
		//$where = " accounts_contacts.contact_id = '$contact_id' ";
        // Get account record, if it exists
        $result = $this->_getEntryList($filter,'',$search_fields);
        //echo "<pre>"; echo $this->sugarClientProxy->debug_str; echo "</pre>";
        // if it returned a account, set flags and return

        if ( is_array($result) ) {
            $returnFlags->isAccount = true;

            $this->closeAutosession();

            return array($result, $returnFlags);
        }

        // If we made it all the way down here, something is seriously wrong
        // we'll fail quietly for right now while we figure out what to do about it
        $this->closeAutosession();

        return array(false, false);
    }


}


?>

