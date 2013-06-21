<?php

/* @version $Id: sugarHTML.php,v 1.1.1.1 2010/04/15 10:03:40 peimic.comprock Exp $ */



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



// This class is the base class that stores useful configuration directives and stuff for the presentation layer



class sugarHTML {

    var $needFormFields = array();

    var $sortBy = '';

	var $sortcolumn = null;

	var $sortorder = null;

    // You will make a $configModule variable that looks exactly like this in your derived class but with different settings.

    var $configGlobal = array(

                    'navSeparator'=>array(

                            'default'=>'|',

                            'description'=>'The characters that will separate navigation items in the main navigation bar.',

                            'type'=>'string',

                            'label'=>'Nav Separator',

                            'required'=>false),

                    'baseUrl'=>array(

                            'default'=>'index.php',

                            'description'=>'The base URL used for links.  Usually this will be \'index.php\'',

                            'type'=>'string',

                            'label'=>'Base URL',

                            'required'=>true),

                    'formFieldClass'=>array(

                            'default'=>'inputbox',

                            'description'=>'The class that will be associated with form input fields.',

                            'label'=>'Form Field Class',

                            'type'=>'string',

                            'required'=>false),

					'datetimeformat'=>array(

                            'default'=>'%Y-%m-%d %H:%M',

                            'description'=>'Format for date time formats.  See http://php.net/strftime for details on how to format dates.',

                            'type'=>'string',

                            'label'=>'Date/Time Format',

                            'required'=>false),

					'fullpagination'=>array(

                            'default'=>false,

                            'description'=>'Use full pagination. Requires custom Sugar SOAP code.',

                            'type'=>'boolean',

                            'label'=>'Full Paging Support',

                            'required'=>false),

					'rowband'=>array(

                            'default'=>'#dfe9ff',

                            'description'=>'The html color for every other row in all lists.',

                            'type'=>'string',

                            'label'=>'Row Color',

                            'required'=>false),

					'editexisting'=>array(

                            'default'=>true,

                            'description'=>'Check to allow users to edit existing records. ',

                            'type'=>'boolean',

                            'label'=>'Edit existing',

                            'required'=>false),

                            );

	var $configModule = array();



    function Initialize() {

        // Setup the configuration variables

        foreach($this->configGlobal as $key=>$value) {

            $this->$key = !empty($parameters[$key])

                           ? $parameters[$key] : $value['default'];

        }

        if( isset($this->configModule) ) {

            foreach($this->configModule as $key=>$value) {

                $this->$key = !empty($parameters[$key])

                               ? $parameters[$key] : $value['default'];

            }

        }

    }



    function setConfig($configList) {

        foreach($this->configGlobal as $key=>$value) {

        	if (isset($configList[$key])) {

            	$this->$key = $configList[$key];

            }

        }

        if( is_array($this->configModule) ) {

            foreach($this->configModule as $key=>$value) {

                $this->$key = $configList[$key];

            }

        }



        $this->configList = $configList;

    }



    function getConfigGlobal() {

        return $this->configGlobal;

    }



    function _getOrderBy() {

        // a cheap cop-out to get the first item to sort by, lets us later decide

        // to sort by any number of columns

		$sortcolumn = "";

		$sortorder = '';

		if( is_array($this->sortBy) ) {

            foreach($this->sortBy as $key=>$value) {

                $sortcolumn = $key;

                $sortorder = $value;

                break;

            }

        }



        return array($sortcolumn, $sortorder);

    }



    // shortcut to get a url of the form "index.php?option=whatever" that includes the needed form fields

    function _getBaseUrl() {

        return $this->baseUrl . '?' . $this->_getNeededFormFields('get');

    }



    function _getNewOrderby($sortcolumn, $sortorder, $parameter) {

        if($sortcolumn == $parameter) {

            $newOrder_by = $parameter;

            if($sortorder == 'desc') {

                $newOrder_by .= ',asc';

            } else {

                $newOrder_by .= ',desc';

            }

        } else {

            $newOrder_by = "$parameter,desc";

        }



        return $newOrder_by;

    }



