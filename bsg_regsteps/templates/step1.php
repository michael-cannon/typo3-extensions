<h3>
Please use the following dropdown menu to select training courses
</h3>


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
$data = $this->getVar('form');
$courseId = intval($data['course']);
?>

<form method="POST" enctype="multipart/form-data">
<table>
    <tr>
        <td>Select course:</td>
        <td>
            <select name="bsg_step_1[course]" size="1" style="width:400px">
<?php
foreach ($this->getVar("courses") as $course) {
    $selected = $course['uid'] == $courseId ? ' selected="selected" ': '';
    echo "<option value='{$course['uid']}'{$selected}>{$course['name']}</option>";
}
?>
            </select>               
        </td>
    </tr>

    <tr>
        <td colspan="2"><input type="submit" value="Submit"></td>
    </tr>   
    
</table>
</form>

<div>

</div>


