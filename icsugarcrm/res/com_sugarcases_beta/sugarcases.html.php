<?php
/* @version $Id: sugarcases.html.php,v 1.1.1.1 2010/04/15 10:03:39 peimic.comprock Exp $ */

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
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
include_once( 'sugarinc/sugarUtils.php');

/**
* @package Joomla_4.5.1
*/
class HTML_sugar_portal extends sugarHTML {
    function HTML_sugar_portal() {
        $this->Initialize();
    }

    function frontpage($bugs, $sugarApp, $columns, $pageNav=NULL, $task=NULL) {
        if( ! $bugs ) {
            $this->_renderTopNav(false);
            echo "No cases to display.";
            return;
        } else {
            $this->_renderTopNav(false);
            $this->_renderSearchFormHeader(); //need this otherwise it breaks some javascript
            $this->_renderList($bugs,$columns);
            $this->_renderPage($pageNav,$task);
        }
    }

    function Render($columns, $bug, $notes) {
        $this->_renderTopNav();
        $this->_renderHeader($bug);
        $this->_renderAppForm($columns, $bug, false);
        $this->_renderNotes($notes, $bug['id']);
        $this->_renderNewNoteForm($bug['id']);
    }

    function RenderNewForm($columns) {
        $this->_renderTopNav(false);
        $this->_renderHeader();
        $this->_renderAppForm($columns);
    }

    function RenderSearchForm($cases, $queryfields, $columnData, $pageNav=NULL, $searchQuery=NULL, $searchType='searchable') {
        $this->_renderTopNav(false);
        $this->_renderSearchFormHeader($searchType);
        $this->_renderSearchButtons($queryfields, $columnData, $searchType);
		if(isset($_SESSION['search_error']))
		{
			echo $_SESSION['search_error'];
			unset($_SESSION['search_error']);
        	$this->_renderSearchFormFooter();
		} else {
        	$this->_renderList($cases, $columnData, 'search');
      		$this->_renderSearchFormFooter();
        	if($cases) {
            	$this->_renderPage($pageNav,NULL,$searchQuery, $searchType);
            }
        }
        // do footer stuff
    }

    function _renderHeader($case = array('name'=>"New") ) {
        ?>
        <table class="contentpaneopen">
          <tr>
            <td class='contentheading'>Case: <?php echo $case['name']; ?></td>
          </tr>
        </table>
        <?php
    }

    function _renderAppForm($columns, $case = false, $new = true) {
        $tmpData = array();
        $tmpCase = array();

        foreach($columns['selected'] as $column) {
            $default[$column['field']] = $column;
        }

        if( isset($case) && $case ) {
            $tmpCase =& $case;
            $savetype = "saveedit";
        } else {
            foreach($default as $key=>$column) {
                $tmpCase[$key] = $column['default'];
            }
            $savetype = "savenew";
        }

        foreach($columns['data'] as $columnData) {
            $tmpData[$columnData->name] = $columnData;
        }
        ?>
        <script language="javascript">
			function submitForm()
			{
				f = document.SaveView;
				if( !ValidateForm(f)) return false;
				document.SaveView.button.value = 'Save';
				f.submit();
			}

        	function ValidateForm(thisForm)
        	{
        		//subject->name summary->summary steps to reproduce->steps expected results->expected_results
        		//name 255 len
        /**
				if(thisForm.name.value == "") {
					alert("Subject is required.");
					return false;
				}
				if(thisForm.summary.value == "") {
					alert("Summary is required.");
					return false;
				}
				if(thisForm.steps.value == "") {
					alert("Steps is required.");
					return false;
				}
				if(thisForm.expected_results.value == "") {
					alert("Expected Results is required.");
					return false;
				}
				if(thisForm.name.value.length > 255) {
					alert("Subject must be less than 255 characters. Please use the summary for anything larger.");
					return false;
				}
		*/

				return true;
        	}
        </script>
        <form name="SaveView" method="post" action="<?php echo $this->baseUrl; ?>">
            <?php echo $this->_getNeededFormFields('post'); ?>
            <input type="hidden" name="task" value="<?php echo $savetype; ?>">
        <table class="contentpaneopen">
            <td style="padding-bottom: 2px;" colspan="2">
		<?php 
		$save_newonly = false; //TODO: make into configurable option
		if($save_newonly) { ?>
                <input class="button" type="button" name="savebutton" value="Save" onclick="javascript:submitForm()" >
                <input type="hidden" name="button" value="Cancel"/>
                <input class="button" type="submit" name="cancelbutton" value="Cancel">
		<?php } else { ?>
                <input class="button" type="button" name="savebutton" value="Save" onclick="javascript:submitForm()" >
                <input type="hidden" name="button" value="Cancel"/>
                <input class="button" type="submit" name="cancelbutton" value="Cancel">
		<?php } ?>
                <input type="hidden" name="id" value="<?php echo $tmpCase['id']; ?>" />
            </td>
        <?php
        foreach($columns['selected'] as $column) {
        	if($new && !(bool)$column['canedit']) continue; //[IC] don't show if new and can't edit the field
            list($inputWidget,$showme) = $this->_getAppropriateFormfield(
                                                        $tmpData[$column['field']]->type,
                                                        $column,
                                                        $tmpCase[$column['field']],
                                                        $tmpData[$column['field']]->options
                                                        );
            if($showme) {
                ?>
                  <tr>
                    <td style="width: 20%; vertical-align: top;"><?php echo $column['name']; ?></td>
                    <td><?php echo $inputWidget ?></td>
                  </tr>
                <?php
            }
        }
        ?>
        </table>
        </form>
        <?php
    }

