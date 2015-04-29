<?php

namespace Orm\Schema;

class Column {

	protected $name;
	protected $type;
	protected $isAutoIncrement;

	public function __construct($name, Types\AbstractDbType $type, $isAutoIncrement = false) {
		$this->name = $name;
		$this->type = $type;
		$this->isAutoIncrement = $isAutoIncrement;
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function isAutoIncrement() {
		return $this->isAutoIncrement;
	}

}