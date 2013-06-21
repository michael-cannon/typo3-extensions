<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addPiFlexFormValue('tfc_lifegroups_pi1', 'FILE:EXT:ext_tfc_lifegroups/flexform_ds_pi1.xml');

t3lib_div::loadTCA('tx_tfclifegroups_lifegroups');
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["semesters"]["config"]["type"]="group";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["semesters"]["config"]["internal_type"]="db";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["semesters"]["config"]["allowed"]="tx_tfclifegroups_semesters";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["semesters"]["config"]["minitems"]="0";

$TCA["tx_tfclifegroups_lifegroups"]["columns"]["day"]["config"]["type"]="group";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["day"]["config"]["internal_type"]="db";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["day"]["config"]["allowed"]="tx_tfclifegroups_days";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["day"]["config"]["minitems"]="0";

$TCA["tx_tfclifegroups_lifegroups"]["columns"]["recurrence"]["config"]["type"]="group";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["recurrence"]["config"]["internal_type"]="db";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["recurrence"]["config"]["allowed"]="tx_tfclifegroups_recurrences";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["recurrence"]["config"]["minitems"]="0";

$TCA["tx_tfclifegroups_lifegroups"]["columns"]["category"]["config"]["type"]="group";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["category"]["config"]["internal_type"]="db";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["category"]["config"]["allowed"]="tx_tfclifegroups_categories";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["category"]["config"]["minitems"]="0";

$TCA["tx_tfclifegroups_lifegroups"]["columns"]["ages"]["config"]["type"]="group";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["ages"]["config"]["internal_type"]="db";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["ages"]["config"]["allowed"]="tx_tfclifegroups_ages";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["ages"]["config"]["minitems"]="0";

$TCA["tx_tfclifegroups_lifegroups"]["columns"]["interests"]["config"]["type"]="group";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["interests"]["config"]["internal_type"]="db";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["interests"]["config"]["allowed"]="tx_tfclifegroups_interests";
$TCA["tx_tfclifegroups_lifegroups"]["columns"]["interests"]["config"]["minitems"]="0";
?>