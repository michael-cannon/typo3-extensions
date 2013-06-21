<?php

/**
 * @author Eugene Lamskoy <e.lamskoy@gmail.com>
 *
 */

class bsg_regsteps_view {
    var $_data;
    var $_template;
    var $_templatesPath;
    var $_errors = array();

    function bsg_regsteps_view() {
        $this->setTemplatePath(t3lib_extMgm::extPath('bsg_regsteps')."/templates/");
    }

    function setTemplatePath($path) {
        $this->_templatesPath = $path;
    }

    function assign($index, $data) {
        $this->_data[$index] = $data;
    }

    function hasVar($index)   {
        return isset($this->_data[$index]);
    }

    function getVar($index) {
        return $this->hasVar($index) ? $this->_data[$index] : null;
    }

    function printVar($index) {
        return print $this->getVar($index);
    }
    
    function setTemplate($template) {
        $this->_template = $template;
    }

    function fetch() {
        ob_start();
        require($this->_templatesPath.$this->_template);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    function isPostMethod() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    function clearErrors() {
        $this->_errors = array();
    }
    function addError($text) {
        $this->_errors[] = $text;
    }
    function getErrors() {
        return $this->_errors;        
    }
    function hasErrors() {
        return count($this->_errors) != 0;
    }    
    function getCurrentPageId() {
        return $GLOBALS['TSFE']->id;
    }
    function customPrice($number) {
        return number_format($number, 2, '.', ',');
    }
}


?>