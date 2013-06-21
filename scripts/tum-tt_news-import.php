<?php
/**
 * Simple script to import TUM XML of magazine articles into TYPO3 tt_news.
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: tum-tt_news-import.php,v 1.5 2011/06/28 08:30:19 peimic.comprock Exp $
 */

// minor customizations
$xmlFile						= 'TumMitExport.xml';
$uploadDir						= 'fileadmin/';

// TYPO3 tt_news defaults
$newsPid						= 2;
// External URL news
$newsType						= 2;
$newsAuthor						= 'TUMcampus';
$newsTstamp						= time();

set_time_limit( 600 );

// pull db connection
require_once( 'typo3conf/localconf.php' );

$db								= mysql_connect( $typo_db_host
									, $typo_db_username
									, $typo_db_password
								)
								or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
	or die( 'Could not select database' );

// not being set causes accented chars to go funky
mysql_set_charset( 'utf8', $db );

if ( isset( $_REQUEST['purge'] ) && $_REQUEST['purge'] ) {
	$purgeTables				= array(
		'tt_news',
		'tt_news_cat',
		'tt_news_cat_mm',
	);

	foreach ( $purgeTables as $table ) {
		mysql_query( "TRUNCATE TABLE {$table};" );
		echo 'Emptied table ' . ' : '; print_r($table); echo '<br />';	
	}
}

// set file destination
$currentDir						= __DIR__ . '/';
$fileDest						= $currentDir . $uploadDir;

// read xml
// @ref http://de2.php.net/manual/en/simplexml.examples-basic.php
$xml							= simplexml_load_file( $xmlFile );

