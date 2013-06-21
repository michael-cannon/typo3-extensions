<?php
include_once( '../../cb_cogs/cb_cogs.config.php' );

$unicodeRegex = "/^\X+/";
$unicodeURegex = "/^\X+/u";
$unicodeText = "你好馬？";

cbDebug( 'unicodeRegex', $unicodeRegex );
cbDebug( 'unicodeURegex', $unicodeURegex );
cbDebug( 'unicodeText', $unicodeText );

$unicodeRegMatch = preg_match( $unicodeRegex, $unicodeText );
cbDebug( 'unicodeRegMatch', $unicodeRegMatch, true );

$unicodeURegMatch = preg_match( $unicodeURegex, $unicodeText );
cbDebug( 'unicodeURegMatch', $unicodeURegMatch, true );

$wordRegex = "/^\w+/";
$wordText = "How are you?";

cbDebug( 'wordRegex', $wordRegex );
cbDebug( 'wordText', $wordText );

$wordRegMatch = preg_match( $wordRegex, $wordText );
cbDebug( 'wordRegMatch', $wordRegMatch, true );

show_source( __FILE__ );
?>
