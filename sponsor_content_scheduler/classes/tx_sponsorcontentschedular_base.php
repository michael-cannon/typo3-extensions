<?php

class tx_sponsorcontentscheduler_base{

	/**
	 * tx_sponsorcontentscheduler_base::isLoggedInUserBelongsToSalesGroup()
	 * 
	 * checks if logged in user belongs to Sales Group
	 * @param $loggedInUserGroup
	 * @param $salesGroupId
	 * @return boolean
	 **/
	function isLoggedInUserBelongsToSalesGroup($loggedInUserGroup, $salesGroupId){
	
		//Get the logged in user groupid in an array
		$_loggedInUserGroup_array = explode(',', $loggedInUserGroup);
		
		if (in_array($salesGroupId, $_loggedInUserGroup_array)) {
		    return TRUE;
		}else{
			return FALSE;
		}
	
	}
	


}


?>