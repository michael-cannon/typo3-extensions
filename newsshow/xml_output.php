<?php

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_content.php');



class Helper extends tslib_pibase
{
    var $prefixId = 'tx_ttnews'; // Same as class name
	var $extKey = 'tt_news'; // The extension key.

	//var $dbTypes = array(  'tt_news' => 'tx_ttnews', 'tx_chcforum_post' => '', 'pages' => 'pages');

    function Helper()
    {
        $this->cObj = t3lib_div::makeInstance('tslib_cObj');
    }

    function GetUrl($newsId, $pageId)
    {
        //$this->prefixId = $this->dbTypes[$db];
        //$this->extKey = $db;

        $piVarsArray['tt_news'] = $newsId;

        return $this->pi_linkTP_keepPIvars_url($piVarsArray,1,1,$pageId);
        //return $this->cObj->lastTypoLinkUrl;

        //return $this->pi_getPageLink($newsPage, '');
    }
}


    /**
     * Creates XML output for Flash.
     *
     * This function creates an XML output wich can be used by a SWF movie.
     *
     * @return      The XML content.
     */
    function writeXML() {

        // XML Storage
        $xml = array();

        $xml[] = '<playlist version="1" xmlns="http://xspf.org/ns/0/">';

        $xml[] = '<trackList>';

        $side = t3lib_div::_GP('side');

        $main = t3lib_div::_GP('main');

        $docRoot = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT');

        $uploadSideDir = str_replace($docRoot,'',t3lib_div::getFileAbsFileName('uploads/pics'));
        $uploadDir = str_replace($docRoot,'',t3lib_div::getFileAbsFileName('uploads/tx_cabnewsmultipleimages/'));

        if (strrpos($uploadDir,'/') != strlen($uploadDir) - 1)
        {
            $uploadDir .= '/';
        }

        if (strrpos($uploadSideDir,'/') != strlen($uploadSideDir) - 1)
        {
            $uploadSideDir .= '/';
        }

        // cbDebug( 'main', $main );

        $lnk = new Helper;

        $items = array();

        if(isset($main['news']) && $main['news'] != '')
        {
            $news = explode(',', $main['news']);

            $news = array_map('intval', $news);

            for($i = 0; $i < count($news); ++$i)
            {
                $items[] = GetNewsRow($news[$i]);
            }
        }

		// cbDebug( 'File ' . __FILE__, 'Line ' . __LINE__ );	
		// cbDebug( 'items', $items );

		// MLC 20070621 check for items already populated
        if( 0 == count( $items ) && isset($main['cat_mode']))
        {
            $cat = ExplodeInt($main['cat']);
            $cat = GetCatChildrens($cat);

			// MLC 20070621 cat 38 Today in savvy doesn't have single pid but is
			// used for selecting content

			// MLC todo cycle through selected categories to look for those
			// without single_pid to create sub-select specifically for them
			$noSinglePid		= 38;
			$hasNoSinglePid		= false;
			$noSinglePidSql		= '';

			if ( in_array( $noSinglePid, $cat ) )
			{
				$hasNoSinglePid	= true;
				unset( $cat[ array_search( $noSinglePid, $cat ) ] );
			}

            switch($main['cat_mode'])
            {
                case 0:
				case 0 == count( $cat ):
                    $cat = " 1 ";
                    break;
                case 1:
                    $cat = "c.uid IN('".implode("','", $cat)."')";
                    break;
                case -1:
                    $cat = "c.uid NOT IN('".implode("','", $cat)."')";
                    break;
                default:
            }

            $sort = array
            (
                'datetime',
                'archivedate',
                'author',
                'title',
                'type',
                'random',
                'tx_newsreadedcount_readedcounter'
            );

            $order = array('asc', 'desc');

            if(array_search($main['asc'], $order) === false)
                $order = 'DESC';
            else
                $order = $main['asc'];


            if(array_search($main['sort'], $sort) === false || $main['sort'] == 'random')
                $sort = '';
            else
                $sort = "n.{$main['sort']} $order";

            if(isset($main['show_last']))
            {
                $limit = intval($main['show_last']);
            }

            if(isset($main['days']))
            {
                $limit = 7;
            }

			if ( $hasNoSinglePid )
			{
				$noSinglePidSql	= ' AND n.uid IN (';
				$noSinglePidSql	.= $GLOBALS['TYPO3_DB']->SELECTquery( 'n.uid'
									, 'tt_news n
							LEFT JOIN tt_news_cat_mm m ON n.uid = m.uid_local
							LEFT JOIN tt_news_cat c ON m.uid_foreign = c.uid'
									, " 1 = 1
									AND m.uid_foreign IN ( $noSinglePid )
									AND n.datetime <= UNIX_TIMESTAMP()"
									, ''
									, $sort
									, ''
								);
				$noSinglePidSql	.= " )
									AND m.uid_foreign NOT IN ( $noSinglePid )";
			}

            $query = $GLOBALS['TYPO3_DB']->SELECTquery(
                                    'n.uid
										, n.title
										, from_unixtime(n.datetime)
										, n.type
										, n.page
										, n.ext_url
										, n.tx_cabnewsmultipleimages_directimages as big_image
										, c.single_pid
									' 
									,'tt_news n
										LEFT JOIN tt_news_cat_mm m ON n.uid = m.uid_local
										LEFT JOIN tt_news_cat c ON m.uid_foreign = c.uid
									'
									, "$cat
                                     AND ( c.single_pid != 0
									 	OR n.type IN ( 1, 2 )
									)
                                     AND n.hidden = 0
									 AND n.tx_cabnewsmultipleimages_directimages != ''
									AND n.datetime <= UNIX_TIMESTAMP()
									 $noSinglePidSql
									 "
                                     , 'n.uid'
									 , $sort
									 , $limit
								 );

			// cbDebug( 'query', $query );	

            $res = $GLOBALS['TYPO3_DB']->sql_query( $query );

            while(($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))
            {
                if(!isset($items['uid'.$item['uid']]))
                    $items['uid'.$item['uid']] = $item;
            }

            if($main['sort'] == 'random')
            {
                foreach($items as $item)
                {
                    $res['item' . rand(0, 100000)] = $item;
                }

                ksort($res);
                $items = $res;
            }
        }

        if(isset($main['days']))
        {
            $days = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

            $items = array_slice($items, 0, 7);

            $day = (int)date('w');

			// MLC 20070621 don't resort manual news selection
			if( !isset($main['news']) )
			{
				$res = array();

				$i = 0;
				foreach($items as $item)
				{
					$ind = $day - $i;
					$res[($ind >= 0)?$ind:(7 + $ind)] = $item;
					++$i;
				}

				ksort($res);

				$items = $res;
			}

            $d = 0;
            foreach($items as $i=>$item)
            {
                $items[$i]['day'] = $days[$d++];
            }
        }

        foreach($items as $item)
        {
            if(!$item) continue;

            $xml[] = '<track>';

            if($item['uid'])
            {
                $link = $lnk->GetUrl($item['uid'], $item['single_pid']);
                $GLOBALS["tx_cabuniquenews_pi1"]["displayednews"][] = $item["uid"];
            }
            else
                $link = '';

            // MPF  20070629 Add support for extenal and internal news page [begin]    
			// cbDebug( 'item', $item );	
			// MLC 20070703 prevent link if desired
            switch ( $item['type'] )
			{
                case 0:
                // the link is ok
                break;   

                case 1:
					// MLC 20070703 todo fix hack for page link
                    $link		= $item['page']
									// ? $this->pi_getPageLink( $item['page'])
									? '/index.php?id=' . $item['page']
									: '';
					break;

                case 2:
                    $link		= $item['ext_url']
									? $item['ext_url']
									: '';
                break;
            }
            // MPF  20070629 Add support for extenal and internal news page [end]    
			// cbDebug( 'item', $item );	
			// cbDebug( 'link', $link );	

            if($item['big_image'])
            {
                $image = explode(',', $item['big_image']);
                $image = $uploadDir . $image[0];
            }
            else
                $image = '';

            if(isset($item['day']))
                $xml[] = '<creator>' . $item['day'] . '</creator>';

            $xml[] = '<location>' . $image . '</location>';
            $xml[] = '<info>' . $link . '</info>';

            $xml[] = '</track>';
        }

        $items = array();

        $items[] = array('single_pid' => $side['bottom'], 'image' => '', 'title' => $side['bottom_title']);//GetNewsRow($side['bottom']);
        $items[] = GetContent($side['middle']);
        $items[] = GetContent($side['top']);


        //print_r($items);

        if (array_search(false, $items) === false)
        {

            foreach($items as $item)
            {

                $xml[] = '<track>';

                if(!isset($item['link']))
                    $link = $lnk->GetUrl($item['uid'], $item['single_pid']);
                else
                    $link = $item['link'];

                $image = explode(',', $item['image']);

                $xml[] = '<title>' . $item['heading'] .'|'. $item['title'] . '</title>';
                $xml[] = '<location>' . $uploadSideDir . $image[0] . '</location>';
                $xml[] = '<info>' . $link . '</info>';

                $xml[] = '</track>';
            }
        }




        $xml[] = '</trackList>';

        $xml[] = '</playlist>';

        return implode(chr(10),$xml);
    }

    // Include Developer API class
    include_once(t3lib_extMgm::extPath('api_macmade') . 'class.tx_apimacmade.php');

    // Write XML
    echo writeXML();

    // Exit
    exit();


    function ExplodeInt($arr)
    {
        return array_map('intval', explode(',', $arr));
    }

    function GetCatChildrens($cat)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                    'c.uid'
                                    ,'tt_news_cat c',
                                    "c.parent_category  IN ('".implode("','", $cat)."')" );

        $childs = array();
        while($child = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
        {
            $childs[] = $child['uid'];
        }

        if(count($childs))
            array_splice($childs, count($childs), 0, GetCatChildrens($childs));

        array_splice($childs, count($childs), 0, $cat);

        return array_unique($childs);
    }

    function GetNewsRow($uid)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                    'n.uid
									, n.type
									, n.page
									, n.ext_url
									, n.title
									, n.image
									, n.tx_cabnewsmultipleimages_directimages AS big_image
									, n.pid'
                                    ,'tt_news n',
                                    "
                                        n.uid = '$uid'" );

        $news = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                    'c.single_pid, c.uid'
                                    ,'tt_news_cat c, tt_news_cat_mm m',
                                    "   m.uid_local = '{$news['uid']}'
                                        AND
                                        m.uid_foreign = c.uid
                                        AND
                                        c.single_pid != 0", '', 'm.sorting ASC' );

        $cat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        if(!$cat)
        {
            $main = t3lib_div::_GP('main');
            $news['single_pid'] = 208;//$main['blogsID']; // pid for blogs
            return $news;
        }

        $cat2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        if(!$cat2)
        {
            $news['single_pid'] = $cat['single_pid'];
            $news['c_uid'] = $cat['uid'];
            return $news;
        }

        $news['single_pid'] = $cat2['single_pid'];
        $news['c_uid'] = $cat2['uid'];
        return $news;

    }

    function GetForumRow($uid)
    {

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                    'thread_subject as title, uid , category_id, conference_id'
                                    ,'tx_chcforum_thread',
                                    "uid = '$uid'" );

        $post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        $post['single_pid'] = 0;

        $post['link'] = "/your-stories/view/single_thread/chc-forum/".$post["category_id"]."/".$post["conference_id"]."/".$post["uid"]. ".html";

        return $post;
    }

    function GetPageRow($uid)
    {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                                    'title'
                                    ,'pages',
                                    "uid = '$uid'" );

        $post = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        $post['single_pid'] = $uid;
        $post['image'] = '';

        return $post;

    }

    function GetContent($item)
    {
        $res = array();
        if(sscanf($item['uid'], "tt_news_%d", $uid))
        {
            $res = GetNewsRow($uid);
        }
        elseif(sscanf($item['uid'], "pages_%d", $uid))
        {
            $res = GetPageRow($uid);
        }
        elseif(sscanf($item['uid'], "tx_chcforum_thread_%d", $uid))
        {
            $res = GetForumRow($uid);
        }

        unset($item['uid']);

        foreach($item as $k=>$val)
        {
            if(strlen($val))
            {
                $res[$k] = $val;
            }
        }

        return $res;
    }
?>
