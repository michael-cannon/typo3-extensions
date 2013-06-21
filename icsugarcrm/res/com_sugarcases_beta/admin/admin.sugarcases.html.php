<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

class HTML_sugar_bugs_admin {
	function showConfigForm(&$configObj) {
	    echo $configObj->getConfigForm();
	}

	function showFieldsForm($option, &$selectedFields, $availableFields, &$page) {
	?>
		<script language='javascript'>
			function listItemTask( id, task ) {
				var f = document.adminForm;
				var cb = eval( 'f.' + id );
				if(cb.value != "") {
					f.cid.value = cb.value;
				} else {
					f.cid.value = id;
				}
				f.task.value = task;
				f.submit();
			}
		</script>
        <form action="index2.php" method="post" name="adminForm">
        <input type="hidden" name="option" value="<?php echo _MYNAMEIS; ?>" />
		<input type="hidden" name="task" value="saveformfields" />
		<input type="hidden" name="reorder_field" value="" />
		<input type="hidden" name="reorder_dir" value="0" />
		<input type="hidden" name="cid" value="" />
        <table class="adminheading">
        <tr>
            <th>Sugar Cases Field Selection</th>
            <td style="text-align: right;">
                <!--<img src="components/<?php echo _MYNAMEIS; ?>/images/powered_by_sugarcrm.gif" />-->
            </td>
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
          <th style="width: 10%">
					<?php echo JHTML::_('grid.sort',   'Order', 's.ordering', @$lists['order_Dir'], @$lists['order'] ); ?>
					<?php echo JHTML::_('grid.order',  $rows ); ?>
          </th>
          <th style="width: 5%">Show</th>
          <th style="width: 5%">Show in Lists</th>
          <th style="width: 5%">Searchable</th>
          <th style="width: 5%">Advanced Search</th>
          <th style="width: 5%">Req by Sugar</th>
          <th style="text-align: left;">Caption</th>
          <th style="text-align: center;">Can Edit?</th>
          <th style="width: 10%; text-align: left;">Type</th>
          <th style="width: 10%; text-align: left;">Size</th>
          <th style="width: 15%; text-align: left; ">Field Name</th>
          <th style="width: 20%; text-align: left;">Default Value</th>
        </tr>
		<?php
		$k =0;

        if(!empty($availableFields)){
        
        $available_array = array();
		foreach($availableFields as $name) {
			$available_array[$name->name] = array( 
									'field' => $name->name,
									'show' => False,
                                    'canedit' => False,
									'name' => $name->label,
									'type' => $name->type,
                                    'options' => $name->options,
                                    'required' => $name->required,
                                    'inlist' => 0,
                                    'default' => '',
                                    'searchable' => 0,
                                    'parameters' => '',
                                    'advanced' => 0,
                                    'size' => 10);
        }
        
        $select_array = array();
		foreach($selectedFields as $selectedField) {
			$select_array[$selectedField['field']] = array( 'id' => $selectedField['id'],
							'field' => $selectedField['field'],
							'show' => $selectedField['show'],
							'canedit' => $selectedField['canedit'],
							'name' => $selectedField['name'],
							'type' => $available_array[$selectedField['field']]['type'],
							'options' => $available_array[$selectedField['field']]['options'],
							'required' => $available_array[$selectedField['field']]['required'],
							'size' => $selectedField['size'],
							'inlist' => $selectedField['inlist'],
							'default' => $selectedField['default'],
							'searchable' => $selectedField['searchable'],
							'parameters' => $selectedField['parameters'],
							'advanced' => $selectedField['advanced'],
							'ordering' => $selectedField['ordering']);
		}

		$all_fields = array_merge($available_array,$select_array);
		usort( $all_fields, "compareOrdering");

		$fields = count($all_fields);
        $m = -1;
		foreach($all_fields as $currentField) {
			$m++;
                                   
			if(empty($currentField['id'])) $currentField['id'] = '';
			if(empty($currentField['ordering'])) $currentField['ordering'] = $m+1;
                                                                
            if( (bool)$currentField['required'] ) {
                $thisReq = true;
            } else {
                $thisReq = false;
            }
			echo '<tr class="row' . $k . '"><td class="order">';
			echo '<input type="hidden" name="' . $currentField['field'] . 'id" value="' . $currentField['id'] . '" />';
			if(!empty($currentField['id'])) {
			echo '<input type="hidden" name="cb'.$m.'" value="'.$currentField['id'].'" />';
			?>	
					<span><?php echo $page->orderUpIcon( $m, true, 'orderup', 'Move Up', true ); ?></span>
					<span><?php echo $page->orderDownIcon( $m, $fields, true, 'orderdown', 'Move Down', true ); ?></span>
			<?php
			} else {
				echo 'Save First';
			}
			?>
					
					<input type="text" name="<? echo $currentField['field']; ?>ordering" size="5" value="<?php echo $currentField['ordering']; ?>" class="text_area" style="text-align: center" />
			<?php
			echo '</td><td style="text-align: center">';
            if($thisReq) {
                echo '&nbsp;';
                echo '<input name="' . $currentField['field'] . 'show" type="hidden" value="on" />';
            } else {
                echo '<input name="' . $currentField['field'] . 'show" type="checkbox"';
                if( $currentField['show'] == 1) echo ' checked';
                echo ' />';
            }
			echo '</td><td style="text-align: center;">';

            echo '<input name="' . $currentField['field'] . 'inlist" type="checkbox"';
            if( $currentField['inlist'] == 1) echo ' checked';
            echo ' />';

            echo '</td><td style="text-align: center;">';

            echo '<input name="' . $currentField['field'] . 'searchable" type="checkbox"';
            if( $currentField['searchable'] == 1) echo ' checked';
            echo ' />';

            echo '</td><td style="text-align: center;">';
            echo '<input name="' . $currentField['field'] . 'advanced" type="checkbox"';
            if( $currentField['advanced'] == 1) echo ' checked';
            echo ' />';

            echo '</td><td style="text-align: center;">';
            if($thisReq) {
                echo "Y";
            } else {
                echo '&nbsp;';
            }
            echo '</td><td>
                ';
            $newCaption = str_replace('_',' ',$currentField['field']);
            $newCaption = ucwords($newCaption);
			echo '<input type="text" name="' . $currentField['field'] . 'name" value="' . (!empty($currentField['name'])?$currentField['name']:$newCaption) . '" />';
			//echo '<input type="hidden" name="' . $currentField['field'] . 'name" value="' . $newCaption . '" />';
            //echo $newCaption;
			echo '</td><td style="text-align: center">';
			echo '<input name="' . $currentField['field'] . 'canedit" type="checkbox"';
			if( $currentField['canedit'] == 1) echo ' checked';
			echo ' />';
			echo '</td><td>';
			echo $currentField['type'];
			echo '<input type="hidden" name="' . $currentField['field'] . 'type" value="' . $currentField['type'] . '" />';
			echo '</td><td>';
			echo '<select name="' . $currentField['field'] . 'size">
				';
			for($i = 0; $i <= 100; $i = $i+10) {
				$selected = '';
				if($currentField['size'] == $i)
					$selected = ' selected';
				echo '<option' . $selected . ' value="' . $i . '">'. $i . '</option>
					';
			}
			echo '</select>';
			echo '</td><td>';
			echo $currentField['field'];
			echo '<input type="hidden" name="' . $currentField['field'] . 'field" value="' . $currentField['field'] . '" />';
			echo '</td><td>';
            //if( is_array($currentField['options']) ) {
            /** [IC] 2007/06/12 jeggers: use enum to determine if dropdown */
            if($currentField['type'] == "enum") {
                echo '<select name="' . $currentField['field'] . 'default">';
                foreach($currentField['options'] as $thisoption) {
                    if($thisoption->name == $currentField->default) {
                        $selectEd = 'selected ';
                    } else {
                        $selectEd = '';
                    }
                    echo '<option ' . $selectEd . 'value="' . $thisoption->name . '">' . $thisoption->value . '</option>
                        ';
                }
                echo '</select>';
            } else {
                echo '<input type="text" name="' . $currentField['field']. 'default" value="' . $currentField['default'] . '" />';
            }
            echo '</td></tr>
			';
			$k = 1 - $k;
		}
        }

		?>
		</table>
		</form>
        </td>
        </tr>
        </table>
	<?php
	}

}


function compareOrdering($a, $b)
{
	if(empty($a['id'])) return 1;  //if a new field coming from sugar
	if(empty($b['id'])) return -1;
	if ($a['ordering'] == $b['ordering']) {
		return 0;
	}
	return ($a['ordering'] < $b['ordering']) ? -1 : 1;
}


?>