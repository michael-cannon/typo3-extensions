<?php


include_once( '../localconf.php' );

/**
Show forgot password form.  Allows user to enter an email or username and get
the password sent by email.  Works in conjunction with forgotpassword.js, which
contains JavaScript functions for opening this script in a small centered window.

<h3>How to use:</h3>
<pre>
$forgotPassword = new ForgotPassword(); 
$forgotPassword->contactInfoHTML = "Nick at <a href='mailto:nick@ndezine2.com'>nick@ndezine2.com</a>";
$forgotPassword->contactInfoText = "Nick at nick@ndezine2.com";
echo $forgotPassword->main();
</pre>

@author Jaspreet Singh
@id $Id: forgotpassword.class.php,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $
*/
class ForgotPassword {
    
    /** name of the site*/
	var $site;
	/** URL to the site. If you want to customize it, set it before main and it won't be reset. */
	var $siteURL;  
    /** page where backend logins happen */
	var $loginPage = 'typo3/';
    /** full URL to backend login page.  If you want to customize it, set it before main and it won't be reset. */
	var $loginURL;  
	/** the contact information to give if we show an error message.  HTML and Text versions. */
	var $contactInfoHTML = "Nick at <a href='mailto:nick@ndezine.com'>nick@ndezine.com</a>";
	var $contactInfoText = "Nick at nick@ndezine.com";
	
	/** location of the HTML template for initial display (enter Email) 
	It's best if this path is an absolute location on the server filesystem and 
	the paths referred to inside the template are also absolute from the webserver root.
	*/
	var $templatePathEnterEmail = "/home/dpm/www/fileadmin/directorypointmarketing.net/forgotpassword/enterEmail.tmpl.html"; 
	/** location of the HTML template for email not found */
	var $templatePathNotFound = "/home/dpm/www/fileadmin/directorypointmarketing.net/forgotpassword/notFound.tmpl.html";
	/** location of the HTML template for success (i.e., password found and emailed)*/
	var $templatePathSuccess = "/home/dpm/www/fileadmin/directorypointmarketing.net/forgotpassword/success.tmpl.html";
	/** marker in a template which is to be replaced */
	var $templateMarkerEmail = '/\#\#\#EMAIL\#\#\#/';
	/** marker in a template which is to be replaced.  This is meant for the 
	name attribute of the email/username field*/
	var $templateMarkerFieldNameEmailOrUserName = '/\#\#\#EMAIL_OR_USERNAME_FIELDNAME\#\#\#/';
	/** the value for $templateMarkerEmail */ 
	var $fieldNameEmailOrUserName = 'emailOrUserName';
	
	/** turns on/off debugging output */
	var $debug = true;
	/** database link.  Set from initialize() */
	var $db;
	    
    /**
	Starting point.
	Allows user to enter an email or username.  If there is a match in the users table,
	the user's password is reset to a random string and
	the login info is sent to the stored email.  If not, an error page is displayed.
	Returns an HTML string containing the output.
	@return HTML string
	*/
	function main()
    {
        $this->site = $_SERVER['SERVER_NAME'];
		if ($this->debug) {
			echo "$ this->siteURL $this->siteURL ";
			echo "$ this->loginURL $this->loginURL ";
		}
        if (!isset($this->siteURL)) {
			$this->siteURL = "http://$this->site/";
		}
		if (!isset($this->loginURL)) {
			$this->loginURL = $this->siteURL . $this->loginPage;
		}
		if ($this->debug) {
			echo "$ this->siteURL $this->siteURL ";
			echo "$ this->loginURL $this->loginURL ";
		}
		
		$this->initialize();
        
        $output = '';
        if (isset($_POST[$this->fieldNameEmailOrUserName])) { //user submitted form, take action
            if ($this->debug) { echo "submitted"; }
			
			$emailOrUserName = $_POST[$this->fieldNameEmailOrUserName]; 
			
			//try to get the login info for this user
			list ($uid, $userName, $email, $password) = $this->getLoginInfo($emailOrUserName);

			//if there is a match (if the username or email entered by the user exists)
			//send the login info to user
			if (''!=$userName) {

				//reset the password, since the old password is an MD5 hash
				$newPassword = $this->setNewRandomPassword($uid);
				$this->sendLoginInfo($userName, $email, $newPassword);
				
				//and show the user that we did so.
				$output .= $this->getPasswordSentHTML($email);
			} else { //otherwise send notfound message
				
				$output .= $this->getNotFoundMessageHTML();
			}
			
        } else { //show initial form
            if ($this->debug) "showForm";
            $output .= $this->getForgotPasswordFormHTML();
        }
        
        $this->finalize();
		
		return $output;
    }
    
    
	
