<?php

namespace Orm\Data;

use \PDO;

abstract class AbstractMapper {

	private $connection;

	public function __construct(PDO $connection) {
		$this->connection = $connection;
	}

	private function prepare($sql, array $data = array()) {

		$statement = $this->connection->prepare($sql);
		$this->lastQuery = $sql;
		if ($data) {
			$i = 1;
			foreach($data as $key => $value) {
				$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$statement->bindParam($i, $value, $type);
			}
		}
		return $statement;
	}

	protected function readRow($table, array $conds) {
		$sql = "SELECT * FROM ${table} WHERE ";
		$wheres = [];
		$params = [];
		foreach ($conds as $key => $value) {
			$wheres []= sprintf('`%s` = ?', $key);
			$params[] = $value;
		}
		$statement = $this->prepare($sql . implode(" AND ", $wheres).' LIMIT 1', $params);
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		$statement->execute();

		foreach ($statement as $row) {
			return $row;
		}
		return null;
	}

	protected function updateRow($table, array $fields, array $conds) {

	}

}