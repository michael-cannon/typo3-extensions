<?php
/*  the Free Software Foundation; either version 2 of the License, or
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
 * Class 'tx_jobbank_tcemainproc'.
 *
 * @author	Riteh Gurung <ritesh@srijan.in>
*/
class tx_jobbank_tcemainproc {
			var $extKey = 'job_bank';
			/**
			 * Function using the hook
		 	 * @param string The status i.e. update or new record.
			 * Right now not used
		 	 * @param string The table where record is
			 * created/updated
		 	 * @param int The ID of the record
			 * created/updated. Right now not used
		 	 * @param array The array containing the
			 * changed fields and theirs values
		 	 * @param object The TCEmain object
		 	 * @return	void
		 	 */	
		 	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray,&$obj) {
						//Further
						//processing
						//only if the
						//record is a
						//news item and
						//the Send
						//Newsletter
						//field is on
						if('tx_jobbank_list'==	$table){
								$fieldArray['starttime']=empty($fieldArray['starttime'])?time():$fieldArray['starttime'];
								//var_dump($fieldArray);
						}
			}
}
?>
