<?php

/***************************************************************
*  Copyright notice
*  
*  (c) 2004  ()
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/** 
 * Module 'Orders Management' for the 'extendedshop' extension.
 *
 * @author	 <>
 */

// DEFAULT initialization of a module [BEGIN]
unset ($MCONF);
require ("conf.php");
require ($BACK_PATH . "init.php");
require ($BACK_PATH . "template.php");
$LANG->includeLLFile("EXT:extendedshop/mod1/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib . "class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]

class tx_extendedshop_module1 extends t3lib_SCbase {
	var $pageinfo;

	var $daysToDelivery = 10; // Default days from the order date and the delivery date
	var $num_orders_for_page = 10; // Number of orders for page
	var $currency = "\$"; // Currency

	/**
	 * 
	 */
	function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

		parent :: init();

		/*
		if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig() {
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"1" => $LANG->getLL("function1"
			),
			"2" => $LANG->getLL("function2"
		), "3" => $LANG->getLL("function3"),));
		parent :: menuConfig();
	}

	// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

		$this->doc = t3lib_div :: makeInstance("bigDoc");
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form = '<form action="" method="POST">';

		// JavaScript
		$this->doc->JScode = '
						<script language="javascript" type="text/javascript">
							script_ended = 0;
							function jumpToUrl(URL)	{
								document.location = URL;
							}
							
							function displayConfirm(testo)
							{
								if (confirm(testo)) {return(true)}
								else {return(false)}
							}
						</script>
					';
		$this->doc->postCode = '
						<script language="javascript" type="text/javascript">
							script_ended = 1;
							if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
						</script>
					';

		$headerSection = $this->doc->getHeader("pages", $this->pageinfo, $this->pageinfo["_thePath"]) . "<br>" . $LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path") . ": " . t3lib_div :: fixed_lgd_pre($this->pageinfo["_thePath"], 50);

		$this->content .= $this->doc->startPage($LANG->getLL("title"));
		$this->content .= $this->doc->header($LANG->getLL("title"));
		$this->content .= $this->doc->spacer(5);
		$this->content .= $this->doc->section("", $this->doc->funcMenu($headerSection, t3lib_BEfunc :: getFuncMenu($this->id, "SET[function]", $this->MOD_SETTINGS["function"], $this->MOD_MENU["function"])));
		$this->content .= $this->doc->divider(5);

		// Render content:
		$this->moduleContent();
		$this->content .= $this->doc->spacer(10);
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent() {

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 */
	function moduleContent() {
		switch ((string) $this->MOD_SETTINGS["function"]) {
			case 1 :
				$content = $this->statistics();
				$this->content .= $this->doc->section("Orders statistics:", $content, 0, 1);
				break;
			case 2 :
				$content = $this->listOrders();
				$this->content .= $this->doc->section("Orders:", $content, 0, 1);
				break;
			case 3 :
				$content = $this->listCompletedOrders();
				$this->content .= $this->doc->section("Completed orders:", $content, 0, 1);
				break;
		}
	}

