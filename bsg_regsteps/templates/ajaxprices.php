<?php
    
    $courses = $this->getVar('courses');
    $proPrice = $this->getVar('proprice');
?>
<content><![CDATA[<table width="100%" cellspacing="4" cellpadding="4" border="0"><thead>
<tr>
<?php if(count($courses)) { ?>
<td width="85%"><b>Description</b></td>
<td><b>Price</b></td></tr></thead><tbody>
<?php    
$totalSum = 0.0;
foreach ($courses as $course) {    
$totalSum = bcadd($totalSum, $course['price'], 3);?>
<tr><td><?php echo $course['name']?></td><td>$ <?php echo $this->customPrice($course['price']) ?></td></tr>
<?php   } ?>
<?php } ?>
<?php if($this->getVar('buypro')) { ?>
<tr><td><b>Additional features</b></td><td><b>Price</b></td></tr>
<?php
	$totalSum = bcadd($totalSum, $proPrice, 3);
	$proMem						= 'Professional membership';
	$proMem						.= ( 2 != $this->getVar('buypro') )
									? ''
									: ' renewal'
								;
?>
<tr><td><b><?php echo $proMem; ?>:</b></td><td>$ <?php echo $this->customPrice($proPrice) ?></td></tr>        
<?php } ?>
<?php if(bccomp($totalSum, "0.0", 2)) { ?>
<tr><td><b>Total:</b></td><td><b>$ <?php echo $this->customPrice($totalSum) ?></b></td></tr>        
<?php } ?>
</tbody>
</table>
]]></content>