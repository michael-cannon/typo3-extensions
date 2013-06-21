/* core indexes */
CREATE TABLE `be_groups` (
	KEY `deleted` ( `deleted`, `hidden` ) 
);
CREATE TABLE `be_sessions` (
	KEY `nametstamp` ( `ses_name` , `ses_tstamp` )
);
CREATE TABLE `cache_hash` (
	KEY `hash` ( `hash` ( 4 ) ) 
);
CREATE TABLE `fe_groups` (
	KEY `deleted` ( `deleted` , `hidden` ) 
);
CREATE TABLE `fe_sessions` (
	KEY `nametstamp` ( `ses_name` , `ses_tstamp` )
);
CREATE TABLE `fe_users` (
	KEY `deleted` ( `deleted` , `disable` ) 
);
CREATE TABLE `pages_language_overlay` (
	KEY `hidden` ( `hidden` ),
	KEY `sys_language_uid` ( `sys_language_uid` ) 
);
CREATE TABLE `pages` (
	KEY `deleted` ( `deleted` , `hidden` ),
	KEY `doktype` ( `doktype` ),
	KEY `sorting` ( `sorting` ),
	KEY `docdelmod` ( `doktype` , `deleted` , `module` )
); 
CREATE TABLE `sys_domain` (
	KEY `hidden` ( `hidden` ),
	KEY `sorting` ( `sorting` ) 
);
CREATE TABLE `sys_filemounts` (
	KEY `deleted` ( `deleted` , `hidden` )
);
CREATE TABLE `sys_language` (
	KEY `hidden` ( `hidden` ) 
);
CREATE TABLE `sys_note` (
	KEY `deleted` ( `deleted` ) 
);
CREATE TABLE `sys_template` (
	KEY `deleted` ( `deleted` , `hidden` ),
	KEY `sorting` ( `sorting` ) 
);
CREATE TABLE `tt_content` (
	KEY `deleted` ( `deleted` , `hidden` ),
	KEY `sorting` ( `sorting` ),
	KEY `sys_language_uid` ( `sys_language_uid` ) 
);


/* non-core typical extensions */
CREATE TABLE `static_countries` (
	KEY `pid` ( `pid` ) 
);
CREATE TABLE `static_country_zones` (
	KEY `pid` ( `pid` ) 
);
CREATE TABLE `static_territories` (
	KEY `pid` ( `pid` ) 
);

CREATE TABLE `tt_news` (
	KEY `delverhidstetfeg` ( `deleted` , `t3ver_state` , `hidden` , `starttime` , `endtime` , `fe_group` (20) ) 
);
CREATE TABLE `tt_news_related_mm` (
	KEY `fortab` ( `uid_foreign` , `tablenames` ( 10 ) ),
	KEY `loctab` ( `uid_local` , `tablenames` ( 10 ) ) 
);
CREATE TABLE `tx_realurl_uniqalias` (
	KEY `valalidtablangexp` ( `value_id` , `field_alias` , `field_id` , `tablename` , `lang` , `expire` ),
	KEY `valfalfidtab` ( `value_alias` (20) , `field_alias` , `field_id` , `tablename` ) 
);


/* other extensions indexes */
CREATE TABLE `sys_dmail_maillog` (
	KEY `response_type` ( `response_type` ),
	KEY `mr` ( `mid` , `response_type` ) 
);
CREATE TABLE `sys_dmail` (
	KEY `ssed` ( `scheduled` , `scheduled_end` , `deleted` ) 
);

CREATE TABLE `tx_veguestbook_entries` (
	KEY `newsdelhid` ( `uid_tt_news` , `deleted` , `hidden` ) 
);


ALTER TABLE `static_countries` DROP INDEX `uid` ;
ALTER TABLE `static_country_zones` DROP INDEX `uid` ;
ALTER TABLE `static_territories` DROP INDEX `uid` ;
ALTER TABLE `tx_realurl_uniqalias` DROP INDEX `bk_realurl01` ;
ALTER TABLE `tx_realurl_uniqalias` DROP INDEX `tablename` ;
