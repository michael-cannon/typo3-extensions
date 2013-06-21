<?php

//include_once "lphp.php";
//require_once "lphp.php";
//require_once(t3lib_extMgm::extPath('sr_feuser_register').'pi1/lphp.php');
//require_once( dirname( __FILE__ ) . '/lphp.php');

/**
Performs Linkpoint credit card functions

$Id: linkpoint.php,v 1.1.1.1 2010/04/15 10:03:07 peimic.comprock Exp $
@author Jaspreet Singh
*/
class LinkpointCreditCard {

	var $debug = false;
	var $testing = false;

	var $countries 				= array(
									"AND"	=> "AD"
									, "ARE"	=> "AE"
									, "AFG"	=> "AF"
									, "ATG"	=> "AG"
									, "AIA"	=> "AI"
									, "ALB"	=> "AL"
									, "ARM"	=> "AM"
									, "ANT"	=> "AN"
									, "AGO"	=> "AO"
									, "ATA"	=> "AQ"
									, "ARG"	=> "AR"
									, "ASM"	=> "AS"
									, "AUT"	=> "AT"
									, "AUS"	=> "AU"
									, "ABW"	=> "AW"
									, "AZE"	=> "AZ"
									, "BIH"	=> "BA"
									, "BRB"	=> "BB"
									, "BGD"	=> "BD"
									, "BEL"	=> "BE"
									, "BFA"	=> "BF"
									, "BGR"	=> "BG"
									, "BHR"	=> "BH"
									, "BDI"	=> "BI"
									, "BEN"	=> "BJ"
									, "BMU"	=> "BM"
									, "BRN"	=> "BN"
									, "BOL"	=> "BO"
									, "BRA"	=> "BR"
									, "BHS"	=> "BS"
									, "BTN"	=> "BT"
									, "BVT"	=> "BV"
									, "BWA"	=> "BW"
									, "BLR"	=> "BY"
									, "BLZ"	=> "BZ"
									, "CAN"	=> "CA"
									, "CCK"	=> "CC"
									, "COD"	=> "CD"
									, "CAF"	=> "CF"
									, "COG"	=> "CG"
									, "CHE"	=> "CH"
									, "CIV"	=> "CI"
									, "COK"	=> "CK"
									, "CHL"	=> "CL"
									, "CMR"	=> "CM"
									, "CHN"	=> "CN"
									, "COL"	=> "CO"
									, "CRI"	=> "CR"
									, "CUB"	=> "CU"
									, "CPV"	=> "CV"
									, "CXR"	=> "CX"
									, "CYP"	=> "CY"
									, "CZE"	=> "CZ"
									, "DEU"	=> "DE"
									, "DJI"	=> "DJ"
									, "DNK"	=> "DK"
									, "DMA"	=> "DM"
									, "DOM"	=> "DO"
									, "DZA"	=> "DZ"
									, "ECU"	=> "EC"
									, "EST"	=> "EE"
									, "EGY"	=> "EG"
									, "ESH"	=> "EH"
									, "ERI"	=> "ER"
									, "ESP"	=> "ES"
									, "ETH"	=> "ET"
									, "FIN"	=> "FI"
									, "FJI"	=> "FJ"
									, "FLK"	=> "FK"
									, "FSM"	=> "FM"
									, "FRO"	=> "FO"
									, "FRA"	=> "FR"
									, "GAB"	=> "GA"
									, "GBR"	=> "GB"
									, "GRD"	=> "GD"
									, "GEO"	=> "GE"
									, "GUF"	=> "GF"
									, "GHA"	=> "GH"
									, "GIB"	=> "GI"
									, "GRL"	=> "GL"
									, "GMB"	=> "GM"
									, "GIN"	=> "GN"
									, "GLP"	=> "GP"
									, "GNQ"	=> "GQ"
									, "GRC"	=> "GR"
									, "SGS"	=> "GS"
									, "GTM"	=> "GT"
									, "GUM"	=> "GU"
									, "GNB"	=> "GW"
									, "GUY"	=> "GY"
									, "HKG"	=> "HK"
									, "HND"	=> "HN"
									, "HRV"	=> "HR"
									, "HTI"	=> "HT"
									, "HUN"	=> "HU"
									, "IDN"	=> "ID"
									, "IRL"	=> "IE"
									, "ISR"	=> "IL"
									, "IND"	=> "IN"
									, "IOT"	=> "IO"
									, "IRQ"	=> "IQ"
									, "IRN"	=> "IR"
									, "ISL"	=> "IS"
									, "ITA"	=> "IT"
									, "JAM"	=> "JM"
									, "JOR"	=> "JO"
									, "JPN"	=> "JP"
									, "KEN"	=> "KE"
									, "KGZ"	=> "KG"
									, "KHM"	=> "KH"
									, "KIR"	=> "KI"
									, "COM"	=> "KM"
									, "KNA"	=> "KN"
									, "PRK"	=> "KP"
									, "KOR"	=> "KR"
									, "KWT"	=> "KW"
									, "CYM"	=> "KY"
									, "KAZ"	=> "KZ"
									, "LAO"	=> "LA"
									, "LBN"	=> "LB"
									, "LCA"	=> "LC"
									, "LIE"	=> "LI"
									, "LKA"	=> "LK"
									, "LBR"	=> "LR"
									, "LSO"	=> "LS"
									, "LTU"	=> "LT"
									, "LUX"	=> "LU"
									, "LVA"	=> "LV"
									, "LBY"	=> "LY"
									, "MAR"	=> "MA"
									, "MCO"	=> "MC"
									, "MDA"	=> "MD"
									, "MDG"	=> "MG"
									, "MHL"	=> "MH"
									, "MKD"	=> "MK"
									, "MLI"	=> "ML"
									, "MMR"	=> "MM"
									, "MNG"	=> "MN"
									, "MAC"	=> "MO"
									, "MNP"	=> "MP"
									, "MTQ"	=> "MQ"
									, "MRT"	=> "MR"
									, "MSR"	=> "MS"
									, "MLT"	=> "MT"
									, "MUS"	=> "MU"
									, "MDV"	=> "MV"
									, "MWI"	=> "MW"
									, "MEX"	=> "MX"
									, "MYS"	=> "MY"
									, "MOZ"	=> "MZ"
									, "NAM"	=> "NA"
									, "NCL"	=> "NC"
									, "NER"	=> "NE"
									, "NFK"	=> "NF"
									, "NGA"	=> "NG"
									, "NIC"	=> "NI"
									, "NLD"	=> "NL"
									, "NOR"	=> "NO"
									, "NPL"	=> "NP"
									, "NRU"	=> "NR"
									, "NIU"	=> "NU"
									, "NZL"	=> "NZ"
									, "OMN"	=> "OM"
									, "PAN"	=> "PA"
									, "PER"	=> "PE"
									, "PYF"	=> "PF"
									, "PNG"	=> "PG"
									, "PHL"	=> "PH"
									, "PAK"	=> "PK"
									, "POL"	=> "PL"
									, "SPM"	=> "PM"
									, "PCN"	=> "PN"
									, "PRI"	=> "PR"
									, "PRT"	=> "PT"
									, "PLW"	=> "PW"
									, "PRY"	=> "PY"
									, "QAT"	=> "QA"
									, "REU"	=> "RE"
									, "ROU"	=> "RO"
									, "RUS"	=> "RU"
									, "RWA"	=> "RW"
									, "SAU"	=> "SA"
									, "SLB"	=> "SB"
									, "SYC"	=> "SC"
									, "SDN"	=> "SD"
									, "SWE"	=> "SE"
									, "SGP"	=> "SG"
									, "SHN"	=> "SH"
									, "SVN"	=> "SI"
									, "SJM"	=> "SJ"
									, "SVK"	=> "SK"
									, "SLE"	=> "SL"
									, "SMR"	=> "SM"
									, "SEN"	=> "SN"
									, "SOM"	=> "SO"
									, "SUR"	=> "SR"
									, "STP"	=> "ST"
									, "SLV"	=> "SV"
									, "SYR"	=> "SY"
									, "SWZ"	=> "SZ"
									, "TCA"	=> "TC"
									, "TCD"	=> "TD"
									, "ATF"	=> "TF"
									, "TGO"	=> "TG"
									, "THA"	=> "TH"
									, "TJK"	=> "TJ"
									, "TKL"	=> "TK"
									, "TKM"	=> "TM"
									, "TUN"	=> "TN"
									, "TON"	=> "TO"
									, "TLS"	=> "TL"
									, "TUR"	=> "TR"
									, "TTO"	=> "TT"
									, "TUV"	=> "TV"
									, "TWN"	=> "TW"
									, "TZA"	=> "TZ"
									, "UKR"	=> "UA"
									, "UGA"	=> "UG"
									, "UMI"	=> "UM"
									, "USA"	=> "US"
									, "URY"	=> "UY"
									, "UZB"	=> "UZ"
									, "VAT"	=> "VA"
									, "VCT"	=> "VC"
									, "VEN"	=> "VE"
									, "VGB"	=> "VG"
									, "VIR"	=> "VI"
									, "VNM"	=> "VN"
									, "VUT"	=> "VU"
									, "WLF"	=> "WF"
									, "WSM"	=> "WS"
									, "YEM"	=> "YE"
									, "MYT"	=> "YT"
									, "ZAF"	=> "ZA"
									, "ZMB"	=> "ZM"
									, "ZWE"	=> "ZW"
									, "PSE"	=> "PS"
									, "CSG"	=> "CS"
								);
	
