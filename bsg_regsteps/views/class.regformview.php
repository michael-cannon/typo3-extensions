<?php

class RegformView extends bsg_regsteps_view {
    function userRecordToForm($data) {
        $arrSubstitutes = array(
        'name' => 'name',
        'address' => 'address',
        'telephone' => 'phone',
        'fax' => 'fax',
        'email'  => 'pmail',
        'bmail' => 'bmail',
        'zip' => 'zip',
        'city' => 'city',
        'country' => 'country',
        'username' => 'username',
        'password' => 'password',
        'password1' => 'password1',
        'expyear' => 'expyear',
        'expmonth' => 'expmonth',
        'zone' => 'state',
        'department'=>'department',
        'title' => 'title',
        'company' => 'company',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'starttime' => 'starttime',
        'endtime' => 'endtime',
        );       
        
        $arrOut = array();
        foreach ($arrSubstitutes as $dbRecord => $arrRecords) {
            if(isset($data[$dbRecord])) {
                if(is_array($arrRecords)) {
                    foreach ($arrRecords as $field) {
                        $arrOut[$field] = $data[$dbRecord];
                    }
                } else {
                    $arrOut[$arrRecords] = $data[$dbRecord];
                }
            }
        }
        return $arrOut;
    }
}

?>