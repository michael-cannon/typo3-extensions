<?php
    $zones = $this->getVar('zones');
?>
<content><![CDATA[
<select style="width:220px" name="bsg_step_1[state]" id="state">
<?php   
    foreach ($zones as $z) {
        $selected = $z['zn_code'] == 'PCO' ? ' selected="selected" ' : '';
        echo "<option value='{$z['zn_code']}'{$selected}>" . utf8_encode($z['zn_name_local']) . "</option>";
    }
?>
</select>
]]></content>