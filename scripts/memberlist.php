<?php

/**
 * Membership list downloader
 *
 * @author Michael Cannon, michael@peimic.com
 * @version $Id: memberlist.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

// grab database login from Typo3 configuration file
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db
include_once( '/home/bpm/public_html/bpminstitute/typo3conf/localconf.php' );

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

set_time_limit( 60 * 10 );

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

$sql							= "
	SELECT 
		u.email Email
		, u.first_name FirstName
		, u.last_name LastName
		, u.company Company
		, u.uid member_id
		, FROM_UNIXTIME( u.crdate, '%M %e, %Y %l:%i %p' ) signup_date
		, FROM_UNIXTIME( u.tstamp, '%M %e, %Y %l:%i %p' ) modified_date
		, u.username
		, u.title
		, u.address
		, u.city
		, u.zone
		, u.zip
		, c.cn_short_en country
		, u.telephone
		, u.fax
		, u.www
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter1 = 1 ) THEN 'Yes'
			ELSE 'No'
			END The_BPM_Bulletin
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter5 = 1 ) THEN 'Yes'
			ELSE 'No'
			END SOA_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter2 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Business_Rules_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter3 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Operational_Performance_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter4 = 1 ) THEN 'Yes'
			ELSE 'No'
			END RFID_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter6 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Governance_Newsletter
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter7 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Business_Architecture
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter8 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Compliance
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter9 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Government
		, CASE
			WHEN ( u.tx_bpmprofile_newsletter10 = 1 ) THEN 'Yes'
			ELSE 'No'
			END Innovation
		, CASE
			WHEN ( u.module_sys_dmail_html = 1 ) THEN 'Yes'
			ELSE 'No'
			END newsletter_html
		, CASE
			WHEN ( FIND_IN_SET( '1', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Complimentary_Member
		, CASE
			WHEN ( FIND_IN_SET( '2', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Professional_Member
		, CASE
			WHEN ( FIND_IN_SET( '16', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Corporate_Member
		, CASE
			WHEN ( FIND_IN_SET( '42', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Group_Leader
		, CASE
			WHEN ( FIND_IN_SET( '43', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Corporate_Leader
		, CASE
			WHEN ( FIND_IN_SET( '4', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Professional_Guest_Member
		, CASE
			WHEN ( FIND_IN_SET( '5', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Visitor_White_Paper
		, CASE
			WHEN ( FIND_IN_SET( '6', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Visitor_Round_Table
		, CASE
			WHEN ( FIND_IN_SET( '7', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Visitor_Presentation
		, CASE
			WHEN ( FIND_IN_SET( '9', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_Chicago
		, CASE
			WHEN ( FIND_IN_SET( '10', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_SF
		, CASE
			WHEN ( FIND_IN_SET( '11', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_DC
		, CASE
			WHEN ( FIND_IN_SET( '12', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Conference_NY
		, CASE
			WHEN ( FIND_IN_SET( '19', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Requested_Conference_Access
		, CASE
			WHEN ( FIND_IN_SET( '18', u.usergroup ) ) THEN 'X'
			ELSE ''
			END BPM_Site_Member
		, CASE
			WHEN ( FIND_IN_SET( '17', u.usergroup ) ) THEN 'X'
			ELSE ''
			END SOA_Site_Member
		, CASE
			WHEN ( FIND_IN_SET( '29', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Access_via_SurveyGizmo
		, CASE
			WHEN ( FIND_IN_SET( '41', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Gartner_Las_Vegas_2008
		, u.referrer_uri referrer_url

		, CASE
			WHEN ( 0 < u.starttime )
				THEN FROM_UNIXTIME( u.starttime, '%M %e, %Y' )
				ELSE ''
			END start_date

		, CASE
			WHEN ( 0 < u.endtime )
				THEN FROM_UNIXTIME( u.endtime, '%M %e, %Y' )
				ELSE ''
			END end_date

		, u.internal_note

		, CASE
			WHEN ( u.processed = 1 ) THEN 'Yes'
			ELSE 'No'
			END processed
		, CASE
			WHEN ( u.paid = 1 ) THEN 'Yes'
			ELSE 'No'
			END paid

		, CASE
			WHEN ( u.disable  = 1 ) THEN 'Yes'
			ELSE 'No'
			END disabled

		, CASE
			WHEN ( u.join_agree = 1 ) THEN 'Yes'
			ELSE 'No'
			END Join_Agree

		, CASE
			WHEN ( 0 < u.lastlogin )
				THEN FROM_UNIXTIME( u.lastlogin, '%M %e, %Y' )
				ELSE ''
			END last_login

		, CASE
			WHEN ( u.tx_memberexpiry_expired = 1 ) THEN 'Yes'
			ELSE 'No'
			END membership_expired

		, CASE
			WHEN ( 0 < u.tx_memberexpiry_expiretime )
				THEN FROM_UNIXTIME( u.tx_memberexpiry_expiretime, '%M %e, %Y' )
				ELSE ''
			END membership_expired_date

		, CASE
			WHEN ( 0 < u.tx_memberexpiry_emailsenttime )
				THEN FROM_UNIXTIME( u.tx_memberexpiry_emailsenttime, '%M %e, %Y' )
				ELSE ''
			END membership_expiry_email_date
		, csl.client_ip
		, u.referrer_uri

	FROM fe_users u
		LEFT JOIN static_countries c ON u.static_info_country = c.cn_iso_3
		LEFT JOIN tx_canspamlog_main csl ON  u.uid = csl.fe_userid
	WHERE 1 = 1
		AND u.pid = $userPid
		AND u.deleted = 0
	/*
	ORDER BY u.uid DESC
	LIMIT 100
	*/
