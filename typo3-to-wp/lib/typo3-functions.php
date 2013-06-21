<?php
/**
 * Plugin Name: TYPO3 to WordPress Importer
 *
 * Helper functions
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: typo3-functions.php,v 1.1 2011/06/07 06:58:50 peimic.comprock Exp $
 */

// TYPO3 includes for helping parse typolink tags
include('typo3/class.t3lib_div.php');
include('typo3/class.t3lib_parsehtml.php');
include('typo3/class.t3lib_softrefproc.php');


/*
 * @param	integer	tt_news id to lookup
 * @return	array	names of tt_news categories
 */
function ttw_get_typo3_post_cats($uid) {
	global $db_typo3;

	$sql = sprintf("
		SELECT c.title
		FROM tt_news_cat c
			LEFT JOIN tt_news_cat_mm m ON c.uid = m.uid_foreign
		WHERE m.uid_local = %s
	", $uid);
	$result = mysql_query($sql, $db_typo3);

	$cats = array();
	while ($row = mysql_fetch_row($result)) {
		$cats[] = $row[0];
	}

	return $cats;
}


// remove TYPO3's broken link span code
function parseLinkSpans($bodytext) {
	$parsehtml = t3lib_div::makeInstance('t3lib_parsehtml');
	$spanTags = $parsehtml->splitTags('span', $bodytext);
	// echo 'spanTags' . ' : '; print_r($spanTags); echo '<br />';	

	foreach($spanTags as $k => $foundValue)	{
		if ($k%2) {
			$spanValue = preg_replace('/<span[[:space:]]+/i','',substr($foundValue,0,-1));
	// echo 'spanValue' . ' : '; print_r($spanValue); echo '<br />';	

			// remove the red border, yellow backgroun broken link code
			if ( preg_match( '#border: 2px solid red#i', $spanValue ) ) {
				$spanTags[$k] = '';
				$spanValue = str_ireplace('</span>', '', $spanTags[$k+1]);
				$spanTags[$k+1] = $spanValue;
			}
		}
	}

	// echo 'spanTags' . ' : '; print_r($spanTags); echo '<br />';	
	// exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	

	return implode( '', $spanTags );
}


// include t3lib_div, t3lib_softrefproc
// look for getTypoLinkParts to parse out LINK tags into array
function parseTypolinks( $bodytext ) {
	$softrefproc = t3lib_div::makeInstance('t3lib_softrefproc');
	$parsehtml = t3lib_div::makeInstance('t3lib_parsehtml');

	$linkTags = $parsehtml->splitTags('link', $bodytext);
	// echo 'linkTags' . ' : '; print_r($linkTags); echo '<br />';	

	foreach($linkTags as $k => $foundValue)	{
		if ($k%2) {
			$typolinkValue = preg_replace('/<LINK[[:space:]]+/i','',substr($foundValue,0,-1));
	// echo 'typolinkValue' . ' : '; print_r($typolinkValue); echo '<br />';	
			$tLP = $softrefproc->getTypoLinkParts($typolinkValue);
	// echo 'tLP' . ' : '; print_r($tLP); echo '<br />';	

			switch ( $tLP['LINK_TYPE'] ) {
				case 'mailto':
					// internal page link, drop link
					$linkTags[$k] = '<a href="mailto:' . $tLP['url'] . '" target="_blank">';
					$typolinkValue = str_ireplace('</link>', '</a>', $linkTags[$k+1]);
					$linkTags[$k+1] = $typolinkValue;
					break;

				case 'url':
					// internal page link, drop link
					$linkTags[$k] = '<a href="' . $tLP['url'] . '" target="_blank">';
					$typolinkValue = str_ireplace('</link>', '</a>', $linkTags[$k+1]);
					$linkTags[$k+1] = $typolinkValue;
					break;

				// TODO pull file into post
				case 'file':
				case 'page':
				default:
					// internal page link, drop link
					$linkTags[$k] = '';
					$typolinkValue = str_ireplace('</link>', '', $linkTags[$k+1]);
					$linkTags[$k+1] = $typolinkValue;
					break;
			}
		}
	}

	// echo 'linkTags' . ' : '; print_r($linkTags); echo '<br />';	
	// exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	

	return implode( '', $linkTags );
}


// remove <br /> from pre code and replace withnew lines
function parsePreCode($bodytext) {
	$parsehtml = t3lib_div::makeInstance('t3lib_parsehtml');
	$preTags = $parsehtml->splitTags('pre', $bodytext);
	// echo 'preTags' . ' : '; print_r($preTags); echo '<br />';	

	foreach($preTags as $k => $foundValue)	{
		if ( 0 == $k%2 ) {
			$preValue = preg_replace('#<br\s?/?>#i', "\n", $foundValue);
			$preTags[$k] = $preValue;
		}
	}

	// echo 'preTags' . ' : '; print_r($preTags); echo '<br />';	
	// exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	

	return implode( '', $preTags );
}

?>
