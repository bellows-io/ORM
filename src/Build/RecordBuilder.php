<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class RecordBuilder {

	use PhpBuilderTrait;

	public function build($namespace, $className, Table $table, Schema $schema) {
		$php = "";
		$properties = [];
		$gettersetters = [];
		$fkGetters = [];
		$tableName = $table->getName();
		$constants = ["const TABLE = '$tableName';"];
		$validatorInits = [];
		$pk = $table->getPrimaryKey();

		$linkingTables = $schema->getLinkingTables($tableName);
		$linkedGetters = [];
		$identify = [];

		foreach ($linkingTables as $name => $keys) {
			$in = $keys['inKey'];
			$out = $keys['outKeys'];
			$linkUpper = $this->camelUpper($name);

			$match = [];
			$local = $in->getLocalColumns();
			$foreign = $in->getForeignColumns();
			foreach ($local as $i => $inKey) {
				$match[] = $linkUpper.'::'.$this->camelLower($foreign[$i]).' => $this->get'.$this->camelUpper($inKey).'()';
			}


			foreach ($out as $outTable => $fk) {
				$upper = $this->camelUpper($outTable);
				$join = [];

				$local = $fk->getLocalColumns();
				$foreign = $fk->getForeignColumns();

				foreach ($local as $i => $localCol) {
					$join[] = $linkUpper.'::'.$this->camelLower($foreign[$i]).' => '.$upper.'::'.$this->camelLower($localCol);
				}

				$cacheName = $fk->getName().':linked';

				$linkedGetters[] = sprintf(
'	public function get'.$upper.'s() {
		if (! $this->hasForeign("'.$cacheName.'")) {
			$this->setForeign("'.$cacheName.'", $this->mapper
				->from('.$upper.'::TABLE)
				->join('.$linkUpper.'::TABLE, [%s])
				->where([%s])
				->getAll());
		}
		return $this->getForeign("'.$cacheName.'");
	}
', implode(", ", $join), implode(', ', $match));;

			}
		}


		foreach ($table->getAllColumns() as $name => $column) {

			$ucProper = $this->camelUpper($name);
			$property = $this->camelLower($name);

			$constants[] = 'const '.$property.' = "`'.$tableName.'`.`'.$name.'`";';
			$gettersetters[] = sprintf(self::$getterTemplate, $ucProper, $property);
			if (! $pk->hasColumn($name)) {
				$gettersetters[] = sprintf(self::$setterTemplate, $ucProper, $property);
			} else {
				$identify[] = 'self::'.$property.' => $this->get'.$ucProper.'()';
			}
		}

		foreach ($table->getForeignKeys() as $foreignKey) {
			$columns = $foreignKey->getLocalColumns();
			$trimRegex = '/('.$table->getName().'_)?(.*?)(_id)?/';
			$matches = [];
			$names = array_map(function($c) use ($trimRegex) {
				$c = preg_replace('/(.*?)(_id)?/', '$1', $c);
				return $this->camelUpper($c);
			}, $columns);
			$fnName = implode($names);
			$fkClass = $this->camelUpper($foreignKey->getForeignTable());

			for ($i = 0; $i < count($columns); $i++) {
				$matches[] = sprintf("${fkClass}::%s => \$this->get%s()",
					$this->camelLower($foreignKey->getForeignColumns()[$i]),
					$this->camelUpper($columns[$i]));
			}
			$matches = implode(',',$matches);

			$keyName = $foreignKey->getName();
			$fkGetters[] =
"	public function get${fnName}() {
		if (! \$this->hasForeign('$keyName')) {
			\$result = \$this->mapper
				->from(${fkClass}::TABLE)
				->where([$matches])
				->readOne();
			\$this->setForeign('$keyName', \$result);
		}
		return \$this->getForeign('$keyName');
	}
";
		}

		$php = sprintf(self::$classTemplate,
			$namespace,
			$className,
			implode("\n\t", $constants),
			implode("\n\n", $gettersetters),
			implode("\n\t", $fkGetters),
			implode("\n\t", $linkedGetters),

			self::indentTabs(implode(",\n", $identify),3)
		);

		return $php;
	}

	protected static $setterTemplate = <<<G
	public function set%1\$s(\$value) {
		\$this->set(self::%2\$s, \$value);
		return \$this;
	}
G;

	protected static $getterTemplate = <<<S
	public function get%1\$s() {
		return \$this->get(self::%2\$s);
	}
S;

	protected static $classTemplate = <<<PHP
<?php

namespace %s;

class %s extends \Orm\Data\AbstractRecord {

	%s

%s
%s
%s
	public function identify() {
		return [
%s
		];
	}


}

PHP;
}