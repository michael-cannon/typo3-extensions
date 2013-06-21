<?php

/**
 * Plugin Name: TYPO3 to WordPress Importer
 * Plugin URI: http://peimic.com/c/tech
 * Description: Migrates the tt_news/timtab posts from TYPO3 to WordPress.
 * Author: Michael Cannon <michael@peimic.com>
 * Author URI: http://peimic.com/
 *
 * Orignal Plugin URI: http://www.fjordinterative.com/
 * Original Author: Brad Touesnard
 * Original Author URI: http://bradt.ca/
 *
 * Version: 0.0.1
 * Revision: $Id: typo3-to-wp.php,v 1.3 2011/06/07 05:01:34 peimic.comprock Exp $
 *
 * Usage: http://aihrus.localhost/wp-admin/plugins.php?ttw_migrate_data=true
 */

// Plugin helpers
require_once('lib/typo3-functions.php');
require_once('lib/wp-functions.php');
require_once('lib/importer.php');

// TODO get TYPO3 pics dir via URL
$dirTYPO3						= '/Users/michael/Sites/acqal/';
$uploadsDirTYPO3				= $dirTYPO3 . 'uploads/';

// get WordPress db access from current WP itself
$db_wp							= mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true);
mysql_selectdb(DB_NAME, $db_wp) or die ('No WordPress database');

// TODO get TYPO3 db access interactively, but saved to session or optoins
require_once($dirTYPO3 . 'typo3conf/localconf.php');
$db_typo3						= mysql_connect($typo_db_host, $typo_db_username, $typo_db_password, true);
mysql_selectdb($typo_db, $db_typo3) or die ('No TYPO3 database');

// TODO get TYPO3 line break & replacement
$newlineTypo3					= "\r\n";
$newlineWp						= "\n\n";

if ( $_REQUEST['ttw_importComments'] ) {
	add_action('init', 'ttw_importComments');
}

if ( $_REQUEST['ttw_migrate_data'] ) {
	add_action('init', 'ttw_migrate_data');
}

?>
