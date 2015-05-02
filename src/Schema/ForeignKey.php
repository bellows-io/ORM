<?php

namespace Orm\Schema;

class ForeignKey {

	protected $localColumns;
	protected $foreignTable;
	protected $name;
	protected $foreignColumns;

	public function __construct($name, array $localColumns, $foreignTable, array $foreignColumns) {
		$this->name           = $name;
		$this->localColumns   = $localColumns;
		$this->foreignTable   = $foreignTable;
		$this->foreignColumns = $foreignColumns;
	}

	public function getName() {
		return $this->name;
	}

	public function getLocalColumns() {
		return $this->localColumns;
	}

	public function getForeignTable() {
		return $this->foreignTable;
	}

	public function getForeignColumns() {
		return $this->foreignColumns;
	}

}