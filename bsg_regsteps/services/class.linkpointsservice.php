<?php

class LinkpointsService {
    var $lphp;
    var $_keyfile;
    var $_host;
    var $_port;
    var $_merchantId;
    var $_validFields;
    var $testing = true;
    var $countries = array(
    "AND"	=> "AD"    , "ARE"	=> "AE"    , "AFG"	=> "AF"    , "ATG"	=> "AG"    , "AIA"	=> "AI"
    , "ALB"	=> "AL"    , "ARM"	=> "AM"    , "ANT"	=> "AN"    , "AGO"	=> "AO"    , "ATA"	=> "AQ"
    , "ARG"	=> "AR"    , "ASM"	=> "AS"    , "AUT"	=> "AT"    , "AUS"	=> "AU"    , "ABW"	=> "AW"
    , "AZE"	=> "AZ"    , "BIH"	=> "BA"    , "BRB"	=> "BB"    , "BGD"	=> "BD"    , "BEL"	=> "BE"
    , "BFA"	=> "BF"    , "BGR"	=> "BG"    , "BHR"	=> "BH"    , "BDI"	=> "BI"    , "BEN"	=> "BJ"
    , "BMU"	=> "BM"    , "BRN"	=> "BN"    , "BOL"	=> "BO"    , "BRA"	=> "BR"    , "BHS"	=> "BS"
    , "BTN"	=> "BT"    , "BVT"	=> "BV"    , "BWA"	=> "BW"    , "BLR"	=> "BY"    , "BLZ"	=> "BZ"
    , "CAN"	=> "CA"    , "CCK"	=> "CC"    , "COD"	=> "CD"    , "CAF"	=> "CF"    , "COG"	=> "CG"
    , "CHE"	=> "CH"    , "CIV"	=> "CI"    , "COK"	=> "CK"    , "CHL"	=> "CL"    , "CMR"	=> "CM"
    , "CHN"	=> "CN"    , "COL"	=> "CO"    , "CRI"	=> "CR"    , "CUB"	=> "CU"    , "CPV"	=> "CV"
    , "CXR"	=> "CX"    , "CYP"	=> "CY"    , "CZE"	=> "CZ"    , "DEU"	=> "DE"    , "DJI"	=> "DJ"
    , "DNK"	=> "DK"    , "DMA"	=> "DM"    , "DOM"	=> "DO"    , "DZA"	=> "DZ"    , "ECU"	=> "EC"
    , "EST"	=> "EE"    , "EGY"	=> "EG"    , "ESH"	=> "EH"    , "ERI"	=> "ER"    , "ESP"	=> "ES"
    , "ETH"	=> "ET"    , "FIN"	=> "FI"    , "FJI"	=> "FJ"    , "FLK"	=> "FK"    , "FSM"	=> "FM"
    , "FRO"	=> "FO"    , "FRA"	=> "FR"    , "GAB"	=> "GA"    , "GBR"	=> "GB"    , "GRD"	=> "GD"
    , "GEO"	=> "GE"    , "GUF"	=> "GF"    , "GHA"	=> "GH"    , "GIB"	=> "GI"    , "GRL"	=> "GL"
    , "GMB"	=> "GM"    , "GIN"	=> "GN"    , "GLP"	=> "GP"    , "GNQ"	=> "GQ"    , "GRC"	=> "GR"
    , "SGS"	=> "GS"    , "GTM"	=> "GT"    , "GUM"	=> "GU"    , "GNB"	=> "GW"    , "GUY"	=> "GY"
    , "HKG"	=> "HK"    , "HND"	=> "HN"    , "HRV"	=> "HR"    , "HTI"	=> "HT"    , "HUN"	=> "HU"
    , "IDN"	=> "ID"    , "IRL"	=> "IE"    , "ISR"	=> "IL"    , "IND"	=> "IN"    , "IOT"	=> "IO"
    , "IRQ"	=> "IQ"    , "IRN"	=> "IR"    , "ISL"	=> "IS"    , "ITA"	=> "IT"    , "JAM"	=> "JM"
    , "JOR"	=> "JO"    , "JPN"	=> "JP"    , "KEN"	=> "KE"    , "KGZ"	=> "KG"    , "KHM"	=> "KH"
    , "KIR"	=> "KI"    , "COM"	=> "KM"    , "KNA"	=> "KN"    , "PRK"	=> "KP"    , "KOR"	=> "KR"
    , "KWT"	=> "KW"    , "CYM"	=> "KY"    , "KAZ"	=> "KZ"    , "LAO"	=> "LA"    , "LBN"	=> "LB"
    , "LCA"	=> "LC"    , "LIE"	=> "LI"    , "LKA"	=> "LK"    , "LBR"	=> "LR"    , "LSO"	=> "LS"
    , "LTU"	=> "LT"    , "LUX"	=> "LU"    , "LVA"	=> "LV"    , "LBY"	=> "LY"    , "MAR"	=> "MA"
    , "MCO"	=> "MC"    , "MDA"	=> "MD"    , "MDG"	=> "MG"    , "MHL"	=> "MH"    , "MKD"	=> "MK"
    , "MLI"	=> "ML"    , "MMR"	=> "MM"    , "MNG"	=> "MN"    , "MAC"	=> "MO"    , "MNP"	=> "MP"
    , "MTQ"	=> "MQ"    , "MRT"	=> "MR"    , "MSR"	=> "MS"    , "MLT"	=> "MT"    , "MUS"	=> "MU"
    , "MDV"	=> "MV"    , "MWI"	=> "MW"    , "MEX"	=> "MX"    , "MYS"	=> "MY"    , "MOZ"	=> "MZ"
    , "NAM"	=> "NA"    , "NCL"	=> "NC"    , "NER"	=> "NE"    , "NFK"	=> "NF"    , "NGA"	=> "NG"
    , "NIC"	=> "NI"    , "NLD"	=> "NL"    , "NOR"	=> "NO"    , "NPL"	=> "NP"    , "NRU"	=> "NR"
    , "NIU"	=> "NU"    , "NZL"	=> "NZ"    , "OMN"	=> "OM"    , "PAN"	=> "PA"    , "PER"	=> "PE"
    , "PYF"	=> "PF"    , "PNG"	=> "PG"    , "PHL"	=> "PH"    , "PAK"	=> "PK"    , "POL"	=> "PL"
    , "SPM"	=> "PM"    , "PCN"	=> "PN"    , "PRI"	=> "PR"    , "PRT"	=> "PT"    , "PLW"	=> "PW"
    , "PRY"	=> "PY"    , "QAT"	=> "QA"    , "REU"	=> "RE"    , "ROU"	=> "RO"    , "RUS"	=> "RU"
    , "RWA"	=> "RW"    , "SAU"	=> "SA"    , "SLB"	=> "SB"    , "SYC"	=> "SC"    , "SDN"	=> "SD"
    , "SWE"	=> "SE"    , "SGP"	=> "SG"    , "SHN"	=> "SH"    , "SVN"	=> "SI"    , "SJM"	=> "SJ"
    , "SVK"	=> "SK"    , "SLE"	=> "SL"    , "SMR"	=> "SM"    , "SEN"	=> "SN"    , "SOM"	=> "SO"
    , "SUR"	=> "SR"    , "STP"	=> "ST"    , "SLV"	=> "SV"    , "SYR"	=> "SY"    , "SWZ"	=> "SZ"
    , "TCA"	=> "TC"    , "TCD"	=> "TD"    , "ATF"	=> "TF"    , "TGO"	=> "TG"    , "THA"	=> "TH"
    , "TJK"	=> "TJ"    , "TKL"	=> "TK"    , "TKM"	=> "TM"    , "TUN"	=> "TN"    , "TON"	=> "TO"
    , "TLS"	=> "TL"    , "TUR"	=> "TR"    , "TTO"	=> "TT"    , "TUV"	=> "TV"    , "TWN"	=> "TW"
    , "TZA"	=> "TZ"    , "UKR"	=> "UA"    , "UGA"	=> "UG"    , "UMI"	=> "UM"    , "USA"	=> "US"
    , "URY"	=> "UY"    , "UZB"	=> "UZ"    , "VAT"	=> "VA"    , "VCT"	=> "VC"    , "VEN"	=> "VE"
    , "VGB"	=> "VG"    , "VIR"	=> "VI"    , "VNM"	=> "VN"    , "VUT"	=> "VU"    , "WLF"	=> "WF"
    , "WSM"	=> "WS"    , "YEM"	=> "YE"    , "MYT"	=> "YT"    , "ZAF"	=> "ZA"    , "ZMB"	=> "ZM"
    , "ZWE"	=> "ZW"    , "PSE"	=> "PS"    , "CSG"	=> "CS"
    );


