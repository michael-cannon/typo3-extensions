<?php
# TYPO3 CVS ID: $Id: ext_localconf.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TYPO3_CONF_VARS['BE']['disable_exec_function'] = '1';
$TYPO3_CONF_VARS['BE']['enabledBeUserIPLock'] = '1';
$TYPO3_CONF_VARS['BE']['IPmaskList'] = '';
$TYPO3_CONF_VARS['BE']['lockBeUserToDBmounts'] = '1';
$TYPO3_CONF_VARS['BE']['lockIP'] = '4';
$TYPO3_CONF_VARS['BE']['lockRootPath'] = '';
$TYPO3_CONF_VARS['BE']['lockSSL'] = '0'; // set 2 if SSL
$TYPO3_CONF_VARS['BE']['usePHPFileFunctions'] = '1';
$TYPO3_CONF_VARS['BE']['warning_email_addr'] = 'support@peimic.com';
$TYPO3_CONF_VARS['BE']['warning_mode'] = '2';
$TYPO3_CONF_VARS['EXT']['extConf']['be_acl'] = 'a:2:{s:26:"disableOldPermissionSystem";s:1:"0";s:20:"enableFilterSelector";s:1:"1";}';
$TYPO3_CONF_VARS['EXT']['extConf']['captcha'] = 'a:21:{s:6:"useTTF";s:1:"1";s:8:"imgWidth";s:3:"100";s:9:"imgHeight";s:2:"30";s:12:"captchaChars";s:1:"4";s:9:"noNumbers";s:1:"0";s:4:"bold";s:1:"0";s:7:"noLower";s:1:"0";s:7:"noUpper";s:1:"0";s:13:"letterSpacing";s:1:"4";s:5:"angle";s:2:"20";s:5:"diffx";s:1:"0";s:5:"diffy";s:1:"2";s:4:"xpos";s:1:"3";s:4:"ypos";s:1:"4";s:6:"noises";s:1:"6";s:9:"backcolor";s:7:"#f4f4f4";s:9:"textcolor";s:7:"#000000";s:11:"obfusccolor";s:7:"#b0b0b0";s:8:"fontSize";s:2:"16";s:8:"fontFile";s:0:"";s:12:"excludeChars";s:14:"gijloGIJLO0169";}';
$TYPO3_CONF_VARS['EXT']['extConf']['wt_doorman'] = 'a:4:{s:14:"varsDefinition";s:88:"uid=int,L=int,pid=int,tx_indexedsearch|sword=alphanum,tx_ttnews|tt_news=int,no_cache=int";s:19:"clearNotDefinedVars";s:1:"0";s:13:"pidInRootline";s:1:"0";s:5:"debug";s:1:"0";}';	//  Modified or inserted by TYPO3 Extension Manager.
$TYPO3_CONF_VARS['EXT']['extConf']['wt_spamshield'] = 'a:10:{s:12:"useNameCheck";s:1:"1";s:12:"usehttpCheck";s:1:"3";s:9:"notUnique";s:0:"";s:13:"honeypodCheck";s:1:"1";s:15:"useSessionCheck";s:1:"1";s:16:"SessionStartTime";s:2:"10";s:14:"SessionEndTime";s:3:"600";s:10:"AkismetKey";s:0:"";s:12:"email_notify";s:0:"";s:3:"pid";s:1:"0";}';	//  Modified or inserted by TYPO3 Extension Manager.
$TYPO3_CONF_VARS['FE']['lockHashKeyWords'] = 'useragent';
$TYPO3_CONF_VARS['FE']['lockIP'] = '2';
$TYPO3_CONF_VARS['FE']['noPHPscriptInclude'] = '1';
$TYPO3_CONF_VARS['FE']['strictFormmail'] = '1';
$TYPO3_CONF_VARS['SYS']['devIPmask'] = '114.*.*.*,127.0.0.1,::1';
$TYPO3_CONF_VARS['SYS']['displayErrors'] = '2';
?>
