<?php
// Applies the migrations of this directory that the database hasn't run yet, in name order.
//
//   php php/migrations/migrate.php                     apply the pending migrations
//   php php/migrations/migrate.php --status            list applied and pending migrations
//   php php/migrations/migrate.php --check             compare the database with setup.sql
//   php php/migrations/migrate.php --mark-applied NAME record a migration applied by hand
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}
require_once(__DIR__ .'/../includes/initdb.php');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

define('MIGRATION_NAME', '#^\d{8}-\d{4}-[a-z0-9-]+\.(sql|php)$#');
define('SETUP_SQL', __DIR__ .'/../../docker/php/scripts/setup.sql');

function migrationFiles() {
	$files = array();
	foreach (scandir(__DIR__) as $file)
		if (preg_match(MIGRATION_NAME, $file))
			$files[] = $file;
	sort($files, SORT_STRING);
	return $files;
}
function hasMigrationsTable() {
	global $dbh;
	return (bool) $dbh->query('SHOW TABLES LIKE "mkmigrations"')->fetch();
}
function appliedMigrations() {
	global $dbh;
	if (!hasMigrationsTable())
		return array();
	return $dbh->query('SELECT name FROM mkmigrations ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
}
function createMigrationsTable() {
	global $dbh;
	$dbh->exec('CREATE TABLE IF NOT EXISTS `mkmigrations` (`name` varchar(255) NOT NULL, `applied_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
}
function recordMigration($file) {
	global $dbh;
	$dbh->prepare('INSERT INTO mkmigrations SET name=?')->execute(array($file));
}

// Splits on the semicolons that end statements, leaving those inside quotes and comments.
// /*! ... */ comments are kept: MySQL runs them.
function splitStatements($sql) {
	$statements = array();
	$statement = '';
	$length = strlen($sql);
	for ($i = 0; $i < $length; $i++) {
		$char = $sql[$i];
		$next = ($i+1 < $length) ? $sql[$i+1] : '';
		if (($char === "'") || ($char === '"') || ($char === '`')) {
			$start = $i;
			for ($i++; $i < $length; $i++) {
				if (($sql[$i] === '\\') && ($char !== '`'))
					$i++;
				elseif ($sql[$i] === $char) {
					if (($i+1 < $length) && ($sql[$i+1] === $char))
						$i++;
					else
						break;
				}
			}
			$statement .= substr($sql, $start, $i-$start+1);
		}
		elseif (($char === '#') || (($char === '-') && ($next === '-') && (($i+2 >= $length) || ctype_space($sql[$i+2])))) {
			$end = strpos($sql, "\n", $i);
			$i = ($end === false) ? $length : $end;
			$statement .= "\n";
		}
		elseif (($char === '/') && ($next === '*') && (($i+2 >= $length) || ($sql[$i+2] !== '!'))) {
			$end = strpos($sql, '*/', $i+2);
			$i = ($end === false) ? $length : $end+1;
			$statement .= ' ';
		}
		elseif ($char === ';') {
			if (trim($statement) !== '')
				$statements[] = trim($statement);
			$statement = '';
		}
		else
			$statement .= $char;
	}
	if (trim($statement) !== '')
		$statements[] = trim($statement);
	return $statements;
}

function runMigration($file) {
	global $dbh;
	$path = __DIR__ .'/'. $file;
	if (substr($file, -4) === '.php') {
		set_error_handler(function($severity, $message, $errorFile, $line) {
			throw new ErrorException($message, 0, $severity, $errorFile, $line);
		});
		try {
			(function() use ($path) {
				global $dbh;
				require($path);
			})();
		}
		finally {
			restore_error_handler();
		}
	}
	else {
		foreach (splitStatements(file_get_contents($path)) as $i => $statement) {
			try {
				$dbh->query($statement)->closeCursor();
			}
			catch (PDOException $e) {
				throw new RuntimeException('statement '. ($i+1) .' failed: '. $e->getMessage() ."\n\n". $statement ."\n", 0, $e);
			}
		}
	}
	recordMigration($file);
}

function migrate() {
	global $dbh;
	if (!$dbh->query('SELECT GET_LOCK("mkpc-migrate", 0)')->fetchColumn()) {
		fwrite(STDERR, "Another migration run is in progress.\n");
		return 1;
	}
	createMigrationsTable();
	$pending = array_values(array_diff(migrationFiles(), appliedMigrations()));
	if (!$pending) {
		echo "No pending migration.\n";
		return 0;
	}
	foreach ($pending as $file) {
		echo "Applying $file... ";
		$start = microtime(true);
		try {
			runMigration($file);
		}
		catch (Throwable $e) {
			echo "FAILED\n\n". $e->getMessage() ."\n\n";
			fwrite(STDERR, "$file is not recorded as applied, and the migrations after it were not run.\n"
				."Statements before the failing one did apply: MySQL commits schema changes immediately.\n"
				."Fix the database or the migration, then run again.\n");
			return 1;
		}
		echo 'done in '. round(microtime(true) - $start, 2) ."s\n";
	}
	return 0;
}

function status() {
	$files = migrationFiles();
	$applied = appliedMigrations();
	foreach ($files as $file)
		echo (in_array($file, $applied) ? 'applied  ':'PENDING  ') . $file ."\n";
	foreach (array_diff($applied, $files) as $missing)
		echo "applied  $missing (file not found)\n";
	if (!$files && !$applied)
		echo "No migration yet.\n";
	return 0;
}

function setupSchema() {
	$schema = array();
	$table = null;
	foreach (file(SETUP_SQL) as $line) {
		if (preg_match('#^CREATE TABLE `([^`]+)`#', $line, $match))
			$schema[$table = $match[1]] = array();
		elseif (($table !== null) && preg_match('#^\s+`([^`]+)` #', $line, $match))
			$schema[$table][] = $match[1];
		elseif ($line[0] === ')')
			$table = null;
	}
	return $schema;
}
// Reports what setup.sql defines and the database lacks, or the other way round for the
// tables setup.sql defines. Tables setup.sql doesn't know are ignored: production has some.
function check() {
	global $dbh;
	$database = array();
	foreach ($dbh->query('SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()') as $row)
		$database[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
	$differences = array();
	foreach (setupSchema() as $table => $columns) {
		if (!isset($database[$table])) {
			$differences[] = "table $table: in setup.sql, missing from the database";
			continue;
		}
		foreach (array_diff($columns, $database[$table]) as $column)
			$differences[] = "column $table.$column: in setup.sql, missing from the database";
		foreach (array_diff($database[$table], $columns) as $column)
			$differences[] = "column $table.$column: in the database, missing from setup.sql";
	}
	if (!$differences) {
		echo "The database matches setup.sql.\n";
		return 0;
	}
	echo implode("\n", $differences) ."\n";
	return 1;
}

function markApplied($file) {
	if (!in_array($file, migrationFiles())) {
		fwrite(STDERR, "No migration named $file.\n");
		return 1;
	}
	if (in_array($file, appliedMigrations())) {
		echo "$file is already recorded as applied.\n";
		return 0;
	}
	createMigrationsTable();
	recordMigration($file);
	echo "$file recorded as applied, without running it.\n";
	return 0;
}

$command = isset($argv[1]) ? $argv[1] : null;
switch ($command) {
case null:
	exit(migrate());
case '--status':
	exit(status());
case '--check':
	exit(check());
case '--mark-applied':
	if (isset($argv[2]))
		exit(markApplied($argv[2]));
default:
	fwrite(STDERR, "Usage: php php/migrations/migrate.php [--status | --check | --mark-applied NAME]\n");
	exit(1);
}
