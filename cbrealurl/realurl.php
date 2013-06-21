<?php 

$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_realurl_pathsegment,tx_realurl_exclude,tx_realurl_pathoverride,alias,nav_title,title';

// @ref http://www.slideshare.net/jweiland/why-realurl-sucks-and-how-to-fix-it
$realurlConfig = array(
	'init' => array(
		'adminJumpToBackend' => true,
		'appendMissingSlash' => 'ifNotFile,redirect[301]',
		'emptyUrlReturnValue' => '/',
		'enableAllUnicodeLetters' => false,
		// needed for non-Latin based character languages
		// best to enable per domain config than by default
		// 'enableAllUnicodeLetters' => true,
		'enableCHashCache' => true,
		'enableUrlDecodeCache' => true,
		'enableUrlEncodeCache' => true,
		'postVarSet_failureMode' => '', 
		'reapplyAbsRefPrefix' => true,
		'respectSimulateStaticURLs' => false,
		'postVarSet_failureMode'=>'ignore',
	),

	'fileName' => array(
		'defaultToHTMLsuffixOnPrev' => false,
		'acceptHTMLsuffix' => true,
		'index' => array(
			'text' => array(
				'keyValues' => array(
					'type' => 99,
				),
			),
			'print' => array(
				'keyValues' => array(
					'type' => 98,
				),
			),
			'rss' => array(
				'keyValues' => array(
					'type' => 100,
				),
			),
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
			'sitemap.xml' => array(
				'keyValues' => array(
					'type' => 841132,
				),
			),
			'sitemap.txt' => array(
				'keyValues' => array(
					'type' => 841131,
				),
			),
			'robots.txt' => array(
				'keyValues' => array(
					'type' => 841133,
				),
			),
		),
	),

	'pagePath' => array(
		'type' => 'user',
		'userFunc' => 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main',
		'spaceCharacter' => '-',
		'languageGetVar' => 'L',
		'expireDays' => 365,
		'rootpage_id' => 1,
	),

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
				'de' => '0',
				'en' => '1',
				/*
				'es' => '2',
				'fr' => '3',
				'it' => '4',
				'pt' => '5',
				'cz' => '6',
				'ja' => '7',
				'nl' => '8',
				'hu' => '9',
				'tr' => '10',
				'zh' => '15',
				'pl' => '16',
				'kr' => '17',
				 */
			),
			'noMatch' => 'bypass',
		),
		array (
			'GETvar' => 'type',
			'valueMap' => array (
				'validation' => '3131'
			),
			'noMatch' => 'bypass'
		),
	),

	'postVarSets' => array(
		'_DEFAULT' => array(
			// news archive parameters
			'archive' => array(
				array(
					'GETvar' => 'tx_ttnews[year]'
				),
				array(
					'GETvar' => 'tx_ttnews[month]',
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
				array(
					'GETvar' => 'tx_ttnews[day]',
					'noMatch' => 'bypass',
				),
				array(
					'GETvar' => 'tx_ttnews[pS]',
					'noMatch' => 'bypass',
				),
				array(
					'GETvar' => 'tx_ttnews[pL]',
					'noMatch' => 'bypass',
				)
			),
			// news pagebrowser
			'p' => array(
				array(
					'GETvar' => 'tx_ttnews[pointer]'
				)
			),
			'pg' => array(
				array(
					'GETvar' => 'tx_ttnews[pg]'
				)
			),
			'news' => array(
				array(
					'GETvar' => 'tt_news'
				)
			),
			// news category
			'category' => array (
				array(
					'GETvar' => 'tx_ttnews[cat]',
					'lookUpTable' => array(
						'table' => 'tt_news_cat',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1
						),
					),
				),
			),
			// news item
			'article' => array(
				array(
					'GETvar' => 'tx_ttnews[tt_news]',
					'lookUpTable' => array(
						'table' => 'tt_news',
						'id_field' => 'uid',
						// MLC search engines like uniqueness
						// for lookup, uid first is best
						'alias_field' => 'concat(uid, "-", title)',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-'
						)
					)
				),
				array(
					'GETvar' => 'tx_ttnews[swords]'
				)
			),
			'abp' => array(
				array(
					'GETvar' => 'tx_ttnews[backPid]'
				)
			),
			'nq' => array(
				array(
					'GETvar' => 'news_search[search_text]'
				)
			),
			'nqc' => array(
				array(
					'GETvar' => 'news_search[category][]'
				)
			),
			'login'	=> array(
				array(
					'GETvar'	=> 'tx_newloginbox_pi3[showUid]'
				)
			),
			'forgot-login'	=> array(
				array(
					'GETvar'	=> 'tx_newloginbox_pi1[forgot]'
				)
			),
			'forgot'	=> array(
				array(
					'GETvar'	=> 'tx_felogin_pi1[forgot]'
				)
			),
			'query'	=> array(
				array(
					'GETvar'	=> 'tx_indexedsearch[sword]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[ext]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[submit_button]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[_sections]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[pointer]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[extResume]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[type]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[group]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[_freeIndexUid]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[media]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[defOp]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[ang]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[desc]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[results]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[sections]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[lang]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[order]'
				),
				array(
					'GETvar'	=> 'tx_indexedsearch[freeIndexUid]'
				)
			),
			'srfu'	=> array(
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[cmd]'
				),
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[pointer]'
				),
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[mode]'
				),
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[sword]'
				),
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[sort]'
				),
				array(
					'GETvar'	=> 'tx_srfeuserregister_pi1[regHash]'
				)
			),
			'scal'	=> array(
				array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[showUid]'
				),
				array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[form]'
				),
				array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[mode]'
				),
				array(
					'GETvar'	=> 'tx_desimplecalendar_pi1[backPath]'
				)
			),
			'calender-category'	=> array(
				array(
					'GETvar'	=> 'tx_advCalendar_pi1[category]'
				)
			),
			'view' => array(
				array(
					'GETvar' => 'view'
				)
			),
			'cforum'		=> array(
				array(
					'GETvar' => 'cat_uid'
				),
				array(
					'GETvar' => 'conf_uid'
				),
				array(
					'GETvar' => 'thread_uid'
				),
				array(
					'GETvar' => 'page'
				),
				array(
					'GETvar' => 'flag'
				)
			),
			'event' => array(
				array(
					'GETvar' => 'eventid'
				)
			),
			'ef' => array(
				array(
					'GETvar' => 'editflag'
				)
			),
			'start' => array(
				array(
					'GETvar' => 'start'
				)
			),
			'day' => array(
				array(
					'GETvar' => 'day'
				)
			),
			'week' => array(
				array(
					'GETvar' => 'week'
				)
			),
			'month' => array(
				array(
					'GETvar' => 'month'
				)
			),
			'bu' => array(
				array(
					'GETvar' => 'backURL'
				)
			),
			'cmd' => array(
				array(
					'GETvar' => 'cmd'
				)
			),
			'year' => array(
				array(
					'GETvar' => 'year'
				)
			),
			'rdfi' => array(
				array(
					'GETvar' => 'tx_nrdfimport_pi1[showUid]'
				)
			),
			'sponsor'	=> array(
				array(
					'GETvar'	=> 'tx_t3consultancies_pi1[showUid]',
					'lookUpTable' => array(
						'table' => 'tx_t3consultancies',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-'
						)
					)
				),
				array(
					'GETvar'	=> 'tx_t3consultancies_pi1[service]'
				),
				array(
					'GETvar'	=> 'tx_t3consultancies_pi1[pointer]'
				)
			),
			'slide-show'	=> array(
				array(
					'GETvar'	=> 'tx_gsislideshow_pi1[total]'
				),
				array(
					'GETvar'	=> 'tx_gsislideshow_pi1[lastUid]'
				),
				array(
					'GETvar'	=> 'tx_gsislideshow_pi1[firstUid]'
				),
				array(
					'GETvar'	=> 'tx_gsislideshow_pi1[current]'
				),
				array(
					'GETvar'	=> 'tx_gsislideshow_pi1[showUid]',
					'lookUpTable' => array(
						'table' => 'tx_gsislideshow_images',
						'id_field' => 'uid',
						'alias_field' => 'caption',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-'
						)
					)
				)
			),
			'tac' => array(
				array(
					'GETvar' => 'tac'
				)
			),
			'bp' => array(
				array(
					'GETvar' => 'backPID'
				)
			),
			'product' => array(
				array(
					'GETvar' => 'tt_products',
					'lookUpTable' => array(
						'table' => 'tt_products',
						'id_field' => 'uid',
						'alias_field' => 'concat(uid, "-", title)',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1
						),
					)
				)
			),
			// MLC Bahag photo gallery
			'gallery' => array(
				array(
					'GETvar' => 'gallery'
				)
			),
			'image' => array(
				array(
					'GETvar' => 'viewImage'
				)
			),
			'rp' => array(
				array(
					'GETvar' => 'resultPage'
				)
			),
			'idx' => array(
				array(
					'GETvar' => 'idx'
				)
			),
			'anmode' => array (
				array('GETvar' => 'tx_piapappnote_pi1[mode]')
			),
			'anptr' => array (
				array('GETvar' => 'tx_piapappnote_pi1[pointer]')
			),
			'anfile' => array (
				array('GETvar' => 'tx_piapappnote_pi1[file]')
			),
			'anseach' => array (
				array('GETvar' => 'tx_piapappnote_pi1[sword]')
			),
			'annote' => array (
				array('GETvar' => 'tx_piapappnote_pi1[noteid]')
			),
			'anauth' => array (
				array('GETvar' => 'tx_piapappnote_pi1[author]')
			),
			'anname' => array (
				array('GETvar' => 'tx_piapappnote_pi1[title]')
			),
			'andesc' => array (
				array('GETvar' => 'tx_piapappnote_pi1[description]')
			),
			'ancat' => array (
				array('GETvar' => 'tx_piapappnote_pi1[categorylist]')
			),
			'anver' => array (
				array('GETvar' => 'tx_piapappnote_pi1[versionlist]')
			),
			'andev' => array (
				array('GETvar' => 'tx_piapappnote_pi1[devicelist]')
			),
			'galp' => array (
				array('GETvar' => 'tx_hldamgallery_pi1[galleryPID]')
			),
			'galcat' => array (
				array('GETvar' => 'tx_hldamgallery_pi1[galleryCID]')
			),
			'galimg' => array (
				array('GETvar' => 'tx_hldamgallery_pi1[imgID]')
			),
			'faq-category' => array (
				array(
					'GETvar' => 'tx_irfaq_pi1[cat]',
					'lookUpTable' => array(
						'table' => 'tx_irfaq_cat',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-'
						)
					)
				)
			),
			// page comments
			'skcomm' => array(
				array(
					'GETvar' => 'tx_skpagecomments_pi1[showComments]',
				),
				array(
					'GETvar' => 'tx_skpagecomments_pi1[showForm]',
				),
			),
			// ab_downloads
			'dl-act' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[action]',
					'valueMap' => array(
						'show-category' => 'getviewcategory',
						'propose-a-new-download' => 'getviewaddnewdownload', 
						'open-download' => 'getviewclickeddownload', 
						'show-details-for-download' => 'getviewdetailsfordownload', 
						'report-broken-download' => 'getviewreportbrokendownload',
						'rate-download' => 'getviewratedownload',
					),
				),
			),
			'dl-cat' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[category_uid]',
					'valueMap' => array(
						'home' => '0',
					),
					'lookUpTable' => array(
						'table' => 'tx_abdownloads_category',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			),
			'dl-file' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[uid]',
					'lookUpTable' => array(
						'table' => 'tx_abdownloads_download',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			),
			'dl-ptr' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[pointer]',
				),
			),
			'll-act' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[action]',
					'valueMap' => array(
						'show-category' => 'getviewcategory',
						'propose-a-new-link' => 'getviewaddnewlink', 
						'open-link' => 'getviewclickedlink', 
						'show-details-for-link' => 'getviewdetailsforlink', 
						'report-broken-link' => 'getviewreportbrokenlink',
						'rate-link' => 'getviewratelink',
					),
				),
			),
			'll-cat' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[category_uid]',
					'valueMap' => array(
						'home' => '0',
					),
					'lookUpTable' => array(
						'table' => 'tx_ablinklist_category',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			),
			'll-link' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[uid]',
					'lookUpTable' => array(
						'table' => 'tx_ablinklist_link',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			),
			'll-ptr' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[pointer]',
				),
			),
			'cal-year'=> array(
				array(
					'GETvar' => 'tx_cal_controller[year]'
				),
			),
			'cal-month'=> array(
				array(
					'GETvar' => 'tx_cal_controller[month]'
				),
			),
			'cal-day'=> array(
				array(
					'GETvar' => 'tx_cal_controller[day]'
				),
			),
			'cal-view'=> array(
				array(
					'GETvar' => 'tx_cal_controller[view]'
				),
			),
			'cal-date'=> array(
				array(
					'GETvar' => 'tx_cal_controller[getdate]'
				),
			),
			'cal-lv'=> array(
				array(
					'GETvar' => 'tx_cal_controller[lastview]'
				),
			),
			'cal-type'=> array(
				array(
					'GETvar' => 'tx_cal_controller[type]'
				),
			),
			'cal-cat'=> array(
				array(
					'GETvar' => 'tx_cal_controller[category]',
					'lookUpTable' => array(
						'table' => 'tx_cal_category',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause'  => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),

					),
				),
			),
			'cal-entry'=> array(
				array(
					'GETvar' => 'tx_cal_controller[uid]',
					'lookUpTable' => array(
						'table' => 'tx_cal_event',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause'  => ' AND NOT deleted',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			),
			'rating' => array(
				array(
					'GETvar' => 'tx_accessibleratings[ref]',
				),
				array(
					'GETvar' => 'tx_accessibleratings[value]',
				),
			),
			'jobid' => array (
				array('GETvar' => 'tx_dmmjobcontrol_pi1[job_uid]')
			),
			'ec' => array(
				array(
					'GETvar' => 'tx_ednewscomments_pi1[pointer]',
				),
				array(
					'GETvar' => 'tx_ednewscomments_pi1[sort]',
				),
			),
			'ch' => array(
				array(
					'GETvar' => 'cHash',
					'noMatch' => 'bypass',
				),
			),
			'pv' => array(
				array(
					'GETvar' => 'tx_atolflashpdfviewer_pi1[table]',
				),
				array(
					'GETvar' => 'tx_atolflashpdfviewer_pi1[uid]',
				),
			)
		)
	),

	'redirects_regex' => array (
	),

);

