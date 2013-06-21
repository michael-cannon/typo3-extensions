<?php
/** Everything after ? on the url line */
$loadUrl = $_SERVER [ "QUERY_STRING" ];

if ( ! substr_count ( strtolower ( $loadUrl ), "google.com" ) > 0 )
{
	exit;
} 

/** Loading the content of the thing we got */
$loadUrl = urldecode($loadUrl);
$content = file_get_contents( $loadUrl );

/** Using it under test conditions on both http and https
connections, so we have to select here */
$scheme = ($_SERVER [ "SERVER_PORT" ] == 80 ? "http" : "https");

/** Building redirect path */
$wwwpath = $scheme."://".$_SERVER [ "HTTP_HOST" ].$_SERVER [ "SCRIPT_NAME" ]."?";

/** Replacing the content */
$content = str_replace ( "http://", $wwwpath."http://", $content );

/** Output */
echo $content;
?>