	/**
	Get the login info matching the passed email or username.
	If there is no match, $userName will be equal to ''.
	@param string The email or username of the user.
	@return array in the form list ($uid, $userName, $email, $password) =
	getLoginInfo($emailOrUserName); 
	*/
	function getLoginInfo($emailOrUserName)
	{
		//Grab the matching rows from the user table
		$rows = $this->getRowsEmailOrUsername($emailOrUserName);
		$uid = null;
		$userName = '';
		$email = '';
		$password = '';
		
		//Iterate over the rows to find the first row where there is a match with 
		//the email or username 
		foreach ($rows as $row) {
			if ($emailOrUserName == $row['email'] || $emailOrUserName == $row['username']) {
				$uid		= $row['uid'];
				$userName 	= $row['username'];
				$email 		= $row['email'];
				$password 	= $row['password'];
				break;
			}
		}
		//and return the information from that row
		return array($uid, $userName, $email, $password);
	}
	
	/**
	Send login info to a user by email.
	@param string The user's login name.
	@param string The email address to which the message should be sent.
	@param string The password to give to the user.
	*/
	function sendLoginInfo($userName, $email, $password)
	{
		$subject = 'Login information for ' . $this->siteURL;
		$message =
			"Dear Member:\r\n"
			."\r\nHere is the login information you requested to have"
			." emailed to you."
			." The original password could not be recovered due to security reasons;"
			." however, a new random password has been assigned to you:\r\n"
			."\r\n\r\nLogin site: $this->loginURL"
			."\r\nUsername: $userName"
			."\r\nPassword: $password"
			."\r\n\r\nOnce you log in, you can change your password if you like"
			." in the 'User: Setup' module's 'Personal Data' section."
			."\r\n\r\nIf you have any other questions or problems,"
			." please contact $this->contactInfoText for further assistance."
			;
		mail($email, $subject, $message, "From: {$_SERVER['SERVER_ADMIN']}\r\n" );
		if ($this->debug) echo $message;
	
	}
	
	/**
	Returns a string containing HTML for a "not found" message.
	This is meant to be sent when a user's email or username is not found in the
	database.
	@return the HTML string
	*/
	function getNotFoundMessageHTML()
	{
		$html = $this->getTemplate($this->templatePathNotFound);
		
		if (''==$html) {
			$templatePathEnterEmail = $this->getNotFoundMessageHTMLBackup();
		} else {
			
		}
		
        return $html;
	}

	/**
	Returns a string containing HTML for a "not found" message.
	This is meant to be sent when a user's email or username is not found in the
	database.
	@return the HTML string
	*/
	function getNotFoundMessageHTMLBackup()
	{
        $html = "
<html>
<head>
<title>Password Helper</title>
</head>
<body>
<div style='font-size:70%'>
<p>Sorry, no match was found for your login information.
</p>
<p>Please contact $this->contactInfoHTML for further assistance.
</p>
<p><a onclick='window.history.back()'href=#>Try again</a></p>
<p><a onclick='window.close()'href=#>Close this window</a></p>
</div>
</body>
</html>        
        ";
        return $html;
	}
	
