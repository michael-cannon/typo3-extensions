<?php

// template settings
/*
config.no_cache = 0
config.simulateStaticDocuments = 0
config.tx_realurl_enable = 1
config.prefixLocalAnchors = all
config.baseURL = http://peimic.com/
*/

// edit rootpage_id near script bottom
// The root page id is the uid of your domain home in the Typo3 page tree

// clean out urls
/*
TRUNCATE `tx_realurl_chashcache`;
TRUNCATE `tx_realurl_pathcache`;
TRUNCATE `tx_realurl_uniqalias`;
TRUNCATE `tx_realurl_urldecodecache`;
TRUNCATE `tx_realurl_urlencodecache`;
UPDATE `pages` SET `tx_realurl_pathsegment` = '';
*/

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc'][] = 'EXT:realurl/class.tx_realurl.php:&tx_realurl->encodeSpURL';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][] = 'EXT:realurl/class.tx_realurl.php:&tx_realurl->decodeSpURL';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_urldecodecache'] = 'tx_realurl_urldecodecache';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_urlencodecache'] = 'tx_realurl_urlencodecache';

$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_realurl_pathsegment,alias,nav_title,title';

$TYPO3_CONF_VARS['EXTCONF']['realurl'] = array();
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
	'init' => array(								 
		'enableCHashCache' => 1
		// disable enableUrlDecodeCache for multiple domains as no root pid is
		// caught and the system check to see if correct page path is grabbed
		, 'enableUrlDecodeCache' => 0
		, 'enableUrlEncodeCache' => 0
		, 'appendMissingSlash' => 'ifNotFile'
		, 'respectSimulateStaticURLs' => 0
		, 'postVarSet_failureMode' => 'redirect_goodUpperDir'
	)											   
	, 'redirects'		=> array()
	, 'preVars' => array(
		array(									   
			'GETvar' => 'no_cache',				  
			'valueMap' => array(					 
				'no_cache' => 1,
			),									   
			'noMatch' => 'bypass',				   
		)
		, array(										   
			'GETvar' => 'L',							 
			'valueMap' => array(
				'no' => '1',							 
			),										   
			'noMatch' => 'bypass',
		),
	)
	, 'pagePath' => array(
		'type'				=> 'user'
		, 'userFunc'		=> 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main'
		, 'spaceCharacter'	=> '-'
		, 'languageGetVar'	=> 'L'
		, 'rootpage_id'		=> 1
		, 'disablePathCache'	=> 0
		, 'expireDays'		=> 1
		, 'segTitleFieldList'	=> 'tx_realurl_pathsegment,alias,nav_title,title'
		, 'excludePageIds'	=> null
	)
	, 'fixedPostVars'	=> array()
	, 'postVarSets' => array(
		'_DEFAULT' => array(
//			'hash' => array(
//				array(
//					'GETvar' => 'cHash'
//				)
//			)
			// news item
			'article' => array(
				array(
					'GETvar' => 'tx_ttnews[tt_news]'
					, 'lookUpTable' => array(
						'table' => 'tt_news'
						, 'id_field' => 'uid'
						, 'alias_field' => 'title'
						, 'addWhereClause' => ' AND NOT deleted'
						, 'useUniqueCache' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
							, 'spaceCharacter' => '-'
						)
					)
				)
				, array(
					'GETvar' => 'tx_ttnews[backPid]'
					, 'noMatch' => 'bypass'
				)
				, array(
					'GETvar' => 'tx_ttnews[swords]'
					, 'noMatch' => 'bypass'
				)
			)
			// news page browser
			, 'news-browse' => array(
				array(
					'GETvar' => 'tx_ttnews[pointer]'
				)
			)
			// news search pager
			// http://www.bpminstitute.org/topics/business-rules/business-rules.html?tx_ttnewssearch_pi1%5Bpointer%5D=1&cHash=b0377b36bf
			, 'news-browser' => array(
				array(
					'GETvar' => 'tx_ttnewssearch_pi1[pointer]'
				)
			)
			// news archive
			, 'news-archive' => array(
				array(
					'condPrevValue' => -1
					, 'GETvar' => 'tx_ttnews[pS]' 
				)
				, array(
					'GETvar' => 'tx_ttnews[pL]'
				)
				, array(
					'GETvar' => 'tx_ttnews[arc]'
					, 'valueMap' => array(
						'archived' => 1
						, 'non-archived' => -1
					)
				)
			)
			, 'news-period' => array(
				array(
					'condPrevValue' => -1
					, 'GETvar' => 'tx_ttnews[pS]' 
				)
				, array(
					'GETvar' => 'tx_ttnews[pL]'
				)
				, array(
					'GETvar' => 'tx_ttnews[arc]'
					, 'valueMap' => array(
						'archived' => 1
						, 'non-archived' => -1
					)
				)
			)
			, 'new-login'	=> array(
				array(
					'GETvar'	=> 'tx_newloginbox_pi3[showUid]'
				)
			)
			, 'forgot-login'	=> array(
				array(
					'GETvar'	=> 'tx_newloginbox_pi1[forgot]'
				)
			)
			, 'indexed-search'	=> array(
				array(
					'GETvar'	=> 'tx_indexedsearch[sword]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[ext]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[submit_button]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[_sections]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[pointer]'
				)
			)
			, 'srfeuser'	=> array(
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[cmd]'
				)
				, array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[pointer]'
				)
				, array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[mode]'
				)
				, array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[sword]'
				)
				, array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[sort]'
				)
			)
			, 'simple-calendar'	=> array(
				array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[showUid]'
				)
				, array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[form]'
				)
				, array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[mode]'
				)
				, array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[backPath]'
				)
			)
			, 'category'	=> array(
				array(
					'GETvar'	=> 'tx_advCalendar_pi1[category]'
				)
			)
			, 'view' => array(
				array(
					'GETvar' => 'view'
				)
			)
			, 'chc-forum'		=> array(
				array(
					'GETvar' => 'cat_uid'
				)
				, array(
					'GETvar' => 'conf_uid'
				)
				, array(
					'GETvar' => 'thread_uid'
				)
			)
			, 'eventid' => array(
				array(
					'GETvar' => 'eventid'
				)
			)
			, 'editflag' => array(
				array(
					'GETvar' => 'editflag'
				)
			)
			, 'start' => array(
				array(
					'GETvar' => 'start'
				)
			)
			, 'day' => array(
				array(
					'GETvar' => 'day'
				)
			)
			, 'week' => array(
				array(
					'GETvar' => 'week'
				)
			)
			, 'month' => array(
				array(
					'GETvar' => 'month'
				)
			)
			, 'year' => array(
				array(
					'GETvar' => 'year'
				)
			)
			, 'rdf-inport' => array(
				array(
					'GETvar' => 'tx_nrdfimport_pi1[showUid]'
				)
			)
			, 'sponsor'	=> array(
				array(
					'GETvar'	=> 'tx_t3consultancies_pi1[showUid]'
					, 'lookUpTable' => array(
						'table' => 'tx_t3consultancies'
						, 'id_field' => 'uid'
						, 'alias_field' => 'title'
						, 'addWhereClause' => ' AND NOT deleted'
						, 'useUniqueCache' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
							, 'spaceCharacter' => '-'
						)
					)
				)
				, array(
					'GETvar'	=> 'tx_t3consultancies_pi1[service]'
				)
				, array(
					'GETvar'	=> 'tx_t3consultancies_pi1[pointer]'
				)
			)
			, 'slide-show'	=> array(
				array(
					'GETvar'	=> 'tx_gsislideshow_pi1[total]'
				)
				, array(
					'GETvar'	=> 'tx_gsislideshow_pi1[lastUid]'
					, 'noMatch' => 'bypass'
				)
				, array(
					'GETvar'	=> 'tx_gsislideshow_pi1[firstUid]'
					, 'noMatch' => 'bypass'
				)
				, array(
					'GETvar'	=> 'tx_gsislideshow_pi1[current]'
				)
				, array(
					'GETvar'	=> 'tx_gsislideshow_pi1[showUid]'
					, 'lookUpTable' => array(
						'table' => 'tx_gsislideshow_images'
						, 'id_field' => 'uid'
						, 'alias_field' => 'caption'
						, 'addWhereClause' => ' AND NOT deleted'
						, 'useUniqueCache' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
							, 'spaceCharacter' => '-'
						)
					)
				)
			)
			, 'news-category' => array (
				array(
					'GETvar' => 'tx_ttnews[cat]'
					, 'lookUpTable' => array(
						'table' => 'tt_news_cat'
						, 'id_field' => 'uid'
						, 'alias_field' => 'title'
						, 'addWhereClause' => ' AND NOT deleted'
						, 'useUniqueCache' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
						),
					),
				),
			)
			, 'tac' => array(
				array(
					'GETvar' => 'tac'
				)
			)
			, 'return-to' => array(
				array(
					'GETvar' => 'backPID'
				)
			)
			, 'product' => array(
				array(
					'GETvar' => 'tt_products'
					, 'lookUpTable' => array(
						'table' => 'tt_products'
						, 'id_field' => 'uid'
						, 'alias_field' => 'title'
						, 'addWhereClause' => ' AND NOT deleted'
						, 'useUniqueCache' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
						),
					)
				)
			)
		)
	)
	, 'fileName' => array (
		'index' => array(
			'index.html' => array(						
				'keyValues' => array(
					'type' => 0,
				)									
			)
			, 'print.html' => array(						
				'keyValues' => array(
					'type' => 98,
				)									
			)
			, 'rss.html' => array(						
				'keyValues' => array(
					'type' => 100,
				)									
			)
			, '_DEFAULT' => array(
				'keyValues' => array()
			)
		)
		, 'defaultToHTMLsuffixOnPrev' => 1
	)											   
);

// multiple domain setup example
// bpminstitute.org is our default anyways, but specify for best results
$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.bpminstitute.org'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'];
$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.bpminstitute.org']['pagePath']['rootpage_id'] = 1;
$TYPO3_CONF_VARS['EXTCONF']['realurl']['bpminstitute.org'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['www.bpminstitute.org'];

$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.soainstitute.org'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'];
$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.soainstitute.org']['pagePath']['rootpage_id'] = 319;
$TYPO3_CONF_VARS['EXTCONF']['realurl']['soainstitute.org'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['www.soainstitute.org'];

$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.profile.brainstorm-group.com'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'];
$TYPO3_CONF_VARS['EXTCONF']['realurl']['www.profile.brainstorm-group.com']['pagePath']['rootpage_id'] = 1220;
$TYPO3_CONF_VARS['EXTCONF']['realurl']['profile.brainstorm-group.com'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['www.profile.brainstorm-group.com'];

?>
