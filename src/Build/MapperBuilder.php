<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class MapperBuilder {

	use PhpBuilderTrait;

	protected static $buildSwitchTemplate = <<<SWITCH
			case %s::TABLE:
				return new %1\$s(\$record, \$this);
SWITCH;
	protected static $readSwitchTemplate = <<<SWITCH
			case %s::TABLE:
				return \$this->from(%1\$s::TABLE)->where([%1\$s::%2s => \$id])->readOne();
SWITCH;

	protected static $template = <<<PHP
<?php

namespace %s;

class Mapper extends \Orm\Data\AbstractMapper {

	public function buildRecordObject(\$table, array \$record) {

		switch (\$table) {
%s
			default:
				throw new \\Exception("Unrecognized table `\$table`");
		}
	}

	public function readFromAutoIncrementId(\$table, \$id) {
		switch (\$table) {
%s
			default:
				throw new \\Exception("No AutoIncrement column on table `\$table`");
		}
	}

	protected function initializeColumnTypes() {
%s
	}
}


PHP;

	public function build($namespace, $objectNamespace, Schema $schema) {

		$cases = [];
		$readSwitch = [];
		$columnTypes = [];
		foreach ($schema->getTables() as $table) {
			$objectClass = $objectNamespace . $this->camelUpper($table->getName());
			$cases[] = sprintf(self::$buildSwitchTemplate, $objectClass);
			if ($aiColumn = $table->getAutoIncrementColumn()) {
				$readSwitch[] = sprintf(self::$readSwitchTemplate, $objectClass, $this->camelLower($aiColumn->getName()));
			}

			foreach ($table->getAllColumns() as $column) {
				$key = sprintf('`%s`.`%s`', $table->getName(), $column->getName());
				$columnTypes[] = "\$this->setColumnType(\"$key\", \\".var_export($column->getType(), true).');';
			}
		}

		return sprintf(self::$template, $namespace, implode("\n", $cases), implode("\n", $readSwitch), $this->indentTabs(implode("\n", $columnTypes), 2));

	}
}