<?php
/* @version $Id: sugarError.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version
 * 1.1.3 ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied.  See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * The Original Code is: SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/


/** ensure this file is being included by a parent file */
defined( '_VALID_SUGAR' ) or die( 'Direct Access to this location is not allowed.' );

define('_ALL_ERRORS', 0);
define('_NO_ERROR', 0);
define('_SOAP_ERROR', 1);
define('_SUGAR_ERROR', 2);
define('_SHOWERRORS', 1);
define('_SHOWDEBUG', 2);
define('_SHOWSOAP', 3);

// sugarError is the class that holds error information for the communication layer
// Pass it an instance of a sugarClientProxy and the error part of the results you
//   got from your method call to the sugarClientProxy.  sugarError will process that
//   into a useful error message, hopefully.
class sugarError {
	var $clientDebugText = '';
	var $sugarErrorNumber = 0;
	
    function sugarError($client_result=false, $sugar_error, $client) { //$sugar=false) {
        // Set these to false, default values indicating no errors
        $this->clientError = false;
        $this->sugarError = false;
        
        // If there's an error in the nusoap connection, we catch it here
       if($client_result) {
            if(is_soap_fault($client_result)) {
                $this->clientError = true;
                $this->clientErrorText = $client_result->faultstring;
                $this->clientDebugText = $client_result->faultcode . $client_result->faultstring 
                	. "Request :<br>". htmlentities($client->__getLastRequest()). "<br>"
                	. "Response :<br>".htmlentities($client->__getLastResponse()). "<br>";
            }
         
    /**
            $genericError = $client->getError();
            if($genericError) {
                $this->clientError = true;
                $this->clientErrorText = $genericError;
                $this->clientDebugText = $client->debug_str . $client->responseData . $client->response;
            }
     */
        }

        // If php_soap is working fine, there may be an error server-side within Sugar
        // Catch it here
        // For some reason not all errors are caught here.
        if($sugar_error) {
            if($sugar_error->number != '0') {
                $this->sugarError = true;
                $this->sugarErrorNumber = $sugar_error->number;
                $this->sugarErrorName = $sugar_error->name;
                $this->sugarErrorText = $sugar_error->description;
            }
        }
    }
    
    function hasError() {
        if($this->getErrorType() != _NO_ERROR) return true;
        
        return false;
    }
    
    function getErrorText($errorType = _ALL_ERRORS) {
        switch($errorType) {
            case _SOAP_ERROR:
                return $this->_getSoapError();
                break;
            case _SUGAR_ERROR:
                return $this->_getSugarError();
                break;
            case _ALL_ERRORS:
            default:
                return $this->_getSoapError() . $this->_getSugarError();
                break;
        }
    }
    
    function getDebugText($errorType = _ALL_ERRORS) {
        switch($errorType) {
            case _SOAP_ERROR:
                return $this->_getSoapDebug();
                break;
            case _SUGAR_ERROR:
                return $this->_getSugarDebug();
                break;
            case _ALL_ERRORS:
            default:
                return $this->_getSoapDebug() . $this->_getSugarDebug();
                break;
        }
    }
    
    function getErrorType() {
        if($this->clientError) return _SOAP_ERROR;
        if($this->sugarError) return _SUGAR_ERROR;
        
        return _NO_ERROR;
    }
    
    function _getSoapDebug() {
        $debugText = '<div style="text-align: left;">
            <pre>' . $this->clientDebugText . '</pre></div>';
        
        return $debugText;
    }
    
    function _getSoapError() {
        if($this->clientError) {
            $errorText = '<div style="text-align: left;">
                        <p><b>Connection Error</b></p>';
            $errorText .= '<p style="font-size: smaller;"><b>' . $this->clientErrorText . '</b></p>';
            
            $errorText .= '</div>';
            
            return $errorText;
        } else {
            return '';
        }
    }

    function _getSugarDebug() {
        return '';
    }
        
    function _getSugarError() {
        if($this->sugarError) {
            $errorText = '<div style="text-align: left;">
                <p><b>Sugar Error</b></p>';
            $errorText .= '<dl><dt>' . $this->sugarErrorName . '</dt>';
            $errorText .= '<dd>' . $this->sugarErrorText . '</dd></dl></div>';
            
            return $errorText;
        } else {
            return '';
        }
    }
}

?>