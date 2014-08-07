<?php
require_once("../shdb.inc.php");

class KFFRecordModel extends RecordModel {
	public function __construct($table_reference = "") {
		parent::__construct("kff", $table_reference);
	}
}

class Order extends KFFRecordModel {
	public function __construct() {
		parent::__construct();
	}

	public function createOrder() {
		try {
			$this->_dbHandler->beginTransaction();
			$now = date("Y-m-d H:i:s");

			$this->setTableReference("tblOrder");
			$record = array(
				"member_account" => "user1",
				"order_create_time" => $now,
				"order_update_time" => $now,
			);
			$this->create($record);
			$order_id = $this->getLastInsertID();

			$this->setTableReference("tblItem");
			for ($i = 0; $i < mt_rand(1, 5); $i++) {
				$record = array(
					"product_id" => mt_rand(1, 2),
					"order_id" => $order_id,
					"item_quantity" => mt_rand(1, 10),
					"item_create_time" => $now,
					"item_update_time" => $now,
				);
				$this->create($record);
			}

			$this->_dbHandler->commit();
		} catch (Exception $e) {
			$this->_dbHandler->rollBack();
			echo $e->getMessage();
		}
	}

	public function readOrder() {
		$sql_params = array(
			"fields" => array("a.member_account", "b.order_id", "b.item_id", "b.product_id", "b.item_quantity", "c.product_name", "c.product_price"),
			"table_reference" => "tblOrder AS a INNER JOIN tblItem AS b USING(order_id) INNER JOIN tblProduct AS c USING(product_id)",
		);

		return $this->read($this->generateReadSQL($sql_params));
	}
}

class TblSpecialSN extends KFFRecordModel {
	public function __construct() {
		parent::__construct("tblSpecialSN");
	}

	public function initTable() {
		$type_list = array("A" => 1, "B" => 2);
		$now = date("Y-m-d H:i:s");
		$special_sn_list = array();

		foreach ($type_list as $prefix => $type) {
			for ($i = 0; $i < 5000; $i++) {
				do {
					$special_sn = $prefix.$this->generateSpecialSN();
				} while (in_array($special_sn, $special_sn_list));

				array_push($special_sn_list, $special_sn);

				$record = array(
					"special_sn" => $special_sn,
					"special_type" => $type,
					"special_create_time" => $now,
					"special_update_time" => $now,
				);
				$this->create($record);
			}
		}
	}

	public function getAllSpecialSN() {
		$sql_params = array(
			"fields" => array("special_sn"),
		);

		return $this->read($this->generateReadSQL($sql_params));
	}

	public function activateSpecialSN($user, $sn) {
		$now = date("Y-m-d H:i:s");
		$record = array(
			"member_account" => $user,
			"special_activate_time" => $now,
			"special_update_time" => $now,
		);
		$where_cond = array(
			"special_sn = ?" => $sn,
			"member_account != ?" => "",
		);
		$affected_count = $this->update($record, $where_cond);
	}

	private function generateSpecialSN() {
		$sn = "";
		$sn_length = 7;
		$code = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$code_length = strlen($code);

		for ($i = 0; $i < $sn_length; $i++) {
			$sn .= $code[mt_rand() % $code_length];
		}

		return $sn;
	}
}
?>