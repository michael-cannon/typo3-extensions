<?php

/**
 * Service class definition
 * (abstract class, you should create derived
 * classes for functionality purporses)
 *
 * @author Eugene Lamskoy <le@krendls.eu>
 * @todo apply Factory pattern in future
 */
class Service {

  /**
   * Constructor
   *
   * @return Service
   */
  function Service() {

  }

  /**
   * Get instance of DAO  object
   *
   * @param string $className
   * @return object
   */
  function & getDAO($className) {
    $className = strtolower($className);
    // require_once(t3lib_extMgm::extPath('bsg_regsteps')."/dao/class.{$className}.php");
    require_once(dirname( __FILE__ )."/dao/class.{$className}.php");
    if(!class_exists($className)) {
      die("Error: {$className} doesn't exist");
    }
    $obj = new $className();
    return $obj;
  }

  /**
   * Get instance of Service object
   *
   * @param unknown_type $className
   * @return unknown
   */
  function & getService($className) {
    $className = strtolower($className);
    // require_once(t3lib_extMgm::extPath('bsg_regsteps')."/services/class.{$className}.php");
    require_once(dirname( __FILE__ )."/services/class.{$className}.php");
    if(!class_exists($className)) {
      die("Error: {$className} doesn't exist");
    }
    $obj = new $className();
    return $obj;
  }

  function buildTranslations($fieldset, &$arrData, $pKey = 'uid', $langField = 'sys_language_uid', $transPrefix = 'trans_', $transSuffix = 'translations') {
    $arrOut = array();
    for($i=0,$iCount=count($arrData);$i<$iCount;$i++) {
      foreach ($fieldset as $fld) {
        if(!isset($arrData[$i][$pKey])) {
          return false;
        }
        $uid = intval($arrData[$i][$pKey]);
        if(isset($arrData[$i][$fld])) {
          $arrOut[$uid][$fld] = $arrData[$i][$fld];
        }

        $langId = intval($arrData[$i][$transPrefix.$langField]);
        if(!$langId) {
          continue;
        }
        if(isset($arrData[$i][$transPrefix.$fld])) {
          $arrOut[$uid][$transSuffix][$langId][$fld] = $arrData[$i][$transPrefix.$fld];
        }
      }
    }
    return $arrOut;
  }

}

?>