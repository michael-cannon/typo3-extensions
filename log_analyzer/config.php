<?php
/**
 * Configuration for log_analyzer
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: config.php,v 1.2 2011/05/12 10:48:30 peimic.comprock Exp $
 */
$config							= unserialize( $TYPO3_CONF_VARS['EXT']['extConf']['log_analyzer'] );

// uid of the page where FE plugin inserted
if (!defined(PAGE_UID))
   define('PAGE_UID',$config['page_uid']);

// base url for links in xls file
if (!defined(BASE_URL))
   define('BASE_URL',$config['base_url']);

// name of backend user group of whom report will be sent to
$be_group = $config['be_group'];

// Log report email settings
if (!defined(LOG_EMAIL_FROM_EMAIL))
   define('LOG_EMAIL_FROM_EMAIL',$config['log_email_from_email']);
if (!defined(LOG_EMAIL_FROM_NAME))
   define('LOG_EMAIL_FROM_NAME',$config['log_email_from_name']);
if (!defined(LOG_EMAIL_SUBJECT))
   define('LOG_EMAIL_SUBJECT',$config['log_email_subject']);
if (!defined(LOG_EMAIL_BODY))
   define('LOG_EMAIL_BODY',$config['log_email_body']);
if (!defined(IGNORE_CRON_CHANGES))
   define('IGNORE_CRON_CHANGES',$config['ignore_cron_changes']);

// default value for reports period
// currently 24 hours 
// 60 seconds * 60 minutes * 24 hours in a day
$report_period = $config['report_period'];

// EDIT NO FURTHER

// extension directory
if (!defined(EXT_ROOT))
   define(EXT_ROOT,dirname(__FILE__) .'/');

// rows in page in report
if (!defined(PAGE_ROWS))
   define('PAGE_ROWS',100);

// rows in page in report XLS
if (!defined(XLS_ROWS))
   define('XLS_ROWS',300);

// PEAR Spreadsheet directory
if (!defined(PEAR_ROOT))
   define('PEAR_ROOT', EXT_ROOT . "Spreadsheet/");

$tables_names = array(
                  'pages'       => 'Pages',
                  'tt_news'     => 'News',
                  'tt_content'  => 'Content',
                  'sys_template'  => 'TypoScript Templates',
                );

// field from which we get title
$tables_fields = array(
                  'pages'       => 'pages.title',
                  'tt_news'     => 'tt_news.title',
                  'tt_content'  => 'pages.title',
                  'be_users'    => 'be_users.username',
                );

$report_type = array(
                  'be'          => 1,
                  'view_daily'  => 2,
                  'view_custom' => 3,
                  'xls_mail'    => 4,
               );

$cron_tables = array(
                     'all',
                     //'tt_news',
                     //'tt_content'
                    );

// log enable/disable for debug uses
if (!defined(LOGGER))
   define('LOGGER',false);
?>
