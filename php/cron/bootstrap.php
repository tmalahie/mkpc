<?php
// Scheduled jobs are run by the system crontab (mkpc.crontab), never over HTTP.
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}
require_once(__DIR__ .'/../includes/initdb.php');

// A run still going when the next one is due makes that one a no-op rather than a second copy
// on top of it. Anything the job throws stops it and fails the run.
function cron_job($job, $work) {
	$lock = fopen(sys_get_temp_dir() .'/mkpc-cron-'. $job .'.lock', 'c');
	if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
		fwrite(STDERR, "$job: previous run still going, skipped\n");
		return 0;
	}
	$start = microtime(true);
	try {
		$work();
	}
	catch (Throwable $e) {
		fwrite(STDERR, "$job: ". $e->getMessage() ."\n");
		return 1;
	}
	echo "$job: done in ". round(microtime(true) - $start, 2) ."s\n";
	return 0;
}

// Runs a job's statements in order on one connection, the way a MySQL event runs its body:
// later statements rely on earlier ones - session variables, temporary tables, rows logged
// before they are deleted - so the first failure stops the job.
function cron_run($job, $statements) {
	return cron_job($job, function() use ($statements) {
		global $dbh;
		foreach ($statements as $i => $sql) {
			try {
				$dbh->query($sql)->closeCursor();
			}
			catch (PDOException $e) {
				throw new Exception('statement '. ($i + 1) .' failed: '. $e->getMessage());
			}
		}
	});
}
