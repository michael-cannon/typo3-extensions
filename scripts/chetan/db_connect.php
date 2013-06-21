<?PHP
require_once("config.php");
class db_connect
{
	var $dbhost = MYSQL_HOST;
	var $dbname = MYSQL_DATABASE;
	var $dbusername = MYSQL_USER;
	var $dbpassword = MYSQL_PASSWORD;

	function connect($db = "")
	{
		//If DB Not null
		if(!empty($db)) $this->dbname= $db;

		if(!($link=mysql_pconnect($this->dbhost, $this->dbusername, $this->dbpassword)))
		{
			$this->error_msg($message);
			return false;
		}

		if(!mysql_select_db($this->dbname))
		{
			$this->error_msg($message);
			return false;
		}
		return $link;
	}
}
?>
