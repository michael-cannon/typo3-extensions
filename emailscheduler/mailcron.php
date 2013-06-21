<?
//error_reporting (E_ALL);
include_once("/home/dev1/www/bpminstitute/typo3conf/localconf.php");
include_once("/home/dev1/www/bpminstitute/typo3conf/ext/emailscheduler/classes/class.phpmailer.php");
define('USERNAME',$typo_db_username);
define('PASSWORD',$typo_db_password);
define('HOST',$typo_db_host);
define('DBNAME',$typo_db);


class emailscheduler{
	
	var $dbConf;
	var $db = array();
	var $tb_user = "fe_users";
	var $tb_news = "tt_news";
	var $tb_featuredWeeks = "featured_weeks";
	var $tb_featuredWeeksmm = "featured_weeks_mm";
	var $tb_package = "tx_sponsorcontentscheduler_package";
	var $tb_emailScheduler = "tx_emailscheduler_main";
	var $templateFolder;
	var $tb_mm ="tx_emailscheduler_main_tt_news_mm";
	var $emailFrom=array();
	
	function init(){
		$this->dbConf = array(
				'username'  => USERNAME,
				'password'  => PASSWORD,
				'host'      => HOST,
				'db'        => DBNAME,
			);
		$this->db['conn'] = mysql_connect($this->dbConf['host'],$this->dbConf['username'],$this->dbConf['password']) or die('Cannon conect');
		$this->db['res'] = mysql_select_db($this->dbConf['db'],$this->db['conn']);
		$this->templateFolder = "/home/dev1/www/bpminstitute/uploads/tx_emailscheduler/";
		$this->emailFrom['email'] = "renewals@bpminstitute.org";
		$this->emailFrom['name'] = "Reminder";
		
	}
	
	function emailscheduler(){
		$this->init();
		
	}
	
	
	function checkRecords(){
		$recorder = $this->getNews();
		$scheduler = $this->getScheduler();
//		$this->_debug($recorder);
		$emails =array();
		
		foreach($recorder as $newsid=>$newsArr){
			if(is_array($this->checkSchedules($newsArr,$scheduler))){
				$emails[] = $this->checkSchedules($newsArr,$scheduler);
				
			}
		}
		$mailSchedule = $this->getKeysMails($emails);
//		$this->_debug($mailSchedule);
		$ttNewsKeys = array_keys($mailSchedule);
		foreach($ttNewsKeys as $valKey){
			$mailerIDKey = array_keys($mailSchedule[$valKey]);
			foreach($mailerIDKey as $mailID){
				$where = "uid_local = '$mailID' and uid_foreign = '$valKey'";
				if($this->__isMailSent($where)){
					$markerArray['USERSINFO'] = $recorder[$valKey]['name'];
					$markerArray['DATE_DUE'] = $mailSchedule[$valKey][$mailID]['last_date'];
					$markerArray['PACKAGE_NAME'] = $recorder[$valKey]['package_name'];
					$templateFileName = $this->templateFolder.$mailSchedule[$valKey][$mailID]['file'];
//					$this->_debug($templateFileName);
					$fileSource = $this->getTemplate($templateFileName);
					$msgBody = $this->replaceMarker($fileSource,$markerArray);
					$conf['from']['email'] = $this->emailFrom['email'];
					$conf['from']['name'] = $this->emailFrom['name'];
					$conf['to']['email'] = $recorder[$valKey]['email'];
					$conf['to']['name'] = $recorder[$valKey]['name'];
					$conf['mail']['subject'] = "Reminder for content";
					$conf['mail']['body'] = $msgBody;
//					echo "sending Mail To ".$recorder[$valKey]['email']." for ".$scheduler[$mailID]['title']."<br/>";
					if($this->SendEmailNotification($conf)){
						$fields =  array(
							'tstamp' => time(),
							'uid_local' => $mailID,
							'uid_foreign' => $valKey
							
						);
						$this->exec_INSERTquery($this->tb_mm,$fields);
						echo "Mail Sent to ".$recorder[$valKey]['email']."<br/><br/>";
					}
					
				}
			}
		}
	}
	
