<?php

namespace Orm\Schema\Types;

class DbEnum extends \Orm\Schema\Types\AbstractDbType {

	protected $values;

	public function __construct(array $values) {
		$this->values = $values;
	}

	public function getValues() {
		return $this->values;
	}

}