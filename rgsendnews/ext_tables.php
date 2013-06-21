<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_rgsendnews_stat');

$TCA["tx_rgsendnews_stat"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:rgsendnews/locallang_db.xml:tx_rgsendnews_stat',		
		'label'     => 'sender',	
	  'label_alt' => 'tstamp,receiver',
	  'label_alt_force' => 1,		
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_rgsendnews_stat.gif',
		'readOnly' =>1,	
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, sender, receiver, newsid, comment, ip, recmail, sendmail, htmlmail",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


//t3lib_extMgm::addPlugin(array('LLL:EXT:rgsendnews/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

if (TYPO3_MODE=="BE")    {
    t3lib_extMgm::insertModuleFunction(
        "web_info",        
        "tx_rgsendnews_modfunc1",
        t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_rgsendnews_modfunc1.php",
        "LLL:EXT:rgsendnews/locallang_db.xml:moduleFunction.tx_rgsendnews_modfunc1"
    );
}


t3lib_extMgm::addStaticFile($_EXTKEY,"res/sendit/","Page to send news");
t3lib_extMgm::addStaticFile($_EXTKEY,"res/tosend/","News SINGLE ");
?>