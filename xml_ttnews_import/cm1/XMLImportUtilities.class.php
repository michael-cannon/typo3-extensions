<?php 

/**
Miscellaneous functions relating to the import of RSS items.
Put here to avoid bloating the main xml_ttnews_import plugin.
@author Jaspreet Singh
$Id: XMLImportUtilities.class.php,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $
*/
class XMLImportUtilities {
	
	/**Array of words which, if they occur in the title of a newsitem, 
	the newsitem should be deleted upon import.*/
	var $bannedItemsArray = array();
	
	/**Debug mode on?*/
	var $debug = false;
	
	/**
	Constructor.
	*/
	function XMLImportUtilities() {
		$this->initializeBannedItemsArray();
	}
	
	/**
	Initiatializes the instance variable $this->bannedItemsArray.
	*/
	function initializeBannedItemsArray() {
		
		//try to read the banlists from the news import table
		// query looks like 'select uid, banlist from tx_ccrdfnewsimport '
		
		$columns = 'uid, banlist';
		$table = 'tx_ccrdfnewsimport';
		$where = '';
		$bannedListRows = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTgetRows($columns, $table, $where );
		$bannedListRows = ( is_array( $bannedListRows ) )
			? $bannedListRows
			: array();
		//echo '$bannedListRows';  print_r( $bannedListRows ); //tmp debug
		$bannedItemsArray = array();
		//Add an array entry to the banned array for each row.  The key is the uid.
		//The value is an array of banned words derived from the comma-separated value stored
		//in the field 'banlist'
		foreach( $bannedListRows as $bannedListRow ) {
			
			//echo "\$bannedListRows['banlist']"; echo $bannedListRow['banlist']; //tmp debug
			//print_r($bannedListRow);
			$bannedItemsArray[$bannedListRow['uid']] = empty($bannedListRow['banlist'])
				? array()
				: explode(',',$bannedListRow['banlist'])
				;
			
		}
		//cache in the instance variable.
		$this->bannedItemsArray = $bannedItemsArray;
		//echo '$bannedItemsArray'; print_r($bannedItemsArray); //tmp debug
	}
	
	
	/**
	Fix the TITLE that is returned in the XML from Google News RSS.
	
	The TITLE is returned in the <title> element of the XML.
	
	Before: Confusion Over Sarbanes-Oxley Procedures Delays Reinstatement Of ... - Mondaq News Alerts
	After: Confusion Over Sarbanes-Oxley Procedures Delays Reinstatement Of ...
	
	Static function.
	@param string the TITLE
	@return the modified TITLE
	@author Jaspreet Singh
	*/
	function fixGoogleNewsTitle($title) {
		
		//empty string returns empty
		if (empty($title)) {
			return "";
		}

		$pattern = '#(.*) - .*#';
		//$replacement = '$2';
		$subject = $title;
		$matches = null;
		preg_match ( $pattern, $subject, $matches );
		//the parenthesized subpattern contains the title
		$title1 = ( $matches[1] )
					? $matches[1]
					: $title;
		
		//Might be empty if the url wasn't empty, but was still bad or different.
		//But that just indicates something needs to be fixed (such as the pattern).
		if ($this->debug) { assert(!empty($title1)); }
		
		//Convert from UTF8
		$fixedTitle = $this->cp1252_utf8_to_iso($title1); 
		//$this->blah();
		return $fixedTitle;
		
	}

	/**
	Extract the author from the TITLE that is returned in the XML from Google News RSS.
	
	The TITLE is returned in the <title> element of the XML.
	
	In: Ultimus and National Gypsum to Speak at 2006 National ... - TMCnet
	Out: TMCnet
	
	Static function.
	@param string the TITLE
	@return the author
	@author Jaspreet Singh
	*/
	function extractGoogleNewsAuthor($title) {
		
		//empty string returns empty
		if (empty($title)) {
			return "";
		}

		$pattern = '#.* - (.*)#';
		//$replacement = '$2';
		$subject = $title;
		$matches = null;
		preg_match ( $pattern, $subject, $matches );
		$author = $matches[1]; //the parenthesized subpattern contains the title
		
		//Might be empty if the url wasn't empty, but was still bad or different.
		//But that just indicates something needs to be fixed (such as the pattern).
		if ($this->debug) { assert(!empty($author)); }
		return $author;
		
	}

