<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_mcpodcast_access" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_podcast/locallang_db.php:tt_news.tx_mcpodcast_access",		
               'config' => Array (
                       'type' => 'select',
                       'items' => Array (
                                Array('', 0),
                                Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
                                Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
                                Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
                        ),
                        'foreign_table' => 'fe_groups'
		)
	),
	"tx_mcpodcast_mp3" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:mc_podcast/locallang_db.php:tt_news.tx_mcpodcast_mp3",		
		"config" => Array (
			"type" => "group",
			"internal_type" => "file",
			"allowed" => "mp3,ogg,ac3",	
			"max_size" => 10000000,	
			"uploadfolder" => "uploads/tx_mcpodcast",
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_mcpodcast_infotxt" => Array (        
		"exclude" => 1,        
		"label" => "LLL:EXT:mc_podcast/locallang_db.php:tt_news.tx_mcpodcast_infotxt",        
		"config" => Array (
			"type" => "none",
		)
	),
);


t3lib_div::loadTCA("tt_news");
t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_news","tx_mcpodcast_access;;;;1-1-1, tx_mcpodcast_mp3, tx_mcpodcast_infotxt");


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
t3lib_extMgm::addPlugin(Array('LLL:EXT:mc_podcast/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Podcast RSS Feed");

?>