	/**
	Returns a string containing HTML for a "password sent" message.
	This is meant to be called when a user's login info has been found and his
	login has been sent to him by email.
	@param string The email to which the login info was sent 
	@return the HTML string
	*/
	function getPasswordSentHTML($email)
    {
		$html = $this->getTemplate($this->templatePathSuccess);
		
		if (''==$html) {
			$html = $this->getPasswordSentHTMLBackup($email);
		} else {
			$html = $this->prepareTemplate($html, array($this->templateMarkerEmail), array($email) );
		}
		
        return $html;
    }

	/**
	Returns a string containing HTML for a "password sent" message.
	This is meant to be called when a user's login info has been found and his
	login has been sent to him by email.
	@param string The email to which the login info was sent 
	@return the HTML string
	*/
	function getPasswordSentHTMLBackup($email)
    {
        $html = "
<html>
<head>
<title>Password Helper</title>
</head>
<body>
<div style='font-size:70%'>
<p>Your password was sent to $email.
</p>
<p><a onclick='window.close()'href=#>Close this window</a></p>
</div>
</body>
</html>        
        ";
        return $html;
    }

	/**
	Returns a string containing HTML for a "forgot password" form.
	This is meant to be initially shown to the user to enable him to enter either
	his username or email.
	@return the HTML string
	*/
    function getForgotPasswordFormHTMLBackup()
    {
        $form = "
<html>
<head>
<title>Password Helper</title>
</head>
<body>
<div style='font-size:70%'>
<p>If you forgot your password, enter your email address or username here to have your password mailed to you:
</p>
<form method='POST'>
<input type='text' name='$this->fieldNameEmailOrUserName'>
<input type='submit' value='Submit' name='submitEmailOrUserName'>
</form>
</div>
</body>
</html>        
        ";
        
		return $form;
    }

	/**
	Returns a string containing HTML for a "forgot password" form.
	This is meant to be initially shown to the user to enable him to enter either
	his username or email.
	This function intially attempts to get a designated template and prepares it
	by replacing markers with text.  Otherwise, it returns a default backup HTML.
	@return the HTML string
	*/
    function getForgotPasswordFormHTML()
    {
		if ($this->debug) {echo "getForgotPasswordFormHTML()"; }
		$html = $this->getTemplate($this->templatePathEnterEmail);
		
		if (''==$html) {
			$html = $this->getForgotPasswordFormHTMLBackup();
		} else {
			
			$html = $this->prepareTemplate(
				$html
				, array($this->templateMarkerFieldNameEmailOrUserName)
				, array($this->fieldNameEmailOrUserName) 
			);
		}
		
		return $html;
    }
	
    

    /**
	Open/cache db connections or other initializations.
	@return void
	*/
    function initialize()
	{
        global $typo_db_host, $typo_db_username, $typo_db_password, $typo_db; 
        
        // create db conection
        $this->db                                = mysql_connect( $typo_db_host
                                            , $typo_db_username
                                            , $typo_db_password
                                        )
                                        or die( 'Could not connect to database' );
        // select database
        mysql_select_db( $typo_db )
            or die( 'Could not select database' );
		
	}
	
    /**
	Close db connections or other finalizations.
	@return void
	*/
	function finalize()
	{
		mysql_close( $this->db );
	}
    
    /**
    * Returns rows with uid, username, email and password from table be_users.
	* A specific row can be searched for by email or username; otherwise all rows
	* are returned.
	* This is user information from the backend users table.
    * Fields: uid, username, email, password
	* Assumes initialize() has been called.
	* @param string the email or the username.  Default is blank, which returns
	*		all rows.
    * @return rows as an array of arrays
    */
    function getRowsEmailOrUsername($emailOrUsername='')
    {
        global $typo_db_host, $typo_db_username, $typo_db_password, $typo_db; 
        
        $sql                            = "
			SELECT uid, username, password, email
			FROM be_users 
            ";
		if ($emailOrUsername != '') {
			$sql .= " WHERE username='$emailOrUsername' OR email='$emailOrUsername' ";
		}
        
        // get query result
        $result                            = mysql_query( $sql )
                                            or die( 'Query failed: ' . mysql_error() );
        
        $rows                        = array();
        
        //append the data rows to $rows
        if ( $result && $data = mysql_fetch_assoc( $result ) ) {
            do { 
                //var_dump( $data );
                $rows[] = $data;
            } while ( $data = mysql_fetch_assoc( $result ) );
            
            // free up our result set
            mysql_free_result( $result );
        }
        
        return $rows;
    }

