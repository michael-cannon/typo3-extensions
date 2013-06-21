<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_memberaccess_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_memberaccess_modfunc1.php",
		"LLL:EXT:member_access/locallang_db.php:moduleFunction.tx_memberaccess_modfunc1",
		"wiz"	
	);
}


if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_func",		
		"tx_memberaccess_modfunc2",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc2/class.tx_memberaccess_modfunc2.php",
		"LLL:EXT:member_access/locallang_db.php:moduleFunction.tx_memberaccess_modfunc2",
		"wiz"	
	);
}

$TCA["tx_memberaccess_acl"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_acl",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_memberaccess_acl.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "email, name, company, accesslevel",
	)
);

$TCA["tx_memberaccess_registrationerrors"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:member_access/locallang_db.php:tx_memberaccess_registrationerrors",		
		"label" => "uid",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_memberaccess_registrationerrors.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, userid, errortime, email, errors",
	)
);
?>