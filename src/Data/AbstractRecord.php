<?php

namespace Orm\Data;

abstract class AbstractRecord {

	private $recordValue = [];
	private $dirtyFields = [];

	protected $modelMapper;
	protected $foreign = [];

	public function __construct(array $recordValue, $modelMapper) {
		$this->modelMapper = $modelMapper;
		$this->recordValue = $recordValue;
	}


	protected function set($name, $value) {
		self::$validators[$name]->validate($value);
		$value = self::$validators[$name]->parse($value);

		if ($value != $this->recordValue[$name]) {
			$this->dirtyFields[$name] = $name;
			$this->recordValue[$name] = $value;
		}
	}

	protected function hasForeign($name) {
		return ! empty($this->foreign[$name]);
	}

	protected function getForeign($name) {
		return $this->foreign[$name];
	}

	protected function setForeign($name, AbstractRecord $value) {
		$this->foreign[$name] = $value;
	}

	protected function get($name) {
		return $this->recordValue[$name];
	}

	public function getChangedValues() {
		$out = [];
		foreach ($this->dirtyFields as $field) {
			$out[$field] = $this->recordValue[$field];
		}
		return $out;
	}

	private static $validators = null;
	private static function initRecordValidators() {
		if (is_null(self::$validators)) {
			self::$validators = self::buildRecordValidators();
		}
	}
}