    function LinkpointsService() {
        $this->lphp = new lphp();
    }

    function setKeyfile($arg) {
        $this->_keyfile = $arg;
    }

    function getKeyfile() {
        return $this->_keyfile;
    }

    function setHost($arg) {
        $this->_host = $arg;
    }

    function getHost() {
        return $this->_host;
    }

    function setPort($arg) {
        $this->_port = $arg;
    }

    function getPort() {
        return $this->_port;
    }

    function setMerchantID($arg) {
        $this->_merchantId = $arg;
    }

    function getMerchantId() {
        return $this->_merchantId;
    }

    function _userArrToOrderArr($userArr) {
        $myorder 				= array();
        $myorder["name"] 		= $userArr['first_name'] 
									. ' ' 
									. $userArr['last_name']
									;
        $myorder["company"] 	= htmlspecialchars( $userArr['company'] );
        $myorder["country"] 	= htmlspecialchars($this->countryConvert($userArr['country']));
        $myorder["address1"] 	= $userArr['address'];
        $myorder["city"] 		= $userArr['city'];
        $myorder["state"] 		= $userArr['state'];
        $myorder["zip"] 		= $userArr['zip'];
        $myorder["phone"] 		= $userArr['phone'];
        $myorder["fax"] 		= $userArr['fax'];
        $myorder["email"] 		= $userArr['pmail'];
        $myorder["configfile"] 	= strval($this->getMerchantId());
        $myorder["keyfile"] 	= strval($this->getKeyfile());
        $myorder["host"] 		= strval($this->getHost());
        $myorder["port"] 		= strval($this->getPort());
        $myorder["ip"] 			= $_SERVER["REMOTE_ADDR"];
        $myorder["cardnumber"] 		= $userArr['ccnumber'];
        $myorder["cardexpmonth"] 	= $userArr['expmonth'];
        $myorder["cardexpyear"] 	= $userArr['expyear'];

        return $myorder;
    }

