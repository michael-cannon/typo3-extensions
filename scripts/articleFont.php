<?php

/**
 * HTML font tag remover for Typo3 tt_news items in bodytext.
 *
 * @author Michael Cannon, michael@peimic.com
 * @version $Id: articleFont.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
 */

// grab database login from Typo3 configuration file
// $typo_db_username
// $typo_db_password
// $typo_db_host
// $typo_db
include_once( 'localconf.php' );

// create db conection
$db								= mysql_connect( $typo_db_host
									, $typo_db_username
									, $typo_db_password
								)
								or die( 'Could not connect to database' );

// select database
mysql_select_db( $typo_db )
	or die( 'Could not select database' );

// load tt_news array of uid and bodytext
$sql							= '
	SELECT uid
		, bodytext
	FROM tt_news
	WHERE 1 = 1
';

// get query result
$result							= mysql_query( $sql )
									or die( 'Query failed: ' . mysql_error() );

if ( $result && $data = mysql_fetch_object( $result ) )
{
	// cycle trhough news items
	do
	{
		// if bodytext contains <font..> tags
		if ( preg_match( '#<font#i', $data->bodytext ) )
		{
			// remove them darn tags
			$data->bodytext		= preg_replace( '#</?font[^>]*>#i'
									, ''
									, $data->bodytext
								);

			// then update the news item
			// quote the string for protection
			$update				= "
				UPDATE tt_news
				SET bodytext = '" 
					. mysql_real_escape_string( $data->bodytext )
					. "'
				WHERE uid = " . $data->uid;

			mysql_query( $update )
				or die( 'Update failed: ' . mysql_error() );
		}
	// once cycle complete, all done
	} while ( $data = mysql_fetch_object( $result ) );

	// free up our result set
	mysql_free_result( $result );
}

// close db connection
mysql_close( $db );

?>
