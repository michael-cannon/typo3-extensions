<?php

/**
* user created scripts for TypoScript operations.
*
* @author Michael Cannon <michael@peimic.com>
* @version $Id: user_scripts.php,v 1.1.1.1 2010/04/15 10:04:13 peimic.comprock Exp $
*/


function user_print()
{
	$host = ( isset($_SERVER['SERVER_NAME']) )
		? $_SERVER['SERVER_NAME']
		: '';

	$uri = ( isset($_SERVER['REQUEST_URI']) )
		? $_SERVER['REQUEST_URI']
		: '';

	$baseurl = 'http://' . $host . $uri;

	$url = url_rewrite($baseurl);

	// leave this alone
	$url = "<a href='$url' target='_cb'>";

	return $url;
}


// function: url_rewrite()  params: $url
// This function takes in a url and adds the typo3 type 98 to the url in order
// to show the print view.


function url_rewrite($url)
{
//echo_ln ("old: " . $url . "<br />");

// parse url to see if there is already a query string.
$url_parts = parse_url($url);

$parts = array(
	'scheme',
	'host',
	'user',
	'pass',
	'path',
	'query',
	'fragment'
);

foreach ( $parts AS $key => $value )
{
	$$value = ( isset($url_parts[$value] ) )
		? $url_parts[$value]
		: '';
}

// find the file extension in the path.
	$ext = ereg_replace("^.+\\.([^.]+)$", "\\1", $path);

// if a query string already exists, add the type=98 and get out.
if ('' != $query && 'html' != $ext)
{
	$query = $query . "&type=98";
	$new_url = $scheme . "://" . $host . $path . "?" . $query;
}
else
{
	
	// build up most of the new url.
	$new_url = $scheme . "://". $host;
	
	// run the file extension through a case and add the type=98 according to
	// the extension.
	switch ($ext) 
	{
		case 'html';
			$reg = '/.html$/';
			$new_ext = '/print.html';
			$new_url = $new_url . preg_replace($reg,$new_ext,$path); 
			$new_url .= ('' != $query)
				? "?" .  $query
				: '';
			break;
		case 'php';
			$reg = '/.php$/';
			$new_ext = '.php?type=98';
			$new_url = $new_url . preg_replace($reg,$new_ext,$path);
			break;
		case '/';
			$path = $path . "?type=98";
			$new_url = $new_url . $path;
			break;
		case '';
			$path = "/?type=98";
			$new_url = $new_url . $path;
			break;
			
	}
	
}

//before going live, change this to return the url and not echo it.
return $new_url;
}


function user_bloomberg()
{
	// from the form we get two variables via a get method
	// type: query symbol

	$query_type = ( isset($_GET['query_type']) )
		? $_GET['query_type']
		: 'symbol';

	$url = ( 'quote' == $query_type )
		? 'http://quote.bloomberg.com/apps/quote?ticker='
		:
'http://search.bloomberg.com/search97cgi/s97r_cgi?start_ndx=1&method=CONTAINS&exchange=US&type1=equity&type2=mutualfund&query=';

	$ticker = ( isset($_GET['ticker']) )
		? preg_replace('/[^[:alnum:]]/', '' , $_GET['ticker']) 
		: '';

	return $url . $ticker;
}



function user_geturl()
{
	// look at cb_common/cb_array.php's get_SERVER()
	$host = ( isset($_SERVER['SERVER_NAME']) )
		? $_SERVER['SERVER_NAME']
		: '';

	$uri = ( isset($_SERVER['REQUEST_URI']) )
		? $_SERVER['REQUEST_URI']
		: '';

		$reg = '/([?|&]type=\d*)|(\.98)/';
		$url = "http://" . $host . preg_replace($reg, '', $uri);

	return $url;
}



/**
 * Creates a new line string out of $variable.
 *
 * @param string $msg to be outputted, ex: 'Now is the time of'
 * @param boolean $var_dump use PHP's var_dump() instead of print_r()
 * @return void
 */
function echo_ln($msg = '', $var_dump = false)
{
	// get the variables using PHP's output buffering and own human readable
	// print functions
	ob_start();

	( !$var_dump ) 
		? print_r($msg) 
		: var_dump($msg);

	$msg = ob_get_contents();
	
	ob_end_clean();

	// turn obnoxious 8 space tabs into 3 spaces
	$msg = preg_replace('/ {8}/', '&nbsp;&nbsp;&nbsp;', $msg);
	$msg = "<pre>$msg</pre>";

	echo $msg;
	flush();
}

/**
 * Raw url encoded tiplink url
 *
 * @return string
 */
