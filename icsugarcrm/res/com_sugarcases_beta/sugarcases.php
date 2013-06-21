<?php
/* @version $Id: sugarcases.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

$grantaccess = "";


// I don't think we need this, I don't know where it came from.  I'm leaving it here for reference in case we decide to use
// it.  It doesn't hurt anything.
// Editor usertype check
//$is_editor = (strtolower($my->usertype) == 'author' || strtolower($my->usertype) == 'editor' || strtolower($my->usertype) == 'administrator' || strtolower($my->usertype) == 'super administrator' );
//$access = new stdClass();
//$access->canEdit = $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'all' );
//$access->canEditOwn = $acl->acl_check( 'action', 'edit', 'users', $my->usertype, 'content', 'own' );
require_once ( $mainframe->getPath( 'class' ) );

define('_MYNAMEIS', 'com_sugarcases');

$basePath = JPATH_SITE . "/components/" . _MYNAMEIS . "/";
require_once( $basePath . "sugarportal.inc.php" );
include_once( 'sugarinc/sugarUtils.php');

// The html class should be derived from a sugarapp class containing useful methods and configuration
// so it has to be included after the sugar includes
require_once ( $mainframe->getPath( 'front_html' ) );

global $task;

$task = JRequest::getVar('task' );
$run_search = JRequest::getVar('run_search' );
$caseID = JRequest::getVar('caseID' );
$Itemid = JRequest::getVar('Itemid' );
/** [IC] 2006/05/22 - pagination */
$limit 		= intval( JRequest::getVar('limit', '10' ) );
$limitstart = intval( JRequest::getVar('limitstart', 0 ) );
$searchType = JRequest::getVar('searchType', 'searchable' );

$my			= & JFactory::getUser();
// We'll do our Joomla permissions check here so we can make the rest of the code sugar-centric
if ($my->id == 0) {
    //mosNotAuth();
    JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
    return;
}
//echo "new sugar app<br/>";

// Joomla says we can be here, now we make the application object and keep going
$caseApp = new sugarAppCase($_REQUEST, $my->username, $task);
//echo "new sugar app done<br/>";

$caseApp->login(); //timing out here!!!!!!!!!!!!!
//echo "logged in<br/>";


//if( $caseApp->$sugarError )
//    $task = "error";

// Do sugar permission check
if( ! $caseApp->sugarAuthorizedPortalUser ) {
   // mosNotAuth();
    JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
    return;
}

// Now we'll finish configuring the application object
$neededFormFields = array('option'=>_MYNAMEIS,
                          'Itemid'=>$Itemid);


$caseApp->setNeededFormFields($neededFormFields);
$caseApp->setSessionStartCallback('startSugarSession');
$caseApp->setSessionStopCallback('stopSugarSession');
// Session management, logs into the sugar soap server and gets the session ID
$caseApp->startSession();
//$caseApp->Initialize();

$presentation = new HTML_sugar_portal();

// You *must* configure the presentation layer like this, it's how you get the configurable options
// put in properly
$caseApp->configurePresentationLayer($presentation);



