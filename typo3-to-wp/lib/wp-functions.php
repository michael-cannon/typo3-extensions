<?php
/**
 * Plugin Name: TYPO3 to WordPress Importer
 *
 * WordPress helpers
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: wp-functions.php,v 1.1 2011/06/07 06:58:50 peimic.comprock Exp $
 */

// TODO add Tools page link
if ( false ) {
$menu_title = __('TYPO3 -> WordPress', 'typo3-to-wp');
$links_page_hook = add_management_page(
	__('Import from TYPO3', 'typo3-to-wp'), 
	$menu_title, 
	'edit_others_posts',
	'view-broken-links',array(&$this, 'links_page')
);
}
?>