	/**
	Set password of user associated with the passed uid to a new, random string.
	Note that the password saved in the database will be an MD5 hash of the generated
	password.
	@param int uid of user
	@return string The new password in clear text.  If failure, returns null. 
	*/
	function setNewRandomPassword($uid)
	{
		$passwordClearText = $this->getRandomPassword();
		$md5Password = MD5($passwordClearText);
		$success = $this->updatePassword($uid, $md5Password);
		
		if (true==$success) {
			$returnValue = $passwordClearText; 
		} else {
			$returnValue = null;
		}
		
		if ($this->debug) echo ' ' . $passwordClearText . ' ';
		return $returnValue;
	}

	/**
	Set password of user associated with the passed uid to the passed string.
	This updates table be_users.
	@param int uid of user
	@param string password.  This should be in the exact hashed format as should
		be saved in the database.
	@return true if success; false if failure 
	*/
	function updatePassword($uid, $password)
	{
		$sql                            = "
			UPDATE be_users 
			SET password = '$password' 
			WHERE uid = $uid
			";
        // get query result
        $result                            = mysql_query( $sql )
                                            or die( 'Query failed: ' . mysql_error() );
		if ($this->debug) echo $sql;
		//if we got here, it's success:
		return true;

	}
	
	

	/**
	Generates a random password.  Call without parameters for reasonable defaults
	or set the password length and the character set from which the password is
	generated.
	@param int The desired length of the password.  8 by default.
	@param string The characters from which the password is generated.  Default is the
		lowercase English letters.  To make the password 
		more difficult, one could add numbers or symbols
	@return the generated password
	*/
	function getRandomPassword($passwordLength = 8, $allowableChars = 'abcdefghijklmnopqrstuvwxyz') {
	
		//the characters from which the password is generated.  To make the password
		//more difficult, one could add numbers or symbols
		$allowableCharsLength = strlen($allowableChars);
		$allowableCharsMaxIndex = $allowableCharsLength-1; 
		$password = '';
	
		for ($i=0; $i<$passwordLength; $i++) {
			$position = rand(0, $allowableCharsMaxIndex);
			$password .= substr($allowableChars, $position, 1); //get a single random char
		}
	
		return $password;
	}

	/**
    * Returns an HTML temlate   
    * @param name/path of the template
    * @return the template
    */
    function getTemplate($templateName)
    {
		$fileName = /* $this->getScriptDirectory() . '/' .
			$templateName . '.' . $this->templateFileExtension;*/ $templateName;
		$templateData ='';
		
		if ($this->debug) {
			
			if ($this->debug) {
				if ( file_exists($fileName) ) {
					echo "\n<br>Template file exists: " . $fileName;
				} else {
					echo "\n<br>Template file does not exist: " . $fileName;	
				}
				//echo ' <br> scriptdir' . $this->getScriptDirectory();
				echo ' <br> self:' . $_SERVER['PHP_SELF'];
			}
		}
		//Read info fr the file if it exists and filesize isn't 0
		if ( file_exists($fileName) && ($fileSize = filesize($fileName)) ) {
			
			
			$fileData = file_get_contents($fileName);
			//if ($this->debug) echo $fileData;
			$templateData = $fileData;
		} else {  // otherwise get template from the fallback
			//$templateData = $this->getTemplateBackup( $templateName );
		}
		
		
		return $templateData;
	}

    /**
    * Prepares an HTML temlate by replacing patterns with replacement text.  
    * @param name of the template (without extension)
    * @param an array of patterns
    * @param an array of replacements
    * @return the template
    */
    function prepareTemplate($template, $replacementPatterns, $replacementText)
    {
        return preg_replace($replacementPatterns, $replacementText, $template);
    }
    
}




?>
