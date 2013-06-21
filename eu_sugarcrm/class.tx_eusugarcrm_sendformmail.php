<?
include_once('class.tx_eusugarcrm_base.php');

class tx_eusugarcrm_sendformmail {
  var $config;
  var $formVars;
  var $serviceObj;
	
  function sendFormmail_preProcessVariables($emailVars, $caller) {
    //$caller->set_no_cache();
		
    $this->formVars = $emailVars;

    if (is_object($this->serviceObj = new tx_eusugarcrm_base())) {
      $post = t3lib_div::_POST();
      $locationData = $post['locationData'];
      $locData = explode(':',$locationData);
      $rslt = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						     'tx_eusugarcrm_mapping, tx_eusugarcrm_createlead',
						     'tt_content',
						     'uid = '.$locData[2]
						     );
      if ($rslt) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rslt);
      if ($row['tx_eusugarcrm_createlead']) {
	// login
	$this->serviceObj->login();
	// load mapping
	$configTS = $row['tx_eusugarcrm_mapping'];
	$this->config = $this->getConf($configTS);
	//debug($this->config);
	// iterate over objects to create
	while(list(, $obj) = each($this->config)) {
	  $this->createObject($obj);
	}
	$this->serviceObj->logout();
      }
    }
		
    return $emailVars;
  }
	
  function createObject($obj, $parent=null) {
    if ($obj['condition.']) {
      $condition = $obj['condition.'];
      $create=1;
      $and = ($condition['or'])?0:1;
      if ($condition['form']) {
	$create = $this->checkCondition($condition['form']);
      }
      if (!$create)
	return FALSE;
    }
    $this->tshift = $obj['timeshift'];
    $type = $obj['type'];
    if ((($type == 'Leads') || ($type == 'Contacts')) && $obj['uniqueEmail']) {
      $email = $this->formVars['email'];
      $arrResult = $this->serviceObj->contact_by_email($email);
      $i=0;
      while (($item = $arrResult['entry_list'][$i]) && ($item['module_name'] != $type)) {
	$i++;
      }
      if ($item) {
	$objID = $item['id'];
      }
    }
    $dateformat = $obj['input_dateformat'];
    $arrValues = $this->mergeValues($obj['default.'], $obj['mapping.'], $dateformat, $parent);
    $arrValues = array_merge($arrValues, array('id' => $objID));
    $arrResult = $this->serviceObj->set_entry($type, $arrValues);
    $objID = $arrResult['id'];
    $arrResult['module_name'] = $type;
    if (is_array($obj['children.'])) {
      while(list(, $child) = each($obj['children.'])) {
	$this->createObject($child, $arrResult);
      }
    }
    return $objID;
  }

  function checkCondition($cond) {
    
    // determine comparison operator first
    $pos = strpos($cond,'=');
    $posn = strpos($cond,'!');
    if (($pos !== FALSE) && ($posn == ($pos-1))) {
      $pos = $posn;
      $op = '!=';
    }
    elseif ($pos !== FALSE) {
      $op = '=';
    }
    $posn = strpos($cond,'<');
    if (($pos === FALSE) || (($posn === TRUE) && ($posn < $pos))) {
      $pos = $posn;
      $op = '<';
      $posn = strpos($cond,'<=');
      if ($posn = $pos) {
	$pos = $posn;
	$op = '<=';
      }
    }
    $posn = strpos($cond,'>');
    if (($pos === FALSE) || (($posn === TRUE) && ($posn < $pos))) {
      $pos = $posn;
      $op = '>';
      $posn = strpos($cond,'>=');
      if ($posn = $pos) {
	$pos = $posn;
	$op = '>=';
      }
    }

    // get variable name and value
    if ($op)
      list($cVar,$cVal) = explode($op,$cond,2);

    // evaluate condition
    switch ($op) {
    case '=':
      return ((trim($this->formVars[$cVar]) == trim($cVal)) || (trim($this->formVars[$cVar]) == intval($cVal)));
      break;      
    case '!=':
      return ((trim($this->formVars[$cVar]) != trim($cVal)) || (trim($this->formVars[$cVar]) != intval($cVal)));
      break;
    case '>':
      return ($this->formVars[$cVar] > intval($cVal));
      break;
    case '<':
      return ($this->formVars[$cVar] < intval($cVal));
      break;
    case '>=':
      return ($this->formVars[$cVar] >= intval($cVal));
      break;
    case '<=':
      return ($this->formVars[$cVar] <= intval($cVal));
      break;
    default:
      return 0;
    }
  }
	
  function mergeValues($default, $mapping, $dateformat='m/d/Y', $parent=null) {
    while(list($key, $val) = each($mapping)) {
      $arrMapping[$key] = $this->formVars[$val];
    }
    $arrResult = $arrMapping;
    if (is_array($default)) $arrResult = array_merge($default, $arrResult);
    while(list($key, $val) = each($arrResult)) {
      $arrValue = explode('.', $val);
      if (is_array($parent) && strtolower($arrValue[0]) == 'parent') {
	$arrResult[$key] = $parent[strtolower($arrValue[1])];
	if ($arrValue[1] == 'type') $arrResult[$key] = $parent['module_name'];
      }
      if (substr($key, 0, 5) == 'date_') {
	if ($date = $this->validateDate($arrResult[$key], $dateformat)) {
	  if (strtotime($date) > time()) {
	    $date = strftime('%Y-%m-%d %H:%M',(strtotime($date)-3600*$this->tshift));
	    $arrResult[$key] = $date;
	  } else {
	    $this->setDefaultDate($arrResult, $default, $key, '%Y-%m-%d');
	  }
	} else {
	  $this->setDefaultDate($arrResult, $default, $key, '%Y-%m-%d');
	}
      }
    }
    //debug($arrResult);
    return $arrResult;
  }
	
  function validateDate($inputdate, $dateformat) {
    $format = str_replace('d', '([0-9]{1,2})', $dateformat);
    $format = str_replace('m', '([0-9]{1,2})', $format);
    $format = str_replace('y', '([0-9]{2})', $format);
    $format = str_replace('Y', '([0-9]{4})', $format);
    $format = str_replace('H', '([0-1][0-9])', $format);
    $format = str_replace('M', '([0-6][0-9])', $format);
    $format = str_replace('.', '\.', $format);
    $format = '^'.$format.'$';
		
    $dateorder = ereg_replace('[^dmyYHM]', '', $dateformat);
    if (ereg($format, $inputdate, $matches)) {
      for ($jj=1; $jj<count($matches); $jj++) {
	switch (substr($dateorder, $jj-1, 1)) {
	case 'd':
	  $day = $matches[$jj];
	  break;
	case 'm':
	  $month = $matches[$jj];
	  break;
	case 'Y';
	case 'y':
	  $year = $matches[$jj];
	  break;
	case 'H':
	  $hour = $matches[$jj];
	  break;
	case 'M':
	  $minute = $matches[$jj];
	  break;
	}
      }
      if (checkdate($month, $day, $year)) {
	return $year.'-'.$month.'-'.$day.' '.sprintf('%02d',$hour).':'.sprintf('%02d',$minute).':00';
      } else {
	return false;
      }
    } else {
      return false;
    }
  }
	
  function setDefaultDate(&$arrResult, $default, $key, $dateformat='%Y-%m-%d') {
    if ($default[$key]) {
      if ($default[$key] == 'now') {
	$arrResult[$key] = gmstrftime($dateformat, time());
      } else {
	$arrResult[$key] = $default[$key];
      }
    } else {
      unset($arrResult[$key]);
    }
  }
	
  /**
   * This function is used to parse the config information of the server record into an array
   * Define the type of konfiguration you want to have
   * Specify a table if you want only this configuration
   * 
   * @param	string	$config: TS configuration
   * @return	array 	multidimensional TS array
   */
  function getConf($config)	{
    // parsing is done by an instance of the t3lib_TSparser
    if(!class_exists('t3lib_TSparser') && defined('PATH_t3lib')) {
      require_once(PATH_t3lib.'class.t3lib_TSparser.php');
    }
    $parser = t3lib_div::makeInstance('t3lib_TSparser');
    $parser->parse($config);
		
    // after the TS is parsed we delete all konfigurations which do not fit the type AND
    // which are not enabled
    reset($parser->setup);
		
    $conf = $parser->setup;
    return $conf;
  }
}
?>
