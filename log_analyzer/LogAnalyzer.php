<?php
ini_set("max_execution_time",6000);
require_once("config.php");
require_once('Render.php');
/**
 * Class for analyzing log tables
 *
 */
class LogAnalyzer {
   var $dates;						// keeping dates for queries
   var $author;						// who made changes for sys_log query
   var $author_log;						// who made changes for sys_history query
   var $type;								// type of change: content, page or news [..]
   var $page_uid = PAGE_UID;		// uid of page where FE difference viewer plugin is inserted
   var $history;						// history of all changies made by author whithin dates range
   
   var $r_type;
   var $mode;						// mode of rendering content
   var $modes = array(
								'be'		=> 1,
								'fe'		=> 2,
								'xls'		=> 3,
								'xls_cust' => 4,
								'xls_mail' => 5,
								);
   var $render;						// object of class Render
   var $rows_in_select = PAGE_ROWS; // how many rows in a page of query
   var $page=0;						// current page of query
   var $max_page;						// max page of query
   
   /**
    * setting mode
    *
    * @param int $mode
    */
	function setMode($mode) {
		if (false === array_search($mode,$this->modes)) {
			$this->mode = 1;
		} else {
			$this->mode = $mode;
		}
	}

   /**
    * initializing and return worked string
    *
    * @return string
    */
	function init($r_type="") {
		if (isset($_REQUEST['page']) && !empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
			$this->page = $_REQUEST['page'];

		if ($this->page < 1)
			$this->page = 0;

		global $report_type;
		$this->author = "";
		$this->author_log = "";
		// MLC 20090102 all none user events
		// 4 User changed workspace
		// 254 Personal settings changed
		// 255 login event
		$this->type = " and sys_log.type NOT IN (4,254,255)";

		// MLC 20100914 don't report errors
		$this->type .= " and sys_log.error = 0";

		// MLC 20110512 ignore cron based changes
		if ( IGNORE_CRON_CHANGES ) {
			$this->type .= " and sys_log.IP NOT LIKE ''";
		}

		if ($r_type == "" && !array_key_exists($r_type,$report_type)) {
			$r_type = 1; // default for be
		} else
			$r_type = strtr($r_type,$report_type);

		$this->r_type = $r_type;

		if ($r_type == 2) { // daily xls report
			unset($_POST['from_date']);
			unset($_POST['to_date']);
		}

		if (!isset($this->mode))
			$this->setMode();

		if (isset($_POST['author']) && !empty($_POST['author']) && $_POST['author'][0] != '0') {
			$this->author = " and be_users.uid IN (".implode(",",$_POST['author']).") ";
			$this->author_log = " and userid IN (".implode(",",$_POST['author']).") ";
		}

		if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'][0] != '0') {
			for ($i = 0, $n = count($_POST['type']); $i < $n ;$i++) {
				$post['type'][$i] = "'".$_POST['type'][$i]."'";
			}
			$this->type = " and sys_log.tablename IN (".implode(",",$post['type']).") ";
		}

		$this->dates = $this->getDates();
		$this->getHistoryData();
		$this->render = new Render();

		switch ($this->mode) {
			case '1':
			case '5':
				if ($this->r_type == 2 || $this->r_type == 3 || $this->r_type == 4) {
					$this->getXLSDifference();
				}
				$content .= $this->BEPlugin();
				break;

			case '2':
				$content = $this->FEPlugin();
				break;
			default:
				break;
		}

		return $content;
	}
   
   /**
    * rendering content for FE plugin
    *
    * @return string
    */
   function FEPlugin() {
		return $this->getFEDifference();
   }
   
