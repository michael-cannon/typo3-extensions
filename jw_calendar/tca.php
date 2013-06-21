<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_jwcalendar_organizer'] = Array (
	'ctrl' => $TCA['tx_jwcalendar_organizer']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,name,description, street,zip,city,phone,email,image,link'
	),
	'feInterface' => $TCA['tx_jwcalendar_organizer']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'name' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'street' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.street',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'zip' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'city' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'phone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'lower',
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/tx_jwcalendar',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
        'link' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_organizer.link',
            'config' => Array (
                'type' => 'input',
                'size' => '25',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => Array(
                    '_PADDING' => 2,
                    'link' => Array(
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'script' => 'browse_links.php?mode=wizard',
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
            )
        ),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,name;;;;2-2-2,description;;;richtext[*]:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/tx_jwcalendar/rte/], street, zip,city,phone,email,image,link')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

//************************************************************************************************
//
//************************************************************************************************

$TCA['tx_jwcalendar_location'] = Array (
	'ctrl' => $TCA['tx_jwcalendar_location']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,location, description, name, street,zip,city,phone,email,image,link'
	),
	'feInterface' => $TCA['tx_jwcalendar_location']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'location' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.location',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'name' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'street' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.street',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'zip' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'city' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'phone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'lower',
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/tx_jwcalendar',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
        'link' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_location.link',
            'config' => Array (
                'type' => 'input',
                'size' => '25',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => Array(
                    '_PADDING' => 2,
                    'link' => Array(
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'script' => 'browse_links.php?mode=wizard',
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
            )
        ),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,location;;;;2-2-2,description;;;richtext[*]:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/tx_jwcalendar/rte/], name,street, zip,city,phone,email,image,link')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);


//************************************************************************************************
//
//************************************************************************************************

$TCA['tx_jwcalendar_categories'] = Array (
	'ctrl' => $TCA['tx_jwcalendar_categories']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,color,fe_entry,fe_group,comment,starttime,endtime'
	),
	'feInterface' => $TCA['tx_jwcalendar_categories']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_categories.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'color' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_categories.color',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'wizards' => Array(
					'_PADDING' => 2,
					'color' => Array(
						'title' => 'Color:',
						'type' => 'colorbox',
						'dim' => '12x12',
						'tableStyle' => 'border:solid 1px black;',
						'script' => 'wizard_colorpicker.php',
						'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
					),
				),
				'eval' => 'trim,nospace',
			)
		),
		'fe_entry' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_categories.fe_entry',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'fe_group' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
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
		'comment' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_categories.comment',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'starttime' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2,color,fe_entry,comment,hidden;;1;;3-3-3')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'starttime,endtime,fe_group',)
	)
);



//************************************************************************************************
$TCA['tx_jwcalendar_exc_groups'] = Array (
	'ctrl' => $TCA['tx_jwcalendar_exc_groups']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,color,bgcolor'
	),
	'feInterface' => $TCA['tx_jwcalendar_exc_groups']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_groups.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'color' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_groups.color',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'wizards' => Array(
					'_PADDING' => 2,
					'color' => Array(
						'title' => 'Color:',
						'type' => 'colorbox',
						'dim' => '12x12',
						'tableStyle' => 'border:solid 1px black;',
						'script' => 'wizard_colorpicker.php',
						'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
					),
				),
				'eval' => 'trim,nospace',
			)
		),
		'bgcolor' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_groups.bgcolor',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,title,color;;;;2-2-2,bgcolor')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);
//************************************************************************************************
$TCA['tx_jwcalendar_exc_events'] = Array (
	'ctrl' => $TCA['tx_jwcalendar_exc_events']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,begin,end,title,exc_group,priority'
	),
	'feInterface' => $TCA['tx_jwcalendar_exc_events']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'begin' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_events.begin',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'end' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_events.end',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_events.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'priority' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_events.priority',		
			'config' => Array (
				'type' => 'input',	
				'size' => '2',	
				'eval' => 'integer',
				'default' => '1',
			)
		),

		'exc_group' => Array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_exc_events.exc_group',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'foreign_table' => 'tx_jwcalendar_exc_groups',
				'foreign_table_where' => 'ORDER BY tx_jwcalendar_exc_groups.uid',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,title,begin,end,;;;;2-2-2,exc_group,priority')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