    function _renderSearchFormHeader($searchType='searchable') {
        list($sortcolumn, $sortorder) = $this->_getOrderBy();
    ?>
        <script type="text/javascript" language="JavaScript">
        <!-- Begin
        statustextholder=window.status;
        function clear_form(form) {
            form.name.value = '';
            form.case_number.value = '';
            form.priority.selectedIndex = 0;
            form.status.selectedIndex = 0;
        }

        function set_order_by_and_submit(form, orderby) {
            form.order_by.value = orderby;
            form.submit();
        }

        function show_status(statustext) {
            statustextholder=window.status;
            window.status = statustext;
            return true;
        }

        function restore_status() {
            window.status = statustextholder;
            return true;
        }
        //  End -->
        </script>

        <form method="get" action="<?php echo $this->baseUrl; ?>" name="SearchForm">
            <?php echo $this->_getNeededFormFields('post'); ?>
            <input type="hidden" name="task" value="search" />
            <input type="hidden" name="run_search" value="true" />
            <input type="hidden" name="order_by" value="<?php echo $sortcolumn . ',' . $sortorder; ?>" />
            <input type="hidden" name="searchType" value="<?php echo $searchType; ?>" />
    <?php
    }

    function _renderSearchFormFooter() {
    ?>
    </form>
    <?php
    }

    function _renderSearchButtons($queryfields, $columnData, $searchType='searchable') {
        foreach($columnData['data'] as $column) {
            $tmpData[$column->name] = $column;
        }

        $columns = array();
        foreach($columnData['selected'] as $column) {
            $columns[$column['field']] = $column;
        }

        $numberofboxes = count($queryfields);
        $numberofrows = ($numberofboxes-($numberofboxes % 2)) / 2;
        if( ($numberofboxes % 2) > 0) $numberofrows++;
        ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="0"   class="tabForm">
        <tr>
          <td style="width: 100%">
            <table style="width: 100%;" border="0" cellspacing="1" cellpadding="1" class="tabForm">
        <?php
        $counter = 0;
            echo '<tr>';
        foreach($queryfields as $field=>$value) {
            echo '<td style="width: 20%;">' . $tmpData[$field]->label . '</td>';
            echo '<td style="width: 25%;">';
            if (is_array($tmpData[$field]->options)) {
                $options = array_merge( array(
                                            array('value'=>'--None--',
                                                  'name'=>'')
                                              ), $tmpData[$field]->options);
            } else {
                $options = $tmpData[$field]->options;
            }

            list($formfield, $canshow) = $this->_getAppropriateFormField($tmpData[$field]->type,
                                                 $columns[$field],
                                                 $value,
                                                 $options,
                                                 true );
            echo $formfield;
            echo '</td>';
            if( ($counter % 2) == 1) echo '</tr><tr>';
            $counter++;
        }
        echo '</tr>';
        ?>

             <tr>
             	<td colspan="4">
             <?php
					$link = "index.php?option=com_sugarcases&amp;Itemid=". $Itemid ."&amp;task=search&amp;searchType=";
					if($searchType == "searchable") {
						$link .= "advanced";
						echo "<a href='$link' class='toclink'>Show Advanced &gt;&gt;</a>";
					} else {
						$link .= "searchable";
						echo "<a href='$link' class='toclink'>&lt;&lt; Hide Advanced</a>";
					}
			?>
             	</td>
             </tr>
            </table>
          </td>
		</tr>
        <tr>
        	<td style="width: 100%">
        		<div align="right">
                <input class="button" type="submit" name="button" value="Search"/>&nbsp;
                <input onclick="clear_form(this.form);" class="button" type="button" name="clear" value="Clear" />
                </div>
            </td>
         </tr>
        </table>
        <?php
    }

