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

	public function getLinkingTables($tableName) {
		$out = [];
		foreach ($this->tables as $name => $table) {
			$pk = $table->getPrimaryKey();
			$pkColumns = $pk->getColumnNames();
			$fks = $table->getForeignKeys();
			if (count($fks) > 1 && strpos($name, '_to_') !== false) {
				$outKeys = [];
				$inKey = null;
				foreach ($fks as $fk) {
					if ($fk->getForeignTable() == $tableName) {
						$inKey = $fk;
					} else {
						$outKeys[$fk->getForeignTable()] = $fk;
					}
				}
				if ($inKey) {
					$out[$name] = [
						'inKey' => $inKey,
						'outKeys' => $outKeys
					];
				}
			}
		}
		return $out;
	}

	public function getTable($tableName) {
		return $this->tables[$tableName];
	}

	public function getTables() {
		return $this->tables;
	}
}