//************************************************************************************************
$TCA['tx_jwcalendar_events'] = Array (
	'ctrl' => $TCA['tx_jwcalendar_events']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,category,begin,end,event_type,exc_title,exc_group,rec_end_date,rec_time_x,rec_daily_type,repeat_days,location,location_id,organiser,organizer_id,organizer_feuser,email,title,teaser,description,link,image,directlink,starttime,endtime'
	),
	'feInterface' => $TCA['tx_jwcalendar_events']['feInterface'],
	'columns' => Array (
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'category' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.category',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_jwcalendar_categories',
				'foreign_table_where' => 'ORDER BY tx_jwcalendar_categories.uid',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'begin' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.begin',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'end' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.end',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
	
		'event_type' => Array (
		'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.event_type',		
		'config' => Array (
			'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.type.regular', 0),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.type.recurring_daily', 1),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.type.recurring_weekly', 2),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.type.recurring_monthly', 3),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.type.recurring_yearly', 4),
				),
			),
		),

		'exc_event' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.exc_event',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_jwcalendar_exc_events',
				'foreign_table_where' => 'ORDER BY tx_jwcalendar_exc_events.uid',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 128,
			)
		),
		'exc_group' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.exc_group',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_jwcalendar_exc_groups',
				'foreign_table_where' => 'ORDER BY tx_jwcalendar_exc_groups.uid',
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 128,
			)
		),

		'rec_end_date' => Array (
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_end_date',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			),
		),
		'rec_time_x' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_time_x',		
			'config' => Array (
				'type' => 'input',	
				'size' => '2',	
				'eval' => 'integer',
				'default' => '1',
			)
		),

		'rec_daily_type' => Array (
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_daily_type',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_daily_type.days', 0),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_daily_type.workdays', 1),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_daily_type.weekend', 2),
				),
			),
		),
		'repeat_days' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_item.repeat_days',		
			'config' => Array (
				'type' => 'input',	
				'size' => '2',	
				'eval' => 'integer',
				'default' => '1',
			)
		),
	
		'rec_weekly_type' => Array (
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_weekly_type',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_weekly_type.days', 0),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_weekly_type.workdays', 1),
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_weekly_type.weekend', 2),
				),
			),
		),
	
		'repeat_weeks' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_weeks',		
			'config' => Array (
				'type' => 'input',	
				'size' => '2',	
				'eval' => 'integer',
				'default' => '1',
			)
		),
		'repeat_week_monday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_monday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'repeat_week_tuesday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_tuesday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'repeat_week_wednesday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_wednesday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'repeat_week_thursday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_thursday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'repeat_week_friday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_friday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'repeat_week_saturday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_saturday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'repeat_week_sunday' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_week_sunday',		
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),

		'repeat_months' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_months',		
			'config' => Array (
				'type' => 'input',	
				'size' => '2',	
				'eval' => 'integer',
				'default' => '1',
			)
		),

		'rec_yearly_type' => Array (
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_yearly_type',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_yearly_type.givendate', 0),
					// Array('LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.rec_daily_type.workdays', 1),
				),
			),
		),
		'repeat_years' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.repeat_years',		
			'config' => Array (
				'type' => 'input',	
				'size' => '2',	
				'eval' => 'integer',
				'default' => '1',
			)
		),

	
		'location' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.location',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'location_id' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.location_id',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'foreign_table' => 'tx_jwcalendar_location',
				'foreign_table_where' => 'ORDER BY tx_jwcalendar_location.location',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'organiser' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.organiser',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'organizer_id' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.organizer_id',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'foreign_table' => 'tx_jwcalendar_organizer',
				'foreign_table_where' => 'ORDER BY tx_jwcalendar_organizer.name',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'organizer_feuser' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.organizer_feuser',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'foreign_table' => 'fe_users',
				'foreign_table_where' => 'ORDER BY username',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'teaser' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.teaser',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
        'link' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.link',
            'config' => Array (
                'type' => 'input',
                'size' => '15',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => Array(
                    '_PADDING' => 2,
                    'link' => Array(
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'script' => 'browse_links.php?mode=wizard',
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
            )
        ),
        'directlink' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.directlink',
            'config' => Array (
                'type' => 'input',
                'size' => '15',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
                'wizards' => Array(
                    '_PADDING' => 2,
                    'link' => Array(
                        'type' => 'popup',
                        'title' => 'direkt-link',
                        'icon' => 'link_popup.gif',
                        'script' => 'browse_links.php?mode=wizard',
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    )
                )
            )
        ),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jw_calendar/locallang_db.php:tx_jwcalendar_events.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/tx_jwcalendar',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'starttime' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
	),

	'types' => Array (
			'0' => Array('showitem' => 'category;;;;1-1-1, event_type;;2;;1-1-1',),
	),
	'palettes' => Array (
		'1'  => Array('showitem' => 'hidden, starttime, endtime'),
		'2'  => Array('showitem' => 'begin, end'),
		'3'  => Array('showitem' => ''),
		'4'  => Array('showitem' => 'location'),
		'5'  => Array('showitem' => 'link'),
		'6'  => Array('showitem' => 'organizer'),
		'8'  => Array('showitem' => ''),
		'9'  => Array('showitem' => ''),
		'10'	=>	Array('showitem' =>	'rec_time_x'),

		// Palette for recurring daily, every X days
		'11'    =>  Array('showitem' => 'repeat_days'),
		'12'	=>	Array('showitem' => 'repeat_week_monday, repeat_week_tuesday, repeat_week_wednesday, repeat_week_thursday, repeat_week_friday, repeat_week_saturday, repeat_week_sunday'),
		'13'	=>	Array('showitem' =>	'repeat_months'),
		'14'	=>	Array('showitem' =>	'repeat_years'),
		'15'	=>	Array('showitem' =>	'exc_skip'),
	),
	
);

	$TCA['tx_jwcalendar_events']['ctrl']['type']		=	'event_type';
	$TCA['tx_jwcalendar_events']['ctrl']['mainpalette']		=	'1';
	$TCA['tx_jwcalendar_events']['ctrl']['canNotCollapse']	=	'1';
	$TCA['tx_jwcalendar_events']['ctrl']['requestUpdate']	=	'rec_daily_type,rec_weekly_type,';

	$TCA['tx_jwcalendar_events']['types']['1'] = Array(
		'showitem' => 'category;;;;1-1-1, event_type;;2;;1-1-1, rec_daily_type;;11;;,rec_time_x, rec_end_date,exc_event,exc_group',
		'subtype_value_field'	=>	'rec_daily_type',
		'subtypes_excludelist'	=>	Array(
							'1'	=>	'repeat_days',
							'2'	=>	'repeat_days',
						),
	);
	$TCA['tx_jwcalendar_events']['types']['2'] = Array(
		'showitem' => ' category;;;;1-1-1, event_type;;2;;1-1-1,rec_weekly_type;;12;;, repeat_weeks, rec_time_x,rec_end_date,exc_event,exc_group', 
		'subtype_value_field'	=>	'rec_weekly_type',
		'subtypes_excludelist'	=>	Array(
							'1'	=>	'repeat_week_monday, repeat_week_tuesday, repeat_week_wednesday, repeat_week_thursday, repeat_week_friday, repeat_week_saturday, repeat_week_sunday',
							'2'	=>	'repeat_week_monday, repeat_week_tuesday, repeat_week_wednesday, repeat_week_thursday, repeat_week_friday, repeat_week_saturday, repeat_week_sunday',
						),
	);
	$TCA['tx_jwcalendar_events']['types']['3'] = Array(
		'showitem' => 'category;;;;1-1-1, event_type;;2;;1-1-1, repeat_months, rec_time_x,rec_end_date,exc_event,exc_group', 
	);
	$TCA['tx_jwcalendar_events']['types']['4'] = Array(
		'showitem' => 'category;;;;1-1-1, event_type;;2;;1-1-1, repeat_years, rec_time_x,rec_end_date,exc_event,exc_group',
	);

	foreach(array('0', '1', '2', '3', '4') as $type) {
		$TCA['tx_jwcalendar_events']['types'][$type]['showitem'] .= ',location_id;;;;2-2-2,location, organizer_id;;;;3-3-3, organiser, organizer_feuser, email, title;;;;4-4-4,teaser, description;;;richtext[*]:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/tx_jwcalendar/rte/], link, directlink,image';
	}

?>