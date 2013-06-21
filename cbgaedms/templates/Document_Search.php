<?php
/**
 * Example template for phpTemplateEngine.
 *
 * Edit this template to match your needs.
 * $entry is of type tx_lib_object and represents a single data row.
 */
?>

<?php if (!defined ('TYPO3_MODE'))      die ('Access denied.'); ?>

<?php if($this->isNotEmpty()) { ?>
        <ol>
<?php } ?>
<?php for($this->rewind(); $this->valid(); $this->next()) {
     $entry = $this->current();
?>
        <li>
			<h3>Insert HTML/Code to display elements here</h3>
        </li>
<?php } ?>
<?php if($this->isNotEmpty()) { ?>
        </ol>
<?php } ?>
