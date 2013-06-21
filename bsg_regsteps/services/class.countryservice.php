<?php

class CountryService extends Service {
    var $_dao;
    
    function CountryService() {
        $this->_dao = $this->getDAO("CountryDAO");
    }
    function getCountries() {
        return $this->_dao->getCountries();        
    }
    function getCountryByUid($uid) {
        return $this->_dao->getCountryByUid($uid);
    }
    function getCountryZones($uidCountry) {
        return $this->_dao->getCountryZones($uidCountry);
    }
}
?>