   /**
    * rendering content for BE plugin
    *
    * @return string
    */
   function BEPlugin() {
		$users = $this->getBEusers();
		$login_counts = $this->usersLogins($this->dates);
		$change = $this->changesMade($this->dates);
		global $tables_names;
		$str = implode(",",array_keys($tables_names));
		$str = preg_replace("/(\w+),?/","'\\1',",$str);
		$str = substr($str,0,-1);
		$tables = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('distinct tablename',
																'sys_history',
																'tstamp >= cast(unix_timestamp(\''.$this->dates['from_stamp'].'\') as signed)
																and
																tstamp <= cast(unix_timestamp(\''.$this->dates['to_stamp'].'\') as signed)
																and
																tablename not in ('.$str.')',
																'','','');
		while (true == ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
		$tables[] = $r['tablename'];
		}
		$content = $this->render->usersTable($login_counts,$change,$this->dates,$users,$tables);
		$content .= $this->render->pages($this->page,$this->max_page);
		$content .= $this->getBEDifference();
		$content .= $this->render->pages($this->page,$this->max_page);
		return $content;
   }
   
   /**
    * getting BE users
    * 
    * @return array
    */
   function getBEusers() {
		$users = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username, realName','be_users','','','','');
		while(($res) && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$users[$row['uid']] = (empty($row['realName']) ? $row['username'] : $row['realName']);
		}
		asort($users);
		return $users;
   }
   
   /**
    * getting dates for queries
    *
    * @return array
    */
   function getDates() {
		global $report_period;
		$dates['from'] = date("m-d-Y",time()-$report_period);
		if (isset($_POST['from_date']) && !empty($_POST['from_date']))
		$dates['from'] = $_POST['from_date'];
		$dates['from_stamp'] = get_date_stamp($dates['from'],false);
		$dates['to'] = date("m-d-Y");
		if (isset($_POST['to_date']) && !empty($_POST['to_date']))
		$dates['to'] = $_POST['to_date'];
		$dates['to_stamp'] = get_date_stamp($dates['to'],true);
		return $dates;
   }
   
   /**
    * getting count of logins of users
    *
    * @return int
    */
   function usersLogins() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(tstamp) as cnt',
			'sys_log',
			'type = 255
			and
			action = 1
			and
			tstamp >= cast(unix_timestamp(\''.$this->dates['from_stamp'].'\') as signed)
			and
			tstamp <= cast(unix_timestamp(\''.$this->dates['to_stamp'].'\') as signed)'
			.$this->author_log,
			'',
			'',
			'');
	if ($res)
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	if ($row)
			$login_counts = $row['cnt'];
	else
		$login_counts = 0;
	return $login_counts;
   }
   
   /**
    * getting array of count authors of changes by users
    *
    * @return array
    */
   function changesMade() {
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('be_users.username as names,
		be_users.realName as realname',
		'sys_log,
		be_users',
		'(sys_log.tstamp >= 
		cast(unix_timestamp(\''.$this->dates['from_stamp'].'\')
		as signed)
		and
		sys_log.tstamp <=
		cast(unix_timestamp(\''.$this->dates['to_stamp'].'\')
		as signed))
		and
			sys_log.userid=be_users.uid
		'.$this->author.$this->type,
		'',
		'be_users.realName asc,be_users.username asc',
		'');
	$changes = array();
	while($res && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		$name = (isset($row['realname']) && !empty($row['realname'])) ? $row['realname'] : $row['names'];
		$changes[$name]++;
		$changes['counts']++;
	}
	ksort($changes);
	return $changes;
   }
   
   /**
    * getting table of changes made for BE plugin
    *
    * @return string
    */
   function getBEDifference() {
		return $this->render->BEDifferenceTable($this->history,$this->page_uid);
   }
   
   /**
    * getting table of changes made for FE plugin
    *
    * @return string
    */
    function getFEDifference() {
		return $this->render->FEDifferenceTable($this->history);
   }
   
   /**
    * getting table of changes made for XLS
    *
    * @return string
    */
   function getXLSDifference() {
		$login_counts = $this->usersLogins($this->dates);
		$change = $this->changesMade($this->dates);
		$this->render->XLSDifferenceTable($this->history,$this->page_uid,$login_counts,$change,$this->r_type);
   }
   
   /**
    * getting history data of changes
    *
    */
	function getHistoryData() {
		// array of accordance of table names in DB and human readable names
		global $tables_names;
		global $cron_tables;
		$cron_tables_str = "";
		if ($this->mode == 5 && 0 < count($cron_tables) && $cron_tables[0] != 'all') {
			foreach ($cron_tables as $table) {
				$cron_tables_str .= "'".$table."',";
			}

			$cron_tables_str = substr($cron_tables_str,0,-1);
			$cron_tables_str = " and tablename not in (".$cron_tables_str.")";
		}
		$limit = "";
		if ($this->r_type == 1) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*) as total_rows', 'sys_log,be_users', '(sys_log.tstamp >= cast(unix_timestamp(\''.$this->dates['from_stamp'].'\') as signed) and sys_log.tstamp <= cast(unix_timestamp(\''.$this->dates['to_stamp'].'\') as signed)) and sys_log.userid=be_users.uid' .$this->author.$this->type .$cron_tables_str, '','','');

			while (($res) && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$total_rows = $row['total_rows'];
			}
			$this->max_page = ($total_rows > $this->rows_in_select) ? intval($total_rows / $this->rows_in_select) : 1;
			if ($this->max_page < 1)
				$this->max_page = 0;

			if ($total_rows) {
				if ($this->page * $this->rows_in_select > $total_rows)
					$this->page = 0;

				$limit = $this->page * $this->rows_in_select.",".$this->rows_in_select;
			}
		} else
			$limit = XLS_ROWS;

		// query to get history data from tables
		if ($this->mode != 2) { // not fe
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_log.uid, sys_log.recuid, sys_log.tstamp, sys_log.tablename, sys_log.log_data, sys_log.details as details, be_users.email, be_users.username as names, be_users.realName as realname', 'sys_log, be_users', '(sys_log.tstamp >= cast(unix_timestamp(\''.$this->dates['from_stamp'].'\') as signed) and sys_log.tstamp <= cast(unix_timestamp(\''.$this->dates['to_stamp'].'\') as signed)) and sys_log.userid=be_users.uid '.  $this->author.$this->type.$cron_tables_str, '', 'sys_log.tstamp desc', $limit);
		} else {
			if (isset($_REQUEST['uid']) && !empty($_REQUEST['uid']))
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_history.uid, sys_history.recuid, sys_history.fieldlist, sys_history.tstamp, be_users.email, be_users.username as names, be_users.realName as realname, sys_history.tablename, sys_history.history_data as data', 'sys_history, sys_log, be_users', 'sys_history.sys_log_uid=sys_log.uid and sys_log.userid=be_users.uid and sys_history.uid='.$_GET['uid'],'','','1');
			else
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_log.uid, sys_log.recuid, sys_log.log_data as data, sys_log.details as details, sys_log.tstamp, be_users.email, be_users.username as names, be_users.realName as realname, sys_log.tablename ', 'sys_log, be_users', 'sys_log.userid=be_users.uid and sys_log.uid='.$_GET['log_uid'],'','','1');
		}

		$i = 0; // array iterator
		// getting associating array from query
		while (($res) && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$temp = array();
			if ($this->mode != 2) {
				$res_history = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_history.uid, sys_history.fieldlist'
				, 'sys_history,sys_log'
				, 'sys_history.sys_log_uid=sys_log.uid and sys_log.uid='.$row['uid']
				, 'sys_history.tstamp desc'
				, ''
				,'1'
				);

				if ($res_history && $row_history = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_history)) {
					$temp['uid'] = $row_history['uid'];
					$temp['fieldlist'] = $row_history['fieldlist'];
				}

				$this->history[$i]['id'] = $temp['uid'];
				$this->history[$i]['field'] = str_replace(",","<br />",$temp['fieldlist']);
			} else {
				$this->history[$i]['id'] = $row['uid'];
				$this->history[$i]['field'] = str_replace(",","<br />",$row['fieldlist']);
			}

			$this->history[$i]['details'] = $row['details'];
			$this->history[$i]['log_uid'] = $row['uid'];
			$this->history[$i]['log_data'] = $row['log_data'];
			$name = (isset($row['realname']) ? $row['realname'] : $row['names']);
			$this->history[$i]['time'] = date("m-d-y H:i",$row['tstamp']);
			$this->history[$i]['age'] = time() - $row['tstamp'];
			$this->history[$i]['user'] = $name;
			$this->history[$i]['email'] = $row['email'];
			$this->history[$i]['type'] = strtr($row['tablename'],$tables_names);
			$this->history[$i]['uid'] = $row['recuid'];
			$this->history[$i]['data'] = $row['data'];

// t3lib_div::debug( __LINE__ );
// t3lib_div::debug( $row );
// t3lib_div::debug( $this->history[$i] );

			// to get uid and title of page where change took place
			// different queries
			global $tables_fields;

			if ($row['tablename'] == "tt_content") {
				$query['string'] = $tables_fields[$row['tablename']].' as title,pages.uid,pages.deleted,pages.hidden, tt_content.deleted as content_deleted,tt_content.hidden as content_hidden';
				$query['from'] = 'sys_log, tt_content, pages';
				$query['where'] = 'sys_log.recuid=tt_content.uid and tt_content.pid=pages.uid';
				// focus on the correct uid unit
				$query['where'] .= ' and sys_log.recuid='.$row['recuid'];
				// ignore the undesirables
				$query['where'] .= $this->type;
			} elseif ($row['tablename'] == "pages") {
				$query['string'] = $tables_fields[$row['tablename']].' as title,pages.uid,pages.deleted,pages.hidden';
				$query['from'] = 'sys_log, pages';
				$query['where'] = 'sys_log.recuid=pages.uid';
				$query['where'] .= ' and sys_log.recuid='.$row['recuid'];
				$query['where'] .= $this->type;
			} elseif ($row['tablename'] == "tt_news") {
				$query['string'] = 'pid, pi_flexform';
				$query['from'] = 'tt_content';
				$query['where'] = 'pi_flexform like \'%<field index="pages">%\'';
			} elseif ($row['tablename'] == "tx_dam") {
				$query['string'] = '*';
				$query['from'] = 'tx_dam, sys_log';
				$query['where'] = 'sys_log.recuid=tx_dam.uid';
				$query['where'] .= ' and sys_log.recuid='.$row['recuid'];
				$query['where'] .= $this->type;
			} elseif ( trim( $row['tablename'] ) ) {
				$str = isset($tables_fields[$row['tablename']]) ?  $tables_fields[$row['tablename']]." as title" : "*";
				$query['string'] = $str;
				$query['from'] = $row['tablename'].', sys_log';
				$query['where'] = 'sys_log.recuid='.$row['tablename'].'.uid';
			} else {
				continue;
			}

			// for known tablename - trying to get uid and title of page
			// else - we don't know for sure if there any page for changed content
			if ($query) {
				$restitle = $GLOBALS['TYPO3_DB']->exec_SELECTquery($query['string'],$query['from'],$query['where'],'','','');
				$item_hidden = false;

				// for news item we do much job:
				if ($row['tablename'] != "tt_news") {
					if ($restitle)
						$rowtitle = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($restitle);
					else
						t3lib_div::debug( $query );

					// getting title
					$this->history[$i]['page_title'] = $rowtitle['title'];
					$this->history[$i]['page_uid'] = $rowtitle['uid'];
					if ($rowtitle['deleted'] == true || $rowtitle['hidden'] == 1 || $rowtitle['content_deleted'] == 1 || $rowtitle['content_hidden'])
						$item_hidden = true;
					$this->history[$i]['item_hidden'] = $item_hidden;

					// elseif ($row['tablename'] == "tt_news")
				} else {
					// from tt_content: pid,pi_flexform
					while (true == ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($restitle))) {
						$arr[] = $r['pi_flexform']; // array of fields tt_content.pi_flexform
						$arr_pid[] = $r['pid'];     // array of tt_content.pid
					}
					// from tt_news_cat_mm: uid_foreign
					$res_cats = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign',
					'tt_news_cat_mm',
					'uid_local='.$row['recuid'],'','','');
					$cats = array();
					while (true == ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_cats))) {
						$cats[] = $r['uid_foreign']; // array of tt_news_cat_mm.uid_foreign
					}
					// from tt_news: pid (pages.uid of storage folder)
					$str = "";
					$str = isset($tables_fields['tt_news']) ? $tables_fields['tt_news'] : "title";
					$str .= " as title";
					$res_pids = $GLOBALS['TYPO3_DB']->exec_SELECTquery($str.',pid,hidden,deleted,endtime','tt_news','uid='.$this->history[$i]['uid'],'','','');
					while (true == ($r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pids))) {
						$pid[] = $r['pid']; // tt_news.pid of current news item
						$news_title = $r['title']; // tt_news.title for news item
						if ($r['hidden'] == 1 || $r['deleted'] == 1 || ($r['endtime'] > time()))
							$item_hidden = true;
					}
					// for every item from tt_content
					$catMode = array();
					$catSel = array();
					$pages = array();
					$news_type = array();
					for ($j = 0, $n = count($arr); $j < $n; $j++) {
						preg_match('/<field index="categoryMode">\s+<value index="vDEF">(.*)<\/value>/',$arr[$j],$catMode);
						preg_match('/<field index="categorySelection">\s+<value index="vDEF">(.*)<\/value>/',$arr[$j],$catSel);
						preg_match('/<field index="pages">\s+<value index="vDEF">(.*)<\/value>/',$arr[$j],$pages);
						preg_match('/<field index="what_to_display">\s+<value index="vDEF">(.*)<\/value>/',$arr[$j],$news_type);
						$matches[$j]['catMode'] = $catMode[1];
						$matches[$j]['catSel'] = $catSel[1];
						$matches[$j]['pages'] = $pages[1];
						$matches[$j]['news_type'] = $news_type[1]; // single??
					}

					for ($j = 0, $n = count($arr); $j < $n; $j++) {
						$flag_cat = false;
						$cat_select = explode(",",$matches[$j]['catSel']);
						foreach ($cats as $cat) {
							foreach ($cat_select as $cat_s) {
								if ($cat == $cat_s) {
									$flag_cat = true;
									break;
								}
							}
						}
						if (strtolower($matches[$j]['news_type']) == "single"
							&& ($matches[$j]['catMode'] == 0 || $matches[$j]['catMode'] == 1 ||$matches[$j]['catMode'] == 0)
							&& (empty($matches[$j]['catSel']) || $flag_cat)
							&& (empty($matches[$j]['pages']) || in_array($pid[0],explode(",",$matches[$j]['pages'])))
						) {
							$uid_pages[] = $arr_pid[$j];
						}
					}
					if (is_array($uid_pages)) {
						$uid_pages = array_unique($uid_pages);
						// getting uid and title for pages with recieved ids
						$res_pages = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','pages','uid in('.implode(",",$uid_pages).')','uid desc','','');
					}
					while ($res_pages && $r = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pages)) {
						$this->history[$i]['page_title'][] = $r['title'];
						$this->history[$i]['page_uid'][] = $r['uid'];
					}
					$this->history[$i]['news_title'] = $news_title;
					$this->history[$i]['item_hidden'] = $item_hidden;
				}
			}
			$i++;
		}
	}
}
/**
 * returns timestamp of string m-d-y
 *
 * @param string $date_string
 * @param boolean $up
 * @return int
 */
