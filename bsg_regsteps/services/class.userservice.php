<?php


class UserService extends Service {
    var $_dao;
    function UserService() {
        $this->_dao = $this->getDAO("UserDao");
    }
    function isFeUserLogged() {
        return $GLOBALS['TSFE']->fe_user->user !== false;
    }
    function getCurrentFeUserRecord() {
        if(!$this->isFeUserLogged()) {
            return false;
        }
        return $GLOBALS['TSFE']->fe_user->user;
    }
    function getCurrentFeUserId() {
        if(!$this->isFeUserLogged()) {
            return false;
        }
        return intval($GLOBALS['TSFE']->fe_user->user['uid']);
    }

    function getCurrentFeUserGroups() {
        if(!$this->isFeUserLogged()) {
            return false;
        }
        return $GLOBALS['TSFE']->fe_user->groupData;
    }

    function getCurrentFeUserGroupIds() {
        if(!$this->isFeUserLogged()) {
            return false;
        }
        $arrData = $GLOBALS['TSFE']->fe_user->groupData["uid"];
        if(is_array($arrData)) {
            array_map("intval", $arrData);
        }
        return $arrData;
    }

    function getFeUserByLogin($login, $table = 'fe_users') {
        return $this->_dao->getFeUserByLogin($login, $table);
    }
    function getFeUserByUid($uid, $table = 'fe_users') {
        return $this->_dao->getFeUserByUid($uid, $table);
    }
    function getFeUserByEmail($email, $table = 'fe_users') {
        return $this->_dao->getFeUserByEmail($email, $table);
    }
    
    function getProfessionalGuestGroupId() {
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_srfeuserregister_pi1."]["professionalGuestGroup"]);
    }
    function getMemberGroupId() {
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_srfeuserregister_pi1."]["complimentaryGroup"]);
    }
    function getProfessionalGroupId() {
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_srfeuserregister_pi1."]["professionalGroup"]);
    }
    function getCorporateGroupId() {
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_srfeuserregister_pi1."]["corporateGroup"]);
    }
    function getUserStoragePid() {
        // plugin.tx_newloginbox_pi1.pid
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_bsgregsteps_pi1."]['usersStoragePid']);        
    }
    function getUserRegistrantPid() {
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_bsgregsteps_pi1."]['registrantPid']);        
    }
    function getMemberPid() {
        return intval($GLOBALS['TSFE']->tmpl->setup["plugin."]["tx_bsgregsteps_pi1."]['memberPid']);        
    }
    
    function isCurrentFeUserProfessionalMember() {
        //return true;
        if(!$this->isFeUserLogged()) {
            return false;
        }
        $ug = $this->getCurrentFeUserGroups();
        return in_array($this->getProfessionalGroupId(), $ug['uid']);
    }

    function isCurrentFeUserCorporateMember() {
        //return true;
        if(!$this->isFeUserLogged()) {
            return false;
        }
        $ug = $this->getCurrentFeUserGroups();
        return in_array($this->getCorporateGroupId(), $ug['uid']);
    }

    function generatePassword() {
        $pass = $this->base64_encode($this->randomName(), true);
        return $pass;
    }  

    
    var $BinaryMap = array(          // Table heavily changed from original
    '0', '8', 'F', 'N', 'V', 'c', 'k', 's', //  7
    '1', '9', 'G', 'O', 'W', 'd', 'l', 't', // 15
    '2', '7', 'H', 'P', 'X', 'e', 'm', 'u', // 23
    '3', 'A', 'I', 'Q', 'Y', 'f', 'n', 'v', // 31
    '4', 'B', 'J', 'R', 'Z', 'g', 'o', 'w', // 39
    '5', 'C', 'K', 'S', '_', 'h', 'p', 'x', // 47
    '6', 'D', 'L', 'T', 'a', 'i', 'q', 'y', // 55
    '7', 'E', 'M', 'U', 'b', 'j', 'r', 'z'  // 63
    );

    function randomName() {
        $str = '';
        $maxVal = count($this->BinaryMap) - 1;
        for($i=0; $i<6; $i++) {
            $e = rand(0, $maxVal);
            $str .= $this->BinaryMap[$e];
        }
        return $str;
    }
    function base64_encode($input) {
        $r = '';
        $p = '';
        $c = strlen($input) % 3;
        if ($c > 0) {
            for (; $c < 3; $c++) {
                $p .= '=';
                $input .= "\0";
            }
        }
        for ($c = 0; $c < strlen($input); $c += 3) {
            $n = (ord($input[$c]) << 16) + (ord($input[$c+1]) << 8) + ord($input[$c+2]);
            $n = array(($n >> 18) & 63, ($n >> 12) & 63, ($n >> 6) & 63, $n & 63);
            $r .= $this->BinaryMap[$n[0]] .
            $this->BinaryMap[$n[1]] .
            $this->BinaryMap[$n[2]] .
            $this->BinaryMap[$n[3]];
        }
        return substr($r, 0, strlen($r) - strlen($p));
    }

    function generateUsername() {
        $lastId = $this->_dao->selectLastGenerated('cmem_%');
        if(preg_match("/_([\d]+)$/i", $lastId['username'], $matches)) {
            $id = intval($matches[1]) + 1;
        } else {
            $id = 1;
        }

        return 'cmem_'.$id;
    }
    
    function addUser($addUser) {
        return $this->_dao->addUser($addUser);
    }
    
    function updateUser($id, $arrData) {
        $id = intval($id);
        return $this->_dao->updateUser($id, $arrData);
    }
    
    function addUserErrors($errors, $email='', $uid = 0) {
        return $this->_dao->addUserRegistrationError($errors, $email, $uid);
    }

    function getPcodeByCurrentUser() {         
        if($this->isCurrentFeUserCorporateMember()) {
            return BSG_REG_CORP_PC;
        }
        if($this->isCurrentFeUserProfessionalMember()) {
            return BSG_REG_PRO_PC;
        }
        if($this->isFeUserLogged()) {
            return BSG_REG_MEM_PC;
        }        
        return BSG_REG_DEFAULT_PC;
    }
    
    function getMemberStatusByCurrentUser() {
        if($this->isCurrentFeUserCorporateMember()) {
            return 'Corporate';
        }
        if($this->isCurrentFeUserProfessionalMember()) {
            return 'Professional';
        }
        if($this->isFeUserLogged()) {
            return 'Member';
        }
        return 'Non-member';
    }

}