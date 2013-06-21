<?php

/*

salesforce.com Partner PHP client 

Copyright (c) 2005 Ryan Choi

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

If you have any questions or comments, please email:

Ryan Choi
rchoi21@hotmail.com
http://www.ryankicks.com

*/
    
/**
 * salesforce client for use with a modified nuSOAP PHP library.
 */

require_once('nusoap.php');

/**
 * 
 * salesforce
 * 
 * @author Ryan Choi <rchoi21@hotmail.com>
 * @version
 * @access public
 */
class salesforce {

    var $client; 
    var $result;

    var $url;
    var $session;

    /**
     * constructor for salesforce client.
     * 
     * @param string
     *            $wsdl local reference to partner WSDL file.
     * @access public
     */
    function salesforce($wsdl) {

        $this->client = new nusoapclient($wsdl, true);

    }

    /**
     * login with username and password. will set SessionId header upon
     * successful login.
     * 
     * @param string
     *            $username username for login.
     * @param string
     *            $password password for login.
     * @return mixed LoginResult complex type. (See WSDL.)
     * @access public
     */
    function login($username, $password){

        // Doc/lit parameters get wrapped
        $param = array('username' => $username, 'password' => $password);
        $this->result = $this->client->call('login', array('parameters' => $param), '', '', false, true);

        if ($this->client->getError() || $this->client->fault) {

            return false;

        } else {

            $wrapper = $this->result['result'];
            $url = $wrapper['serverUrl'];
            $session = $wrapper['sessionId'];

            $this->client->forceEndpoint = str_replace("https", "http", $url);

            $element = new soapval('sessionId', null, $session);
            $element = array($element);
            $this->setHeader('SessionHeader', $element);

            return $this->result['result'];

        }
    }
    
    /**
     * set header on client.
     * 
     * @param string
     *            $headerName name of header
     * @param array
     *            $headerValue array of soapvals of values
     * @access public
     */
    function setHeader($headerName, $headerValue){

        $header = new soapval($headerName, null, $headerValue);
        $headers = null;

        if ($this->client->requestHeaders == null){
            $headers = array($header);
        } else {
            $headers = $this->client->requestHeaders;
            $count = 0;
            foreach ($headers as $hdr) {
                $existingHdrName = $hdr->name;
                if ($existingHdrName == $headerName){
                    break;
                }
                $count++;
            }
            array_splice($headers, $count, 1, array($header));
        }
        
        $this->client->setHeaders($headers);

    }

