<?php


defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JApplicationHelper::getPath( 'admin_html' ) ); 

define('_MYNAMEIS', 'com_sugarcases');

$basePath = JPATH_SITE . "/components/" . _MYNAMEIS . "/";
require_once( $basePath . "sugarportal.inc.php" );

//require_once ( $mainframe->getPath( 'front_html' ) );
require_once ( JApplicationHelper::getPath( 'front_html' ) );

$task = JRequest::getVar('task', '' );
$cid = JRequest::getVar('cid', array());

$reorder_field = JRequest::getVar('reorder_field','');
$reorder_dir = JRequest::getVar('reorder_dir','');
//$scope 		= JRequest::getCmd( 'scope' );
$cid 		= JRequest::getVar( 'cid', array(0), '', 'array' );

// needed to configure the presentation layer for this component
$presentationLayer = new HTML_sugar_portal();
$bugApp = new sugarAppCase($_REQUEST, 'portal');

$bugApp->configurePresentationLayer($presentationLayer);

// Sanity checks
//if( $sugarError )
//    $task = "error";
switch($task) {
    case 'error':
        $mainframe->redirect( JURI::base() . "index2.php?option=" . _MYNAMEIS . "&task=config", "There is a configuration error, please examine your configuration and fix it." );
        echo $sugarError;
        exit();
        break;
	case 'config':
		showConfigForm($bugApp);
		break;
	case 'saveconfig':
		saveConfig($bugApp);
		break;
	case 'saveformfields':
		saveFormFields($bugApp);
		break;
	case 'orderup':
		orderSection( $cid[0], -1, $option, $bugApp );
		break;
	case 'orderdown':
		orderSection( $cid[0], 1, $option, $bugApp );
		break;
	case 'saveorder':
		saveOrder( $cid, $bugApp );
		break;
    case 'formfields':
    default:
		showFields($bugApp);
		break;
}

function showConfigForm(&$bugApp) {
	HTML_sugar_bugs_admin::showConfigForm($bugApp);
}

function showFields(&$bugApp) {
	$database = & JFactory::getDBO();
    $configObj = &$bugApp->sugarConf;
	$database = &$configObj->joomlaDatabase;

    $database->setQuery('SELECT * FROM #__sugar_case_portal_fields order by ordering');
	$results = $database->query();

	$selectedFields = array();
    $availableFields = array();

	while ($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
		$selectedFields[] = $result;
	}
    $availableFields = $bugApp->getAvailableFields();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( 0,0,0); //$total, $limitstart, $limit );
	
	HTML_sugar_bugs_admin::showFieldsForm($configObj->joomlaOption, $selectedFields, $availableFields, $pageNav);
}

function saveFormFields(&$bugApp) { //,$newonly = false) {
	global $mainframe;
	
    $configObj = $bugApp->sugarConf;
    $database = &$configObj->joomlaDatabase;
    $availableFields = $bugApp->getAvailableFields();

	$emptyField = array( 'id' => '',
							'field' => $field,
							'show' => 0,
                            'canedit' => 0,
							'name' => '',
							'type' => 'text',
							'size' => '10',
                            'inlist' => 0,
                            'default' => '',
                            'searchable' => 0,
                            'parameters' => '',
                            'advanced' => 0,
                            'ordering' => 0);
                          
	foreach($availableFields as $fielder) {
        $field = $fielder->name;
		$currentField = array( 'id' => $_POST[$field . 'id'],
							'field' => $_POST[$field . 'field'],
							'show' => $_POST[$field . 'show'],
                            'canedit' =>$_POST[$field . 'canedit'],
							'name' => $_POST[$field . 'name'],
							'type' => $_POST[$field . 'type'],
							'size' => $_POST[$field . 'size'],
                            'inlist' => $_POST[$field . 'inlist'],
                            'default' => $_POST[$field . 'default'],
                            'searchable' => $_POST[$field . 'searchable'],
                            'parameters' => $_POST[$field . 'parameters'],
                            'advanced' => $_POST[$field . 'advanced'],
                            'ordering' => $_POST[$field . 'ordering']);
                            
       // if($newonly && !empty($currentField['id'])) continue; //only save new fields...for ordering

		if( $currentField['show'] == 'on' ) {
			$currentField['show'] = True;
		} else {
			$currentField['show'] = 0;
		}
		if( $currentField['canedit'] == 'on' ) {
			$currentField['canedit'] = True;
		} else {
			$currentField['canedit'] = 0;
		}
        if( $currentField['inlist'] == 'on' ) {
            $currentField['inlist'] = True;
        } else {
            $currentField['inlist'] = 0;
        }
        if( $currentField['searchable'] == 'on' ) {
            $currentField['searchable'] = True;
        } else {
            $currentField['searchable'] = 0;
        }
        if( $currentField['advanced'] == 'on' ) {
            $currentField['advanced'] = True;
        } else {
            $currentField['advanced'] = 0;
        }
		if( $currentField != $emptyField ) {

			$row = new mosSugar_Case_Fields($database);
			if (!$row->bind( $currentField )) {
				echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>n";
				exit();
			}

			if (!$row->store()) {
				echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>n";
				exit();
			}
		}
	}
	//if($newonly == false) 
	$mainframe->redirect( JURI::base() . "index2.php?option="._MYNAMEIS."&task=formfields", "Form Fields Saved");

}

function saveConfig(&$sugarConf) {
    global $_POST;
    global $mainframe;

    $confReturn = $sugarConf->saveConfig($_POST);
    $mainframe->redirect( JURI::base() . "index2.php?option=" . _MYNAMEIS, $confReturn);

}

function orderSection( $uid, $inc, $option, &$bugApp )
{
	global $mainframe;

	// Check for request forgeries
	//JRequest::checkToken() or die( 'Invalid Token' );

	//saveFormFields($bugApp,true); 
	//if(strpos($uid,"cb") === 0 ) {
		//brand new field...we need to get the real id
	//}
	
	$db =& JFactory::getDBO();
	$row =& JTable::getInstance('sugar_case_portal_fields');

	$row->load( $uid );
	$row->move( $inc, '' );

	$mainframe->redirect( 'index.php?option='. $option );
}


function saveOrder( &$cid ) //, &$bugApp )
{
	global $mainframe;

	// Check for request forgeries
	//JRequest::checkToken() or die( 'Invalid Token' );

	$db			=& JFactory::getDBO();
	$row		=& JTable::getInstance('sugar_case_portal_fields');

	$total		= count( $cid );
	$order		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	JArrayHelper::toInteger($order, array(0));

	// update ordering values
	for( $i=0; $i < $total; $i++ )
	{
		$row->load( (int) $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				JError::raiseError(500, $db->getErrorMsg() );
			}
		}
	}

	$row->reorder( );

	$msg 	= JText::_( 'New ordering saved' );
	$mainframe->redirect( 'index.php?option='. $option, $msg );
}

?>


