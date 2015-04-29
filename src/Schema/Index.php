<?php

namespace Orm\Schema;

class Index {

	protected $name;
	protected $columns;
	protected $isUnique;

	public function __construct($name, array $columns, $isUnique = false) {
		$this->name = $name;
		$this->columns = $columns;
		$this->isUnique = $isUnique;
	}

	public function getName() {
		return $this->name;
	}

	public function getColumns() {
		return $this->columns;
	}

	public function getIsUnique() {
		return $this->isUnique;
	}

}