<?php
/**
* Gallery configuration constants
*/
define('THUMB_HEIGHT', 60);
define("THUMB_WIDTH", 80);
define("THUMB_QUALITY", 60);
define("THUMB_DEPTH", 8);
define('LARGE_THUMB_HEIGHT', 120);
define("LARGE_THUMB_WIDTH", 160);
define('IMAGE_ROWS',3);
define('IMAGE_COLUMNS',3);
define('IMAGE_WIDTH',600);
define('IMAGE_HEIGHT','');

/**
* Gallery configuration constants
*/
define('DOWNLOAD_NORMAL', 1);
define('DOWNLOAD_ZIPPED', 2);

define('ENLARGE_VIEW_ICON', t3lib_extMgm::siteRelPath('bahag_photogallery').'templates/img/zoom.gif');
define('JPG_ICON', t3lib_extMgm::siteRelPath('bahag_photogallery').'templates/img/img1.gif');
define('ZIP_ICON', t3lib_extMgm::siteRelPath('bahag_photogallery').'templates/img/img2.gif');

/**
* Label constants
*/
define('NEXT_PAGE', 'Vor');
define('PREVIOUS_PAGE', 'Zur�ck');
define('GO_FIRST', 'Go first');
define('GO_LAST', 'Go last');
define('ZIPPED_DOWNLOAD_TEXT', 'Download as zip file');

$IMAGE_TYPES = array(1 => 'GIF', 'JPG', 'PNG', 'SWF', 'PSD', 'BMP', 'TIFF', 'TIFF', 'JPC', 'JP2', 'JPX', 'JB2', 'SWC', 'IFF', 'WBMP', 'XBM');
?>
