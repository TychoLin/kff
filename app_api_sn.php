<?php
require_once("common.inc.php");
require_once("ApiControl.class.php");

class RequestGet {
	public function index() {
		if (isset($_GET["sn"])) {
			try {
				$mwsn = new MovieWatchSN();
				echo json_encode(array("status" => "success", "sn" => $mwsn->getSNInfo($_GET["sn"])));
			} catch (PDOException $e) {
				echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			}
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
			if (isset($_POST["account"], $_POST["provider"]) && $sn_type == 2 && $trade->isAllowedProvider($_POST["provider"])) {
				$mwsn->activateSN($sn_watch_code, $_POST["account"]);

				$now = date("Y-m-d H:i:s");
				if ($trade->isMobileProvider($_POST["provider"])) {
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
				} else {
					// allpay flow
				}
			}

			$mwsn->dbHandler->commit();
			echo json_encode(array("status" => "success", "sn" => $sn_watch_code));
		} catch (PDOException $e) {
			echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			$mwsn->dbHandler->rollBack();
		}
	}

	public function activate() {
		if (isset($_POST["account"], $_POST["sn"])) {
			try {
				$mwsn = new MovieWatchSN();
				if ($mwsn->isSNActivated($_POST["sn"])) {
					echo json_encode(array("status" => "fail", "error_msg" => "無效序號"));
				} else {
					$mwsn->activateSN($_POST["sn"], $_POST["account"]);
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