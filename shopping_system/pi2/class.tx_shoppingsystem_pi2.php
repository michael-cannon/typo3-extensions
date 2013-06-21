<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Titarenko Dmitri <td@krendls.eu>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Products list' for the 'shopping_system' extension.
 *
 * @author	Titarenko Dmitri <td@krendls.eu>
 * @author 	Eugene Lamskoy <le@krendls.eu>
 */




require_once(PATH_tslib.'class.tslib_pibase.php');

session_start();

class tx_shoppingsystem_pi2 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_shoppingsystem_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'shopping_system';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		/*return 'Hello World!<HR>
			Here is the TypoScript passed to the method:'.
					t3lib_div::view_array($conf);
		*/
		return '';
	}

	function extraCodesProcessor($news)
	{
		$pid = intval($news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['pages']['vDEF']);
 		// MLC 20070629 grab master pid if no overriding
 		$pid = $pid ? $pid : $news->pid_list;

		$currentPriceIndex= 0;
		$currentPagingIndex = 5;
		$currentSortingIndex = 1;
				
		$prices = array(
			0 => array('title'=>'Any price'),
			1 => array('title'=>'Less than 25', 'from'=>'0', 'to'=>'25'),
			2 => array('title'=>'$25-50', 'from'=>'25', 'to'=>'50'),
			3 => array('title'=>'$50-100', 'from'=>'50', 'to'=>'100'),
			4 => array('title'=>'$100-200', 'from'=>'100', 'to'=>'200'),
			5 => array('title'=>'$200+', 'from'=>'200'),
		);

		$pagings = array(
		  	1=> array('title'=>'2', 'records'=>2),
		  	2=> array('title'=>'4', 'records'=>4),
		  	3=> array('title'=>'8', 'records'=>8),
		  	4=> array('title'=>'10', 'records'=>10),
		  	5=> array('title'=>'20', 'records'=>20),
		  	6=> array('title'=>'50', 'records'=>50),
		  	7=> array('title'=>'100', 'records'=>100),
		  	8=> array('title'=>'All per page', 'records'=> -1 ),
		);
		$sortings = array (
			1 => array('title'=>'Relevance', 'field'=>' keywords DESC, title DESC, tx_shoppingsystem_product_brand DESC, tx_shoppingsystem_product_store DESC ', 'order'=> ''),			
			2 => array('title'=>'Price: low to high', 'field'=>'tx_shoppingsystem_product_price', 'order'=>'asc'),
			3 => array('title'=>'Price: high to low', 'field'=>'tx_shoppingsystem_product_price', 'order'=>'desc'),
			4 => array('title'=>'Newest arrivals', 'field'=>'tstamp', 'order'=>'desc'),		
			5 => array('title'=>'Oldest arrivals', 'field'=>'tstamp', 'order'=>'asc'),
		);


		//var_dump($_SESSION);

        if($news->theCode == 'PRODUCTS_LIST')	
        {

		//var_dump($_SERVER);

		if(preg_match("/search/i", $_SERVER['REQUEST_URI'] )) {
			$searchMode = true;
            
            if ( isset($_POST['category-browse']) && $_POST['category-browse'] > 0 ){
                
                unset($_POST['category']);
                unset($_POST['product']);
                unset($_SESSION['savysort']['keyword']);                
                
                $_POST['category'] = $_POST['category-browse'];
            }
            
            
			if(isset($_POST['category'])) {
				$_SESSION['savysort']['category'] = $cat = intval($_POST['category']);			
			} elseif (isset($_SESSION['savysort']['category'])) {
				$cat = $_SESSION['savysort']['category'];
	
			} else {
                	        $cat = $news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categorySelection']['vDEF'];
			}

		} else {
			$cat = $news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categorySelection']['vDEF'];
			$searchMode = false;
			if(isset($_SESSION['savysort']['keyword']))	
			{		
				unset($_SESSION['savysort']['keyword']);				
			}
			if(isset($_SESSION['savysort']['category']))	
			{				
				unset($_SESSION['savysort']['category']);	
			} 
		}

		
                $keywordPart = '';		

                                  
	if($searchMode) {
	 	if(isset($_POST['product'])) {
			$product = trim(strval($_POST['product']));
        		$_SESSION['savysort']['keyword'] = $product;
                
                
                
                
			if(strlen($product)) {				
                $keywords = explode( ' ',$product );
    
			}
		} elseif(isset($_SESSION['savysort']['keyword'])) {
			$product = $_SESSION['savysort']['keyword'];
            
            $keywords = explode( ' ',$product );
            
			
		}
	}	
    
    if ( is_array($keywords) ){
        
        foreach ( $keywords as $keyword ){
     
        $keywordPart .= " AND ( keywords LIKE \"%{$keyword}%\" OR tx_shoppingsystem_product_store LIKE \"%{$keyword}%\" OR tx_shoppingsystem_product_brand LIKE \"%{$keyword}%\" OR `title` LIKE \"%{$keyword}%\" )" ;   
     
        }
        
    }
    

		//var_dump($keywordPart);
        	
                $productSpecified = isset($_POST['product']);
		

        	// number of products per page
        	$news->config['limit'] = $pagings[$currentPagingIndex]['records'];
		
	        if(isset($_POST['sortform']['pagelimit'])) {
			$val = intval($_POST['sortform']['pagelimit']);
			if( in_array($val, array_keys($pagings))) {							
		        	$news->config['limit'] = $pagings[$val]['records'];
				$currentPagingIndex = $val;
				$_SESSION['savysort']['pagelimit'] = $val;
			}
		} elseif(isset($_SESSION['savysort']['pagelimit'])) {
			$currentPagingIndex = $val = $_SESSION['savysort']['pagelimit'];
			$news->config['limit'] = $pagings[$val]['records'];
		}

		                                       
		$pricesRangePart = '';
		if($productSpecified) {
			// Skip range for product search
		
	        } elseif(isset($_POST['sortform']['pricerange'])) {
			$val = intval($_POST['sortform']['pricerange']);
			if( in_array($val, array_keys($prices))) {
				
				$fld = 'tx_shoppingsystem_product_price';
				$min = $prices[$val]['from'];

				if($val == 0) {
					
				} elseif(isset($prices[$val]['to'])) {
					$max = $prices[$val]['to'];
					$pricesRangePart = " AND {$fld} >= $min AND {$fld} <= $max ";				
				} else {
					$pricesRangePart = " AND {$fld} >= $min ";
				}				
					                          	
				$currentPriceIndex = $val;
				$_SESSION['savysort']['pricerange'] = $val;
			}
		} elseif(isset($_SESSION['savysort']['pricerange'])) {
			$currentPriceIndex = $val = $_SESSION['savysort']['pricerange'];
			$fld = 'tx_shoppingsystem_product_price';
			$min = $prices[$val]['from'];

			if($val == 0) {


			} elseif(isset($prices[$val]['to'])) {
				$max = $prices[$val]['to'];
				$pricesRangePart = " AND {$fld} >= $min AND {$fld} <= $max ";				
			} else {
				$pricesRangePart = " AND {$fld} >= $min ";
			}				
		}
		
		$orderPart = " uid DESC ";
		
	        if(isset($_POST['sortform']['sorting'])) {
			$val = intval($_POST['sortform']['sorting']);
			if( in_array($val, array_keys($sortings))) {				
				$_SESSION['savysort']['sorting'] = $currentSortingIndex = $val;
				$orderPart = " {$sortings[$val]['field']} {$sortings[$val]['order']} ";
			        //var_dump($orderPart);
			}
		} elseif(isset($_SESSION['savysort']['sorting'])) {                                  
			$currentSortingIndex = $val = $_SESSION['savysort']['sorting'];
			$orderPart = " {$sortings[$val]['field']} {$sortings[$val]['order']} ";
		        //var_dump($orderPart);
		}



        	$offset = 0;
        	$cur_page = 0;

        	if(isset($_REQUEST['tx_ttnews'][5]))
		{
			$cur_page = intval($_REQUEST['tx_ttnews'][5]);
			$offset = $cur_page * $news->config['limit'];
			//var_dump($cur_page);			
		}
		

		
		if(isset($_POST['sortform']['pagelimit']) || isset($_POST['sortform']['sorting']) || isset($_POST['sortform']['pricerange']) ) {
			$cur_page = 0;
			$offset = $cur_page * $news->config['limit'];	
		}


        	if(true)
        	{
			
        		$templateFile = t3lib_div::getURL(t3lib_extMgm::extPath('shopping_system').'res/template.html');
			$templates['formRegTemplate'] = $news->cObj->getSubpart($templateFile, '###TEMPLATE_PRODUCTS_LIST###');
			if($cat != 0 ) {
	        		$singleWhere = 'uid_foreign='.$cat;
			} else {
	        		$singleWhere = ' 1=1 ';
			}
			

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local','tt_news_cat_mm',$singleWhere);

				if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
				{
					
					$singleWhere = 'tt_news.uid IN (';

					$num_rows = 0;
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
					{
						$singleWhere .= $row['uid_local'];
						if($num_rows < $GLOBALS['TYPO3_DB']->sql_num_rows($res)-1)
							$singleWhere .= ', ';
							
						$num_rows++;
					}
					
					//<-- to get total number of products for this cat -->
					$singleWhereTotal = $singleWhere;                     
					$singleWhereTotal .= ') AND NOT deleted AND NOT hidden AND pid = '.$pid." ".$pricesRangePart.$keywordPart;
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_news',$singleWhereTotal);
					$num_rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        				//<-- to get total number of products for this cat -->

					// fetch products
					//var_dump($pid);
					$singleWhere .= ') AND NOT deleted AND NOT hidden AND pid = '.$pid." ".$pricesRangePart.$keywordPart;

					if($news->config['limit'] >= 0) {
						$singleWhere .= " ORDER BY {$orderPart} LIMIT $offset, ".$news->config['limit'];
						$hidePageLinks = false;
					} else {
						$singleWhere .= " ORDER BY {$orderPart} ";	
						$hidePageLinks = true;
					}

					//var_dump($singleWhere);
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_news',$singleWhere);

				
					/** template **/

					$templates['formSubTemplate'] = $news->cObj->getSubpart($templateFile, '###TEMPLATE_SINGLE_PRODUCT###');
                                                                                                                                               
					$arr_producnt_rows = array();
					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
					{
						$row['tx_shoppingsystem_product_price'] = sprintf("%01.2f",$row['tx_shoppingsystem_product_price']);						
						$templateMarkers['###PRODUCT_PIC###'] = "<a href='".$row['tx_shoppingsystem_product_merchant_url']."' target='_blank'><img src='/uploads/tx_shoppingsystem/$row[tx_shoppingsystem_product_image_small]' style='border: 0' /></a>";
						$templateMarkers['###PRODUCT_TITLE###'] = "<a href='".$row['tx_shoppingsystem_product_merchant_url']."' onmouseout='style.textDecoration=\"underline\"' onmouseover='style.textDecoration=\"none\"' style='text-decoration: underline' target='_blank'>".$row['title']."</a>";
						$templateMarkers['###PRODUCT_BRAND###'] = $row['tx_shoppingsystem_product_brand'];
						$templateMarkers['###PRODUCT_PRICE###'] = $row['tx_shoppingsystem_product_price'];
						$templateMarkers['###PRODUCT_STORE###'] = 'from '.$row['tx_shoppingsystem_product_store'];

						$arr_producnt_rows[] = $news->cObj->substituteMarkerArrayCached($templates['formSubTemplate'], $templateMarkers);
					}

					$templates['formRowTemplate'] = $news->cObj->getSubpart($templateFile, '###TEMPLATE_PRODUCT_ROW###');
					
					$count = 0;
											
					foreach($arr_producnt_rows as $key => $row)
					{
						if(count($arr_producnt_rows) < 2)
							$content .= $news->cObj->substituteSubpart($templates['formRowTemplate'], '###TEMPLATE_SINGLE_PRODUCT###', $arr_producnt_rows[$key]);
						elseif($key % 2 == 1)
							$content .= $news->cObj->substituteSubpart($templates['formRowTemplate'], '###TEMPLATE_SINGLE_PRODUCT###', $arr_producnt_rows[$key-1].$arr_producnt_rows[$key]);
						elseif(count($arr_producnt_rows) % 2 != 0 && $count == count($arr_producnt_rows)-1)
							$content .= $news->cObj->substituteSubpart($templates['formRowTemplate'], '###TEMPLATE_SINGLE_PRODUCT###', $arr_producnt_rows[$key]);
						
						$count++;
					}

					$content = $news->cObj->substituteSubpart($templates['formRegTemplate'], '###TEMPLATE_PRODUCT_ROW###', $content);

					$news->internal['res_count'] = $num_rows;
					$news->internal['results_at_a_time'] = $news->config['limit'];
					$news->internal['showPBrowserText'] = $news->conf['pageBrowser.']['showPBrowserText'];
					$news->internal['maxPages'] = $news->conf['pageBrowser.']['maxPages'];
					$news->internal['pagefloat'] = $news->conf['pageBrowser.']['pagefloat'];
				//	$news->internal['showFirstLast'] = $news->conf['pageBrowser.']['showFirstLast'];
				//	$news->internal['showRange'] = $news->conf['pageBrowser.']['showRange'];
					$news->internal['dontLinkActivePage'] = $news->conf['pageBrowser.']['dontLinkActivePage'];

					$$wrapArrFields = explode(',', 'disabledLinkWrap,inactiveLinkWrap,activeLinkWrap,browseLinksWrap,showResultsWrap,showResultsNumbersWrap,browseBoxWrap');
					$wrapArr = array();
					foreach($$wrapArrFields as $key) {
						if ($news->conf['pageBrowser.'][$key]) {
							$wrapArr[$key] = $news->conf['pageBrowser.'][$key];
						}
					}
					// if there is a GETvar in the URL that is not in this list, caching will be disabled for the pagebrowser links
					$news->pi_isOnlyFields = $pointerName.',tt_news,year,month,day,pS,pL,arc';
					$news->pi_alwaysPrev = $news->conf['pageBrowser.']['alwaysPrev'];

					$pages = $news->pi_list_browseresults($news->conf['pageBrowser.']['showResultCount'], $news->conf['pageBrowser.']['tableParams'],$wrapArr, 5, $news->conf['pageBrowser.']['hscText']);
					
					//var_dump($pages);
					$modified = true;
					$singleItem = preg_match("@<strong>1</strong></div>@i", $pages);

					//var_dump($pages);

					if($singleItem) {
						$pages = '';
						$modified = true;
					}				
					
//					$pages = preg_replace("@(cHash=[\d\w]+[^\"])\"@", "\${1}&category={$cat}\"",$pages);
//					$pages = preg_replace("@(=[\d]+)\"@", "\${1}&category={$cat}\"",$pages);
//					$pages = preg_replace("@(html)\"@", "\${1}?category={$cat}\"",$pages);
	                                $pages = preg_replace("@><</a>@", "><img src='fileadmin/arrows/prevarr.gif' border='0'></a>", $pages);
					$pages = preg_replace("@>></a>@", "><img src='fileadmin/arrows/nextarr.gif' border='0'></a>", $pages);
					$pages = preg_replace("@(>[\d]+</a>)@", "\${1} |", $pages);
					$pages = preg_replace("@(<strong>[\d]+</strong>)@", "\${1} |", $pages);



					$priceOpts = '';
					foreach ($prices as $key=>$arr) {
						if($currentPriceIndex == $key)  {
						  $tmp = ' selected="selected" ';
						} else {
						  $tmp = '';
						}
						$priceOpts .= '<option value="'.$key."\"{$tmp}>{$arr['title']}</option>\n";
					}

					$pagingOpts ='';
					foreach ($pagings as $key=>$arr) {
						if($currentPagingIndex == $key)  {
						  $tmp = ' selected="selected" ';
						} else {
						  $tmp = '';
						}
						$pagingOpts .= '<option value="'.$key."\"{$tmp}>{$arr['title']}</option>\n";
					}

					$sortingOpts ='';
					foreach ($sortings as $key=>$arr) {
						if($currentSortingIndex == $key)  {
						  $tmp = ' selected="selected" ';
						} else {
						  $tmp = '';
						}
						$sortingOpts .= '<option value="'.$key."\"{$tmp}>{$arr['title']}</option>\n";
					}


					//var_dump($pages);

			$searchTop =<<<EOT
<style>
frmSearch_ks {
   font-size: 10px;
}
</style>
<table cellspacing="0" cellpadding="0" border="0" style="margin-top:10px;margin-bottom:10px;width:100%">
<tr>
<td>
<form method="post" enctype="multipart/form-data" class="frmSearch_ks" name="frmSearchks" id="frmSearchks">
&nbsp;&nbsp;Sort by:
<select name="sortform[sorting]" onchange="javascript:dosortchange(this);" style="font-size:10px">
{$sortingOpts}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Price:
<select name="sortform[pricerange]" onchange="javascript:dopricechange(this);" style="font-size:10px">
{$priceOpts}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Show:
<select name="sortform[pagelimit]" onchange="javascript:dolimitchange(this);" style="font-size:10px; width: 55px">
{$pagingOpts}
</select>                                      
</form>
</td>
<td>
EOT;

$afterSearchBlock =<<<EOT
</td>
</tr>
</table>
<script>

  function dosortchange(obj) {
	document.getElementById('frmSearchks').submit();
  }
  function dopricechange(obj) {
	document.getElementById('frmSearchks').submit();
  }
  function dolimitchange(obj) {
	document.getElementById('frmSearchks').submit();
  }

  function dosortchange2(obj) {
	document.getElementById('frmSearchks2').submit();
  }
  function dopricechange2(obj) {
	document.getElementById('frmSearchks2').submit();
  }
  function dolimitchange2(obj) {
	document.getElementById('frmSearchks2').submit();
  }

</script>
EOT;

					$templates['formRegTemplate'] = $news->cObj->getSubpart($templateFile, '###TEMPLATE_PRODUCTS_LIST_PAGES###');
					$templateMarkers['###PRODUCT_PAGES###'] = $pages;		
					$pageLinkz ='';
					if(!$hidePageLinks) {
						$pageLinkz = $news->cObj->substituteMarkerArrayCached($templates['formRegTemplate'], $templateMarkers);
						$pageLinkz = preg_replace("@<[\w]?[/]?(div|br[/]?)[\w]?[^>]+>@", " ",$pageLinkz);
					}

					//var_dump($pageLinkz);

					// if($searchMode && $count == 0)
					// MLC 20070627 show no results for empty results
                    // MPF 20070628 only show no result when parameters ARE passed
					if( $count == 0 && 
						(   isset($_POST['product'] ) ||  
							isset($_POST['sortform']['pricerange'] )
						)
                    )
					{
						 $content .= "<div align='center' style='font-size: 14px; color: #832A48;'>Sorry, we don't have any items that match your search at this time</div>";
					}

					else
					{
					$form =<<<EOT
<form method="post" enctype="multipart/form-data" class="frmSearch_ks" name="frmSearchks" id="frmSearchks2">
&nbsp;&nbsp;Sort by:
<select name="sortform[sorting]" onchange="javascript:dosortchange2(this);" style="font-size:10px">
{$sortingOpts}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Price:
<select name="sortform[pricerange]" onchange="javascript:dopricechange2(this);" style="font-size:10px">
{$priceOpts}
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Show:
<select name="sortform[pagelimit]" onchange="javascript:dolimitchange2(this);" style="font-size:10px; width: 55px">
{$pagingOpts}
</select>                                      
</form>
EOT;
        				$content .= '<div align="right"><table cellpadding="4" cellspacing="0" border="0" width="100%"><tr><td>'.$form.'</td><td>'.$pageLinkz.'</td></tr></table></div>';
					}

					$templates['formSearch'] = $news->cObj->getSubpart($templateFile, '###TEMPLATE_PRODUCTS_LIST_SEARCH###');
					$templateMarkers['###PRODUCTS_SEARCHBOX###'] = $searchTop.$pageLinkz.$afterSearchBlock;
					$boxSearch = $news->cObj->substituteMarkerArrayCached($templates['formSearch'], $templateMarkers);


					//$pageLinkz = str_replace('</div>', '', $pageLinkz);
					//var_dump($pageLinkz);

				        $content = $boxSearch.$content;
		                        //var_dump($content);
				}
				
        	}

		//var_dump($_SESSION);
		return '<div>'.$content.'</div>';
        	return $this->pi_wrapInBaseClass($content);
        }
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi2/class.tx_shoppingsystem_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi2/class.tx_shoppingsystem_pi2.php']);
}

?>