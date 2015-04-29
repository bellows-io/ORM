<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class RecordBuilder {

	use PhpBuilderTrait;

	public function build($namespace, $className, Table $table) {
		$php = "";
		$properties = [];
		$arguments = [];
		$gettersetters = [];
		$assignments = [];
		$validatorProperties = [];
		$validatorInits = [];

		foreach ($table->getAllColumns() as $name => $column) {

			$ucProper = $this->camelUpper($name);
			$property = $this->camelLower($name);

			$properties[] = "protected \$$property;";
			$validatorProperties[] = "private static \$${property}Type;";

			$arguments[]  = "\$$property";
			$export = "self::\${$property}Type = \\".var_export($column->getType(), true);

			$export = self::spaceToTab($export, 3);

			$validatorInits[] = $export.";";
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
			self::indentTabs(implode("\n", $validatorInits), 3)
		);

		return $php;
	}

	protected static $getterSetterTemplate = <<<GS
	public function set%1\$s(\$value) {
		self::\$%2\$sType->validate(\$value);
		\$value = self::\$%2\$sType->parse(\$value);

		if (\$value != \$this->%2\$s) {
			\$this->markDirty();
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

class %s extends \Orm\Data\AbstractRecord {

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