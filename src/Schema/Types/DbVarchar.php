<?php

namespace Orm\Schema\Types;

class DbVarchar extends \Orm\Schema\Types\AbstractDbType {

	protected $length;

	public function __construct($isNullable, $length) {
		parent::__construct($isNullable);
		$this->length = $length;
	}

	public function getLength() {
		return $this->length;
	}

	public function validate($value) {
		parent::validate($value);
	}

	public static function __set_state(array $data) {
		return new self($data['isNullable'], $data['length']);
	}
}