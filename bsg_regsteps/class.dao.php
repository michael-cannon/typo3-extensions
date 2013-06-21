<?php

define('DAO_REPLACE_COMMAND', true);
define('DAO_INSERT_COMMAND', false);

/**
 * DAO class - wrapper for TYPO3 database object
 * Designed to simplify operations with TYPO3 database
 */
class DAO {
  /**
   * Private variable - TYPO3 database connector
   *
   * @var object
   */
  var $db;

  /**
   * Constructor
   * must be always called from constructor of derived class
   * @return DAO
   */
  function DAO() {
    $this->db = &$GLOBALS['TYPO3_DB'];
  }

  /**
   * Perform SQL query
   *
   * @param string $sql
   * @return object
   */
  function query ($sql) {
    return $this->db->sql_query($sql);
  }

  /**
   * Fetch assoc row
   *
   * @param object $res
   * @return array
   */
  function fetch_assoc($res) {
    return $this->db->sql_fetch_assoc($res);
  }

  /**
   * Fetch single row
   *
   * @param object $res
   * @return array
   */
  function fetch_row($res) {
    return $this->db->sql_fetch_row($res);
  }

  /**
   * Get number of rows
   *
   * @param object $res
   * @return int
   */
  function num_rows($res) {
    return $this->db->sql_num_rows($res);
  }

  /**
   * Select multiple rows from database
   *
   * @param string $sql
   * @return array
   */
  function selectMultiple($sql, $key = false) {
    if(!($res = $this->query($sql))) {
      return false;
    }
    $result = array();
    while($sqlRow = $this->fetch_assoc($res)) {
      if($key && isset($sqlRow[$key])) {
        $result[$sqlRow[$key]] = $sqlRow;
      } else {
        $result[] = $sqlRow;
      }
    }
    return $result;
  }

  /**
   * Simple selection for one item
   *
   * @param string $sql
   * @param bool $singleField (optional)
   * @return array
   */
  function selectSimple($sql, $singleField = false) {
    if (!$sqlRes = $this->query($sql)) {
      return false;
    }
    $data = $this->fetch_assoc($sqlRes);
    return $singleField !== false ? (isset($data[$singleField]) ? $data[$singleField] : false) : $data;
  }


  function getLinearList($table, $wherePart = '1=1', $key = false) {
    $sql = "SELECT * FROM {$table} WHERE {$wherePart}";
    return $this->selectMultiple($sql, $key);
  }

  /**
   * Format field set (including alias)
   *
   * @param array $arrFields
   * @param string $alias
   * @return string
   */
  function formatFieldSet($arrFields, $alias = false) {
    $arrOut = array();
    if($alias === false) {
      foreach ($arrFields as $fld) {
        $arrOut [] = "`{$fld}`";
      }
    } else {
      foreach ($arrFields as $fld) {
        $arrOut [] = "`{$alias}`.`{$fld}`";
      }
    }
    return implode(', ', $arrOut);
  }

  function formatIdsList($arrIds, $useBrackets = true) {
    if(!$arrIds) {

      return $useBrackets ? '()' : '';
    }
    if(is_scalar($arrIds)) {
      return $useBrackets ? "({$arrIds})" : strval($arrIds);
    }
    $arrOut = array();
    foreach ($arrIds as $id) {
      $val = intval($id);
      if($val) {
        $arrOut[] = $val;
      }
    }
    $imp = implode(',',$arrOut);
    return $useBrackets ? "({$imp})" : strval($imp);
  }

  /**
   * Private function used to build VALUES query part 
   * in INSERT INTO / REPLACE INTO constructions
   *
   * @param array $arrData
   * @param array $fieldset
   * @param bool $singleItemMode
   * @return string
   */
  function buildValuesQueryPart(&$arrData, &$fieldset, $singleItemMode = false) {   
    
    // Single Item Mode processing
    if($singleItemMode) {
      $arrFields = array();
      foreach ($fieldset as $fld) {
        $arrFields[] = "\"{$arrData[$fld]}\"";        
      }
      return '('.implode(",", $arrFields).')';
      
    }
        
    // Multiple Items Mode Processing
    $arrItems = array();
    foreach ($arrData as $item) {
      $arrFields = array();
      foreach ($fieldset as $fld) {
        $arrFields[] = "\"{$arrData[$fld]}\"";
      }
      $arrItems[] = '('.implode(",", $arrFields).')';
    }
    return implode(',', $arrItems);    
  }

