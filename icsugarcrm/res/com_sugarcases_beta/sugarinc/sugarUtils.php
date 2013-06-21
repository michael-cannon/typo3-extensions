<?php
/**
 *	Misc utils to help process Sugar data
 *
 *	@author Jason Eggers (jason.eggers@infinitecampus.com)
 */

function nameValuePairToSimpleArray($sets) {
	$newarray = array();
    foreach ( $sets as $set )
    {
        $newarray[$set['name']] = $set['value'];
    }
    
    return $newarray;
}

function debug_print_r($data)
{
    ob_start();
    print_r($data);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

?>