<?php

/**
 * RealURL alternate rootpage_id and multiple domain setup example
 *
 * multiple domain setup
 * 	Update domains
 * 	Update rootpage_id
 *	The root page id is the uid of your domain home in the TYPO3 page tree
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: realurl-custom.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $
 */

// Edit rootpage_id to your website's root page UID
if ( false ) {
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT']['pagePath']['rootpage_id'] = 1;
}

// edit for single subdomains
if ( false ) {
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['subdomain.example.com'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'];
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['subdomain.example.com']['pagePath']['rootpage_id'] = 357;
}

// edit for multiple domains
if ( false ) {
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.example.com'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'];
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.example.com']['pagePath']['rootpage_id'] = 45;
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['example.com'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['www.example.com'];
}

// edit for multiple languages
if ( false ) {
	$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DOMAINS'] = array(
		'encode' => array(
			// English
			array(
				'GETvar' => 'L',
				'value' => '0',
				'useConfiguration' => 'example.com',
				'urlPrepend' => 'http://example.com'
			),
			// Traditional Chinese
			array(
				'GETvar' => 'L',
				'value' => '1',
				'useConfiguration' => 'example.com',
				'urlPrepend' => 'http://example.com.tw'
			),
			// Simplified Chinese
			array(
				'GETvar' => 'L',
				'value' => '2',
				'useConfiguration' => 'example.com',
				'urlPrepend' => 'http://example.com.cn'
			),
		),
		'decode' => array(
			// English
			'example.com' => array(
				'GETvars' => array(
					'L' => '0',
				),
				'useConfiguration' => 'example.com'
			),
			// Traditional Chinese
			'example.com.tw' => array(
				'GETvars' => array(
					'L' => '1',
				),
				'useConfiguration' => 'example.com'
			),
			// Simplified Chinese
			'example.com.cn' => array(
				'GETvars' => array(
					'L' => '2',
				),
				'useConfiguration' => 'example.com'
			),
		)
	);
}

?>
