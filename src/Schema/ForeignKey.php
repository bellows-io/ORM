<?php

namespace Orm\Schema;

class ForeignKey {

	protected $localColumn;
	protected $foreignTable;
	protected $foreignColumn;

	public function __construct($localColumn, $foreignTable, $foreignColumn) {
		$this->localColumn   = $localColumn;
		$this->foreignTable  = $foreignTable;
		$this->foreignColumn = $foreignColumn;
	}

	public function getLocalColumn() {
		return $this->localColumn;
	}

	public function getForeginTable() {
		return $this->foreignTable;
	}

	public function getForeignColumn() {
		return $this->foreignColumn;
	}

}