    function preAuth($chargetotal, $userArr = array()) {
        if($this->testing) {
            $userArr = array(
            'ccnumber' => '4111111111111111',
            );
            $chargetotal = '1.05';
        }
        
        $orderID 				= false;
        $myorder = $this->_userArrToOrderArr($userArr);

        $myorder["ordertype"] 	= "PREAUTH";
        if($this->testing) {
            $myorder["result"] = "GOOD"; # For a test, set result to GOOD, DECLINE, or DUPLICATE
        }
        $myorder["subtotal"] 		= $chargetotal;
        $myorder["tax"] 			= 0;
        $myorder["shipping"] 		= 0;
        $myorder["chargetotal"] 	= $chargetotal;
        $myorder["cvmvalue"] 		= '';
        $myorder["cvmindicator"] 	= 'not_present';
        $myorder["referred"] 	= "";

        $result = $this->lphp->curl_process($myorder); # use curl methods
        
        return $result;
    }


    function postAuth($chargetotal, $orderID, $userArr)
    {
        $newOrderID 			= false;
        
        if($this->testing) {
            $userArr = array(
            'ccnumber' => '4111111111111111',            
            );
            $orderID = '909090';
        }
        
        $myorder = $this->_userArrToOrderArr($userArr);
        $myorder["oid"] 		= $orderID; # Must be a valid order ID from a prior Sale or PreAuth
        $myorder["ordertype"] 	= "POSTAUTH";
        
        if($this->testing) {
            $myorder["result"] = "GOOD";  
        }

        $myorder["subtotal"] 		= $chargetotal;
        $myorder["tax"] 			= 0;
        $myorder["shipping"] 		= 0;
        $myorder["chargetotal"] 	= $chargetotal;
        $myorder["cvmvalue"] 		= '';
        $myorder["cvmindicator"] 	= 'not_present';
        $myorder["referred"] 	= "";

        # Send transaction. Use one of two possible methods #
        // $result = $mylphp->process($myorder); # use shared library model
        $result = $this->lphp->curl_process($myorder); # use curl methods
        return $result;
    }

    function countryConvert($str) {
        return isset($this->countries[$str]) ? $this->countries[$str] : '';
    }

}



?>