  /**
   * Insert multiple rows into database using INSERT INTO / REPLACE INTO  constructions
   *
   * @param array $arrData
   * @param string $table
   * @param array $fieldset
   * @param bool $replace user REPLACE INTO construction instead of INSERT INTO
   * @param array $pKeys primary keys array to exclude from fieldset, optional
   * @return bool
   */

  function insertMultiple(&$arrData, $table, $fieldset, $replace = false, $pKeys = array()) {
    if(!count($arrData)) {
      return false;
    }
    if(count($pKeys)) {
      $fieldset = array_diff($fieldset, $pKeys);
    }
    $prefix = $replace ? "REPLACE" : "INSERT";
    $fields = $this->formatFieldSet($fieldset);
    $valuesPart = $this->buildValuesQueryPart($arrData, $fieldset);
    $sqlQuery = "{$prefix} INTO `{$table}` ({$fields}) VALUES {$valuesPart}";

    
    return $this->query($sqlQuery);
  }

  function insertSimple(&$arrData, $table, $fieldset, $replace = false, $pKeys = array() ) {
    if(!count($arrData)) {
      return false;
    }
    if(count($pKeys)) {
      $fieldset = array_diff($fieldset, $pKeys);
    }
    $prefix = $replace ? "REPLACE" : "INSERT";
    $fields = $this->formatFieldSet($fieldset);
    $valuesPart = $this->buildValuesQueryPart($arrData, $fieldset, true);
    $sqlQuery = "{$prefix} INTO `{$table}` ({$fields}) VALUES {$valuesPart}";   
    /*var_dump($sqlQuery);
    die();*/
    return $this->query($sqlQuery);    
  }
  

  function eraseRecords($table, $wherePart, $limitToOne = false) {
    $limitPart = $limitToOne ? 'LIMIT 1' : '';
    $sqlQuery = "DELETE FROM `{$table}` WHERE {$wherePart} {$limitPart}";
    return $this->query($sqlQuery);
  }
  function eraseRecordsByIds($arrIds, $table, $pKey) {
    $ids = $this->formatIdsList($arrIds);
    $wherePart = " {$pKey} IN {$ids} ";
    return $this->eraseRecords($table, $wherePart);
  }

  function setFlag($table, $field, $value, $wherePart = '1=1', $limitToOne = false) {
    $limitPart = $limitToOne ? 'LIMIT 1' : '';
    $sqlQuery = "UPDATE {$table} SET `{$field}` = '{$value}' WHERE {$wherePart} {$limitPart}";
    return $this->query($sqlQuery);
  }

  function selectItems($table, $pKey, $arrIds, $fieldset = array(), $singleItem = false) {
    $fieldset = !count($fieldset) ? '*' : $this->formatFieldSet($fieldset);
    $limitPart = $singleItem ? "LIMIT 1" : '';
    $ids = $this->formatIdsList($arrIds);
    $sqlQuery = "SELECT {$fieldset} FROM `{$table}` WHERE `{$pKey}` IN {$ids} {$limitPart}";    
    return $this->selectMultiple($sqlQuery, $pKey);
  }
  
  function getSingleItem($table, $pKey, $id, $fieldset = array()) {    
    return $this->selectItems($table, $pKey, $id, $fieldset, true);
  }
  
  function buildUpdateQueryPart(&$arrData, $excludeFields =  array()) {
    $keys = array_keys($arrData);
    if(count($excludeFields)) {
      $keys = array_diff($keys, $excludeFields);
    }
    $data = array_values($arrData);    
    $arrOut = array();
    for($i=0, $iCount = count($keys); $i<$iCount; $i++) {
      $arrOut[] = "`{$keys[$i]}` = \"{$data[$i]}\"";
    }
    return implode(',',$arrOut);    
  }

  function updateRecordSimple(&$arrData, $table, $id, $pkey) {
    $updateQueryPart = $this->buildUpdateQueryPart($arrData);
    $wherePart = " `{$pkey}` = \"{$id}\"";
    $sqlQuery = "UPDATE `{$table}` SET {$updateQueryPart} WHERE {$wherePart} LIMIT 1";
    return $this->query($sqlQuery);
  }
  

}

?>