<?php


include_once( '../localconf.php' );
include_once( 'forgotpassword.class.php' );

// $Id: forgotpassword.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $

//Show the forgot password form
$forgotPassword = new ForgotPassword(); 
$forgotPassword->contactInfoHTML = "Support at <a href='mailto:info@directorypointmarketing.com'>info@directorypointmarketing.com</a>";
$forgotPassword->contactInfoText = "Support at info@directorypointmarketing.com";
//The following 3 lines are to put an https in the URL; otherwise everything would be set
//automatically.
$forgotPassword->site = $_SERVER['SERVER_NAME'];
$forgotPassword->siteURL = "https://" . $forgotPassword->site ."/";
$forgotPassword->loginURL = $forgotPassword->siteURL . $forgotPassword->loginPage;
//locations of the template files

/* location of the HTML template for the initial screen */
$forgotPassword->$templatePathEnterEmail = "/home/dpm/www/fileadmin/directorypointmarketing.net/forgotpassword/enterEmail.tmpl.html"; 
/* location of the HTML template for email not found */
$forgotPassword->$templatePathNotFound = "/home/dpm/www/fileadmin/directorypointmarketing.net/forgotpassword/notFound.tmpl.html";
/* location of the HTML template for success (i.e., password found and emailed)*/
$forgotPassword->$templatePathSuccess = "/home/dpm/www/fileadmin/directorypointmarketing.net/forgotpassword/success.tmpl.html";


if (false) {
	$forgotPassword->initialize();
	$rows = $forgotPassword->getRowsEmailOrUsername();
	print_r ($rows);
	//echo $forgotPassword->getForgotPasswordFormHTML();
	//phpinfo();
}
if ($forgotPassword->debug) {
	echo "wrapper: this->siteURL $forgotPassword->siteURL ";
	echo "wrapper: this->loginURL $forgotPassword->loginURL ";

}
echo $forgotPassword->main();


?>
