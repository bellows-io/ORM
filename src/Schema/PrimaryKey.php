<?php

namespace Orm\Schema;

class PrimaryKey {

	protected $columnNames;

	public function __construct(array $columnNames) {
		$this->columnNames = $columnNames;
	}

	public function getColumnNames() {
		return $this->columnNames;
	}

	public function hasColumn($columnName) {
		return in_array($columnName, $this->columnNames);
	}

}