	function __isMailSent($where){
		$field = "count(*) as total";
		$result = $this->exec_SELECTquery($field,$this->tb_mm,$where);
		if($result){
			$row = $this->sql_fetch_assoc($result);
		
		}
		if(intval($row['total']) > 0){
			return false;
		}else{
			return true;
		}
		
	}
	
	function getKeysMails($arrEmails){
		$returnKey=array();
		foreach($arrEmails as $valEmail){
			$__key = array_keys($valEmail);
//			$this->_debug($__key[0]);
			$returnKey[$__key[0]]=$valEmail[$__key[0]];
		}
		return $returnKey;
	}
	
	function getNews(){
		$returnRow = array();
		$fields = "$this->tb_news.*, (TO_DAYS(NOW()) - TO_DAYS(FROM_UNIXTIME( tx_sponsorcontentscheduler_news_due_date) )) as days_elapsed, $this->tb_package.title as package_name,$this->tb_user.name,$this->tb_user.email";
		
		$where = "$this->tb_news.title='[No Title]' ";
		$where .= "and $this->tb_news.tx_sponsorcontentscheduler_package_id = $this->tb_package.uid ";
		$where .= "and $this->tb_package.fe_uid = $this->tb_user.uid ";

		$where.=" and $this->tb_news.deleted = 0";
		
		$tables = "$this->tb_news,$this->tb_package,$this->tb_user";

		$result = $this->exec_SELECTquery($fields,$tables,$where);
		
		if($result){
			while($row = $this->sql_fetch_assoc($result)){
				$returnRow[$row['uid']]=$row;
			}
		}
		return $returnRow;
	}
	
	function checkSchedules($news_id,$scheduler){
		$returnVal = array();
		foreach($scheduler as $schdArr){
			if($news_id['days_elapsed']>=$schdArr['duration'] ){
				$returnVal[$news_id['uid']][$schdArr['uid']]['mailer_uid'] = $schdArr['uid'];
				$returnVal[$news_id['uid']][$schdArr['uid']]['file'] =$schdArr['emailcontent'] ;
				$returnVal[$news_id['uid']][$schdArr['uid']]['name'] =$news_id['name'] ;
				$returnVal[$news_id['uid']][$schdArr['uid']]['email'] =$news_id['email'] ;
				$returnVal[$news_id['uid']][$schdArr['uid']]['last_date'] =date('d-m-Y',$news_id['tx_sponsorcontentscheduler_news_due_date']) ;
			}
		}
		if(count($returnVal)<1){
			$returnVal = '';
		}
		return $returnVal;
	}
	
	function getScheduler(){
		$returnScheduler = array();
		$fields = "*";
		$where = "1=1 ";
		$where .= "AND $this->tb_emailScheduler.deleted=0";
		$result = $this->exec_SELECTquery($fields,$this->tb_emailScheduler,$where);
		if($result){
			while($row = $this->sql_fetch_assoc($result)){
				$returnScheduler[$row['uid']]=$row;
			}
		}
		return $returnScheduler;
	}
	
	function _debug($var){
		echo "<pre>";
		var_dump($var);
		echo "</pre>";
	}
	
	function SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function
			// Build basic query:
		$query = 'SELECT '.$select_fields.'
			FROM '.$from_table.
			(strlen($where_clause)>0 ? '
			WHERE
				'.$where_clause : '');

			// Group by:
		if (strlen($groupBy)>0)	{
			$query.= '
			GROUP BY '.$groupBy;
		}
			// Order by:
		if (strlen($orderBy)>0)	{
			$query.= '
			ORDER BY '.$orderBy;
		}
			// Group by:
		if (strlen($limit)>0)	{
			$query.= '
			LIMIT '.$limit;
		}
		
			// Return query:
		
		return $query;
	}
	
