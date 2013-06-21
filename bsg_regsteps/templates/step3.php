<?php
    $step = $this->getVar('step1');
    $courses = $this->getVar('courses');
    $priceSum = 0;
?>
<h3>Confirm your agenda</h1>

<table cellpadding="5" cellspacing="1" border="1">
<thead>
<tr>
    <th style="width:200px">Course</th>
    <th style="width:300px">Price</th>
</tr>
</thead>
<tbody>
<?php foreach ($courses as $c) {    
    $priceSum += $c['price'];    
    echo "<tr>";
    echo "<td>{$c['name']}</td>";
    echo "<td>{$c['price']}</td>";
    echo "</tr>"; 
    
}
echo "<tr>";
echo "<td><b>Total:</b></td>";
echo "<td><b>$priceSum</b></td>";
echo "</tr>"; 
?>
</tbody>
</table>
<br>
<form enctype="multipart/form-data" method="POST">
    <input type="submit" name="bsg_step_3[submit]" value="Continue">
</form>
