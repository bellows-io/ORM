<?php

namespace Orm\Utilities;

class MysqlSchemaFetcher {

	public function fetchDbCreationSql(\PDO $db) {

		$stmt = $db->prepare("SHOW TABLES");
		$stmt->execute();

		$tables = [];
		foreach ($stmt as $row) {
			$table = $row[0];
			$sql = $this->fetchTableCreationSql($table, $db);

			$tables[$table] = $sql;
		}

		return $tables;
	}

	public function fetchTableCreationSql($table, \PDO $db) {
		$tst = $db->prepare("SHOW CREATE TABLE `$table`");
		$tst->execute();
		foreach ($tst as $row) {
			return $row[1];
		}
		return null;
	}

}