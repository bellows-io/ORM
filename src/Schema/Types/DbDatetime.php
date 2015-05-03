<?php

namespace Orm\Schema\Types;

class DbDateTime extends \Orm\Schema\Types\AbstractDbType {

	public function parse($value) {
		return new \DateTime($value);
	}

	public function stringify($value) {
		return $value->format("Y-m-d H:i:s");
	}


}