function user_urlencode()
{
	$url = t3lib_div::getIndpEnv("TYPO3_REQUEST_URL");
	$url = rawurlencode($url);
// echo_ln($url);

	return $url;
}

function qp_enc( $input = "", $line_max = 76, $space_conv = false ) 
{
	$hex = array('0','1','2','3','4','5','6','7','8','9',
'A','B','C','D','E','F');
	$lines = preg_split("/(?:\r\n|\r|\n)/", $input);
	$eol = "\r\n";
	$escape = "=";
	$output = "";

	while( list(, $line) = each($lines) ) 
	{
		// $line = rtrim($line); 
		// remove trailing white space -> no =20\r\n necessary
		$linlen = strlen($line);
		$newline = "";

		for($i = 0; $i < $linlen; $i++) 
		{
			$c = substr( $line, $i, 1 );
			$dec = ord( $c );

			if ( ( $i == 0 ) && ( $dec == 46 ) ) 
			{ 
				// convert first point in the line into =2E
				$c = "=2E";
			}

			if ( $dec == 32 ) 
			{
				if ( $i == ( $linlen - 1 ) ) 
				{ 
					// convert space at eol only
					$c = "=20";
				} 
				
				else if ( $space_conv ) 
				{
					$c = "=20";
				}
			} 

			elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) 
			{ 
				// always encode "\t", which is *not* required
				$h2 = floor($dec/16);
				$h1 = floor($dec%16);
				$c = $escape.$hex["$h2"].$hex["$h1"];
			}

			if ( (strlen($newline) + strlen($c)) >= $line_max ) 
			{ 
				// CRLF is not counted
				$output .= $newline.$escape.$eol; 
				// soft line break; " =\r\n" is okay
				$newline = "";
				// check if newline first character will be point or not

				if ( $dec == 46 ) 
				{
					$c = "=2E";
				}
			}

			$newline .= $c;
		} // end of for

		$output .= $newline.$eol;
	} // end of while

	return trim($output);
}

function user_todaysDate ()
{   
	return date( "l, F j" );
}

function user_loginRequestUri ()
{
	$request					= $_SERVER[ 'REQUEST_URI' ];

	if ( preg_match( '#logintype=logout#i', $request ) )
	{
		$request				= 'http://' . $_SERVER[ 'HTTP_HOST' ];
	}

	return $request;
}


/**
 * Log visitor users out.
 *
 * @param string underscore separated visitor groups 5_6_7 with colon separated string underscore separated pages to ignore
 * @return boolean true if visitor and logged out
 */
function user_usergroupLogout ( $visitorCheck = '' )
{
	$groupPage					= explode( ':', $visitorCheck );

	// visitor usergroup
	$searchForGroups			= preg_replace( '#_#', '|', $groupPage[ 0 ] );

	// pages to ignore running on
	$searchForPages				= preg_replace( '#_#', '|', $groupPage[ 1 ] );

	// auto logout visitors 
	if ( $visitorCheck 
		&& preg_match( '#\b(' . $searchForGroups . ')\b#'
			, $GLOBALS['TSFE']->gr_list
		)
		&& ! preg_match( '#\b(' . $searchForPages . ')\b#'
			, $GLOBALS['TSFE']->id
		)
	)
	{
		$GLOBALS['TSFE']->fe_user->logoff();
		$GLOBALS['TSFE']->gr_list	= '0,-1';
		$GLOBALS['TSFE']->loginUser	= 0;

		return true;
	}

	return false;
}


/**
 * Put in template
 *
 * @param string conference_req_conf_ 9_19_6/2/2006
 * @return boolean
 */
