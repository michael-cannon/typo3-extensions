<?php
/**
 * Language labels for database tables/fields belonging to extension "jw_calendar"
 *
 * This file is detected by the translation tool.
 */

$LOCAL_LANG = Array (
	'default' => Array (
		'tx_jwcalendar_categories' => 'Calendar Categories',
		'tx_jwcalendar_categories.title' => 'Categorie title',
		'tx_jwcalendar_categories.color' => 'Color (e.g. green or #15E337)',
		'tx_jwcalendar_categories.fe_entry' => 'Allow FE_Entry',
		'tx_jwcalendar_categories.comment' => 'Comment',

		'tx_jwcalendar_exc_groups' => 'Calendar Exception Groups',
		'tx_jwcalendar_exc_groups.title' => 'Title',
		'tx_jwcalendar_exc_groups.exc_group' => 'Group',
		'tx_jwcalendar_exc_groups.color' => 'Color (e.g. green or #15E337)',
		'tx_jwcalendar_exc_groups.bgcolor' => 'Show as background color',

		'tx_jwcalendar_exc_events' => 'Calendar Exception Events',
		'tx_jwcalendar_exc_events.begin' => 'Beginning',
		'tx_jwcalendar_exc_events.end' => 'End',
		'tx_jwcalendar_exc_events.priority' => 'Priority',
		'tx_jwcalendar_exc_events.title' => 'Title',
		'tx_jwcalendar_exc_events.exc_group' => 'Group',
	
		'tx_jwcalendar_organizer' => 'Calendar Organizer',
		'tx_jwcalendar_organizer.name' => 'Name',	
		'tx_jwcalendar_organizer.description' => 'Description',	
		'tx_jwcalendar_organizer.street' => 'Street',	
		'tx_jwcalendar_organizer.zip' => 'Zip',	
		'tx_jwcalendar_organizer.city' => 'City',	
		'tx_jwcalendar_organizer.phone' => 'Phone',	
		'tx_jwcalendar_organizer.email' => 'E-mail',	
		'tx_jwcalendar_organizer.image' => 'Logo/Image',	
		'tx_jwcalendar_organizer.link' => 'Homepage',	

		'tx_jwcalendar_location' => 'Calendar Location',
		'tx_jwcalendar_location.location' => 'Location',	
		'tx_jwcalendar_location.description' => 'Description',	
		'tx_jwcalendar_location.name' => 'Name',	
		'tx_jwcalendar_location.street' => 'Street',	
		'tx_jwcalendar_location.zip' => 'Zip',	
		'tx_jwcalendar_location.city' => 'City',	
		'tx_jwcalendar_location.phone' => 'Phone',	
		'tx_jwcalendar_location.email' => 'E-mail',	
		'tx_jwcalendar_location.image' => 'Logo/Image',	
		'tx_jwcalendar_location.link' => 'Homepage',	

		'tx_jwcalendar_events' => 'Calendar Events',
		'tx_jwcalendar_events.category' => 'Category',
		'tx_jwcalendar_events.begin' => 'Beginning',
		'tx_jwcalendar_events.end' => 'End',
		'tx_jwcalendar_events.location' => 'Location',
		'tx_jwcalendar_events.location_id' => 'Location from List',
		'tx_jwcalendar_events.organiser' => 'Organiser',
		'tx_jwcalendar_events.organizer_id' => 'Organiser from List',
		'tx_jwcalendar_events.organizer_feuser' => 'Organiser from FE_user list',
		'tx_jwcalendar_events.email' => 'Email',
		'tx_jwcalendar_events.title' => 'Title',
		'tx_jwcalendar_events.teaser' => 'Teaser',
		'tx_jwcalendar_events.description' => 'Description of the event',
		'tx_jwcalendar_events.link' => 'Link',
		'tx_jwcalendar_events.image' => 'Image/Logo',
        'tx_jwcalendar_events.directlink' => 'Direct Link',
    	'tx_jwcalendar_events.event_type' => 'Event type',
    	'tx_jwcalendar_events.type.regular' => 'Regular',
    	'tx_jwcalendar_events.type.recurring_daily' => 'Recurring_daily',
    	'tx_jwcalendar_events.type.recurring_weekly' => 'Recurring_weekly',
    	'tx_jwcalendar_events.type.recurring_monthly' => 'Recurring_monthly',
    	'tx_jwcalendar_events.type.recurring_yearly' => 'Recurring_yearly',
    	'tx_jwcalendar_events.type.exception' => 'Exception',
    	'tx_jwcalendar_events.rec_end_date' => 'Recur enddate',
    	'tx_jwcalendar_events.rec_time_x' => 'Recur X times',

    	'tx_jwcalendar_events.rec_daily_type' => 'daily type',
    	'tx_jwcalendar_events.rec_daily_type.days' => 'daily type days',
    	'tx_jwcalendar_events.rec_daily_type.workdays' => 'daily type workdays',
    	'tx_jwcalendar_events.rec_daily_type.weekend' => 'daily type weekend',
    	'tx_jwcalendar_item.repeat_days' => 'repeat days',

    	'tx_jwcalendar_events.rec_weekly_type' => 'weekly type',
    	'tx_jwcalendar_events.rec_weekly_type.days' => 'weekly type days',
    	'tx_jwcalendar_events.rec_weekly_type.workdays' => 'weekly type workdays',
    	'tx_jwcalendar_events.rec_weekly_type.weekend' => 'weekly type weekend',

		'tx_jwcalendar_events.repeat_week_type' => 'week type:',
		'tx_jwcalendar_events.repeat_weeks' => 'Every/All X weeks:',
		'tx_jwcalendar_events.repeat_week_monday' => 'On Mondays',
		'tx_jwcalendar_events.repeat_week_tuesday' => 'On Tuesdays',
		'tx_jwcalendar_events.repeat_week_wednesday' => 'On Wednesdays',
		'tx_jwcalendar_events.repeat_week_thursday' => 'On Thursdays',
		'tx_jwcalendar_events.repeat_week_friday' => 'On Fridays',
		'tx_jwcalendar_events.repeat_week_saturday' => 'On Saturdays',
		'tx_jwcalendar_events.repeat_week_sunday' => 'On Sundays',
			
		'tx_jwcalendar_events.rec_monthly_type' => 'Select iteration',
		'tx_jwcalendar_events.rec_monthly_type.dayofmonth' => 'Day of the month',
		'tx_jwcalendar_events.repeat_months' => 'Every X months:',

		'tx_jwcalendar_events.exc_group' => 'Exc. group',
		'tx_jwcalendar_events.exc_event' => 'Exception event',

		'tx_jwcalendar_events.rec_yearly_type' => 'Select iteration',
		'tx_jwcalendar_events.rec_yearly_type.givendate' => 'Repeat every X years at above date',
		'tx_jwcalendar_events.repeat_years' => 'Every X years:',
			
	
    
		'tt_content.list_type_pi1' => 'JW Calendar',
	),
	'dk' => Array (
	),
	'de' => Array (
		'tx_jwcalendar_categories' => 'Kalender Kategorien',
		'tx_jwcalendar_categories.title' => 'Kategorietitel',
		'tx_jwcalendar_categories.color' => 'Farbe (z.B. green oder #15E337)',
		'tx_jwcalendar_categories.fe_entry' => 'FE Eintrag erlauben',
		'tx_jwcalendar_categories.comment' => 'Anmerkung',

		'tx_jwcalendar_events' => 'Kalender Termine',
		'tx_jwcalendar_events.category' => 'Kategorie',
		'tx_jwcalendar_events.begin' => 'Anfang',
		'tx_jwcalendar_events.end' => 'Ende',
		'tx_jwcalendar_events.location' => 'Veranstaltungsort',
		'tx_jwcalendar_events.location_id' => 'Veranstaltungsort von Liste',
		'tx_jwcalendar_events.organiser' => 'Veranstalter',
		'tx_jwcalendar_events.organizer_id' => 'Veranstalter von Liste',
		'tx_jwcalendar_events.organizer_feuser' => 'Veranstalter von FE_Benutzer Liste',
		'tx_jwcalendar_events.email' => 'Email',
		'tx_jwcalendar_events.title' => 'Titel', 
		'tx_jwcalendar_events.teaser' => 'Teaser', 
		'tx_jwcalendar_events.description' => 'Terminbeschreibung', 
		'tx_jwcalendar_events.link' => 'Link', 
        'tx_jwcalendar_events.image' => 'Bild/Logo',
        'tx_jwcalendar_events.directlink' => 'Direkter Link',
        'tt_content.list_type_pi1' => 'JW Kalender', 

		'tx_jwcalendar_organizer' => 'Kalender Veranstalter',
		'tx_jwcalendar_organizer.name' => 'Name',	
		'tx_jwcalendar_organizer.description' => 'Beschreibung',	
		'tx_jwcalendar_organizer.street' => 'Straße',	
		'tx_jwcalendar_organizer.zip' => 'Postleitzahl',	
		'tx_jwcalendar_organizer.city' => 'Stadt',	
		'tx_jwcalendar_organizer.phone' => 'Telefon',	
		'tx_jwcalendar_organizer.email' => 'E-mail',	
		'tx_jwcalendar_organizer.image' => 'Logo/Bild',	
		'tx_jwcalendar_organizer.link' => 'Homepage',	

		'tx_jwcalendar_location' => 'Kalender Veranstaltungsort',
		'tx_jwcalendar_location.location' => 'Veranstaltungsort',	
		'tx_jwcalendar_location.description' => 'Beschreibung',	
		'tx_jwcalendar_location.name' => 'Kontakt',	
		'tx_jwcalendar_location.street' => 'Straße',	
		'tx_jwcalendar_location.zip' => 'Postleitzahl',	
		'tx_jwcalendar_location.city' => 'Stadt',	
		'tx_jwcalendar_location.phone' => 'Telefon',	
		'tx_jwcalendar_location.email' => 'E-mail',	
		'tx_jwcalendar_location.image' => 'Logo/Bild',	
		'tx_jwcalendar_location.link' => 'Homepage',	

		'tx_jwcalendar_exc_groups' => 'Kalender Ausnahmeereignis Gruppe',
		'tx_jwcalendar_exc_groups.title' => 'Titel',
		'tx_jwcalendar_exc_groups.color' => 'Farbe (z.B. green oder #15E337)',
		'tx_jwcalendar_exc_groups.bgcolor' => 'Farbe ale Hintergrundfarbe',

		'tx_jwcalendar_exc_events' => 'Kalender Ausnahmeereignisse',
		'tx_jwcalendar_exc_events.begin' => 'Anfang',
		'tx_jwcalendar_exc_events.end' => 'Ende',
		'tx_jwcalendar_exc_events.priority' => 'Priorität',
		'tx_jwcalendar_exc_events.title' => 'Titel',
		'tx_jwcalendar_exc_events.exc_group' => 'Gruppe',
			
    	'tx_jwcalendar_events.event_type' => 'Ereignistyp',
    	'tx_jwcalendar_events.type.regular' => 'Normal',
    	'tx_jwcalendar_events.type.recurring_daily' => 'Täglich wiederholend',
    	'tx_jwcalendar_events.type.recurring_weekly' => 'Wöchentlich wiederholend',
    	'tx_jwcalendar_events.type.recurring_monthly' => 'Monatlich wiederholend',
    	'tx_jwcalendar_events.type.recurring_yearly' => 'Jährlich wiederholend',
    	'tx_jwcalendar_events.rec_end_date' => 'Ereignisfolge Enddatum',
    	'tx_jwcalendar_events.rec_time_x' => 'Ereignis X mal',

    	'tx_jwcalendar_events.rec_daily_type' => 'Wiederholung Tages Typ',
    	'tx_jwcalendar_events.rec_daily_type.days' => 'Täglich wiederholend',
    	'tx_jwcalendar_events.rec_daily_type.workdays' => 'An jedem Werktag',
    	'tx_jwcalendar_events.rec_daily_type.weekend' => 'An jedem Wochenendtag',
    	'tx_jwcalendar_item.repeat_days' => 'Wiederhole alle X Tage',

    	'tx_jwcalendar_events.rec_weekly_type' => 'Wiederholung Wochen Typ',
    	'tx_jwcalendar_events.rec_weekly_type.days' => 'Wöchentlich wiederholend',
    	'tx_jwcalendar_events.rec_weekly_type.workdays' => 'Wöchentlich jeder Werktag',
    	'tx_jwcalendar_events.rec_weekly_type.weekend' => 'Wöchentlich jeder Wochenendtag',

		'tx_jwcalendar_events.repeat_week_type' => 'Woche Typ:',
		'tx_jwcalendar_events.repeat_weeks' => 'Alle X Wochen:',
		'tx_jwcalendar_events.repeat_week_monday' => 'Am Montag',
		'tx_jwcalendar_events.repeat_week_tuesday' => 'Am Dienstag',
		'tx_jwcalendar_events.repeat_week_wednesday' => 'Am Mittwoch',
		'tx_jwcalendar_events.repeat_week_thursday' => 'Am Donnerstag',
		'tx_jwcalendar_events.repeat_week_friday' => 'Am Freitag',
		'tx_jwcalendar_events.repeat_week_saturday' => 'Am Samstag',
		'tx_jwcalendar_events.repeat_week_sunday' => 'Am Sonntag',
			
		'tx_jwcalendar_events.repeat_months' => 'Alle X Monate:',
		'tx_jwcalendar_events.repeat_years' => 'Alle X Jahre:',

		'tx_jwcalendar_events.exc_group' => 'Ausnahmeereignis Gruppe',
		'tx_jwcalendar_events.exc_event' => 'Ausnahmeereignis',
	),
	'no' => Array (
	),
	'it' => Array (
	),
	'fr' => Array (
	),
	'es' => Array (
		'tx_jwcalendar_categories' => 'Rubros del calendario',
		'tx_jwcalendar_categories.title' => 'Títulos de los rubros', 
		'tx_jwcalendar_categories.fe_entry' => 'Allow FE_Entry',
		'tx_jwcalendar_categories.color' => 'Color',
		'tx_jwcalendar_events' => 'Fechas en el calendario',
		'tx_jwcalendar_events.category' => 'Rubros',
		'tx_jwcalendar_events.begin' => 'Inicio',
		'tx_jwcalendar_events.end' => 'Final',
		'tx_jwcalendar_events.location' => 'Lugar del evento',
		'tx_jwcalendar_events.organiser' => 'Organizador del evento',
		'tx_jwcalendar_events.email' => 'E-mail',
		'tx_jwcalendar_events.title' => 'Título',
		'tx_jwcalendar_events.teaser' => 'Teaser', 
		'tx_jwcalendar_events.description' => 'Descripción del evento',
		'tx_jwcalendar_events.link' => 'Enlace',
        'tx_jwcalendar_events.image' => 'Logotipo',
        'tx_jwcalendar_events.directlink' => 'Enlace directo',
        'tt_content.list_type_pi1' => 'Calendario JW',
	),
	'nl' => Array (
	),
	'cz' => Array (
	),
	'pl' => Array (
	),
	'si' => Array (
	),
	'fi' => Array (
		'tx_jwcalendar_categories' => 'Kalenteriluokat',
		'tx_jwcalendar_categories.title' => 'Luokan otsikko',
		'tx_jwcalendar_events' => 'Luokan tapahtumat',
		'tx_jwcalendar_events.category' => 'Luokka',
		'tx_jwcalendar_categories.color' => 'Color',
		'tx_jwcalendar_events.begin' => 'Aloitus',
		'tx_jwcalendar_events.end' => 'Loppu',
		'tx_jwcalendar_events.location' => 'Paikka',
		'tx_jwcalendar_events.organiser' => 'Jäjestäjä',
		'tx_jwcalendar_events.email' => 'Sähköpostiosoite',
		'tx_jwcalendar_events.title' => 'Nimike',
		'tx_jwcalendar_events.teaser' => 'Houkutin',
		'tx_jwcalendar_events.description' => 'Tapahtuman kuvaus',
		'tx_jwcalendar_events.link' => 'Linkki',
		'tx_jwcalendar_events.image' => 'Kuva/logo',
		'tt_content.list_type_pi1' => 'JW Kalenterin',
	),
	'tr' => Array (
	),
	'se' => Array (
	),
	'pt' => Array (
	),
	'ru' => Array (
	),
	'ro' => Array (
	),
	'ch' => Array (
	),
	'sk' => Array (
	),
	'lt' => Array (
	),
	'is' => Array (
	),
	'hr' => Array (
	),
	'hu' => Array (
	),
	'gl' => Array (
	),
	'th' => Array (
	),
	'gr' => Array (
	),
	'hk' => Array (
	),
	'eu' => Array (
	),
	'bg' => Array (
	),
	'br' => Array (
		'tx_jwcalendar_categories' => 'Categorias do Calendário',
		'tx_jwcalendar_categories.title' => 'Título da Categoria',
		'tx_jwcalendar_events' => 'Eventos do Calendário',
		'tx_jwcalendar_events.category' => 'Categoria',
		'tx_jwcalendar_events.begin' => 'Início',
		'tx_jwcalendar_events.end' => 'Término',
		'tx_jwcalendar_events.location' => 'Local',
		'tx_jwcalendar_events.organiser' => 'Organizador(a)',
		'tx_jwcalendar_events.email' => 'E-mail',
		'tx_jwcalendar_events.title' => 'Título',
		'tx_jwcalendar_events.teaser' => 'Chamada',
		'tx_jwcalendar_events.description' => 'Descrição do evento',
		'tx_jwcalendar_events.link' => 'Link',
		'tx_jwcalendar_events.image' => 'Imagem/Logo',
		'tt_content.list_type_pi1' => 'Calendário JW',
	),
	'et' => Array (
	),
	'ar' => Array (
	),
	'he' => Array (
	),
	'ua' => Array (
	),
	'lv' => Array (
	),
	'jp' => Array (
	),
	'vn' => Array (
	),
);
?>