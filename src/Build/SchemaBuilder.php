<?php

namespace Orm\Build;

use \Orm\Schema\Schema;

class SchemaBuilder {

	protected $recordBuilder;
	protected $mapperBuilder;
	protected $namespace;

	public function __construct(RecordBuilder $recordBuilder, MapperBuilder $mapperBuilder, $namespace) {
		$this->recordBuilder = $recordBuilder;
		$this->mapperBuilder = $mapperBuilder;
		$this->namespace = $namespace;
	}

	protected function toUpperCamel($string) {
		return implode("", array_map('ucwords', explode("_", $string)));
	}

	public function build(Schema $schema) {

		$out = [];

		foreach ($schema->getTables() as $table) {
			$base = $this->toUpperCamel($table->getName());

			$recordClassName = $base.'Record';
			$recordPath = 'Objects/'.$recordClassName.'.php';

			$php = $this->recordBuilder->build(
				$this->namespace.'\\Objects',
				$recordClassName,
				$table);

			$out[$recordPath] = $php;


			$mapperClassName = $base.'Mapper';
			$mapperPath = 'Mappers/'.$mapperClassName.'.php';

			$php = $this->mapperBuilder->build(
				$this->namespace.'\\Mappers',
				$recordClassName,
				$mapperClassName,
				$table);

			$out[$mapperPath] = $php;
		}

		return $out;
	}

}