<?php
/**
 * render of history data
 *
 */
class Render {
	var $url = BASE_URL;
	var $diffTableHeader = '
<table border="0" cellpadding="2" cellspacing="2" id="typo3-history" style="table-layout: auto;position:relative;">
<tr class="bgColor5 c-head" align="center">
<td>Date</td>
<td>Age</td>
<td>Changed By</td>
<td>Record Type</td>
<td>Id</td>
<td>Field Changed</td>
<td>Page Title</td>
<td>Differences</td>
</tr>';
   /**
    * table of users, logins and changes
    *
    * @param int $login_counts
    * @param array $changes
    * @param array $dates
    * @param array $users
    * @return string
    */
   function usersTable($login_counts,$changes,$dates,$users,$tables) {
		global $tables_names;
		$content = '
<script type="text/javascript" language="javascript" src="/typo3conf/ext/log_analyzer/validation.js"></script>
<form name="settings" id="settings" action="#" method="post" onsubmit="return ValidateForm(this)">
<input type="hidden" name="report_type" value="" />
<input type="hidden" name="page" value="" />
<table><tr>
<td colspan="2" align="left">
<table class="typo3-adminPanel-hRow"><tr>
<td class="bgColor5 c-head">Logins</td>
<td class="bgColor4-20" align="right">
<tablealign="left" class="typo3-history-item">
<tr><td align="right">'.$login_counts.'</td></tr></table>
</td></tr><tr class="bgColor4-20">
<td class="bgColor5 c-head" valign="middle">Changes per user</td>
<td valign="top">';
		if ($changes) {
		   $content .= '<table align="left" class="typo3-history-item">';
		   foreach ($changes as $name => $count) {
				if ($name != 'counts')
					 $content .= '<tr><td style="padding-right:3px">'.$name.'</td><td>'.$count.'</td></tr>'."\n";
			 }
			 $content .= '</table>';
		} else {
		   $content .= '<table align="left" class="typo3-history-item"><tr><td align="right">0</td></tr></table>';
		}
		$content .= '
</td>
</tr>
<tr class="bgColor4-20">
<td class="bgColor5 c-head">Total changes</td>
<td class="bgColor4-20" align="right">
<table align="left" class="typo3-history-item">
<tr><td align="right">'.(isset($changes['counts']) ? $changes['counts'] : 0).'</td></tr>
</table></td></tr></table></td></tr><tr>
<td>Date</td>
<td><table>
<tr>
<td>From:</td>
<td><input type="text" name="from_date" id="from_date" value="'.$dates['from'].'" size="15" />&nbsp;<small>MM-DD-YYYY</small>
</td>
</tr>
<tr>
<td>To:</td>
<td><input type="text" name="to_date" id="to_date" value="'.$dates['to'].'" size="15" />&nbsp;<small>MM-DD-YYYY</small>
</td>
</tr>
</table></td>
</tr>
<tr><td colspan="2"><table>
<tr>
<td>Content record type</td>
<td><select name="type[]" style="width:150px" multiple="multiple" size="5">
<option value="0"'.((isset($_POST['type']) && !empty($_POST['type'][0])) ? '' : ' selected="selected"').'>All</option>';
		if (is_array($tables_names)) {
			foreach ($tables_names as $table => $name) {
			   if (isset($_POST['type']) && (false !== array_search($table,$_POST['type'])))
					$str = " selected=\"selected\"";
			   else
					$str = "";
			   $content .= "<option value='".$table."'".$str.">".$name."</option>\n";
			}
		}
   	unset($table);
   	foreach ($tables as $table) {
   	   if (isset($_POST['type']) && (false !== array_search($table,$_POST['type'])))
   			$str = " selected=\"selected\"";
   	   else
   			$str = "";
   	   if (false === array_search($table,$tables_names))
   			$content .= "<option value='".$table."'".$str.">".$table."</option>\n";
   	}
	   $content .= '
</select>
</td>
</tr><tr>
<td>Author</td>
<td><select name="author[]" style="width:150px" multiple="multiple" size="5">
<option value="0"'.((isset($_POST['author']) && !empty($_POST['author'][0])) ? '' : ' selected="selected"').'>All</option>';
		foreach ($users as $uid => $username) {
		   if (isset($_POST['author']) && false !== array_search($uid,$_POST['author']))
				$str = " selected=\"selected\"";
		   else
				$str = "";
		   $content .= "<option value='$uid'$str>$username</option>\n";
		}
		$content.=' 
</select>
</td></tr><tr><td colspan="2">&nbsp;</td></tr>
<tr><td align="right"><input type="submit" value="Submit" /></td>
<td>&nbsp;</td></tr></table></td></tr></table></form>';
		return $content;
   }
   
