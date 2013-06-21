<?php
/**
 * Helper script for going through directory of location named directories and
 * loading documents contained therein to that location on the edms.
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: edmsAutoDocImport.php,v 1.1.1.1 2010/04/15 10:03:08 peimic.comprock Exp $
 */
include_once('/home/edmscan/local/cb_cogs/cb_cogs.config.php');
include_once( 'typo3conf/localconf.php' );

// create db conection
$db = mysql_connect($typo_db_host, $typo_db_username, $typo_db_password) or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db ) or die( 'Could not select database' );

// cycle through ipg docs directory
$baseDir = '/home/edmscan/public_html/INTERPUBLIC ASSET DOCUMENTS';
$baseDirContents = scandir( $baseDir );
$forCount = 0;
$fileCount = 0;
$locationNotLoaded = array();

foreach( $baseDirContents as $bdKey => $bdValue ) {
	if ( preg_match( '/^\./', $bdValue ) )
		continue;

	// cbDebug( 'bdValue', $bdValue );

	// lookup location id
	$agency = htmlentities( $bdValue );
	$query = <<<EOD
		SELECT uid
		FROM tx_cbgaedms_agency
		WHERE 1 = 1
			AND NOT deleted
			AND NOT hidden
			AND agency LIKE '{$agency}'
EOD;

	// get query result
	$result = mysql_query( $query ) or die( 'Query failed: ' . mysql_error() );
	$data = mysql_fetch_assoc( $result );

	// no location found, skip doc import
	if ( ! isset($data['uid']) ) {
		$locationNotLoaded[] = $bdValue;
		continue;
	}
	
	$agencyUid = $data['uid'];
	
	$forCount++;
	if ( false && 3 == $forCount )
		break;

	$fileSource = $baseDir . '/' . $bdValue;

	// each subdirectory is name of a location that matches locations in edms 
	$tempDirContents = scandir( $fileSource );

	// cycle through location folder
	// grab each file
	foreach( $tempDirContents as $tdKey => $fileName ) {
		if ( preg_match( '/^\./', $fileName ) )
			continue;

		$fileCount++;
		// cbDebug( 'fileName', $fileName );
		// load file to edms under current location
		$filePathFull = $fileSource . '/' . $fileName;

		insert( $agencyUid, $fileName, $fileSource );
	}
}

mysql_close( $db );
cbDebug( 'fileCount', $fileCount );
cbDebug( 'locationNotLoaded', $locationNotLoaded );

function insert($agencyUid, $fileName, $fileSource) {
	global $db;

	$insertArray = preparedParameters($fileName);
	$query = INSERTquery('tx_cbgaedms_doc', $insertArray);
	// cbDebug( 'query', $query ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
	$result = mysql_query($query, $db);
	$docUid = mysql_insert_id($db);
	// cbDebug( 'docUid', $docUid ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	

	// insert into tx_cbgaedms_agency_documents_mm
	// relate agency uid_local, doc uid_foreign
	$insertArray = array(
		'uid_local' => $agencyUid
		, 'uid_foreign' => $docUid
	);
	$query = INSERTquery('tx_cbgaedms_agency_documents_mm', $insertArray);
	// cbDebug( 'query', $query ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
	$result = mysql_query($query, $db);

	insertDocumentVersion( $agencyUid, $docUid, $fileName, $fileSource );
}

function preparedParameters ( $fileName ) {
	$parameterArray = array();
	$parameterArray['doc'] = $fileName;
	$parameterArray['hidden'] = 0;
	$parameterArray['doctype'] = 0;
	$parameterArray['description'] = '';
	$parameterArray['tstamp'] = time();
	$parameterArray['crdate'] = time();
	$parameterArray['pid'] = 245;
	$parameterArray['feuser'] = 4418;
	$parameterArray['version'] = 1;

	return $parameterArray;
}

function insertDocumentVersion( $agencyUid, $docUid, $fileNameOrg, $fileSource ) {
	global $db;

	// upload newfile to uploads/tx_cbgaedms
	$filePath = '/home/edmscan/public_html/uploads/tx_cbgaedms/';

	// create unique filename
	$fileNameParts = explode('.', $fileNameOrg);
	$fileNameExt = array_pop( $fileNameParts );
	$fileName = implode('.', $fileNameParts)
		. '.' . uniqid()
		. '.' . $fileNameExt;
	$fileNamePath = $filePath . $fileName;

	// upload file under new/old name
	$sourceFile = $fileSource . '/' . $fileNameOrg;
	if ( ! copy($sourceFile, $fileNamePath)) {
		// cbDebug( 'sourceFile', $sourceFile ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
		// cbDebug( 'fileNamePath', $fileNamePath ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
		echo "File copy failed. Try creating a new version\n";
	}

	$newfilename = $fileName;

	// insert version
	$insertArray = preparedVersionParameters($agencyUid, $docUid, $fileNameOrg, $newfilename);
	$query = INSERTquery('tx_cbgaedms_docversion', $insertArray);
	// cbDebug( 'query', $query ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
	$result = mysql_query($query, $db);
	$versionUid = mysql_insert_id($db);

	// insert doc version mm
	$insertArray = array(
		'uid_local' => $docUid
		, 'uid_foreign' => $versionUid
	);
	$query = INSERTquery('tx_cbgaedms_doc_version_mm', $insertArray);
	// cbDebug( 'query', $query ); cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
	$result = mysql_query($query, $db);
}

function preparedVersionParameters ($agencyUid, $docUid, $fileName, $newfilename) {
	$docVersion = 1;

	$parameterArray = array();
	$parameterArray['description'] = '';

	$parameterArray['hidden'] = 0;
	$parameterArray['tstamp'] = $parameterArray['crdate'] = time();

	$parameterArray['pid'] = 245;
	$parameterArray['feuser'] = 4418;
	$parameterArray['docversion'] = $docVersion;
	$parameterArray['filename'] = $fileName;
	$parameterArray['file'] = $newfilename;
	$parameterArray['versiontitle'] = 'A' . $agencyUid
		. '-D' . $docUid
		. '-V' . $docVersion
		. '-' . $fileName;

	return $parameterArray;
}

function INSERTquery( $table, $data ) {
	$query = 'INSERT INTO ' . $table . ' SET ';
	$set = array();
	foreach ( $data as $key => $value ) {
		$set[] = "{$key} = '$value'";
	}

	$query .= implode(', ', $set);

	return $query;
}

?>