<?php

namespace Orm\Data;

use \PDO;

abstract class AbstractMapper {

	private $connection;
	protected $lastQuery;
	protected $lastQueryParams;

	public function __construct(PDO $connection) {
		$this->connection = $connection;
		$this->initializeColumnTypes();
	}

	protected abstract function buildRecordObject($tableName, array $record);
	protected abstract function readFromAutoIncrementId($tableName, $id);

	private $columnTypes;
	protected abstract function initializeColumnTypes();

	private function prepare($sql, array $data = array()) {

		$statement = $this->connection->prepare($sql);
		$this->lastQuery = $sql;
		$this->lastQueryParams = $data;
		if ($data) {
			$i = 1;
			foreach($data as $key => &$value) {
				/*if ($value instanceof \DateTime) {
					$value = $value->format('Y-m-d H:i:s');
				}*/
				$type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$statement->bindParam($i, $value, $type);
				$i++;
			}
		}

		return $statement;
	}

	public function create($tableName, array $input) {
		$sql = "INSERT INTO `$tableName` (%s) VALUES (%s);";
		$keys = [];
		$values = array_values($input);
		//$keys = array_keys($input);

		foreach ($input as $key => $value) {
			list($table, $key) = explode('.', $key);
			$keys[] = $key;
		}

		$sql = sprintf($sql, implode(', ', $keys), implode(', ', array_fill(0, count($input), '?')));

		$statement = $this->prepare($sql, $values);

		$statement->setFetchMode(PDO::FETCH_BOTH);
		$statement->execute();

		if ($statement->errorCode() != '00000') {
			list($a, $b, $message) = $statement->errorInfo();
			throw new \Exception($message);
		}

		if ($insertId = $this->connection->lastInsertId()) {
			$out = $this->readFromAutoIncrementId($tableName, $insertId);
			return $out;
		}
		return null;
	}

	public function setColumnType($columnName, \Orm\Schema\Types\AbstractDbType $type) {
		$this->columnTypes[$columnName] = $type;
	}

	public function getColumnType($columnName) {
		return $this->columnTypes[$columnName];
	}

	public function from($tableName) {
		$builder = new QueryBuilder($tableName, $this, function($sql, $params, $return) use ($tableName) {
			$statement = $this->prepare($sql, $params);
			$statement->setFetchMode(PDO::FETCH_ASSOC);
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