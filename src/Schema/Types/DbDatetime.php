<?php

namespace Orm\Schema\Types;

class DbDateTime extends \Orm\Schema\Types\AbstractDbType {

	public function parse($value) {
		return strtotime($value);
	}

}