    /**
     * create sObjects.
     * 
     * @param mixed
     *            $values either a single sObject or an array of sObjects
     * @return mixed either a single SaveResult complex type or an array of
     *         SaveResult complex types. (See WSDL.)
     * @access public
     */
    function create($sObjects){
        $param = array('sObjects' => $sObjects);
        $this->result = $this->client->call('create', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }

    /**
     * update sObjects.
     * 
     * @param mixed
     *            $values either a single sObject or an array of sObjects
     * @return mixed either a single SaveResult complex type or an array of
     *         SaveResult complex types. (See WSDL.)
     * @access public
     */
    function update($sObjects){
        $param = array('sObjects' => $sObjects);
        $this->result = $this->client->call('update', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    } 

    /**
     * delete sObjects.
     * 
     * @param mixed
     *            $values either a single id (string) or an array of ids (array
     *            of strings)
     * @return mixed either a single DeleteResult complex type or an array of
     *         DeleteResult complex types. (See WSDL.)
     * @access public
     */
    function delete($ids){
        $param = array('ids' => $ids);
        $this->result = $this->client->call('delete', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }

    /**
     * perform query using SOQL command.
     * 
     * @param string
     *            $queryString SOQL query request
     * @return mixed QueryResult complex type. (See WSDL.)
     * @access public
     */
    function query($queryString){
        $param = array('queryString' => $queryString);
        $this->result = $this->client->call('query', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
    /**
     * executes retrieval of more query records based on query locator.
     * 
     * @param string
     *            $queryLocator string representing query locator
     * @return mixed QueryResult complex type. (See WSDL.)
     * @access public
     */
    function queryMore($queryLocator){
        $param = array('queryLocator' => $queryLocator);
        $this->result = $this->client->call('queryMore', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
    /**
     * returns current time of salesforce server
     * 
     * @return timestamp time at sforce server.
     * @access public
     */
    function getServerTimestamp(){
        $param = array('' => '');
        $this->result = $this->client->call('getServerTimestamp', array('parameters' => $param), '', '', false, true);
        return $this->result['result']['timestamp'];
    }
    
    /**
     * returns global description of API settings
     * 
     * @return mixed DescribeGlobalResult complex type. (See WSDL.)
     * @access public
     */
    function describeGlobal(){
        $param = array('' => '');
        $this->result = $this->client->call('describeGlobal', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
    /**
     * returns description for given sObject
     * 
     * @param string
     *            $sObjectType string sObject name
     * @return mixed DescribeSObjectResult complex type. (See WSDL.)
     * @access public
     */
    function describeSObject($sObjectType){
        $param = array('sObjectType' => $sObjectType);
        $this->result = $this->client->call('describeSObject', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
    /**
     * returns describe results for multiple sObjects.
     * 
     * @param array
     *            $sObjectTypes array of string sObject names
     * @return array array of DescribeSObjectResult complex types. (See WSDL.)
     * @access public
     */
    function describeSObjects($sObjectTypes){
        $param = array('sObjectType' => $sObjectTypes);
        $this->result = $this->client->call('describeSObjects', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
    /**
     * returns layouts for given sObject
     * 
     * @param string
     *            $sObjectType string sObject name
     * @return mixed DescribeLayoutResult complex type. (See WSDL.)
     * @access public
     */
    function describeLayout($sObjectType){
        $param = array('sObjectType' => $sObjectType);
        $this->result = $this->client->call('describeLayout', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
    /**
     * searches for specified entities given SOSL
     * 
     * @param string
     *            $searchString SOSL search request
     * @return mixed SearchResult complex type. (See WSDL.)
     * @access public
     */
    function search($searchString){
        $param = array('searchString' => $searchString);
        $this->result = $this->client->call('search', array('parameters' => $param), '', '', false, true);
        return $this->result['result'];
    }
    
}

/**
 * salesforce sObject.
 * 
 * @access public
 */
class sObject {
    
    var $type;
    var $id;
    var $values;
    var $fieldsToNull;
    
    function sObject($type, $id=null, $values=null, $fieldsToNull=null) {
        
        // deserialize record from nusoap.php
        if (is_array($type)){
        
            $this->values = array();
            
            foreach ($type as $k => $v){
                if ($k == 'type'){
                    $this->type = $v;
                } else if ($k == 'Id'){
                    if (is_array($v)){
                        $this->id = $v[0];
                    } else {
                        $this->id = $v;
                    }
                } else {
                    $this->values[$k] = $v;
                }
            }
            
        } else {
            
            $this->type = $type;
            $this->id = $id;
            $this->values = $values;
            $this->fieldsToNull = $fieldsToNull;
            
        }
    }
    
    function serialize(){
        
        $valuesSer['type'] = $this->type;
        if ($this->fieldsToNull != null){
            $fieldsToNull = array();
            $index = 0;
            foreach($this->fieldsToNull as $value){
                $fieldsToNull[$index] = $value;
                $index++;
            }
            $valuesSer['fieldsToNull'] = new RepeatedElementsArray('fieldsToNull', $fieldsToNull);
        }
        $valuesSer['Id'] = $this->id;
        
        foreach ($this->values as $k => $v) {
            $valuesSer[$k] = $v;
        }
        
        $contact = new soapval('sObject', false, $valuesSer);

        return $contact->serialize();

    }
    
}

/**
 * helps SOAP-ENC arrays to be encoded as repeated elements.
 * 
 * @access private
 */
class RepeatedElementsArray {
    
    var $elementName;
    var $values;
    
    /**
     * @param string
     *            name of element
     * @param array
     *            values to be encoded. Currently, only strings are supported.
     * @access public
     */
    function RepeatedElementsArray($elementName, $values){
        $this->elementName = $elementName;
        $this->values = $values;    
    }
    
    function serialize($use='encoded'){
        $xml = "";
        foreach($this->values as $value){
            $xml .= "<$this->elementName>$value</$this->elementName>";
        }
        return $xml;
    }
    
}

?>
