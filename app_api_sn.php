<?php
require_once("common.inc.php");
require_once("ApiControl.class.php");

class RequestGet {
	public function index() {
		try {
			$mwsn = new MovieWatchSN();
			if (isset($_GET["account"])) {
				echo json_encode(array("status" => "success", "sn" => $mwsn->getUserSNInfo($_GET["account"])));
			} else if (isset($_GET["sn"])) {
				echo json_encode(array("status" => "success", "sn" => $mwsn->getSNInfo($_GET["sn"])));
			}
		} catch (PDOException $e) {
			echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
		}
	}

	public function report() {
		try {
			$mwsn = new MovieWatchSN();
			echo json_encode(array("status" => "success", "report" => $mwsn->getActivatedSNReport()));
		} catch (PDOException $e) {
			echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
		}
	}
}

class RequestPost {
	public function create() {
		try {
			$mwsn = new MovieWatchSN();
			$mwsn->dbHandler->beginTransaction();

			$sn_type = 2;
			if (isset($_POST["type"]) && $mwsn->isAllowedSNType($_POST["type"])) {
				$sn_type = $_POST["type"];
			}
			
			$sn_watch_code = $mwsn->generateNewSN($sn_type);
			$sn_id = $mwsn->createNewSN($sn_watch_code, $sn_type);

			$trade = new Trade();
			if (isset($_POST["account"], $_POST["provider"]) && $sn_type == 2 && $trade->isMobileProvider($_POST["provider"])) {
				$mwsn->activateSN($sn_watch_code, $_POST["account"]);

				$now = date("Y-m-d H:i:s");
				$order = new Order();
				$order_id = $order->createOrder($_POST["account"]);
				$order->makeOrderPaid($order_id, $sn_id);

				$params = array(
					"order_id" => $order_id,
					"trade_provider" => $_POST["provider"],
					"trade_status" => 1,
					"trade_amount" => 180,
					"payment_time" => $now,
				);
				$trade->createTrade($params);
			}

			$mwsn->dbHandler->commit();
			echo json_encode(array("status" => "success", "sn" => $mwsn->getSNInfo($sn_watch_code)));
		} catch (PDOException $e) {
			$mwsn->dbHandler->rollBack();
			echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
		}
	}

	public function activate() {
		if (isset($_POST["account"], $_POST["sn"])) {
			try {
				$mwsn = new MovieWatchSN();
				if ($mwsn->isSNNotActivated($_POST["sn"])) {
					$new_sn_info = $mwsn->getSNInfo($_POST["sn"]);
					$user_sn_info = $mwsn->getUserSNInfo($_POST["account"]);
					
					if (is_null($user_sn_info)) {
						$mwsn->activateSN($_POST["sn"], $_POST["account"]);
						echo json_encode(array("status" => "success", "sn" => $mwsn->getUserSNInfo($_POST["account"])));
					} else {
						if ($user_sn_info["sn_type"] == 4) {
							echo json_encode(array("status" => "fail", "error_msg" => "你已經無敵了"));
						} else if (in_array($user_sn_info["sn_type"], array(1, 2))) {
							if ($new_sn_info["sn_type"] == 4) {
								$mwsn->activateSN($_POST["sn"], $_POST["account"]);
								echo json_encode(array("status" => "success", "sn" => $mwsn->getUserSNInfo($_POST["account"])));
							} else {
								echo json_encode(array("status" => "fail", "error_msg" => "你已經付費了"));
							}
						} else if ($user_sn_info["sn_type"] == 3) {
							if ($new_sn_info["sn_type"] != 3) {
								$mwsn->activateSN($_POST["sn"], $_POST["account"]);
								echo json_encode(array("status" => "success", "sn" => $mwsn->getUserSNInfo($_POST["account"])));
							} else {
								echo json_encode(array("status" => "fail", "error_msg" => "你已經使用過免費序號"));
							}
						}
					}
				} else {
					echo json_encode(array("status" => "fail", "error_msg" => "無效序號"));
				}
			} catch (PDOException $e) {
				echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			}
		}
	}

	public function consumeCount() {
		if (isset($_POST["account"], $_POST["sn"])) {
			try {
				$mwsn = new MovieWatchSN();
				$sn_info = $mwsn->getUserSNInfo($_POST["account"]);
				if (!is_null($sn_info) && $sn_info["sn_watch_code"] == $_POST["sn"] && $sn_info["sn_type"] == 3) {
					$mwsn->consumeWatchCount($_POST["sn"]);
					echo json_encode(array("status" => "success", "sn" => $mwsn->getUserSNInfo($_POST["account"])));
				}
			} catch (PDOException $e) {
				echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			}
		}
	}
}

$api = new ApiControl();
$api->run();
?>