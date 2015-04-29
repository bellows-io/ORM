<?php

namespace Orm\Schema;

class Column {

	protected $name;
	protected $type;
	protected $isNullable;

	public function __construct($name, Type $type, $isNullable = false) {
		$this->name = $name;
		$this->type = $type;
		$this->isNullable = $isNullable;
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function getIsNullable() {
		return $this->isNullable;
	}

}