   /**
    * table of differences with link to page where differences are shown
    *
    * @param array $dates
    * @param int $page_uid
    * @return string
    */
   function BEDifferenceTable($history,$page_uid) {
		$content = $this->diffTableHeader;
		// if we have something to display
		if ($history) {
		   foreach ($history as $hist) {
		   	$data = unserialize($hist['log_data']);
				if (!$data)
				   $data = array();
   		   $content .= '
<tr class="bgColor4-20">
<td>'.$hist['time'].'</td>
<td>'.getAge($hist['age']).'</td>
<td><a href="mailto:'.$hist['email'].'" title="'.$hist['email'].'">'.$hist['user'].'</a></td>
<td>'.$hist['type'].'</td>
<td>'.$hist['uid'].'</td>
<td>';
   		   // if empty $hist['field'] - trying to get something from sys_log
   		   if (!empty($hist['field']))
   				$content .= $hist['field'];
   		   else {
   		   	    $str = sprintf($hist['details'],$data[0],$data[1],$data[2],$data[3]);
   				$content .= '<span style="color:#ff1111">'.$str.'</span>';
   		   }
   		   $content .= '</td><td>';
   	    // pages titles
   		if (is_array($hist['page_title'])) {
				if ($hist['item_hidden'] == true) {
				   $content .= $hist['news_title'].'<br />'.$notLive;
				} else {
				   $content .= '<a target="_blank" href="'.$this->url.'index.php?id='.$hist['page_uid'][0].'&tx_ttnews[tt_news]='.$hist['uid'].'&no_cache=1">'.$hist['news_title'].'</a><br />';
				}
		  } else {
				if ($hist['type'] != "News") {
				   if (empty($hist['page_title']))
						$content .= $data[0];
				   else {
						if ($hist['item_hidden'] == true)
						   $content .= $hist['page_title'].'<br />'.$notLive;
						elseif ($hist['page_uid'])
						   $content .= '<a target="_blank" href="'.$this->url.'index.php?id='.$hist['page_uid'].'&no_cache=1">'.$hist['page_title'].'</a>';
						else
						   $content .= $hist['page_title'];
				   }
				} else {
				    $content .= '<a target="_blank" href="'.$this->url.'index.php?id='.$hist['page_uid'].'&no_cache=1">'.$hist['news_title'].'</a>';
				}
		   }
		   $content .= '
   </td><td align="center">
   <a target="_blank" href="'.$this->url.'index.php?id='.$page_uid.'&uid='.$hist['id'].'&log_uid='.$hist['log_uid'].'&no_cache=1">see&nbsp;difference</a>
   </td>
   </tr>'."\n";
   	   }
		}
		$content .= '<tr class="bgColor5 c-head" style="height:2px"><td colspan="8"></td></tr>';
		$content .= '</table><p align="right" style="width:600px"><a href="#">Back to top</a></p>';
	   return $content;
   }

   /**
    * table of differences with links to page where content is shown
    * @param array $history
    *
    * @return string
    */
   function FEDifferenceTable($history) {
		$notLive = '<span style="color:#ff1111">This item isn\'t live</span>';
		$content = $this->diffTableHeader;
		$data = unserialize($history[0]['data']);
		if ($data)
		   $n = count($data['oldRecord']);
		else
		   $n = 1;
		$content .= '
<tr class="bgColor4" valign="top" align="center">
<td rowspan="'.$n.'">'.$history[0]['time'].'</td>
<td rowspan="'.$n.'">'.getAge($history[0]['age']).'</td>
<td rowspan="'.$n.'"><a href="mailto:'.$history[0]['email'].'" title="'.$history[0]['email'].'">'.$history[0]['user'].'</a></td>
<td rowspan="'.$n.'">'.$history[0]['type'].'</td>
<td rowspan="'.$n.'">'.$history[0]['uid'].'</td>
<td rowspan="'.$n.'">';
		// title is array - news
		if (is_array($history[0]['page_title'])) {
		   if ($history[0]['item_hidden']) {
				if (!empty($history[0]['news_title']))
				   $history[0]['news_title'] .= "<br />"; 
				$content .= $history[0]['news_title'].$notLive;
		   } else {
				$content .= '<a target="_blank" href="'.$this->url.'index.php?id='.$history[0]['page_uid'][0].'&tx_ttnews[tt_news]='.$history[0]['uid'].'&no_cache=1">'.$history[0]['news_title'].'</a><br /><br />';
		   }
		} else {
		// title isn't array 
		   if ($history[0]['type'] != "News") {
		   // not news
				if (empty($history[0]['page_title'])) {
//				   if (isset($data['newRecord'])) {
//						foreach ($data['newRecord'] as $key) {
//						   $content .= wordwrap($key,50,"<br />\n",true);
//						}
//				   }
				   $content .= $data[0];
				} else {
				// news
				   if ($history[0]['item_hidden'] == true)
						$content .= $history[0]['page_title'].'<br />'.$notLive;
				   elseif ($history[0]['page_uid'])
						$content .= '<a target="_blank" href="'.$this->url.'index.php?id='.$history[0]['page_uid'].'&no_cache=1">'.$history[0]['page_title'].'</a>';
				   else
						$content .= $history[0]['page_title'];
				}
		   } else {
				$content .= '<a target="_blank" href="'.$this->url.'index.php?id='.$history[0]['page_uid'].'&tx_ttnews[tt_news]='.$history[0]['uid'].'&no_cache=1">'.$history[0]['news_title'].'</a>';
		   }
		}
		   $content .= '</td>';
		if (!$data) {
		   $content .= '
<td colspan="2"><span style="color:#ff1111">No difference report:</span><br />'.$history[0]['details'].'</td></tr>';
		} else {
			 $content .= $this->FEDataTable($data,$history[0]['details']);
		}
		$content .= '</table>';
		return $content;
   }
   