$TYPO3_CONF_VARS['EXTCONF']['realurl'] = array(
	'_DEFAULT'				=> $realurlConfig,
	// only useful for simple domain setups
	$_SERVER['HTTP_HOST']	=> $realurlConfig,
	// 'example.com'		=> $realurlConfig,
	// 'www.example.com'		=> $realurlConfig,
	// 'www.example.com.cn'	=> $realurlConfig,
	// 'www.example.de'		=> $realurlConfig,
);

unset($realurlConfig);

// $TYPO3_CONF_VARS['EXTCONF']['realurl']['www.example.com.cn']['init']['enableAllUnicodeLetters'] = true;
// $TYPO3_CONF_VARS['EXTCONF']['realurl']['www.example.com.cn']['pagePath']['rootpage_id'] = 123;

// following not needed for simpler sites
// _DOMAINS configuration conflicts with rlmp_languagedetect
if ( false ) {
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DOMAINS'] = array(
	'encode' => array(
		array(
			'GETvar' => 'L',
			'value' => '0', // de
			'useConfiguration' => $_SERVER['HTTP_HOST'],
			'urlPrepend' => 'http://' . $_SERVER['HTTP_HOST'],
		),
		array(
			'GETvar' => 'L',
			'value' => '1', // en
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '2', // es
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '3', // fr
			'useConfiguration' => 'www.example.fr',
			'urlPrepend' => 'http://www.example.com/fr/'
		),
		array(
			'GETvar' => 'L',
			'value' => '4', // it
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '5', // pt
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '6', // cz
			'useConfiguration' => 'www.example.cz',
			'urlPrepend' => 'http://www.example.cz'
		),
		array(
			'GETvar' => 'L',
			'value' => '7', // ja
			'useConfiguration' => 'www.example.co.jp',
			'urlPrepend' => 'http://www.example.co.jp'
		),
		array(
			'GETvar' => 'L',
			'value' => '8', // nl
			'useConfiguration' => 'www.example.nl',
			'urlPrepend' => 'http://www.example.nl'
		),
		array(
			'GETvar' => 'L',
			'value' => '9', // hu
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '10', // tr
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '15', // zh
			'useConfiguration' => 'www.example.com.cn',
			'urlPrepend' => 'http://www.example.com.cn'
		),
		array(
			'GETvar' => 'L',
			'value' => '16', // pl
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
		array(
			'GETvar' => 'L',
			'value' => '17', // kr
			'useConfiguration' => 'www.example.com',
			'urlPrepend' => 'http://www.example.com'
		),
	),
	'decode' => array(
		$_SERVER['HTTP_HOST'] => array(
			'GETvars' => array(
				'L' => '0',
			),
			'useConfiguration' => $_SERVER['HTTP_HOST']
		),
		'www.example.de' => array(
			'GETvars' => array(
				'L' => '1',
			),
			'useConfiguration' => 'www.example.com'
		),
		'www.example.fr' => array(
			'GETvars' => array(
				'L' => '3',
			),
			'useConfiguration' => 'www.example.fr'
		),
		'www.example.cz' => array(
			'GETvars' => array(
				'L' => '6',
			),
			'useConfiguration' => 'www.example.cz'
		),
		'www.example.co.jp' => array(
			'GETvars' => array(
				'L' => '7',
			),
			'useConfiguration' => 'www.example.co.jp'
		),
		'www.example.nl' => array(
			'GETvars' => array(
				'L' => '8',
			),
			'useConfiguration' => 'www.example.nl'
		),
		'www.example.com.cn' => array(
			'GETvars' => array(
				'L' => '15'
			),
			'useConfiguration' => 'www.example.com.cn'
		),
	)
);
}

?>