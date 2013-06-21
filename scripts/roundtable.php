<?php

/**
 * Round table participant downloader
 *
 * @author Michael Cannon, michael@peimic.com
 * @version $Id: roundtable.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

// grab database login from Typo3 configuration file
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db
include_once( 'localconf.php' );

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

// create db conection
$db								= mysql_connect( $typo_db_host
									, $typo_db_username
									, $typo_db_password
								)
								or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
	or die( 'Could not select database' );

// load tx_pksaveformmail_data
// fields
// uid pid tstamp crdate cruser_id deleted hidden subject recipient sentbody fields_order sentBodySerialized TCABase sentbody_base64encoded

$roundTablePid					= 124;
$sql							= "
	SELECT tpd.uid signup_id 
		, FROM_UNIXTIME( tpd.tstamp, '%M %e, %Y %l:%i %p' ) signup
		, tpd.subject
		, tpd.sentbody
	FROM tx_pksaveformmail_data tpd
	WHERE 1 = 1
		AND pid = $roundTablePid
	ORDER BY signup_id DESC
	/* LIMIT 5 */
";

// get query result
$result							= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

$attendees						= array();

if ( $result && $data = mysql_fetch_assoc( $result ) )
{
	// create headings
	$attendees[]				= array_keys( $data );

	// cycle through formmail items
	do
	{
		// cbPrint2( 'data', $data );

		// build up data of
		// tstamp, subject, exploded body
		$attendee				= array();
		$attendee[]				= $data[ 'signup_id' ];
		$attendee[]				= $data[ 'signup' ];
		$attendee[]				= $data[ 'subject' ];

		// expand sentbody
		$sentbody				= $data[ 'sentbody' ];
		// cbPrint2( 'sentbody', $sentbody );

		// explode on newlines
		$sentbody				= explode( "\n", $sentbody );
		// cbPrint2( 'sentbody', $sentbody );
		$sentbodyCount			= count( $sentbody );

		for ( $i = 0; $i < $sentbodyCount; $i++ )
		{
			$value				= $sentbody[ $i ];
			// cbPrint2( 'value', $value );

			// skip the REQUEST_REFERENCE: and its value
			if ( preg_match( '/REQUEST_REFERENCE:/', $value ) )
			{
				$i++;

				continue;
			}

			if ( ! preg_match( '/REFERENCE/', $value ) )
			{
				// remove capitalized prefaces
				$value			= preg_replace( '/^[-_A-Z0-9]*:\s*/'
									, ''
									, $value
								);
			}

			// remove empties
			$value				= trim( $value );

			if ( $value )
			{
				$attendee[]		= $value;
			}
		}

		// push attendee onto attendees array
		$attendees[]			= $attendee;
		// once cycle complete, all done
	} while ( $data = mysql_fetch_assoc( $result ) );

	// free up our result set
	mysql_free_result( $result );
}

// close db connection
mysql_close( $db );

// display 
// cbPrint2( 'round table participants', $attendees );

// make filename with today's date and round table
$today							= cbSqlNow( true );
$filename						= 'round_table_attendees_' . $today . '.csv';

// convert attendees to csv for download
$attendeesCsv					= '';

foreach ( $attendees as $key => $value )
{
	$attendeesCsv				.= cbMkCsvString( $value );
}

// cbPrint2( 'attendeesCsv', $attendeesCsv );

// download attendees list
cbBrowserDownload( $filename, $attendeesCsv );

?>
