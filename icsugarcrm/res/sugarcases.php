<?php
/**
 * SugarCRM SOAP helper
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: sugarcases.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $
 */

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
