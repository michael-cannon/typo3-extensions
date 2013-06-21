#!/usr/bin/php -q
<?php
/**
 *  Crates a simple HTML table based template for submitted Powermail forms.
 *
 *  @author Michael Cannon <michael@peimic.com>
 *  @version $Id: powermail-results-template.php,v 1.2 2011/11/16 19:27:24 peimic.comprock Exp $
 */

// Defining circumstances for CLI mode:
define('TYPO3_cliMode', TRUE);

// Defining PATH_thisScript here: Must be the ABSOLUTE path of this script in the right context:
// This will work as long as the script is called by it's absolute path!
define('PATH_thisScript', $_SERVER['SCRIPT_FILENAME']);
if (!PATH_thisScript)
	define('PATH_thisScript', $_ENV['_'] ? $_ENV['_'] : $_SERVER['_']);

// Include configuration file:
define('TYPO3_MOD_PATH', '../typo3conf/ext/');
$BACK_PATH						= '../../typo3/';
$MCONF['name']					= '_CLI_wecservant';

// Include init file:
require_once(dirname(PATH_thisScript).'/'.$BACK_PATH.'init.php');

# HERE you run your application!
$db								= $GLOBALS['TYPO3_DB'];

// pid of powermail form to create results template for
$pidsSelect						= 'DISTINCT pid';
$pidsFrom						= 'tx_powermail_fieldsets';
$pidsWhere						= ' deleted = 0 AND hidden = 0';
$pidsOrder						= ' sorting ASC';
$pidsResults					= $db->exec_SELECTgetRows( $pidsSelect, $pidsFrom, $pidsWhere, '', $pidsOrder );

foreach ( $pidsResults as $pid ) {
	$template						= '<table>';
	$pid							= $pid['pid'];
	$fsSelect						= 'uid, title';
	$fsFrom							= 'tx_powermail_fieldsets';
	$fsWhere						= ' deleted = 0 AND hidden = 0 AND pid = ' . $pid;
	$fsOrder						= ' sorting ASC';
	$fsResults						= $db->exec_SELECTgetRows( $fsSelect, $fsFrom, $fsWhere, '', $fsOrder );

	// for each fieldset, in order
	foreach ( $fsResults as $fs ) {
		// put fieldset into th
		$template					.= '<tr><th>' . $fs['title'] . '</th></tr>';

		// grab the fields, in order
		$fSelect					= 'uid, title';
		$fFrom						= 'tx_powermail_fields';
		$fWhere						= ' deleted = 0 AND hidden = 0 AND pid = ' . $pid;
		$fWhere						.= ' AND fieldset = ' . $fs['uid'];
		$fOrder						= ' sorting ASC';
		$fResults					= $db->exec_SELECTgetRows( $fSelect, $fFrom, $fWhere, '', $fOrder );

		foreach ( $fResults as $f ) {
			// put field and result into td
			$template				.= '<tr><td>' . $f['title'] . '</td><td>###UID' . $f['uid'] . '###</td></tr>';
		}
	}

	$template						.= '</table>';

	// push template to file for safe keeping
	$templateFile					= 'powermail-results-template-' . $pid . '.html';
	$file							= fopen( $templateFile, 'w+' );
	fwrite( $file, $template );
	fclose( $file );
}

?>