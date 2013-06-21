<?php
/**
 * Language labels for module ""
 * 
 * This file is detected by the translation tool.
 $Id: locallang.php,v 1.1.1.1 2010/04/15 10:03:50 peimic.comprock Exp $
 */

$LOCAL_LANG = Array (
	"default" => Array (
		"title" => "Membership Access Tool"	
		, "description" => "This tool provides a way to upload a list 
            of users who are authorized to access specified resources.
            <p>Paste the tab-separated values into the text input area and press
            the button to upload.
            <p>After you upload an access list, the entire current list (with your changes
            applied) is displayed.  You may have to scroll down to see it."
		, 'form.upload.sectiontitle' => 'Membership Access List: Upload New/Changed List'
        , 'form.upload.helptext' => "To upload a new/changed list of access levels,
            paste the tab-separated values into the text input area and press the
            Upload button.
            <p>&nbsp;&nbsp;
            To do this, normally you need only select the access list
            area in Excel, choose Edit:Copy, and then paste here.
            <p>&nbsp;&nbsp;
            The columns should be in the order Name, Company, Email, Accesslevel
			, DesiredEndtime, EndtimeExtension.
            Don't include the column names when pasting.  The column Accesslevel
            should be in the format [number],[number] where number is the ID of a 
            group that the person should have access to.  I.e., '9,10' would mean
            Chicago and San Francisco while '10' would mean San Francisco. Don't 
            include the quotes when entering Accesslevel data.
            <p>&nbsp;&nbsp;
            The access levels you upload here are added to those that the user
            may already have, so you don't have to specify Professional or 
            Complimentary groups here.  If a user is already a member of any
            group(s), that user will remain a member of that group.
			<p>&nbsp;&nbsp;Example data:<br>
			<br>Name	Company	Email 	Accesslevel	DesiredEndtime	EndtimeExtension
			<br>John	Acme	blah@example.com 	12 	08/12/2006 	0
			<br>Jack	Acme	blah2@example.com 	11 	0 	30

			"
        , 'form.upload.endtimeextension' => "<p>Enter the number of days by which the endtime
            of the uploaded users should be extended.
            <p>&nbsp;&nbsp;"
        , 'form.upload.helptext2' => "<table><caption>Usergroup Codes/Names</caption>
			<thead><td>Code</td><td>Name</td></thead>
			<tbody>
			<tr><td>9</td><td>Chicago Conference</td>
			<tr><td>10</td><td>San Francisco Conference</td>
			<tr><td>11</td><td>Washington DC Conference</td>
			<tr><td>12</td><td>New York Conference</td>
			</tbody>
			</table>"
		, 'form.upload.options.display.buttontext' => 'Upload'
		, 'form.download.sectiontitle' => 'Membership Access List: Download Current List'
        , 'form.download.helptext' => 'To download the current access list as it exists
            on the database in the form of a spreadsheet file, press one of the 
            Download buttons:
            <p>Download for Reporting will return a file that shows group names.
            To modify the Access List, though, you need to use group codes; for that purpose,
            choose Download for ACL Modification.'
            
        , 'form.download.options.button.reporting.buttontext' => 'Download for Reporting'
        , 'form.download.options.button.aclmodification.buttontext' => 'Download for ACL Modification'
        , 'report.accesslist.title' => 'Report: Membership Access List'
        , "checklabel" => "Check box #1"
		, 
	),
	"dk" => Array (
	),
	"de" => Array (
	),
	"no" => Array (
	),
	"it" => Array (
	),
	"fr" => Array (
	),
	"es" => Array (
	),
	"nl" => Array (
	),
	"cz" => Array (
	),
	"pl" => Array (
	),
	"si" => Array (
	),
	"fi" => Array (
	),
	"tr" => Array (
	),
	"se" => Array (
	),
	"pt" => Array (
	),
	"ru" => Array (
	),
	"ro" => Array (
	),
	"ch" => Array (
	),
	"sk" => Array (
	),
	"lt" => Array (
	),
	"is" => Array (
	),
	"hr" => Array (
	),
	"hu" => Array (
	),
	"gl" => Array (
	),
	"th" => Array (
	),
	"gr" => Array (
	),
	"hk" => Array (
	),
	"eu" => Array (
	),
	"bg" => Array (
	),
	"br" => Array (
	),
	"et" => Array (
	),
	"ar" => Array (
	),
	"he" => Array (
	),
	"ua" => Array (
	),
	"lv" => Array (
	),
	"jp" => Array (
	),
	"vn" => Array (
	),
	"ca" => Array (
	),
	"ba" => Array (
	),
	"kr" => Array (
	),
);
?>
