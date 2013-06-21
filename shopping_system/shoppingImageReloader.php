<?php
// shopping image reloader
// @author Michael Cannon <michael@peimic.com>
// @version $Id: shoppingImageReloader.php,v 1.1.1.1 2010/04/15 10:04:02 peimic.comprock Exp $

require_once( '../../localconf.php' );
require_once( 'class.shopping_tcemainprocdm.php' );

set_time_limit( 3600 );

$fileDir						= dirname(__FILE__)
									. '/../../../uploads/tx_shoppingsystem';
$fileBigExt						= '_big.jpg';
$fileSmallExt					= '_small.jpg';
$imageBigSize					= 165; //MPF 20070628 from 130 to 165
$imageSmallSize					= 145; //MPF 20070628 from 130 to 165

// create db conection
$db								= mysql_connect( $typo_db_host
									, $typo_db_username
									, $typo_db_password
								)
								or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
	or die( 'Could not select database' );

// query tt_news for products with images
$query							= "
	SELECT 
		uid
		, tx_shoppingsystem_product_image_small
		, tx_shoppingsystem_product_image
		, tx_shoppingsystem_product_fetch_url
	FROM tt_news
	WHERE 1 = 1
		AND tx_shoppingsystem_product_fetch_url != ''
	;
								";

// get query result
$result							= mysql_query( $query )
									or die( 'Query failed: ' . mysql_error() );

// cycle through products with images
while( $result && $data = mysql_fetch_assoc( $result ) )
{
	$uid						 = $data[ 'uid' ];

	// images already loaded, just resize
	if ( $data[ 'tx_shoppingsystem_product_image_small' ] )
	{
		// ignore already done images this time
		continue;

		// pull the small, big filenames
		$fileSmallName			= $fileDir . $data[ 'tx_shoppingsystem_product_image_small' ];
		$fileBigName			= $fileDir . $data[ 'tx_shoppingsystem_product_image' ];
	}

	// grab missing images
	else
	{
		$fileKey				= mkey(12);
		$fileSmallName			= $fileKey . $fileSmallExt;
		$fileBigName			= $fileKey . $fileBigExt;

		$update					= "
			UPDATE tt_news
			SET tx_shoppingsystem_product_image_small = '$fileSmallName'
				, tx_shoppingsystem_product_image = '$fileBigName'
			WHERE uid = $uid
	;
								";
		cbDebug( 'update', $update );	
		$resultU				= mysql_query( $update )
									or die( 'Update failed: ' . mysql_error() );

		$fileSmallName			= $fileDir . $fileSmallName;
		$fileBigName			= $fileDir . $fileBigName;
	}

	// pull the original image url, tx_shoppingsystem_product_fetch_url
	$fetchUrl					= $data[ 'tx_shoppingsystem_product_fetch_url' ];

	// save the img to a file locally
	$filename					= rtrim( $fileBigName, $fileBigExt ); 

	if ( ! is_file( $filename ) )
	{
		fetchimage( $fetchUrl, $filename );
	}

	// resize image per small, big filenames
	cbDebug( 'stuff', $uid . '|'. $filename .'|'. $fileBigName .'|'.  $fileSmallName .'|'.  $imageBigSize . '|' .  $imageSmallSize);
	writeresized( $filename, $fileBigName, $imageSmallSize, $imageSmallSize );
	writeresized( $filename, $fileSmallName, $imageBigSize , $imageBigSize);
	// exit();
}

?>
