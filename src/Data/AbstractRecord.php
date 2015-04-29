<?php

namespace Orm\Data;

abstract class AbstractRecord {

	private $isDirty = false;

	public function isDirty() {
		return $this->isDirty;
	}

	protected function markDirty() {
		$this->isDirty = true;
	}

	public function resetDirty() {
		$this->isDirty = false;
	}


}