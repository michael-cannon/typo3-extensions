<?php

/**
 * Bulletin email list downloader
 *
 * @author Michael Cannon, michael@peimic.com
 * @version $Id: emaillist.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

// grab database login from Typo3 configuration file
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db
include_once( '/home/bpm/www/bpminstitute/typo3conf/localconf.php' );

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

$bulletinId						= cbGet( 'b' );
$firstLast						= cbGet( 'fl' );
// cbDebug( 'bulletinId', $bulletinId, true );
// cbDebug( 'firstLast', $firstLast, true );

if ( ! $bulletinId )
{
	// show links for download
	$bulletins					= array(
									'BPM'	=> 'BPM Bulletin'
									, 'BR'	=> 'Business Rules Bulletin'
									, 'G'	=> 'Governance Bulletin'
									, 'OP'	=> 'Organizational Performance Bulletin'
									, 'RFID'=> 'RFID Bulletin'
									, 'SOA'	=> 'SOA Bulletin'
									, 'BA'	=> 'Business Architecture Bulletin'
									, 'C'	=> 'Compliance Bulletin'
									, 'GV'	=> 'Government Bulletin'
									, 'IN'	=> 'Innovation Bulletin'
								);

	$html						= '';
	$html						.= '<html>
		<head>
		<title>BSG Bulletin Email Download</title>
		</head>
		<body>
		<p>Select a bulletin to download. URLs can be saved for direct
		access.</p>
		<ul>
	';

	foreach ( $bulletins as $key => $value )
	{
		$html					.= '<li>';
		$html					.= '<a href="?b=' 
									. $key 
									. '">'
									. $value 
									. ' ( Combined first/last )</a>';
		$html					.= '</li>';
		$html					.= '<li>';
		$html					.= '<a href="?b='
									. $key . '&fl=1">'
									. $value 
									. ' ( Separate first/last )</a>';
		$html					.= '</li>';
	}
		
	$html						.= '
		</ul>
		</body>
		</html>
	';

	echo $html;
	exit();
}

else
{
	// set bulletin for download
	switch ( $bulletinId )
	{
		case 'BPM':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter1 = 1 )
			';
			break;

		case 'SOA':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter5 = 1 )
			';
			break;

		case 'BR':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter2 = 1 )
			';
			break;

		case 'OP':
		case 'PP':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter3 = 1 )
			';
			break;

		case 'RFID':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter4 = 1 )
			';
			break;

		case 'G':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter6 = 1 )
			';
			break;

		case 'BA':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter7 = 1 )
			';
			break;

		case 'C':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter8 = 1 )
			';
			break;

		case 'GV':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter9 = 1 )
			';
			break;

		case 'IN':
			$bulletin			= '
				AND ( u.tx_bpmprofile_newsletter10 = 1 )
			';
			break;

		default:
			$bulletin			= '';
			break;
	}
}

// create db conection
$db								= mysql_connect( $typo_db_host
									, $typo_db_username
									, $typo_db_password
								)
								or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
	or die( 'Could not select database' );

// load fe_users folder locations
$userPid						= 20;

if ( ! $firstLast )
{
	$firstLastSql 				= "
		CONCAT_WS( ' ', u.first_name, u.last_name ) name
	";
}

else
{
	$firstLastSql 				= "
		u.first_name
		, u.last_name
	";
}


$sql							= "
	SELECT 
		$firstLastSql
		, u.company
		, u.email
		, csl.client_ip
	FROM fe_users u
		LEFT JOIN tx_canspamlog_main csl ON u.uid = csl.fe_userid
	WHERE 1 = 1
		AND u.pid = $userPid
		AND u.deleted != 1
		AND u.disable != 1
		AND u.email NOT LIKE ''
		$bulletin
	/*
	LIMIT 20
	*/
	GROUP BY u.email
";

// cbDebug( 'sql', $sql );
// exit();

// get query result
$result							= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

$members						= array();

if ( $result && $data = mysql_fetch_assoc( $result ) )
{
	// create headings
	// $members[]					= array_keys( $data );

	// cycle through formmail items
	do
	{
		// push member onto members array
		$members[]			= $data;
		// once cycle complete, all done
	} while ( $data = mysql_fetch_assoc( $result ) );

	// free up our result set
	mysql_free_result( $result );
}

// close db connection
mysql_close( $db );

// display 
// make filename with today's date and memberlist
$today							= cbSqlNow( true );
$filename						= 'emaillist_' 
									. $bulletinId
									. '_'
									. $today
									. '.txt';

// convert members to csv for download
$membersCsv					= '';

foreach ( $members as $key => $value )
{
	$membersCsv				.= cbMkCsvString( $value, "\t" );
}

// download members list
cbBrowserDownload( $filename, $membersCsv );

?>
