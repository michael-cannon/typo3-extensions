<?php
// Getting the pid list via the flexform, and using it if no one is defined in typoscript
$pid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'page') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'page') : $this->conf['mthfeedit.']['pid'];
$this->conf['mthfeedit.']['pid'] =  $pid ? $pid : $GLOBALS['TSFE']->id;

// Set the noSpecialLoginForm
if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'noSpecialLoginForm', 'sCreate')) {
  $this->conf['mthfeedit.']['create.']['noSpecialLoginForm'] = 1;
}
// Set if forntend login is required
$this->conf['mthfeedit.']['requireLogin'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'requireLogin') ? 1 : $this->conf['mthfeedit.']['requireLogin'];
$this->conf['mthfeedit.']['no_header'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'noHeader') ? 1 : $this->conf['mthfeedit.']['no_header'];
$this->conf['mthfeedit.']['show_help_icons'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showHelpIcons') ? 1 : $this->conf['mthfeedit.']['show_help_icons'];
// Sets the allowed groups for frontend editing
$this->conf['mthfeedit.']['allowedGroups'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'allowedGroups') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'allowedGroups') : $this->conf['mthfeedit.']['allowedGroups'];
// Sets the pages to clear catch of
$this->conf['mthfeedit.']['clearCacheOfPages'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'clearCacheOfPages') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'clearCacheOfPages') : $this->conf['mthfeedit.']['clearCacheOfPages'];

// Set the delete, create and edit flag
if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disableDelete')) {
  $this->conf['mthfeedit.']['delete'] = 0;
}
if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disableCreate', 'sCreate')) {
  $this->conf['mthfeedit.']['create'] = 0;
}
if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'disableEdit', 'sEdit')) {
  $this->conf['mthfeedit.']['edit'] = 0;
}

// Set the default cmd
$cmdInt = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'defaultCmd');
switch($cmdInt) {
 case 1:
   $this->conf['mthfeedit.']['defaultCmd'] = 'create';
 break;
 case -1:
   $this->conf['mthfeedit.']['defaultCmd'] = 'edit';
 break;
}



?>