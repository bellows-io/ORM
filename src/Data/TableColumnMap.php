<?php

namespace Orm\Data;

class TableColumnMap {

	protected $tableName;
	protected $map = array();

	public function __construct($tableName, array $map) {
		$this->tableName = $tableName;
		$this->map = $map;
	}

	public static function fromTableColumnMap(array $tableColumnMap) {
		$tableName = 'none';
		$map = [];

		foreach ($tableColumnMap as $key => $value) {
			list($tableName, $columnName) = explode(".", $key);
			$map[$columnName] = $key;
		}

		return new self::($tableName, $map);
	}

	public function getMap() {
		return $this->map;
	}

	public function getTableName() {
		return $this->tableName;
	}


}