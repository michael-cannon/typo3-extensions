<?php
//$Id: dbinfo.php,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $
/**
@author Jaspreet Singh
What we do in this file is set up the database info.
We get the db info from the main localconf file. Things are set up so that,
on dev2, the username/password/db is for dev2, while on stage and live,
the appropriate usernames/passwords/db are returned for those environments
without any manual changes to this file.
*/

/**
Gets the user directory. I.e., the part after /home/
*/
function getUserDirectory() {
	
	$pattern = '#/home/([^/]*)/#';
	$scriptFilename = $_SERVER["SCRIPT_FILENAME"];
	$subject = $scriptFilename;
	$matches = null;
	preg_match ( $pattern, $subject, $matches );
	
	return $matches[1];
}

// $userDirectory  = getUserDirectory(); 
//echo "dbinfo.php: \$userDirectory $userDirectory ";
//phpinfo();
$configurationFile = "/home/$userDirectory/public_html/bpminstitute/typo3conf/localconf.php";
$configurationFile = "../../..//localconf.php";

require_once( $configurationFile );

//Info grabbed from localconf
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db

// If this file has been included in a regular PHP script (not Typo3 extension),
// the db variables will be set.  In a Typo3 extension, the db variables are unset, so
// we grab the info from the constants which are set by Typo3 in config_default.php
$db_username = empty($typo_db_username) ? TYPO3_db_username : $typo_db_username;
$db_password = empty($typo_db_password) ? TYPO3_db_password : $typo_db_password;
$db_db = empty($typo_db) ? TYPO3_db : $typo_db;
$db_host = empty($typo_db_host) ? TYPO3_db_host : $typo_db_host;


$bpmdsn = "mysql://$db_username:$db_password@$db_host/$db_db";

//echo "dbinfo.php: " . $bpmdsn;

//Looks something like
//$bpmdsn = 'mysql://bpm_dev2:asdfqwer@localhost/bpm_3dev2';
//Looks something like
// $dbPassword = "asdfqwer"; 
// Looks something like:
//$dbUser = "bpm_dev2";
//echo 'dbinfo';
?>
