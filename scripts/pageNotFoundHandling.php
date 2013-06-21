<?php
define(LOGIN_URL, "http://www.2elearning.com/subscribe/login-successful/login.html");
define(NOTFOUND_URL, "http://www.2elearning.com/");
 
class user_pageNotFound {
  function pageNotFound($param, $ref) {
    if ($param["pageAccessFailureReasons"]["fe_group"] != array(""=>0)) {
      header("HTTP/1.0 403 Forbidden");
      $url = LOGIN_URL."?redirect_url=" . $param["currentUrl"];
    } else {
      $url = NOTFOUND_URL;    
    }
    
    session_start();
    $strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
    session_write_close(); 
  
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_COOKIE, $strCookie);
    $contents = curl_exec($c);
    curl_close($c);

    if ($contents) return $contents;
        else return FALSE;
  }
} 
?>