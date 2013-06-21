#!/usr/local/bin/php
<?php

/**
 * This script provides the means of syncronizing Typo3 fe_users tables with
 * ZenCart's zen_address_book, zen_customers, and zen_customer_info.
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: zcfuSync.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

error_reporting( E_ALL );

// it's assumed that zencart database user can connect to typo3 database, if not
// make it so. Usually just add the typo3 table access permissions to the
// zencart database user.

define( 'USER_DIR', '/home/luican/' );
define( 'WWW_DIR', USER_DIR . 'www/' );
define( 'ZENCART_ADMIN_DIR', WWW_DIR . 'zencart/admin/' );

// zencart configurations
require_once( ZENCART_ADMIN_DIR . 'includes/configure.php' );
require_once( ZENCART_ADMIN_DIR . 'includes/functions/general.php' );
require_once( ZENCART_ADMIN_DIR . 'includes/functions/password_funcs.php' );

// cb_cogs helpers
require_once( USER_DIR . 'cb-third_party/cb_cogs/Adodb.config.php' );
require_once( CB_COGS_DIR . 'cb_string.php' );

// zencart <> fe_users sync
require_once( WWW_DIR . 'typo3conf/scripts/class.zcfuSync.php' );

// typo3 database name
define( 'T3_DB', 'cannon_bpm' );

// do some work
$zcfuSync						= new zcfuSync();
$zcfuSync->t3Pid				= 20;
$zcfuSync->t3Usergroup			= 1;
$zcfuSync->sync();

?>
