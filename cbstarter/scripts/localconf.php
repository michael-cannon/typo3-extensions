<?php

/* 
 * localconf.php helper script
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: localconf.php,v 1.1.1.1 2010/04/15 10:03:15 peimic.comprock Exp $
 */

$TYPO3_CONF_VARS['BE']['accessListRenderMode'] = 'checkbox';
$TYPO3_CONF_VARS['BE']['elementVersioningOnly'] = '1';
$TYPO3_CONF_VARS['BE']['forceCharset'] = 'utf-8';
$TYPO3_CONF_VARS['BE']['interfaces'] = 'backend,frontend';
$TYPO3_CONF_VARS['BE']['lockIP'] = '0';
$TYPO3_CONF_VARS['BE']['maxFileSize'] = '100000';
$TYPO3_CONF_VARS['BE']['sessionTimeout'] = '32400'; // 9 hours admin access
$TYPO3_CONF_VARS['BE']['warning_mode'] = '2';
$TYPO3_CONF_VARS['EXT']['extConf']['captcha'] = 'a:21:{s:6:"useTTF";s:1:"1";s:8:"imgWidth";s:3:"100";s:9:"imgHeight";s:2:"30";s:12:"captchaChars";s:1:"4";s:9:"noNumbers";s:1:"0";s:4:"bold";s:1:"0";s:7:"noLower";s:1:"0";s:7:"noUpper";s:1:"0";s:13:"letterSpacing";s:1:"4";s:5:"angle";s:2:"20";s:5:"diffx";s:1:"0";s:5:"diffy";s:1:"2";s:4:"xpos";s:1:"3";s:4:"ypos";s:1:"4";s:6:"noises";s:1:"6";s:9:"backcolor";s:7:"#f4f4f4";s:9:"textcolor";s:7:"#000000";s:11:"obfusccolor";s:7:"#b0b0b0";s:8:"fontSize";s:2:"16";s:8:"fontFile";s:0:"";s:12:"excludeChars";s:14:"gijloGIJLO0169";}';	// Modified or inserted by TYPO3 Extension Manager. 
$TYPO3_CONF_VARS['FE']['forceCharset'] = 'utf-8';
$TYPO3_CONF_VARS['FE']['lifetime'] = '31536000'; //for frontend user
$TYPO3_CONF_VARS['FE']['lockIP'] = '0';
$TYPO3_CONF_VARS['FE']['permalogin'] = '1';
$TYPO3_CONF_VARS['GFX']['png_truecolor'] = '1';
$TYPO3_CONF_VARS['SYS']['USdateFormat'] = '1';
$TYPO3_CONF_VARS['SYS']['ddmmyy'] = 'm/d/Y';
$TYPO3_CONF_VARS['SYS']['displayErrors'] = '2';
$TYPO3_CONF_VARS['SYS']['hhmm'] = 'g:i a';
$TYPO3_CONF_VARS['SYS']['loginCopyrightShowVersion'] = '1';
$TYPO3_CONF_VARS['SYS']['maxFileNameLength'] = '255';
$TYPO3_CONF_VARS['SYS']['recursiveDomainSearch'] = '1';
$TYPO3_CONF_VARS['SYS']['serverTimeZone'] = '0';
$TYPO3_CONF_VARS['SYS']['setDBinit'] = 'SET NAMES utf8;';
$TYPO3_CONF_VARS['SYS']['textfile_ext'] = 'txt,pdf,html,htm,css,inc,php,php3,tmpl,js,sql';

// MLC 20070611 if you want to enable a group, change the false to true if
// there's something in a group you want to disable, comment it out with // like
// the beginning of these lines

// MLC modify as needed for file and directory permissions
if ( false )
{
	$TYPO3_CONF_VARS['BE']['fileCreateMask'] = '0664';
	$TYPO3_CONF_VARS['BE']['folderCreateMask'] = '0775';
}

// MLC if you have a specific 404 page, enable and modify the following
if ( false )
{
	$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = '/404.html';
}

// MLC multi-byte content; change to false for older US English only sites
// Leave false as not really need anymore for TYPO3 4.2 and above
if ( false )
{
	$TYPO3_CONF_VARS['SYS']['UTF8filesystem'] = 'true';

	// not needed if your database is already running UTF-8
	// $TYPO3_CONF_VARS['SYS']['multiplyDBfieldSize'] = '2';

	// For GIFBUILDER support
	// Set it to 'iconv' or 'mbstring'
	$TYPO3_CONF_VARS['SYS']['t3lib_cs_convMethod'] = 'iconv';

	// For 'iconv' support you need PHP 5!
	$TYPO3_CONF_VARS['SYS']['t3lib_cs_utils'] = 'iconv';
}

// company support details 
if ( false )
{
	// admin login warning email
	$TYPO3_CONF_VARS['BE']['warning_email_addr'] = 'server@peimic.com';
	$TYPO3_CONF_VARS['SYS']['loginCopyrightWarrantyProvider'] = 'Peimic.com';
	$TYPO3_CONF_VARS['SYS']['loginCopyrightWarrantyURL'] = 'http://www.peimic.com/';
}

// graphics settings
if ( false )
{
	$TYPO3_CONF_VARS['GFX']["im"] = '1';
	$TYPO3_CONF_VARS['GFX']["im_path"] = '/usr/bin/';
	$TYPO3_CONF_VARS['GFX']["im_path_lzw"] = '/usr/bin/';
	$TYPO3_CONF_VARS['GFX']['TTFdpi'] = '96';
	$TYPO3_CONF_VARS['GFX']['im_combine_filename'] = 'composite';
	$TYPO3_CONF_VARS['GFX']['im_version_5'] = 'im5';
}

// curl and filepath helpers
if ( false )
{
	$TYPO3_CONF_VARS['BE']['unzip_path'] = '/usr/bin/unzip';
	$TYPO3_CONF_VARS['SYS']['binPath'] = '/usr/local/bin,/usr/bin';
	$TYPO3_CONF_VARS['SYS']['curlUse'] = '1';
}

// tidy for Tidy cleans the HTML-code for nice display
if ( false )
{
	$TYPO3_CONF_VARS['FE']['tidy'] = '1';
	$TYPO3_CONF_VARS['FE']['tidy_option'] = 'output';  // all, cached, output
	$TYPO3_CONF_VARS['FE']['tidy_path'] = 'tidy -i --quiet true --tidy-mark true -wrap 0 -utf8 --output-xhtml true';
}

// development effort helpers
if ( false )
{
	$TYPO3_CONF_VARS['SYS']['devIPmask'] = '192.168.*,127.0.0.1,71.255.125.178';
	$TYPO3_CONF_VARS['SYS']['displayErrors'] = '1';
	$TYPO3_CONF_VARS['SYS']['sqlDebug'] = '1';	
	$TYPO3_CONF_VARS['SYS']['systemLog'] = 'error_log,,1';
	$TYPO3_CONF_VARS['SYS']['systemLogLevel'] = '1';
}

?>
