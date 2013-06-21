<?php

define( 'BSG_REG_DEFAULT_PC', 'SF8PUBW' );
define( 'BSG_REG_MEM_PC', 'SF8MEMW' );
define( 'BSG_REG_PRO_PC', 'SF8PROW' );
define( 'BSG_REG_CORP_PC', 'SF8CORPW' );
define( 'BSG_REG_CONF_CITY', 'San Francisco' );
define( 'BSG_REG_CONF_USERGROUP', '10' );

$regHeader						= <<<EOD
<h1>BrainStorm San Francisco Registration</h1>
<p>
BrainStorm San Francisco will take place June 29-July 2 in San Francisco.
</p>
<p>
Please use the following form to complete your registration.
</p>
EOD;

$confSeries	= array(
	'No Conference Selection' => '',
	'BA Conference' => 'a005000000DCi0B',
	'BPM Conference' => 'a005000000DCi0C',
	'BDM/Business Rules Symposium' => 'a005000000DCi0a',
);

$confSeriesTrainingOnly	= 'a005000000DCi0f';

$courses = array (
	array(
		'title'=>'<b>June 29</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'June 29',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1l2', 'name'=>'BA 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1l7', 'name'=>'BR & BDM 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1ll', 'name'=>'BPM 101', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1mg', 'name'=>'Developing Process-Centric, Business Requirements (2-Day Course) ', 'type'=> 'training-per-2d'),
//			array('uid'=>'a0B50000001W1oQ', 'name'=>'Simple, Open & Standardized', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oa', 'name'=>'SOA 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001Xous', 'name'=>'Methodologies and Approaches for BPM', 'type'=> 'training-per-1d'),
		),
	),

/*
//	array(
		'title'=>'',
		'subtitle'=>'AM Workshops (9am-12noon)',
		'thankyoutitle'=>'June 29',
		'background'=>'#eeeeee',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a005000000AhOrL', 'name'=>'Collaborative Manufacturing', 'type'=> 'workshops'),
			array('uid'=>'a005000000AhOrV', 'name'=>'Nimble Banking', 'type'=> 'workshops'),
		),
	),

//	array(
		'title'=>'',
		'subtitle'=>'PM Workshops (2pm-5pm)',
		'thankyoutitle'=>'June 29',
		'background'=>'#eeeeee',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a005000000AhOrG', 'name'=>'Smart Retailing', 'type'=> 'workshops'),
			array('uid'=>'a005000000AhOrQ', 'name'=>'Personalized Insurance', 'type'=> 'workshops'),
		),
	),
*/

	array(
		'title'=>'<b>June 30</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'June 30',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1ki', 'name'=>'Analyzing the "As Is" and Creating the "To Be"', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mC', 'name'=>'Business Architecture Integration', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1np', 'name'=>'Facilitation Skills for Process Improvement Projects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1of', 'name'=>'SOA for Architects', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'<b>Conference Packages</b>',
		'thankyoutitle'=>'June 30',
		'type'=>'conference',
		'courses' => array(
			array('uid'=>'DAY1', 'name'=>'1-Day Conference Package - June 30 Only', 'type'=> 'conference-1d'),
			array('uid'=>'2DAYS', 'name'=>'2-Day Conference Package - June 30-July 1', 'type'=> 'conference-2d'),
			array('uid'=>'2DAYSNS', 'name'=>'2-Day Non-Sponsoring Solution Provider Package - June 30-July 1', 'type'=> 'non-sponsoring'),
		),
	),

	array(
		'title'=>'<b>July 1</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'July 1',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
//			array('uid'=>'a0B50000001W1lq', 'name'=>'BPM and Six Sigma', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mj', 'name'=>'Business Transformation Methodologies', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1mH', 'name'=>'Data Architecture for Business Architects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oB', 'name'=>'Process Modeling With BPMN (2-Day Course)', 'type'=> 'training-per-2d'),
			array('uid'=>'a0B50000001W1oL', 'name'=>'Service Oriented Integration', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oW', 'name'=>'BPM and Lean: Taking the Waste Out of Work', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nk', 'name'=>'Process Management and Metrics ', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'',
		'subtitle'=>'<b>Conference Packages</b>',
		'thankyoutitle'=>'July 1',
		'background'=>'#eeeeee',
		'type'=>'conference',
		'courses' => array(
			array('uid'=>'DAY2', 'name'=>'1-Day Conference Package - July 1 Only', 'type'=> 'conference-1d'),
		),
	),

	array(
		'title'=>'<b>July 2</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'July 2',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1mA', 'name'=>'Business Rules Driven Requirements', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mP', 'name'=>'Business Architecture/ IT Architecture Alignment', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nD', 'name'=>'Designing Service Oriented Solutions', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nX', 'name'=>'Developing Business Process Models in the Real-World', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nZ', 'name'=>'Establishing Business Process Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1nr', 'name'=>'Establishing SOA Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
		),
	),


/*
//	array(
		'title'=>'',
		'thankyoutitle'=>'JJuly 2',
		'subtitle'=>'AM Workshops (9am-12noon)',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a0B50000000Zb8Z', 'name'=>'Business Process Management with a Business Decision Management and Business Rules Approach', 'type'=> 'training-per-1d'),
		),
	),

//	array(
		'title'=>'',
		'subtitle'=>'PM Workshops (2pm-5pm)',
		'thankyoutitle'=>'July 2',
		'type'=>'events',
		'courses' => array(
			array('uid'=>'a005000000Ai9f2', 'name'=>'Building a BPM Center of Excellence', 'type'=> 'workshops'),
			array('uid'=>'a005000000Ai9f7', 'name'=>'Building an SOA Center of Excellence', 'type'=> 'workshops'),
		),
	),
*/
);

// put same day courses that conflict with 1st day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY1', "'a0B50000001W1ki', 'a0B50000001W1mC', 'a0B50000001W1np', 'a0B50000001W1of'" );


// put same day courses that conflict with 2nd day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY2', "'a0B50000001W1lq', 'a0B50000001W1mj', 'a0B50000001W1mH', 'a0B50000001W1oB', 'a0B50000001W1oL', 'a0B50000001W1oW', 'a0B50000001W1nk'" );

// put the 2-day course and then the conflicting second-day 1-day courses
define( 'BSG_REG_COURSE_CONFLICT_GENERAL', "'a0B50000001W1oB', 'a0B50000001W1mA', 'a0B50000001W1mP', 'a0B50000001W1nD', 'a0B50000001W1nX', 'a0B50000001W1nZ', 'a0B50000001W1nr'" );

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