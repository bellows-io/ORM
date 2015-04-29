<?php

namespace Orm\Schema;

class Table {

	protected $name;
	protected $columns;
	protected $primaryKey;
	protected $indices;

	public function __construct($name, array $columns, array $primaryKey, array $indices = array()) {
		$this->name        = $name;
		$this->columns     = [];
		$this->primaryKey = $primaryKey;
		$this->indices     = $indices;

		foreach ($columns as $column) {
			$this->columns[$column->getName()] = $column;
		}
	}

	public function getName() {
		return $this->name;
	}

	public function getAllColumns() {
		return $this->columns;
	}

	public function hasColumn($columnName) {
		return array_key_exists($this->columns, $columnName);
	}

	public function getColumn($columnName) {
		return $this->columns[$columnName];
	}

	public function getIndices() {
		return $this->indices;
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}

}