switch( $task ) {
    case "new":

		if( $grantaccess == 'auth_readonly' ) {
			//mosNotAuth();
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			return;
		}
        $columns = getColumnData($caseApp);
		
		/** [IC] 2006/03/24 - allow user to select which account to create case for */
		/**
        foreach($columns['data'] as &$column) {
        	if($column['name'] == 'account_name') { //account_id,account_name
				for($i = 0; $i < sizeof($caseApp->account); $i++) {
					
            		$tempArray = nameValuePairToSimpleArray($caseApp->account[$i]['name_value_list']);
					$column['options'][$i]['value'] = $tempArray['name']; //they have name and value flip-flopped...tards
					$column['options'][$i]['name'] = $tempArray['id'];
				}
        	}
        }
        */

        $presentation->RenderNewForm($columns);
        break;
    case "search":
    	
        // First get the columns that are wanted.  We could use a simple query for this,
        // but let's keep all the querying in one place, shall we?
        $columnData = getColumnData($caseApp);
        $columns = $columnData['selected'];
        $searchcolumns = array();
        $numberSearch == false;
        foreach($columns as $column) {
            if($column[$searchType] == 1) {
                if( isset( $_REQUEST[$column['field']] ) ) {
                	// [IC] eggsurplus: if an integer is passed in the search box on the top assume they want to search by the number...not name
                	if(isset($_REQUEST["quicksearch"]) && $column['field'] == "name" && is_numeric($_REQUEST[$column['field']])) {
                    	$searchcolumns["case_number"] = $_REQUEST[$column['field']];
                    	$searchcolumns[$column['field']] = '';
        				$numberSearch == true;
                	} else {
                    	$searchcolumns[$column['field']] = $_REQUEST[$column['field']];
                    }
                } else if(!isset($_REQUEST["quicksearch"]) || $numberSearch == false || $column['field'] != "case_number") { // [IC] just in case case_number is called after..prevent overwrite
                    $searchcolumns[$column['field']] = '';
                }
            }
        }


		$filter = array();
		foreach ($searchcolumns as $field => $search_value) {
        	if (!empty($search_value)) {
        		$searchcolumns_query[$field] = $search_value;
        		if(strpos($field,'id') !== false) {
        			$filter[] = array('name'=>$field,'value'=>$search_value,'operator'=>'=','value_array'=>'');	
        		} else {
        			$filter[] = array('name'=>$field,'value'=>'%'.$search_value.'%','operator'=>'LIKE','value_array'=>'');	
        		}
        	}
        }       
        
		$cases = array();
		//total for pagination - 2007/03/07
		$total = 0;
		if (!empty($filter)) { // || !empty($customWhere)
		/**
    		unset($_SESSION['total_count']); 
			//need to find total count first...not smart to do it this way but we have no choice for now
			$temp_select_fields['selected'][] = 'id';
			$cases = $caseApp->search($filter,$temp_select_fields);
			$total = 0; //getting incorrect size with sizeof($cases);
			foreach($cases as $case) {
				if(isset($case['id'])) $total++;
			}
		*/
			
			$select_fields = getDataToPass($caseApp,'list');
			$cases = $caseApp->search($filter, $select_fields, $limitstart, $limit);
			$total = $limit+1; //need to add 1 or JPagination will set to All
			if($presentation->fullpagination == 1) $total = $cases['total_count'];
        } else if(!empty($run_search) && $run_search == 'true') {
          //prompt user to enter some sort of filter
          $_SESSION['search_error'] = "At least one search filter is required. Please enter a search filter and try again.";
          //unset($_SESSION['search_error']);
         // return null;
        }

		/** [IC] 2006/05/22 - pagination */
		//require_once( $GLOBALS['mosConfig_absolute_path'] . '/includes/pageNavigation.php' );
		//$pageNav = new mosPageNav( $total, $limitstart, $limit );
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

        $presentation->RenderSearchForm($cases, $searchcolumns, $columnData, $pageNav, $searchcolumns_query, $searchType);
        break;
    case "edit":

        $columns = getColumnData($caseApp);

        if(isset($caseID) && ! $caseID) {
            $presentation->RenderNewForm($columns);
        } else {
            list($thiscase,$notes) = $caseApp->get($caseID);
            $presentation->Render($columns, $thiscase, $notes);
        }
        break;
    case "newnote":
		if( $grantaccess == 'auth_readonly' ) {
			//mosNotAuth();
			JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			return;
		}

        // First add the new note
        $cases = $caseApp->createNote($_POST['caseID'], $_POST, $_FILES);

       // echo $cases . "<br />";

        // broke error checking
        if($caseApp->sugarError) {
            //echo $sugarComm->getErrorText();
            $mainframe->redirect( JURI::base() . "index.php?option=" . _MYNAMEIS . "&task=edit&caseID={$_POST['caseID']}",'There was an error processing your request.');
        }
        $mainframe->redirect( JURI::base() . "index.php?option=" . _MYNAMEIS . "&task=edit&caseID={$_POST['caseID']}",'Note saved!');
        break;
    case "error":
        echo $sugarConf->getBrokeMessage();
        break;
    case "saveedit":
        if( $_POST['button']=='Save' ) {
            $cases = $caseApp->modify($_POST);
            $mainframe->redirect( JURI::base() . "index.php?option=" . _MYNAMEIS . "&task=edit&caseID={$cases['id']}",'Case saved!');
        } else {
            $mainframe->redirect( JURI::base() . "index.php?option=$option", "New case is cancelled.");
        }
        break;
    case "savenew":
        if( $_POST['button']=='Save' ) {
            $cases = $caseApp->create($_POST);
            $mainframe->redirect( JURI::base() . "index.php?option=" . _MYNAMEIS . "&task=edit&caseID={$cases['id']}",'Case saved!');
        } else {
            $mainframe->redirect( JURI::base() . "index.php?option=$option", "New case is cancelled.");
        }
        break;
    case "download":
        if(!empty($_REQUEST['noteid'])) {
			$theFile = $caseApp->getNoteAttachment($_REQUEST['moduleid'],$_REQUEST['noteid']);
            $file = base64_decode($theFile->file);

            $discard = ob_end_clean();
            $content_dispo_header = "Content-Disposition: attachment; filename=\"".$theFile->filename."\"";

            header($content_dispo_header);
            header("Content-Type: application/force-download");
            header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
            header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Content-Length: ".strlen($file));
			echo $file;
            die();
		} else {
            $mainframe->redirect( JURI::base() . "index.php?option=$option", "No File To Download.");
        }
        break;
    case "refresh":
		$caseApp->stopSession();
        $caseApp->startSession();
        break;
	case "home":
    default:
/**
		//work around for not having total count...do just first time for each list
		if(!isset($_SESSION['total_count'])) { 
			//need to find total count first...not smart to do it this way but we have no choice for now
			$temp_select_fields['selected'][] = 'id';
			$cases = $caseApp->getAll($temp_select_fields);
			$total = 0; //getting incorrect size with sizeof($cases);
			foreach($cases as $case) {
				if(isset($case['id'])) $total++;
			}
			$_SESSION['total_count'] = $total;
        } else {
        	$total = $_SESSION['total_count'];
        }
*/
    	$select_fields = getDataToPass($caseApp,'list');
		//$limitStr = "$limitstart,$limit"; //no longer needed
        $cases = $caseApp->getAll($select_fields,$limitstart, $limit);
		$total = $limit+1; //need to add 1 or JPagination will set to All
		if($presentation->fullpagination == 1) $total = $cases['total_count']; //need to do a customization in Sugar to return this...should be added by default in future versions

    	/** [IC] 2006/05/22 - pagination */
		//require_once( $GLOBALS['mosConfig_absolute_path'] . '/includes/pageNavigation.php' );
		//$pageNav = new mosPageNav( $total, $limitstart, $limit );
		//echo "total: $total start: $limitstart: show: $limit";
		if (empty($pageNav))
		{
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $total, $limitstart, $limit );
		}

    	$columns = getColumnData($caseApp);

        if($caseApp->err) {
            echo $sugarComm->getErrorText();
            return true;
        }

        $presentation->frontpage($cases, $caseApp, $columns, $pageNav);
        break;
}

