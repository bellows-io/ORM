<?php

// Connect to your database
$pdo = new \PDO("mysql:host=localhost;dbname=test", "root", "");

// Build a `Schema` Object
$sqlFetcher = new \Orm\Utilities\MysqlSchemaFetcher();
$sqlParser  = new \Orm\Utilities\MysqlSchemaParser();

$tableSqls = $sqlFetcher->fetchDbCreationSql($pdo);
$tables = [];
foreach ($tableSqls as $tableName => $sql) {
	$tables[] = $sqlParser->parseTableSql($sql);
}
$schema = new \Orm\Schema\Schema($tables);

$namespace = "MyApp";
$root = __DIR__.'/cache/Data/';
$recordBuilder = new \Orm\Build\RecordBuilder();
$mapperBuilder = new \Orm\Build\MapperBuilder();

file_put_contents($root.'Mapper.php', $mapperBuilder->build($namespace, '', $schema));
foreach ($schema->getTables() as $table) {
	$className = $recordBuilder->camelUpper($table->getName());
	$contents = $recordBuilder->build($namespace, $className, $table, $schema);
	file_put_contents($root.$className, $contents);
}