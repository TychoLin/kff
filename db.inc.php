<?php
class DB {
	private static $_db;
	private static $_dbStores = array(
		"dcviewSH" => array(
			"dsn" => "mysql:host=localhost;dbname=dcviewSH",
			"username" => "root",
			"password" => "9999",
			"options" => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			),
		),
		"dcviewEdm" => array(
			"dsn" => "mysql:host=localhost;dbname=dcviewEdm",
			"username" => "root",
			"password" => "9999",
			"options" => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			),
		),
		"kff" => array(
			"dsn" => "mysql:host=localhost;dbname=kff",
			"username" => "root",
			"password" => "9999",
			"options" => array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			),
		),
	);

	private function __construct($database) {
		try {
			$reflectionObj = new ReflectionClass("PDO");
			self::$_db[$database] = $reflectionObj->newInstanceArgs(self::$_dbStores[$database]);
			self::$_db[$database]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo "Connection failed: ".$e->getMessage();
		}
	}

	public static function getPDO($database) {
		if (!isset(self::$_db[$database])) {
			new self($database);
		}
		return self::$_db[$database];
	}
}

class RecordModel {
	public $dbHandler;

	public function __construct($database) {
		$this->dbHandler = DB::getPDO($database);
	}

	public function create($sql_params) {
		$table_reference = $sql_params["table_reference"];
		$fields = implode(",", $sql_params["fields"]);

		$sql = array();
		array_push($sql, "INSERT INTO", $table_reference, "($fields)", "VALUES");

		$params = array();
		$multiple = array();
		$place_holders = implode(",", array_fill(0, count($sql_params["fields"]), "?"));
		foreach ($sql_params["records"] as $record) {
			array_push($multiple, "($place_holders)");
			$params = array_merge($params, $record);
		}

		array_push($sql, implode(",", $multiple));

		if (isset($sql_params["duplicate_clause"])) {
			$duplicate_clause = $sql_params["duplicate_clause"];
			if (is_array($duplicate_clause) && count($duplicate_clause) > 0) {
				array_push($sql, "ON DUPLICATE KEY UPDATE", implode(",", array_keys($duplicate_clause)));
				$params = array_merge($params, array_values($duplicate_clause));
			}
		}

		return $this->execSQL(implode(" ", $sql), $params)->rowCount();
	}

	public function read($sql_params, $fetch_style = PDO::FETCH_ASSOC) {
		$params = array();

		$fields = implode(",", $sql_params["fields"]);

		if (is_array($sql_params["table_reference"])) {
			$table_reference = implode(" ", $sql_params["table_reference"]);
		} else {
			$table_reference = $sql_params["table_reference"];
		}

		$where_clause = $this->getWhereClause($sql_params, $params);

		$sql = array();
		array_push($sql, "SELECT", $fields, "FROM", $table_reference, "WHERE", $where_clause);

		if (isset($sql_params["group_by_clause"])) {
			array_push($sql, "GROUP BY", $sql_params["group_by_clause"]);
		}

		if (isset($sql_params["order_by_clause"])) {
			array_push($sql, "ORDER BY", $sql_params["order_by_clause"]);
		}

		if (isset($sql_params["limit"])) {
			array_push($sql, "LIMIT", $sql_params["limit"]);
		}

		if (isset($sql_params["offset"])) {
			array_push($sql, "OFFSET", $sql_params["offset"]);
		}

		$ps = $this->execSQL(implode(" ", $sql), $params);
		return $ps->fetchAll($fetch_style);
	}

	public function update($sql_params) {
		$table_reference = $sql_params["table_reference"];

		$set_clause = $params = array();
		foreach ($sql_params["record"] as $key => $value) {
			if (is_null($value)) {
				array_push($set_clause, $key);
			} else {
				array_push($set_clause, "$key = ?");
				array_push($params, $value);
			}
		}
		$set_clause = implode(',', $set_clause);

		$where_clause = $this->getWhereClause($sql_params, $params);

		$sql = array();
		array_push($sql, "UPDATE", $table_reference, "SET", $set_clause, "WHERE", $where_clause);

		return $this->execSQL(implode(" ", $sql), $params)->rowCount();
	}

	public function delete($sql_params) {
		$table_names = "";
		if (isset($sql_params["table_names"])) {
			$table_names = $sql_params["table_names"];
			if (is_array($table_names) && count($table_names) > 0) {
				$table_names = implode(",", $table_names);
			}
		}

		if (is_array($sql_params["table_reference"])) {
			$table_reference = implode(" ", $sql_params["table_reference"]);
		} else {
			$table_reference = $sql_params["table_reference"];
		}

		$params = array();
		$where_clause = $this->getWhereClause($sql_params, $params);

		$sql = array();
		array_push($sql, "DELETE", $table_names, "FROM", $table_reference, "WHERE", $where_clause);

		return $this->execSQL(implode(" ", $sql), $params)->rowCount();
	}

	public function execSQL($sql, $params) {
		$ps = $this->dbHandler->prepare($sql);
		$ps->execute($params);
		return $ps;
	}

	public function getLastInsertID() {
		return $this->dbHandler->lastInsertId();
	}

	private function getWhereClause($sql_params, &$params) {
		$where_clause = "1 = 1";
		if (isset($sql_params["where_cond"])) {
			$where_cond = $sql_params["where_cond"];
			if (is_array($where_cond) && count($where_cond) > 0) {
				$where_clause = implode(" AND ", array_keys($where_cond));

				foreach (array_values($where_cond) as $value) {
					if (is_array($value)) {
						$params = array_merge($params, $value);
					} else if (!is_null($value)) {
						array_push($params, $value);
					}
				}
			}
		}

		return $where_clause;
	}
}
?>