    function _getNeededFormFields($formType = 'get') {

        $returnFields = '';



        switch($formType) {

            case 'post':

                foreach($this->needFormFields as $field=>$value) {

                    $returnFields .= '<input type="hidden" name="' . $field . '" value="' . $value . '" />';

                }

                break;

            case 'get':

            default:

                $tmpFields = array();

                foreach($this->needFormFields as $field=>$value) {

                    $tmpFields[] = $field . '=' . $value;

                    $returnFields = implode($tmpFields, '&');

                }

                break;

        }



        return $returnFields;

    }

/** [IC] 2006/04/19 - if editexisting is not set don't show html fields..just text */

    function _getAppropriateFormfield($atype, $column, $value, $options, $ignorewidth=false) {

        //$this->Initialize();

    	global $task;



        $returnWidget = '';



        $showMe = true;



        $widgetWidth = '';



        if( $ignorewidth ) {

            $widgetWidth = '100';

        } else {

            $widgetWidth = $column['size'];

        }



		/** [IC] 2006/03/24 - trying to get an account drop-down list */

		//if ($atype == "relate" && $options != "") {

		//	$atype = "enum";

		//}



		/** [IC] 2006/03/24 - have account_name as hidden */

		$additional = '';

		//if ($column['field'] == "account_name" && $atype == 'enum') {

		//	$additional = '<input type="hidden" name="' . $column['field'] . '" value="' . $options[0]['value'] . '" />';

		//}



        if( !(bool)$column['show'] && $task != "search" ) {

            //$returnWidget = '<input type="hidden" name="' . $column['field'] . '" value="' . $value . '" />';

            $showMe = false;

        } else {

            switch($atype) {

                case 'plaintext':

                    $returnWidget = nl2br($value);

                    break;

                case 'enum':

					if( ! (bool)$column['canedit'] && $task != "search") {

                        foreach($options as $thisoption) {

                            if( $thisoption->name == $value ) {

                                $returnWidget = $thisoption->value;

								//$returnWidget .= '<input type="hidden" name="' . $column['field'] . '" value="' . $value . '" />';

                                break;

                            }

                        }

					} else {

						/** [IC] 2006/03/24 - set account drop-down as account_id */

						$field_name = $column['field'];

						//if($column['field'] == "account_name") {

						//	$field_name = "account_id";

						//}



						/** [IC] eggsurplus: if account name just show it */

						//if($task == "edit" && $column['field'] == "account_name") {

						//	$returnWidget = $value;

						//} else 

						if($this->editexisting != 1 && $task != "search") {

							foreach($options as $thisoption) {

								if( $thisoption->name == $value ) {

									$returnWidget = $thisoption->value;

								}

							}

						} else {

							if($column['size'] > 0) {

								//if($field_name == "account_id") {

								//	$returnWidget = '<select name="' . $field_name . '" class="' . $this->formFieldClass . '" style="width: 100%;" onchange="document.NewView.account_name.value = this.options[selectedIndex].text;">';

								//} else {

									$returnWidget = '<select name="' . $field_name . '" class="' . $this->formFieldClass . '" style="width: 100%;">';

								//}

							} else {

								//if($field_name == "account_id") {

								//	$returnWidget = '<select name="' . $field_name . '" class="' . $this->formFieldClass . '" onchange="document.NewView.account_name.value = this.options[selectedIndex].text;">';

								//} else {

									$returnWidget = '<select name="' . $field_name . '" class="' . $this->formFieldClass . '">';

								//}

							}

							foreach($options as $thisoption) {

								if( $thisoption->name == $value ) {

									$selectEd = 'selected ';

								} else {

									$selectEd = '';

								}

								$returnWidget .= '<option ' . $selectEd . 'value="' . $thisoption->name . '">' . $thisoption->value . '</option>';

							}

							$returnWidget .= '</select>';

						}

					}

                    break;

				case 'datetime':

					if(isset($value) && $value != '') {

						$currentUser = JFactory::getUser();

						$date_instance = new JDate($value);

						$date_instance->setOffset($currentUser->getParam('timezone'));



						$value = $date_instance->toFormat($this->datetimeformat);

					}

							

					if( ! (bool)$column['canedit'] && $task != "search") {

						if( isset($value) && $value != '') {

							$returnWidget = $value;

						} else

							$returnWidget = '';

					} else {

						if($column['size'] < 50 && $column['size'] > 0) {

							if($this->editexisting != 1 && $task != "search") {

								$returnWidget = nl2br($value);

							} else {

								$returnWidget = '<input type="text" name="' . $column['field'] . '" value="' . $value . '" class="' . $this->formFieldClass . '" size="' . $column['size'] . '" />';

							}

						} elseif($column['size'] >= 50) {

							if($this->editexisting != 1 && $task != "search") {

								$returnWidget = nl2br($value);

							} else {

								$returnWidget = '<textarea class="' . $this->formFieldClass . '" name="' . $column['field'] . '" cols="'.$column['size'] . '" rows="5">' . $value . '</textarea>';

							}

						} else {

							if($this->editexisting != 1 && $task != "search") {

								$returnWidget = nl2br($value);

							} else {

								$returnWidget = '<input type="text" name="' . $column['field'] . '" value="' . $value . '" class="inputbox" />';

							}

						}

					}

					break;

				case 'text':

					if( $this->editexisting != 1 || (! (bool)$column['canedit'] && $task != "search")) {

                    	$returnWidget = nl2br($value);

					} else {

						if($column['size'] == 0) { $column['size'] = 40; } //just default just in case

						$returnWidget = '<textarea class="' . $this->formFieldClass . '" name="' . $column['field'] . '" cols="'.$column['size'] . '" rows="5">' . $value . '</textarea>';

					}

					break;

                default:

					if( ! (bool)$column['canedit'] && $task != "search") {

						$returnWidget = nl2br($value);

						//$returnWidget .= '<input type="hidden" name="' . $column['field'] . '" value="' . $value . '" />';

					} else {

					/** [IC] 2006/05/22 - don't want huge textareas for search */

						if($task == "search") {

							$column['size'] = 30;

						}



						if($column['size'] < 50 && $column['size'] > 0) {

							if($this->editexisting != 1 && $task != "search") {

								$returnWidget = nl2br($value);

							} else {

								$returnWidget = '<input type="text" name="' . $column['field'] . '" value="' . $value . '" class="' . $this->formFieldClass . '" size="' . $column['size'] . '" />';

							}

						//} elseif($column['size'] >= 50) {

						//	if($task == "edit") {

						//		$returnWidget = nl2br($value);

						//	} else {

						//		$returnWidget = '<textarea class="' . $this->formFieldClass . '" name="' . $column['field'] . '" cols="'.$column['size'] . '" rows="5">' . $value . '</textarea>';

						//	}

						} else {

							if($this->editexisting != 1 && $task != "search") {

								$returnWidget = nl2br($value);

							} else {

								$returnWidget = '<input type="text" name="' . $column['field'] . '" value="' . $value . '" class="inputbox" />';

							}

						}

					}

                    break;

            }

        }



        return array($additional.$returnWidget,$showMe);

    }



    function _getAppropriateListfield($atype, $column, $value, $options, $ignorewidth=false) {

        //$this->Initialize();

        $returnWidget = '';



        $showMe = true;



        $widgetWidth = '';



        if( $ignorewidth ) {

            $widgetWidth = '100';

        } else {

            $widgetWidth = $column['size'];

        }



        if( ! (bool)$column['inlist'] ) {

            $showMe = false;

        } else {

            switch($atype) {

                case 'plaintext':

                    $returnWidget = nl2br($value);

                    break;

                case 'enum':

                    foreach($options as $thisoption) {

                        if( $thisoption->name == $value ) {

                            $returnWidget = $thisoption->value; //need this to get the appropriate value to show...

                            break;

                        }

                    }

                    break;

                case 'datetime':

                    if( isset($value) && $value != '') {

						$currentUser = JFactory::getUser();

						$date_instance = new JDate($value);

						$date_instance->setOffset($currentUser->getParam('timezone'));

						

						$returnWidget = $date_instance->toFormat($this->datetimeformat);

                    } else

                        $returnWidget = '';

                    break;

                default:

                    $returnWidget = nl2br($value);

                    break;

            }

        }



        return array($returnWidget,$showMe);

    }

}



?>