   function FEDataTable($data,$details) {
   	  $row_flag = false;
   	  if (isset($data['oldRecord']) && is_array($data['oldRecord'])) {
			foreach ($data['oldRecord'] as $field => $val) {
			   if ($row_flag)
				    $content .= '<tr class="bgColor4" align="center">';
			   $content .= '
<td align="left">'.$field.'</td>
<td><table>
<tr class="bgColor4-20">
<td><em>Old&nbsp;record:</em></td><td>';
			   if (false !== unserialize($data['oldRecord'][$field])) {
					foreach (unserialize($data['oldRecord'][$field]) as $key => $value) {
					   $content .= $key."<br />\n";
					}
			   } else {
					if (preg_match("/time/",$field))
					   $content .= date("m-d-Y H:i",$data['oldRecord'][$field]);
					else
					   $content .= wordwrap($data['oldRecord'][$field],50,"<br />\n",true);
			   }
			   $content .= '
</td></tr><tr style="background-color:#eeeeee">
<td><em>New&nbsp;record:</em></td><td>';
			   if (false !== unserialize($data['newRecord'][$field])) {
					foreach (unserialize($data['newRecord'][$field]) as $key => $value) {
					   $content .= "<br />\n";
					}
			   } else {
					if (preg_match("/time/",$field))
					   $content .= date("m-d-Y H:i",$data['newRecord'][$field]);
					else
					   $content .= wordwrap($data['newRecord'][$field],50,"<br />\n",true);
			   }
			   $content .= '
</td></tr></table></td></tr>'."\n";
			   $row_flag = true;
		   }
   	  } else {
   	  	  if (preg_match("/insert/i",$details))
				 $str = "item ".$data[0]." was inserted";
   	  	  elseif (preg_match("/delete/i",$details))
   	  	     $str = "item ".$data[0]." was deleted";
   	  	  elseif (preg_match("/update/i",$details))
   	  	     $str = "item ".$data[0]." was updated";
   	  	  elseif (preg_match("/move/i",$details))
   	  	     $str = "item ".$data[0]." was moved";
   	  	  else
   	  	  	$str = sprintf($details,$data[0],$data[1],$data[2],$data[3]);
   	  	  $content .= '
   	  	  <td align="left" colspan="2">
   	  	  '.$str.'
   	  	  </td>';
   	  }
   	  return $content;
   }
   
