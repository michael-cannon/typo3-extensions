<?php
if(!defined('PATH_tslib')) {
    define('PATH_tslib', t3lib_extMgm::extPath('cms').'tslib/');
}

require_once(PATH_tslib.'class.tslib_pibase.php');

class bsg_controller extends tslib_pibase {
    var $_validator = null;

    function & getService($serviceName) {
        $name = strtolower($serviceName);
        require_once(t3lib_extMgm::extPath($this->extKey)."/services/class.{$name}.php");
        $obj = new $serviceName();
        return $obj;
    }

    function & getView($serviceName) {
        $name = strtolower($serviceName);
        require_once(t3lib_extMgm::extPath($this->extKey)."/views/class.{$name}.php");
        $obj = new $serviceName();
        return $obj;
    }

    function getAction() {
        $cmdIndex = 'cmd';
        $default = 'default';
        $validActs = array_keys($this->actions);
        $act = isset($_REQUEST[$cmdIndex]) ? strtolower(trim(($_REQUEST[$cmdIndex]))) : $default;
        return in_array($act, $validActs) ? $act : $default;
    }

    function processAction($act) {
        $method = isset($this->actions[$act]) ? $this->actions[$act] : false;
        if($method) {
            return call_user_method($method, $this);
        }
        return false;
    }


    function isPostMethod() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    function trimPostData($postIndex) {
        $arrOut = array();
        if(count($_POST[$postIndex])) {
            foreach ($_POST[$postIndex] as $key=>$val) {
                $arrOut[$key] = trim($val);
            }
        }
        return $arrOut;
    }

    function redirect($url) {
        header("Location: {$url}");
    }

    function & getValidator() {
        if(is_null($this->_validator)) {
            $this->_validator = new bsg_validator();
        }
        return $this->_validator;
    }

    function validate($arrData, $rules) {
        $v = $this->getValidator();
        return $v->validate($arrData, $rules);
    }

    function _sendContents(&$content, $name='', $type='application/octet-stream') {
        header('HTTP/1.0 200 Ok');
        header('Content-Type: '.$type);
        header('Content-Disposition: inline; filename='.$name);
        header('Content-length: '.strlen($content));
        header('Expires: -1');
        header('Cache-Control: must-revalidate');
        header('Pragma: no-cache');
        echo $content;
        exit;
    }

    function _ajaxResponse($data, $responseFileName='response.xml', $responseType='application/xml') {
        if (!is_array($data))  {
            $xmlResponse = '<?xml version="1.0" encoding="utf-8"?>' .
            "<ajax-response><response>{$data}</response></ajax-response>";
        } else {
            $xmlResponse = '<?xml version="1.0" encoding="utf-8"?><ajax-response>';
            foreach ($data as $dat) {
                $xmlResponse .= '<response>' . $dat . '</response>' . "\n";
            }
            $xmlResponse .= '</ajax-response>';
        }
        return $this->_sendContents($xmlResponse, $responseFileName, $responseType);
    }

    function cdataWrap($data) {
        return "<![CDATA[{$data}]]>";
    }
}
?>