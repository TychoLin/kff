<?php
require_once("db.inc.php");

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
			"fields" => array("a.*", "b.order_no"),
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
	private $_SNTypeList = array(1 => "A", 2 => "B", 3 => "C", 4 => "D");

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
			"record" => array("sn_watch_count = IF(sn_watch_count <= 0, sn_watch_count, sn_watch_count - 1)" => null, "sn_update_time" => $now),
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
			"where_cond" => array("sn_watch_code = ?" => $sn_watch_code),
		);

		$result = $this->read($sql_params);

		if (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	public function getActivatedSNReport() {
		$sql_params = array(
			"fields" => array("member_account", "sn_watch_code", "sn_activate_time"),
			"table_reference" => "tblMovieWatchSN",
			"where_cond" => array("sn_status = ?" => 2),
			"order_by_clause" => "sn_type ASC, sn_activate_time DESC",
		);

		return $this->read($sql_params, PDO::FETCH_NUM);
	}

	public function isSNNotActivated($sn_watch_code) {
		$sn_info = $this->getSNInfo($sn_watch_code);

		if (is_null($sn_info)) {
			return null;
		}

		return ($sn_info["sn_status"] == 1) ? true : false;
	}

	public function isSNFree($sn_watch_code) {
		$sn_info = $this->getSNInfo($sn_watch_code);

		return ($sn_info["sn_type"] == 3) ? true : false;
	}

	public function hasFreeSN($user) {
		$sql_params = array(
			"fields" => array("sn_id"),
			"table_reference" => "tblMovieWatchSN",
			"where_cond" => array("member_account = ?" => $user, "sn_type = ?" => 3),
		);

		$result = $this->read($sql_params);

		return (count($result) > 0) ? true : false;
	}

	public function initSN() {
		$sn_list = array();

		for ($i = 0; $i < 50; $i++) {
			do {
				$sn_watch_code = "A".$this->generateSNCode();
			} while (in_array($sn_watch_code, $sn_list));

			array_push($sn_list, $sn_watch_code);

			$this->createNewSN($sn_watch_code, 1);
		}

		// $sn_list = array();
		
		// for ($i = 0; $i < 200000; $i++) {
		// 	do {
		// 		$sn_watch_code = "C".$this->generateSNCode();
		// 	} while (in_array($sn_watch_code, $sn_list));

		// 	array_push($sn_list, $sn_watch_code);

		// 	$this->createNewSN($sn_watch_code, 3);
		// }
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
		$sn_length = 5;
		$code = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$code_length = strlen($code);

		for ($i = 0; $i < $sn_length; $i++) {
			$sn .= $code[mt_rand() % $code_length];
		}

		return $sn;
	}
}

class Movie extends KFFRecordModel {
	public function __construct() {
		parent::__construct();
	}

	public function getMovieInfo($movie_no) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "tblMovie",
			"where_cond" => array("movie_no = ?" => $movie_no),
		);

		$result = $this->read($sql_params);

		if (count($result) == 1) {
			return $result[0];
		} else {
			return null;
		}
	}

	public function createMovie($movie_no) {
		$now = date("Y-m-d H:i:s");

		$records = array();
		array_push($records, array($movie_no, $now, $now));

		$sql_params = array(
			"table_reference" => "tblMovie",
			"fields" => array("movie_no", "movie_create_time", "movie_update_time"),
			"records" => $records,
		);

		$this->create($sql_params);
	}

	public function accumulateWatchCount($movie_no) {
		$now = date("Y-m-d H:i:s");

		$sql_params = array(
			"table_reference" => "tblMovie",
			"record" => array("movie_watch_count = movie_watch_count + 1" => null, "movie_update_time" => $now),
			"where_cond" => array("movie_no = ?" => $movie_no),
		);

		$this->update($sql_params);
	}

	public function isMovieExisted($movie_no) {
		$movie_info = $this->getMovieInfo($movie_no);

		return is_null($movie_info) ? false : true;
	}
}

class Member extends KFFRecordModel {
	public function __construct() {
		parent::__construct();
	}

	public function getMember($where_cond) {
		$sql_params = array(
			"fields" => array("*"),
			"table_reference" => "member",
			"where_cond" => $where_cond,
		);

		return $this->read($sql_params);
	}

	public function createMember($email, $pw) {
		$records = array();
		$data = array("member_account" => $email, "member_password" => md5($pw));
		array_push($records, array_values($data));

		$sql_params = array(
			"table_reference" => "member",
			"fields" => array_keys($data),
			"records" => $records,
		);

		$this->create($sql_params);

		return $this->getLastInsertID();
	}

	public function updateMember($email, $pw) {
		$sql_params = array(
			"table_reference" => "member",
			"record" => array("member_password" => md5($pw)),
			"where_cond" => array("member_account = ?" => $email),
		);

		$this->update($sql_params);
	}

	public function isMemberExisted($email) {
		$result = $this->getMember(array("member_account = ?" => $email));

		return (count($result) > 0) ? true : false;
	}

	public function login($email, $pw) {
		$where_cond = array(
			"member_account = ?" => $email,
			"member_password = ?" => md5($pw),
		);
		$result = $this->getMember($where_cond);

		if (count($result) == 1) {
			$member_info = $result[0];
			$_SESSION["member_account"] = $member_info["member_account"];
			return true;
		} else {
			return false;
		}
	}

	public function logout() {
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		}

		session_destroy();
		// header("Location: index.php");
		// exit();
	}

	public function isLogined() {
		if (isset($_SESSION) && count($_SESSION) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function validate_email($email) {
		$pattern = "/^[\w]+[\.\w-_]{2,}@([\w-]+\.)+[a-zA-Z]{2,7}$/";

		return (preg_match($pattern, $email) == 1) ? true : false;
	}
}
?>