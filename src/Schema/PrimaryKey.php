<?php

namespace Orm\Schema;

class PrimaryKey {

	protected $columns;

	public function(array $columns) {
		$this->columns = $columns;
	}

	public function getColumns() {
		return $this->columns;
	}

}