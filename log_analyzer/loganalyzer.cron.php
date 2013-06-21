<?php
/**
 * Cron tack for log report
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @author	Dmitry Gordienko <dmitry.gordienko@gmail.com>
 * @version $Id:
 */
error_reporting (E_ALL ^ E_NOTICE);

function recursiveDirName($file, $x){
    if($x > 0) return recursiveDirName( dirname($file), $x-1 );
    return $file;
}
$file = recursiveDirName(__FILE__, 4);
if (!defined('PATH_site'))
   define('PATH_site', $file.'/');

if ($_SERVER['PHP_SELF']) {
	if (!defined('PATH_thisScript')) define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', $_SERVER['PHP_SELF'])));
} else {
	if (!defined('PATH_thisScript')) define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', $_ENV['_'])));
}
if (!defined('PATH_site')) define('PATH_site', dirname(dirname(dirname(dirname(dirname(PATH_thisScript))))).'/');
if (!defined('PATH_t3lib')) {
   if (!defined('PATH_t3lib')) define('PATH_t3lib', PATH_site.'t3lib/');
      define('PATH_typo3conf', PATH_site.'typo3conf/');
}
define('TYPO3_mainDir', 'typo3/');
if (!defined('PATH_typo3')) define('PATH_typo3', PATH_site.TYPO3_mainDir);
if (!defined('PATH_tslib')) {
	if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
		define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
	} elseif (@is_dir(PATH_site.'tslib/')) {
		define('PATH_tslib', PATH_site.'tslib/');
	}
}
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE', 'BE');

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'config_default.php');
require_once(PATH_typo3conf.'localconf.php');

if (!defined ('TYPO3_db'))  die ('The configuration file was not included.');
if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS']))      die('You cannot set the GLOBALS-array from outside this script.');

	// Check if cronjob is already running:
if (@file_exists (PATH_site.'typo3temp/tx_log_analyzer_cron.lock')) {
		// If the lock is not older than 1 day, skip index creation:
	if (filemtime (PATH_site.'typo3temp/tx_log_analyzer_cron.lock') > (time() - (60*60*24))) {
		die('TYPO3 Log Analyzer Cron: Aborting, another process is already running!'.chr(10));
	} else {
		// echo('TYPO3 Log Analyzer Cron: A .lock file was found but it is older than 1 day! Processing log ...'.chr(10));
	}
}
touch (PATH_site.'typo3temp/tx_log_analyzer_cron.lock');

	// Connect to the database
require_once(PATH_t3lib.'class.t3lib_db.php');
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
$result = $TYPO3_DB->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password); 
if (!$result)	{
	die("Couldn't connect to database at ".TYPO3_db_host);
}
$TYPO3_DB->sql_select_db(TYPO3_db);

// ****************************************************
// Include tables customization (tables + ext_tables)
// ****************************************************
include (TYPO3_tables_script ? PATH_typo3conf.TYPO3_tables_script : PATH_t3lib.'stddb/tables.php');
	// Extension additions
if ($TYPO3_LOADED_EXT['_CACHEFILE'])    {
	include (PATH_typo3conf.$TYPO3_LOADED_EXT['_CACHEFILE'].'_ext_tables.php');
} else {
	include (PATH_t3lib.'stddb/load_ext_tables.php');
}
	// extScript
if (TYPO3_extTableDef_script)   {
	include (PATH_typo3conf.TYPO3_extTableDef_script);
}

require_once(PATH_t3lib.'class.t3lib_cs.php');

require_once(t3lib_extMgm::extPath('log_analyzer').'LogAnalyzer.php');
$log = t3lib_div::makeInstance('LogAnalyzer');
$log->setMode(5);
$log->init('xls_mail');
global $be_group;
$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','be_groups','title=\''.$be_group.'\'','','','1');
$group = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
$group = $group['uid'];

$users = array();
$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('username, realName, email, usergroup','be_users','be_users.disable=0 and be_users.deleted=0','','','');
while(($res) && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
   $name = (empty($row['realName']) ? $row['username'] : $row['realName']);
   $users[$name]['email'] = $row['email'];
   $users[$name]['group'] = $row['usergroup'];
}
foreach ($users as $user => $val) {
   if (is_array($val)) {
      $group_arr = explode(',',$val['group']);
      if (false !== array_search($group,$group_arr))
         $users[$user]['valid'] = true;
      else
         $users[$user]['valid'] = false;
   }
}

// MLC 20080911 test
$xusers = array(
	'bob' => array( 'valid' => true, 'email' => 'michael@peimic.com' )
);

// MLC 20090428 ignore as pimailer extension is installed
if ( !class_exists('PHPMailer') ) require_once('class.phpmailer.php');

$mailer = new PHPMailer();
$mailer->Subject = LOG_EMAIL_SUBJECT;
$mailer->Body = LOG_EMAIL_BODY;
$mailer->FromName = LOG_EMAIL_FROM_NAME;
$mailer->From = LOG_EMAIL_FROM_EMAIL;
$filename = 'loganalyzer_'.gmdate("m-d-Y_H-i").'.xls';
$mailer->AddAttachment(PATH_site.'typo3temp/report.xls',$filename,'base64','application/vnd.ms-excel');

logger(">>> start...\n");

foreach ($users as $user) {
	if (empty($user['email']) || ! $user['valid'])
		continue;

	$mailer->AddAddress($user['email']);

	if(!$mailer->Send()) {
		logger($mailer->ErrorInfo);
	} else {
		logger($filename.' sent to '.$user['email']."\n");
	}

	$mailer->ClearAddresses();
}

logger("<<< end\n");
unlink (PATH_site.'typo3temp/tx_log_analyzer_cron.lock');
unlink (PATH_site.'typo3temp/report.xls');
?>
