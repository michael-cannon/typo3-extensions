<?php

class CountryDao extends DAO {
    var $_table = 'static_countries';
    function getCountries() {        
        return $this->getLinearList($this->_table, '1=1 ORDER BY cn_short_en ASC', 'cn_iso_3');
    }
    function getCountryByUid($id) {
        return $this->getSingleItem($this->_table, "cn_iso_3", $id);
    }
    function getCountryZones($uidCountry) {
        $uidCountry = trim(addslashes(strip_tags($uidCountry)));
        return $this->getLinearList('static_country_zones', "`zn_country_iso_3` = \"{$uidCountry}\"");
    }
}

?>