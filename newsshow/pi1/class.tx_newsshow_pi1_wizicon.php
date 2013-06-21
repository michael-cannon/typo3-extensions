<?php

	class tx_newsshow_pi1_wizicon {

		/***************************************************************
		 * SECTION 1 - MAIN
		 *
		 * Wizard items functions.
		 ***************************************************************/

		/**
		 * Add wizard item to the backend
		 *
		 * @param		$wizardItems		The wizard items
		 * @return		The wizard item
		 */
		function proc($wizardItems) {
			global $LANG;

			// Get locallang values
			$LL = $this->includeLocalLang();

			// Wizard item
			$wizardItems['plugins_tx_newsshow_pi1'] = array(

				// Icon
				'icon'=>t3lib_extMgm::extRelPath('newsshow').'pi1/ce_wiz.gif',

				// Title
				'title'=>$LANG->getLLL('pi1_title',$LL),

				// Description
				'description'=>$LANG->getLLL('pi1_plus_wiz_description',$LL),

				// Parameters
				'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=newsshow_pi1'
			);

			// Return items
			return $wizardItems;
		}

		/**
		 * Include locallang values
		 *
		 * @return		The content of the locallang file
		 */
		function includeLocalLang() {

			// Include file
			include(t3lib_extMgm::extPath('newsshow').'locallang.php');

			// Return file content
			return $LOCAL_LANG;
		}
	}

	/**
	 * XCLASS inclusion
	 */
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newsshow/pi1/class.tx_newsshow_pi1_wizicon.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newsshow/pi1/class.tx_newsshow_pi1_wizicon.php']);
	}
?>
