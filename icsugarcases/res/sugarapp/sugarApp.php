<?php
/* @version $Id: sugarApp.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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

/** ensure this file is being included by a parent file */
defined( '_VALID_SUGAR' ) or die( 'Direct Access to this location is not allowed.' );

// This class is the base class that stores all the application logic of the component

class sugarApp {
    var $request = null;
    var $sugarAuthorizedPortalUser = false;
    var $sortBy = array('case_number'=>'desc');
    var $appFields = false;
    var $sessionID = false;
    // DO NOT override $configGlobal in your derived class!!!!
    var $configGlobal = array(
		'useSessions' => array(
			'default'=>true,
			'description'=>'Use a php session variable to store the Sugar Session ID',
			'type'=>'boolean',
			'label'=>'Use Sessions',
			'required'=>false
		)
	);

    // Override $configModule in your derived class to provide component scope configuration directives
    var $configModule = array();
    var $globalDescription = '<h1>SugarCRM Case Self-Service Portal for TYPO3</h1>';
	var $neededFormFields = null;

    function Initialize($request = false) {
        // setup the sort.  We'll use this on any page that sorts.  The order_by var in
        // the querystring should look like:
        //       number,desc
        //       priority,asc
        //       etc.
        if(isset($request['order_by'])){
            $this->sortBy = array();
            $tmpGet = urldecode($request['order_by']);
            list($sortColumn,$sortOrder) = explode(',',$tmpGet);
            $this->sortBy[$sortColumn] = $sortOrder;
        }

        $theDatabaseClass = 'MOS' . _MYNAMEIS;
        $theDatabase = new $theDatabaseClass();

        $this->sugarConf = new sugarConfiguration($theDatabase);

        foreach($this->configGlobal as $key=>$value) {
            $this->$key = isset($parameters[$key])
                           ? $parameters[$key] : $value['default'];
        }

        $this->sugarConf->RegisterDirective($this->configGlobal, 'GLOBAL');

        if( isset($this->configModule) ) {
            foreach($this->configModule as $key=>$value) {
                $this->$key = isset($parameters[$key])
                               ? $parameters[$key] : $value['default'];
            }
            $this->sugarConf->RegisterDirective($this->configModule, _MYNAMEIS);
        }

        $this->sugarError = false;

        // Sugar Sanity Check
        if( ! $this->sugarConf->checkConfig() ) {
            $this->sugarError = true;
        }
        if( ! $this->sugarConf->checkSugar() ) {
            $this->sugarError = true;
        }

        $configList = $this->sugarConf->getAppConfig('GLOBAL');
        $myConfigList = $this->sugarConf->getAppConfig(_MYNAMEIS);

        $theConfigList = array_merge($configList, $myConfigList);

        $this->setConfig($configList);

        $this->request = $request;
    }

    function configurePresentationLayer(&$presentation) {
        $this->sugarConf->RegisterDirective($presentation->configGlobal, 'GLOBAL');
        if( isset($presentation->configModule) ) {
            $this->sugarConf->RegisterDirective($presentation->configModule, _MYNAMEIS);
        }

		$presentation->sortBy = $this->sortBy;
        $presentation->setConfig($this->configList);
        $presentation->needFormFields = $this->neededFormFields;
    }

    function getAvailableFields() {
        if(!$this->appFields) {
            $this->sugarComm->getAvailableFields();
            $this->appFields = $this->sugarComm->moduleFields;
        }

        return $this->appFields;
    }

    function getComponentDesc() {
        $descToReturn = '';

        if( isset($this->componentDesc) && $this->componentDesc != '') {
            $descToReturn = $this->componentDesc;
        } else {
            $descToReturn = $this->globalDesc;
        }

        return $descToReturn;
    }

    function setNeededFormFields($fields) {
        $this->neededFormFields = $fields;
    }

    function setConfig($configList) {
        foreach($this->configGlobal as $key=>$value) {
            $this->$key = $configList[$key];
        }
        if( is_array($this->configModule) ) {
            foreach($this->configModule as $key=>$value) {
                $this->$key = $configList[$key];
            }
        }

        $this->configList = $configList;
    }

    function saveConfig($post) {
        return $this->sugarConf->_saveConfig($post);
    }

