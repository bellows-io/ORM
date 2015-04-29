<?php

namespace Orm\Schema\Types;

abstract class AbstractDbType {

	protected $isNullable;

	public function __construct($isNullable) {
		$this->isNullable = $isNullable;
	}

	public function getIsNullable() {
		return $this->isNullable;
	}

	public function validate($value) {
		if (! $this->isNullable && is_null($value)) {
			throw new \Exception("Value may not be null");
		}
	}
}