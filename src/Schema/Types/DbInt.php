<?php

namespace Orm\Schema\Types;

class DbInt extends \Orm\Schema\Types\AbstractDbType {

	protected $length;

	public function __construct($length) {
		$this->length = $length;
	}

	public function getLength() {
		return $this->length;
	}

}