";
	$sql					.= ( cbGet( 'u' ) )
								? 'GROUP BY u.email'
								: '';

// cbDebug( 'sql', $sql );
// exit();

// get query result
$result							= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

// file to write to
$filenameTmp					= '/tmp/' . uniqid(rand(), true) . '.csv';
$filelink						= fopen( $filenameTmp, 'w+' );

if ( $result && $data = mysql_fetch_assoc( $result ) )
{
	$bpmInvolvement			= 'involvement';
	$bpmInvolvementId		= 18;
	$soaInvolvement			= 'involvement-soa';
	$soaInvolvementId		= 17;

	$involvement			= loadInvolvement();
	// cbDebug( 'involvement', $involvement );

	// create headings
	$membersHeader			= array_keys( $data );
	$membersHeader[]		= $bpmInvolvement;
	$membersHeader[]		= $soaInvolvement;
	$membersCsv				= cbMkCsvString( $membersHeader );

	fwrite( $filelink, $membersCsv );

	// cycle through formmail items
	do
	{
		// MLC line breaks make for ugly downloads
		foreach ( $data as $key => $value )
		{
			if ( preg_match( "#\s#", $value ) )
			{
				$data[ $key ] 	= preg_replace( "#\s#"
									, ' '
									, $value
								);
			}

			$data[ $bpmInvolvement ]	= isset( $involvement[ $data[ 'member_id' ] ][ $bpmInvolvementId ] )
											? $involvement[ $data[ 'member_id' ] ][ $bpmInvolvementId ]
											: '';
			$data[ $soaInvolvement ]	= isset( $involvement[ $data[ 'member_id' ] ][ $soaInvolvementId ] )
											? $involvement[ $data[ 'member_id' ] ][ $soaInvolvementId ]
											: '';
		}

		// push member onto members file
		$membersCsv				= cbMkCsvString( $data );
		fwrite( $filelink, $membersCsv );
	} while ( $data = mysql_fetch_assoc( $result ) );

	// free up our result set
	mysql_free_result( $result );
}

// close db connection
mysql_close( $db );

// display 
// make filename with today's date and memberlist
$today							= cbSqlNow( true );
$filename						= 'memberlist_' . $today . '.csv';

// read newly created file back for sending to user
$membersCsv						= file_get_contents( $filenameTmp );

fclose( $filelink );
unlink( $filenameTmp );

// download members list
cbBrowserDownload( $filename, $membersCsv );

/**
 * Loads involvement from survey results into array.
 *
 * @return array
 */
function loadInvolvement()
{
	$bpmInvolvement			= 'involvement';
	$soaInvolvement			= 'involvement-soa';

	$array						= array();

	$sql						= "
		SELECT fe_cruser_id
			, results
			, domain_group_id
		FROM tx_mssurvey_results
		WHERE 1 = 1
			AND deleted = 0
		/*
		ORDER BY fe_cruser_id DESC
		LIMIT 100
		*/
	";

	$result						= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

	if ( $result && $data = mysql_fetch_assoc( $result ) )
	{
		do
		{
			$dr					= explode( '","', trim($data['results'], '"') );
			$results			= '';
			
			foreach ( $dr as $item )
			{
				list( $itemName, $answer )	= explode( '":"', $item );

				if ( preg_match( "#^($bpmInvolvement|$soaInvolvement)$#"
						, $itemName
					)
				)
				{
					$results	=  stripslashes( 
										preg_replace( "#\s#"
											, ' '
											, $answer )
									);

					// only need short item
					list( $results )	= explode( ':', $results );
				}
			}

			$array[ $data[ 'fe_cruser_id' ] ][ $data[ 'domain_group_id' ] ] =
									$results;
		} while ( $data = mysql_fetch_assoc( $result ) );

		// free up our result set
		mysql_free_result( $result );
	}

	return $array;
}

?>
