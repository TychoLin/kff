<?php
class ApiControl {
	public $method = "";

	private $_apiKey = "9dcba708e91abe2f1ef6b087a2c57fac";

	public function __construct() {
		header("Content-Type: application/json");

		$this->method = $_SERVER["REQUEST_METHOD"];
	}

	public function run() {
		ob_start();

		if ($this->method == "GET") {
			$input = $_GET;
		} else if ($this->method == "POST") {
			$input = $_POST;
		} else {
			return;
		}

		if (isset($input["apikey"])) {
			if ($input["apikey"] == $this->_apiKey) {
				$class_name = "Request".ucfirst(strtolower($this->method));
				if (class_exists($class_name)) {
					$obj = new $class_name();

					if (isset($input["method_name"])) {
						$method_name = $input["method_name"];
					} else {
						$method_name = "index";
					}

					if (method_exists($obj, $method_name)) {
						$obj->$method_name();
					}
				}
			} else {
				echo json_encode(array("status" => "fail", "error_msg" => "授權碼錯誤"));
			}
		} else {
			echo json_encode(array("status" => "fail", "error_msg" => "無授權"));
		}

		ob_end_flush();
	}
}
?>