	function sale() {
		if ($this->debug) {
			echo "function sale() {";
		}
	}
	
	/**
	Preauthorizes a transaction.
	$result = preAuth( ... );
	$newOrderID = $result['r_ordernum']; //the order ID.  false if bad transaction.
	@return array. the result returned from the gateway.
	*/
	function preAuth($cc_number, $cc_month, $cc_year, $chargetotal, $userArr, $details)
	{

		if ($this->testing) {			
			$cc_number = '4111-1111-1111-1111';
			$cc_month = '03';
			$cc_year = '10';
			$chargetotal = '1.09';
		}
		
		$orderID 				= false;		
		$mylphp 				= new lphp;
		
		// billing data fields
		$myorder 				= array();
        $myorder["name"] 		= $userArr['name'];
		// convert & to &amp; per LP API
        $myorder["company"] 	= htmlspecialchars( $userArr['company'] );
        $myorder["address1"] 	= $userArr['address'];
        $myorder["city"] 		= $userArr['city'];
        $myorder["state"] 		= $userArr['zone'];
		// Required for AVS. If not provided, transactions will downgrade.
        $myorder["zip"] 		= $userArr['zip'];
		// MLC needs to be 2-digit
        $myorder["country"] 	= $this->countries[ $userArr['static_info_country'] ];
        $myorder["phone"] 		= $userArr['telephone'];
        $myorder["fax"] 		= $userArr['fax'];
        $myorder["email"] 		= $userArr['email'];
		//$myorder["addrnum"] 	= "123"; # Required for AVS. If not provided, transactions will downgrade.

		// transaction data fields
        $myorder["ip"] 			= $_SERVER["REMOTE_ADDR"];

		// order options data fields
		$myorder["ordertype"] 	= "PREAUTH";
		if ($this->testing) $myorder["result"] = "GOOD"; # For a test, set result to GOOD, DECLINE, or DUPLICATE

		// payment data fields
		$myorder["subtotal"] 		= $chargetotal;
		$myorder["tax"] 			= 0;
		$myorder["shipping"] 		= 0;
		$myorder["chargetotal"] 	= $chargetotal;

		// creait card data fields
		$myorder["cardnumber"] 		= $cc_number;
		$myorder["cardexpmonth"] 	= $cc_month;
		$myorder["cardexpyear"] 	= $cc_year;
		$myorder["cvmvalue"] 		= '';
		$myorder["cvmindicator"] 	= 'not_present';

		// merchant info data fields
		$myorder["configfile"] 	= "972837"; # Change this to your store number
		$myorder["keyfile"] 	= "/home/bpm/public_html/972837.pem"; # Change this to the name and location of your certificate file
		$myorder["host"] 		= "secure.linkpt.net";
		$myorder["port"] 		= "1129";

		// notes data fields
		$myorder["comments"] 	= $details[ 1 ];
		// $myorder["comments"] 	= "";
		$myorder["referred"] 	= "";

        // $myorder["debugging"]="true";

		# Send transaction. Use one of two possible methods #

		$result = $mylphp->curl_process($myorder); # use curl methods

		if ($result["r_approved"] != "APPROVED") {	

		} else { 
			$orderID = $result['r_ordernum'];
		}		
		return $result; 
	}

