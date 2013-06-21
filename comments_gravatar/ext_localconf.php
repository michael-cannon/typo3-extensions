<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

if (TYPO3_MODE == 'FE')    {
	require_once(t3lib_extMgm::extPath('comments_gravatar') . 'class.tx_commentsgravatar.php');
}

// hook registering
$TYPO3_CONF_VARS['EXTCONF']['comments']['comments_getComments']['comments_gravatar'] = 'EXT:comments_gravatar/class.tx_commentsgravatar.php:&tx_commentsgravatar->comments_getComments';
?>