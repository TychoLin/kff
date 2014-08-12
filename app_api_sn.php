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
		if (isset($_POST["account"], $_POST["type"])) {
			try {
				$mwsn = new MovieWatchSN();
				$sn_watch_code = $mwsn->generateNewSN($_POST["type"]);
				$mwsn->createNewSN($sn_watch_code, $_POST["type"]);
				$mwsn->activateSN($sn_watch_code, $_POST["account"]);
				echo json_encode($mwsn->getUserSNInfo($_POST["account"]));
			} catch (PDOException $e) {
				echo json_encode(array("status" => "fail", "error_msg" => $e->getMessage()));
			}
		}
	}

	public function activate() {
		if (isset($_POST["account"], $_POST["sn"])) {
			try {
				$mwsn = new MovieWatchSN();
				if ($mwsn->isSNActivated($sn)) {
					echo json_encode(array("status" => "fail", "error_msg" => "無效序號"));
				} else {
					$mwsn->activateSN($sn, $_POST["account"]);
					echo json_encode($mwsn->getUserSNInfo($_POST["account"]));
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