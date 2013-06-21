<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.2 2011/11/16 19:27:21 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);	// unserializing the configuration so we can use it here:
if ($_EXTCONF['setPageTSconfig'])	{
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:icbase/pageTSconfig.txt">');
}

if ($_EXTCONF['setUserTSconfig']) {
	t3lib_extMgm::addUserTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:icbase/userTSconfig.txt">');
}


$TYPO3_CONF_VARS['BE']['accessListRenderMode'] = 'checkbox';
$TYPO3_CONF_VARS['BE']['elementVersioningOnly'] = '1';
$TYPO3_CONF_VARS['BE']['explicitADmode'] = 'explicitAllow';
$TYPO3_CONF_VARS['BE']['forceCharset'] = 'utf-8';
$TYPO3_CONF_VARS['BE']['interfaces'] = 'backend,frontend';
$TYPO3_CONF_VARS['BE']['maxFileSize'] = '100000';
$TYPO3_CONF_VARS['BE']['sessionTimeout'] = '32400'; // 9 hours admin access
$TYPO3_CONF_VARS['BE']['unzip_path'] = '/usr/bin/unzip';
$TYPO3_CONF_VARS['FE']['disableNoCacheParameter'] = '1';
$TYPO3_CONF_VARS['FE']['forceCharset'] = 'utf-8';
$TYPO3_CONF_VARS['FE']['lifetime'] = '31536000';
$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = '/page-not-found/';
$TYPO3_CONF_VARS['FE']['permalogin'] = '1';
$TYPO3_CONF_VARS['GFX']["im"] = '1';
$TYPO3_CONF_VARS['GFX']['im_combine_filename'] = 'composite';
$TYPO3_CONF_VARS['GFX']['im_imvMaskState'] = '1';
$TYPO3_CONF_VARS['GFX']["im_path_lzw"] = '/usr/bin/';
$TYPO3_CONF_VARS['GFX']["im_path"] = '/usr/bin/';
$TYPO3_CONF_VARS['GFX']['im_v5effects'] = '1';
$TYPO3_CONF_VARS['GFX']['im_version_5'] = 'im6';
$TYPO3_CONF_VARS['GFX']['png_truecolor'] = '1';
$TYPO3_CONF_VARS['GFX']['TTFdpi'] = '96';
$TYPO3_CONF_VARS['SYS']['binPath'] = '/usr/local/bin,/usr/bin';
$TYPO3_CONF_VARS['SYS']['compat_version'] = '4.4';
$TYPO3_CONF_VARS['SYS']['curlUse'] = '1';
$TYPO3_CONF_VARS['SYS']['ddmmyy'] = 'm/d/Y';
$TYPO3_CONF_VARS['SYS']['hhmm'] = 'g:i a';
$TYPO3_CONF_VARS['SYS']['loginCopyrightShowVersion'] = '1';
$TYPO3_CONF_VARS['SYS']['loginCopyrightWarrantyProvider'] = 'TYPO3 Vagabond';
$TYPO3_CONF_VARS['SYS']['loginCopyrightWarrantyURL'] = 'http://www.typo3vagabond/';
$TYPO3_CONF_VARS['SYS']['maxFileNameLength'] = '255';
$TYPO3_CONF_VARS['SYS']['recursiveDomainSearch'] = '1';
$TYPO3_CONF_VARS['SYS']['serverTimeZone'] = '0';
$TYPO3_CONF_VARS['SYS']['setDBinit'] = 'SET NAMES utf8;';
$TYPO3_CONF_VARS['SYS']['textfile_ext'] = 'txt,pdf,html,htm,css,inc,php,php3,tmpl,js,sql';
$TYPO3_CONF_VARS['SYS']['USdateFormat'] = '1';
$TYPO3_CONF_VARS['EXT']['extConf']['kb_tv_migrate'] = 'a:1:{s:7:"foldOut";s:1:"0";}';	// Modified or inserted by TYPO3 Extension Manager. 


// MLC 20070611 if you want to enable a group, change the false to true if
// there's something in a group you want to disable, comment it out with // like
// the beginning of these lines

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
	$TYPO3_CONF_VARS['SYS']['devIPmask'] = '192.168.*,127.0.0.1,114.44.*';
	$TYPO3_CONF_VARS['SYS']['displayErrors'] = '1';
	$TYPO3_CONF_VARS['SYS']['sqlDebug'] = '1';	
	$TYPO3_CONF_VARS['SYS']['systemLog'] = 'error_log,,1';
	$TYPO3_CONF_VARS['SYS']['systemLogLevel'] = '1';
}

//t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_icbase_pi1.php','_pi1','list_type',1);
?>
