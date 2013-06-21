<?php

require_once("db_connect.php");

// Define constants for survey domain user groups
define( 'BPM_MEMBER', 18);
define( 'SOA_MEMBER', 17);

// Define constants for user groups
define( 'FREE_MEMBER', 1);
define( 'PROFESSIONAL_MEMBER', 2);

// PID for survey questions
define( 'QUESTIONS_PID', 503);

// PID for survey results
define( 'RESULTS_PID', 484);

// Target table to be used for separate domain specific user survey records
define( 'SURVEY_RESULTS_TABLE', 'tx_mssurvey_results_new');


//Connect to database
$db = new db_connect();
$link = $db->connect();

// Proceed if successful in connecting to database
if ( $link) {

    // fetch all survey results
    $user_survey_results = getSurveyResults();
    
    // Get filtered records
    $domain_survey_results = filerDomainSurveyResults( $user_survey_results);
    
    // Generate sql
    $sql = generateSQL( $domain_survey_results);
    
    /*$query = "SELECT `uid`, `pid`, `cruser_id`, `fe_cruser_id`, `domain_group_id`, `results` FROM `test` order by domain_group_id, results";
    
    if ( $rs = mysql_query( $query)) {
        if ( mysql_num_rows( $rs)) {
            $tmp = '<table border="1" cellpadding="2" cellspacing="2" width="100%">  <tbody>';
;
            
            while ( $row = mysql_fetch_assoc( $rs)) {
                $tmp .= '<tr>  <td>'.$row['uid'].'</td>      <td>'.$row['pid'].'</td>      <td>'.$row['cruser_id'].'</td>      <td>'.$row['fe_cruser_id'].'</td>      <td>'.$row['domain_group_id'].'</td>      <td>'.$row['results'].'</td>    </tr> ';
            }
            
            $tmp .=  '</tbody></table>';
        }
        
        echo $tmp;
    }*/
    
    
    
    
    //echo '<PRE>'.print_r( $sql, true).'</PRE>';
        
} else {

    echo 'Error: Unable to connect to database';
    
}

/**
 * Function to generate sql for insertion of
 * segregated BPM and SOA user survey results
 */
