<?php

/**
Writes logging messages to a Mysql table for debugging.

The reason Typo3 sys_log isn't being used is that its message field is only a 
TEXT(255) and we need more space than that.

The table for logging should be something like this: 

CREATE TABLE `tmp_debuglog` (
`uid` INT NOT NULL AUTO_INCREMENT ,
`feuid` INT( 11 ) DEFAULT '0' NOT NULL ,
`code1` INT NOT NULL ,
`code2` INT NOT NULL ,
`short_message` VARCHAR( 255 ) NOT NULL ,
`long_message` TEXT NOT NULL ,
PRIMARY KEY ( `uid` ) ,
INDEX ( `code1` , `code2` )
) COMMENT = 'Created for debugging by js';

How to use:
$logger		= new DebugLogger(); 
$logger->logEntry($newId, 0, 0, 'save()', $this->currentArr);

@author Jaspreet Singh
$Id: DebugLogger.class.php,v 1.1.1.1 2010/04/15 10:04:04 peimic.comprock Exp $
*/
class DebugLogger {

	/** Default name of the logging table.  Change if necessary by setting it from the caller. */
	var $logTable 	= 'tmp_debuglog';
	
	/** Typo3 db link.  Should be set before using the class or the default $GLOBALS[ 'TYPO3_DB' ] is used. */
	var $db			= null;
	
	/** Whether debug mode is on.  If on, echoes additional info to screen. */
	var $debug 		= false;
	
	/**
	Writes a single log entry.
	@return void
	*/
	function logEntry( $feuid = 0, $code1, $code2, $shortMessage, $longMessage) {
		
		//assert( $db != null );
		if ($this->debug) {
			echo "function logEntry( $feuid = 0, $code1, $code2, $shortMessage, $longMessage) {";
		}
		
		//print out the array if necessary
		$longMessage2 = is_array($longMessage)
			? print_r( $longMessage, true)
			: $longMessage;
			;

		$shortMessage = mysql_escape_string( $shortMessage );
		$longMessage2 = mysql_escape_string( $longMessage2 );
		
		$query = "INSERT INTO $this->logTable
			(feuid, code1, code2, short_message, long_message)	
			VALUES ($feuid, $code1, $code2, '$shortMessage', '$longMessage2')
		";
		
		$GLOBALS[ 'TYPO3_DB' ]->sql(TYPO3_db, $query);
		
	}
	
	function appendMessage( $message ) {
		
	}
	
}

?>
