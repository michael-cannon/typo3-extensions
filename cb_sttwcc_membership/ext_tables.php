<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{
		
	t3lib_extMgm::addModule("user","txcbsttwccmembershipM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

$tempColumns = Array (
	"tx_cbsttwccmembership_country" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:cb_sttwcc_membership/locallang_db.php:fe_users.tx_cbsttwccmembership_country",		
		"config" => Array (
			"type" => "select",	
			"items" => Array (
				Array("",0),
			),
			"foreign_table" => "static_countries",	
			"foreign_table_where" => "ORDER BY static_countries.uid",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_cbsttwccmembership_zone" => Array (		
		"exclude" => 1,		
		"label" =>
		"LLL:EXT:cb_sttwcc_membership/locallang_db.php:fe_users.tx_cbsttwccmembership_zone",		
		"config" => Array (
			"type" => "select",	
			"items" => Array (
				Array("",0),
			),
			"foreign_table" => "static_country_zones",	
			"foreign_table_where" => "ORDER BY static_country_zones.uid",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_cbsttwccmembership_country;;;;1-1-1, tx_cbsttwccmembership_zone");

$tempColumns = Array (
	"tx_cbsttwccmembership_zone" => Array (		
		"exclude" => 1,		
		"label" =>
		"LLL:EXT:cb_sttwcc_membership/locallang_db.php:fe_users.tx_cbsttwccmembership_zone",		
		"config" => Array (
			"type" => "select",	
			"items" => Array (
				Array("",0),
			),
			"foreign_table" => "static_country_zones",	
			"foreign_table_where" => "ORDER BY static_country_zones.uid",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
    "tx_cbsttwccmembership_city" => Array (        
        "exclude" => 1,        
        "label" =>
"LLL:EXT:cb_sttwcc_membership/locallang_db.php:fe_users.tx_cbsttwccmembership_city",        
        "config" => Array (
            "type" => "input",    
            "size" => "30",
        )
    ),
);


// t3lib_div::loadTCA("tx_t3consultancies");
// t3lib_extMgm::addTCAcolumns("tx_t3consultancies",$tempColumns,1);
// t3lib_extMgm::addToAllTCAtypes("tx_t3consultancies","tx_cbsttwccmembership_zone;;;;1-1-1,tx_cbsttwccmembership_city");
?>
