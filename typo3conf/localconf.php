<?php
// Default password is 'joh316'
$TYPO3_CONF_VARS['BE']['installToolPassword'] = 'bacb98acf97e0b6112b1d1b650b84971';
## INSTALL SCRIPT EDIT POINT TOKEN - all lines after this points may be changed by the install script!

$TYPO3_CONF_VARS["GFX"]["im_version_5"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["im_negate_mask"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["im_imvMaskState"] = '0';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["im_no_effects"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["im_v5effects"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["im_combine_filename"] = 'composite';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["enable_typo3temp_db_tracking"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["TTFdpi"] = '96';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["SYS"]["sitename"] = 'SITE_NAME';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["SYS"]["ddmmyy"] = 'm/d/Y';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["SYS"]["loginCopyrightWarrantyProvider"] = 'Peimic.com';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["SYS"]["loginCopyrightWarrantyURL"] = '';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["SYS"]["loginCopyrightShowVersion"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["SYS"]["curlUse"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["EXT"]["allowGlobalInstall"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["EXT"]["allowLocalInstall"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["EXT"]["em_devVerUpdate"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["EXT"]["em_alwaysGetOOManual"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["EXT"]["em_systemInstall"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["unzip_path"] = '/usr/bin/unzip';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["diff_path"] = '/usr/bin/diff';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["warning_email_addr"] = 'michael@peimic.com';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["warning_mode"] = '2';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["compressionLevel"] = '3';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["maxFileSize"] = '100000';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["BE"]["trackBeUser"] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["FE"]["logfile_dir"] = 'fileadmin/logs/';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["FE"]["publish_dir"] = 'publish/';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["FE"]["simulateStaticDocuments"] = '0';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["FE"]["compressionLevel"] = '3';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["FE"]["pageNotFound_handling"] = '/';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS["GFX"]["im_path"] = '/usr/bin/';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['EXT']['extList'] = 'install,welcome,func_wizards,wizard_crpages,setup,tstemplate,tstemplate_ceditor,tstemplate_info,tstemplate_analyzer,rtehtmlarea,extra_page_cm_options,tstemplate_objbrowser,realurl,context_help,wizard_sortpages,cms_plaintext_import,viewpage,belog,loginusertrack,erotea_date2cal,sys_stat,tsconfig_help,lowlevel';       // Modified or inserted by TYPO3 Extension Manager. 
$TYPO3_CONF_VARS['EXT']['extConf']['rtehtmlarea'] = 'a:1:{s:16:"enableAllOptions";s:1:"0";}';	//  Modified or inserted by TYPO3 Extension Manager.
// Updated by TYPO3 Install Tool 06-06-2004 22:56:24
require_once( 'user_scripts.php' );
require_once( 'realurl.php' );
require_once( dirname( __FILE__ ) . '/../../../cb-third_party/cb_cogs/cb_cogs.config.php' );
require_once( CB_COGS_DIR . 'cb_string.php' );
require_once( CB_COGS_DIR . 'cb_validation.php' );

?>
