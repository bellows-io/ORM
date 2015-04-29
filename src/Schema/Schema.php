<?php

namespace Orm\Schema;

class Schema {

	protected $tables = array();

	public function __construct(array $tables) {
		foreach ($tables as $table) {
			$this->tables[$table->getName()] = $table;
		}
	}

	public function hasTable($tableName) {
		return array_key_exists($tableName, $this->tables);
	}

	public function getTable($tableName) {
		return $this->tables[$tableName];
	}

	public function getTables() {
		return $this->tables;
	}
}