    function _renderNotes($notes, $bugID) {
        ?>
        <table class="contentpaneopen"><tr><td class='contentheading'>Notes</td></tr></table>
        <?php
        if($notes) {
            ?>
            <table class="contentpaneopen">

            <?php
            foreach($notes as $note) {

            	$filename = '(No Attachment)';
            	if(!empty($note['filename'])){
            		$filename= '<a href="index.php?option=' . _MYNAMEIS . '&task=download&noteid='.$note["id"].'&moduleid='.$bugID.'">'.$note["filename"] .'</a>';
            	}
            	/** [IC] 2006/3/10 added who the note was modified by */
                ?>

                 <tr><td><b>Subject:</b>&nbsp;<?php echo $note['name']; ?></td><td ><b>Last Modified:</b>&nbsp;
                 <?php 

						$currentUser = JFactory::getUser();
						$date_instance = new JDate($note['date_modified']);
						$date_instance->setOffset($currentUser->getParam('timezone'));

						$value = $date_instance->toFormat($this->datetimeformat);
						
                 		echo $value.' by '.$note['modified_by_name']; 
                 
                 ?></td></tr>
                <tr>
                    <td colspan="2" style="margin-left: 10%; margin-right: 10%;">
                    <b>Note:</b><br>
                    <?php echo nl2br($note['description']); ?><br />

                    </td>
                </tr>
                 <tr><td><b>Attachment:</b>&nbsp;<?php echo $filename; ?></td></tr>
                   <tr><td colspan=2><hr></td>

                <?php
            }
            ?>
            </table>
            <?php
        } else {
            echo "<p>No notes at this time.</p>";
        }
    }

    function _renderNewNoteForm($caseID) {
        ?>
        <table class="contentpaneopen">
          <tr>
            <td class='contentheading'>New Note</td>
          </tr>
        </table>
        <script language="javascript">
        	function requiredFields(thisform)
        	{
        		if(thisform.name.value.length == 0) {
        			alert("Subject field is required");
        			return false;
   				}
        	}
        </script>
        <form name="NewView" enctype="multipart/form-data" method="POST" action="index.php" onsubmit="return requiredFields(this)">
            <!-- MAX_FILE_SIZE must precede the file input field -->
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
			<?php echo $this->_getNeededFormFields('post'); ?>
            <input type="hidden" name="task" value="newnote" />
            <input type="hidden" name="caseID" value="<?php echo $caseID; ?>" />
            <input type="hidden" name="embed_flag" value="0" />
        <table class="contentpaneopen">
          <tr>
            <th>New Note</th>
          </tr>
          <tr>
            <td>Subject*: <input type="text" class="inputbox" name="name" /></td>
          </tr>
          <tr>
            <td>Note:</td>
          </tr>
          <tr>
            <td>
                <textarea class="inputbox" cols="50" rows="10" name="description"></textarea>
            </td>
          <tr>
            <td>File Attachment: <input type="file" name="attachment"/></td>
          </tr>
		  <tr><td height="5">&nbsp;</td></tr>
          </tr>
          <?php /*<tr>
            <td>Attachment:
              <input name="attachment" type="file" class="inputbox" />
            </td>
          </tr> */ ?>
          <tr>
            <td>
                <input class="button" type="submit" name="button" value="Save Note" />
            </td>
          </tr>
        </table>
        </form>
        <?php
    }

