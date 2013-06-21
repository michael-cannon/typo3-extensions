<?php

########################################################################
# Extension Manager/Repository config file for ext: "mc_podcast"
#
# Auto generated 08-01-2009 06:09
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Podcast System',
	'description' => 'Podcast system based on News (tt_news extension) RSS2 and RSS2 iTunes Complaint, too add mp3 player to news (XSPF Web Music Player)',
	'category' => 'fe',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Máximo Cuadros Ortiz',
	'author_email' => 'mcuadros@gmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.5.0-0.0.0',
			'php' => '3.0.0-0.0.0',
			'tt_news' => '',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:75:{s:22:"class.ux_tx_ttnews.php";s:4:"b892";s:12:"ext_icon.gif";s:4:"da47";s:17:"ext_localconf.php";s:4:"28b5";s:14:"ext_tables.php";s:4:"245a";s:14:"ext_tables.sql";s:4:"e74c";s:28:"ext_typoscript_constants.txt";s:4:"01d9";s:24:"ext_typoscript_setup.txt";s:4:"7a77";s:16:"locallang_db.php";s:4:"6116";s:15:"musicplayer.swf";s:4:"780a";s:13:"posdcast.tmpl";s:4:"ad32";s:24:"tx_extrep_e6f1e686b1.gif";s:4:"da47";s:14:"doc/manual.sxw";s:4:"22b2";s:30:"pi1/class.tx_mcpodcast_pi1.php";s:4:"6c45";s:17:"pi1/locallang.php";s:4:"a890";s:24:"pi1/static/editorcfg.txt";s:4:"1cbd";s:30:"getid3/extension.cache.dbm.php";s:4:"ebf1";s:32:"getid3/extension.cache.mysql.php";s:4:"595b";s:21:"getid3/getid3.lib.php";s:4:"0eb9";s:17:"getid3/getid3.php";s:4:"452c";s:30:"getid3/module.archive.gzip.php";s:4:"7f81";s:29:"getid3/module.archive.rar.php";s:4:"556a";s:30:"getid3/module.archive.szip.php";s:4:"7023";s:29:"getid3/module.archive.tar.php";s:4:"e852";s:29:"getid3/module.archive.zip.php";s:4:"c2d3";s:33:"getid3/module.audio-video.asf.php";s:4:"2db0";s:34:"getid3/module.audio-video.bink.php";s:4:"056b";s:33:"getid3/module.audio-video.flv.php";s:4:"6d7d";s:38:"getid3/module.audio-video.matroska.php";s:4:"a09d";s:34:"getid3/module.audio-video.mpeg.php";s:4:"21e1";s:33:"getid3/module.audio-video.nsv.php";s:4:"4d87";s:39:"getid3/module.audio-video.quicktime.php";s:4:"180d";s:34:"getid3/module.audio-video.real.php";s:4:"d413";s:34:"getid3/module.audio-video.riff.php";s:4:"a48b";s:33:"getid3/module.audio-video.swf.php";s:4:"4361";s:27:"getid3/module.audio.aac.php";s:4:"7e99";s:27:"getid3/module.audio.ac3.php";s:4:"20a8";s:26:"getid3/module.audio.au.php";s:4:"ebc4";s:27:"getid3/module.audio.avr.php";s:4:"9f36";s:28:"getid3/module.audio.bonk.php";s:4:"55a9";s:28:"getid3/module.audio.flac.php";s:4:"71c2";s:26:"getid3/module.audio.la.php";s:4:"7f2d";s:28:"getid3/module.audio.lpac.php";s:4:"ed95";s:28:"getid3/module.audio.midi.php";s:4:"748a";s:27:"getid3/module.audio.mod.php";s:4:"25b7";s:30:"getid3/module.audio.monkey.php";s:4:"4af2";s:27:"getid3/module.audio.mp3.php";s:4:"ad6d";s:27:"getid3/module.audio.mpc.php";s:4:"90f0";s:27:"getid3/module.audio.ogg.php";s:4:"8683";s:33:"getid3/module.audio.optimfrog.php";s:4:"5b72";s:28:"getid3/module.audio.rkau.php";s:4:"897a";s:31:"getid3/module.audio.shorten.php";s:4:"7c7a";s:27:"getid3/module.audio.tta.php";s:4:"8bc8";s:27:"getid3/module.audio.voc.php";s:4:"2a9d";s:27:"getid3/module.audio.vqf.php";s:4:"32ff";s:31:"getid3/module.audio.wavpack.php";s:4:"9709";s:29:"getid3/module.graphic.bmp.php";s:4:"4c6f";s:29:"getid3/module.graphic.gif.php";s:4:"ecc4";s:29:"getid3/module.graphic.jpg.php";s:4:"2dc2";s:29:"getid3/module.graphic.pcd.php";s:4:"82c4";s:29:"getid3/module.graphic.png.php";s:4:"89e4";s:30:"getid3/module.graphic.tiff.php";s:4:"f80a";s:26:"getid3/module.misc.exe.php";s:4:"88a1";s:26:"getid3/module.misc.iso.php";s:4:"33e0";s:28:"getid3/module.tag.apetag.php";s:4:"68bc";s:27:"getid3/module.tag.id3v1.php";s:4:"1d5f";s:27:"getid3/module.tag.id3v2.php";s:4:"b4f6";s:29:"getid3/module.tag.lyrics3.php";s:4:"f033";s:23:"getid3/write.apetag.php";s:4:"0c1b";s:22:"getid3/write.id3v1.php";s:4:"cd9f";s:22:"getid3/write.id3v2.php";s:4:"febb";s:24:"getid3/write.lyrics3.php";s:4:"e2b4";s:25:"getid3/write.metaflac.php";s:4:"f9f3";s:16:"getid3/write.php";s:4:"2e8f";s:21:"getid3/write.real.php";s:4:"5043";s:30:"getid3/write.vorbiscomment.php";s:4:"36ce";}',
);

?>