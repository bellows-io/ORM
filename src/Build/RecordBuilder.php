<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class RecordBuilder {

	protected function toUpperCamel($string) {
		return implode("", array_map('ucwords', explode("_", $string)));
	}

	public function build($namespace, $className, Table $table) {
		$php = "";
		$properties = [];
		$arguments = [];
		$gettersetters = [];
		$assignments = [];
		$validatorProperties = [];
		$validatorInits = [];

		foreach ($table->getAllColumns() as $name => $column) {

			$ucProper = $this->toUpperCamel($name);
			$property = lcfirst($ucProper);

			$properties[] = "protected \$$property;";
			$validatorProperties[] = "private static \$${property}Type;";

			$arguments[]  = "\$$property";
			$export = var_export($column->getType(), true);
			$export = str_replace("   ", "\t\t\t\t", $export);
			$export = str_replace("\n)", "\n\t\t\t)", $export);

			$validatorInits[] = "self::\${$property}Type = \\".$export.";";
			$assignments[] = sprintf("\$this->set%s(\$%s);", $ucProper, $property);
			$gettersetters[] = sprintf(self::$getterSetterTemplate, $ucProper, $property);

		}

		$php = sprintf(self::$classTemplate,
			$namespace,
			$className,
			implode("\n\t", $properties),
			implode(", ", $arguments),
			implode("\n\t\t", $assignments),
			implode("\n\n", $gettersetters),
			implode("\n\t", $validatorProperties),
			implode("\n\t\t\t", $validatorInits)
		);

		return $php;
	}

	protected static $getterSetterTemplate = <<<GS
	public function set%1\$s(\$value) {
		self::\$%2\$sType->validate(\$value);
		if (\$value != \$this->%2\$s) {
			\$this->modified = true;
			\$this->%2\$s = \$value;
		}
	}

	public function get%1\$s(\$value) {
		return \$this->%2\$s;
	}
GS;

	protected static $classTemplate = <<<PHP
<?php

namespace %s;

class %s {

	%s

	public function __construct(%s) {
		self::initValidators();
		%s
	}

%s


	private static \$validatorsInit = false;
	%s

	protected static function initValidators() {
		if (! self::\$validatorsInit) {
			%s
			self::\$validatorsInit = true;
		}
	}


}

PHP;
}