	/**
	Postauths a transaction.
	$result = postAuth( ... );
	$newOrderID = $result['r_ordernum']; //the order ID.  false if bad transaction.
	@return array. the result returned from the gateway.
	*/
	function postAuth($cc_number, $cc_month, $cc_year, $chargetotal, $orderID
        , $userArr
        , $details
	)
	{
		if ($this->debug) {
			echo "function postAuth($cc_number, $cc_month, $cc_year, $chargetotal, $orderID) {";
		}
		if ($this->testing) {
			$cc_number = '4111-1111-1111-1111';
			$cc_month = '03';
			$cc_year = '10';
			$chargetotal = '1.09';
			$orderID = '12345678-A345';
		}
		
		$newOrderID 			= false;
		
		$mylphp 				= new lphp;

		// billing data fields
		$myorder 				= array();
        $myorder["name"] 		= $userArr['name'];
		// convert & to &amp; per LP API
        $myorder["company"] 	= htmlspecialchars( $userArr['company'] );
        $myorder["address1"] 	= $userArr['address'];
        $myorder["city"] 		= $userArr['city'];
        $myorder["state"] 		= $userArr['zone'];
		// Required for AVS. If not provided, transactions will downgrade.
        $myorder["zip"] 		= $userArr['zip'];
		// MLC needs to be 2-digit
        $myorder["country"] 	= $this->countries[ $userArr['static_info_country'] ];
        $myorder["phone"] 		= $userArr['telephone'];
        $myorder["fax"] 		= $userArr['fax'];
        $myorder["email"] 		= $userArr['email'];
		//$myorder["addrnum"] 	= "123"; # Required for AVS. If not provided, transactions will downgrade.

		// transaction data fields
        $myorder["ip"] 			= $_SERVER["REMOTE_ADDR"];
		$myorder["oid"] 		= $orderID; # Must be a valid order ID from a prior Sale or PreAuth

		// order options data fields
		$myorder["ordertype"] 	= "POSTAUTH";
		if ($this->testing) $myorder["result"] = "GOOD"; # For a test, set result to GOOD, DECLINE, or DUPLICATE

		// payment data fields
		$myorder["subtotal"] 		= $chargetotal;
		$myorder["tax"] 			= 0;
		$myorder["shipping"] 		= 0;
		$myorder["chargetotal"] 	= $chargetotal;

		// creait card data fields
		$myorder["cardnumber"] 		= $cc_number;
		$myorder["cardexpmonth"] 	= $cc_month;
		$myorder["cardexpyear"] 	= $cc_year;
		$myorder["cvmvalue"] 		= '';
		$myorder["cvmindicator"] 	= 'not_present';

		// merchant info data fields
		$myorder["configfile"] 	= "972837"; # Change this to your store number
		$myorder["keyfile"] 	= "/home/bpm/public_html/972837.pem"; # Change this to the name and location of your certificate file
		$myorder["host"] 		= "secure.linkpt.net";
		$myorder["port"] 		= "1129";

		// notes data fields
		$myorder["comments"] 	= $details[ 1 ];
		$myorder["referred"] 	= "";

        // $myorder["debugging"]="true";

		# Send transaction. Use one of two possible methods #
		// $result = $mylphp->process($myorder); # use shared library model
		$result = $mylphp->curl_process($myorder); # use curl methods

		if ($result["r_approved"] != "APPROVED") // transaction failed, print the reason
		{
			if ($this->debug) {
				echo "Status: $result[r_approved]\n";
				echo "Error: $result[r_error]\n";
			}			
		} else { // success
			$newOrderID = $result['r_ordernum']; //the order ID.  false if bad transaction.
		}
			
		return $result; 
	}
}

?>
