<?php

class UserDao extends DAO {
    
    function getFeUserByUid($uid, $table = 'fe_users') {
        $sql = "SELECT * FROM `{$table}` WHERE `uid` = {$uid} LIMIT 1";
        return $this->selectSimple($sql);
    }

    function addUser($arrData) {
if($_SERVER['REMOTE_ADDR'] == '124.125.174.14'){
 t3lib_div::debug($conf);
}
        return ( $this->db->exec_INSERTquery('fe_users', $arrData) )
			? $this->db->sql_insert_id()
			: false;
    }
    
    function updateUser($uid, $arrData) {
        return $this->db->exec_UPDATEquery('fe_users', "`uid` = {$uid}", $arrData);
    }
    
    function selectLastGenerated($pattern) {
        $sql = "SELECT `username` FROM `fe_users` WHERE `username` LIKE ('{$pattern}') ORDER BY `uid` DESC LIMIT 1";
        return $this->selectSimple($sql);
    }
    function getFeUserByLogin($login, $table = 'fe_users') {
        $sql = "SELECT * FROM `{$table}` WHERE `username` = \"{$login}\" LIMIT 1";
        return $this->selectSimple($sql);
    }
    function addUserRegistrationError($errors, $email = '', $uid = 0) {
        $errors = implode(",", $errors);
        $sql = "INSERT INTO `tx_memberaccess_registrationerrors` ( `userid`, `errortime`, `email`, `errors` )
		VALUES (\"$uid\", NOW(), \"$email\", \"$errors\" )";

        return $this->query($sql);
    }
    function getFeUserByEmail($email, $table = 'fe_users') {
        $sql = "SELECT *
			FROM `{$table}`
			WHERE `email` = \"{$email}\"
				AND ( usergroup REGEXP \"[[:<:]]1[[:>:]]\"
					OR usergroup REGEXP \"[[:<:]]2[[:>:]]\"
					OR usergroup REGEXP \"[[:<:]]4[[:>:]]\"
				)
				AND disable = 0
				AND deleted = 0
			ORDER BY tstamp ASC
			LIMIT 1";
        return $this->selectSimple($sql);
    }
}

?>