   /**
    * xls file
    *
    * @param array $history
    * @param int $page_uid
    */
   function XLSDifferenceTable($history,$page_uid,$login_counts,$changes,$type=2) {
		require_once PEAR_ROOT."Writer.php";
		if ($type == 4 || $type == 5) {
			$xls =& new Spreadsheet_Excel_Writer(PATH_site.'typo3temp/report.xls');
		} else {
			$xls =& new Spreadsheet_Excel_Writer();
		}

		$filename					= 'report_'.gmdate('m-d-Y_H:i').'.xls';
		$xls->send($filename);
		// ob_end_clean();
		$sheet =& $xls->addWorksheet('Change Log');
		
		$titleFormat =& $xls->addFormat();
		$titleFormat->setFontFamily('Helvetica'); 
		$titleFormat->setBold();
		$titleFormat->setSize('13');
		$titleFormat->setColor('navy');
		$titleFormat->setBottom(2);
		$titleFormat->setBottomColor('navy');
		
		$errorFormat =& $xls->addFormat();
		$errorFormat->setColor('red');
		
		$sheet->write(0,0,'Date',$titleFormat);
		$sheet->write(0,1,'Age',$titleFormat);
		$sheet->write(0,2,'Changed By',$titleFormat);
		$sheet->write(0,3,'Record Type',$titleFormat);
		$sheet->write(0,4,'Id',$titleFormat);
		$sheet->write(0,5,'Field Changed',$titleFormat);
		$sheet->write(0,6,'Page Title',$titleFormat);
		$sheet->write(0,7,'Difference',$titleFormat);
		
		if ($history) {
		   $j = 1;
		   for ($i = 0, $n = count($history); $i < $n; $i++) {
				$sheet->write($j,0,$history[$i]['time']);
				$sheet->write($j,1,getAge($history[$i]['age']));
				$sheet->write($j,2,$history[$i]['user']);
				$sheet->write($j,3,$history[$i]['type']);
				$sheet->write($j,4,$history[$i]['uid']);
				$sheet->write($j,5,str_replace("<br />",chr(10),$history[$i]['field']));
				$data = unserialize($history[$i]['log_data']);
				if (!$history[$i]['page_uid']) {
				   $sheet->write($j,6,$data[0]);
				} else {
				   if (is_array($history[$i]['page_title'])) {
						if ($history[$i]['item_hidden']) {
						   $sheet->write($j,6,$history[$i]['news_title'].chr(10).$notLive,$errorFormat);
						} else {
						   $sheet->writeUrl($j,6,$this->url.'index.php?id='.$history[$i]['page_uid'][0].'&tx_ttnews[tt_news]='.$history[$i]['uid'].'&no_cache=1',$history[$i]['news_title']);
						}
				   } else {
						if ($history[$i]['type'] != "News") {
						   if (empty($history[$i]['page_title']))
								$sheet->write($j,6,$data[0]);
						   else {
								if ($history[$i]['item_hidden'] == true)
								   $sheet->write($j,6,$history[$i]['page_title'].chr(10).$notLive,$errorFormat);
								else
								   $sheet->writeUrl($j,6,$this->url.'index.php?id='.$history[$i]['page_uid'].'&no_cache=1',$history[$i]['page_title']);
						   }
						} else {
						   $sheet->writeUrl($j,6,$this->url.'index.php?id='.$history[$i]['page_uid'].'&no_cache=1',$history[$i]['news_title']);
						}
				   }
				}
				$sheet->writeUrl($j,7,$this->url.'index.php?id='.$page_uid.'&uid='.$history[$i]['id'].'&log_uid='.$history[$i]['log_uid'].'&no_cache=1','see difference');
				$j++; 
		   }
		} else {
		   $sheet->write(1,0,'No changes for this period');
		}
		
		$sheet =& $xls->addWorksheet('Login and change summary');
		
		$titleFormat->setBottom(0);
		$sheet->write(0,0,"Logins today",$titleFormat);
		$sheet->write(0,2,$login_counts);
		
		$sheet->write(1,0,"Total changes",$titleFormat);
		$sheet->write(1,2,(isset($changes['counts']) ? $changes['counts'] : 0));
		
		$sheet->write(2,0,"Changes per login",$titleFormat);
		if ($changes) {
		   $i = 3; // row
		   foreach ($changes as $name => $count) {
				if ($name != 'counts') {
				   $sheet->write($i,1,$name);
				   $sheet->write($i,2,$count);
				   $i++;
				}
			}
		} else {
		   $sheet->write(2,2,0);
		}
		$xls->close();
   }
   
   function pages($page,$max_page) {
   	  if (!$page)
   	     $page = 1;
   	  $content .= '
<div class="query_page" style="width:100%;text-align:left;padding:4px 0px">
<select name="go_page" onchange="go(this.value)">
';
		for ($i = 1; $i <= intval($max_page); $i++) {
		   $str = "";
		   if ($page == $i)
				$str = ' selected="selected"';
		   $content .= '<option value="'.$i.'"'.$str.'>'.$i.'</option>'."\n";
		}
		$content .= '
</select>
<a href="javascript:go(0)">&lt;&lt;</a>&nbsp;
<a href="javascript:go('.($page-1).')">&lt;</a>&nbsp;
'.$page.' of '.$max_page.'
<a href="javascript:go('.($page+1).')">&gt;</a>
&nbsp;<a href="javascript:go('.($max_page).')">&gt;&gt;</a>&nbsp;
</div>
';
   	  return $content;
   }
}
?>
