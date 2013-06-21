<?php
/* @version $Id: sugarNote.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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

// pass $contact to the constructor, you *must* give it a contact, otherwise it will
// behave unpredictably, also needs a pusername, which is the portal_name in Sugar
class sugarNote extends sugarCommunication {
	 var $module = "Notes";

    function sugarNote(&$confObj, $pusername=false) {
        $this->Initialize($confObj, $pusername);
    }

    function getAllNotes($module,$modID, $selectFields = array()) {

         $this->createAutosession();

        $notes = $this->sugarClient->portal_get_related_notes(
                                                $this->sessionID,
                                                $module,
                                                $modID,
                                                $selectFields
                                                ,' date_modified desc'); // [IC] eggsurplus: order_by

        if ($this->_noError($notes,$notes->error)) {
        	 $results = array();
            if(!empty($notes->entry_list)){
	            foreach($notes->entry_list as $entry){
	            	$results[] = $this->_convertToDict($entry);
	            }
            }
            return $results;
        }
        $this->_showErrors();
        $this->closeAutosession();

        return false;
    }

    function getAvailableFields() {
        return $this->_getModuleFields();
    }

    // This is the search functino and it doesn't work yet
    function getSomeNotes($fieldstosearch) {
        $this->createAutosession();

        // format the query first
        $tempFields = array();

        // get all notes, we'll use this later to filter our search results

        // todo: check to see if we need this anymore, it's used to prevent a note
        // user from seeing notes that aren't his or his account's.  Formerly this
        // check wasn't handled on the server side, but it's supposed to be handled
        // there now.  Code here left so as not to break anything


        $orderby = '';


        $result = $this->_getEntryList($fieldstosearch, $orderby, array());

        //echo "<pre>"; var_dump($result); echo "</pre>";

        if( is_array($result) ) {
            // now we'll filter the results to make sure we only show notes that this
            // user is authorized to view

            return $result;
        } else {
            return array();
        }
    }

    function getOneNote($noteID) {
        $this->createAutosession();

        $thenote = $this->_getEntry($noteID);

		if($this->_noError($result,$result->error)) {
            $this->closeAutosession();

            return $thenote[0];
        }

		$this->closeAutosession();
		return false;
    }

    // returns false on failure, true on success
    function createRelatedNote($module, $note, $modID, $files=false){
    	$result = $this->createNote($note);

		if ($this->_noError($result,$result->error)) {
    		if( $this->relateNote($result['id'], $module, $modID) ) {
				// The next two statements handle files
				if( isset($files) && $files != false) {
					if(isset($files['attachment']) && $files['attachment']['size'] > 0) {
						$fp = fopen($files['attachment']['tmp_name'], 'rb');
						$file = base64_encode(fread($fp, filesize($files['attachment']['tmp_name'])));
						fclose($fp);
						$this->addNoteAttachment($result['id'], $files['attachment']['name'],$file);
					}
				}
			}
    	}
    	$this->_showErrors();
    	return false;
    }


    function relateNote($noteID, $module, $modID){
    	$this->createAutoSession();
    	$result = $this->sugarClient->portal_relate_note_to_module($this->sessionID, $noteID, $module, $modID);

		if ($this->_noError($result,$result->error)) {
    	 	return true;
    	}
    	$this->_showErrors();
    	        
    	return false;
    }

    function createNote($note) {
        return $this->modifyNote($note);
    }

    function modifyNote($note) {
        // Let's "purify" the note data
        $tmpArray = $this->_bind($note);

        $tmpArray = $this->prepareString($tmpArray);

		$result = $this->_setEntry($tmpArray);

        return $result;
    }

     function getNoteAttachment($module, $moduleID, $noteID){
     	/** [IC] eggsurplus: why with the throwAways? do on server side all in one call...once bug is fixed in SugarPortalUsers.php */
     	/** [IC] eggsurplus: to set note id in session viewable array */
	 	$throwAway = $this->sugarClient->portal_get_related_notes($this->sessionID, $module, $moduleID, array(),''); // [IC] eggsurplus: order_by	
    	
		/** [IC] eggsurplus: should be able to comment this out once the bug is fixed in SugarPortalUsers.php where it'll check on the server side */
    	//$throwAway = $this->getOneNote($noteID); //in that case just get one...getAll just doesn't cut it
    	/** [IC] added module */
    	$result = $this->sugarClient->portal_get_note_attachment($this->sessionID, $noteID, $module, $moduleID);
		 
		 if ($this->_noError($result,$result->error)) {
    	 	return $result->note_attachment;
    	 }
    	 return false;
    }

    function addNoteAttachment($noteID, $filename, &$file){
    	$this->createAutoSession();
		/** [IC] eggsurplus: should be able to comment this out once the bug is fixed in SugarPortalUsers.php where it'll check on the server side */
        $throwAway = $this->getOneNote($noteID); //in that case just get one...getAll just doesn't cut it
        
    	$result = $this->sugarClient->portal_set_note_attachment($this->sessionID, array('id'=>$noteID, 'filename'=>$filename, 'file'=> $file));
    	 if ($this->_noError($result,$result->error)) {
    	 	return true;
    	 }
    	 return false;
    }

}

?>