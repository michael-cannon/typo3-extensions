<?php

if(! function_exists('str_split'))
{
    function str_split($text, $split = 1)
    {
        $array = array();

        for ($i = 0; $i < strlen($text);)
        {
            $array[] = substr($text, $i, $split);
            $i += $split;
        }

        return $array;
    }
}

class bsg_validator {

    function isValid_email(&$str) {
        // the base regexp for address
        $regex = '&^(?: # recipient:
         ("\s*(?:[^"\f\n\r\t\v\b\s]+\s*)+")|                          #1 quoted name
         ([-\w!\#\$%\&\'*+~/^`|{}]+(?:\.[-\w!\#\$%\&\'*+~/^`|{}]+)*)) #2 OR dot-atom
         @(((\[)? #3 domain, 4 as IPv4, 5 optionally bracketed
         (?:(?:(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))\.){3}
               (?:(?:25[0-5])|(?:2[0-4][0-9])|(?:[0-1]?[0-9]?[0-9]))))(?(5)\])|
         ((?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)*[a-z](?:[-a-z0-9]*[a-z0-9])?)  #6 domain as hostname
         \.((?:([^-])[-a-z]*[-a-z])?)) #7 ICANN domain names 
         $&xi';
        return (bool) preg_match($regex, $str);
    }

    function isValid_int(&$str, $noConvert = false) {
        if(!preg_match("/^[\d]+$/i", $str)) {
            return false;
        }
        if(!$noConvert) {
            $str = intval($str);
        } else {
            if(intval($str) < 10) {
                $str = "0".strval($str);
            }
        }
        return true;
    }

    function isValid_formstring(&$str) {
        $str = trim(strip_tags($str));
        $tmp = str_split($str, 2048);
        $str = $tmp[0];
        return !empty($str);
    }



    function validate($data, $rules) {
        $arrResult = array();
        $fields = array_keys($rules);


        // Mandatory checkups
        foreach ($fields as $fld) {

            $mandatory = isset($rules[$fld]['mandatory']) && (bool) ($rules[$fld]['mandatory']);

            if($mandatory && (!isset($data[$fld]) || empty($data[$fld]))) {                
                $title = $rules[$fld]['title'];
                $arrResult['errors'][] = "Field \"{$title}\" is empty";
                $arrResult['errors_flds'][] = $fld;
                continue;
            }

            $type = $rules[$fld]['type'];
            $title = $rules[$fld]['title'];


            switch ($type) {
				case ! $mandatory && ! $data[$fld]:
					break;

                case "int":
                    if(!$this->isValid_int($data[$fld])) {
                        
                        $arrResult['errors'][] = "Field \"{$title}\" is not an integer";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "intc":
                    if(!$this->isValid_int($data[$fld])) {
                        $arrResult['errors'][] = "Field \"{$title}\" is not an integer";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    } 
                    break;                    
                case "email":
                    if(!$this->isValid_email($data[$fld])) {
                        $arrResult['errors'][] = "Field \"{$title}\" contains invalid email";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "formstring":
                    if(!$this->isValid_formstring($data[$fld])) {
                        $arrResult['errors'][] = "Field \"{$title}\" is invalid";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "salesforceid":
                    if(!preg_match("#^[-a-zA-Z0-9]+$#",$data[$fld])) {
                        $arrResult['errors'][] = "Course \"{$title}\" is invalid";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "expiration":
                    if(!preg_match("@[0-9]{1,2}/[0-9]{4}@", $data[$fld])) {
                        $arrResult['errors'][] = "Field \"{$title}\" contains invalid expiration date";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "zip":
		   			 $valid = false;
					 // US 03801-2409 or 03801-2409 or 03801
                    if('USA' == $data['country'] && preg_match("/^[\d]{5}-?([\d]{4})?$/i", $data[$fld])) {
						$valid = true;	
                    }
					// Canadian ANA NAN or ANANAN
                    if('CAN' == $data['country'] && preg_match("/^[a-zA-Z]{1}[\d]{1}[a-zA-Z]{1}[\s]?[\d]{1}[a-zA-Z]{1}[\d]{1}$/i", $data[$fld])) {
						$valid = true;	
                    }
                    if('USA' != $data['country'] 
						&& 'CAN' != $data['country'] 
						&& preg_match("/^[ -a-zA-Z0-9]{1,15}$/i", $data[$fld])) {
						$valid = true;	
                    }
					if(!$valid) {
						$arrResult['errors'][] = "Field \"{$title}\" contains invalid zip";
						$arrResult['errors_flds'][] = $fld;
						continue;
					}               
                    break;
                case "ccnumber":
                    $validator = new CreditCardValidator($data[$fld]);
                    if(!(bool) $validator->mod10($data[$fld]) == CC_SUCCESS ) {
                        $arrResult['errors'][] = "Field \"{$title}\" contains invalid credit card number";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "phone":
                    if(!preg_match("/^[0-9 +-]+$/", $data[$fld])) {
                        $arrResult['errors'][] = "Field \"{$title}\" contains invalid phone number";
                        $arrResult['errors_flds'][] = $fld;
                        continue;
                    }
                    break;
                case "pcode":
                    require(t3lib_extMgm::extPath('bsg_regsteps')."/config/" .  BSG_REG_CONF_CODE . ".priority-codes.php");
                    $this->codes = $priorityCodes;
                    $userCode = strtoupper(trim(strval($data[$fld])));
                    if(isset($this->codes[$userCode])) {
                        $data[$fld] = $userCode;
                    } else {
                        $data[$fld] = BSG_REG_DEFAULT_PC;
                    }
                    break;
                case "state":
                    if($data[$fld] == 'PCO' ) {
                        $arrResult['errors'][] = "Please select a valid state";
                        continue;
                    }
                    break;
            }
            
            $arrResult['data'][$fld] = $data[$fld];
        }

        
        return $arrResult;
    }
    
    

}
?>