<?php

namespace Orm\Data;

abstract class AbstractRecord {

	protected $recordValue = [];
	protected $dirtyFields = [];

	protected $mapper;
	protected $foreign = [];

	public function __construct(array $recordValue, AbstractMapper $mapper) {

		foreach ($recordValue as $col => $value) {
			$property = '`'.static::TABLE."`.`$col`";
			$this->recordValue[$property] = $value;
		}

		$this->mapper = $mapper;
		$this->dirtyFields = [];
	}

	protected function set($name, $value) {
		$this->mapper->getColumnType($name)->validate($value);
		if ($value != $this->recordValue[$name]) {
			$this->dirtyFields[$name] = $value;
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

	public function save() {
		if ($this->dirtyFields) {
			$this->mapper
				->from(static::TABLE)
				->where($this->identify())
				->update($this->getChangedValues());

			$this->dirtyFields = [];
		}
	}

	public function getChangedValues() {
		return $this->dirtyFields;
	}

	protected static function validator($name) {
		return $this->mapper->getColumnType($name);
	}
}