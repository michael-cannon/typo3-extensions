<?php

/**
 * RealURL helper script
 *
 * @ref http://typo3bloke.net/post-details/archive/2008/may/30/realurl_made_easy_part_1/
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: cbrealurl.realurl.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $
 */

// Prevent new realurl updates from clearing cache
unset($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearAllCache_additionalTables']['tx_realurl_pathcache']);

// realurl naming precedence configuration
$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_realurl_pathsegment,alias,title';

$TYPO3_CONF_VARS['EXTCONF']['realurl'] = array();
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
	'init' => array( 
		'enableCHashCache' => true
		, 'appendMissingSlash' => 'ifNotFile,redirect[301]'
		, 'adminJumpToBackend' => true
		, 'enableUrlDecodeCache' => true
		, 'enableUrlEncodeCache' => true
		, 'emptyUrlReturnValue' => '/'
		// Allow for proper SEO 404 handling
		, 'postVarSet_failureMode' => ''
		, 'reapplyAbsRefPrefix' => true
	)   
	, 'redirects'		=> array()
	, 'preVars' => array(
			array(   
				'GETvar' => 'no_cache'
				, 'valueMap' => array( 
					'nc' => 1
			)
			, 'noMatch' => 'bypass'
		)
		// this section might be removed if multiple languages are based upon
		// domains than preVars. See realurl-custom.php
		, array(   
			'GETvar' => 'L', 
			'valueMap' => array(
				// id's need to line up with Website Language Ids in TYPO3
				// English, default, no preVar applied
				// 'english' => '0',
				// 'en' => '0',
				'' => '0',
				// Traditional Chinese
				'tw' => '1',
				// Simplified Chinese
				'cn' => '2',
			),   
			'noMatch' => 'bypass',
		)
		// powermail validation page type helper
		, array (
			'GETvar' => 'type',
			'valueMap' => array (
				'validation' => '3131'
			),
			'noMatch' => 'bypass'
		),
	)
	, 'pagePath' => array(
		'type'			=> 'user'
		, 'userFunc' => 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main'
		, 'spaceCharacter'	=> '-'
		, 'languageGetVar'	=> 'L'
		, 'rootpage_id'		=> 1
		, 'segTitleFieldList'	=> 'tx_realurl_pathsegment,alias,title'
		, 'expireDays'		=> 1095
	)
	, 'fixedPostVars'	=> array()
	, 'postVarSets' => array(
		'_DEFAULT' => array(
			// news archive parameters
			'archive' => array(
				array(
					'GETvar' => 'tx_ttnews[year]'
				)
				, array(
					'GETvar' => 'tx_ttnews[month]'
					// MLC uncomment if month names instead of numbers are
					// desired
					/*
					, 'valueMap' => array(
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
					*/
				)
				, array(
					'GETvar' => 'tx_ttnews[day]'
					, 'noMatch' => 'bypass',
				)
				, array(
					'GETvar' => 'tx_ttnews[pS]'
					, 'noMatch' => 'bypass',
				)
				, array(
					'GETvar' => 'tx_ttnews[pL]'
					, 'noMatch' => 'bypass',
				)
			)
			// news pagebrowser
			, 'p' => array(
				array(
					'GETvar' => 'tx_ttnews[pointer]'
				)
			)
			, 'pg' => array(
				array(
					'GETvar' => 'tx_ttnews[pg]'
				)
			)
			, 'news' => array(
				array(
					'GETvar' => 'tt_news'
				)
			)
			// news category
			, 'c' => array (
				array(
					'GETvar' => 'tx_ttnews[cat]'
					, 'lookUpTable' => array(
						'table' => 'tt_news_cat'
						, 'id_field' => 'uid'
						, 'alias_field' => 'title'
						, 'addWhereClause' => ' AND deleted != 1'
						, 'useUniqueCache' => 1
						, 'autoUpdate' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
						),
					),
				),
			)
			// news item
			, 'article' => array(
				array(
					'GETvar' => 'tx_ttnews[tt_news]'
					, 'lookUpTable' => array(
						'table' => 'tt_news'
						, 'id_field' => 'uid'
						// MLC Google wants uniqueness for spidering
						, 'alias_field' => 'concat(title, " ", uid)'
						, 'addWhereClause' => ' AND deleted != 1'
						, 'useUniqueCache' => 1
						, 'autoUpdate' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
							, 'spaceCharacter' => '-'
						)
					)
				)
				, array(
					'GETvar' => 'tx_ttnews[swords]'
				)
			)
			, 'abp' => array(
				array(
					'GETvar' => 'tx_ttnews[backPid]'
				)
			)
			, 'nq' => array(
				array(
					'GETvar' => 'news_search[search_text]'
				)
			)
			, 'nqc' => array(
				array(
					'GETvar' => 'news_search[category][]'
				)
			)
			, 'login'	=> array(
				array(
					'GETvar'	=> 'tx_newloginbox_pi3[showUid]'
				)
			)
			, 'forgot-login'	=> array(
				array(
					'GETvar'	=> 'tx_newloginbox_pi1[forgot]'
				)
			)
			, 'forgot'	=> array(
				array(
					'GETvar'	=> 'tx_felogin_pi1[forgot]'
				)
			)
			, 'query'	=> array(
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
				, array(
					'GETvar'	=> 'tx_indexedsearch[extResume]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[type]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[group]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[_freeIndexUid]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[media]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[defOp]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[ang]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[desc]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[results]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[sections]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[lang]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[order]'
				)
				, array(
					'GETvar'	=> 'tx_indexedsearch[freeIndexUid]'
				)
			)
			, 'srfu'	=> array(
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
			, 'scal'	=> array(
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
			, 'calender-category'	=> array(
				array(
					'GETvar'	=> 'tx_advCalendar_pi1[category]'
				)
			)
			, 'view' => array(
				array(
					'GETvar' => 'view'
				)
			)
			, 'cforum'		=> array(
				array(
					'GETvar' => 'cat_uid'
				)
				, array(
					'GETvar' => 'conf_uid'
				)
				, array(
					'GETvar' => 'thread_uid'
				)
				, array(
					'GETvar' => 'page'
				)
				, array(
					'GETvar' => 'flag'
				)
			)
			, 'event' => array(
				array(
					'GETvar' => 'eventid'
				)
			)
			, 'ef' => array(
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
			, 'bu' => array(
				array(
					'GETvar' => 'backURL'
				)
			)
			, 'cmd' => array(
				array(
					'GETvar' => 'cmd'
				)
			)
			, 'year' => array(
				array(
					'GETvar' => 'year'
				)
			)
			, 'rdfi' => array(
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
						, 'addWhereClause' => ' AND deleted != 1'
						, 'useUniqueCache' => 1
						, 'autoUpdate' => 1
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
				)
				, array(
					'GETvar'	=> 'tx_gsislideshow_pi1[firstUid]'
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
						, 'addWhereClause' => ' AND deleted != 1'
						, 'useUniqueCache' => 1
						, 'autoUpdate' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
							, 'spaceCharacter' => '-'
						)
					)
				)
			)
			, 'tac' => array(
				array(
					'GETvar' => 'tac'
				)
			)
			, 'bp' => array(
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
						// MLC Google wants uniqueness for spidering
						, 'alias_field' => 'concat(title, " ", uid)'
						, 'addWhereClause' => ' AND deleted != 1'
						, 'useUniqueCache' => 1
						, 'autoUpdate' => 1
						, 'useUniqueCache_conf' => array(
							'strtolower' => 1
						),
					)
				)
			)
			// MLC Bahag photo gallery
			, 'gallery' => array(
				array(
					'GETvar' => 'gallery'
				)
			)
			, 'image' => array(
				array(
					'GETvar' => 'viewImage'
				)
			)
			, 'rp' => array(
				array(
					'GETvar' => 'resultPage'
				)
			)
			, 'idx' => array(
				array(
					'GETvar' => 'idx'
				)
			)
			, 'anmode' => array (
					array('GETvar' => 'tx_piapappnote_pi1[mode]')
			)
			, 'anptr' => array (
					array('GETvar' => 'tx_piapappnote_pi1[pointer]')
)
			, 'anfile' => array (
					array('GETvar' => 'tx_piapappnote_pi1[file]')
			)
			, 'anseach' => array (
					array('GETvar' => 'tx_piapappnote_pi1[sword]')
			)
			, 'annote' => array (
					array('GETvar' => 'tx_piapappnote_pi1[noteid]')
			)
			, 'anauth' => array (
					array('GETvar' => 'tx_piapappnote_pi1[author]')
			)
			, 'anname' => array (
					array('GETvar' => 'tx_piapappnote_pi1[title]')
			)
			, 'andesc' => array (
					array('GETvar' => 'tx_piapappnote_pi1[description]')
			)
			, 'ancat' => array (
					array('GETvar' => 'tx_piapappnote_pi1[categorylist]')
			)
			, 'anver' => array (
					array('GETvar' => 'tx_piapappnote_pi1[versionlist]')
			)
			, 'andev' => array (
					array('GETvar' => 'tx_piapappnote_pi1[devicelist]')
			)													   
			, 'galp' => array (
					array('GETvar' => 'tx_hldamgallery_pi1[galleryPID]')
			)													   
			, 'galcat' => array (
					array('GETvar' => 'tx_hldamgallery_pi1[galleryCID]')
			)													   
			, 'galimg' => array (
					array('GETvar' => 'tx_hldamgallery_pi1[imgID]')
			)													   
			, 'faq-category' => array (
					array('GETvar' => 'tx_irfaq_pi1[cat]'
						, 'lookUpTable' => array(
							'table' => 'tx_irfaq_cat'
							, 'id_field' => 'uid'
							, 'alias_field' => 'title'
							, 'addWhereClause' => ' AND deleted != 1'
							, 'useUniqueCache' => 1
							, 'autoUpdate' => 1
							, 'useUniqueCache_conf' => array(
								'strtolower' => 1
								, 'spaceCharacter' => '-'
							)
					)
				)
			)													   
			// page comments
			, 'skcomm' => array(
					array(
						'GETvar' => 'tx_skpagecomments_pi1[showComments]',
					),
					array(
						'GETvar' => 'tx_skpagecomments_pi1[showForm]',
					),
			)
			// ab_downloads
			, 'dl-act' => array(
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
			)
			, 'dl-cat' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[category_uid]',
					'valueMap' => array(
						'home' => '0',
					),
					'lookUpTable' => array(
						'table' => 'tx_abdownloads_category',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND deleted != 1',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			)
			, 'dl-file' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[uid]',
					'lookUpTable' => array(
						'table' => 'tx_abdownloads_download',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND deleted != 1',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			)
			, 'dl-ptr' => array(
				array(
					'GETvar' => 'tx_abdownloads_pi1[pointer]',
				),
			)
			, 'll-act' => array(
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
			)
			, 'll-cat' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[category_uid]',
					'valueMap' => array(
						'home' => '0',
					),
					'lookUpTable' => array(
						'table' => 'tx_ablinklist_category',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND deleted != 1',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			)
			, 'll-link' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[uid]',
					'lookUpTable' => array(
						'table' => 'tx_ablinklist_link',
						'id_field' => 'uid',
						'alias_field' => 'label',
						'addWhereClause' => ' AND deleted != 1',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			)
			, 'll-ptr' => array(
				array(
					'GETvar' => 'tx_ablinklist_pi1[pointer]',
				),
			)
			, 'cal'=> array(
				array(
					'GETvar' => 'tx_cal_controller[view]'
				),
				array(
					'GETvar' => 'tx_cal_controller[getdate]'
				),
				array(
					'GETvar' => 'tx_cal_controller[lastview]'
				),
				array(
					'GETvar' => 'tx_cal_controller[type]'
				),
				array(
					'GETvar' => 'tx_cal_controller[category]',
					'lookUpTable' => array(
						'table' => 'tx_cal_category',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause'  => ' AND deleted != 1',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						 ),
					 
					),
				),
				array(
					'GETvar' => 'tx_cal_controller[uid]',
					'lookUpTable' => array(
						'table' => 'tx_cal_event',
						'id_field' => 'uid',
						'alias_field' => 'title',
						'addWhereClause'  => ' AND deleted != 1',
						'useUniqueCache' => 1,
						'autoUpdate' => 1,
						'useUniqueCache_conf' => array(
							'strtolower' => 1,
							'spaceCharacter' => '-',
						),
					),
				),
			)
			, 'rating' => array(
				array(
					'GETvar' => 'tx_accessibleratings[ref]',
				),
				array(
					'GETvar' => 'tx_accessibleratings[value]',
				),
			)
			, 'jobid' => array (
					array('GETvar' => 'tx_dmmjobcontrol_pi1[job_uid]')
			)
			, 'ec' => array(
				array(
					'GETvar' => 'tx_ednewscomments_pi1[pointer]',
				),
				array(
					'GETvar' => 'tx_ednewscomments_pi1[sort]',
				),
			)
			, 'ch' => array(
				array(
					'GETvar' => 'cHash',
					'noMatch' => 'bypass',
				),
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
			, 'text.html' => array(
				'keyValues' => array(
					'type' => 99,
				)
			)
			, 'rss.xml' => array(
				'keyValues' => array(
					'type' => 100,
				)
			)
			, 'rss091.xml' => array(
				'keyValues' => array(
					'type' => 101,
				 )
			)
			, 'rdf.xml' => array(
				'keyValues' => array(
					'type' => 102,
				)
			)
			, 'atom.xml' => array(
				'keyValues' => array(
					'type' => 103,
				)
			)
			// ext:seo_basics overrides this
			, 'sitemap.xml' => array(
				'keyValues' => array(
					'type' => 776,
				)
			)
			, '_DEFAULT' => array(
				'keyValues' => array()
			)
		)
		, 'defaultToHTMLsuffixOnPrev' => false
		, 'acceptHTMLsuffix' => 1
	)   
);

$customFile						= PATH_typo3conf .'realurl-custom.php';

// include customizations outside of this script
if ( file_exists( $customFile ) ) {
	include( $customFile );
}

?>
