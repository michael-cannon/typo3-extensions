<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Chetan Thapliyal (chetan@srijan.in)
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
 * Module extension (addition to function menu) 'Membership Survey' for the 'sr_feuser_register_survey' extension.
 *
 * @author	Chetan Thapliyal <chetan@srijan.in>
 */



require_once(PATH_t3lib."class.t3lib_extobjbase.php");

require_once ( t3lib_extMgm::extPath('sr_feuser_register_survey').'classes/class.sr_feuser_register_survey_common.php');

class tx_srfeuserregistersurvey_modfunc1 extends t3lib_extobjbase {

    /**
     * Pages table
     *
     * @access private
     * @var    string
     *
     * @author Chetan Thapliyal <chetan@srijan.in>
     */
    var $pages_table;

    /**
     * Survey Template File
     *
     * @access private
     * @var    string
     *
     * @author Chetan Thapliyal <chetan@srijan.in>
     */
    var $template_file;

    /**
     * Object to template handling library
     *
     * @access private
     * @var    string
     *
     * @author Chetan Thapliyal <chetan@srijan.in>
     */
    var $lib_template;

	function modMenu()	{
		global $LANG;

		return Array (
			"tx_srfeuserregistersurvey_modfunc1_check" => "",
		);
	}

	function main()	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// $theOutput.=$this->wizard->pObj->doc->spacer(5);
		// $theOutput.=$this->wizard->pObj->doc->section($LANG->getLL("title"),"Dummy content here...",0,1);

		// $theOutput .= $this->pObj->doc->spacer(5);
		$theOutput .= $this->getSurveyEnabledPagesReport(); //$this->pObj->doc->section( $LANG->getLL("title"), "Dummy content here...",0,1);

		/*$menu=array();
		$menu[]=t3lib_BEfunc::getFuncCheck($this->wizard->pObj->id,"SET[tx_srfeuserregistersurvey_modfunc1_check]",$this->wizard->pObj->MOD_SETTINGS["tx_srfeuserregistersurvey_modfunc1_check"]).$LANG->getLL("checklabel");
		$theOutput.=$this->wizard->pObj->doc->spacer(5);
		$theOutput.=$this->wizard->pObj->doc->section("Menu",implode(" - ",$menu),0,1);*/

		return $theOutput;
	}

    /**
     * Function to initialize class variables
     *
     * @access private
     */
    function init() {

        $this->pages_table   = 'pages';
    	$this->template_file = t3lib_extMgm::extPath('sr_feuser_register_survey').'templates/survey.tmpl';
    	$this->lib_template  = t3lib_div::makeInstance( 'sr_feuser_register_survey_common');

    }

    /**
     * Function to get listing of survey enabled pages
     * from database.
     *
     * @access private
     * @return array    Lisitng of all the pages enabled for survey check
     */
    function getSurveyEnabledPagesReport() {

        $report_content = '';

		$template['content'] = file_get_contents( $this->template_file);

		if ( strcmp( $template['content'], '')) {
			$template['SURVEY_ENABLED_PAGES_REPORT'] = $this->lib_template->getSubTemplate( $template['content'], 'SURVEY_ENABLED_PAGES_REPORT_TEMPLATE');

			if ( strcmp( $template['SURVEY_ENABLED_PAGES_REPORT'], '')) {
				$template['PAGE_DETAILS'] =  $this->lib_template->getSubTemplate( $template['SURVEY_ENABLED_PAGES_REPORT'], 'PAGE_DETAILS');

				if ( strcmp( $template['PAGE_DETAILS'], '')) {
					// Get listing of survey enabled pages
					$survey_enabled_pages = $this->getSurveyEnabledPages();

					$report_content = '';

					if ( is_array( $survey_enabled_pages) && !empty( $survey_enabled_pages)) {

					  foreach ( $survey_enabled_pages as $key => $page_details) {
							/*echo '<PRE>';
							var_dump( $page_details);
							echo '</PRE>';*/

						  $replace = array (
							  'PAGE_ID'    => $page_details['uid'],
							  'PAGE_TITLE' => $page_details['title']
						  );

						  $report_content .= $this->lib_template->replaceTplMarkers( $template['PAGE_DETAILS'], $replace);
					  }

					}

					else
					{
						// no pages
						$replace	= array (
										'PAGE_ID'    => ''
										, 'PAGE_TITLE' => 'No survey enabled pages'
									);

						$report_content = $this->lib_template->replaceTplMarkers( $template['PAGE_DETAILS'], $replace);
					}

					$report_content = $this->lib_template->replaceTplMarkers( $template['SURVEY_ENABLED_PAGES_REPORT'], array( 'PAGE_DETAILS' => $report_content), true);
				}

			}
		}

        return $report_content;
    }

    /**
     * Function to get listing of survey enabled pages
     * from database.
     *
     * @access private
     * @return array    Lisitng of all the pages enabled for survey check
     */
    function getSurveyEnabledPages() {

        $survey_enabled_pages = array();

        $select_fields = 'uid, title';
    	$table         = $this->pages_table;
    	$where         = 'tx_srfeuserregistersurvey_survey_check > 0';
    	$order_by      = 'uid';

        // Uncomment to debug it
        // echo $GLOBALS['TYPO3_DB']->SELECTquery( $select_fields, $table, $where, '', $order_by);
        
        $rs = $GLOBALS['TYPO3_DB']->exec_SELECTquery( $select_fields, $table, $where, '', $order_by);

    	if ( $rs) {

    	    if ( $GLOBALS['TYPO3_DB']->sql_num_rows( $rs)) {

    	        while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $rs)) {

    		      $survey_enabled_pages[] = $row;
    		      
    	        }
     
    	    }
    	}

        return $survey_enabled_pages;
    }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sr_feuser_register_survey/modfunc1/class.tx_srfeuserregistersurvey_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sr_feuser_register_survey/modfunc1/class.tx_srfeuserregistersurvey_modfunc1.php']);
}

?>
