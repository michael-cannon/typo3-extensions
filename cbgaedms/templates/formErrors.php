<?php
$hasErrors = false;
if ( $this->get('_errorCount') ) {
	$this->setErrorMessageList();
	$hasErrors = true;
}

$this->set('hasErrors', $hasErrors);
?>
