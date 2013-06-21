<?php
/* @version $Id: sugarConfiguration.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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

class sugarConfiguration {
    var $joomlaOption = null;  // Joomla option variable
    var $joomlaDatabase = null;  // Joomla global database variable
    var $appConfig = array();
    var $availableFields = array();  // Fields available to Contacts and Leads in the Sugar database
    var $configGlobal = array(
                    'username'=>array(
                            'default'=>'',
                            'description'=>'The username that you will use to connect to Sugar Suite.',
                            'type'=>'string',
                            'label'=>'Username',
                            'required'=>true),
                    'password'=>array(
                            'default'=>'',
                            'description'=>'The password of the user denoted by \'username\' in the Sugar Suite installation.',
                            'type'=>'password',
                            'label'=>'Password',
                            'required'=>true),
                    'server'=>array(
                            'default'=>'http://www.example.com/sugarcrm/soap.php',
                            'description'=>'The complete URI for the soap.php file in your Sugar Suite installation.',
                            'type'=>'string',
                            'label'=>'Sugar Soap Location',
                            'required'=>true),
                    'file_storage'=>array(
                            'default'=>'/tmp',
                            'description'=>'The location of uploaded files and also files retrieved from Sugar Suite',
                            'type'=>'string',
                            'label'=>'Temporary File Storage',
                            'required'=>true),
                    'appname'=>array(
                            'default'=>'Joomla',
                            'description'=>'The name of the CMS application used to access Sugar Suite.',
                            'type'=>'string',
                            'label'=>'Application Name',
                            'required'=>true)
                    );

    function sugarConfiguration($database = false) {
        global $mainframe;
        // class constructor

        $this->joomlaOption = _MYNAMEIS;

        if( $database ) {
            $this->joomlaDatabase = $database;
        } else {
            $database = & JFactory::getDBO();
            $this->joomlaDatabase = $database;
        }

        // Before we make the object sane, we have to make sure the database is sane
        if( $this->isOldTable() ) {
            $this->updateTable();
        }

        // The only useful purpose of this in the constructor is to keep the object sane at all times.  Otherwise, all of this
        // data gets filled in from the database anyway.
        foreach($this->configGlobal as $key=>$value) {
            $this->$key = isset($parameters[$key])
                           ? $parameters[$key] : $value['default'];
        }

        $this->RegisterDirective($this->configGlobal, 'GLOBAL');
    }

    // RegisterDirective registers a single directive in the database, but DOES NOT retrieve the directive or in any other way store it!  It just checks to
    // see if the directive is there already, and if not, create it with the default value.
    // $scope should either be _MYNAMEIS or "GLOBAL".  If you typo "GLOBAL", you'll get _MYNAMEIS for the scope.
    // Also works on a whole array of directives
    function RegisterDirective($directive, $scope) {
        // First eliminate any existing keys that might already be in the database
        foreach($directive as $key=>$value) {
            $this->joomlaDatabase->setQuery("SELECT * FROM #__sugar_portal_configuration WHERE `name` LIKE '$key' AND `component` LIKE '$scope';");
            $results = $this->joomlaDatabase->query();

            while($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
                // update the meta field if needed
                if( ! isset($result['meta']) ) {
                    $this->joomlaDatabase->id = $result['id'];
                    $this->joomlaDatabase->meta = $this->_squishConfigMeta($directive[$key]);
                    $this->joomlaDatabase->store();
                }
                unset($directive[$result['name']]);
            }
        }

        // Now add the default values of whatever is left
        foreach($directive as $key=>$value) {
            // blank out ID to prevent existing rows from being overwritten
        	$this->joomlaDatabase->id = "";
            $this->joomlaDatabase->name = $key;
            $this->joomlaDatabase->value = $value['default'];
            if($scope != 'GLOBAL') {
                $this->joomlaDatabase->component = _MYNAMEIS;
            } else {
                $this->joomlaDatabase->component = $scope;
            }
            $this->joomlaDatabase->meta = $this->_squishConfigMeta($value);
            $this->joomlaDatabase->store();
        }

    }

    // Converts the type information for a config directive to an .ini format for storage in the database
    function _squishConfigMeta($meta) {
        $retMeta = '';
        $tmpMeta = array();

        foreach($meta as $key=>$value) {
            $tmpMeta[] = "$key=$value";
        }

        $retMeta = implode(chr(13),$tmpMeta);

        return $retMeta;
    }

    // Parses the .ini formatted type information for a config directive
    function _unsquishConfigMeta($meta) {
        $retMeta = array();

        $tmpMeta = explode(chr(13),$meta);

        foreach($tmpMeta as $oneMeta) {
            list($key, $value) = explode('=', $oneMeta);
            if( is_array($value) || is_array($key) ) {
                $retMeta['description'] = 'No meta information available, this is a BUG.';
            } else {
                $retMeta[$key] = $value;
            }
        }

        return $retMeta;
    }

    function getAppConfig($scope) {
        $this->_getConfig($scope);

		if( array_key_exists($scope,$this->appConfig) )
	        return $this->appConfig[$scope];
		else
			return array();
    }

    // deprecated, use getAppConfig instead.  This method is still used in the communication layer
    function getDirective($directive) {
        return $this->appConfig['GLOBAL'][$directive];
    }

    function _getConfig($scope) {
        $this->joomlaDatabase->setQuery('SELECT * FROM #__sugar_portal_configuration WHERE `component` LIKE \''.$scope.'\';');
        $results = $this->joomlaDatabase->query();

        $retrievedConfig = array();

        while ($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
            $configName = $result['name'];
            $meta = $this->_unsquishConfigMeta($result['meta']);

            switch($meta['type']) {
                case 'boolean':
                    $this->appConfig[$scope][$configName] = (bool) $result['value'];
                    break;
                case 'password':
                    $this->appConfig[$scope][$configName] = $result['value'];
                    break;
                case 'string':
                default:
                    $this->appConfig[$scope][$configName] = $result['value'];
                    break;
            }
        }
    }

    function _saveConfig($post) {
        $this->joomlaDatabase->setQuery('SELECT * FROM #__sugar_portal_configuration;');
        $results = $this->joomlaDatabase->query();

        $configSaved = '';

        while ($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
            $configName = $result['name'];
            $meta = $this->_unsquishConfigMeta($result['meta']);

            switch($meta['type']) {
                case 'boolean':
                    $value = ($post[$configName] == 'on');
                    break;
                case 'password':
                    if( ($post[$configName] != '') ) {
                        if( ($post[$configName] == $post[$configName . 'confirm']) ) {
                            $value = md5($post[$configName]);
                        } else {
                            $value = $result['value'];
                            $configSaved .= 'Values for "' . $meta['label'] . '" did not match, so the new password was not saved.  ';
                        }
                    } else {
                        $value = $result['value'];
                    }
                    break;
                case 'string':
                default:
                    $value = $post[$configName];
                    break;
            }
            $this->joomlaDatabase->value = $value;
            $this->joomlaDatabase->id = $result['id'];
            $this->joomlaDatabase->store();
        }

        $configSaved .= 'Configuration saved!';

        return $configSaved;
    }

    function getAuth() {
        return array('user_name'=>$this->appConfig['GLOBAL']['username'],
                     'password'=>$this->appConfig['GLOBAL']['password'],
                     'version'=>".01");
    }

    function getAllDirectives() {
        $this->joomlaDatabase->setQuery('SELECT * FROM #__sugar_portal_configuration');
        $results = $this->joomlaDatabase->query();

        $retrievedConfig = array();

        while ($result = mysql_fetch_array($results, MYSQL_ASSOC) ) {
            // I love python
            list( $key, $value ) = array($result['component'],$result['value']);

            $retrievedConfig[$key][$result['name']] = $this->_unsquishConfigMeta($result['meta']);
            $retrievedConfig[$key][$result['name']]['value'] = $value;
        }

        return $retrievedConfig;
    }

    function getBrokeMessage() {
        $msg = '
        <p><strong>I am very sorry, but the webpage you are trying to access is currently
           unavailable.  Please try again later.</strong></p>';

        return $msg;
    }

    function getFirsttimeConfigForm() {
        $configForm = '
        ';
        $configForm .= $this->getConfigForm(true);
        $configForm .= "
        ";

        return $configForm;
    }

    function isOldTable() {
        // First check to see if we have the new column
        // todo: find a more graceful way of dealing with this instead of this kludge
        $this->joomlaDatabase->setQuery("SHOW COLUMNS FROM #__sugar_portal_configuration");
        $results = $this->joomlaDatabase->query();

        $hasComponent = false;
        $hasMeta = false;
        while ($row = mysql_fetch_array($results, MYSQL_ASSOC) ) {
            if($row['Field'] == 'component') {
                $hasComponent = true;
            }
            if($row['Field'] == 'meta') {
                $hasMeta = true;
            }
        }

        if($hasComponent && $hasMeta) {
            return false;
        }

        return true;
    }

    function updateTable() {
        // First check to see if we have the new column
        // todo: find a more graceful way of dealing with this instead of this kludge
        $this->joomlaDatabase->setQuery("SHOW COLUMNS FROM #__sugar_portal_configuration");
        $results = $this->joomlaDatabase->query();

        $hasComponent = false;
        $hasMeta = false;
        while ($row = mysql_fetch_array($results, MYSQL_ASSOC) ) {
            if($row['Field'] == 'component') {
                $hasComponent = true;
            }
            if($row['Field'] == 'meta') {
                $hasMeta = true;
            }
        }

        // If the component field isn't there, add it
        if( ! $hasComponent ) {
            $this->joomlaDatabase->setQuery("ALTER TABLE `#__sugar_portal_configuration` ADD `component` VARCHAR( 254 ) DEFAULT 'GLOBAL' NOT NULL AFTER `id` ;");
            $results = $this->joomlaDatabase->query();
        }

        // If meta isn't there, add it too
        if( ! $hasMeta ) {
            $this->joomlaDatabase->setQuery("ALTER TABLE `#__sugar_portal_configuration` ADD `meta` TEXT;");
            $results = $this->joomlaDatabase->query();
        }

        return true;
    }

    function checkConfig() {
        // Just check to see if there's a soap config.  All others are setup, the soap config
        // is stuff we need from the user.
        $this->joomlaDatabase->setQuery("SELECT * FROM #__sugar_portal_configuration");
        $results = $this->joomlaDatabase->query();

        $hasserver = false;
        $hasuser = false;
        $haspasswd = false;

        while ($row = mysql_fetch_array($results, MYSQL_ASSOC) ) {
            switch($row['name']) {
                case 'username':
                    $hasuser = true;
                    break;
                case 'server':
                    $hasserver = true;
                    break;
                case 'password':
                    $haspasswd = true;
                    break;
            }
        }

        return ($hasuser && $hasserver && $haspasswd) ;
    }

    function checkSugar() {
        // run some sanity tests to make sure we can connect to sugar

        // todo: implement this

        // just return True until this is actually implemented
        return true;
    }

}


?>