	/**
	Fix the URL that is returned in the XML from Google News RSS.
	
	The URL is returned in the Link element of the XML.
	The google.com is removed from the front and any cid=* is removed from the back.
	
	Before: http://www.google.com/news/url?sa=T&ct=us/9-0&fd=R&url=http://www.internetadsales.com/modules/news/article.php%3Fstoryid%3D6879&cid=0
	After: http://www.internetadsales.com/modules/news/article.php%3Fstoryid%3D6879
	
	Static function.
	@param string the URL
	@return the modified URL
	@author Jaspreet Singh
	*/
	function fixGoogleNewsURL($url) {
		
		//empty string returns empty
		if (empty($url)) {
			return "";
		}

		$pattern = '#^.+(http.+)(&cid=.+)$|^.+(http.+)$#';
		$subject = $url;
		//$fixedURL = preg_replace ( $pattern, $replacement, $subject );
		//		$matches = preg_match ( $pattern, $subject );
		$matches = null;
		preg_match ( $pattern, $subject, $matches );
		$url1 = $matches[1]; //the parenthesized subpattern contains the URL.
		if (empty($url1)) {
			$url1 = $matches[3];
		}
		if (empty($url1)) {
			$url1 = $url;
		}
		//echo "matches: "; print_r($matches);
		
		//Might be empty if the url wasn't empty, but was still bad or different.
		//But that just indicates something needs to be fixed (such as the pattern).
		if ($this->debug) { assert(!empty($url1)); }
		
		//Convert from UTF8
		$fixedURL = $this->cp1252_utf8_to_iso($url1);
		//$this->blah();
		//echo $this->htmlToPlainText("<b>blah</b>");
		//exit();
		//
		return $fixedURL;
		
	}
	
	/**
	Fix the Description that is returned in the XML from Google News RSS.
	
	This is returned in the Description element of the XML.
	
	The function removes the title and date from the description as well as removing
	HTML.
	
	Input: <br><table border=0 width= valign=top cellpadding=2 cellspacing=7><tr><td valign=top><a href="http://www.google.com/news/url?sa=T&ct=us/9-0&fd=R&url=http://www.internetadsales.com/modules/news/article.php%3Fstoryid%3D6879&cid=0">Webmethods Advances Composite Application Development With Latest <b>...</b></a><br><font size=-1><font color=#6f6f6f>Internet Ad Sales (press release),&nbsp;Sweden&nbsp;-</font> <nobr>Feb 20, 2006</nobr></font><br><font size=-1><b>...</b> As a result, it can be used to address the user interaction requirements associated with a service-oriented architecture (<b>SOA</b>) in a manner consistent with the <b>...</b>  </font><br></table>
	Output: ... As a result, it can be used to address the user interaction requirements associated with a service-oriented architecture (SOA) in a manner consistent with the ...  
	
	Static function.
	@param string Raw description
	@return The cleaned up description
	@author Jaspreet Singh
	*/
	function fixGoogleNewsDescription($description) {
		
		//empty string returns empty
		if (empty($description)) {
			return "";
		}
		
		//echo 'fixGoogleNewsDescription'; // debug
		
		//We want just the teaser text and nothing else.
		//The teaser text isn't really marked.  It's just comes after the second fontsize element.
		//Although we remove the HTML below, the font ending and table ending are also removed just
		//in case we want to use the text as HTML later.
		$pattern = '#^.+<table.+<font size=-1>.+<font size=-1>(.+)</font><br></table>$#';
		$subject = $description;
		//$matches = null;
		preg_match ( $pattern, $subject, $matches );
		$teaserText = $matches[1]; //the parenthesized subpattern contains the teaser text.
		
		if (false) {
			echo "matches";
			print_r($matches);
			var_dump($teaserText);
		}
		if ($this->debug) { assert(!empty($teaserText)); }
		$teaserTextPlain = $this->htmlToPlainText($teaserText);
		if ($this->debug) { assert(!empty($teaserTextPlain)); }
		
		if (false) var_dump($teaserTextPlain);
		
		return $teaserTextPlain;
	}
	
	/**
	Converts HTML to plain text.
	Taken with thanks from PHP help.
	Some modifications.
	
	Static function.
	
	@author Jaspreet Singh
	@param string HTML
	@return Plain text
	*/
	function htmlToPlainText($html) {
		
		// $html should contain an HTML document.
		// This will remove HTML tags, javascript sections
		// and white space. It will also convert some
		// common HTML entities to their text equivalent.
		
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
						"'<[\/\!]*?[^<>]*?>'si",           // Strip out HTML tags
						"'([\r\n])[\s]+'",                 // Strip out white space
						"'&(quot|#34);'i",                 // Replace HTML entities
						"'&(amp|#38);'i",
						"'&(lt|#60);'i",
						"'&(gt|#62);'i",
						"'&(nbsp|#160);'i",
						"'&(iexcl|#161);'i",
						"'&(cent|#162);'i",
						"'&(pound|#163);'i",
						"'&(copy|#169);'i",
						"'&#(\d+);'e");                    // evaluate as php
		
		$replace = array ("",
						 "",
						 "\\1",
						 "\"",
						 "&",
						 "<",
						 ">",
						 " ",
						 chr(161),
						 chr(162),
						 chr(163),
						 chr(169),
						 "chr(\\1)");
		
		$text = preg_replace($search, $replace, $html);
		
		$decodedText = html_entity_decode($text);
		