	function exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')	{
		$res = mysql_query($this->SELECTquery($select_fields,$from_table,$where_clause,$groupBy,$orderBy,$limit), $this->db['conn']);
//		if ($this->debugOutput)	$this->debug('exec_SELECTquery');
		return $res;
	}
	
	function sql_fetch_assoc($res)	{
		return mysql_fetch_assoc($res);
	}
	
	/**
	 * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 * Using this function specifically allows us to handle BLOB and CLOB fields depending on DB
	 * Usage count/core: 47
	 *
	 * @param	string		Table name
	 * @param	array		Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	function exec_INSERTquery($table,$fields_values)	{
		$res = mysql_query($this->INSERTquery($table,$fields_values), $this->db['conn']);
//		if ($this->debugOutput)	$this->debug('exec_INSERTquery');
		return $res;
	}
	
	
	/**************************************
	 *
	 * Query building
	 *
	 **************************************/

	/**
	 * Creates an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 * Usage count/core: 4
	 *
	 * @param	string		See exec_INSERTquery()
	 * @param	array		See exec_INSERTquery()
	 * @return	string		Full SQL query for INSERT (unless $fields_values does not contain any elements in which case it will be false)
	 * @depreciated			use exec_INSERTquery() instead if possible!
	 */
	function INSERTquery($table,$fields_values)	{

			// Table and fieldnames should be "SQL-injection-safe" when supplied to this function (contrary to values in the arrays which may be insecure).
		if (is_array($fields_values) && count($fields_values))	{

				// Add slashes old-school:
			foreach($fields_values as $k => $v)	{
				$fields_values[$k] = $this->fullQuoteStr($fields_values[$k], $table);
			}

				// Build query:
			$query = 'INSERT INTO '.$table.'
				(
					'.implode(',
					',array_keys($fields_values)).'
				) VALUES (
					'.implode(',
					',$fields_values).'
				)';

				// Return query:
			if ($this->debugOutput || $this->store_lastBuiltQuery) $this->debug_lastBuiltQuery = $query;
			return $query;
		}
	}
	
	
	
	
	/**************************************
	 *
	 * Various helper functions
	 *
	 * Functions recommended to be used for
	 * - escaping values,
	 * - cleaning lists of values,
	 * - stripping of excess ORDER BY/GROUP BY keywords
	 *
	 **************************************/

	/**
	 * Escaping and quoting values for SQL statements.
	 * Usage count/core: 100
	 *
	 * @param	string		Input string
	 * @param	string		Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
	 * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 * @see quoteStr()
	 */
	function fullQuoteStr($str, $table)	{
		return '\''.addslashes($str).'\'';
	}
	
	
	
	/**
	 * Get Template Content
	 *
	 * @param string $templateFileName Template File Name
	 * @return string 
	 */
	function getTemplate($templateFileName){
		$template_path = $templateFileName;
		if( file_exists( $template_path)) {
			if( $fp = fopen( $template_path, 'r')) {
				$content = trim(fread( $fp, filesize( $template_path)));
				fclose($fp);
			}
		}
		return $content;
	}

	/**
	 * Replace the Markers in the template
	 *
	 * @param string $template Template File Name
	 * @param array $markerArray Array of markers to be replaced
	 * @return string
	 */
	function replaceMarker($template,$markerArray){
		foreach ( $markerArray as $key => $value) {
              $template = str_replace( '###'.$key.'###', $value, $template);
          }
		  return $template;
	}
	
	
	function SendEmailNotification($conf)
	{
		//$userName=$this->userData['first_name'].' '.$this->userData['last_name'];
//		$this->_debug($conf);
		$mail = new PHPMailer();
		$mail->Mailer = "sendmail";
//		$mail->SMTPDebug = true;
		$mail->From = $conf['from']['email'];
		
		$mail->FromName = $conf['from']['name'];;
		
		$mail->AddAddress($conf['to']['email'],$conf['to']['name']);  
		
		$mail->AddReplyTo($conf['from']['email'], $conf['from']['name']);  
			
		$mail->IsHTML(true);       // set email format to HTML
		
		$mail->Subject = $conf['mail']['subject'];
		$mail->Body    = $conf['mail']['body'];

		if(!$mail->Send())
		{
		   return false;
		}
		return true;
	}
	
}

$emailer =  new emailscheduler();
$emailer->checkRecords();
?>