<?php 
/* @version $Id: install.sugarcases.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version
 * 1.1.3 ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied.  See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * The Original Code is: SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/


defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

global $mainframe;

define('_MYNAMEIS', 'com_sugarcases');

$basePath = JPATH_SITE . "/components/" . _MYNAMEIS . "/";
require_once ( $basePath . 'sugarcases.class.php' ) ;

require_once( $basePath . "sugarportal.inc.php" );

function com_install() 
{
    global $_REQUEST, $mainframe;
    ?>
    <div style="margin-left: 20%; margin-right: 20%;">
    <?php
    $bugApp = new sugarAppCase($_REQUEST, 'portal');

	echo $bugApp->globalDescription;
    echo $bugApp->installSanityTest();

?>
</div>
<?php
    return "Success!";
}
?>