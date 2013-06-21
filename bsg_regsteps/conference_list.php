<?php

/**
 * Membership list downloader
 *
 * @author Michael Cannon, michael@peimic.com
 * @version $Id: conference_list.php,v 1.1.1.1 2010/04/15 10:03:06 peimic.comprock Exp $
 */

// grab database login from Typo3 configuration file
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db


include_once( '/home/bpm/public_html/bpminstitute/typo3conf/localconf.php' );
		$confCode				= isset( $_REQUEST['confCode'] )
									? $_REQUEST['confCode'] 
									: 'CH';
		define( 'BSG_REG_CONF_CODE', $confCode );
require(dirname(__FILE__)."/config/".BSG_REG_CONF_CODE.".config.php");
include_once( '/home/bpm/public_html/bpminstitute/t3lib/class.t3lib_extmgm.php' );

require_once(dirname(__FILE__)."/class.service.php");
require_once(dirname(__FILE__)."/services/class.stubservice.php");

$stub = new StubService();

function getAllCourses() {
    global $stub;
    $data =  $stub->getCourses();
    $courses = array();
    foreach ($data as $arr) {
        foreach ($arr['courses'] as $course) {
            $courses[intval($course['uid'])] = $course;
        }
    }
    return $courses;
}

function getNames($courses) {
    $arrOut = array();
    foreach ($courses as $c) {
        $arrOut[] = $c['name'];
    }
    return $arrOut;
}

function getIds($courses) {
    $arrOut = array();
    foreach ($courses as $c) {
        $arrOut[] = (int) $c['uid'];
    }
    return $arrOut;
}

// download helper
require_once( CB_COGS_DIR . 'cb_html.php' );

set_time_limit( 60 * 5 );

// create db conection
$db = mysql_connect( $typo_db_host, $typo_db_username, $typo_db_password ) or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
or die( 'Could not select database' );

// load fe_users folder locations
// $userPid						= 20;

$sql							= "
	SELECT 
		u.email Email
		, u.first_name FirstName
		, u.last_name LastName
		, u.company Company
		, u.department Department
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
			WHEN ( FIND_IN_SET( '4', u.usergroup ) ) THEN 'X'
			ELSE ''
			END Professional_Guest_Member
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
			WHEN ( u.disable  = 1 ) THEN 'Yes'
			ELSE 'No'
			END disabled

		, CASE
			WHEN ( 0 < u.lastlogin )
				THEN FROM_UNIXTIME( u.lastlogin, '%M %e, %Y' )
				ELSE ''
			END last_login
		, u.courses
		, u.priority_code
		, u.conf_series
		, u.courses_list

	FROM fe_users u
		LEFT JOIN static_countries c ON u.static_info_country = c.cn_iso_3
	WHERE 1 = 1
		/*
		AND u.pid = $userPid
		*/
		AND u.deleted = 0
		AND u.courses <> ''
	/*
	ORDER BY u.uid DESC
	LIMIT 100
	*/
";

/*
$sql					.= ( cbGet( 'u' ) )
? 'GROUP BY u.email'
: '';
*/

// cbDebug( 'sql', $sql );
// exit();

// get query result
$result							= mysql_query( $sql )
or die( 'Query failed: ' . mysql_error() );

// file to write to
$filenameTmp					= '/tmp/' . uniqid(rand(), true) . '.csv';
$filelink						= fopen( $filenameTmp, 'w+' );

$maxCourses = 0;
$rows = array();
$rowsCount = 0;

function escape_dbl($val) {
    return '"'.$val.'"';
}

if ( $result && $data = mysql_fetch_assoc( $result ) )
{
    $bpmInvolvement			= 'involvement';
    $bpmInvolvementId		= 18;
    $soaInvolvement			= 'involvement-soa';
    $soaInvolvementId		= 17;

    $involvement			= loadInvolvement();
    // cbDebug( 'involvement', $involvement );


    $courses = getAllCourses();
    $names = getNames($courses);
    $ids = getIds($courses);
    $idCount = count($ids);

    // cycle through formmail items
    do {
        $rowsCount++;        
       
        $course_ids = array_map('intval', explode(",", $data['courses_list']));        
	// for testing
	// $course_ids = array(1,3,4);
       
        $arrCourses = array();
        foreach ($ids as $id) {
            $arrCourses[] = in_array($id, $course_ids) ? 'X' : '';
        }

        foreach ( $data as $key => $value )
        {
            $data[ $bpmInvolvement ]	= isset( $involvement[ $data[ 'member_id' ] ][ $bpmInvolvementId ] )
            ? $involvement[ $data[ 'member_id' ] ][ $bpmInvolvementId ]
            : '';
            $data[ $soaInvolvement ]	= isset( $involvement[ $data[ 'member_id' ] ][ $soaInvolvementId ] )
            ? $involvement[ $data[ 'member_id' ] ][ $soaInvolvementId ]
            : '';
            if(is_array($value)) {
                continue;
            }
            if ( preg_match( "#\s#", $value ) ) {
                $data[ $key ] = preg_replace( "#\s#", ' ', $value );
            }
        }
        $keys = array_keys($data);
        $offset = array_search('courses', $keys);
        array_splice($data, $offset, 1, $arrCourses);

        $rows[] = $data;

        //$membersCsv = cbMkCsvString( $data );
        //	fwrite( $filelink, $membersCsv );
    } while($data = mysql_fetch_assoc($result));

    $offset = array_search('courses', $keys);
    array_splice($keys, $offset, 1, $names);
    $membersHeader = $keys;
    $membersHeader[] = $bpmInvolvement;
    $membersHeader[] = $soaInvolvement;

    //$membersHeader	= array_keys( $data );

    fwrite( $filelink, cbMkCsvString( $membersHeader  ));

    for( $i=0; $i<$rowsCount; $i++ ) {
        $membersCsv = cbMkCsvString($rows[$i]);
        fwrite( $filelink, $membersCsv );
    }

    // free up our result set
    mysql_free_result( $result );
}

// close db connection
mysql_close( $db );

// display
// make filename with today's date and memberlist
$today							= cbSqlNow( true );
$filename						= 'conferencelist_' . $today . '.csv';

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

    if ( $result && $data = mysql_fetch_assoc( $result ) )   {
        do {
            $dr = explode( '","', trim($data['results'], '"') );
            $results = '';

            foreach ( $dr as $item ) {
                list( $itemName, $answer )= explode( '":"', $item );

                if ( preg_match( "#^($bpmInvolvement|$soaInvolvement)$#", $itemName )) {
                    $results	=  stripslashes(preg_replace( "#\s#", ' ', $answer ));
                    // only need short item
                    list( $results )	= explode( ':', $results );
                }
            }
            $array[ $data[ 'fe_cruser_id' ] ][ $data[ 'domain_group_id' ] ] = $results;
        } while ( $data = mysql_fetch_assoc( $result ) );

        // free up our result set
        mysql_free_result( $result );
    }

    return $array;
}

?>
