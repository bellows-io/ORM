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

	private $callback;

	public function __construct($tableName, AbstractMapper $mapper, callable $callback) {
		$this->tableName = $tableName;
		$this->mapper    = $mapper;
		$this->callback  = $callback;
	}

	public function where(array $map) {
		foreach ($map as $key => $value) {
			$this->conds[] = "`$key` = ?";
			$this->params[] = $value;
		}
		return $this;
	}

	public function whereNot(array $map) {
		foreach ($map as $key => $value) {
			$this->conds[] = "`$key` != ?";
			$this->params[] = $value;
		}
		return $this;
	}

	public function orderAsc($columnName) {
		$order[] = "$columnName ASC";
		return $this;
	}

	public function orderDesc($columnName) {
		$order[] = "$columnName DESC";
		return $this;
	}

	public function readAll() {
		$condsql = "";
		if ($this->conds) {
			$condsql = "WHERE ".implode(" AND ", $this->conds);
		}
		$sql = "SELECT * FROM {$this->tableName} {$condsql}";
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_ARRAY);
	}

	public function readOne() {
		$condsql = "";
		if ($this->conds) {
			$condsql = "WHERE ".implode(" AND ", $this->conds);
		}
		$sql = "SELECT * FROM {$this->tableName} {$condsql} LIMIT 1";
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_SINGLE);
	}

	public function readSlice($start, $length) {
		$condsql = "";
		if ($this->conds) {
			$condsql = "WHERE ".implode(" AND ", $this->conds);
		}
		$sql = "SELECT * FROM {$this->tableName} {$condsql} LIMIT {$start}, {$length}";
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_ARRAY);
	}

	public function update($map) {
		$updateMap = TableColumnMap::fromTableColumnMap($map);
	}

	public function delete() {
		$condsql = "";
		if ($this->conds) {
			$condsql = "WHERE ".implode(" AND ", $this->conds);
		}
		$sql = "DELETE FROM {$this->tableName} {$condsql}";
		return call_user_func($this->callback, $sql, $this->params, self::RETURN_NONE);
	}

}