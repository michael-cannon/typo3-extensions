/*
Support functions for forgotpassword module.  Include this into the login page
with
<script type="text/javascript" language="javascript1.5" src="forgotPassword.js"></script>
Adjust the src path as necessary.

Include a line like this:
<p>If you forgot your password, click <a onclick="openForgotPasswordWindow()" href='#'>here</a>

or

<p><a onclick="openForgotPasswordWindow()" href='#'>Forgot your password?</a>

@author Jaspreet Singh
@id $Id: forgotPassword.js,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
*/

/**
Opens a Forgot Password window and prefills the username into the username/email
box.
Set the URL variable below to where the Forgot Password form resides.
The FIELD_EMAIL_OR_USERNAME_FORGOT and FIELD_USERNAME_LOGIN should be set if the 
form element names are different than below.
@return void
*/
function openForgotPasswordWindow() {

    var URL = '/typo3conf/scripts/forgotpassword.php'; //the password reminder form/script
    var FIELD_EMAIL_OR_USERNAME_FORGOT = 'emailOrUserName'; //field to fill in the forgot window
    var usernameText = document.loginform.username.value;
    //alert(usernameText);

    var newWindow = openCenteredWindow(URL);
    //get the username that the user filled into this form and fill it into
    //the form we are displaying
    newWindow.document.getElementById(FIELD_EMAIL_OR_USERNAME_FORGOT).value = usernameText;
    
}

/**
Opens a little resizable window centered on the screen.
@return the new window reference
*/
function openCenteredWindow(url) {
    
    var width = 400;
    var height = 300;
    var left = parseInt(screen.availWidth/2) - width/2;
    var top = parseInt(screen.availHeight/2) - height/2;
    var windowFeatures = "width=" + width + ",height=" + height 
        + ",status,resizable,left=" + left + ",top=" + top
        + ",screenX=" + left + ",screenY=" + top;
    var newWindow = window.open(url, "subWind", windowFeatures);
    return newWindow;
}