$issue							= 0;
$articleTotal					= 0;
// cycle through xml TumMit records
foreach ( $xml->NList->TumMit as $TumMit ) {
	$issue++;
	// while reading each TumMitPdf
	$datetime					= strtotime( $TumMit->EffectiveDate );
	$crdate						= strtotime( $TumMit->CreationDate );
	$archivedate				= strtotime( $TumMit->ExpirationDate );

	$article					= 0;
	foreach ( $TumMit->PdfList->TumMitPdf as $TumMitPdf ) {
		$article++;
		$articleTotal++;
		// TumMitPdf which are the PDF files of articles, contains
		// transfer the pdf file
		// from PdfUrl http://portal.mytum.de/pressestelle/tum_mit/2006nr1/03-12.pdf
		$fileFrom				= (string) $TumMitPdf->PdfUrl;
		$fileFrom				= preg_replace( '#\s+#', '', $fileFrom );
		$fileFromLessDomain		= preg_replace( '#^https?://[^/]+/#', '', $fileFrom );
		// to fileadmin/pressestelle/tum_mit/2006nr1/03-12.pdf
		$fileTo					= $fileDest . $fileFromLessDomain;
		$fileToBase				= dirname( $fileTo );

		if ( ! is_dir( $fileToBase ) ) {
			mkdir( $fileToBase, 0775, true );
		}

		if ( ! is_file( $fileTo ) ) {
			$fileFromContents	= file_get_contents( $fileFrom ) ;
			if ( ! file_put_contents( $fileTo, $fileFromContents ) ) {
				die( __LINE__ . ':' . __FILE__ . ' Unable to save ' . $fileTo );
			}
		}

		$fileToTypo3			= preg_replace( "#{$currentDir}#", '', $fileTo );

		$title					= (string) $TumMitPdf->PdfTitel;
		$authorEmail			= (string) $TumMitPdf->Contact;
		// create tt_news as external url
		$insertValues			= array(
			'pid'				=> $newsPid,
			'type'				=> $newsType,
			'title'				=> $title,
			'ext_url'			=> $fileToTypo3,
			'author'			=> $newsAuthor,
			'author_email'		=> $authorEmail,
			'datetime'			=> $datetime,
			'crdate'			=> $crdate,
			'archivedate'		=> $archivedate,
			'tstamp'			=> $newsTstamp,
		);
		$insertSql				= "INSERT INTO tt_news SET ";
		$insertValuesSet		= array();
		foreach ( $insertValues as $key => $value ) {
			$value				= mysql_escape_string( $value );
			$insertValuesSet[]	= $key . '="' . $value . '"';
		}
		
		$selectSql				= "SELECT uid FROM tt_news WHERE ";
		$result					= mysql_query( $selectSql );
		// 
		if ( $result && $row = mysql_fetch_assoc( $result )) {
			mysql_free_result( $result );
			$newsUid			= $row['uid'];
			echo 'News found' . ' : '; print_r($title); echo '<br />';	

			// wipe current news category links
			$deleteSql			= "DELETE FROM tt_news_cat_mm WHERE uid_local = {$newsUid}";
			mysql_query( $deleteSql );
		} else {
			$insertSql			.= implode( ',', $insertValuesSet );
			// echo 'insertSql' . ' : '; print_r($insertSql); echo '<br />';	
			echo 'News inserted' . ' : '; print_r($title); echo '<br />';	

			mysql_query( $insertSql );
			$newsUid			= mysql_insert_id();
		}
		flush();

		$newsCategories			= array(
			getNewsCategoryId( 'TUMcampus' ),
			getNewsCategoryId( (string) $TumMit->TitelDe ),
			getNewsCategoryId( (string) $TumMitPdf->Rubrik )
		);

		// create tt_news category links
		$sorting				= 1;
		foreach ( $newsCategories as $value ) {
			// Don't relate to non-existant categories
			if ( empty( $value ) ) {
				continue;
			}

			$insertSql			= "
				INSERT INTO tt_news_cat_mm SET
				uid_local = {$newsUid}
				, uid_foreign = {$value}
				, sorting = {$sorting}
			";
			// echo 'insertSql' . ' : '; print_r($insertSql); echo '<br />';	

			mysql_query( $insertSql );
			$sorting++;
		}
	
		// update news category count
		$updateSql				= 'UPDATE tt_news SET category = ';
		$updateSql				.= count( $newsCategories );
		$updateSql				.= ' WHERE uid = ' . $newsUid;
		// echo 'updateSql' . ' : '; print_r($updateSql); echo '<br />';	
		mysql_query( $updateSql );
	}

	echo 'Issue' . ' : '; print_r($issue); echo '<br />';	
	echo 'Articles' . ' : '; print_r($article); echo '<br />';	
}

echo 'Total Issues' . ' : '; print_r($issue); echo '<br />';	
echo 'Total Articles' . ' : '; print_r($articleTotal); echo '<br />';	

// build up category map Rubrik > tt_news_cat.uid
function getNewsCategoryId( $title ) {
	global $newsPid, $newsTstamp;

	// parse title - some have numerics
	// 01. Dies academicus => Dies academicus
	$title						= preg_replace( '#^(\d+)?(\.)?(\s+)?#', '', $title );

	// Don't create empty title categories
	if ( empty( $title ) ) {
		return false;
	}

	// look for prior insert
	$query						= "
		SELECT uid FROM tt_news_cat WHERE title = '{$title}'
	";
	$result						= mysql_query( $query );
	if ( $result && $row = mysql_fetch_assoc( $result )) {
		mysql_free_result( $result );
		return $row['uid'];
	} else  {
		$insertValues			= array(
			'pid'				=> $newsPid,
			'title'				=> $title,
			'crdate'			=> $newsTstamp,
			'tstamp'			=> $newsTstamp,
		);
		$insertSql				= "INSERT INTO tt_news_cat SET ";
		$insertValuesSet		= array();
		foreach ( $insertValues as $key => $value ) {
			$insertValuesSet[]	= $key . '="' . mysql_escape_string( $value ) . '"';
		}
		$insertSql				.= implode( ',', $insertValuesSet );

		mysql_query( $insertSql );

		echo 'Category inserted' . ' : '; print_r($title); echo '<br />';	
		return mysql_insert_id();
	}
}

?>