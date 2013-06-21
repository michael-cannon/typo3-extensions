<?
require_once('class.tx_emailscheduler_utils.php');
class tx_emailscheduler_base_module extends t3lib_SCbase {
	var $tb_scheduler = "tx_emailscheduler_main";
	var $tb_scheduler_mm = "tx_emailscheduler_main_tt_news_mm";
	var $tb_user = "fe_users";
	var $tb_news = "tt_news";
	var $tb_package = "tx_sponsorcontentscheduler_package";
	var $objUtils;
	var $templateFile;
	var $extKey = "tx_emailscheduler";
	var $piVars;
	
	function init(){
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
		$this->objUtils = new tx_emailscheduler_utils();
		$this->templateFile = array();
		$this->templateFile['mainHeader'] = t3lib_extMgm::extPath('emailscheduler')."res/modheader.html";
		$this->templateFile['detail'] = t3lib_extMgm::extPath('emailscheduler')."res/moddetail.html";
		$this->templateFile['error'] = t3lib_extMgm::extPath('emailscheduler')."res/errormod.html";
		$this->templateFile['stat'] = t3lib_extMgm::extPath('emailscheduler')."res/stat1.html";
		
		$this->templateFile['statdetails'] = t3lib_extMgm::extPath('emailscheduler')."res/stat_details.html";
		if(t3lib_div::_POST($this->extKey)){
			$this->piVars = t3lib_div::_POST($this->extKey);
		}
//		tx_emailscheduler_utils::_debug($this->piVars);
	}
	
	function __getHeaderInfo()
	{
		$template = $this->objUtils->getTemplate($this->templateFile['mainHeader']);
		$confHeader = $this->__getHeaderControls();
//		tx_emailscheduler_utils::_debug($confHeader);
		foreach($confHeader as $markerName=>$markerValue){
			$markerArray[$markerName]=tx_emailscheduler_utils::getControl('input',$markerValue);
		}
		$markerArray['DETAILS'] = $this->__getDetails();
		$content = tx_emailscheduler_utils::replaceMarker($template,$markerArray);
		
		return $content;
		
	}
	
	function __getHeaderControls(){
		$returnField = array(
			'TITLE' =>
				array(
					'type' => 'text',
					'size' => '30',
					'name' => 'tx_emailscheduler[title]',
				),
			'DURATION' =>
				array(
					'type' => 'text',
					'size' => '2',
					'name' => 'tx_emailscheduler[duration]',
				),
			'EMAILCONTENT' =>
				array(
					'type' => 'file',
					'name' => 'tx_emailscheduler[emailcontent]',
				),
			'SUBMIT' =>
				array(
					'type' => 'submit',
					'name' => 'tx_emailscheduler[submit]',
					'value' => 'Submit',
				),
		);
		return $returnField;
	}
	
	function __getDetails(){
		$detailContent =  tx_emailscheduler_utils::getTemplate($this->templateFile['detail']);
		$fields = "uid,title,duration,emailcontent";
		$where = "1=1 ";
		$where .= t3lib_BEfunc::deleteClause($this->tb_scheduler);
		$dataRow='';
//		tx_emailscheduler_utils::_debug($GLOBALS['TYPO3_DB']->SELECTquery($fields,$this->tb_scheduler,$where));
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$this->tb_scheduler,$where);
		if($result){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$markerArray = array();
				foreach($row as $key=>$val){
					$markerArray[strtoupper($key)] =  $val;
				}
				$dataRow.= tx_emailscheduler_utils::replaceMarker($detailContent,$markerArray);
			}
		}
		if($dataRow==''){
			$content = tx_emailscheduler_utils::getTemplate($this->templateFile['error']);
		}else{
			$content = $dataRow;
		}
		return $content;
	}
	
	function __insert(){
		foreach ($this->piVars as $key=>$val){
			if($key!='submit') $fields[$key] = $val;
		}
		$fields['emailcontent'] = $_FILES[$this->extKey][name][emailcontent];
		$destinationFolder = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')."/uploads/$this->extKey/".$_FILES[$this->extKey][name][emailcontent];

		move_uploaded_file($_FILES[$this->extKey][tmp_name][emailcontent], $destinationFolder);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tb_scheduler,$fields);
//		tx_emailscheduler_utils::_debug($destinationFolder);
//		tx_emailscheduler_utils::_debug($_FILES[$this->extKey][name][emailcontent]);
//		tx_emailscheduler_utils::_debug($_FILES);
	}
	
	
	function __reports(){
			return $this->__getReportsDropdown();
	}
	
	function __getReportsDropdown(){
		$controlArr = array(
			'name' => $this->extKey."[reportMode]",
			'option' => array(
					'1'=>'Show All',
					'2'=>'User Specific',
					'3'=>'Date Range',
				),
			'selected' => array($this->piVars['reportMode']),
		);
		$controlArrSubmit = array(
			'name' => $this->extKey."[submit]",
			'type' => 'submit',
			'value' => 'Generate'
		);
		return tx_emailscheduler_utils::getControl('select',$controlArr).' '. tx_emailscheduler_utils::getControl('input',$controlArrSubmit);
	}
	
	
	function generateStats($__key){
		return $this->__generate($__key);
		
		
	}
	
	function __generate($__key){
		$fields = "$this->tb_scheduler_mm.*,$this->tb_news.title as news_title, $this->tb_user.name as name,$this->tb_scheduler.title,$this->tb_package.title as package_name,$this->tb_user.email as email";
		$tables = "$this->tb_scheduler_mm, $this->tb_news,$this->tb_user,$this->tb_scheduler,$this->tb_package";
		$where = "$this->tb_scheduler_mm.uid_local = $this->tb_scheduler.uid and $this->tb_scheduler_mm.uid_foreign=$this->tb_news.uid";
		$where.=" and $this->tb_news.tx_sponsorcontentscheduler_package_id=$this->tb_package.uid and $this->tb_package.fe_uid=$this->tb_user.uid";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tables,$where);
		$dataRow='';
		$dataFile = tx_emailscheduler_utils::getTemplate($this->templateFile['statdetails']);
		if($result){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$markerArray['DESCRIPTION'] = "Mail Sent to ".$row['name']. "for <b>".$row['package_name']."</b> on ".date('d-m-Y',$row['tstamp']);
				$markerArray['EMAILID']=$row['email'];
				$dataRow.=tx_emailscheduler_utils::replaceMarker($dataFile,$markerArray);
			}
		}
		$mainMarker['DATAROW']=$dataRow;
		$mainContent = tx_emailscheduler_utils::getTemplate($this->templateFile['stat']);
		$content = tx_emailscheduler_utils::replaceMarker($mainContent,$mainMarker);
		return $content;
	}
	
}
?>