function get_date_stamp($date_string,$up) {
   list($m,$d,$y) = explode("-",$date_string);
   if ($y < 1970) {
		$y = 1970;
		$m = 1;
		$d = 1;
   }
   if ($y > 2037 || ($y == 2038 && $m > 1) || ($y == 2038 && $m == 1 && $d > 18)) {
		$y = 2038;
		$m = 1;
		$d = 17;
   }
   if ($up) {
		$d = $d." 23:59:59";
   } else {
   	$d = $d." 00:00:00";
   }
   $time = $y."-".$m."-".$d;
   return $time;
}
/**
 * returns string of time (secs,mins,hours or days) passed in seconds
 *
 * @param int $secs
 * @return string
 */
function getAge($secs) {
   $age = "";
   if ($secs < 60)
		$age = $secs." sec(s)";
   elseif ($secs < 3600)
		$age = intval($secs / 60)." min(s)";
   elseif ($secs <= (3600*24)) {
   	$age = intval($secs/3600);
   	if ($age == 24)
   	$age = "1 day";
   	else
			$age = $age." hour(s)";
   } else
		$age = intval($secs/3600/24)." day(s)";
   return $age;
}

/**
 * log events
 *
 * @param string $text
 */
function logger($text) {
   if (!LOGGER)
		return;
   $fh = @fopen('log','a');
   if ($fh) {
		@fwrite($fh,gmdate("m-d-Y H:i:s")." - ".$text);
		@fclose($fh);
   }
}
?>
