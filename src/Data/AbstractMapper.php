<?php

namespace Orm\Data;

use \PDO;

abstract class AbstractMapper {

	private $connection;

	public function __construct(PDO $connection) {
		$this->connection = $connection;
	}

	protected abstract function buildRecordObject($tableName, array $record);
	protected abstract function readFromAutoIncrementId($tableName, $id);

	private function prepare($sql, array $data = array()) {

		$statement = $this->connection->prepare($sql);
		$this->lastQuery = $sql;
		if ($data) {
			$i = 1;
			foreach($data as $key => $value) {
				$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$statement->bindParam($i, $value, $type);
				$i++;
			}
		}
		return $statement;
	}

	public function create($tableName, array $input) {
		$sql = "INSERT INTO `$tableName` (`%s`) VALUES (%s);";
		$keys = [];
		$values = [];


		foreach ($input as $key => $value) {
			$keys[] = '`'.$key.'`';
		}

		$sql = sprintf($sql, implode('`, `', array_keys($input)), implode(', ', array_fill(0, count($input), '?')));

		$statement = $this->prepare($sql, array_values($input));

		$statement->setFetchMode(PDO::FETCH_BOTH);
		$statement->execute();

		if ($insertId = $this->connection->lastInsertId()) {

			return $this->readFromAutoIncrementId($tableName, $insertId);
		}
		return null;
	}

	public function from($tableName) {
		$builder = new QueryBuilder($tableName, $this, function($sql, $params, $return) use ($tableName) {
			$statement = $this->prepare($sql, $params);
			$statement->setFetchMode(PDO::FETCH_BOTH);
			$statement->execute();

			if ($return == QueryBuilder::RETURN_NONE) {
				return;
			}
			$out = [];
			foreach ($statement as $row) {
				$record = $this->buildRecordObject($tableName, $row);
				if ($return == QueryBuilder::RETURN_SINGLE) {
					return $record;
				}
				$out[] = $record;
			}
			return $out;
		});
		return $builder;
	}

}