$caseApp->logout();



function getColumnData(&$caseApp) {
    $database = $caseApp->sugarConf->joomlaDatabase;
    $columnData = $caseApp->getAvailableFields();

	$query = "SELECT * FROM #__sugar_case_portal_fields ";
	//if($type == 'list') {
	//	$query .= " WHERE inlist = 1 OR `field` IN ('id')";
	//}
	$query .= " order by ordering ";
    $database->setQuery($query);
    $results = $database->query();

    while ($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
		$columns['selected'][] = $result;
		//$columns['data'][]['name'] = $result['field'];
	}

    $columns['data'] = $columnData;

    return $columns;
}

function getDataToPass(&$caseApp,$type = 'all') {
    $database = $caseApp->sugarConf->joomlaDatabase;

	$query = "SELECT `field` FROM #__sugar_case_portal_fields ";
	if($type == 'list') {
		$query .= " WHERE inlist = 1 OR `field` IN ('id')";
	}
	$query .= " order by ordering ";
    $database->setQuery($query);
    $results = $database->query();

	$i = 0;
    while ($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
		$columns[$i++] = $result['field'];
	}

    return $columns;
}

// Session management function
function startSugarSession(&$caseApp) {
    if(!isset($_SESSION)){
        session_start();
    }
    // Check to see if we already have a sugar session
    if(isset($_SESSION['sugar_session'])){
        $caseApp->setSugarSessionID($_SESSION['sugar_session']);
    } else {
        // If not, create one and get going
        $caseApp->createSession();
        $_SESSION['sugar_session'] = $caseApp->getSugarSessionID();
    }
}

function stopSugarSession(&$caseApp) {
    if(!isset($_SESSION)){
        session_start();
    }
    if(empty($caseApp->sessionID) && isset($_SESSION['sugar_session'])){
        $caseApp->setSugarSessionID($_SESSION['sugar_session']);
    }
    $caseApp->closeSession();
    unset($_SESSION['sugar_session']);
}


?>
