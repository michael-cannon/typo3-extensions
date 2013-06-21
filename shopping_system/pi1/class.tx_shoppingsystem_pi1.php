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
 * Plugin 'Products category list' for the 'shopping_system' extension.
 *
 * @author	Titka Dmitri <td@krendls.eu>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_shoppingsystem_pi1 extends tslib_pibase {
	var $prefixId = 'tx_shoppingsystem_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_shoppingsystem_pi1.php';	// Path to this script relative to the extension dir.
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
		return 'Hello World!<HR>
			Here is the TypoScript passed to the method:'.
					t3lib_div::view_array($conf);
	}

	function extraCodesProcessor($news)
	{
/*
		session_start();
		if(isset($_SESSION['savysort']['category']))	
		{				
			unset($_SESSION['savysort']['category']);	
		}		
*/
		if($news->theCode == 'PRODUCTS_CATEGORIES')
        {
			if($news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categoryMode']['vDEF'] == 0)
				$singleWhere = 'tt_news_cat.parent_category ='.$news->conf['rootCategoryUid'] . ' AND deleted<>"1" AND hidden<>"1"';
			elseif($news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categoryMode']['vDEF'] == 1)
				$singleWhere = '(tt_news_cat.parent_category='.$news->conf['rootCategoryUid'].' AND tt_news_cat.uid IN(';
			elseif($news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categoryMode']['vDEF'] == -1)
				$singleWhere = '(tt_news_cat.parent_category='.$news->conf['rootCategoryUid'].' AND tt_news_cat.uid NOT IN(';

        	$cats = explode(',', $news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categorySelection']['vDEF']);

        	if(is_array($cats))
        	{
				$templateFile = t3lib_div::getURL(t3lib_extMgm::extPath('shopping_system').'res/template.html');
				$templates['formRegTemplate'] = $news->cObj->getSubpart($templateFile, '###TEMPLATE_PRODUCTS_CATEGORIES###');

        		if($news->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['categoryMode']['vDEF'] != 0)
        		{
					for($i=0; $i<count($cats); $i++)
					{
	        			$singleWhere .= '"' . intval($cats[$i]) . '"';
	        			if($i < count($cats)-1)
	        				$singleWhere .= ', ';
					}
					$singleWhere .= ')) AND deleted<>"1" AND hidden<>"1"';
        		}

        		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tt_news_cat',
					$singleWhere);

				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
				{
					$featured_prdct = '&nbsp;';
					$featured_prdct_pic = '<img src="/uploads/tx_shoppingsystem/no_products.gif" />';

					$singleWhere = 'tt_news.uid='.$row['tx_shoppingsystem_featured_product'].' AND hidden<>1 AND deleted<>1';

					$res_featured_product = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'title, tx_shoppingsystem_product_image, tx_shoppingsystem_product_merchant_url',
					'tt_news',
					$singleWhere);

                    $rURL = $news->pi_getPageLink($row['shortcut']); // MPF 20070629 shortcut instead of single_pid

                    
					if($res_featured_product && $GLOBALS['TYPO3_DB']->sql_num_rows($res_featured_product) != 0)
					{
						$row_featured_prdct = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_featured_product);

						$featured_prdct = '<a href="'.$rURL.'" style="font-size:12px">'.$row_featured_prdct['title'].'</a>';
						$featured_prdct_pic = '<a href="'.$rURL.'" ><img src="/uploads/tx_shoppingsystem/'.$row_featured_prdct["tx_shoppingsystem_product_image"].'" style="border: 0" /></a>';
                        
                        /*
                        $featured_prdct = "<a href='".$row_featured_prdct['tx_shoppingsystem_product_merchant_url']."' target='_blank' style='font-size:12px'>".$row_featured_prdct['title']."</a>";
                        $featured_prdct_pic = "<a href='".$row_featured_prdct['tx_shoppingsystem_product_merchant_url']."' target='_blank'><img src='/uploads/tx_shoppingsystem/$row_featured_prdct[tx_shoppingsystem_product_image]' style='border: 0' /></a>";
                        */
                        
					}

					
					$img = "<img alt='".htmlentities($row['title'], ENT_QUOTES)."' border='0' src='/typo3conf/ext/shopping_system/genImage.php?text=".rawurlencode($row['title'])."&amp;typeT=5' />";
					$templateMarkers['###CATEGORY_TITLE###'] = "<a href='$rURL' style='color: black;display: block; height: 18px;' title='".htmlentities($row['title'], ENT_QUOTES)."'>$img</a>";
                    
                    
                    if ( $row['image'] )
                        $imgcat = '<img alt="'.htmlentities($row['title'], ENT_QUOTES).'" border="0" src="/uploads/pics/'.$row['image'].'" />';
                    else
                        unset($imgcat );
                    
                    
                      
                    $templateMarkers['###CATEGORY_IMAGE###'] = '<a href="'.$rURL.'" style="color: black;display: block; height: 18px;" title="'.htmlentities($row['image'], ENT_QUOTES).'">'.$imgcat.'</a><br />';
                    $templateMarkers['###CATEGORY_DESCRIPTION###'] = '<br /><a href="'.$rURL.'" style="color: #D94275; font-weight: bold; display: block; height: 18px;" title="'.htmlentities($row['description'], ENT_QUOTES).'">'.$row['description'].'</a>';
                      
                    
					$templateMarkers['###FEATURED_PIC###'] = $featured_prdct_pic;
					$templateMarkers['###FEATURED_TITLE###'] = $featured_prdct;

					$content .= $news->cObj->substituteMarkerArrayCached($templates['formRegTemplate'], $templateMarkers);
				}
        	}
        	return $this->pi_wrapInBaseClass($content);
        }
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi1/class.tx_shoppingsystem_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/shopping_system/pi1/class.tx_shoppingsystem_pi1.php']);
}

?>