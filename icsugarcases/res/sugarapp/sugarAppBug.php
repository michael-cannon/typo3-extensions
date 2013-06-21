<?php
/* @version $Id: sugarAppBug.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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



class sugarAppBug extends sugarApp {
    // The standard object that represents the app data
    var $sugarComm = null;
    // The contact object--needed for some apps
    var $sugarContact = null;
    // The account object--needed for accountID
    /** [IC] 2006/1/12 */
    var $sugarAccount = null;
    // The sugar session to be shared among all communication objects
    var $sugarSessionID = null;
	var $globalDescription = '<h1>Sugar Bug Tracker for Joomla</h1>

	<p>This is the Joomla interface to the bug tracker in SugarSuite.</p>

	';

	var $user_id = "";
	
    function sugarAppBug($request = false, $pusername = '') {

        $this->Initialize($request);
        $this->myusername = $pusername;
        $this->sugarComm = new sugarBug($this->sugarConf, $this->myusername);
    }


    function login() {
        if( $this->sugarComm->createSession() ) {
            $this->sugarSessionID = $this->sugarComm->getSugarSessionID();
			$this->user_id = $this->sugarComm->user_id;

			if($this->task == 'new') {				
				$this->sugarContact->setSugarSessionID($this->sugarSessionID);
			}			
            $this->sugarAuthorizedPortalUser = true;
        } else {
            // do error handling here
            $this->sugarAuthorizedPortalUser = false;
        }
    }

    function logout() {
        $this->sugarSessionID = false;
       // $this->sugarContact->closeSession();
        $this->sugarComm->setSugarSessionID(false);
        $this->contactFlags = false;
        $this->sugarAuthorizedPortalUser = false;
    }

    function create($record) {
        return $this->sugarComm->createNew($record);
    }

    function modify($record) {
        return $this->sugarComm->modify($record);
    }

    function get($recordID) {
        $bug = $this->sugarComm->getOne($recordID);
		$bug['release_name'] = $bug['release'];
        $notes = $this->getNotes($recordID);

        return array($bug,$notes);
    }

    function getNotes($recordID, $selectFields = array()) {
        $sugNote = new sugarNote($this->sugarConf, $this->username);

		$sugNote->setSugarSessionID($this->sugarSessionID);

        return $sugNote->getAllNotes($this->sugarComm->module,$recordID, $selectFields);
    }
		
	function getNoteAttachment($bugID, $noteID) {
		$sugNote = new sugarNote($this->sugarConf, $this->username);
		/** [IC] eggsurplus: what a waste! new sugarNote takes care of the session already*/
		$throwAway = $this->sugarComm->getOne($bugID); //in that case just get one...getAll just doesn't cut it

		$sugNote->setSugarSessionID($this->sugarSessionID);

		//$theNote = $sugNote->getOneNote($noteID);
		$fileContents = $sugNote->getNoteAttachment("Bugs", $bugID, $noteID);

		$retArray = $fileContents;
		return $retArray;
	}

    function createNote($recordID, $note, $files) {
        $sugNote = new sugarNote($this->sugarConf, $this->username);
		/** [IC] eggsurplus: what a waste! new sugarNote takes care of the session already*/
       // $this->sugarComm->getSome(); 
        /** [IC] eggsurplus: should be able to comment this out once the bug is fixed in SugarPortalUsers.php where it'll check on the server side */
		$this->sugarComm->getSome(array('name_value_operator'=>array('name'=>'id','value'=>$recordID,'operator'=>'=','value_array'=>''))); // eggsurplus: why is this even here?....to get sugarSessionID
		
        $sugNote->setSugarSessionID($this->sugarSessionID);
        return $sugNote->createRelatedNote($this->sugarComm->module, $note, $recordID, $files);
    }

    function search($filter=array(),$fields = array(),$row_offset='', $limit='') {
        return $this->sugarComm->getSome($filter,$this->sortBy, $fields, $row_offset,$limit);
    }
}