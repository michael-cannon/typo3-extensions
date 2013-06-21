<?php

/*
 *  So this file is just a file that defines constants, globals, and so forth, and
 *     makes sure the rest of the sugarincs get included somehow.  This *is* the core
 *     and *every* sugar portal component needs to have it, and only it.
 *
 */

defined('_JEXEC') or
    die('Direct Access to this file is not allowed.');

// todo: make the sugarincs check for this define
define('_VALID_SUGAR','');

if( defined('_MYNAMEIS') ) {
    $sugarName = _MYNAMEIS;
} else {
    // Fill in a default name.  If it's wrong, there will be trouble later
    $sugarName = "com_sugarcases";
}

$basePath = t3lib_extMgm::extPath('icsugarcrm') . "res/";

// Error defines and the sugarError class
require_once( $basePath . "sugarinc/sugarError.php" );

// Enable this to debug the component (might show password hashes and usernames)
// NOT SAFE FOR PUBLIC USERS
//define('_DEBUG',_SHOWDEBUG);

// Enable this to show errors (should be safe for public users)
//define('_DEBUG',_SHOWERRORS);

// Enable this to see the soap payloads
// NOT SAFE FOR PUBLIC USERS
define('_DEBUG',_SHOWSOAP);
//define('_DEBUG', '');

// bring in the sugar support code

// bring in the soap stuff and anything else sugarincs are going to need
//require_once( $basePath . "nusoap/nusoap.php" ); //use php_soap instead

//Joomla libraries
require_once( $basePath ."joomla/date.php" );

// Core
require_once( $basePath . "sugarinc/sugarDB.php" );
require_once( $basePath . "sugarinc/sugarConfiguration.php" );
require_once( $basePath . "sugarinc/sugarCommunication.php" );

// Components
// Todo: scan dependencies and only include those that are actually needed by any
//       dependencies.  (needs the dependency checking in place)
require_once( $basePath . "sugarinc/sugarLeads.php" );
require_once( $basePath . "sugarinc/sugarUser.php" );
require_once( $basePath . "sugarinc/sugarContact.php" );
/** [IC] 2006/1/12 */
require_once( $basePath . "sugarinc/sugarAccount.php" );
require_once( $basePath . "sugarinc/sugarCase.php" );
require_once( $basePath . "sugarinc/sugarBug.php" );
require_once( $basePath . "sugarinc/sugarDownload.php" );
require_once( $basePath . "sugarinc/sugarNote.php" );

// Application logic
//   brings in the core of the logic layer.  You still need to include the specific file that contains your logic class
require_once( $basePath . "sugarapp/sugarApp.php" );
require_once( $basePath . "sugarapp/sugarAppBug.php" );
require_once( $basePath . "sugarapp/sugarAppCase.php" );
require_once( $basePath . "sugarapp/sugarHTML.php" );


$sugarContact = false;


?>