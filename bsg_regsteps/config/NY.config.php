<?php

define( 'BSG_REG_DEFAULT_PC', 'NY9PUBW' );
define( 'BSG_REG_MEM_PC', 'NY9MEMW' );
define( 'BSG_REG_PRO_PC', 'NY9PROW' );
define( 'BSG_REG_CORP_PC', 'NY9CORPW' );
define( 'BSG_REG_CONF_CITY', 'New York' );
define( 'BSG_REG_CONF_USERGROUP', '12' );

$regHeader						= <<<EOD
<h1>BrainStorm New York Registration</h1>
<p>
BrainStorm New York will take place November 2-5 at the Westin Times Square.
</p>
<p>
Please use the following form to complete your registration.
</p>
EOD;

$confSeries	= array(
	'No Conference Selection' => '',
	'BPM Conference' => 'a005000000DCi1U',
	'BA Conference' => 'a005000000DCi1s',
	'Cloud Symposium' => 'a005000000Fbotl',
	'BDM Symposium' => 'a005000000DZU9Z',
);

$confSeriesTrainingOnly	= 'a005000000DCi2R';

$courses = array (
	array(
		'title'=>'<b>November 2</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'November 2',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1l3', 'name'=>'BA 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1l4', 'name'=>'BPM 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1lH', 'name'=>'BR & BDM 101', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1oI', 'name'=>'Simple, Open & Standardized BPM Metrics', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oK', 'name'=>'SOA 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001Xov2', 'name'=>'Methodologies and Approaches for BPM', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'<b>November 3</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'November 3',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1ks', 'name'=>'Analyzing the "As Is" and Creating the "To Be"', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1me', 'name'=>'Business Architecture Integration', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mL', 'name'=>'Facilitation Skills for Process Improvement Projects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1ok', 'name'=>'SOA for Architects', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'<b>Conference Selection</b>',
		'thankyoutitle'=>'November 3',
		'type'=>'conference',
		'courses' => array(
			array('uid'=>'DAY1-a005000000DCi1s', 'name'=>'BA Conference Nov 3', 'type'=> 'conference-1d'),
			array('uid'=>'DAY1-a005000000DCi1U', 'name'=>'BPM Conference Nov 3', 'type'=> 'conference-1d'),
			array('uid'=>'2DAYSNS', 'name'=>'2-Day Non-Sponsoring Solution Provider Package - November 3-16', 'type'=> 'non-sponsoring'),
		),
	),

	array(
		'title'=>'<b>November 4</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'November 4',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1ku', 'name'=>'BPM and Six Sigma', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mG', 'name'=>'Business Transformation Methodologies', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mJ', 'name'=>'Data Architecture for Business Architects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oG', 'name'=>'Process Modeling With BPMN (2-Day Course)', 'type'=> 'training-per-2d'),
			array('uid'=>'a0B50000001W1oC', 'name'=>'Service Oriented Integration', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oq', 'name'=>'BPM and Lean: Taking the Waste Out of Work', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1o2', 'name'=>'Process Measurement and Metrics ', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'<b>Conference Selection</b>',
		'thankyoutitle'=>'November 4',
		'background'=>'#eeeeee',
		'type'=>'conference',
		'courses' => array(
			array('uid'=>'DAY2-a005000000FrWxh', 'name'=>'BA Conference Nov 4', 'type'=> 'conference-1d'),
			array('uid'=>'DAY2-a005000000DZU9Z', 'name'=>'BDM Symposium Nov 4', 'type'=> 'conference-1d'),
			array('uid'=>'DAY2-a005000000FrWxg', 'name'=>'BPM Conference Nov 4', 'type'=> 'conference-1d'),
			array('uid'=>'DAY2-a005000000Fbotl', 'name'=>'Cloud Symposium Nov 4', 'type'=> 'conference-1d'),
		),
	),

	array(
		'title'=>'<b>November 5</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'November 5',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001Xwy3', 'name'=>'Organizational Change Management', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mK', 'name'=>'Business Rules Driven Requirements', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mB', 'name'=>'Business Architecture / IT Architecture Alignment', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mk', 'name'=>'Designing Service Oriented Solutions', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mf', 'name'=>'Developing Business Process Models in the Real-World', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nm', 'name'=>'Establishing Business Process Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nw', 'name'=>'Establishing SOA Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
		),
	),
);

// put same day courses that conflict with 1st day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY1', "'a0B50000001W1ks', 'a0B50000001W1me', 'a0B50000001W1nO', 'a0B50000001W1mL', 'a0B50000001W1ok'" );

// put same day courses that conflict with 2nd day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY2', "'a0B50000001W1ku', 'a0B50000001W1mG', 'a0B50000001W1mJ', 'a0B50000001W1oG', 'a0B50000001W1oC', 'a0B50000001W1oq', 'a0B50000001W1o2'" );

// put the 2-day course and then the conflicting second-day 1-day courses
define( 'BSG_REG_COURSE_CONFLICT_GENERAL', "'a0B50000001W1oG', 'a0B50000001W1mK', 'a0B50000001W1mB', 'a0B50000001W1mk', 'a0B50000001W1mf', 'a0B50000001W1nm', 'a0B50000001W1nw'" );

$checkCourseConfig = <<<EOD
function checkCourseConfig( course ) {
	var doAlert = false;
	var errorMsg = "Select 1 training course OR 1 conference package PER DAY.";

	switch(course) {
		case 'course_2' : if(\$F(course) != 0) {
			if(\$F('course_3') != 0) { $('course_2').options[$('course_2').length-1].selected = true; doAlert = true; }
			if(doAlert) { alert(errorMsg); }
		}
						  break;
		case 'course_3' : if(\$F(course) != 0) {
			if(\$F('course_2') != 0) { $('course_3').options[$('course_3').length-1].selected = true; doAlert = true; }
			if(doAlert) { alert(errorMsg); }
		}
						  break;
		case 'course_4' : if(\$F(course) != 0) {
			if(\$F('course_5') != 0) { $('course_4').options[$('course_4').length-1].selected = true; doAlert = true; }
			if(doAlert) { alert(errorMsg); }
		}
						  break;
		case 'course_5' : if(\$F(course) != 0) {
			if(\$F('course_4') != 0) { $('course_5').options[$('course_5').length-1].selected = true; doAlert = true; }
			if(doAlert) { alert(errorMsg); }
		}
						  break;
	}
}
EOD;

define( 'BSG_REG_HEADER', $regHeader );
define( 'BSG_REG_COURSE_CHECK', $checkCourseConfig );

?>