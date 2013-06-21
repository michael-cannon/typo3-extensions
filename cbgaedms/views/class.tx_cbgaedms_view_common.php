<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Michael Cannon <michael@peimic.com>
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
 * Common class baseview for extension cbgaedms.
 *
 * @author	Michael Cannon <michael@peimic.com>
 * @package	TYPO3
 * @subpackage	tx_cbgaedms
 */

tx_div::load('tx_lib_phpTemplateEngine');

class tx_cbgaedms_view_common extends tx_lib_phpTemplateEngine {
	var $frequency = array(
		1 => '%%%frequencyI0%%%'
		, 7 => '%%%frequencyI1%%%'
		, 28 => '%%%frequencyI2%%%'
		, 90 => '%%%frequencyI3%%%'
		, 183 => '%%%frequencyI4%%%'
		, 365 => '%%%frequencyI5%%%'
	);

	var $reports = array(
		1 => '%%%reportI1%%%'
		, 2 => '%%%reportI2%%%'
		, 3 => '%%%reportI0%%%'
	);

	function asSuggest( $fieldName, $selected, $modelName, $loadName, $exclude = null, $onChange = null) {
		$fieldNameStr = $fieldName . 'Str';
		$fieldNameList = $fieldName . 'List';

		$options = array();
		$selectedName = '';

		$modelClassName = tx_div::makeInstanceClassName($modelName);
		$model = new $modelClassName($this->controller);
		$parameters = new tx_lib_parameters($this->controller);
		$parameters->set('exclude', $exclude);
		$model->$loadName($parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $tempOpt = $model->current();
			$name = $tempOpt->get('optionname');
			$value = $tempOpt->get('optionvalue');
			$options[] .= "'$name'";
			if ( $value == $selected )
				$selectedName = $name;
        }

		$html = '';
		$html .= '<input type="text" autocomplete="off" name="' .  $this->controller->getDefaultDesignator() .  '[' . $fieldNameStr . ']" id="' .  $fieldNameStr . '" value="' . $selectedName . '" />';
		$html .= '<div class="autocomplete" id="' . $fieldNameList . '" style="display: none"></div>';
		$html .= '<input type="hidden" name="' .  $this->controller->getDefaultDesignator() .  '[' . $fieldName . ']" id="' .  $fieldName . '" value="' . $selected . '" />';
		$html .= '
		<script type="text/javascript">
		//<![CDATA[
		var ' . $fieldNameList . ' = [ ';
		$html .= implode(',', $options);
		$html .= ' ];

		new Autocompleter.Local("' .$fieldNameStr .'", "' . $fieldNameList . '",
' . $fieldNameList . ', {
			partialChars: 1
			, fullSearch: "true"
		});
		//]]>
		</script>
		';

		return $html;
	}

	function printAsLinkText($title, $pid = false, $linkTitle = false, $action = false, $uid = false, $backId = false) {
		print $this->asLinkText($title, $pid, $linkTitle, $action, $uid, $backId);
	}

	function asLinkText($title, $pid = false, $linkTitle = false, $action = false, $uid = false, $backId = false) {
		if ( ! $title )
			return '';

	 	$link = $this->createLink($pid, $action, $uid, $backId);
		$link->label($title);

		if ( $linkTitle )
			$title = $linkTitle . ' ' . $title;

		$link->title($title);
		$tag = $link->makeTag();

		// double & encoding removal
		$tag = str_replace( 'amp;amp;', 'amp;', $tag );
		return $tag;
	}

	function printAsLink($pid = false, $action = false, $uid = false, $backId = false) {
	 	$link = $this->createLink($pid, $action, $uid, $backId);
		print $link->makeUrl();
	}

	function createLink($pid = false, $action = false, $uid = false, $backId = false) {
	 	$link = tx_div::makeInstance('tx_lib_link');
		$link->designator($this->getDesignator());
		if ( false === $pid ) {
			$destination = $this->getDestination();
		} elseif ( is_numeric( $pid ) ) {
			$destination = $pid;
		} else {
			$destination = $this->controller->configurations->get($pid);
		}
		$link->destination($destination);

		$searchString = $this->controller->parameters->get('searchString');
		if(strlen($searchString)) {
			$link->overruled(array('searchString' => $searchString));
			$link->noHash(); // Don't cache this dynamic query, else we risk a DOS attack!!!!
		}

		$parameters = array();

		if ( is_array( $uid ) ) {
			foreach ( $uid as $key => $value ) {
				$parameters[$key] = $value;
			}
		}

		if ( $action )
			$parameters['action'] = $action;

		if ( is_numeric( $uid ) )
			$parameters['uid'] = $uid; 
		elseif ( true === $uid || ( isset( $uid['uid'] ) && true === $uid['uid'] ) )
			$parameters['uid'] = $this->get('uid'); 

		if ( is_numeric( $backId ) )
			$parameters['backId'] = $backId;
		elseif ( $backId )
			$parameters['backId'] = $this->get('backId'); 

		$link->parameters($parameters);

		return $link;
	}

	// series of htmlspecialchars fouls things up
	function asText($key, $parseFuncKey = '') {
		$text = tx_lib_viewBase::asText($key, $parseFuncKey);
		$text = preg_replace( '#(&amp;)(amp;)+#', '\1', $text );
		return $text;
	}

	// series of htmlspecialchars fouls things up
	function asForm($key) {
		$text = tx_lib_viewBase::asForm($key);
		// $text = preg_replace( '#&amp;( {0,}\X+;)+#', '&\1', $text );
		$text = preg_replace( '#(&amp;)(amp;)+#', '\1', $text );
		return $text;
	}

	function setErrorMessageList() {
		$errorList = $this->get('_errorList');
		$tempList = array();

		foreach ( $errorList as $key => $value ) {
			$key = $value['field'];

			if ( ! is_array( $tempList[$key] ) )
				$tempList[$key] = array();

			$tempList[$key][] = $value['message'];
		}

		$this->set('errorMessageList', $tempList);
	}

	function printAsError( $key, $addBr = true ) {
		print $this->asError( $key, $addBr );
	}

	function asError( $key, $addBr = true, $class = 'errors' ) {
		if ( ! $this->get('hasErrors' ) )
			return '';

		$errors = $this->get('errorMessageList');

		if ( ! isset( $errors[$key] ) )
			return '';

		$errorMessages = $errors[$key];
		$errorMessages = '<span class="' . $class .'">' 
			. implode( ';', $errorMessages)
			. '</span>';

		return $addBr ? '<br />' . $errorMessages : $errorMessages;
	}

	function printAsUsersSuggest( $fieldName, $selected, $exclude = null ) {
		print $this->asSuggest( $fieldName, $selected, 'tx_cbgaedms_model_fe_users', 'loadUsersNoTitleAsOptions', $exclude );
	}

	function printAsLocationsSuggest( $fieldName, $selected ) {
		print $this->asSuggest( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgenciesAsOptions' );
	}

	function printAsLocationsSelect( $fieldName, $selected ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgenciesAsOptions' );
	}

	function printAsLocationParentsSelect( $fieldName, $selected ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgencyParentsAsOptions' );
	}

	function printAsLocationCitiesSuggest( $fieldName, $selected ) {
		print $this->asSuggest( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgencyCities' );
	}

	function printAsLocationCitiesSelect( $fieldName, $selected ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgencyCities' );
	}

	function printAsLocationStatesSuggest( $fieldName, $suggested ) {
		print $this->asSuggest( $fieldName, $suggested, 'tx_cbgaedms_model_agency', 'loadAgencyStates' );
	}

	function printAsLocationStatesSelect( $fieldName, $selected ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgencyStates' );
	}

	function printAsLocationCountriesSuggest( $fieldName, $suggested ) {
		print $this->asSuggest( $fieldName, $suggested, 'tx_cbgaedms_model_agency', 'loadAgencyCountries' );
	}

	function printAsLocationCountriesSelect( $fieldName, $selected ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgencyCountries' );
	}

	function printAsLocationSilosSuggest( $fieldName, $suggested ) {
		print $this->asSuggest( $fieldName, $suggested, 'tx_cbgaedms_model_agency', 'loadAgencySilos' );
	}

	function printAsLocationSilosSelect( $fieldName, $selected ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgencySilos' );
	}

	function asSelect( $fieldName, $selected, $modelName, $loadName, $exclude = null, $onChange = null) {
		$html = '';
		$html .= '<select name="' . $this->controller->getDefaultDesignator() .  '[' . $fieldName . ']" id="' . $fieldName . '"';
		$html .= $onChange ? ' onchange="' . $onChange . '"' : '';
		$html .= '>';
		$options = '';

		// first option is blank
		$options .= '<option value="">%%%pleaseSelect%%%</option>';

		$modelClassName = tx_div::makeInstanceClassName($modelName);
		$model = new $modelClassName($this->controller);
		$parameters = new tx_lib_parameters($this->controller);
		$parameters->set('exclude', $exclude);
		$model->$loadName($parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $tempOpt = $model->current();
			$name = $tempOpt->get('optionname');
			$name = ( ", ; " != $name ) ? $name : $tempOpt->get('altname');
			$value = $tempOpt->get('optionvalue');

			if ( $selected != $value )
				$options .= '<option value="' . $value . '">' . $name .  '</option>';
			else
				$options .= '<option value="' . $value . '" selected="selected">' . $name .  '</option>';
        }

		$html .= $options;
		$html .= '</select>';

		return $html;
	}

	function printAsAgenciesSelect( $fieldName, $selected, $exclude = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_agency', 'loadAgenciesAsOptions', $exclude );
	}

	function printAsSilosSelect( $fieldName, $selected, $exclude = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_silo', 'loadSilosAsOptions', $exclude );
	}

	function printAsCountriesSelect( $fieldName, $selected, $exclude = null, $onChange = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_static_countries', 'loadCountriesAsOptions', $exclude, $onChange );
	}

	function printAsStatesSelect( $fieldName, $selected, $exclude = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_static_country_zones', 'loadStatesAsOptions', $exclude );
	}

	function printAsUsersSelect( $fieldName, $selected, $exclude = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_fe_users', 'loadUsersAsOptions', $exclude );
	}

	function printAsUsergroupsSelect( $fieldName, $selected, $exclude = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_fe_groups', 'loadUsergroupsAsOptions', $exclude );
	}

	function printAsDoctypeSelect( $fieldName, $selected, $exclude = null ) {
		print $this->asSelect( $fieldName, $selected, 'tx_cbgaedms_model_doctype', 'loadDoctypesAsOptions', $exclude );
	}

	function printAsLinkDownload($uid = true) {
	 	$link = $this->createLink('documentPid','Document_Version_Download',$uid);
		print $link->makeUrl();
		print '" target="_blank';
	}

	function printAsCreateDocumentLink($locationUid) {
		$parameters = array( 'agencyId' => $locationUid );
	 	$link = $this->createLink('documentPid', 'Document_Form', $parameters);
		print $link->makeUrl();
	}

	function printAsCreateVersionLink($locationUid) {
		$parameters = array( 'docId' => $locationUid );
	 	$link = $this->createLink('documentPid', 'Document_Versions_Form', $parameters);
		print $link->makeUrl();
	}

	function printAsDualSelectLocations( $uid, $fieldName ) {
		print $this->asDualSelect( $uid, $fieldName, 'tx_cbgaedms_model_agency', 'loadAgenciesAsDualSelect' );
	}

	function printAsDualSelectUsers( $uid, $fieldName, $selected = null) {
		print $this->asDualSelect( $uid, $fieldName, 'tx_cbgaedms_model_fe_users', 'loadUserAsDualSelect', $selected );
	}

	function asDualSelect( $uid, $fieldName, $modelName, $loadName, $selected = null) {
		$left = $fieldName . 'left';
		$right = $fieldName . 'right';
		$extName = $this->controller->getDefaultDesignator();
		$html = <<<EOD
<!-- two column select -->
<script language="JavaScript">
<!--
var {$fieldName} = new OptionTransfer("{$left}","{$right}");
{$fieldName}.setAutoSort(true);
{$fieldName}.setDelimiter(",");
{$fieldName}.saveAddedLeftOptions("{$extName}[added{$left}]");
{$fieldName}.saveAddedRightOptions("{$extName}[added{$right}]");
{$fieldName}.saveNewLeftOptions("{$extName}[new{$left}]");
-->
</script>

<div class="selectLeft">
	<select name="{$left}" multiple="multiple" size="15" ondblclick="{$fieldName}.transferRight()">
EOD;

		$modelClassName = tx_div::makeInstanceClassName($modelName);
		$model = new $modelClassName($this->controller);
		$parameters = new tx_lib_parameters($this->controller);
		$parameters->set('uid', $uid);
		$parameters->set('fieldName', $fieldName);
		$parameters->set('selected', $selected);
		$model->$loadName($parameters);
		$optionsSelected = '';
        for($model->rewind(); $model->valid(); $model->next()) {
            $tempOpt = $model->current();
			$name = $tempOpt->get('optionname');
			$value = $tempOpt->get('optionvalue');

			$optionsSelected .= '<option value="' . $value . '">' . $name .  '</option>';
        }

		$html .= $optionsSelected;
		$html .= <<<EOD
	</select>
</div>
<div class="selectCenter">
		<input type="button" name="left" value="&lt;&lt;"
		onclick="{$fieldName}.transferLeft()" />
<br><br>
		<input type="button" name="left" value="All &lt;&lt;"
		onclick="{$fieldName}.transferAllLeft()" />
<br><br>
		<input type="button" name="right" value="&gt;&gt;"
		onclick="{$fieldName}.transferRight()" />
<br><br>
		<input type="button" name="right" value="All &gt;&gt;"
		onclick="{$fieldName}.transferAllRight()" />
</div>
<div class="selectLeft">
	<select name="{$right}" multiple="multiple" size="15" ondblclick="{$fieldName}.transferLeft()">
EOD;

		$parameters->set('exclude', true);
		$model->clear();
		$model->$loadName($parameters);
		$optionsNotSelected = '';
        for($model->rewind(); $model->valid(); $model->next()) {
            $tempOpt = $model->current();
			$name = $tempOpt->get('optionname');
			$value = $tempOpt->get('optionvalue');

			$optionsNotSelected .= '<option value="' . $value . '">' . $name .  '</option>';
        }

		$html .= $optionsNotSelected;
		$html .= <<<EOD
	</select>
</div>
<input type="hidden" name="{$extName}[added{$left}]" value="" />
<input type="hidden" name="{$extName}[added{$right}]" value="" />
<input type="hidden" name="{$extName}[new{$left}]" value="" />
<script language="JavaScript">
<!--
{$fieldName}.init(document.forms[0]);
-->
</script>
<!-- /two column select -->
EOD;

		return $html;
	}

	function printAsListUserAccess( $uid, $fieldName ) {
		print $this->asList( $uid, $fieldName, 'tx_cbgaedms_model_fe_users', 'loadUserAsDualSelect', 'usersPid', 'FE_Users_View');
	}

	function printAsListLocationAccess( $uid, $fieldName ) {
		print $this->asList( $uid, $fieldName, 'tx_cbgaedms_model_agency', 'loadLocationAccess', 'locationsPid', 'Location_View');
	}

	function asList($uid, $fieldName, $modelName, $loadName, $page = null, $action = null) {
		$html = '<ul>';
		$options = '';

		$modelClassName = tx_div::makeInstanceClassName($modelName);
		$model = new $modelClassName($this->controller);
		$parameters = new tx_lib_parameters($this->controller);
		if ( ! is_array( $uid ) ) {
			$parameters->set('uid', $uid);
		} else {
			foreach( $uid as $key => $value ) {
				$parameters->set($key, $value);
			}
		}
		$parameters->set('fieldName', $fieldName);
		$model->$loadName($parameters);
        for($model->rewind(); $model->valid(); $model->next()) {
            $tempOpt = $model->current();
		$name = $tempOpt->get('optionname');
		if ( ! is_array( $uid ) ) {
			$value = $tempOpt->get('optionvalue');
		} else {
			$value = $uid;
			$value['uid'] = $tempOpt->get('optionvalue');
		}

		$options .= '<li>' . $this->asLinkText($name, $page, 'View', $action, $value) .  '</li>';
        }

		$options = $options ? $options : "<li>%%%noLocation{$fieldName}Access%%%</li>";

		$html .= $options;
		$html .= '</ul>';

		return $html;
	}

	function printResultBrowser() {
		print $this->controller->get('resultBrowser');
	}

	function printAsUsers($key, $linked = false) {
		// userIds is csv based
		$userIds = tx_lib_viewBase::asText($key);
		$userIdArr = explode(',', $userIds);
		foreach ( $userIdArr as $keyU => $userId ) {
			$name = '';
			
			if ( $userId ) {
				// look up userId for name
				$modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_fe_users');
				$model = new $modelClassName($this->controller);
				$parameters = new tx_lib_parameters($this->controller);
				$parameters->set('uid', $userId);
				$model->load($parameters);
				if ( $model->count() ) {
					$tempOpt = $model->current();
					$name = $tempOpt->get('name');
					$name = ( ", " != $name ) ? $name : $tempOpt->get('altname');
				}
			}

			if ( $name && $linked )
				$userIdArr[$keyU] = $this->asLinkText($name,'usersPid','View','FE_Users_View',$userId);
			elseif ( $name )
				$userIdArr[$keyU] = $name;
			else
				unset( $userIdArr[$keyU] );
		}

		print implode('; ', $userIdArr);
	}

	function asUsersEmail($key) {
		// userIds is csv based
		$userIds = tx_lib_viewBase::asText($key);
		$userIdArr = explode(',', $userIds);
		foreach ( $userIdArr as $keyU => $userId ) {
			$name = '';
			
			if ( $userId ) {
				// look up userId for name
				$modelClassName = tx_div::makeInstanceClassName('tx_cbgaedms_model_fe_users');
				$model = new $modelClassName($this->controller);
				$parameters = new tx_lib_parameters($this->controller);
				$parameters->set('uid', $userId);
				$model->loadEmail($parameters);
				if ( $model->count() ) {
					$tempOpt = $model->current();
					$name = $tempOpt->get('name');
				}
			}

			if ( $name )
				$userIdArr[$keyU] = $name;
			else
				unset( $userIdArr[$keyU] );
		}

		return implode(', ', $userIdArr);
	}

	function printAsPeriodSelect( $fieldName, $selected ) {
		$options = array(
			'day' => 'Past Day'
			, 'week' => 'Past Week'
			, 'month' => 'Past Month'
			, 'year' => 'Past Year'
		);

		print $this->asArraySelect( $fieldName, $selected, $options );
	}

	function printAsReportTypeSelect( $fieldName, $selected ) {
		print $this->asArraySelect( $fieldName, $selected, $this->reports );
	}

	function printAsFrequencyTypeSelect( $fieldName, $selected ) {
		print $this->asArraySelect( $fieldName, $selected, $this->frequency );
	}

	function asArraySelect( $fieldName, $selected, $optionArray ) {
		$html = '';
		$html .= '<select name="' . $this->controller->getDefaultDesignator() .  '[' . $fieldName . ']" id="' . $fieldName . '"';
		$html .= '>';
		$options = '';

		// first option is blank
		$options .= '<option value="">%%%pleaseSelect%%%</option>';

        foreach($optionArray as $value => $name) {
			if ( $selected != $value )
				$options .= '<option value="' . $value . '">' . $name .  '</option>';
			else
				$options .= '<option value="' . $value . '" selected="selected">' . $name .  '</option>';
        }

		$html .= $options;
		$html .= '</select>';

		return $html;
	}

	function asReportType( $uid, $translate = false ) {
		if ( ! $translate )
			return $this->reports[$uid];
		else {
			switch( $uid ) {
				case 1:
					return 'Document Types';
					break;

				case 2:
					return 'Document Changes';
					break;

				case 3:
					return 'Locations';
					break;
			}
		}
	}

	function printAsReportType( $uid ) {
		print $this->asReportType( $uid );
	}

	function asFrequencyType( $uid ) {
		return $this->frequency[$uid];
	}

	function printAsFrequencyType( $uid ) {
		print $this->asFrequencyType( $uid );
	}

	var $region	= array(
				0	=> ''
				, 1	=> 'Lat-Am'
				, 2	=> 'North America'
				, 3	=> 'EMEA'
				, 4	=> 'Asia-Pac'
			);

	function keyRegionType( $value ) {
		$flip = array_flip($this->region);
		return ( isset($flip[$value] ) )
			? $flip[$value]
			: '';
	}

	function asRegionType( $uid ) {
		return $this->region[$uid];
	}

	function printAsRegionType( $uid ) {
		$key = tx_lib_viewBase::asText($uid);
		print $this->asRegionType( $key );
	}

	var $userStatus	= array(
				0	=> ''
				, 1	=> ''
				, 2	=> 'CMG'
				, 3	=> 'Draft FCB'
				, 4	=> 'Independent'
				, 5	=> 'Interpublic Corporate'
				, 6	=> 'Lowe'
				, 7	=> 'Lowe Healthcare'
				, 8	=> 'Media Brands'
				, 9	=> 'McCann'
				, 10	=> 'McCann Healthcare'
			);

	function keyUserStatusType( $value ) {
		$flip = array_flip($this->userStatus);
		return ( isset($flip[$value] ) )
			? $flip[$value]
			: '';
	}

	function asUserStatusType( $uid ) {
		return $this->userStatus[$uid];
	}

	function printAsUserStatusType( $uid ) {
		$key = tx_lib_viewBase::asText($uid);
		print $this->asUserStatusType( $key );
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/views/class.tx_cbgaedms_view_common.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cbgaedms/views/class.tx_cbgaedms_view_common.php']);
}

?>
