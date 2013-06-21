<?php
/*echo "You selected course: ";
var_dump($this->getVar('step1'));*/
?>


<?php

if($this->hasErrors()) {
    echo "<div style='color:red'>";
    foreach ($this->getErrors() as $err) {
        echo $err;
        echo "<br>";        
    }
    echo "</div>";
}
?>

<?php
$form = $this->getVar('form');

$flds = array ('name','title','company','department','address', 
               'zip', 'phone', 'fax', 'pmail', 'bmail', 'state', 'country', 'city');
               
foreach ($flds as $f) {
    $$f = isset($form[$f]) ? htmlspecialchars(stripslashes($form[$f])) : null;
}

?>

<h2>
    Fill your personal information:
</h2>
<form enctype="multipart/form-data" method="POST">
<table>
    <tr>
        <td>Name: </td>
        <td><input type="text" name="bsg_step_2[name]" value="<?php echo $name ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td>Title:</td>
        <td><input type="text" name="bsg_step_2[title]" value="<?php echo $title ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td>Company:</td>
        <td><input type="text" name="bsg_step_2[company]" value="<?php echo $company ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td>Department:</td>
        <td><input type="text" name="bsg_step_2[department]" value="<?php echo $department ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td>Address:</td>
        <td><textarea name="bsg_step_2[address]" style="width:220px;height:60px"><?php echo $address ?></textarea></td>
    </tr>
    <tr>
        <td>City:</td>
        <td><input type="text" name="bsg_step_2[city]" style="width:220px;" value="<?php echo $city ?>"></td>
    </tr>
    
    <tr>
        <td>State/province:</td>
        <td>
        <select style="width:220px" name="bsg_step_2[state]">
<?php
/*
foreach ($this->getVar("states") as $c) {
    $selected = $c['uid'] == $state ? ' selected="selected" ': '';
    echo "<option value='{$c['uid']}'{$selected}>{$c['name']}</option>";
}
*/
?>                    
        </select>
        </td>
    </tr>
    <tr>
        <td>Country:</td>
        <td>
        <select style="width:220px" name="bsg_step_2[country]">
<?php
/*
foreach ($this->getVar("countries") as $c) {
    $selected = $c['uid'] == $country ? ' selected="selected" ': '';
    echo "<option value='{$c['uid']}'{$selected}>{$c['name']}</option>";
}*/
?>                    
        </select>
        </td>
    </tr>
     <tr>
        <td>Zip:</td>
        <td><input type="text" name="bsg_step_2[zip]"  value="<?php echo $department ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td>Phone:</td>
        <td><input type="text" name="bsg_step_2[phone]"  value="<?php echo $phone ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td>Fax:</td>
        <td><input type="text" name="bsg_step_2[fax]"  value="<?php echo $fax ?>" style="width:220px"></td>
    </tr>    
    <tr>
        <td>Business email for<br>verification:</td>
        <td><input type="text" name="bsg_step_2[bmail]"  value="<?php echo $bmail ?>" style="width:220px"></td>
    </tr>    
    <tr>
        <td>Preferred email:</td>
        <td><input type="text" name="bsg_step_2[pmail]"  value="<?php echo $pmail ?>" style="width:220px"></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="Continue">
        </td>
    </tr>
</table>
</form>
<div>
<pre>
<?php
/*
    if($this->isPostMethod()) {
        var_dump($_POST);
        var_export(array_keys($_POST['bsg_step_2']));
    }
    */
?>
</pre>
</div>