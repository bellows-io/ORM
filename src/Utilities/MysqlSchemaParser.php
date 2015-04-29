<?php

namespace Orm\Utilities;

class MysqlSchemaParser {

	public function parseTableSql($sql) {

		if (! preg_match('/^\s*CREATE TABLE\s+`(?P<name>.+?)`\s*\(\s*(?P<inner>.*)\s*\)(.*)$/is', $sql, $matches)) {
			return null;
		}
		$name = $matches['name'];
		$inner = $matches['inner'];
		$defs = explode(',', $inner);
		$columns = [];
		$pkeys = [];

		try {
			foreach ($defs as $def) {
				$def = trim($def);
				if ($def[0] == '`') {
					$columns[] = $this->parseColumnDef($def);
				} else if (substr($def, 0, 12) == 'PRIMARY KEY ') {
					if (preg_match('/PRIMARY KEY \(`(.+)`\)/', $def, $matches)) {
						$pkeys = explode(',', str_replace('`', '', $matches[1]));
					}
				}
			}
		} catch (\Exception $ex) {
			throw new \Exception("in table sql: ".$sql.":\n\t".$ex->getMessage());
		}

		return new \Orm\Schema\Table($name, $columns, new \Orm\Schema\PrimaryKey($pkeys), [], []);

	}

	public function parseColumnDef($sql) {
		if (! preg_match('/\s*`(?P<name>.+?)`\s+(?P<type>(?P<base>[a-z]+)(\((?P<length>.+)\))?)\s*(?P<un>unsigned)?\s*(?P<nu>NULL|NOT NULL)?\s*(DEFAULT\s+(?P<def>.+))?\s*(?P<ai>AUTO_INCREMENT)?(.*?)$/', $sql, $matches)) {
			throw new \Exception("Could not parse column: \"$sql\"");
		}

		$nullable = $matches['nu'] == 'NULL' || $matches['def'] == 'NULL';
		$base = strtolower($matches['base']);
		switch ($base) {
			case 'varchar':
				$type = new \Orm\Schema\Types\DbVarchar($nullable, (int)$matches['length']);
				break;
			case 'text':
				$type = new \Orm\Schema\Types\DbText($nullable);
				break;
			case 'enum':
				$type = new \Orm\Schema\Types\DbEnum($nullable, array_map(function($str) {
					return trim($str, '"\'');
				}, explode(',', $matches['length'])));
				break;
			case 'int':
				$type = new \Orm\Schema\Types\DbInt($nullable, (int)$matches['length']);
				break;
			case 'mediumint':
				$type = new \Orm\Schema\Types\DbMediumInt($nullable, (int)$matches['length']);
				break;
			case 'datetime':
				$type = new \Orm\Schema\Types\DbDateTime($nullable);
				break;
		}

		$c =  new \Orm\Schema\Column(
			$matches['name'],
			$type,
			! empty($matches['ai'])
		);

		return $c;
	}

	public function parseType($sql) {

		if (! preg_match('/^(?P<datatype>(?P<base>[a-z]+?)(\((?P<length>[0-9]+)\))?\s+(unsigned)\s*(?P<nu>NULL|NOT NULL)?)(.*?)$/si', $sql, $matches)) {
			throw new \Exceptino("COULD NOT MATCH type for line $sql");
		}
		$base = $matches['base'];
		$nullable = $matches['nu'] == 'NULL';
		switch (strtolower($base)) {
			case 'varchar':
				return new \Orm\Schema\Types\DbVarchar($nullable, (int)$matches['length']);
			case 'text':
				return new \Orm\Schema\Types\DbText($nullable);
			case 'enum':
				return new \Orm\Schema\Types\DbEnum($nullable, array_map(function($str) {
					return trim($str, '"\'');
				}, explode(',', $matches['length'])));
			case 'int':
				return new \Orm\Schema\Types\DbInt($nullable, (int)$matches['length']);
			case 'mediumint':
				return new \Orm\Schema\Types\DbMediumInt($nullable, (int)$matches['length']);
			case 'datetime':
				return new \Orm\Schema\Types\DbDateTime($nullable);
		}
		return null;
	}

}

/*
	$userTable = new Table(
		"user",
		[
			new Column("user_id",       new Types\DbInt(false, 11), true),
			new Column('user_username', new Types\DbVarchar(false, 255)),
			new Column('user_name',     new Types\DbVarchar(false, 255)),
			new Column('user_email',    new Types\DbVarchar(false, 255)),
			new Column('user_cookie',   new Types\DbVarchar(true, 255)),
			new Column('user_password', new Types\DbVarchar(false, 255)),
			new Column('user_role',     new Types\DbInt(false, 11))
		],
		new PrimaryKey(["user_id"]),
		[
			new Index("idx_username", ["username"])
		],
		[
			new ForeignKey("user_role_id", "user_role", "user_role_id")
		]);
 */