<?php
require_once("common.inc.php");
require_once("ApiControl.class.php");

class RequestGet {
	public function index() {
		if (isset($_GET["account"])) {
			try {
				$trade = new Trade();
				$result = $trade->getTrade($_GET["account"]);

				$trade_info = array();
				if (count($result) > 0) {
					$trade_info = array_shift($result);
				}

				echo json_encode(array("status" => "success", "trade" => $trade_info));
			} catch (PDOException $e) {
				echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			}
		}
	}
}

$api = new ApiControl();
$api->run();
?>