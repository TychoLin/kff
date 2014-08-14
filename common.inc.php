<?php
require_once("shdb.inc.php");

class KFFRecordModel extends RecordModel {
	public function __construct() {
		parent::__construct("kff");
	}
}

class Order extends KFFRecordModel {
	public function __construct() {
		parent::__construct();
	}

	public function createOrder($user) {
		$now = date("Y-m-d H:i:s");

		$records = array();
		array_push($records, array($user, $now, $now));

		$sql_params = array(
			"table_reference" => "tblOrder",
			"fields" => array("member_account", "order_create_time", "order_update_time"),
			"records" => $records,
		);

		$this->create($sql_params);

		$order_id = $this->getLastInsertID();

		$order_no = "KFF".date("Ymd").$order_id;
		$sql_params = array(
			"table_reference" => "tblOrder",
			"record" => array("order_no" => $order_no),
			"where_cond" => array("order_id = ?" => $order_id),
		);

		$this->update($sql_params);

		return $order_id;
	}

	public function getOrder($order_id) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "tblOrder",
			"where_cond" => array("order_id = ?" => $order_id),
		);

		$result = $this->read($sql_params);

		if (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	public function getUserOrder($user) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "tblOrder",
			"where_cond" => array("member_account = ?" => $user),
		);

		return $this->read($sql_params);
	}

	public function getOrderNoOrder($order_no) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "tblOrder",
			"where_cond" => array("order_no = ?" => $order_no),
		);

		$result = $this->read($sql_params);

		if (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	public function makeOrderPaid($order_id, $sn_id) {
		$now = date("Y-m-d H:i:s");
		$sql_params = array(
			"table_reference" => "tblOrder",
			"record" => array("order_status" => 2, "order_product_sn_id" => $sn_id, "order_update_time" => $now),
			"where_cond" => array("order_id = ?" => $order_id),
		);

		$this->update($sql_params);
	}
}

class Trade extends KFFRecordModel {
	private $_tradeProviderList = array(1 => "allpay", 2 => "android", 3 => "ios");

	public function __construct() {
		parent::__construct();
	}

	public function createTrade($params) {
		$now = date("Y-m-d H:i:s");

		$params["trade_create_time"] = $now;

		$records = array();
		array_push($records, array_values($params));

		$sql_params = array(
			"table_reference" => "tblTrade",
			"fields" => array_keys($params),
			"records" => $records,
		);

		$this->create($sql_params);
	}

	public function getTrade($user) {
		$sql_params = array(
			"fields" => array("a.*"),
			"table_reference" => "tblTrade AS a INNER JOIN tblOrder AS b USING (order_id)",
			"where_cond" => array("b.member_account = ?" => $user, "b.order_status = ?" => 2),
		);

		return $this->read($sql_params);
	}

	public function isAllowedProvider($trade_provider) {
		return in_array($trade_provider, array_keys($this->_tradeProviderList));
	}

	public function isMobileProvider($trade_provider) {
		return in_array($trade_provider, array(2, 3));
	}
}

class MovieWatchSN extends KFFRecordModel {
	private $_SNTypeList = array(1 => "A", 2 => "B", 3 => "C");

	public function __construct() {
		parent::__construct();
	}

	public function activateSN($sn_watch_code, $user) {
		$now = date("Y-m-d H:i:s");

		// disable user's other SN if available
		$sql_params = array(
			"table_reference" => "tblMovieWatchSN",
			"record" => array("sn_status" => 3, "sn_update_time" => $now),
			"where_cond" => array("member_account = ?" => $user),
		);

		$this->update($sql_params);

		// activate user's new SN
		$sql_params = array(
			"table_reference" => "tblMovieWatchSN",
			"record" => array("sn_status" => 2, "member_account" => $user, "sn_activate_time" => $now, "sn_update_time" => $now),
			"where_cond" => array("sn_watch_code = ?" => $sn_watch_code),
		);

		$this->update($sql_params);
	}

	public function disableSN($sn_watch_code) {
		$now = date("Y-m-d H:i:s");
		$sql_params = array(
			"table_reference" => "tblMovieWatchSN",
			"record" => array("sn_status" => 3, "sn_update_time" => $now),
			"where_cond" => array("sn_watch_code = ?" => $sn_watch_code),
		);

		$this->update($sql_params);
	}

	public function consumeWatchCount($sn_watch_code) {
		$now = date("Y-m-d H:i:s");

		$sql_params = array(
			"table_reference" => "tblMovieWatchSN",
			"record" => array("sn_watch_count = IF(sn_watch_count = 0, 0, sn_watch_count - 1)" => null, "sn_update_time" => $now),
			"where_cond" => array("sn_watch_code = ?" => $sn_watch_code),
		);

		$this->update($sql_params);
	}

	public function getUserSNInfo($user) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "tblMovieWatchSN",
			"where_cond" => array("member_account = ?" => $user, "sn_status = ?" => 2),
		);

		$sn_infos = $this->read($sql_params);

		if (count($sn_infos) == 1) {
			return $sn_infos[0];
		} else {
			return null;
		}
	}

	public function getSNInfo($sn_watch_code) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "tblMovieWatchSN",
			"where_cond" => array("sn_watch_code = ?" => $sn_watch_code, "sn_status != ?" => 3),
		);

		$result = $this->read($sql_params);

		if (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	public function isSNActivated($sn_watch_code) {
		$sn_info = $this->getSNInfo($sn_watch_code);

		if (is_null($sn_info)) {
			return null;
		}

		return ($sn_info["sn_status"] == 2) ? true : false;
	}

	public function initSN() {
		$sn_list = array();

		foreach ($this->_SNTypeList as $sn_type => $prefix) {
			for ($i = 0; $i < 10000; $i++) {
				do {
					$sn_watch_code = $prefix.$this->generateSNCode();
				} while (in_array($sn_watch_code, $sn_list));

				array_push($sn_list, $sn_watch_code);

				$this->createNewSN($sn_watch_code, $sn_type);
			}
		}
	}

	public function createNewSN($sn_watch_code, $sn_type) {
		$now = date("Y-m-d H:i:s");

		// -1: unlimited times, 3: three times
		$sn_watch_count = -1;
		if ($sn_type == 3) {
			$sn_watch_count = 3;
		}

		$records = array();
		array_push($records, array($sn_watch_code, $sn_type, 1, $sn_watch_count, $now, $now));

		$sql_params = array(
			"table_reference" => "tblMovieWatchSN",
			"fields" => array("sn_watch_code", "sn_type", "sn_status", "sn_watch_count", "sn_create_time", "sn_update_time"),
			"records" => $records,
		);

		$this->create($sql_params);

		return $this->getLastInsertID();
	}

	public function generateNewSN($sn_type) {
		$sn_list = array();

		do {
			$sn_watch_code = $this->_SNTypeList[$sn_type].$this->generateSNCode();

			if (!in_array($sn_watch_code, $sn_list) && is_null($this->getSNInfo($sn_watch_code))) {
				break;
			}

			array_push($sn_list, $sn_watch_code);
		} while(true);

		return $sn_watch_code;
	}

	public function isAllowedSNType($sn_type) {
		return in_array($sn_type, array_keys($this->_SNTypeList));
	}

	private function generateSNCode() {
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