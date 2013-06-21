<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005 Anton (anton@bcs-it.com)
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
 * Plugin 'Application Note' for the 'piap_appnote' extension.
 *
 * @author    Anton <anton@bcs-it.com>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_piapappnote_pi1 extends tslib_pibase {
    // Same as class name
    var $prefixId = 'tx_piapappnote_pi1';
    // Path to this script relative to the extension dir
    var $scriptRelPath = 'pi1/class.tx_piapappnote_pi1.php';
    // The extension key.
    var $extKey = 'piap_appnote';
    // The backReference to the mother cObj object set at call time
    var $cObj;
    // Special status priority mapping from database values to english
    var $arrStatus = array();
    // The template
    var $templateCode;
    // Filters
    var $categoryFilter = null;
    var $versionFilter = null;
    var $deviceFilter = null;
    var $lastXDays = null;
    var $maxNotesToShow = null;
    // whether we're actually searching (have given search parameters)
    var $isSearching = false;
    // list, detail or search
    var $mode = null;
    // sort parameters
    var $sortfield = null;
    var $sortorder = null;
    // detail view
    var $noteid = null;
    // file to download
    var $file = null;
    // the details page id to link to for detail view
    var $detailspageid = null;
    // the list page id to use in details view case
    var $listpageid = null;

    /**
     * Main function
     */
    function main($content,$conf)    {
        $this->pi_USER_INT_obj = 1;
        $this->conf = $conf;

        global $TSFE;

        if (!array_key_exists('mode', $this->piVars))
            $this->piVars['mode'] = 'list';
        if (!in_array($this->piVars['mode'], array('list', 'detail', 'search')))
            $this->piVars['mode'] = 'list';
        $this->mode = $this->piVars['mode'];

        if (is_array($this->piVars['typeS'])) {
            if (array_key_exists("categories", $this->piVars['typeS'])) {
                $this->piVars['typeS']['categorylist'] = implode(",", $this->piVars['typeS']['categories']);
                unset($this->piVars['typeS']['categories']);
            }
            if (array_key_exists("devices", $this->piVars['typeS'])) {
                $this->piVars['typeS']['devicelist'] = implode(",", $this->piVars['typeS']['devices']);
                unset($this->piVars['typeS']['devices']);
            }
            if (array_key_exists("versions", $this->piVars['typeS'])) {
                $this->piVars['typeS']['versionlist'] = implode(",", $this->piVars['typeS']['versions']);
                unset($this->piVars['typeS']['versions']);
            }
        }
            
        switch ($this->mode) {
            case 'list':
            case 'search':
                $this->piVars["pointer"] = $this->dict_get($this->piVars, "pointer", "0");
                if (!is_numeric($this->piVars["pointer"]))
                    $this->piVars["pointer"] = '0';
                $TSFE->fe_user->setKey('ses', sprintf('tx_piapappnote_pointer%d', $this->cObj->data['pid']),
                                       $this->piVars['pointer']);

                $this->sortfield = $this->conf['defaultSortField'];
                $this->sortorder = $this->conf['defaultSortOrder'] == 'ascending' ? 1 : 0;

                if (array_key_exists('file', $this->piVars) and strpos($this->piVars['file'], ":") !== false) {
                    list($this->sortfield, $this->sortorder) =
                        explode(":", $this->piVars['file']);
                    unset($this->piVars['file']);
                } else {
                    $sf = $TSFE->fe_user->getKey('ses', sprintf('tx_piapappnote_sortfield%d', $this->cObj->data['pid']));
                    $so = $TSFE->fe_user->getKey('ses', sprintf('tx_piapappnote_sortorder%d', $this->cObj->data['pid']));
                    if (!is_null($sf)) {
                        $this->sortfield = $sf;
                        $this->sortorder = $so;
                    }
                }
                $TSFE->fe_user->setKey('ses', sprintf('tx_piapappnote_sortfield%d', $this->cObj->data['pid']),
                                       $this->sortfield);
                $TSFE->fe_user->setKey('ses', sprintf('tx_piapappnote_sortorder%d', $this->cObj->data['pid']),
                                       $this->sortorder);
                if (is_numeric($this->cObj->data['tx_piapappnote_details_page'])
                        && $this->cObj->data['tx_piapappnote_details_page'] != $this->cObj->data['pid']) {
                    $this->detailspageid = $this->cObj->data['tx_piapappnote_details_page'];
                    $this->listpageid = $this->cObj->data['pid'];
                }
                break;
            case 'detail':
                $this->noteid = $this->dict_get($this->piVars, 'pointer', "");
                $file_or_listpid_or_sort = $this->dict_get($this->piVars, 'file');
                if (is_numeric($file_or_listpid_or_sort)) {
                    $this->detailspageid = $this->cObj->data['pid'];
                    $this->listpageid = $file_or_listpid_or_sort;
                } else if (strpos($file_or_listpid_or_sort, ".") !== false) {
                    $this->file = $file_or_listpid_or_sort;
                }
        }

        global $TYPO3_DB;
        if (!is_null($this->file)) {
            $extension = substr($this->file, -3);
            if (in_array($extension, array('zip', 'pdf'))) {
                $fields = array('tstamp' => time(),
                                'crdate' => time(),
                                'file'   => $this->file,
                          );
                $TYPO3_DB->exec_INSERTquery(sprintf('tx_piapappnote_%ss', $extension), $fields);
                header(sprintf("Location: %suploads/tx_piapappnote/%s",
                               $TSFE->config['config']['baseURL'], $this->file));
                return;
            }
        }

        $this->templateCode = $this->cObj->fileResource($this->conf["templateFile"]);

        if ($this->templateCode == '')
            $this->templateCode = '<!-- ###LISTVIEW### begin -->No template set. Set <tt>plugin.tx_piapappnote_pi1.templateFile</tt> to point to a template file.<!-- ###LISTVIEW### end -->';

        global $TCA;
        t3lib_div::loadTCA('tx_piapappnote_notes');
        $LL = $this->includeLocalLang();
        foreach ($TCA['tx_piapappnote_notes']['columns']['specialpriority']['config']['items'] as $item)
            // FIXME: this is a bad hack
            $this->arrStatus[$item[1]] = $LL['default'][substr($item[0], 38)];

        if ($this->mode == 'detail') {
            $this->internal['currentTable'] = 'tx_piapappnote_notes';
            $res = $TYPO3_DB->sql_query(sprintf("
                SELECT
                    *
                FROM
                    tx_piapappnote_notes
                WHERE
                    LOWER(noteid) = %s
                    AND deleted = 0
                    AND hidden = 0
                ", $TYPO3_DB->fullQuoteStr($this->noteid, '')));
            if ($TYPO3_DB->sql_num_rows($res) == 0) {
                header("HTTP/1.0 404 Not Found");
                print("<h1>Not Found</h1>");
                exit();
            }
            $this->internal['currentRow'] = $TYPO3_DB->sql_fetch_assoc($res);
            return $this->pi_wrapInBaseClass($this->singleView());
        }

        // list or advanced search
        $this->categoryFilter = $this->my_explode(",", $this->cObj->data["tx_piapappnote_categories"]);
        $this->versionFilter = $this->my_explode(",", $this->cObj->data["tx_piapappnote_versions"]);
        $this->deviceFilter = $this->my_explode(",", $this->cObj->data["tx_piapappnote_devices"]);
        $this->lastXDays = $this->cObj->data['tx_piapappnote_published_in_the_last_x_days'];
        $this->maxNotesToShow = $this->cObj->data['tx_piapappnote_max_notes_to_show'];

        return $this->pi_wrapInBaseClass($this->listView($content));
    }

    // get the value at $key out of the associative array $dict.
    // if the dict doesn't have that key, return $default.
    function dict_get(&$dict, $key, $default = null)
    {
        if (!array_key_exists($key, $dict))
            return $default;
        return $dict[$key];
    }

    /* like explode, but returns null if the input string is empty */
    function my_explode($sep, $s)
    {
        if ($s == '')
            return null;
        return explode($sep, $s);
    }

    /* like explode, but returns an empty array if the input string is empty */
    function my_explode2($sep, $s)
    {
        if ($s == '')
            return array();
        return explode($sep, $s);
    }

    /* like split, but returns an empty array if the input string is empty
     * instead of the brain dead array('') */
    function my_split($sep, $s)
    {
        if ($s == '')
            return array();
        return split($sep, $s);
    }

    function includeLocalLang()
    {
        include(t3lib_extMgm::extPath($this->extKey, 'locallang_db.php'));
        return $LOCAL_LANG;
    }

    /**
     * Draw the table
     */
    function listView($content)    {
        global $TYPO3_DB;
        // Loading the LOCAL_LANG values
        $this->pi_loadLL();

        $lConf = $this->conf['listView.'];    // Local settings for the listView function

        // Number of results to show in a listing.
        if ($this->maxNotesToShow > 0)
            $this->internal['results_at_a_time'] = $this->maxNotesToShow;
        else
            $this->internal['results_at_a_time'] = t3lib_div::intInRange($lConf['results_at_a_time'], 0, 1000, 3);
        // The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.($lConf['maxPages'])
        $this->internal['maxPages'] = t3lib_div::intInRange($lConf['maxPages'], 0, 1000, 2);

        // Search
        $where = array();
        if (is_array($this->piVars['typeS'])) {
            $searchFields = array();
            foreach ($this->piVars['typeS'] as $field => $value) {
                if (in_array($field, array('title', 'author', 'noteid', 'description'))) {
                    if ($value != 'Y')
                        continue;
                    if ($this->piVars['sword'] == "")
                        continue;
                    $searchFields[] = $field;
                }
                if (in_array($field, array('categorylist', 'versionlist', 'devicelist'))) {
                    $value = explode(",", $value);
                    if (!is_array($value))
                        continue;

                    if ($field == 'versionlist')
                        $field = 'versions';
                    if ($field == 'categorylist')
                        $field = 'categories';
                    if ($field == 'devicelist')
                        $field = 'devices';

                    if ($field == 'versions')
                        $value = $this->addChildVersion($value);

                    $uids = array();
                    if (!in_array(0,$value))    {
                        foreach ($value as $uid) {
                            if (!is_numeric($uid))
                                continue;
                            $uid = intval($uid);
                            $uids[] = "($field = '$uid' OR $field LIKE '$uid,%' OR $field LIKE '%,$uid,%' OR $field LIKE '%,$uid')";
                        }
                    } else {
                        $uids[] = "($field LIKE '%')";
                    }
                    $where[] = '('.implode(" OR ", $uids).')';
                }
            }
            $searchWords = $this->my_split('[, ]', $this->piVars['sword']);
            $conds = array();
            foreach ($searchWords as $searchWord) {
                $wordconds = array();
                foreach ($searchFields as $searchField) {
                    $wordconds[] = sprintf("%s LIKE '%%%s%%'", $searchField, $TYPO3_DB->quoteStr($searchWord, ''));
                }
                $conds[] = '(' . implode(" OR ", $wordconds) . ')';
            }
            if (count($conds) > 0)
                $where[] = '('.implode(" AND ", $conds).')';
        }

        $this->isSearching = count($where) > 0;
        // Don't show app notes on advanced search page if there were no search parameters given
        if (!$this->isSearching and ($this->mode == 'search'))
            $where[] = '1 = 0';

        // Apply filter
        $this->applyFilter("categories", $this->categoryFilter, $where);
        $this->applyFilter("versions", $this->versionFilter, $where);
        $this->applyFilter("devices", $this->deviceFilter, $where);

        // Apply date filter
        if ($this->lastXDays > 0)
            $where[] = sprintf('unix_timestamp(current_timestamp) - datetime < %d * 60 * 60 * 24', $this->lastXDays);

        if (count($where) > 0)
            array_unshift($where, '');

        $orderby = null;
        if (($this->lastXDays > 0) || ($this->maxNotesToShow > 0))
            $orderby = 'datetime DESC';
        else
            switch($this->sortfield) {
                case 'noteid':
                case 'datetime':
                case 'author':
                    $orderby = sprintf("%s %s", $this->sortfield, $this->sortorder == '1' ? '' : 'DESC');
                    break;
                default:
                case 'title':
                    if ($this->sortorder == 1)
                        $orderby = 'case when specialstart <= unix_timestamp() and specialend >= unix_timestamp() then specialpriority else 0 end desc, title asc';
                    else
                        $orderby = 'case when specialstart <= unix_timestamp() and specialend >= unix_timestamp() then specialpriority else 0 end asc, title desc';
                    break;
            }

        $this->conf['pidList'] = $this->cObj->data['pages'];
        $this->conf['recursive'] = $this->cObj->data['recursive'];

        // Get number of records:
        $res = $this->pi_exec_query('tx_piapappnote_notes', 1, implode(" AND ", $where));
        list($this->internal['res_count']) = $TYPO3_DB->sql_fetch_row($res);

        if ($this->internal['res_count'] == 0) {
            if (!$this->isSearching and ($this->mode == 'search'))
                $htmlItems = "";
            else
                $htmlItems = $this->cObj->getSubpart($this->templateCode, '###EMPTY_LIST###');
        } else {
            // Make listing query, pass query to SQL database:
            $res = $this->pi_exec_query('tx_piapappnote_notes', 0, implode(" AND ", $where), '', '', $orderby);
            $this->internal['currentTable'] = 'tx_piapappnote_notes';

            $htmlItems = "";
            $rowcount = 0;
            while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
                if (($rowcount % 2) == 0)
                    $templateItem = $this->cObj->getSubpart($this->templateCode, '###EVEN_ROW###');
                else
                    $templateItem = $this->cObj->getSubpart($this->templateCode, '###ODD_ROW###');
                $htmlItems .= $this->renderItem($templateItem, $row);
                $rowcount++;
            }
        }

        $templateSimpleSearchForm = $this->cObj->getSubpart($this->templateCode, '###SIMPLE_SEARCH_FORM###');
        $templateAdvancedSearchForm = $this->cObj->getSubpart($this->templateCode, '###ADVANCED_SEARCH_FORM###');
        if ($this->mode == 'search') {
            $htmlAdvancedSearchForm = $this->getSearchForm($templateAdvancedSearchForm);
        } else {
            $htmlSimpleSearchForm = $this->getSearchForm($templateSimpleSearchForm);
        }

        $this->internal['dontLinkActivePage'] = true; 
        $this->internal['pagefloat'] = 'center'; 
        $marker = array('###SORT_BY_NUMBER_URL###'          => $this->getSortByFieldURL('noteid'),
                        '###SORT_BY_TITLE_URL###'           => $this->getSortByFieldURL('title'),
                        '###SORT_BY_DATE_PUBLISHED_URL###'  => $this->getSortByFieldURL('datetime'),
                        '###SORT_BY_AUTHOR_URL###'          => $this->getSortByFieldURL('author'),
                        '###NAVIGATION_BAR###'              => $this->pi_list_browseresults(0),
                  );
        $subpartmarker = array('###ADVANCED_SEARCH_FORM###' => $htmlAdvancedSearchForm,
                               '###SIMPLE_SEARCH_FORM###'   => $htmlSimpleSearchForm,
                               '###LIST_ITEM###'            => $htmlItems,
                               '###EMPTY_LIST###'           => '',
                         );

        $templateListView = $this->cObj->getSubpart($this->templateCode, '###LISTVIEW###');
        return $this->cObj->substituteMarkerArrayCached($templateListView, $marker, $subpartmarker);
    }

    // add all child versions
    function addChildVersion($ids)
    {
        $newids = array();
        foreach ($ids as $id)
            $newids[] = $id;
        $newids = array_merge($newids, $this->getChildVersions($id));
        return $newids;
    }

    function getChildVersions($id)
    {
        $children = array();
        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf('
            SELECT
                uid
            FROM
                tx_piapappnote_versions
            WHERE
                childof = %s
                AND hidden = 0
                AND deleted = 0
            ', $id));
        while ($row = $TYPO3_DB->sql_fetch_row($res)) {
            $children[] = $row[0];
            $children = array_merge($children, $this->getChildVersions($row[0]));
        }
        return $children;
    }

    /*
     * Add contitions to the where conditions array. $filter contains a list of ids that should be in field $field.
     */
    function applyFilter($field, $filter, &$where)
    {
        if (is_array($filter)) {
            $catcond = array();
            foreach ($filter as $id)
                $cond[] = "($field LIKE '$id' OR $field LIKE '$id,%' OR $field LIKE '%,$id,%' OR $field LIKE '%,$id')";
            $where[] = '('.implode(" OR ", $cond).')';
        }
    }

    /**
     * Get a date nicely formatted
     */
    function getDateFormatted($date)
    {
        if ($date == 0)
            return '';
        return strftime('%b %d, %Y', $date);
    }

    /**
     * Get a time nicely formatted
     */
    function getTimeFormatted($time)
    {
        if ($time == 0)
            return '';
        return strftime('%I:%M %p', $time);
    }

    // Get a human readable filesize
    function getHumanReadableFilesize($fsize)
    {
        if ($fsize < 1024)
            return "$fsize Bytes";
        if ($fsize < 1024*1024)
            return round($fsize / 1024, 1) . " KBytes";
        return round($fsize / 1024 / 1024, 1) . " MBytes";
    }

    /**
     * Draw tne concret Note
     */
    function singleView()    {
        global $TYPO3_DB;
        $this->pi_loadLL();
        $row = $this->internal['currentRow'];

        // This sets the title of the page for use in indexed search results:
        if ($row['title'])
            $GLOBALS['TSFE']->indexedDocTitle = $row['title'];

        $templateSingleView = $this->cObj->getSubpart($this->templateCode, '###SINGLEVIEW###');
        return $this->renderItem($templateSingleView, $row);
    }

    // Return a real link from a Typo3 link field
    function getLink($link)
    {
        global $TSFE;
        if (is_numeric($link)) {
            $piVarsSave = $this->piVars;
            unset($this->piVars);
            $retlink = $this->pi_linkTP_keepPIvars_url(array(), 0, 0, $link);
            $this->piVars = $piVarsSave;
            return $retlink;
        }
        if (strstr($link, "@"))
            return sprintf("mailto:%s", $link);
        if (strstr($link, "://"))
            return $link;
        if ($this->startswith($link, "fileadmin/"))
            return $TSFE->config['config']['baseURL'] . $link;
        return sprintf("http://%s", $link);
    }

    function startswith($s, $prefix)
    {
        return substr($s, 0, strlen($prefix)) == $prefix;
    }

    function renderItem($template, $row)
    {
        global $TYPO3_DB;
        $templateZipFiles = $this->cObj->getSubpart($template, '###ZIP_FILES###');
        $templateCategories = $this->cObj->getSubpart($template, '###CATEGORIES###');
        $templateRelatedNotes = $this->cObj->getSubpart($template, '###RELATED_NOTES###');
        $templateDevices = $this->cObj->getSubpart($template, '###DEVICES###');

        // construct HTML for ZIP Files
        $htmlZipFiles = '';
        $zipfiles = t3lib_div::trimExplode(",", $row['zipfiles'], 1);
        foreach ($zipfiles as $zipfile) {
            $htmlZipFile = $templateZipFiles;
            $zipsize = $this->getHumanReadableFilesize(filesize(PATH_site . "uploads/tx_piapappnote/" . $zipfile));
            $htmlZipFile = $this->cObj->substituteMarker($htmlZipFile, '###ZIP_SIZE###', $zipsize);
            $savePiVars = $this->piVars;
            $this->piVars = array("mode" => "detail", "pointer" => $savePiVars['pointer']);
            $htmlZipFile = $this->cObj->substituteMarker($htmlZipFile, '###ZIP_URL###', 
                    $this->pi_linkTP_keepPIvars_url(array("file" => $zipfile)));
            $this->piVars = $savePiVars;
            $htmlZipFile = $this->cObj->substituteMarker($htmlZipFile, '###ZIP_FILENAME###', $zipfile);
            list($downloads) = $TYPO3_DB->sql_fetch_row($TYPO3_DB->sql_query(
                        'SELECT COUNT(1) FROM tx_piapappnote_zips WHERE file = '
                        . $TYPO3_DB->fullQuoteStr($zipfile, 'tx_piapappnote_zips')));
            $htmlZipFile = $this->cObj->substituteMarker($htmlZipFile, '###ZIP_DOWNLOADS###', $downloads);
            $htmlZipFiles .= $htmlZipFile;
        }

        // construct HTML for categories
        $htmlCategories = '';
        $categories = t3lib_div::trimExplode(",", $row['categories'], 1);

        foreach ($categories as $category) {
            $htmlCategory = $templateCategories;
            $category = $this->pi_getRecord('tx_piapappnote_categories', intval($category));
            $htmlCategory = $this->cObj->substituteMarker($htmlCategory, '###CATEGORY###', $category['title']);
            $htmlCategories .= $htmlCategory;
        }

        // construct HTML for related notes
        $relatedNoteIds = $row['related_appnotes'];
        $htmlRelatedNotes = "";
        if ($relatedNoteIds != "") {
            $relatedNotes = $TYPO3_DB->exec_SELECTgetRows('uid, noteid, title',
                                                          'tx_piapappnote_notes',
                                                          'uid IN ('.$relatedNoteIds.')',
                                                          '', '', '', 'uid');
            $relatedNoteIds = t3lib_div::trimExplode(",", $relatedNoteIds, 1);
            foreach ($relatedNoteIds as $relatedNote) {
            $htmlRelatedNote = $templateRelatedNotes;
            $htmlRelatedNote = $this->cObj->substituteMarker($htmlRelatedNote,
                '###RELATEDNOTE_URL###', 
                $this->pi_linkTP_keepPIvars_url(array("pointer" => strtolower($relatedNotes[$relatedNote]["noteid"]))));
            $htmlRelatedNote = $this->cObj->substituteMarker($htmlRelatedNote,
                '###RELATEDNOTE_ID###', $relatedNotes[$relatedNote]["noteid"]);
            $htmlRelatedNote = $this->cObj->substituteMarker($htmlRelatedNote,
                '###RELATEDNOTE_TITLE###', $relatedNotes[$relatedNote]["title"]);
            $htmlRelatedNotes .= $htmlRelatedNote;
            }
        }

        // construct HTML for devices
        $htmlDevices = '';
        $devices = t3lib_div::trimExplode(",", $row['devices'], 1);

        foreach ($devices as $device) {
            $htmlDevice = $templateDevices;
            $device = $this->pi_getRecord('tx_piapappnote_devices', intval($device));
            $htmlDevice = $this->cObj->substituteMarker($htmlDevice, '###DEVICE_TITLE###', $device['title']);
            $htmlDevice = $this->cObj->substituteMarker($htmlDevice, '###DEVICE_URL###', $this->getLink($device['link']));
            $htmlDevices .= $htmlDevice;
        }

        $pdffile = $row['pdffile'];
        if ($pdffile != "") {
            list($pdfdownloads) = $TYPO3_DB->sql_fetch_row($TYPO3_DB->sql_query(
                        'SELECT COUNT(1) FROM tx_piapappnote_pdfs WHERE file = '
                        . $TYPO3_DB->fullQuoteStr($pdffile, '')));
            $pdfsize = $this->getHumanReadableFilesize(filesize(PATH_site . "uploads/tx_piapappnote/" . $pdffile));
        }

        $version = $this->pi_getRecord('tx_piapappnote_versions', intval($row['versions']));
        $version = $version['title'];

        $singleviewparams = array('mode'    => 'detail',
                                  'pointer' => strtolower($row['noteid']),
                            );
        if (!is_null($this->detailspageid)) {
            $singleviewparams['file'] = $this->listpageid;
            $singleviewurl = $this->pi_linkTP_keepPIvars_url($singleviewparams, 0, 0, $this->detailspageid);
        } else {
            $singleviewurl = $this->pi_linkTP_keepPIvars_url($singleviewparams);
        }

        global $TSFE;
        $backlinkparams = array('mode' => 'list',
                                'pointer' => $TSFE->fe_user->getKey('ses', sprintf('tx_piapappnote_pointer%d',
                                                                    $this->cObj->data['pid'])),
                          );
        $piVarsSave = $this->piVars;
        unset($this->piVars['file']);
        if (!is_null($this->listpageid))
            $backlinkurl = $this->pi_linkTP_keepPIvars_url($backlinkparams, 0, 0, $this->listpageid);
        else
            $backlinkurl = $this->pi_linkTP_keepPIvars_url($backlinkparams);

        $this->piVars = array("mode" => "detail", "pointer" => $savePiVars['pointer']);
        $pdfurl = $this->pi_linkTP_keepPIvars_url(array("file" => $row['pdffile']));
        $this->piVars = $piVarsSave;

        $marker = array('###ID###'                        => $row['uid'],
                        '###NUMBER###'                    => $row['noteid'],
                        '###TITLE###'                     => (strlen($row['title']) > 0 ?
                                                              $row['title'] : $this->conf['noTitleText']),
                        '###SINGLEVIEW_URL###'            => $singleviewurl,
                        '###DESCRIPTION###'               => $row['description'],
                        '###DATE_PUBLISHED###'            => $this->getDateFormatted($row['datetime']),
                        '###TIME_PUBLISHED###'            => $this->getTimeFormatted($row['datetime']),
                        '###AUTHOR###'                    => $row['author'],
                        '###PDF_URL###'                   => $pdfurl,
                        '###PDF_FILENAME###'              => $pdffile,
                        '###PDF_SIZE###'                  => $pdfsize,
                        '###PDF_DOWNLOADS###'             => $pdfdownloads,
                        '###SOFTWARE_VERSION###'          => $version,
                        '###SPECIAL_STATUS_START_DATE###' => $this->getDateFormatted($row['specialstart']),
                        '###SPECIAL_STATUS_START_TIME###' => $this->getTimeFormatted($row['specialstart']),
                        '###SPECIAL_STATUS_END_DATE###'   => $this->getDateFormatted($row['specialend']),
                        '###SPECIAL_STATUS_END_TIME###'   => $this->getTimeFormatted($row['specialend']),
                        '###SPECIAL_STATUS_PRIORITY###'   => $this->arrStatus[$row['specialpriority']],
                        '###LAST_UPDATED_DATE###'         => $this->getDateFormatted($row['tstamp']),
                        '###LAST_UPDATED_TIME###'         => $this->getTimeFormatted($row['tstamp']),
                        '###CREATED_DATE###'              => $this->getDateFormatted($row['crdate']),
                        '###CREATED_TIME###'              => $this->getTimeFormatted($row['crdate']),
                        '###BACKLINK_URL###'              => $backlinkurl,
                  );

        $subpartmarker = array('###ZIP_FILES###'     => $htmlZipFiles,
                               '###CATEGORIES###'    => $htmlCategories,
                               '###RELATED_NOTES###' => $htmlRelatedNotes,
                               '###DEVICES###'       => $htmlDevices,
                         );

        $content = $this->cObj->substituteMarkerArrayCached($template, $marker, $subpartmarker);

        return $content;
    }

    /**
     * Search Form
     */
    function getSearchForm($template)
    {
        $templateCategories = $this->cObj->getSubpart($template, '###CATEGORIES###');
        $templateVersions = $this->cObj->getSubpart($template, '###VERSIONS###');
        $templateDevices = $this->cObj->getSubpart($template, '###DEVICES###');
        $allText = "---&nbsp;ALL&nbsp;---";

        $sql = "
            SELECT
            uid,
            title
                FROM
                tx_piapappnote_%s
                WHERE
                hidden = 0
                AND deleted = 0
                ORDER BY
                title
                ";

        global $TYPO3_DB;
        $res = $TYPO3_DB->sql_query(sprintf($sql, "categories"));
        $cats = $this->my_explode2(",", $this->piVars['typeS']['categorylist']);
        $htmlCategories = '';
        $marker = array(
                '###VALUE###' => 0,
                '###NAME###' => $allText,
                '###SELECTED###' => in_array(0, $cats) ? ' selected="selected"' : '',
                );    
        $htmlCategories .= $this->cObj->substituteMarkerArray($templateCategories, $marker);    
        while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
            $marker = array(
                    '###VALUE###' => $row['uid'],
                    '###NAME###' => $row['title'],
                    '###SELECTED###' => (in_array($row['uid'], $cats) && !in_array(0, $cats)) ? ' selected="selected"' : '',
                    );
            $htmlCategories .= $this->cObj->substituteMarkerArray($templateCategories, $marker);
        };

        $res = $TYPO3_DB->sql_query(sprintf($sql, "versions"));
        $vers = $this->my_explode2(",", $this->piVars['typeS']['versionlist']);
        $htmlVersions = '';
        $marker = array(
                '###VALUE###' => 0,
                '###NAME###' => $allText,
                '###SELECTED###' => in_array(0, $vers) ? ' selected="selected"' : '',
                );        
        $htmlVersions .= $this->cObj->substituteMarkerArray($templateVersions, $marker);
        while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
            $marker = array(
                    '###VALUE###' => $row['uid'],
                    '###NAME###' => $row['title'],
                    '###SELECTED###' => (in_array($row['uid'], $vers) && !in_array(0, $cats)) ? ' selected="selected"' : '',
                    );
            $htmlVersions .= $this->cObj->substituteMarkerArray($templateVersions, $marker);
        };

        $res = $TYPO3_DB->sql_query(sprintf($sql, "devices"));
        $devs = $this->my_explode2(",", $this->piVars['typeS']['devicelist']);
        $htmlDevices = '';
        $marker = array(
                '###VALUE###' => 0,
                '###NAME###' => $allText,
                '###SELECTED###' => in_array(0, $devs) ? ' selected="selected"' : '',
                );    
        $htmlDevices .= $this->cObj->substituteMarkerArray($templateDevices, $marker);            
        while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
            $marker = array(
                    '###VALUE###' => $row['uid'],
                    '###NAME###' => $row['title'],
                    '###SELECTED###' => (in_array($row['uid'], $devs) && !in_array(0, $cats)) ? ' selected="selected"' : '',
                    );
            $htmlDevices .= $this->cObj->substituteMarkerArray($templateDevices, $marker);
        };

        $subpartmarker = array(
                '###CATEGORIES###' => $htmlCategories,
                '###VERSIONS###' => $htmlVersions,
                '###DEVICES###' => $htmlDevices,
                );

        $piVarsSave = $this->piVars;
        unset($this->piVars['typeS']);
        unset($this->piVars['sword']);
        $simpleSearchURL = $this->pi_linkTP_keepPIvars_url(array("mode" => "list", "pointer" => "0"));
        $simpleSearchAction = $this->pi_linkTP_keepPIvars_url(array("mode" => "list", "pointer" => "0"));
        $advancedSearchAction = $this->pi_linkTP_keepPIvars_url(array("mode" => "search", "pointer" => "0"));
        $this->piVars = $piVarsSave;
        $checkSearchFields = false;
        if (!array_key_exists("typeS", $this->piVars))
            $checkSearchFields = true;
        $marker = array(
                '###NOTEID_CHECKED###' => ($this->piVars['typeS']['noteid'] == 'Y') || $checkSearchFields ?
                                          ' checked="checked"' : '',
                '###AUTHOR_CHECKED###' => ($this->piVars['typeS']['author'] == 'Y') || $checkSearchFields ?
                                          ' checked="checked"' : '',
                '###TITLE_CHECKED###' => ($this->piVars['typeS']['title'] == 'Y') || $checkSearchFields ?
                                          ' checked="checked"' : '',
                '###DESCRIPTION_CHECKED###' => ($this->piVars['typeS']['description'] == 'Y') || $checkSearchFields ?
                                          ' checked="checked"' : '',
                '###SWORD###' => $this->piVars['sword'],
                '###ADVANCED_SEARCH_URL###' => $this->pi_linkTP_keepPIvars_url(array("mode" => "search", "pointer" => "0")),
                '###ADVANCED_SEARCH_ACTION###' => $advancedSearchAction,
                '###SIMPLE_SEARCH_URL###' => $simpleSearchURL,
                '###SIMPLE_SEARCH_ACTION###' => $simpleSearchAction,
                );

        return $this->cObj->substituteMarkerArrayCached($template, $marker, $subpartmarker);
    }    


    /**
     * Header of column sort link URL
     */
    function getSortByFieldURL($field)
    {
        $dir = '1';
        if ($this->sortfield == $field)
            if ($this->sortorder == '1')
                $dir = '0';

        return $this->pi_linkTP_keepPIvars_url(array(
                    'file' => $field . ':' . $dir));
    }

	function debug($mixed)
	{
		echo '<pre>';
		var_dump($mixed);
		echo '</pre>';
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/piap_appnote/pi1/class.tx_piapappnote_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/piap_appnote/pi1/class.tx_piapappnote_pi1.php']);
}

?>