    function _renderTopNav($isHome=false) {
    ?>
    <table width='100%'>
      <tr>
        <td style="width:60%" valign="middle" align='left'>
          <span class="buttonheading">
            <a href="<?php echo $this->_getBaseUrl() ?>">Home</a> <?php echo $this->navSeparator; ?>
            <a href="<?php echo $this->_getBaseUrl() ?>&task=new">New...</a> <?php echo $this->navSeparator; ?>
            <a href="<?php echo $this->_getBaseUrl() ?>&task=search">Search</a>
            <?php
            if($isHome) {
                ?>
                <?php echo $this->navSeparator; ?> <a href="<?php echo $this->_getBaseUrl() ?>&task=refresh">Refresh</a>
                <?
            }
            ?>
          </span>
        </td>
        <td style="text-align: right;" valign="middle">
        <?php
        global $task;
        
        if($task != 'search') {
        	// [IC] eggsurplus: quicksearch param added to aid with number or name search...only do on top bar search
        ?>
            <form method="get" action="index.php">
                <input type="hidden" name="option" value="<?php echo _MYNAMEIS; ?>" />
                <input type="hidden" name="task" value="search" />
                <input type="hidden" name="case_number" value="" />
                <input type="hidden" name="priority" value="" />
                <input type="hidden" name="status" value="" />
                <input type="hidden" name="quicksearch" value="true" />
                <input type="text" name="name" size="20" class="inputbox" />&nbsp;
                <input type="submit" name="Search" class="button" value="Go"/>
            </form>
        <?
        } else {
            echo '&nbsp;';
        } ?>
        </td>
      </tr>
    </table>
    <?php
    }

    function _renderList($cases = false, $columnData, $task='home') {
        if( ! $cases ) {
            echo "<p>No cases to display.</p>";
            return;
        }
        $columns = $columnData['selected'];

        $columnInfo = array();
        foreach($columnData['data'] as $aColumn) {
            $columnInfo[$aColumn->name] = $aColumn;
        }

        echo '<table cellpadding="0" cellspacing="0" class="contentpaneopen;" style="width: 100%;">
            ';
        echo '<tr>';

        list($sortcolumn, $sortorder) = $this->_getOrderBy();
		global $illegalSortFields;
        foreach($columns as $column) {
            if( (bool)$column['inlist']) {
                echo '<td style="text-align: left; padding-right: 2px; padding-left: 2px;">';
                $orderby = $this->_getNewOrderBy($sortcolumn, $sortorder, $column['field']);
                $onClick = '';
                $href = 'href=#';
                if(!in_array($column['field'],$illegalSortFields)) {
					if($task == 'search') {
						$onClick = 'onClick="javascript: set_order_by_and_submit(document.SearchForm, \'' . $orderby . '\');"';
					} else {
						$href = 'href="'.$this->_getBaseUrl().'&task=' . $task . '&order_by=' . $orderby . '"';
					}
                
					echo '<a ' . $href . ' ' . $onClick . ' onmouseover="javascript: return show_status(\'Sort by ' . $orderby . '\');"' .
						' onMouseout="javascript: return restore_status();"'.
						'>' . $column['name'] . '</a>';
					if($column['field'] == $sortcolumn) {
						echo "<img style='vertical-align: middle' src=\"images/M_images/sort".($sortorder == "asc"?"0":"1").".png\" width=\"11\" height=\"11\" border=\"0\" />";
					}
				} else {
					echo $column['name'];
				}
                echo '</td>';
            }
        }

        echo '<td>&nbsp;</td></tr>';
        $ctr = 0;
        //echo "rowband: $this->rowband";
        $rowband = $this->rowband;
        foreach($cases as $case) {
        	if(empty($case['id'])) continue; //for some reason it added an additional row
        	$ctr++;
            echo "<tr ".($ctr % 2 ==0?" bgcolor='".$rowband."'":"").">"; //shade every-other row for ease of reading
           // echo "<tr>";
            /** [IC] row colors */
            //echo "<tr class='sectiontableentry".(($ctr % 2)+1)."'>";
            foreach($columns as $column) {
                if( (bool)$column['inlist']) {
                    echo '<td valign="top" style="padding: 2px;">';
                    //var_dump($columnInfo[$column['field']]);
                    if( !isset($column['options']) ) $column['options'] = array();
                    list($inputWidget,$showme) = $this->_getAppropriateListfield(
                                                                $column['type'],
                                                                $column,
                                                                $case[$column['field']],
                                                                $columnInfo[$column['field']]->options
                                                                );
                    echo $inputWidget;
                    //$case[ $column['field'] ];
                    echo '</td>';
                }
            }
            echo '<td valign="top" style="padding-right: 4px;"><a href="' . $this->_getBaseUrl() . '&task=edit&caseID=' . $case['id'] . '">View</a>
                </td>';
            echo '</tr>';
        }
        echo '</table>
            ';
    }

