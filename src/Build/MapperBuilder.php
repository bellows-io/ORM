<?php

namespace Orm\Build;

use \Orm\Schema\Schema;
use \Orm\Schema\Table;

class MapperBuilder {

	protected function toUpperCamel($string) {
		return implode("", array_map('ucwords', explode("_", $string)));
	}

	public function build($namespace, $recordClassName, $className, Table $table) {

		$str = '<?php'."\n";
		$str .= $this->buildCreateMethod($recordClassName, $className, $table)."\n";
		$str .= $this->buildUpdateMethod($recordClassName, $className, $table);

		return $str;

	}

	protected function buildUpdateMethod($recordClassName, $className, Table $table) {

		$arguments  = [];
		$updateArgs = [];
		$matchArgs  = [];
		$objectName = lcfirst($recordClassName);

		$pkey = $table->getPrimaryKey();

		foreach ($table->getAllColumns() as $name => $column) {
			$base = $this->toUpperCamel($column->getName());
			$row = sprintf('\'%s\' => $%s->get%s()', $column->getName(), $objectName, $base);
			if ($pkey->hasColumn($column->getName())) {
				$matchArgs[] = $row;
			} else {
				$updateArgs[] = $row;
			}
		}

		$php =<<<PHP

public function update(%s \$%s) {
	\$this->updateRow("%s", [
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

	protected function buildCreateMethod($recordClassName, $className, Table $table) {

		$arguments  = [];
		$insertArgs = [];
		$aiAssignStatement = '';
		$aiArg = '';

		$aiColumn = $table->getAutoIncrementColumn();
		if ($aiColumn) {
			$aiParam  = lcfirst($this->toUpperCamel($aiColumn->getName()));
			$aiAssignStatement = '$'.$aiParam.' = ';
			$aiArg = '$'.$aiParam.', ';
		}

		foreach ($table->getAllColumns() as $name => $column) {
			if ($column != $aiColumn) {
				$base = lcfirst($this->toUpperCamel($column->getName()));
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
use \PDO;

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