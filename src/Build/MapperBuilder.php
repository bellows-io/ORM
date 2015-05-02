<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class MapperBuilder {

	use PhpBuilderTrait;

	protected static $buildSwitchTemplate = <<<SWITCH
			case Objects\\%s::TABLE:
				return new Objects\\%1\$s(\$record, \$this);
SWITCH;
	protected static $readSwitchTemplate = <<<SWITCH
			case Objects\\%s::TABLE:
				return \$this->from(Objects\\%1\$s::TABLE)->where([Objects\\%1\$s::%2s => \$id])->readOne();
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
}


PHP;

	public function build($namespace, $recordObjectNamespace, Schema $schema) {

		$cases = [];
		$readSwitch = [];
		foreach ($schema->getTables() as $table) {
			$cases[] = sprintf(self::$buildSwitchTemplate, $this->camelUpper($table->getName()));
			if ($aiColumn = $table->getAutoIncrementColumn()) {
				$readSwitch[] = sprintf(self::$readSwitchTemplate, $this->camelUpper($table->getName()), $this->camelLower($aiColumn->getName()));
			}
		}

		return sprintf(self::$template, $namespace, implode("\n", $cases), implode("\n", $readSwitch));

	}
}