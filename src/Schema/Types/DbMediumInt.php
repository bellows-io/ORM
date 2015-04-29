<?php

namespace Orm\Schema\Types;

class DbMediumInt extends \Orm\Schema\Types\AbstractDbType {

	protected $length;

	public function __construct($nullable, $length) {
		parent::__construct($nullable);
		$this->length = $length;
	}

	public function getLength() {
		return $this->length;
	}

	public function validate($value) {
		parent::validate($value);
	}

	public function parse($value) {
		return (int)$value;
	}

}