function user_conferenceAccessUpdater ( $conferenceCheck = '' )
{
	// cbDebug( 'tsfe' , $GLOBALS['TSFE']);
	// 9_19_6/2/2006
	// 9 chicago access
	// 19 conference request
	// 20060602 date to end access
	$searchForGroup			= explode( '_', $conferenceCheck );

	// check for need for updating
	// make sure user is logged in
	if ( $searchForGroup[ 0 ] 
		&& ! preg_match( '#\b' . $searchForGroup[ 0 ] . '\b#'
			, $GLOBALS['TSFE']->gr_list
		)
		&& $GLOBALS['TSFE']->loginUser
		&& $GLOBALS['TSFE']->fe_user->user['uid']
	)
	{
		// update current user group settings in db
		$uid					= $GLOBALS['TSFE']->fe_user->user['uid'];
		$usergroup				= $GLOBALS['TSFE']->fe_user->user['usergroup'];
		$username				= $GLOBALS['TSFE']->fe_user->user['username'];
		$password				= $GLOBALS['TSFE']->fe_user->user['password'];

		// grant access, plus mark as requested access
		$usergroup				.= ',' . $searchForGroup[ 0 ]
									;
		$GLOBALS['TSFE']->gr_list	.= ',' . $searchForGroup[ 0 ]
									;

		if ( ! preg_match( '#\b' . $searchForGroup[ 1 ] . '\b#', $usergroup ) )
		{
			$usergroup					.= ',' . $searchForGroup[ 1 ]
											;
			$GLOBALS['TSFE']->gr_list	.= ',' . $searchForGroup[ 1 ]
											;
		}

		// update endtime
		$complimentary			= 1;
		$proGuest				= 4;
		$endtime				= '';

		if ( preg_match( '#\b' . $complimentary . '\b#', $usergroup ) )
		{
			$usergroup			= preg_replace( '#\b' . $complimentary . '\b#'
									, $proGuest
									, $usergroup
								);
		}

		$fields_values			= array(
									'usergroup' => $usergroup
								);

		if ( preg_match( '#'.'\b' . $complimentary . '\b'. '|' . '\b'. $proGuest . '\b'.'#'
			, $usergroup )
		)
		{
			$endtime			= $searchForGroup[ 2 ];
			$endtime			= strtotime( $endtime . ' 23:59:59 EST' );
			$fields_values['endtime']	= $endtime;
		}

		$table					= 'fe_users';
		$where					= "uid = $uid";
		
		$query					= $GLOBALS['TYPO3_DB']->UPDATEquery($table
									, $where
									, $fields_values
								);

		$result					= $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table
									, $where
									, $fields_values
								);

		// redirect to current page again for Typo3 to reload user with correct
		// access information
		$location				= 'http://'
									. $_SERVER[ 'HTTP_HOST' ]
									. $_SERVER[ 'REQUEST_URI' ];

		header( 'Location: ' . $location );
	}

	return false;
}


/**
 * Put in template
 *
 * request type usergroup
 * days to allow
 * page id to redirect to
 *
 * @param string 29_60_805
 * @return boolean
 */
function user_trialAccessUpdater ( $trialCheck = '' )
{
	$complimentary				= 1;
	$proGuest					= 4;

	// cbDebug( 'tsfe' , $GLOBALS['TSFE']);
	// 29_60_805
	// 29 requested via surveygizmo
	// 60 days access
	// 805 page id of redirect
	$trialCheckFields			= explode( '_', $trialCheck );

	// check for need for updating
	// make sure user is logged in
	// if user already has trialCheckFields0 then no need to modify further
	if ( $trialCheckFields[ 0 ] 
		&& ! preg_match( '#\b' . $trialCheckFields[ 0 ] . '\b#'
			, $GLOBALS['TSFE']->gr_list
		)
		&& $GLOBALS['TSFE']->loginUser
		&& $GLOBALS['TSFE']->fe_user->user['uid']
	)
	{
		// update current user group settings in db
		$uid					= $GLOBALS['TSFE']->fe_user->user['uid'];
		$usergroup				= $GLOBALS['TSFE']->fe_user->user['usergroup'];
		$username				= $GLOBALS['TSFE']->fe_user->user['username'];
		$password				= $GLOBALS['TSFE']->fe_user->user['password'];
		$endtime				= $GLOBALS['TSFE']->fe_user->user['endtime'];

		if ( ! $endtime )
		{
			$endtime			= time();
		}

		// grant access, plus mark as requested access
		$usergroup				.= ',' . $trialCheckFields[ 0 ]
									;
		$GLOBALS['TSFE']->gr_list	.= ',' . $trialCheckFields[ 0 ]
									;

		// update endtime
		// number of days * 60 seconds * 60 minutes * 24 hours/day
		$endtime				+= $trialCheckFields[ 1 ] * 60 * 60 * 24;

		// set proGuest if only complimentary member
		if ( preg_match( '#\b' . $complimentary . '\b#', $usergroup ) )
		{
			$usergroup			= preg_replace( '#\b' . $complimentary . '\b#'
									, $proGuest
									, $usergroup
								);
		}

		$fields_values			= array(
									'usergroup' => $usergroup
									, 'endtime' => $endtime
								);

		$table					= 'fe_users';
		$where					= "uid = $uid";
		
		$query					= $GLOBALS['TYPO3_DB']->UPDATEquery($table
									, $where
									, $fields_values
								);

		$result					= $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table
									, $where
									, $fields_values
								);

		// redirect to current page again for Typo3 to reload user with correct
		// access information
		$location				= 'http://'
									. $_SERVER[ 'HTTP_HOST' ]
									. '/index.php?id='
									. $trialCheckFields[ 2 ];

		header( 'Location: ' . $location );
	}

	return false;
}

?>