function generateSQL( $domain_survey_results) {

    $sql = '';
    
    $sql_struc = '
        INSERT INTO '.SURVEY_RESULTS_TABLE.'
        (pid, tstamp, crdate, fe_cruser_id, domain_group_id, surveyid, results)
        VALUES ( ';

    if ( is_array( $domain_survey_results) && !empty( $domain_survey_results)) {
    
        foreach ( $domain_survey_results['bpm'] as $result) {
        
            $insert_fields  = '';
            $insert_fields .= $result['pid'].','.$result['tstamp'].',';
            $insert_fields .= $result['crdate'].','.$result['fe_cruser_id'].',';
            $insert_fields .= $result['domain_group_id'].','.$result['surveyid'].',';
            $insert_fields .= '\''.addslashes( $result['results']).'\'';
            
            $sql .= $sql_struc.$insert_fields.' );';
        
        }

        $sql .= "\n\n#\n# Records for SOA survey results starts here\n#\n";

        foreach ( $domain_survey_results['soa'] as $result) {
        
            $insert_fields  = '';
            $insert_fields .= $result['pid'].','.$result['tstamp'].',';
            $insert_fields .= $result['crdate'].','.$result['fe_cruser_id'].',';
            $insert_fields .= $result['domain_group_id'].','.$result['surveyid'].',';
            $insert_fields .= '\''.addslashes( $result['results']).'\'';
            
            $sql .= $sql_struc.$insert_fields.' );';
        
        }
    
        $sql .= "\n\n#\n# Records for BPM-SOA archive survey results starts here\n#\n";

        foreach ( $domain_survey_results['bpm-soa-archive'] as $result) {
        
            $insert_fields  = '';
            $insert_fields .= $result['pid'].','.$result['tstamp'].',';
            $insert_fields .= $result['crdate'].','.$result['fe_cruser_id'].',';
            $insert_fields .= $result['domain_group_id'].','.$result['surveyid'].',';
            $insert_fields .= '\''.addslashes( $result['results']).'\'';
            
            $sql .= $sql_struc.$insert_fields.' );';
        
        }
    
    }

	// MLC carray over non RESULTS_PID items
	$sql .= '
		insert into ' . SURVEY_RESULTS_TABLE . '
			(pid
				, tstamp
				, crdate
				, fe_cruser_id
				, domain_group_id
				, surveyid
				, results) 
			SELECT pid
				, tstamp
				, crdate
				, fe_cruser_id
				, domain_group_id
				, surveyid
				, results 
			FROM tx_mssurvey_results
				WHERE pid != ' . RESULTS_PID . ';';
    
    echo '<PRE>'.print_r( $sql, true).'</PRE>';
    return $sql;
}

/**
 * Function to segregate BPM and SOA user survey results
 * from previous merged records
 */
function filerDomainSurveyResults( $user_survey_results) {

    $survey_results = array();
    
    // fetch all surveys items for BPM
    $bpm_survey_item_titles = getDomainSurveyItems( BPM_MEMBER);
    $bpm_survey_item_count  = count($bpm_survey_item_titles);
    
    // fetch all surveys items for SOA
    $soa_survey_item_titles = getDomainSurveyItems( SOA_MEMBER);
    $soa_survey_item_count  = count($soa_survey_item_titles);
    
    // titles for domain specific questions
    $bpm_only_survey_item_titles = array_diff($bpm_survey_item_titles, $soa_survey_item_titles);
    $soa_only_survey_item_titles = array_diff($soa_survey_item_titles, $bpm_survey_item_titles);
    
    if ( is_array( $user_survey_results) && !empty( $user_survey_results)) {
    
        foreach ( $user_survey_results as $key => $result) {
        
            $result_array = results2array( $result);
            $check = array (
                                'bpm'      => true,
                                'soa'      => true,
                                'bpm_only' => true,
                                'soa_only' => true
                           );
            $x = 3;

            while ( $x--) {

                $keys = array();
                
                foreach ( $result_array as $value) {
                    $keys[] = key($value);
                }
                
                $keys_count = count( $keys);
                $answered_item_titles = array();
                
                for ( $i = $keys_count-1; $i >= 0; $i--) {
                    $key = array_shift(explode('_', $keys[$i]));
                    if ( $key !== $answered_item_titles[0]) {
                        array_unshift($answered_item_titles, $key); 
                    }
                }
                
                $answered_item_count = count($answered_item_titles);
                if ( !in_array('comments', $answered_item_titles)) {
                    $answered_item_count++;
                }

                // Check for whole BPM survey
                if ( $check['bpm']) {
                    // If answered items are greater than current BPM survey items
                    // it means there is a probability of existence of current 
                    // BPM survey results
                    $start_index    = $answered_item_count - $bpm_survey_item_count;
                    $last_items     = array_slice($answered_item_titles, $start_index, $bpm_survey_item_count);
                    
                    // If end keys of survey results matches with current survey
                    // titles then record contains current survey results
                    $has_curr_survey_results = true;
                    
                    foreach ( $bpm_survey_item_titles as $value) {
                        if ( !in_array($value, $last_items) && $value !== 'comments') {
                            $has_curr_survey_results = false;
                            break;
                        }
                    }
                    
                    if ($has_curr_survey_results) {
                        // Put current records in survey results table
                        // and archive others in BPM account
                        $curr_bpm_results = array();
                        
                        for ( $i = (count($result_array)-1); (is_array($result_array[$i])) && (key($result_array[$i]) !== 'involvement'); $i--) {
                            $curr_bpm_results = array_merge(array_pop($result_array), $curr_bpm_results);
                        }
                        
                        $curr_bpm_results = array_merge(array_pop($result_array), $curr_bpm_results);
                        //$survey_results['bpm']['current'] = createCSV($curr_bpm_results);
                        
                        $tmp = $result;
                        $tmp['pid']             = RESULTS_PID;
                        $tmp['domain_group_id'] = BPM_MEMBER;
                        $tmp['results']         = createCSV($curr_bpm_results);

                        $survey_results['bpm'][] = $tmp;
                        
                        $check['bpm'] = false;
                    }
                }
                
                // Check for whole SOA survey
                if ( $check['soa']) {
                    // If answered items are greater than current BPM survey items
                    // it means there is a probability of existence of current 
                    // SOA survey results
                    $start_index    = $answered_item_count - $soa_survey_item_count;
                    $last_items     = array_slice($answered_item_titles, $start_index);
                    
                    // If end keys of survey results matches with current survey
                    // titles then record contains current survey results
                    $has_curr_survey_results = true;
                    
                    foreach ( $soa_survey_item_titles as $value) {
                        if ( !in_array($value, $last_items) && $value !== 'comments') {
                            $has_curr_survey_results = false;
                            break;
                        }
                    }
                    
                    if ($has_curr_survey_results) {
                        // Put current records in survey results table
                        // and archive others in BPM account
                        $curr_soa_results = array();
                        
                        for ( $i = (count($result_array)-1); (is_array($result_array[$i])) && (key($result_array[$i]) !== 'involvement-soa'); $i--) {
                            $curr_soa_results = array_merge(array_pop($result_array), $curr_soa_results);
                        }
                        
                        $curr_soa_results = array_merge(array_pop($result_array), $curr_soa_results);
                        //$survey_results['soa']['current'] = createCSV($curr_soa_results);
                        
                        $tmp = $result;
                        $tmp['pid']             = RESULTS_PID;
                        $tmp['domain_group_id'] = SOA_MEMBER;
                        $tmp['results']         = createCSV($curr_soa_results);

                        $survey_results['soa'][] = $tmp;
                        
                        //echo '<PRE>'.print_r( $survey_results['soa']['current'], true).'</PRE>';
                        $check['soa'] = false;
                    }
                }
                
                // Check for whole BPM Only
                if ( $check['bpm_only'] && ($check['soa'] == true)) {
                    // If answered items are greater than current BPM survey items
                    // it means there is a probability of existence of current 
                    // BPM only survey results
                    $start_index    = $answered_item_count - count($bpm_only_survey_item_titles);

                    //**********************
                    $start_index--;     // $bpm_only_survey_item_titles don't contain Comment field
                    //**********************
                    
                    $last_items     = array_slice($answered_item_titles, $start_index);
                    
                    // If end keys of survey results matches with current survey
                    // titles then record contains current survey results
                    $has_curr_survey_results = true;
                    
                    foreach ( $bpm_only_survey_item_titles as $value) {
                        if ( !in_array($value, $last_items) && $value !== 'comments') {
                            $has_curr_survey_results = false;
                            break;
                        }
                    }
                    
                    if ($has_curr_survey_results) {
                        // Put current records in survey results table
                        // and archive others in BPM account
                        $curr_bpm_results = array();
                        
                        for ( $i = (count($result_array)-1); (is_array($result_array[$i])) && (key($result_array[$i]) !== 'involvement'); $i--) {
                            $curr_bpm_results = array_merge(array_pop($result_array), $curr_bpm_results);
                        }
                        
                        $curr_bpm_results = array_merge(array_pop($result_array), $curr_bpm_results);
                        
                        //$survey_results['bpm_only']['current'] = createCSV($curr_bpm_results);
                        
                        $tmp = $result;
                        $tmp['pid']             = RESULTS_PID;
                        $tmp['domain_group_id'] = BPM_MEMBER;
                        $tmp['results']         = createCSV($curr_bpm_results);

                        $survey_results['bpm'][] = $tmp;
                        
                        //echo '<PRE>'.print_r( $survey_results['bpm_only']['current'], true).'</PRE>';
                        $check['bpm_only'] = false;
                        $check['bpm'] = false;
                    }
                }
                
                // Check for SOA Only
                if ( $check['soa_only'] && ($check['bpm'] == true)) {
                    // If answered items are greater than current BPM survey items
                    // it means there is a probability of existence of current 
                    // SOA only survey results
                    $start_index    = $answered_item_count - count($soa_only_survey_item_titles);

                    //**********************
                    $start_index--;     // $soa_only_survey_item_titles don't contain Comment field
                    //**********************
                    
                    
                    $last_items     = array_slice($answered_item_titles, $start_index);
                    
                    // If end keys of survey results matches with current survey
                    // titles then record contains current survey results
                    $has_curr_survey_results = true;
                    
                    foreach ( $soa_only_survey_item_titles as $value) {
                        if ( !in_array($value, $last_items) && $value !== 'comments') {
                            $has_curr_survey_results = false;
                            break;
                        }
                    }
                    
                    if ($has_curr_survey_results) {
                        $curr_soa_results = array();
                        
                        for ( $i = (count($result_array)-1); (is_array($result_array[$i])) && (key($result_array[$i]) !== 'involvement-soa'); $i--) {
                            $curr_soa_results = array_merge(array_pop($result_array), $curr_soa_results);
                        }
                        
                        $curr_soa_results = array_merge(array_pop($result_array), $curr_soa_results);
                        //$survey_results['soa_only']['current'] = createCSV($curr_soa_results);
                        
                        $tmp = $result;
                        $tmp['pid']             = RESULTS_PID;
                        $tmp['domain_group_id'] = SOA_MEMBER;
                        $tmp['results']         = createCSV($curr_soa_results);

                        $survey_results['soa'][] = $tmp;
                        
                        $check['soa_only'] = false;
                        $check['soa'] = false;
                    }
                }
            } // End of while
            
            // Check if anything to dump in archive
            if ( !empty($result_array)) {
                
                $curr_soa_results = array();
                
                foreach ( $result_array as $value) {
                    //list($key, $ans) = each($value);
                    $curr_soa_results = array_merge($value, $curr_soa_results);
                }
                
                $tmp = $result;
                $tmp['pid']             = RESULTS_PID;
                $tmp['domain_group_id'] = SOA_MEMBER;
                $tmp['results']         = createCSV($curr_soa_results);

                $survey_results['bpm-soa-archive'][] = $tmp;
            }
        }
    }

    return $survey_results;
}

/**
 * Function to get all the user survey results
 * from database
 */
function getSurveyResults() {

    $survey_results = array();

    $select_query = 'SELECT * FROM tx_mssurvey_results';
    
    $rs = mysql_query( $select_query);
    
    if ( $rs) {
    
       if ( mysql_num_rows( $rs)) {
       
           while ( $row = mysql_fetch_assoc( $rs)) {
           
               $survey_results[] = $row;
               
           }
       }
    }
    
    return $survey_results;
}

/**
 * Function to get survey item names for provided
 * survey domain user group
 */
function getDomainSurveyItems( $domain_user_group) {

	$select_query = '
	   SELECT DISTINCT *
	   FROM 
	       tx_mssurvey_items AS si
	   LEFT JOIN
	       tx_mssurvey_items_item_groups_mm AS si_gid ON si.uid = si_gid.uid_local
	   WHERE
	       si.pid IN ('.QUESTIONS_PID.')
		   AND uid_foreign IN ( '.$domain_user_group.', '.FREE_MEMBER.' )
	       AND deleted = 0 AND hidden = 0
	   ORDER BY si.sorting
    ';

    if ( $rs = mysql_query( $select_query)) {
    
		if ( mysql_num_rows( $rs)) {
		
			while ( $row = mysql_fetch_assoc( $rs)) {
			
				$tmp[$row['uid']][] = $row['uid_foreign'];
				$surveyItems[$row['uid']] = $row['title'];
				
			}
		
            if ( !empty( $tmp)) {

                $domainSurveyGroup = array( $domain_user_group, FREE_MEMBER);

			    foreach ( $tmp as $key => $value) {
			    
			        if ( is_array( $value) && !empty( $value)) {
			        
			            if ( count( array_diff( $domainSurveyGroup, $value)) > 0) {
			            
			                unset( $surveyItems[$key]);
			                
			            }
			            
			        }
			        
			    }
			    
			}
			
		}
		
	}

    return $surveyItems;
}

function getDomainSurveyItem( $item_title) {

    $surveyItem = array();

	$select_query = '
	   SELECT DISTINCT *
	   FROM 
	       tx_mssurvey_items
	   WHERE
	       pid = '.QUESTIONS_PID.'
	       AND title = \''.$item_title.'\'
	       AND deleted = 0 AND hidden = 0
    ';

    // echo $select_query;
    
    if ( $rs = mysql_query( $select_query)) {
    
		if ( mysql_num_rows( $rs)) {
		
			$surveyItem = mysql_fetch_assoc( $rs);
			
		}
	}
		
    return $surveyItem;
}

/**
 * Function to convert user survey results CSV
 * to array
 *
 * @param array $survey_result  CSV of user survey results
 * @return array
 */
function results2array( $survey_result) {

    $result_array = array();

    
    if ( is_array( $survey_result) && !empty( $survey_result)) {
    
        $results = $survey_result['results'];
        $answers = explode( '",', $results);
        
        foreach ( $answers as $key => $value) {

            list( $item_name, $answer) = explode( '":', $value);
            $item_name = trim($item_name, '"');
            $answer    = trim($answer, '"');
            
            $result_array[$key][$item_name] = $answer;

        }
    }

    //echo '<PRE>'.print_r($result_array, true).'</PRE>';
        
    return $result_array;
}

/**
 * Function to generate CSV of user replies 
 * to survey for storing in database
 */
function createCSV( $surveyResult) {
	$csv = '';

	if ( count( $surveyResult) > 0) {
		foreach ( $surveyResult as $item => $value) {
		    if ( !preg_match( '/(.)*_hidden$/i', $item)) {
				$csv .= ( strcmp( trim( $value), ''))
						? '"'.trim( $item).'":"'.trim( $value).'",'
						: '';
		    }
		}

		$csv = trim( $csv, ',');
	}

	return $csv;
}

?>
