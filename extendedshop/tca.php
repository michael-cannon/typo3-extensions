<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_extendedshop_status"] = Array (
    "ctrl" => $TCA["tx_extendedshop_status"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,status,priority"
    ),
    "feInterface" => $TCA["tx_extendedshop_status"]["feInterface"],
    "columns" => Array (
        'sys_language_uid' => Array (        
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
            'config' => Array (
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => Array(
                    Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
                    Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
                )
            )
        ),
        'l18n_parent' => Array (        
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
            'config' => Array (
                'type' => 'select',
                'items' => Array (
                    Array('', 0),
                ),
                'foreign_table' => 'tx_extendedshop_status',
                'foreign_table_where' => 'AND tx_extendedshop_status.pid=###CURRENT_PID### AND tx_extendedshop_status.sys_language_uid IN (-1,0)',
            )
        ),
        'l18n_diffsource' => Array (        
            'config' => Array (
                'type' => 'passthrough'
            )
        ),
        "hidden" => Array (        
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),
        "status" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_status.status",        
            "config" => Array (
                "type" => "input",    
                "size" => "20",    
                "max" => "20",    
                "eval" => "required,trim,unique",
            )
        ),
        "priority" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_status.priority",        
            "config" => Array (
                "type" => "input",    
                "size" => "5",    
                "max" => "3",    
                "range" => Array ("lower"=>0,"upper"=>1000),    
                "eval" => "int,unique",
            )
        ),
    ),
    "types" => Array (
        "0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, status, priority")
    ),
    "palettes" => Array (
        "1" => Array("showitem" => "")
    )
);

