<?php

namespace Orm\Schema\Types;

class DbEnum extends \Orm\Schema\Types\AbstractDbType {

	protected $values;

	public function __construct($nullable, array $values) {
		parent::__construct($nullable);
		$this->values = $values;
	}

	public function getValues() {
		return $this->values;
	}

}