    // I don't like this, but I put the html for the config form here so I could have
    // it in one place for all the components.  This is *not* Joomla-like, but it is
    // the best thing to do.  It's better than what I had before when it was done in
    // the sugarConfig object
    function getConfigForm($submitbutton = false) {
        $configDirectives = $this->sugarConf->getAllDirectives();
        ob_start();

        ?>
        <form action="index2.php" method="post" name="adminForm">
        <table class="adminheading">
        <tr>
            <th colspan="2">Sugar Case Self-Service Portal Configuration</th>
        </tr>
        <tr>
            <td colspan="2">
                &nbsp;
            </td>
        </tr>
        <tr>
          <td colspan="2">
          <table class="adminlist">
            <tr>
              <th style="width: 5%;">&nbsp;</th>
              <th style="width: 10%;">Scope</th>
              <th style="width: 15%;">Directive</th>
              <th style="width: 15%;">Value</th>
              <th>Description</th>
              <th style="width: 5%;">&nbsp;</th>
            </tr>

        <?php

        ksort($configDirectives);
		$k = 1;

        foreach($configDirectives as $component=>$directive) {
            // Apparently I ran out of useful variable names
            ksort($directive);
            foreach($directive as $name=>$stuff) {
                $k = 1 - $k;
                echo '<tr class="row' . $k . '">
                    <td>&nbsp;</td>';

                echo "<td>$component</td>";
                echo "<td>{$stuff['label']}</td>";
                echo "<td>" . $this->_getAppropriateConfigFormField($name,$stuff) . "</td>";
                echo "<td>{$stuff['description']}<br />(default: '{$stuff['default']}')</td>";

                echo '<td>&nbsp;</td>
                    </tr>';
            }
        }
        ?>
          </table>
          </td>
        </tr>
        </table>
        <input type="hidden" name="option" value="<?php echo _MYNAMEIS; ?>" />
        <input type="hidden" name="task" value="saveconfig" />
        <?php

        $retForm = ob_get_contents();
        ob_end_clean();

        return $retForm;
    }

    function _getAppropriateConfigFormField($name,$field) {
        $retField = '';

        switch($field['type']) {
            case 'boolean':
                $retField = '<input name="' . $name . '" type="checkbox"';
                if($field['value']) $retField .= ' checked';
                $retField .= ' />';
                break;
            case 'password':
                $retField = 'Enter new:<br /> <input name="' . $name . '" size="40" type="password" /><br /><br />';
                $retField .= 'Confirm: <br /><input name="' . $name . 'confirm" size="40" type="password" />';
                break;
            case 'string':
            default:
                $retField = '<input name="' . $name . '" type="text" size="40" value="' . $field['value'] . '" />';
                break;
        }

        return $retField;
    }

    // Get all gets the entire basic dataset associated with this object.  If you need to
    // get more than one Sugar module's worth of data for your basic dataset,
    // override getAll in your app class.
	/** [IC] jeggers: added $row_offset */
    function getAll( $fields = array(),$row_offset='', $limit='' ) {
        return $this->sugarComm->getSome(array(), $this->sortBy, $fields, $row_offset, $limit);
    }

    function checkConfig() {
        return $this->sugarConf->checkConfig();
    }

    // These are all for storing the sugar session ID in a session variable here.  It should be a configurable item

    // This will be used to start the session.  You *must* call setSessionStartCallback first!
    function startSession() {
        if($this->useSessions) {
            call_user_func($this->sessionStartCallback,$this);
        }
    }

    // This will be used to stop the session.  You *must* call setSessionStopCallback first!
    function stopSession() {
        if($this->useSessions) {
            call_user_func($this->sessionStopCallback,$this);
        }
    }

    function setSugarSessionID($id) {
        $this->sessionID = $id;
    }

    // createSession just logs into the sugar server and gets a session id
    function createSession() {
        $this->sessionID = $this->sugarComm->createSession();
    }

    // closeSession logs out of the sugar server
    function closeSession() {
        $this->sugarComm->closeSession();
    }

    // This is for the callback for session starting.  You should write a global function to pass in that will
    // use the portal's own session management API.  Your global function should take a reference to sugarApp as its
    // only parameter
    function setSessionStartCallback($callback) {
        $this->sessionStartCallback = $callback;
    }

    // As above, so below.  This is to set the callback that will be used to stop the session.
    function setSessionStopCallback($callback) {
        $this->sessionStopCallback = $callback;
    }

    function getSugarSessionID() {
        return $this->sessionID;
    }
}

?>