$TCA["tx_extendedshop_category"] = Array (
	"ctrl" => $TCA["tx_extendedshop_category"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,code,title,summary,description,image"
	),
	"feInterface" => $TCA["tx_extendedshop_category"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"code" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_category.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "245",	
				"eval" => "required,trim",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_category.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"summary" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_category.summary",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_category.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "8",
			)
		),
		"image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_category.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_extendedshop",
				"show_thumbs" => 1,	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, code, title;;;;2-2-2, summary;;;;3-3-3, description, image")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_extendedshop_orders"] = Array (
	"ctrl" => $TCA["tx_extendedshop_orders"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,code,customer,shippingcustomer,date,shipping,payment,total,weight,volume,trackingcode,state,ip,note,status,deliverydate"
	),
	"feInterface" => $TCA["tx_extendedshop_orders"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"code" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "50",	
				"eval" => "required,trim",
			)
		),
		"customer" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.customer",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"shippingcustomer" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.shippingcustomer",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tt_address",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.date",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"shipping" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.shipping",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"payment" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.payment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"total" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.total",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"eval" => "required",
			)
		),
		"weight" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.weight",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"volume" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.volume",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"trackingcode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.trackingcode",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "50",	
				"eval" => "trim",
			)
		),
		"state" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.state",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "15",	
				"eval" => "trim",
			)
		),
		"ip" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.ip",		
			"config" => Array (
				"type" => "none",
			)
		),
		"note" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.note",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"status" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.status",        
            "config" => Array (
                "type" => "select",    
                "items" => Array (
                    Array("",0),
                ),
                "foreign_table" => "tx_extendedshop_status",    
                "foreign_table_where" => "AND tx_extendedshop_status.sys_language_uid=0 ORDER BY tx_extendedshop_status.priority",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,    
            )
        ),
        "deliverydate" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.deliverydate",        
            "config" => Array (
                "type" => "input",
                "size" => "8",
                "max" => "20",
                "eval" => "date",
                "checkbox" => "0",
                "default" => "0"
            )
        ),
		"complete" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.complete",        
            "config" => Array (
                "type" => "check",
            )
        ),
		"ordernote" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_orders.ordernote",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, code, customer, shippingcustomer, date, shipping, payment, total, weight, volume, trackingcode, state, ip, note, status, deliverydate")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_extendedshop_rows"] = Array (
	"ctrl" => $TCA["tx_extendedshop_rows"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,ordercode,productcode,quantity,price,weight,volume,state,accessoriescodes,options"
	),
	"feInterface" => $TCA["tx_extendedshop_rows"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"ordercode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.ordercode",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_extendedshop_orders",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"productcode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.productcode",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_extendedshop_products",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"quantity" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.quantity",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"price" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.price",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"eval" => "required",
			)
		),
		"weight" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.weight",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"volume" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.volume",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"state" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.state",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"eval" => "trim",
			)
		),
		"itemcode" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.itemcode",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"options" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_rows.options",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, ordercode, productcode, quantity, price, weight, volume, state, accessoriescodes, options")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_extendedshop_products"] = Array (
	"ctrl" => $TCA["tx_extendedshop_products"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,starttime,endtime,code,title,summary,description,image,price,pricenotax,instock,category,www,ordered,weight,volume,correlatedaccessories,offertprice,offertpricenotax,discount,sizes,colors,correlatedproducts"
	),
	"feInterface" => $TCA["tx_extendedshop_products"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"starttime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,date("m")-1,date("d"),date("Y"))
				)
			)
		),
		"code" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.code",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "100",	
				"eval" => "required,trim",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "required,trim",
			)
		),
		"pagetitle" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.pagetitle",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "100",	
				"eval" => "trim",
			)
		),
		"summary" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.summary",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "3",
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.description",		
			"config" => Array (
                "type" => "text",
                "cols" => "30",
                "rows" => "5",
                "wizards" => Array(
                    "_PADDING" => 2,
                    "RTE" => Array(
                        "notNewRecords" => 1,
                        "RTEonly" => 1,
                        "type" => "script",
                        "title" => "Full screen Rich Text Editing|Titolo",
                        "icon" => "wizard_rte2.gif",
                        "script" => "wizard_rte.php",
                    ),
                ),
            )
		),
		"image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_extendedshop",
				"show_thumbs" => 1,	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 10,
			)
		),
		"price" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.price",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"eval" => "required",
			)
		),
		"pricenotax" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.pricenotax",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"instock" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.instock",		
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"category" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.category",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_extendedshop_category",	
				"foreign_table_where" => "ORDER BY tx_extendedshop_category.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"www" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.www",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "150",	
				"eval" => "trim",
			)
		),
		"ordered" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.ordered",		
			"config" => Array (
				"type" => "none",
			)
		),
		"weight" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.weight",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"volume" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.volume",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",
			)
		),
		"offertprice" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.offertprice",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"offertpricenotax" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.offertpricenotax",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"discount" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.discount",		
			"config" => Array (
				"type" => "input",	
				"size" => "5",	
				"max" => "2",	
				"eval" => "trim",
			)
		),
		"sizes" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.sizes",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "245",	
				"eval" => "trim",
			)
		),
		"colors" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.colors",		
			"config" => Array (
				"type" => "input",	
				"size" => "15",	
				"max" => "245",	
				"eval" => "trim",
			)
		),
		"correlatedproducts" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.correlatedproducts",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_extendedshop_products",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.lingua',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:extendedshop/locallang_db.php:tx_extendedshop_products.parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_extendedshop_pi1',
				'foreign_table_where' => 'AND tx_extendedshop_products.uid=###REC_FIELD_l18n_parent### AND tx_extendedshop_products.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
		),

	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, sys_language_uid, I18n_parent, code, title;;2;;2-2-2, summary;;;;3-3-3, description;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_extendedshop/rte/], image, price;;4;;1-1-1, instock, category, www, ordered, weight, volume, offertprice;;3;;1-1-1, sizes, colors, correlatedproducts")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "starttime, endtime"),
		"2" => Array("showitem" => "pagetitle"),
		"3" => Array("showitem" => "offertpricenotax, discount"),
		"4" => Array("showitem" => "pricenotax"),
	)
);
?>
