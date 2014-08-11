<?php
require_once("common.inc.php");
require_once("ApiControl.class.php");

class RequestGet {
	public function index() {
		if (isset($_GET["sn"])) {
			$mwsn = new MovieWatchSN();
			echo json_encode($mwsn->getSNInfo($_GET["sn"]));
		}
	}
}

class RequestPost {
	public function create() {
		if (isset($_POST["user"], $_POST["type"])) {
			try {
				$mwsn = new MovieWatchSN();
				$sn_watch_code = $mwsn->generateNewSN($_POST["type"]);
				$mwsn->createNewSN($sn_watch_code, $_POST["type"]);
				$mwsn->activateSN($sn_watch_code, $_POST["user"]);
				echo json_encode($mwsn->getUserSNInfo($_POST["user"]));
			} catch (PDOException $e) {
				echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			}
		}
	}
}

$api = new ApiControl();
$api->run();
?>