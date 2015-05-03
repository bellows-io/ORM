<?php

namespace Orm\Data;

class QueryBuilder {

	const RETURN_NONE = 0;
	const RETURN_SINGLE = 1;
	const RETURN_ARRAY = 2;

	private $tableName;
	private $mapper;

	private $conds = array();
	private $params = array();
	private $order = array();
	private $joins = array();

	private $callback;

	public function __construct($tableName, AbstractMapper $mapper, callable $callback) {
		$this->tableName = $tableName;
		$this->mapper    = $mapper;
		$this->callback  = $callback;
	}

	public function where(array $map) {
		foreach ($map as $key => $value) {
			$this->conds[] = "$key = ?";
			$this->params[] = $value;
		}
		return $this;
	}

	public function join($table, array $joins) {
		$this->joins[] = [$table, $joins];
		return $this;
	}

	public function whereNot(array $map) {
		foreach ($map as $key => $value) {
			$this->conds[] = "$key != ?";
			$this->params[] = $value;
		}
		return $this;
	}

	public function orderAsc($columnName) {
		$this->order[] = "$columnName ASC";
		return $this;
	}

	public function orderDesc($columnName) {
		$this->order[] = "$columnName DESC";
		return $this;
	}

	protected function buildQueryBody($setSql = '', $limit = '') {
		$condSql = "";
		$joinSql = "";
		$orderSql = "";
		if ($this->conds) {
			$condSql = "WHERE ".implode(" AND ", $this->conds);
		}
		if ($this->joins) {
			$joinStatements = [];
			foreach ($this->joins as $join) {
				$statement = [];
				list($joinTable, $ons) = $join;
				foreach ($ons as $fromCol => $toCol) {
					$statement[] = $fromCol.' = '.$toCol;
				}
				$joinStatements[] = ' INNER JOIN '.$joinTable.' ON '.implode(' AND ', $statement);
			}
			$joinSql = implode("\n", $joinStatements);
		}
		if ($this->order) {
			$orderSql = "ORDER BY ".implode(", ", $this->order);
		}
		return "{$joinSql} {$setSql} {$condSql} {$orderSql} {$limit}";
	}

	protected function buildSelectQuery($limit = '') {
		return "SELECT {$this->tableName}.* FROM {$this->tableName}".$this->buildQueryBody('', $limit);
	}

	public function readAll() {
		$sql = $this->buildSelectQuery();
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_ARRAY);
	}

	public function readOne() {
		$sql = $this->buildSelectQuery("LIMIT 1");
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_SINGLE);
	}

	public function readSlice($start, $length) {
		$sql = $this->buildSelectQuery("LIMIT {$start}, {$length}");
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_ARRAY);
	}

	public function update(array $map) {
		$setSql = "SET %s";
		$setValues = [];
		foreach ($map as $key => $value) {
			$column = $this->mapper->getColumnType($key);
			array_unshift($this->params, $column->stringify($value));
			$setValues[] = "$key = ?";
		}

		$sql = "UPDATE {$this->tableName} ".$this->buildQueryBody(sprintf($setSql, implode(", ", $setValues)));
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_NONE);
	}

	public function delete() {
		$sql = "DELETE {$this->tableName} FROM {$this->tableName} ".$this->buildQueryBody();
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_NONE);
	}

}