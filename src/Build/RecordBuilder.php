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


		foreach ($table->getAllColumns() as $name => $column) {

			$ucProper = $this->camelUpper($name);
			$property = $this->camelLower($name);

			$export = "self::{$property} => \\".var_export($column->getType(), true);
			$export = self::spaceToTab($export, 3);


			$constants[] = 'const '.$property.' = "'.$name.'";';
			$validatorInits[] = $export;
			$gettersetters[] = sprintf(self::$getterTemplate, $ucProper, $property);
			if (! $pk->hasColumn($name)) {
				$gettersetters[] = sprintf(self::$setterTemplate, $ucProper, $property);
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
					$foreignKey->getForeignColumns()[$i], $this->camelUpper($columns[$i]));
			}
			$matches = implode(',',$matches);

			$keyName = $foreignKey->getName();
			$fkGetters[] = "	public function get${fnName}() {
		if (! \$this->hasForeign('$keyName')) {
			\$result = \$this->mapper->from(${fkClass}::TABLE)->where([$matches])->readOne();
			\$this->setForeign('$keyName', \$result);
		}
		return \$this->getForeign('$keyName');
	}
";

			//$fkGetters[] = sprintf(self::$fkGetterTemplate, $fnName, $foreignKey->getForeignTable(), implode(',', $matches), $className, $foreignKey->getName());
		}

		$php = sprintf(self::$classTemplate,
			$namespace,
			$className,
			implode("\n\n", $gettersetters),
			implode("\n\t", $fkGetters),
			implode("\n\t", $constants),
			self::indentTabs(implode(",\n", $validatorInits), 3)
		);

		return $php;
	}

	protected static $setterTemplate = <<<G
	public function set%1\$s(\$value) {
		\$this->set(self::%2\$s, \$value);
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

	protected static function buildValidators() {
		return [
%s
		];
	}


}

PHP;
}