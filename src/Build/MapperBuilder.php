<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class MapperBuilder {

	use PhpBuilderTrait;

	protected static $template = <<<PHP
<?php

namespace %s;

use %s;

class %s extends \Orm\Data\AbstractMapper {

%s

}


PHP;

	public function build($namespace, $recordClassNamespace, $className, Table $table) {

		$nsPartials = explode('\\', $recordClassNamespace);
		$recordClassName = $nsPartials[count($nsPartials)-1];

		$str = '';
		$str .= $this->buildCreateMethod($recordClassName, $table);
		$str .= $this->buildReadMethod($recordClassName, $table);
		$str .= $this->buildUpdateMethod($recordClassName, $table);
		$str .= $this->buildDeleteMethod($recordClassName, $table);

		return sprintf(self::$template,
			$namespace,
			implode('\\', $nsPartials),
			$className,
			$this->indentTabs($str, 1)
		);
	}

	protected function buildDeleteMethod($recordClassName, Table $table) {

		$arguments  = [];
		$matchArgs  = [];
		$objectName = lcfirst($recordClassName);

		$pkey = $table->getPrimaryKey();

		foreach ($table->getAllColumns() as $name => $column) {
			$base = $this->camelUpper($column->getName());
			$row = sprintf('\'%s\' => $%s->get%s()', $column->getName(), $objectName, $base);
			if ($pkey->hasColumn($column->getName())) {
				$matchArgs[] = $row;
			}
		}

		$php =<<<PHP

public function delete(%s \$%s) {
	return \$this->deleteRow("%s", [
		%s
	]);
}

PHP;

		$php = sprintf($php,
			$recordClassName,
			lcfirst($recordClassName),
			$table->getName(),
			implode(",\n\t\t", $matchArgs)
		);

		return $php;
	}

	protected function buildUpdateMethod($recordClassName, Table $table) {

		$arguments  = [];
		$updateArgs = [];
		$matchArgs  = [];
		$objectName = lcfirst($recordClassName);

		$pkey = $table->getPrimaryKey();

		foreach ($table->getAllColumns() as $name => $column) {
			$base = $this->camelUpper($column->getName());
			$row = sprintf('\'%s\' => $%s->get%s()', $column->getName(), $objectName, $base);
			if ($pkey->hasColumn($column->getName())) {
				$matchArgs[] = $row;
			} else {
				$updateArgs[] = $row;
			}
		}

		$php =<<<PHP

public function update(%s \$%s) {
	return \$this->updateRow("%s", [
		%s
	], [
		%s
	]);
}

PHP;

		$php = sprintf($php,
			$recordClassName,
			lcfirst($recordClassName),
			$table->getName(),
			implode(",\n\t\t", $updateArgs),
			implode(",\n\t\t", $matchArgs)
		);

		return $php;
	}

	protected function buildReadMethod($recordClassName, Table $table) {

		$arguments  = [];
		$updateArgs = [];
		$matchArgs  = [];
		$objectName = lcfirst($recordClassName);
		$arguments = [];
		$constructorArgs = [];

		$pkey = $table->getPrimaryKey();

		foreach ($table->getAllColumns() as $name => $column) {

			$arg  = $this->camelLower($column->getName());
			if ($pkey->hasColumn($column->getName())) {
				$arguments[]= '$'.$arg;
				$matchArgs[] = sprintf('\'%s\' => $%s', $column->getName(), $arg);
			}
			$constructorArgs[] = sprintf('$row[\'%s\']', $column->getName());
		}

		$php =<<<PHP

public function read(%s) {
	\$row = \$this->readRow("%s", [
		%s
	]);
	return \$this->makeFromRow(\$row);
}

public function makeFromRow(array \$row) {
	return new %s(%s);
}

PHP;

		$php = sprintf($php,
			implode(", ", $arguments),
			$table->getName(),
			implode(",\n\t\t", $matchArgs),
			$recordClassName,
			implode(', ', $constructorArgs)
		);

		return $php;
	}

	protected function buildCreateMethod($recordClassName, Table $table) {

		$arguments  = [];
		$insertArgs = [];
		$aiAssignStatement = '';
		$aiArg = '';

		$aiColumn = $table->getAutoIncrementColumn();
		if ($aiColumn) {
			$aiParam  = lcfirst($this->camelUpper($aiColumn->getName()));
			$aiAssignStatement = '$'.$aiParam.' = ';
			$aiArg = '$'.$aiParam.', ';
		}

		foreach ($table->getAllColumns() as $name => $column) {
			if ($column != $aiColumn) {
				$base = lcfirst($this->camelUpper($column->getName()));
				$arguments[] = '$'.$base;
				$insertArgs[] = sprintf('\'%s\' => $%s', $column->getName(), $base);
			}
		}

		$php =<<<PHP

public function create(%s) {
	%s\$this->insertRow("%s", [
		%s
	]);

	return new %s(%s%1\$s);
}

PHP;

		$php = sprintf($php,
			implode(', ', $arguments),
			$aiAssignStatement,
			$table->getName(),
			implode(",\n\t\t", $insertArgs),
			$recordClassName,
			$aiArg
		);

		return $php;

	}
}

/*

namespace OranjIo\Mappers;

class UserMapper extends \Orm\Data\AbstractMspper {

	public function create($userUsername, $userName, $userEmail, $userCookie, $userPassword, $userRole) {

		$userId = $this->insert("user", [
			"user_username" => $userUsername,
			"user_name" => $userName,
			"user_email" => $userEmail,
			"user_cookie" => $userCookie,
			"user_password" => $userPassword,
			"user_role" => $userRole
		]);

		return new OranjIo\Objects\UserRecord($userId, $userUsername, $userName, $userEmail, $userCookie, $userPassword, $userRole);
	}

}



 */