		return $decodedText;
	}
	
	
	/**
	Determines whether this news item should be deleted upon import.
	Some items shouldn't be shown (according to a set of criteria given by BSG).
	They are set as deleted=1.
	
	Static function.
	
	@author Jaspreet Singh
	@param string The title of the newsitem
	@param int The uid of the feed (from table tx_ccrdfnewsimport) 
	@return string 1 if true; 0 if false
	*/
	function shouldItemBeDeletedUponImport( $title, $feedID ) {
		
		$shouldBeDeleted = '0';
		
		//$banList = 'Oracle,Microsoft,EDS,Accenture,Bearingpoint,Metastorm,ultimus,iway software,sap,lanner,tibco,software ag,ids scheer,fuego,computer associates'; 
		//$banArray = explode(',',$banList);
		$banArray = $this->bannedItemsArray;
		//echo $title;
		//print_r($banArray);
		
		if ( ! isset( $banArray[$feedID] ) )
		{
			return $shouldBeDeleted;
		}

		foreach($banArray[$feedID] as $bannedItem) {
			$match = stristr($title,$bannedItem);
			if (false!=stristr($title,$bannedItem)) {
				//echo "\$title: $title \$bannedItem: $bannedItem";
				//var_dump($match);
				if (false!=$match) {
					$shouldBeDeleted = '1';
					//echo "$title should be deleted";
					break;
				}
			}
		
		return $shouldBeDeleted;
		}

	}
	
	/**
	For converting between Windows text and  UTF8.
	Taken with thanks from PHP help.
	
	@author dobersch at gmx dot net
	*/
	var $cp1252_map = array(
		"\xc2\x80" => "\xe2\x82\xac", // EURO SIGN 
		"\xc2\x82" => "\xe2\x80\x9a", // SINGLE LOW-9 QUOTATION MARK 
		"\xc2\x83" => "\xc6\x92",    // LATIN SMALL LETTER F WITH HOOK 
		"\xc2\x84" => "\xe2\x80\x9e", // DOUBLE LOW-9 QUOTATION MARK 
		"\xc2\x85" => "\xe2\x80\xa6", // HORIZONTAL ELLIPSIS 
		"\xc2\x86" => "\xe2\x80\xa0", // DAGGER 
		"\xc2\x87" => "\xe2\x80\xa1", // DOUBLE DAGGER 
		"\xc2\x88" => "\xcb\x86",    // MODIFIER LETTER CIRCUMFLEX ACCENT 
		"\xc2\x89" => "\xe2\x80\xb0", // PER MILLE SIGN 
		"\xc2\x8a" => "\xc5\xa0",    // LATIN CAPITAL LETTER S WITH CARON 
		"\xc2\x8b" => "\xe2\x80\xb9", // SINGLE LEFT-POINTING ANGLE QUOTATION 
		"\xc2\x8c" => "\xc5\x92",    // LATIN CAPITAL LIGATURE OE 
		"\xc2\x8e" => "\xc5\xbd",    // LATIN CAPITAL LETTER Z WITH CARON 
		"\xc2\x91" => "\xe2\x80\x98", // LEFT SINGLE QUOTATION MARK 
		"\xc2\x92" => "\xe2\x80\x99", // RIGHT SINGLE QUOTATION MARK 
		"\xc2\x93" => "\xe2\x80\x9c", // LEFT DOUBLE QUOTATION MARK 
		"\xc2\x94" => "\xe2\x80\x9d", // RIGHT DOUBLE QUOTATION MARK 
		"\xc2\x95" => "\xe2\x80\xa2", // BULLET 
		"\xc2\x96" => "\xe2\x80\x93", // EN DASH 
		"\xc2\x97" => "\xe2\x80\x94", // EM DASH 
		"\xc2\x98" => "\xcb\x9c",    // SMALL TILDE 
		"\xc2\x99" => "\xe2\x84\xa2", // TRADE MARK SIGN 
		"\xc2\x9a" => "\xc5\xa1",    // LATIN SMALL LETTER S WITH CARON 
		"\xc2\x9b" => "\xe2\x80\xba", // SINGLE RIGHT-POINTING ANGLE QUOTATION
		"\xc2\x9c" => "\xc5\x93",    // LATIN SMALL LIGATURE OE 
		"\xc2\x9e" => "\xc5\xbe",    // LATIN SMALL LETTER Z WITH CARON 
		"\xc2\x9f" => "\xc5\xb8"      // LATIN CAPITAL LETTER Y WITH DIAERESIS
		);
		
	
	/**
	Converts Windows text to UTF8.
	Taken with thanks from PHP help.
	
	@author dobersch at gmx dot net
	@param string HTML
	@return UTF8 text
	*/
	function cp1252_to_utf8($str) {
		// I find this name a little misleading because the result won't be valid UTF8 data
		 
		return  strtr(utf8_encode($str), $this->cp1252_map);
	}
	/**
	Converts UTF8 to Windows text.
	Taken with thanks from PHP help.
	
	@author dobersch at gmx dot net
	@param string HTML
	@return UTF8 text
	*/
	function cp1252_utf8_to_iso($str) { // the other way around...
		return  utf8_decode( strtr($str, array_flip($this->cp1252_map)) );
	}

}
?>
