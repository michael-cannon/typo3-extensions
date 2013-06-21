<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>
<content><![CDATA[
<select name="tx_cbgaedms[state]" id="state">
	<option value="">%%%pleaseSelect%%%</option>
<?php
if ( 0 < $this->count() ) {
	for($this->rewind(); $this->valid(); $this->next()) {
		$entry = $this->current();
		echo '<option value="';
		echo $entry->asInteger('uid');
		echo '">';
		echo utf8_encode($entry->get('zn_name_local'));
		echo '</option>';
	}   
} else {
	echo '<option value="0">%%%noState%%%</option>';
}
?>
</select>
]]></content>
