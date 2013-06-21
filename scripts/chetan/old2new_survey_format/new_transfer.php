<?php

require_once("db_connect.php");

// PID of sysfolder containing survey results
define( 'RESULTS_PID', 484);

// PID of sysfolder containing survey questions
define( 'SURVEY_ID', 503);

// Source table containing previous (old format) survey results (fe_users)
define( 'TEST_FE_USERS', 'fe_users');

// Target table to be used for storing surver results in new format
define( 'TEST_SURVEY', 'tx_mssurvey_results_new');

// Table containing survey results in new format (tx_mssurvey_results)
define( 'SURVEY_RESULTS_TABLE', 'tx_mssurvey_results');

// Archive table (tx_srfeuserregistersurvey_results_archive)
define( 'TEST_SURVEY_ARCHIVE', 'tx_srfeuserregistersurvey_results_archive_new');

// Group-ID for BPM domain
define('BPM_DOMAIN_GROUP_ID', 18);

//Connect to database
$db = new db_connect();
$link = $db->connect();


// Proceed if successful in connecting to database
if ( $link) {

    $map_arr = array(
        'tx_bpmprofile_involvement' => 'involvement',
        'tx_bpmprofile_involvement_text' => 'involvement_Other',
        'tx_bpmprofile_industry' => 'company-primary-industry',
        'tx_bpmprofile_industry_text' => 'company-primary-industry_Other',
        'tx_bpmprofile_budget' => 'projected-overall-it-budget',
        'tx_bpmprofile_role' => 'role-in-purchasing-bpm-services',
        'tx_bpmprofile_interests' => 'reason-for-interest',
        'tx_bpmprofile_interests_text' => 'reason-for-interest_Other_value',
        'tx_bpmprofile_gain' => 'wish-to-gain-from-bpm',
        'tx_bpmprofile_how_heard' => 'how-hear-about-bpm',
        'tx_bpmprofile_training' => 'interested-bpm-training',
        'tx_bpmprofile_training_text' => 'interested-bpm-training_Other_value',
        'tx_bpmprofile_involvement_orientation' => 'bpm-involvement-orientation',
        'tx_bpmprofile_involvement_orientation_text' => 'bpm-involvement-orientation_Other',
        'tx_bpmprofile_reason_for_interest' => 'reason-for-interest',
        'tx_bpmprofile_reason_for_interest_text' => 'reason-for-interest_Other',
        'tx_bpmprofile_stage' => 'stage',
        'tx_bpmprofile_stage_text' => 'stage_Other',
        'tx_bpmprofile_policydefiner' => 'policy-definer',
        'tx_bpmprofile_policydefiner_text' => 'policy-definer_Other',
        'tx_bpmprofile_understanding' => 'understanding-of-soa',
        'tx_bpmprofile_understanding_text' => 'understanding-of-soa_Other_value',
        'tx_bpmprofile_projects' => 'top-3-process-management-projects',
        'tx_bpmprofile_projects_text' => 'top-3-process-management-projects_Other_value',
        'tx_bpmprofile_target_processes' => 'targeting-processes-or-systems',
        'tx_bpmprofile_target_processes_text' => 'targeting-processes-or-systems_Other_value',
        'tx_bpmprofile_comments' => 'comments'
    );

    $bpm_profile_fields = array_keys( $map_arr);
    $bpm_profile_fields[] = 'uid';

    // Fetch original old survey records stored together with user details
    $select_query = 'SELECT * FROM '.TEST_FE_USERS;
    $rs_bpm_profile = mysql_query($select_query);

    if ( $rs_bpm_profile) {
        if ( mysql_num_rows($rs_bpm_profile)) {
            $trans_queries = array();
            $arch_queries  = array();

            while ( $profile = mysql_fetch_assoc($rs_bpm_profile)) {
                unset($new_format_survey_results);
                $new_format_survey_results = array();

                foreach ( $profile as $name => $value) {
                    if ( array_key_exists($name, $map_arr) && strcmp('', trim($value))) {
                        $new_format_survey_results[] = '"'.$map_arr[$name].'":"'.$value.'"';
                    }
                }

                $survey_result = ( 0 < count($new_format_survey_results))
                                 ? implode(',', $new_format_survey_results)
                                 : '';

                if ( strcmp("", $survey_result)) {

                    if ( false !== ($survey_result_id = user_has_new_survey_results($profile['uid']))) {

                        // Generate query for archiving old survey results
                        $time = time();
                        $insert_record = array(
                            'pid' => RESULTS_PID,
                            'tstamp' => $time,
                            'crdate' => $time,
                            'cruser_id' => $profile['uid'],
                            'domain_group_id' => BPM_DOMAIN_GROUP_ID,
                            'survey_result_id' => $survey_result_id,
                            'survey_user_id'=> $profile['uid'],
                            'survey_tstamp' => $profile['tstamp'],
                            'survey_crdate' => $profile['crdate'],
                            'survey_result' => "'".mysql_real_escape_string($survey_result, $link)."'"
                        );

                        // Generate SQL query for archiving
                        $fields = array_keys($insert_record);
                        $fields = implode(', ', $fields);
                        $arch_queries[] = 'INSERT INTO '.TEST_SURVEY_ARCHIVE.' ('.$fields.')'
                                        . 'VALUES ('.implode(', ', $insert_record).');';
                    } else {
                        // Generate query for adding survey results in transaction table
                        $insert_record = array(
                            'pid' => RESULTS_PID,
                            'tstamp' => $profile['tstamp'],
                            'crdate' => $profile['crdate'],
                            'deleted' => $profile['deleted'],
                            'fe_cruser_id' => $profile['uid'],
                            'domain_group_id' => BPM_DOMAIN_GROUP_ID,
                            'surveyid' => SURVEY_ID,
                            'results' => "'".mysql_real_escape_string($survey_result, $link)."'"
                        );

                        // Generate SQL query
                        $fields = array_keys($insert_record);
                        $fields = implode(', ', $fields);
                        $trans_queries[] = 'INSERT INTO '.TEST_SURVEY.' ('.$fields.')'
                                         . 'VALUES ('.implode(', ', $insert_record).');';
                    }
                }
            }

            $trans_queries = implode('<br><br>', $trans_queries);
            $arch_queries = implode('<br><br>', $arch_queries);

            // echo $trans_queries.'<br><br>############### ARCHIVE SURVEY RECORDS STARTS FROM HERE ###########<br><br>';
            echo $arch_queries;
        }
    }
}

/**
 * Checks for existence of survey record in new format for provided user-id
 *
 * @param  integer $user_id     Unique-ID of user record in database table (fe_users)
 * @return integer | boolean    ID of user survey record if successful, else false
 */
function user_has_new_survey_results( $user_id) {
    $has_new_survey_results = false;
    $user_id = intval($user_id);

    if ( 0 < $user_id) {

        $select_query = 'SELECT * FROM '.SURVEY_RESULTS_TABLE.' WHERE fe_cruser_id = '.$user_id;
        // Uncomment to edit
        // echo $select_query;
        $rs = mysql_query($select_query);

        if ( $rs) {
            if ( mysql_num_rows( $rs)) {
                $row = mysql_fetch_assoc($rs);
//                echo '<PRE>'.print_r($row, true).'</PRE>';
                $has_new_survey_results = $row['uid'];
            }
        }
    }

    return $has_new_survey_results;
}

?>