    function _renderPage(&$pageNav,$task=NULL,$searchQuery=NULL,$searchType='searchable')
    {
        list($sortcolumn, $sortorder) = $this->_getOrderBy();
        
        global $task;
    ?>
    	<style type="text/css">
			.list-footer {
				display: inline;
			}
			div.limit {
				float: left;
			}
			span.pagination {
				text-align: center;
			}
			div.counter {
				float: right;
			}
    	</style>
        <script type="text/javascript" language="JavaScript">
        <!-- Begin
        function submitForm(form,limitstart,limit) {
            form.limitstart.value = limitstart;
            form.limit.value = limit;

            form.submit();
        }

        //  End -->
        </script>

        <form method="get" action="<?php echo $this->baseUrl; ?>" name="SearchListForm">
            <?php echo $this->_getNeededFormFields('post'); ?>
            <input type="hidden" name="task" value="<?php echo $task; ?>" />
            <input type="hidden" name="run_search" value="true" />
            <input type="hidden" name="order_by" value="<?php echo $sortcolumn . ',' . $sortorder; ?>" />
            <input type="hidden" name="searchType" value="<?php echo $searchType; ?>" />
            <input type="hidden" name="limitstart" value="0" />
	<?php
		if(!empty($searchQuery)) {
			foreach($searchQuery as $field=>$value) {
				echo "<input type='hidden' name='$field' value='$value'/>";
			}
		}
	?>


		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="4">&nbsp;</td>
			</tr>
			<tr>
			<?php if($this->fullpagination == 1) { ?>
				<td width="100%" colspan="4" align="center">
					<?php echo $pageNav->getListFooter(); ?>
				</td>
			<?php } else { ?>
				<td align="left" valign="bottom">
				<?php

				define('_PN_PREVIOUS',"Previous");
				define('_PN_NEXT',"Next");
				
				global $Itemid;
				$link = JURI::base().'/index.php?option=com_sugarcases&amp;Itemid='. $Itemid;
				if(isset($task)) $link .= "&amp;task=".$task;
				if($pageNav->limitstart == 0) {
					echo '<span class="pagenav">&lt;&nbsp;'. _PN_PREVIOUS .'</span> ';
				} else {
					if(!empty($searchQuery)) {
						echo '<a href="javascript:submitForm(document.SearchListForm,'.($pageNav->limitstart - $pageNav->limit) .','.$pageNav->limit.')" class="pagenav" title="'. _PN_PREVIOUS .'">&lt;&nbsp;'. _PN_PREVIOUS .'</a> ';
					} else {
						echo '<a href="'.  "$link&amp;limitstart=".($pageNav->limitstart - $pageNav->limit)  .'" class="pagenav" title="'. _PN_PREVIOUS .'">&lt;&nbsp;'. _PN_PREVIOUS .'</a> ';
					}
				}
				?>
				</td>
				<td align="center" width="80%">&nbsp;</td>
				<td align="right" valign="bottom" style="padding-right: 10px">
				<?php
				if($this->fullpagination == 1 && $pageNav->total <= $pageNav->limitstart + $pageNav->limit) {
					echo '<span class="pagenav">'. _PN_NEXT .'&nbsp;&gt;</span> ';
				} else {
					if(!empty($searchQuery)) {
						echo '<a href="javascript:submitForm(document.SearchListForm,'.($pageNav->limitstart + $pageNav->limit) .','.$pageNav->limit.')" class="pagenav" title="'. _PN_NEXT .'">'. _PN_NEXT .'&nbsp;&gt;</a> ';
					} else {
						echo '<a href="'.  $link ."&amp;limitstart=".($pageNav->limitstart + $pageNav->limit)  .' " class="pagenav" title="'. _PN_NEXT .'">'. _PN_NEXT . '&nbsp;&gt;</a> ';
					}
				}
				?>
				</td>
				<td align="right" valign="bottom" width="20%">
				Show:
				<?php
				if(!empty($searchQuery)) {
					$link = 'SearchListForm';
					echo $pageNav->getLimitBox( $link, 'search' );
				} else {
					$link = 'index.php?option=com_sugarcases&amp;Itemid='. $Itemid;
					if(isset($task)) $link .= "&amp;task=".$task;
					echo $pageNav->getLimitBox( $link );
				}
				?>
				</td>
			<?php } ?>
			</tr>
		</table>
		</form>
	<?php
    }
}

?>
