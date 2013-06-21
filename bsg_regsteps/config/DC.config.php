<?php

define( 'BSG_REG_DEFAULT_PC', 'DC9PUBW' );
define( 'BSG_REG_MEM_PC', 'DC9MEMW' );
define( 'BSG_REG_PRO_PC', 'DC9PROW' );
define( 'BSG_REG_CORP_PC', 'DC9CORPW' );
define( 'BSG_REG_CONF_CITY', 'DC' );
define( 'BSG_REG_CONF_USERGROUP', '11' );

$regHeader						= <<<EOD
<h1>BrainStorm DC Registration</h1>
<p>
BrainStorm DC will take place September 21-24.
</p>
<p>
Please use the following form to complete your registration.
</p>
EOD;

$confSeries	= array(
	'No Conference Selection' => '',
//	'BPM Conference' => 'a005000000DCi19',
//	'SOA Conference' => 'a005000000DCi1J',
//	'BA Conference' => 'a005000000DCi0M',
//	'BDM/Rules Symposium' => 'a005000000DZU9P',
);

$confSeriesTrainingOnly	= 'a005000000DCi1T';

$courses = array (
	array(
		'title'=>'<b>September 21</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'September 21',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1kt', 'name'=>'BPM 101', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1nc', 'name'=>'Developing Process-Centric, Business Requirements (2-Day Course) ', 'type'=> 'training-per-2d'),
			array('uid'=>'a0B50000001W1lC', 'name'=>'BR & BDM 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1oJ', 'name'=>'SOA 101', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1kk', 'name'=>'BA 101', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1oV', 'name'=>'Simple, Open & Standardized BPM Metrics', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001Xoux', 'name'=>'Methodologies and Approaches for BPM', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'<b>September 22</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'September 22',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1o1', 'name'=>'Facilitation Skills for Process Improvement Projects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1kj', 'name'=>'Analyzing the "As Is" and Creating the "To Be"', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1ob', 'name'=>'SOA for Architects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mZ', 'name'=>'Business Architecture Integration', 'type'=> 'training-per-1d'),
		),
	),	

        array(
		'title'=>'<b>September 22</b>',
		'subtitle'=>'<b>Workshops</b>',
		'thankyoutitle'=>'September 22',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a005000000FLvMV', 'name'=>'Sept 22 Full-day Keynotes and Workshops', 'type'=> 'workshops-1d'),
			array('uid'=>'a005000000FKtY6', 'name'=>'Sept 22 AM Only: Opening Keynote & Lombardi Workshop', 'type'=> 'workshops'),
			array('uid'=>'a005000000FKtbU', 'name'=>'Sept 22 PM Only: Luncheon Keynote & Pallas Athena Workshop', 'type'=> 'workshops'),
		),
	),

	array(
		'title'=>'<b>September 23</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'September 23',
		'background'=>'#eeeeee',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1mo', 'name'=>'Business Transformation Methodologies', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1op', 'name'=>'BPM and Lean: Taking the Waste Out of Work', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mM', 'name'=>'Process Modeling With BPMN (2-Day Course)', 'type'=> 'training-per-2d'),
			array('uid'=>'a0B50000001W1oH', 'name'=>'Service Oriented Integration', 'type'=> 'training-per-1d'),
//			array('uid'=>'a0B50000001W1lv', 'name'=>'BPM and Six Sigma', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mI', 'name'=>'Data Architecture for Business Architects', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nq', 'name'=>'Process Measurement and Metrics ', 'type'=> 'training-per-1d'),
		),
	),

	array(
		'title'=>'<b>September 23</b>',
		'subtitle'=>'<b>Workshops</b>',
		'thankyoutitle'=>'September 23',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a005000000FLvMB', 'name'=>'Sept 23 Full-Day Keynotes and Workshops', 'type'=> 'workshops-1d'),
			array('uid'=>'a005000000FKtbe', 'name'=>'Sept 23 AM Only: Opening Keynote & Appian Workshop', 'type'=> 'workshops'),
			array('uid'=>'a005000000FKtbj', 'name'=>'Sept 23 PM Only: Luncheon Keynote & Savvion Workshop', 'type'=> 'workshops'),
		),
	),

	array(
		'title'=>'<b>September 24</b>',
		'subtitle'=>'<b>Training</b>',
		'thankyoutitle'=>'September 24',
		'type'=>'training',
		'courses' => array(
			array('uid'=>'a0B50000001W1nY', 'name'=>'Developing Business Process Models in the Real-World', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nN', 'name'=>'Designing Service Oriented Solutions', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mQ', 'name'=>'Business Architecture / IT Architecture Alignment', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001Xwxy', 'name'=>'Organizational Change Management', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1mF', 'name'=>'Business Rules Driven Requirements', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nQ', 'name'=>'Establishing Business Process Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
			array('uid'=>'a0B50000001W1nR', 'name'=>'Establishing SOA Governance and Centers of Excellence', 'type'=> 'training-per-1d'),
		),
	),
);

// put same day courses that conflict with 1st day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY1', "'a0B50000001W1nc', 'a0B50000001W1o1', 'a0B50000001W1kj', 'a0B50000001W1ob', 'a0B50000001W1mZ'" );

// put same day courses that conflict with 2nd day of conference
define( 'BSG_REG_COURSE_CONFLICT_CONF_DAY2', "'a0B50000001W1mo', 'a0B50000001W1op', 'a0B50000001W1mM', 'a0B50000001W1oH', 'a0B50000001W1lv', 'a0B50000001W1mI', 'a0B50000001W1nq'" );

// put the 2-day course and then the conflicting second-day 1-day courses
define( 'BSG_REG_COURSE_CONFLICT_GENERAL', "'a0B50000001W1mM', 'a0B50000001W1nY', 'a0B50000001W1nN', 'a0B50000001W1mQ', 'a0B50000001W1mF', 'a0B50000001W1nQ', 'a0B50000001W1nR'" );

$checkCourseConfig = <<<EOD
function xcheckCourseConfig( course ) {
	return;
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