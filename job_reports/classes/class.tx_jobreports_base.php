<?


class tx_jobreports_base extends t3lib_SCbase{
	var $objUtils;
	var $tb_jobBank ="tx_jobbankresumemgr_info";
	var $tb_jobBankMain = "tx_jobbank_list";
	var $tb_user = "fe_users";
	var $resultSet;
	var $extPath;
	var $templateArr;
	var $extKey = 'job_reports';
	var $prefixId = 'tx_jobreports_pi1';
	var $piVars;
	var $folderResumeFile;
	
	function init(){
		parent::init();
		$this->extPath = t3lib_extMgm::extPath('job_reports');
		$this->objUtils = new utils();
		$this->templateArr = array();
		$this->templateArr['main'] = $this->extPath."res/mod_main.html";
		$this->templateArr['data'] = $this->extPath."res/mod_data.html";
		$this->templateArr['title'] = $this->extPath."res/mod_title.html";
		if(t3lib_div::_POST($this->prefixId)){
			$this->piVars = t3lib_div::_POST($this->prefixId);
		}
		$this->folderResumeFile=t3lib_div::linkThisUrl(t3lib_div::getIndpEnv('TYPO3_SITE_URL'))."uploads/tx_jobbankresumemgr/";
		
	}
	
	
	function prepareResult(){
		$fields ="$this->tb_jobBank.*, $this->tb_jobBankMain.occupation,$this->tb_user.name,$this->tb_user.email";
		$where = "$this->tb_jobBank.job_id = $this->tb_jobBankMain.uid and $this->tb_jobBank.user_id = $this->tb_user.uid ";
		if($this->piVars['job_id']){
			$where .= "and $this->tb_jobBank.job_id = '".$this->piVars['job_id']."' ";
		}
		if($this->piVars['user_id']){
			$where .= "and $this->tb_jobBank.user_id = '".$this->piVars['user_id']."' ";
		}
		$where .= t3lib_BEfunc::deleteClause($this->tb_jobBank);
		$tables = "$this->tb_jobBank,$this->tb_jobBankMain,$this->tb_user";
		$order_by = "$this->tb_jobBankMain.occupation";
//		utils::_debug($GLOBALS['TYPO3_DB']->SELECTquery($fields,$tables,$where));
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tables,$where,'',$order_by);
		return $result;
	}
	
	function __render(){
		
		$template = utils::getTemplate($this->templateArr['main']);
		$templateData = utils::getTemplate($this->templateArr['data']);
		$res = $this->prepareResult();
		
		
//		utils::_debug($result);
		$data_row='';
		if($res){
			
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$newHeader=($occupation!=$row['occupation'])?$row['occupation']:'';
				$occupation = $row['occupation'];
				$bgcolor = ($bgcolor=='bgColor4-20')?'bgColor5':"bgColor4-20";
				$data_row.=$this->__renderData($templateData,$row,$bgcolor,$newHeader);
			}
		}
		$markerArray['DATA'] = $data_row;
		$content = utils::replaceMarker($template,$markerArray);
		return $content;
	}
	
	function __renderSponsor(){
		$submit = array(
			'type' =>'submit',
			'name' =>$this->prefixId.'[submit]',
			'value' =>'Generate',
		);
		$content = "Select a Post : ".$this->__getSposorList();
		$content .= "&nbsp;&nbsp;".utils::getControl('input',$submit);
		if($this->piVars['job_id']){
			$content.=$this->__render();
		}
		return $content;
	}
	
	function __renderUsers(){
		$submit = array(
			'type' =>'submit',
			'name' =>$this->prefixId.'[submit]',
			'value' =>'Generate',
		);
		$content = "Select a user : ".$this->__getUserList();
		$content .= "&nbsp;&nbsp;".utils::getControl('input',$submit);
		if($this->piVars['user_id']){
			$content.=$this->__render();
		}
		return $content;
	}
	
	
	function __getUserList(){
		$content = '';
		$fields ="distinct($this->tb_user.name),$this->tb_jobBank.user_id";
		$result = $this->getSponsorResult($fields);
		if($result){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
//				utils::_debug($row);
				$optionArr[$row['user_id']] = $row['name'];
			}
				$controlArr= array(
				'option' => $optionArr,
				'name' => $this->prefixId."[user_id]",
			
			);
			if($this->piVars['user_id']){
				$controlArr['selected'] =array($this->piVars['user_id']);
			}
			$content = utils::getControl('select',$controlArr);
		}
		return $content;
	}
	
	function __getSposorList(){
		$content = '';
		$fields ="distinct($this->tb_jobBankMain.occupation),$this->tb_jobBank.job_id";
		$result = $this->getSponsorResult($fields);
		if($result){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
//				utils::_debug($row);
				$optionArr[$row['job_id']] = $row['occupation'];
			}
				$controlArr= array(
				'option' => $optionArr,
				'name' => $this->prefixId."[job_id]",
			
			);
			if($this->piVars['job_id']){
				$controlArr['selected'] =array($this->piVars['job_id']);
			}
			$content = utils::getControl('select',$controlArr);
		}
		return $content;
	}
	
	
	function getSponsorResult($fields){
		
		$where = "$this->tb_jobBank.job_id = $this->tb_jobBankMain.uid and $this->tb_jobBank.user_id = $this->tb_user.uid ";
		$where .= t3lib_BEfunc::deleteClause($this->tb_jobBank);
		$tables = "$this->tb_jobBank,$this->tb_jobBankMain,$this->tb_user";
		$order_by = "$this->tb_jobBankMain.occupation";
//		utils::_debug($GLOBALS['TYPO3_DB']->SELECTquery($fields,$tables,$where));
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tables,$where,'',$order_by);
		return $result;
	}
	
	function __renderData($template,$dataRow,$bgColor='',$header=''){
		$content='';
		foreach ($dataRow as $key => $val ){
			if($key =='crdate'){
				if((intval($val)!=0) && ($val!=''))
				{
					$markerArray[strtoupper($key)] = 'on '.date('d-m-Y',$val);
				}else{
					$markerArray[strtoupper($key)] = '';
				}
			}else{
				$markerArray[strtoupper($key)] = $val;
			}
		}
		$headerInfo=utils::getTemplate($this->templateArr['title']);
		$markerArray['BGCOLOR'] = $bgColor;
		if($header!=''){
			$subArray['BGCOLOR']=$bgColor;
			$subArray['OCCUPATION']=$header;
			$content = utils::replaceMarker($headerInfo,$subArray);
			
		}
		$markerArray['OCCUPATION']='';
		$markerArray['DOWNLOAD']='';
		if($markerArray['RESUME_FILE']!=''){
			$markerArray['DOWNLOAD']="<a href='".$this->folderResumeFile.$markerArray['RESUME_FILE']."' target='_blank'>View</a>";
		}
//		utils::_debug($markerArray);
		$content .= utils::replaceMarker($template,$markerArray);
		return $content;
	}
	
	

}
?>