	function statistics() {
		$content = "<div align=center><strong>Orders statistics</strong></div><br /><br />";
		$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_extendedshop_orders', 'complete<>1 AND deleted<>1', '', '', '');
		$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res2);
		$content .= "Orders not yet completed: " . $num . "<br />";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('SUM(total) AS totale', 'tx_extendedshop_orders', 'complete<>1 AND deleted<>1', '', '', '');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$content .= "Total for orders not yet completed: " . $this->priceFormat($row["totale"]) . "<br />";
		$content .= "<br /><hr /><br /><br />";
		$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_extendedshop_orders', 'complete=1 AND deleted<>1', '', '', '');
		$num = $GLOBALS['TYPO3_DB']->sql_num_rows($res2);
		$content .= "Completed orders: " . $num . "<br />";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('SUM(total) AS totale', 'tx_extendedshop_orders', 'complete=1 AND deleted<>1', '', '', '');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$content .= "Total for completed orders: " . $this->priceFormat($row["totale"]) . "<br />";
		$content .= "<br /><hr /><br /><br />";
		return $content;
	}

	/**
	 * This function lists the orders
	 */
	function listOrders() {
		$delete = t3lib_div :: _GP("delete");
		$del_order = t3lib_div :: _GP("del_order");
		$complete = t3lib_div :: _GP("complete");
		$pageNumber = t3lib_div :: _GP("pageNumber");
		$orderId = t3lib_div :: _GP("orderId");
		$ordersubmit = t3lib_div :: _GP("ordersubmit");
		$order = t3lib_div :: _GP("order");

		if ($pageNumber == "")
			$pageNumber = 1;

		if ($delete) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_orders', 'uid=' . $del_order . ' AND deleted<>1', '', '', '');
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tt_content', 'pid=' . $row["pid"]);
			$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('pages', 'uid=' . $row["pid"]);
			$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_extendedshop_orders', 'uid=' . $del_order);
			$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_extendedshop_rows', 'ordercode=' . $del_order);
		}
		if ($complete) {
			$updateFields["complete"] = "1";
			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_extendedshop_orders', 'uid=' . $orderId, $updateFields);
		}
		if (isset ($ordersubmit)) {
			foreach ($order as $key => $value) {
				$updateField["status"] = $value["status"];
				$updateField["ordernote"] = $value["ordernote"];
				$data = explode("-", $value["deliverydate"]);
				if ($data[0] > 0 && $data[0] < 13 && $data[1] > 0 && $data[1] < 32 && $data[2] > 1970 && $data[2] < 2200)
					$deliveryDate = mktime(0, 0, 0, $data[0], $data[1], $data[2]);
				else
					$deliveryDate = 0;
				$updateField["deliverydate"] = $deliveryDate;
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_extendedshop_orders', 'uid=' . $key, $updateField);
			}
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_orders', 'complete<>1 AND deleted<>1', 'date DESC', '', '');

		$num_orders = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		// NUMERO DI ORDINI PER PAGINA
		$num_orders_for_page = $this->num_orders_for_page;

		// Numero di pagine
		$num_pages = ceil($num_orders / $num_orders_for_page);

		$content = "<form name='ordermanagement' method='post'><table width=100% padding=3>";

		$pageLink = "<a href='index.php?id=0&pageNumber=all&SET[function]=2'>show all</a> |";
		for ($i = 1; $i <= $num_pages; $i++) {
			if ($i == $pageNumber) {
				$pageLink .= " <b>" . $i . "</b>";
			} else {
				$pageLink .= " <a href='index.php?id=0&pageNumber=" . $i . "&SET[function]=2'>" . $i . "</a>";
			}
		}
		$content .= "<tr><td colspan='7' align='right'>" . $pageLink . "</td></tr>";
		$content .= "<tr><td colspan=7><hr /></td></tr>";
		$content .= "<tr><td><b>Number</b></td><td><b>Order date</b></td><td><b>Customer name</b></td><td><b>Shipping</b></td><td><b>Payment</b></td><td align=right><b>Total</b></td><td></td></tr>";
		$content .= "<tr><td colspan=7><hr /></td></tr>";

		// Scarta i primi ordini se sono in pagine successive
		if ($pageNumber != "all" && $pageNumber > 1) {
			for ($i = 0; $i < ($pageNumber -1) * $num_orders_for_page; $i++) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			}
		} else
			if ($pageNumber == "all") {
				$num_orders_for_page = $num_orders;
			}

		// Prepara la select per gli stati degli ordini
		$resStatus = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_status', 'sys_language_uid=0 AND hidden<>1 AND deleted<>1', 'priority ASC', '', '');
		$selectStatus = "<select name='order[###UID###][status]'>";
		while ($rowStatus = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resStatus)) {
			$selectStatus .= "<option value='" . $rowStatus["uid"] . "'###" . $rowStatus["uid"] . "###>" . $rowStatus["status"] . "</option>";
		}
		$selectStatus .= "</select>";

		//while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
		for ($i = 0; $i < $num_orders_for_page && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); $i++) {
			$resUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid=' . $row['customer'], '', '', '');
			$rowUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resUser);

			$totalProducts = 0;
			$resProd = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_rows', 'ordercode=' . $row["uid"], '', '', '');
			while ($rowProd = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resProd)) {
				$totalProducts += $rowProd["price"] * $rowProd["quantity"];
			}
			$priceShipping = trim(substr($row["shipping"], strrpos($row["shipping"], "-") + 1));
			if ($priceShipping == "")
				$priceShipping = "0,00";
			$pricePayment = trim(substr($row["payment"], strrpos($row["payment"], "-") + 1));
			if ($pricePayment == "")
				$pricePayment = "0,00";
			$totale = $this->priceFormat($totalProducts + $pricePayment + $priceShipping);
			$tot = $totalProducts + $pricePayment + $priceShipping;
			// Aggiorna il totale...
			if ($tot != $row['total']) {
				$update["total"] = $tot;
				$resUpdate = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_extendedshop_orders', 'uid=' . $row["uid"], $update);
			}

			// Gestione dello status
			$status = str_replace("###UID###", $row["uid"], $selectStatus);
			$status = str_replace("###" . $row["status"] . "###", " selected", $status);
			$status = ereg_replace("###[0-9]###", '', $status);

			if ($i % 2 == 0)
				$color = "#CCCCCC";
			else
				$color = "#FFFFFF";

			$deliveryDate = $row["deliverydate"];
			if ($deliveryDate == 0 || $deliveryDate == "") {
				$deliveryDate = mktime(0, 0, 0, date("m", $row["date"]), date("d", $row["date"]) + $this->daysToDelivery, date("Y", $row["date"]));
			}
			if ($deliveryDate < time())
				$colorAlert = " style='background-color: #FF3333;'";
			else
				$colorAlert = "";

			$content .= "<tr style='background-color:" . $color . ";'><td><b>" . $row['code'] . "</b></td><td>" . date("d M Y - H:i:s", $row['date']) . "</td><td>" . $rowUser['name'] . "</td><td>" . $row["shipping"] . "</td><td>" . $row["payment"] . "</td><td align=right>" . $this->currency . " " . $totale . "</td>";
			if ($row["tx_wfinvoice_num_fatture"] == '') {
				$content .= "<td><a href='index.php?id=0&delete=true&del_order=" . $row['uid'] . "&SET[function]=2' title='Delete' onClick='return displayConfirm(\"Delete order?\");'><img src='../res/delete.gif' border=0></a> <a href='index.php?id=0&complete=true&orderId=" . $row['uid'] . "&SET[function]=2' title='Complete' onClick='return displayConfirm(\"Order completed?\");'><img src='../res/check.gif' border=0></a></td>";
			} else {
				$content .= "<td></td>";
			}
			$content .= "</tr>";
			$content .= "<tr style='background-color:" . $color . ";'><td></td><td colspan='2'>Order status: " . $status . "</td><td colspan='2'" . $colorAlert . ">Expected delivery date: <input type='text' size='12' maxlength='10' name='order[" . $row["uid"] . "][deliverydate]' value='" . date("m-d-Y", $deliveryDate) . "'> <i>(mm-dd-yyyy)</i></td><td align=right></td><td><a href='../../../../index.php?id=" . $row["pid"] . "' target=_blank><img src='../res/page.gif' border=0></a></td></tr>";
			$content .= "<tr style='vertical-align: top; background-color:" . $color . ";'><td></td><td colspan='4'>Seller note:&nbsp;&nbsp; <textarea style='vertical-align:top;' cols='74' name='order[" . $row["uid"] . "][ordernote]'>" . $row["ordernote"] . "</textarea></td><td align=right></td><td></td></tr>";
			$content .= "<tr><td colspan=7><hr /></td></tr>";
		}
		$content .= "</table><br /><input type='submit' name='ordersubmit' value='Update'></form>";
		return $content;
	}

	/**
	 * This function lists the orders
	 */
	function listCompletedOrders() {
		$pageNumber = t3lib_div :: _GP("pageNumber");
		if ($pageNumber == "")
			$pageNumber = 1;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_orders', 'complete=1 AND deleted<>1', 'date DESC', '', '');

		$num_orders = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		// NUMERO DI ORDINI PER PAGINA
		$num_orders_for_page = $this->num_orders_for_page;

		// Numero di pagine
		$num_pages = ceil($num_orders / $num_orders_for_page);

		$content = "<table width=100% padding=3>";

		$pageLink = "<a href='index.php?id=0&pageNumber=all&SET[function]=2'>show all</a> |";
		for ($i = 1; $i <= $num_pages; $i++) {
			if ($i == $pageNumber) {
				$pageLink .= " <b>" . $i . "</b>";
			} else {
				$pageLink .= " <a href='index.php?id=0&pageNumber=" . $i . "&SET[function]=2'>" . $i . "</a>";
			}
		}
		$content .= "<tr><td colspan='7' align='right'>" . $pageLink . "</td></tr>";
		$content .= "<tr><td><b>Number</b></td><td><b>Order date</b></td><td><b>Customer name</b></td><td><b>Shipping</b></td><td><b>Payment</b></td><td align=right><b>Total</b></td></tr>";

		// Scarta i primi ordini se sono in pagine successive
		if ($pageNumber != "all" && $pageNumber > 1) {
			for ($i = 0; $i < ($pageNumber -1) * $num_orders_for_page; $i++) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			}
		} else
			if ($pageNumber == "all") {
				$num_orders_for_page = $num_orders;
			}

		//while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))		{
		for ($i = 0; $i < $num_orders_for_page && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res); $i++) {
			$resUser = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid=' . $row['customer'], '', '', '');
			$rowUser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resUser);

			$totalProducts = 0;
			$resProd = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_extendedshop_rows', 'ordercode=' . $row["uid"], '', '', '');
			while ($rowProd = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resProd)) {
				$totalProducts += $rowProd["price"] * $rowProd["quantity"];
			}
			$priceShipping = trim(substr($row["shipping"], strrpos($row["shipping"], "-") + 1));
			if ($priceShipping == "")
				$priceShipping = "0,00";
			$pricePayment = trim(substr($row["payment"], strrpos($row["payment"], "-") + 1));
			if ($pricePayment == "")
				$pricePayment = "0,00";
			$totale = $this->priceFormat($totalProducts + $pricePayment + $priceShipping);
			$tot = $totalProducts + $pricePayment + $priceShipping;
			// Aggiorna il totale...
			if ($tot != $row['total']) {
				$update["total"] = $tot;
				$resUpdate = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_extendedshop_orders', 'uid=' . $row["uid"], $update);
			}

			if ($i % 2 == 0)
				$color = "#CCCCCC";
			else
				$color = "#FFFFFF";
			$content .= "<tr style='background-color:" . $color . ";'><td><b>" . $row['code'] . "</b></td><td>" . date("d M Y - H:i:s", $row['date']) . "</td><td>" . $rowUser['name'] . "</td><td>" . $row["shipping"] . "</td><td>" . $row["payment"] . "</td><td align=right>" . $this->currency . " " . $totale . "</td></tr>";
		}
		$content .= "</table>";
		return $content;
	}

	/**
	 * Formatting a price
	 */
	function priceFormat($double, $priceDecPoint = ",", $priceThousandPoint = ".") {
		return number_format($double, 2, $priceDecPoint, $priceThousandPoint);
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/extendedshop/mod1/index.php"]) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/extendedshop/mod1/index.php"]);
}

// Make instance:
$SOBE = t3lib_div :: makeInstance("tx_extendedshop_module1");
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE)
	include_once ($INC_FILE);

$SOBE->main();
$SOBE->printContent();
?>