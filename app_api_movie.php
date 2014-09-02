<?php
require_once("common.inc.php");
require_once("ApiControl.class.php");

class RequestGet {
}

class RequestPost {
	public function increaseCount() {
		if (isset($_POST["movie_no"])) {
			try {
				$movie = new Movie();
				if ($movie->isMovieExisted($_POST["movie_no"])) {
					$movie->accumulateWatchCount($_POST["movie_no"]);
				} else {
					$movie->createMovie($_POST["movie_no"]);
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