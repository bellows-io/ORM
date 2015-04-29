# WIPORM

Not entirely sure what this is supposed to do, other than autogenerate database interaction.

```php
namespace Orm {

	/**
	 * This step will create the Schema object from a db connection.
	 *
	 * The user may construct it manually, but this should speed
	 * things up a bit.
	 */
	$schemaSqlFetcher = new Utilities\MysqlSchemaFetcher();
	$sqlParser = new Utilities\MysqlSchemaParser();

	$tables = $schemaSqlFetcher->fetchDbCreationSql($pdo);
	$tableObjs = [];
	foreach ($tables as $sql) {
		$tableObjs[] = $sqlParser->parseTableSql($sql);
	}
	$schema = new \Orm\Schema\Schema($tableObjs);

	/**
	 * With a Schema object, we can build out our mapper
	 * and record objects
	 */
	$schemaBuilder = new \Orm\Build\SchemaBuilder(
		new Build\RecordBuilder(),
		new Build\MapperBuilder(),
		"MyApp");

	$fileContents = $schemaBuilder->build($schema);
	foreach ($fileContents as $filename => $contents) {
		if (! is_null($contents)) {
			file_put_contents(__DIR__.'/cache/Orm/'.$filename, $contents);
		}
	}

	/**
	 * Great! Now you have a CRUD interface for your database all
	 * ready for you. Set up an autoloader for convenience
	 */
	$userMapper = new \MyApp\Mappers\UserMapper($pdo);
	$user = $userMapper->read(1);




```