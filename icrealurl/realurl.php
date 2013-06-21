<?php

/**
 * RealURL configuration helper script
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: realurl.php,v 1.1.1.1 2010/04/15 10:03:37 peimic.comprock Exp $
 */

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc'][] = 'EXT:realurl/class.tx_realurl.php:&tx_realurl->encodeSpURL';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][] = 'EXT:realurl/class.tx_realurl.php:&tx_realurl->decodeSpURL';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_urldecodecache'] = 'tx_realurl_urldecodecache';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_urlencodecache'] = 'tx_realurl_urlencodecache';

$TYPO3_CONF_VARS['FE']['addRootLineFields'].= ',tx_realurl_pathsegment';

$TYPO3_CONF_VARS['EXTCONF']['realurl'] = array(
	'_DEFAULT' => array(
		'init' => array(
			'enableCHashCache' => 1,
			'appendMissingSlash' => 'ifNotFile,redirect[301]',
			'enableUrlDecodeCache' => 1,
			'enableUrlEncodeCache' => 1,
		),
		'redirects' => array(),
		'preVars' => array(
			array(
				'GETvar' => 'no_cache',
				'valueMap' => array(
					'nc' => 1,
				),
				'noMatch' => 'bypass',
			),
			array(
				'GETvar' => 'L',
				'valueMap' => array(
					// id's need to line up with Website Language Ids in TYPO3
					// English, default, no preVar applied
					'' => '0',
					// Alternate English url labeling
					// 'english' => '0', or 'en' => '0',
					'dk' => '2',
					'de' => '1',
				),
				'noMatch' => 'bypass',
			),
		),
		'pagePath' => array(
			'type' => 'user',
			'userFunc' => 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main',
			'spaceCharacter' => '-',
			'languageGetVar' => 'L',
			'expireDays' => 1095,
## include your rootpage id here
			'rootpage_id' => 1,
		),
		'fixedPostVars' => array(),
		'postVarSets' => array(
			'_DEFAULT' => array(
// tt_news
				// news archive parameters
				'archive' => array(
					array(
						'GETvar' => 'tx_ttnews[year]' ,
						),
					array(
						'GETvar' => 'tx_ttnews[month]' ,
						'valueMap' => array(
							'january' => '01',
							'february' => '02',
							'march' => '03',
							'april' => '04',
							'may' => '05',
							'june' => '06',
							'july' => '07',
							'august' => '08',
							'september' => '09',
							'october' => '10',
							'november' => '11',
							'december' => '12',
							)
						),
					),
				// news pagebrowser
				'browse' => array(
					array(
						'GETvar' => 'tx_ttnews[pointer]',
						),
					),
				// news categories
				'select_category' => array (
					array(
						'GETvar' => 'tx_ttnews[cat]',
						),
					),
				// news articles and searchwords
				'detail' => array(
					array(
						'GETvar' => 'tx_ttnews[tt_news]',
						'lookUpTable' => array(
							'table' => 'tt_news',
							'id_field' => 'uid',
							'alias_field' => 'title',
							'addWhereClause' => ' AND NOT deleted',
							'useUniqueCache' => 1,
							'useUniqueCache_conf' => array(
								'strtolower' => 1,
								'spaceCharacter' => '-',
								),
							),
						),
					array(
						'GETvar' => 'tx_ttnews[swords]',
						),
					),
					'w' => array(
						array(
							'GETvar' => 'tx_drwiki_pi1[keyword]',
						),
						array(
							'GETvar' => 'tx_drwiki_pi1[showUid]',
						),
						array(
							'GETvar' => 'tx_drwiki_pi1[cmd]',
						),
					),
				),
			),
		// configure filenames for different pagetypes
		'fileName' => array(
			'index' => array(
				'rss.xml' => array(
					'keyValues' => array(
						'type' => 100,
					),
				),
				'rss091.xml' => array(
					'keyValues' => array(
						'type' => 101,
					),
				),
				'rdf.xml' => array(
					'keyValues' => array(
						'type' => 102,
					),
				),
				'atom.xml' => array(
					'keyValues' => array(
						'type' => 103,
					),
				),
			),
			'acceptHTMLsuffix' => 1
		),
	),
);

?>
