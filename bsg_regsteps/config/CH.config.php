<?php

define( 'BSG_REG_DEFAULT_PC', 'CH9PUBW' );
define( 'BSG_REG_MEM_PC', 'CH9MEMW' );
define( 'BSG_REG_PRO_PC', 'CH9PROW' );
define( 'BSG_REG_CORP_PC', 'CH9CORPW' );
define( 'BSG_REG_CONF_CITY', 'Chicago' );
define( 'BSG_REG_CONF_USERGROUP', '9' );

$regHeader						= <<<EOD
<h1>BrainStorm Chicago Registration</h1>
<p>
BrainStorm Chicago will take place April 6-9. Please use the following form to complete your registration.
</p>
EOD;

$confSeries	= array(
	'No Conference Selection' => '',
	'BA Conference' => 'a005000000DAsJn',
	'BDM Symposium' => 'a005000000DZKdr',
	'BPM Conference' => 'a005000000DAsJY',
//	'OP Symposium' => 'a005000000DZKdw',
//	'SOA Conference' => 'a005000000DAsJd',
);

$confSeriesTrainingOnly	= 'a005000000DAsJx';

$courses = array (
	array(
		'title'=>'<b>April 6</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'April 6',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001ViLp', 'name'=>'BA 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViLo', 'name'=>'BR & BDM 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViLi', 'name'=>'BPM 101', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViLn', 'name'=>'Developing Process-Centric, Business Requirements (2-Day Course) ', 'type'=> 'training-per-2d'),
//			array('uid'=>'a0B50000001ViLs', 'name'=>'SOA 101', 'type'=> 'training-per-1d'),
		),
	),

/*
	array(
		'title'=>'',
		'subtitle'=>'AM Workshops (9am-12noon)',
		'thankyoutitle'=>'April 6',
		'background'=>'#eeeeee',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a005000000AhOrL', 'name'=>'Collaborative Manufacturing', 'type'=> 'workshops'),
			array('uid'=>'a005000000AhOrV', 'name'=>'Nimble Banking', 'type'=> 'workshops'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'PM Workshops (2pm-5pm)',
		'thankyoutitle'=>'April 6',
		'background'=>'#eeeeee',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a005000000AhOrG', 'name'=>'Smart Retailing', 'type'=> 'workshops'),
			array('uid'=>'a005000000AhOrQ', 'name'=>'Personalized Insurance', 'type'=> 'workshops'),
		),
	),
*/

	array(
		'title'=>'<b>April 7</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'April 7',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001ViLk', 'name'=>'Analyzing the "As Is" and Creating the "To Be"', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViLt', 'name'=>'Business Architecture Integration', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViLj', 'name'=>'Facilitation Skills for Process Improvement Projects', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViLx', 'name'=>'SOA for Architects', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'<b>Conference Packages</b>',
		'thankyoutitle'=>'April 7',
		'type'=>'conference',
		'courses' => array(
			array('uid'=>'DAY1', 'name'=>'1-Day Conference Package - April 7 Only', 'type'=> 'conference-1d'),
			array('uid'=>'2DAYS', 'name'=>'2-Day Conference Package - April 7-8', 'type'=> 'conference-2d'),
			array('uid'=>'2DAYSNS', 'name'=>'2-Day Non-Sponsoring Solution Provider Package - April 7-8', 'type'=> 'non-sponsoring'),
		),
	),

	array(
		'title'=>'<b>April 8</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'April 8',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
//			array('uid'=>'a0B50000001ViLz', 'name'=>'BPM and Six Sigma', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViLu', 'name'=>'Business Transformation Methodologies', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViLm', 'name'=>'Data Architecture for Business Architects', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViM2', 'name'=>'Process Modeling With BPMN (2-Day Course)', 'type'=> 'training-per-2d'),
//			array('uid'=>'a0B50000001ViLy', 'name'=>'Service Oriented Integration', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViLr', 'name'=>'Using Simulations to Increase Process Efficiency', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViLl', 'name'=>'Process Management and Metrics ', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'<b>Conference Packages</b>',
		'thankyoutitle'=>'April 8',
		'background'=>'#eeeeee',
		'type'=>'conference',
		'courses' => array(
			array('uid'=>'DAY2', 'name'=>'1-Day Conference Package - April 8 Only', 'type'=> 'conference-1d'),
		),
	),

	array(
		'title'=>'<b>April 9</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'April 9',
		'type'=>'training',
		'courses' => array(
//			array('uid'=>'a0B50000001ViLv', 'name'=>'Advanced Process Management Principles & Practices', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViM4', 'name'=>'Business Rules Driven Requirements', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViM3', 'name'=>'Business Architecture / IT Architecture Alignment', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViM0', 'name'=>'Establishing Business Process Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViM8', 'name'=>'Establishing SOA Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001ViMC', 'name'=>'Designing Service Oriented Solutions', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001ViM7', 'name'=>'Developing Business Process Models in the Real-World', 'type'=> 'training-per-1d'),
		),
	),

/*
	array(
		'title'=>'',
		'thankyoutitle'=>'April 9',
		'subtitle'=>'AM Workshops (9am-12noon)',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a0B50000000Zb8Z', 'name'=>'Business Process Management with a Business Decision Management and Business Rules Approach', 'type'=> 'training-per-1d'),
		),
	),

//	array(
		'title'=>'',
		'subtitle'=>'PM Workshops (2pm-5pm)',
		'thankyoutitle'=>'April 17',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a005000000Ai9f2', 'name'=>'Building a BPM Center of Excellence', 'type'=> 'workshops'),
			array('uid'=>'a005000000Ai9f7', 'name'=>'Building an SOA Center of Excellence', 'type'=> 'workshops'),
		),
	),
*/
);

// put same day courses that conflict with 1st day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY1', "'a0B50000001ViLk', 'a0B50000001ViLt', 'a0B50000001ViLn', 'a0B50000001ViLj', 'a0B50000001ViLx'" );

// put same day courses that conflict with 2nd day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY2', "'a0B50000001ViLz', 'a0B50000001ViLu', 'a0B50000001ViLm', 'a0B50000001ViM2', 'a0B50000001ViLy', 'a0B50000001ViLr', 'a0B50000001ViLl'" );

// put the 2-day course and then the conflicting second-day 1-day courses
define( 'BSG_REG_COURSE_CONFLICT_GENERAL', "'a0B50000001ViM2', 'a0B50000001ViM4', 'a0B50000001ViM3', 'a0B50000001ViM0', 'a0B50000001ViM8', 'a0B50000001ViMC', 'a0B50000001ViM7'" );

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